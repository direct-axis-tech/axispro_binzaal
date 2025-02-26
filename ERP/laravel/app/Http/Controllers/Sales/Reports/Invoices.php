<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;
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

class Invoices extends Controller {

    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_INVOICEREP), 403);
        $inputs = [
            'invoice_no' => '',
            'customer' => [],
            'tran_date_from' => date(dateformat()),
            'tran_date_to' => date(dateformat()),
            'employee' => '',
            'payment_status' => '',
        ];
        $customers = Customer::select('debtor_no', 'name')->get();
        $users = User::select('id', 'user_id')->get();

        return view(
            'reports.managementReport.invoices',
            compact('customers', 'users', 'inputs')
        );
    }

    /**
     * Retrive today's invoices
     */
    public function getTodaysInvoices(Request $request)
    {

        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_TODAYS_INV), 403);

        $report = $this->todaysInvoices($request->all())
            ->each(function($invoice) {
                $invoice->append('print_link')->append('update_transaction_id_link');
            });

        return ["data" => $report];
    }

    /**
     * Retrive today's invoices
     *
     * @param $filters
     * @return EloquentCollection
     */
    public function todaysInvoices($filters = [])
    {
        $user = $filters['user'] ?? request()->user();
        $builder = CustomerTransaction::from('0_debtor_trans as trans')
            ->leftJoin('0_debtor_trans_details as detail', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'trans.type')
                    ->whereColumn('detail.debtor_trans_no', 'trans.trans_no');
            })
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'trans.debtor_no')
            ->leftJoin('0_users as user', 'user.id', 'detail.created_by')
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'voided.type')
                    ->whereColumn('detail.debtor_trans_no', 'voided.id');
            })
            ->select(
                'trans.reference as invoice_no',
                DB::raw('ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`, 2) as `invoice_amount`'),
                'trans.order_',
                'trans.trans_no',
                'detail.created_by',
                'trans.token_number',
                'trans.payment_flag',
                'trans.display_customer',
                'debtor.name as customer_name',
                'user.user_id as created_employee',
                'trans.tran_date as transaction_date',
                'trans.dimension_id',
                'trans.payment_method',
                'detail.transaction_id',
    
                DB::raw(
                    '('
                       .'CASE'
                          . ' WHEN COUNT(if(`detail`.`transaction_id`="",NULL,1))=0 THEN "Not Completed"'
                          . ' WHEN COUNT(if(`detail`.`transaction_id`="",NULL,1))!=0 AND COUNT(if(`detail`.`transaction_id`="",NULL,1)) < COUNT(*) THEN "Partially Completed"'
                          . ' WHEN COUNT(if(`detail`.`transaction_id`="",NULL,1))!=0 AND COUNT(if(`detail`.`transaction_id`="",NULL,1)) = COUNT(*) THEN "Completed"'
                        . 'END'
                    .') AS `transaction_status`',
                ),

                DB::raw(
                    '('
                        .'CASE  '
                            . ' WHEN ROUND(`trans`.`alloc`) >= ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`) THEN "Fully Paid"'
                            . ' WHEN `trans`.`alloc` = 0 THEN "Not Paid"'
                            . ' WHEN ROUND(`trans`.`alloc`) < ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`) THEN "Partially Paid"'
                        . ' END'
                    .') as `payment_status`'
                ),
                'trans.id',
                'trans.type',
                'trans.debtor_no'
            )
            ->where('trans.type', CustomerTransaction::INVOICE)
            ->whereNull('voided.id')
            ->where('trans.tran_date', date(DB_DATE_FORMAT))
            ->groupBy('trans.id', 'user.id')
            ->orderBy('trans.trans_no', 'desc');

        if ($user->doesntHavePermission(Permissions::SA_MANAGEINVALL)) {
            $builder->whereIn('trans.dimension_id', $user->authorized_dimensions);
            if ($user->doesntHavePermission(Permissions::SA_MANAGEINVDEP)) {
                $builder->where('user.id', $user->id);
            }
        }

        if (!empty($filters['dim_id'])) {
            $builder->where('trans.dimension_id', $filters['dim_id']);
        }

        if (!empty($filters['show_only_pending'])) {
            $builder->whereRaw('ROUND(`trans`.`alloc`) < ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`)');
        }

        return $builder->get();
    }
    /**
     * Retrive today's invoices
     */
    public function getTodaysReceipts(Request $request)
    {
        //dd('hi');
        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_TODAYS_REC), 403);

        $report = $this->todaysReceipts($request->all())
            ->each(function($invoice) {
                $invoice->append('print_link')->append('update_transaction_id_link');
            });


        return ["data" => $report];
    }
    public function todaysReceipts($filters = [])
    {
        $user = $filters['user'] ?? request()->user();
        $builder = CustomerTransaction::from('0_debtor_trans as trans')
            ->leftJoin('0_debtor_trans_details as detail', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'trans.type')
                    ->whereColumn('detail.debtor_trans_no', 'trans.trans_no');
            })
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'trans.debtor_no')
            ->leftJoin('0_users as user', 'user.id', 'trans.created_by')
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'voided.type')
                    ->whereColumn('detail.debtor_trans_no', 'voided.id');
            })
            ->select(
                'trans.reference as invoice_no',
                DB::raw('ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`, 2) as `invoice_amount`'),
                'trans.order_',
                'trans.trans_no',
                'detail.created_by',
                'trans.payment_flag',
                'trans.display_customer',
                'debtor.name as customer_name',
                'user.user_id as created_employee',
                'trans.tran_date as transaction_date',
                'trans.dimension_id',
                DB::raw(
                    '('
                    .'CASE'
                    . ' WHEN ROUND(`trans`.`alloc`) >= ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`) THEN "Fully Paid"'
                    . ' WHEN `trans`.`alloc` = 0 THEN "Not Paid"'
                    . ' WHEN ROUND(`trans`.`alloc`) < ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`) THEN "Partially Paid"'
                    . ' END'
                    .') as `payment_status`'
                ),
                'trans.id',
                'trans.type',
                'trans.debtor_no'
            )
            ->where('trans.type', CustomerTransaction::PAYMENT)
            ->whereNull('voided.id')
            ->where('trans.tran_date', date(DB_DATE_FORMAT))
            ->groupBy('trans.id', 'user.id')
            ->orderBy('trans.trans_no', 'desc');


        if ($user->doesntHavePermission(Permissions::SA_MANAGEINVALL)) {
            $builder->whereIn('trans.dimension_id', $user->authorized_dimensions);
            if ($user->doesntHavePermission(Permissions::SA_MANAGEINVDEP)) {
                $builder->where('user.id', $user->id);
            }
        }

        if (!empty($filters['dim_id'])) {
            $builder->where('trans.dimension_id', $filters['dim_id']);
        }

        if (!empty($filters['show_only_pending'])) {
            $builder->whereRaw('ROUND(`trans`.`alloc`) < ROUND(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`)');
        }
        //dd($builder->toSql());

        return $builder->get();
    }

    /**
     * Returns the query builder instance
     *
     * @param array $filters
     * @return Builder
     */
    public function getBuilder($filters = [])
    {
        $total = "`invoice`.`ov_amount` + `invoice`.`ov_gst` + `invoice`.`ov_freight` + `invoice`.`ov_freight_tax` + `invoice`.`ov_discount`";

        $builder = DB::query()
            ->select(
                'invoice.reference as invoice_no',
                'invoice.tran_date',
                'debtor.name as customer_name',
                'invoice.display_customer as reference_customer',
                'user.user_id as created_employee',
                'invoice.alloc'
            )
            ->selectRaw(
                "CASE"
                    . " WHEN `invoice`.`alloc` = 0 THEN 'Not Paid'"
                    . " WHEN `invoice`.`alloc` < {$total} THEN 'Partially Paid'"
                    . " ELSE 'Fully Paid'"
                ." END as `payment_status`"
            )
            ->selectRaw("{$total} as invoice_amount")
            ->from('0_debtor_trans as invoice')
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'invoice.debtor_no')
            ->leftJoin('0_users as user', 'invoice.created_by', 'user.id')
            ->where('invoice.type', CustomerTransaction::INVOICE)
            ->whereRaw("{$total} <> 0")
            ->orderBy('invoice.reference', 'desc');

        if (!empty($filters['employee'])) {
            $builder->where('user.id', $filters['employee']);
        }

        if (!empty($filters['customer'])) {
            $builder->whereIn(
                'invoice.debtor_no',
                is_array($filters['customer']) ? $filters['customer'] : (array)$filters['customer']
            );
        }

        if (!empty($filters['invoice_no'])) {
            $builder->where('invoice.reference', $filters['invoice_no']);
        }

        if (!empty($filters['tran_date_from'])) {
            $builder->where('invoice.tran_date', '>=', Carbon::parse($filters['tran_date_from']));
        }

        if (!empty($filters['tran_date_to'])) {
            $builder->where('invoice.tran_date', '<=', Carbon::parse($filters['tran_date_to']));
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

        return $builder;
    }

    /**
     * Handles the dataTable api requests
     *
     * @param Request $request
     */
    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_INVOICEREP), 403);

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
        abort_unless($request->user()->hasPermission(Permissions::SA_INVOICEREP), 403);

        $ext = $request->input('to');
        $title = 'Invoices';

        // Its stated in wkhtmltopd docs that if user input is
        // not sanitized when generating report, it could lead
        // to complete server takedown
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'customer' => 'nullable|array',
            'customer.*' => 'integer',
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

        $queryBuilder = $this->getBuilder($request->all());

        // Set Column to be displayed
        $textColumns = [
            'Invoice No' => 'invoice_no',
            'Date' => 'tran_date',
            'Customer' => 'customer_name',
            'Display Customer' => 'reference_customer',
            'Employee' => 'created_employee',
            'Payment Status' => 'payment_status',
        ];

        $amountColumns = [
            'Invoice Amount' => 'invoice_amount',
            'Allocated Amount' => 'alloc'
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
            "redirect_to" => url(route("file.download", ['type' => 'invoice_list', 'file' => basename($file)]))
        ];
    }
}
