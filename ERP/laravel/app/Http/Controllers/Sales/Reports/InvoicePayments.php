<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
use App\Models\System\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Yajra\DataTables\QueryDataTable;

class InvoicePayments extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index()
    {
        $inputs = [
            'invoice_no' => '',
            'receipt_no' => '',
            'customer' => [],
            'bank' => '',
            'tran_date_from' => date(dateformat()),
            'tran_date_to' => date(dateformat()),
            'invoice_date_from' => '',
            'invoice_date_to' => '',
            'user' => '',
            'payment_method' => '',
            'payment_invoice_date_relationship' => '',
        ];
        $customers = Customer::select('debtor_no', 'name')->get();
        $users = User::select('id', 'user_id')->get();
        $banks = BankAccount::select('id', 'account_code', 'bank_account_name')->get();

        return view('reports.managementReport.invoicePayments',
            compact('customers', 'banks', 'users', 'inputs')
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
        $total = function($key) {
            return "`{$key}`.`ov_amount` + `{$key}`.`ov_gst` + `{$key}`.`ov_freight` + `{$key}`.`ov_freight_tax` + `{$key}`.`ov_discount`";
        };

        $totalIsNotZero = function($key) use ($total) {
            return $total($key) . " <> 0";
        };

        $builder = DB::query()
            ->select(
                'payment.tran_date',
                'payment.reference as receipt_no',
                'invoice.tran_date as invoice_date',
                'invoice.reference AS invoice_number',
            )
            ->selectRaw('round(`alloc`.`amt`, 2) as alloc_amt')
            ->selectRaw('round('.$total('invoice').', 2) as invoice_amt')
            ->selectRaw('round(ifnull(`payment`.`ov_discount`, 0), 2) as reward_amount')
            ->selectRaw('round('.$total('payment').', 2) as payment_amt')
            ->addSelect(
                'bank_account.bank_account_name',
                'debtor.name as customer_name',
                'user.user_id',
                'payment.payment_method'
            )
            ->from('0_debtor_trans as payment')
            ->leftJoin('0_bank_trans as bank_trans', function(JoinClause $join) {
                $join->on('bank_trans.type', 'payment.type')
                    ->whereColumn('bank_trans.trans_no', 'payment.trans_no');
            })
            ->leftJoin('0_bank_accounts as bank_account', 'bank_account.id', 'bank_trans.bank_act')
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'payment.debtor_no')
            ->leftJoin('0_users as user', 'payment.created_by', 'user.id')
            ->leftJoin('0_cust_allocations as alloc', function(JoinClause $join) {
                $join->on('alloc.person_id', 'payment.debtor_no')
                    ->whereColumn('alloc.trans_type_from', 'payment.type')
                    ->whereColumn('alloc.trans_no_from', 'payment.trans_no');
            })
            ->leftJoin('0_debtor_trans as invoice', function(JoinClause $join) {
                $join->on('alloc.trans_type_to', 'invoice.type')
                    ->whereColumn('alloc.trans_no_to', 'invoice.trans_no')
                    ->whereColumn('alloc.person_id', 'invoice.debtor_no');
            })
            ->where('payment.type', CustomerTransaction::PAYMENT)
            ->whereRaw($totalIsNotZero('payment'))
            ->where('invoice.type', CustomerTransaction::INVOICE)
            ->whereRaw($totalIsNotZero('invoice'));

        if (!empty($filters['user'])) {
            $builder->where('user.id', $filters['user']);
        }

        if (!empty($filters['bank'])) {
            $builder->where('bank_account.id', $filters['bank']);
        }

        if (!empty($filters['customer'])) {
            $builder->where('payment.debtor_no', $filters['customer']);
        }

        if (!empty($filters['receipt_no'])) {
            $builder->where('payment.reference', $filters['receipt_no']);
        }

        if (!empty($filters['payment_method'])) {
            $builder->where('payment.payment_method', $filters['payment_method']);
        }

        if (!empty($filters['tran_date_from'])) {
            $builder->where('payment.tran_date', '>=', Carbon::parse($filters['tran_date_from']));
        }

        if (!empty($filters['tran_date_to'])) {
            $builder->where('payment.tran_date', '<=', Carbon::parse($filters['tran_date_to']));
        }

        if (!empty($filters['invoice_date_from'])) {
            $builder->where('invoice.tran_date', '>=', Carbon::parse($filters['invoice_date_from']));
        }

        if (!empty($filters['invoice_date_to'])) {
            $builder->where('invoice.tran_date', '<=', Carbon::parse($filters['invoice_date_to']));
        }

        if (!empty($filters['invoice_no'])) {
            $builder->havingRaw('count(`invoice`.`reference`) > 0')
                ->whereIn('invoice.reference', array_map('trim', explode(',', $filters['invoice_no'])));
        }

        if (!empty($filters['payment_invoice_date_relationship'])) {
            switch ($filters['payment_invoice_date_relationship']) {
                case 'payment_before_or_after_invoice':
                    $builder->whereColumn('payment.tran_date', '<>', 'invoice.tran_date');
                    break;
                case 'payment_after_invoice':
                    $builder->whereColumn('payment.tran_date', '>', 'invoice.tran_date');
                    break;
                case 'payment_before_invoice':
                    $builder->whereColumn('payment.tran_date', '<', 'invoice.tran_date');
                    break;
                case 'payment_on_invoice_date':
                    $builder->whereColumn('payment.tran_date', '=', 'invoice.tran_date');
                    break;
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
        $ext = $request->input('to');
        $title = 'Invoice Payments';

        // Its stated in wkhtmltopd docs that if user input is
        // not sanitized when generating report, it could lead
        // to complete server takedown
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'customer' => 'nullable|array',
            'customer.*' => 'integer',
            'tran_date_from' => 'required|date_format:'.dateformat(),
            'tran_date_to' => 'required|date_format:'.dateformat(),
            'invoice_date_from' => 'nullable|date_format:'.dateformat(),
            'invoice_date_to' => 'nullable|date_format:'.dateformat(),
            'user' => 'nullable|integer',
            'bank' => 'nullable|integer',
        ]);

        $meta = [
            "Period" => (
                  ($inputs['tran_date_from'] ?? 'Begning')
                . ' to '
                . ($inputs['tran_date_to'] ?? date(dateformat()))
            ),
            "Invoice Period" => (
                    ($inputs['invoice_date_from'] ?? 'Begning')
                . ' to '
                . ($inputs['invoice_date_to'] ?? date(dateformat()))
            )
        ];

        if (!empty($inputs['user'])) {
            $meta['Employee'] = User::find($inputs['user'])->name ?? null;
        }

        if (!empty($inputs['customer'])) {
            $customers = Customer::select('name')->whereIn('debtor_no', $inputs['customer'])->get()->implode('name', ',');
            $meta['Customers'] = $customers;
        }

        if (!empty($inputs['bank'])) {
            $meta['Bank Account'] = BankAccount::select('account_code', 'bank_account_name')
                ->where('id', $inputs['bank'])
                ->first()
                ->formatted_name ?? null;
        }

        $queryBuilder = $this->getBuilder($request->all());

        $textColumns = [ // Set Column to be displayed
            'Receipt No' => 'receipt_no',
            'Date' => 'tran_date',
            'Customer' => 'customer_name',
            'Invoice' => 'invoice_number',
            'Inv Date' => 'invoice_date',
            'Collected Bank' => 'bank_account_name',
            'User' => 'user_id',
            'Payment Method' => 'payment_method'
        ];

        $amountColumns = [
            'Invoice Amt' => 'invoice_amt',
            'Alloc' => 'alloc_amt',
            'Payment Amt' => 'payment_amt',
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