<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Inventory\StockCategory;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Yajra\DataTables\QueryDataTable;

class Services extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_SERVICEMSTRREP), 403);

        $inputs = [
            'category' => '',
            'bank' => ''
        ];
        $categories = StockCategory::select('category_id', 'description')->get();
        $banks = BankAccount::select('id', 'account_code', 'bank_account_name')->get();

        return view(
            'reports.managementReport.services',
            compact('categories', 'banks', 'inputs')
        );
    }

    /**
     * Returns the query builder instance
     *
     * @param array $filters
     * @return Builder
     */
    public function getBuilder($filters = [])
    {
        $builder = DB::table('0_stock_master as stock')
            ->leftJoin('0_stock_category as category', 'stock.category_id', 'category.category_id')
            ->leftJoin('0_prices as price', function(JoinClause $join) {
                $join->on('price.stock_id', 'stock.stock_id')
                    ->where('price.sales_type_id', 1);
            })
            ->leftJoin('0_chart_master as govt_ledger','govt_ledger.account_code', 'stock.govt_bank_account')
            ->leftJoin('0_chart_master as returnable_to_ledger','returnable_to_ledger.account_code','stock.returnable_to')
            ->leftJoin('0_chart_master as split_govt_fee_acc_ledger','split_govt_fee_acc_ledger.account_code','stock.split_govt_fee_acc')
            ->select(
                'stock.stock_id',
                'stock.description',
                'stock.long_description',
                'category.description as category_name',
                'price.price as service_charge',
                'stock.govt_fee',
                'stock.pf_amount',
                'govt_ledger.account_name as govt_bank_account',
                'stock.bank_service_charge',
                'stock.bank_service_charge_vat',
                'stock.commission_loc_user',
                'stock.commission_non_loc_user',
                'returnable_to_ledger.account_name as returnable_to',
                'stock.returnable_amt',
                'split_govt_fee_acc_ledger.account_name as split_govt_fee_acc',
                'stock.split_govt_fee_amt',
                'stock.extra_srv_chg',
            );

        if (!empty($filters['category'])) {
            $builder->where('stock.category_id', $filters['category']);
        }

        if (!empty($filters['bank'])) {
            $builder->where('stock.govt_bank_account', $filters['bank']);
        }

        return $builder;
    }

    /**
     * Handles the dataTable api requests
     *
     * @param Request $request
     */
    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_SERVICEMSTRREP), 403);

        $builder = DB::query()->fromSub($this->getBuilder($request->all()), 't');
        $dataTable = new QueryDataTable($builder);
        
        return $dataTable->toJson(); 
    }

    /**
     * Exports the report
     *
     * @param Request $request
     */
    public function export(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_SERVICEMSTRREP), 403);
        $meta = []; 
        $ext = $request->input('to');
        $title = 'Invoice Payments';

        // Its stated in wkhtmltopd docs that if user input is
        // not sanitized when generating report, it could lead
        // to complete server takedown
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'category' => 'nullable|integer',
            'bank' => 'nullable|integer',
        ]);

        if (!empty($inputs['category'])) {
            $meta['category'] = StockCategory::select('description')->where('category_id', $inputs['category'])->value('description');
        }

        if (!empty($inputs['bank'])) {
            $meta['Bank Account'] = BankAccount::select('account_code', 'bank_account_name')
                ->where('id', $inputs['bank'])
                ->first()
                ->formatted_name ?? null;
        }

        $queryBuilder = $this->getBuilder($request->all());

        $textColumns = [ // Set Column to be displayed
            'Stock Id' => 'stock_id',
            'Service Name' => 'description',
            'Service Name (Arabic)' => 'long_description',
            'Category' => 'category_name',
            'Govt Bank' => 'govt_bank_account',
            'Recievable Benefits Acc' => 'returnable_to',
            'Split Govt. Fee Acc' => 'split_govt_fee_acc',
        ];

        $amountColumns = [
            'Service Charge' => 'service_charge',
            'Govt. Fee' => 'govt_fee',
            'PF. Amount' => 'pf_amount',
            'Bank Charge' => 'bank_service_charge',
            'Vat(Bank Charge)' => 'bank_service_charge_vat',
            'Employee(Local) Comm' => 'commission_loc_user',
            'Employee(Non-Local) Comm' => 'commission_non_loc_user',
            'Recievable Benefits Amt' => 'returnable_amt',
            'Split Govt. Fee Amt' => 'split_govt_fee_amt',
            'Extra Service Chg' => 'extra_srv_chg'

        ];

        $generator = app($ext == 'xlsx' 
            ? ExcelReport::class
            : PdfReport::class
        )->of($title, $meta, $queryBuilder, array_merge($textColumns, $amountColumns))
        ->setPaper('a4');

        $showTotal = [];
        foreach ($amountColumns as $column => $key) {
            $showTotal[$column] = 'point';
        }
        $generator->showTotal($showTotal);

        if ($ext == 'pdf') {
            foreach ($amountColumns as $column => $key) {
                $generator->editColumn($column, [
                    'displayAs' => function($result) use ($key) {
                        return number_format($result->{$key}, 2);
                    },
                    'class' => 'text-right'
                ]);
            }
            foreach ($textColumns as $column => $key) {
                $generator->editColumn($column, [
                    'displayAs' => function ($result) use ($key) {
                        return Str::limit($result->{$key}, '20');
                    } 
                ]);
            }
        } else {
            $generator->simple();
        }

        $file = 'download/'.Str::orderedUuid().".$ext";
        $generator->store($file);

        return [
            "redirect_to" => url(route("file.download", ['type' => 'service-report', 'file' => basename($file)]))
        ];
    }
}