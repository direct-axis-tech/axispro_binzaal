<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesMan;
use App\Models\System\User;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Yajra\DataTables\QueryDataTable;

class ServiceTransactions extends Controller {

    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(
            Permissions::SA_SERVICETRANSREP_OWN,
            Permissions::SA_SERVICETRANSREP_ALL
        ), 403);
        
        $inputs = [
            'invoice_no' => '',
            'customer' => [],
            'category' => '',
            'invoice_type' => '',
            'sales_man_id' => '',
            'tran_date_from' => date(dateformat()),
            'tran_date_to' => date(dateformat()),
            'ref_name' => '',
            'transaction_id' => '',
            'service' => '',
            'display_customer' => '',
            'employee' => '',
            'payment_status' => '',
            'transaction_status' => '',
        ];
        $customers = Customer::select('debtor_no', 'name')->get();
        $categories = StockCategory::select('category_id', 'description')->get();
        $salesMen = SalesMan::select('salesman_code', 'salesman_name')->get();
        $stockItems = StockItem::select('stock_id', 'description')->get();
        $users = User::select('id', 'user_id')->get();

        return view(
            'reports.managementReport.serviceTransactions',
            compact('customers', 'categories', 'salesMen', 'stockItems', 'users', 'inputs')
        );
    }

    /**
     * Returns the query builder instance
     *
     * @param array $filters
     * @param boolean $authorizedOnly
     * @return Builder
     */
    public function getBuilder($filters = [], $authorizedOnly = true)
    {
        $factor = "if(`detail`.`debtor_trans_type` = '".CustomerTransaction::CREDIT."', -1, 1)";
        $total = "`invoice`.`ov_amount` + `invoice`.`ov_gst` + `invoice`.`ov_freight` + `invoice`.`ov_freight_tax` + `invoice`.`ov_discount`";
        $unitPrice = '(`detail`.`unit_price` + `detail`.`returnable_amt` - `detail`.`pf_amount` - if(`invoice`.`tax_included`, `detail`.`unit_tax`, 0))';
        $builder = DB::query()
            ->addSelect(
                'category.description as category_name',
                'salesman.salesman_name',
                'invoice.invoice_type',
                'invoice.reference as invoice_no',
                'detail.stock_id',
                'stock.category_id',
                'stock.description as service_eng_name',
                DB::raw("{$factor} * {$unitPrice} as unit_price"),
                DB::raw("{$factor} * detail.unit_tax as unit_tax"),
                DB::raw("{$factor} * detail.quantity as quantity"),
                DB::raw("{$factor} * detail.discount_amount as discount_amount"),
                DB::raw("{$factor} * detail.govt_fee as govt_fee"),
                DB::raw("{$factor} * detail.bank_service_charge as bank_service_charge"),
                DB::raw("{$factor} * detail.bank_service_charge_vat as bank_service_charge_vat"),
                DB::raw("{$factor} * detail.pf_amount as pf_amount"),
                'detail.application_id',
                'detail.ref_name',
                DB::raw("{$factor} * detail.user_commission as user_commission"),
                'detail.created_by',
                'detail.updated_by',
                'invoice.tran_date',
                'invoice.display_customer as reference_customer',
                'debtor.name as customer_name',
                'user.user_id as created_employee',
                DB::raw("{$factor} * detail.customer_commission as customer_commission"),
                DB::raw("{$factor} * detail.customer_commission2 as customer_commission2")
            )
            ->selectRaw('left(`detail`.`description`, 60) as `description`')
            ->selectRaw("{$factor} * {$unitPrice} * `detail`.`quantity` as `total_price`")
            ->selectRaw("{$factor} * `detail`.`unit_tax` * `detail`.`quantity` as `total_tax`")
            ->selectRaw("{$factor} * `detail`.`govt_fee` * `detail`.`quantity` as `total_govt_fee`")
            ->selectRaw('('
                .    $unitPrice
                . ' + `detail`.`govt_fee`'
                . ' + `detail`.`bank_service_charge`'
                . ' + `detail`.`bank_service_charge_vat`'
                . ' + `detail`.`unit_tax`'
                . ' - `detail`.`discount_amount`'
            .") * {$factor} * `detail`.`quantity` as `invoice_amount`")
            ->selectRaw(
                '('
                    .    $unitPrice
                    . ' - `detail`.`discount_amount`'
                    . ' - `detail`.`user_commission`'
                    . ' - `detail`.`customer_commission`'
                    . ' - `detail`.`customer_commission2`'
                .") * {$factor} * `detail`.`quantity` as `net_service_charge`"
            )
            ->selectRaw('`detail`.`discount_percent` * 100 as `discount_percent`')
            ->selectRaw('if(`detail`.`transaction_id` <> 0, concat("\'", `detail`.`transaction_id`), null) as `transaction_id`')
            ->selectRaw(
                "CASE"
                    . " WHEN `invoice`.`alloc` = 0 THEN 'Not Paid'"
                    . " WHEN `invoice`.`alloc` < {$total} THEN 'Partially Paid'"
                    . " ELSE 'Fully Paid'"
                ." END as `payment_status`"
            )
            ->from('0_debtor_trans_details as detail')
            ->leftJoin('0_stock_master as stock', 'detail.stock_id', 'stock.stock_id')
            ->leftJoin('0_stock_category as category', 'stock.category_id', 'category.category_id')
            ->leftJoin('0_users as user', 'user.id', 'detail.created_by')
            ->leftJoin('0_debtor_trans as invoice', function(JoinClause $join) {
                $join->on('invoice.type', 'detail.debtor_trans_type')
                    ->whereColumn('invoice.trans_no', 'detail.debtor_trans_no');
            })
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'invoice.debtor_no')
            ->leftJoin('0_customer_discount_items as discount', function(JoinClause $join) {
                $join->on('discount.item_id', 'stock.category_id')
                    ->whereColumn('discount.customer_id', 'invoice.debtor_no');
            })
            ->leftJoin('0_salesman as salesman', 'salesman.salesman_code', 'debtor.salesman_id')
            ->whereIn('detail.debtor_trans_type', [CustomerTransaction::INVOICE, CustomerTransaction::CREDIT])
            ->whereRaw("{$total} <> 0")
            ->where('detail.quantity', '>', 0);


        // Attach filters
        if (!empty($filters['invoice_no'])) {
            $builder->where('invoice.reference', $filters['invoice_no']);
        }

        if (!empty($filters['tran_date_from'])) {
            $builder->where('invoice.tran_date', '>=', Carbon::parse($filters['tran_date_from']));
        }

        if (!empty($filters['tran_date_to'])) {
            $builder->where('invoice.tran_date', '<=', Carbon::parse($filters['tran_date_to']));
        }

        if (!empty($filters['customer'])) {
            $builder->whereIn(
                'invoice.debtor_no',
                is_array($filters['customer']) ? $filters['customer'] : (array)$filters['customer']
            );
        }

        if (!empty($filters['employee'])) {
            $builder->where('user.id', $filters['employee']);
        }

        if (!empty($filters['sales_man_id'])) {
            $builder->where('salesman.salesman_code', $filters['sales_man_id']);
        }

        if (!empty($filters['display_customer'])) {
            $builder->where('invoice.display_customer', 'like', "%{$filters['display_customer']}%");
        }
        
        if (!empty($filters['payment_status'])) {
            switch ($filters['payment_status']) {
                case '1':
                    $builder->whereRaw("`invoice`.`alloc` >= {$total}");
                    break;
                case '2':
                    $builder->whereRaw("`invoice`.`alloc` = 0");
                    break;
                case '3':
                    $builder->whereRaw("`invoice`.`alloc` < {$total}");
                    $builder->where('invoice.alloc', '>', 0);
                    break;
            }
        }
        
        if (!empty($filters['category'])) {
            $builder->where('stock.category_id', $filters['category']);
        }
        
        if (!empty($filters['service'])) {
            $builder->where('detail.stock_id', $filters['service']);
        }

        if (!empty($filters['transaction_id'])) {
            $builder->where('detail.transaction_id', $filters['transaction_id']);
        }

        if (!empty($filters['ref_name'])) {
            $builder->where('detail.ref_name', $filters['ref_name']);
        }

        if (!empty($filters['invoice_type'])) {
            $builder->where('invoice.invoice_type', $filters['invoice_type']);
        }

        if (!empty($filters['transaction_status'])) {
            switch ($filters['transaction_status']) {
                case '1':
                    $builder->where('detail.transaction_id', '<>', '');
                    break;
                case '2':
                    $builder->where('detail.transaction_id', '');
                    break;
            }
        }

        $authUser = auth()->user();
        if (
            $authorizedOnly &&
            $authUser->doesntHavePermission(Permissions::SA_SERVICETRANSREP_ALL)
        ) {
            if ($authUser->hasPermission(Permissions::SA_SERVICETRANSREP_OWN)) {
                $builder->where('detail.created_by', $authUser->id);
            } else {
                // User Doesn't have permission to see any transaction
                $builder->where('detail.created_by', -1);
            }
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
        abort_unless($request->user()->hasAnyPermission(
            Permissions::SA_SERVICETRANSREP_OWN,
            Permissions::SA_SERVICETRANSREP_ALL
        ), 403);

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
        abort_unless($request->user()->hasAnyPermission(
            Permissions::SA_SERVICETRANSREP_OWN,
            Permissions::SA_SERVICETRANSREP_ALL
        ), 403);

        $ext = $request->input('to');
        $title = 'Service Transactions';

        // Its stated in wkhtmltopd docs that if user input is
        // not sanitized when generating report, it could lead
        // to complete server takedown
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'customer' => 'nullable|array',
            'customer.*' => 'integer',
            'category' => 'nullable|integer',
            'tran_date_from' => 'required|date_format:'.dateformat(),
            'tran_date_to' => 'required|date_format:'.dateformat(),
            'employee' => 'nullable|integer',
        ]);

        $meta = [
            "Period" => (
                  ($inputs['tran_date_from'] ?? 'Begning')
                . ' to '
                . ($inputs['tran_date_to'] ?? date(dateformat()))
            )
        ];

        if (!empty($inputs['employee'])) {
            $meta['Employee'] = User::find($inputs['employee'])->name ?? null;
        }

        if (!empty($inputs['customer'])) {
            $customers = Customer::select('name')->whereIn('debtor_no', $inputs['customer'])->get()->implode('name', ',');
            $meta['Customers'] = $customers;
        }

        if (!empty($inputs['category'])) {
            $meta['Category'] = StockCategory::find($inputs['category'])->description ?? null;
        }

        $queryBuilder = $this->getBuilder($request->all());

        $textColumns = [ // Set Column to be displayed
            'Invoice No' => 'invoice_no',
            'Date' => 'tran_date',
            'Customer' => 'customer_name',
            'Display Customer' => 'reference_customer',
            'Sales Man' => 'salesman_name',
            'Service' => 'service_eng_name',
            'Category' => 'category_name',
            'Transaction ID' => 'transaction_id',
            'Ref Name' => 'ref_name',
            'Employee' => 'created_employee',
            'Payment Status' => 'payment_status',
            'Card Type' => 'invoice_type',
        ];

        $amountColumns = [
            'Unit Price' => 'unit_price',
            'Quantity' => 'quantity',
            'Total Service Charge' => 'total_price',
            'Unit Tax' => 'unit_tax',
            'Total Tax' => 'total_tax',
            'Discount Amount' => 'discount_amount',
            'Govt Fee' => 'govt_fee',
            'Total Govt Fee' => 'total_govt_fee',
            'Bank Charge' => 'bank_service_charge',
            'Vat (Bank Charge)' => 'bank_service_charge_vat',
            'PF Amount' => 'pf_amount',
            'Customer Commission' => 'customer_commission',
            'Salesman Commission' => 'customer_commission2',
            'Employee Commission' => 'user_commission',
            'Net Service Charge' => 'net_service_charge',
            'Invoice Amount' => 'invoice_amount'
        ];

        $generator = app($ext == 'xlsx' 
            ? ExcelReport::class
            : PdfReport::class
        )->of($title, $meta, $queryBuilder, array_merge($textColumns, $amountColumns))
        ->setPaper('a2')
        ->setOrientation('landscape');

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
                        return Str::limit($result->{$key}, '15');
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