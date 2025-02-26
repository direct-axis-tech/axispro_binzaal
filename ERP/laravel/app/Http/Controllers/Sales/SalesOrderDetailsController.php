<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesMan;
use App\Models\Sales\SalesOrder;
use App\Models\System\User;
use App\Permissions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;
use Yajra\DataTables\QueryDataTable;

class SalesOrderDetailsController extends Controller {
    /**
     * Shows the index for the sales order details report
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        abort_unless(
            authUser()->hasAnyPermission(
                Permissions::SA_SALES_LINE_VIEW,
                Permissions::SA_SALES_LINE_VIEW_OWN,
                Permissions::SA_SALES_LINE_VIEW_DEP,
            ),
            403
        );

        $salesMans = SalesMan::pluck('salesman_name', 'salesman_code')->toArray();
        $categories = StockCategory::pluck('description', 'category_id')->toArray();
        $stockItems = StockItem::pluck('description', 'stock_id')->toArray();
        $statuses = [
            'Pending',
            'Completed',
            'Work in Progress',
        ];
        $statuses = array_combine($statuses, $statuses);
        $invStatuses = [
            'Invoiced',
            'Not Invoiced'
        ];
        $invStatuses = array_combine($invStatuses, $invStatuses);
        $assignees = User::authorized(
                [
                    'ALL' => authUser()->hasPermission(Permissions::SA_SALES_LINE_VIEW),
                    'OWN' => authUser()->hasPermission(Permissions::SA_SALES_LINE_VIEW_OWN),
                    'DEP' => authUser()->hasPermission(Permissions::SA_SALES_LINE_VIEW_DEP)
                ]
            )
            ->select('id', 'user_id', 'real_name')
            ->get()
            ->map->append('formatted_name')
            ->pluck('formatted_name', 'id')
            ->toArray();

        $inputs = [
            'line_reference' => '',
            'order_reference' => '',
            'debtor_no' => '',
            'salesman_id' => '',
            'assignee_id' => '',
            'category_id' => '',
            'stock_id' => '',
            'transaction_status' => '',
            'invoice_status' => '',
            'order_date_from' => Today(),
            'order_date_till' => Today(),
            'line_narration' => ''
        ];

        $inputs = array_merge(
            $inputs,
            $request->validate($this->validationRules())
        );

        return view('sales.order.details.index', compact(
            'salesMans',
            'assignees',
            'categories',
            'stockItems',
            'statuses',
            'invStatuses',
            'inputs',
        ));
    }
    
    /**
     * Returns the validation rules for the main inquiry
     *
     * @return array
     */
    public function validationRules()
    {
        return [
            'line_reference' => 'nullable|regex:/[A-Z0-9\/]*/',
            'order_reference' => 'nullable|regex:/^[A-Z0-9\/]*$/',
            'line_narration' => 'nullable',
            'debtor_no' => 'nullable|integer',
            'salesman_id' => 'nullable|integer',
            'assignee_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'stock_id' => 'nullable|regex:/^[A-Z0-9_\-]*$/',
            'transaction_status' => 'nullable|in:Completed,Work in Progress,Pending,Partially Completed',
            'invoice_status' => 'nullable|in:Invoiced,Not Invoiced',
            'order_date_from' => 'nullable|date_format:'. dateformat(),
            'order_date_till' => 'nullable|date_format:'. dateformat(),
        ];
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function dataTable(Request $request)
    {
        abort_unless(authUser()->hasAnyPermission(
            Permissions::SA_SALES_LINE_VIEW,
            Permissions::SA_SALES_LINE_VIEW_OWN,
            Permissions::SA_SALES_LINE_VIEW_DEP
        ), 403);

        $inputs = $request->validate($this->validationRules());
        $inputs['auth'] = true;
        $dataTable = (new QueryDataTable(DB::query()->fromSub($this->getBuilder($inputs), 't')))
            ->addColumn('total_expense', function ($order) {
                if ($order->qty_not_sent > 0) {
                    return 0;
                }

                return $this->getExpenseFromOrderLine($order);
            })
            ->addColumn('profit', function($order) {
                if ($order->qty_not_sent > 0 || $order->qty_credited > 0) {
                    return 0;
                }

                return $order->total - $this->getExpenseFromOrderLine($order);
            })
            ->addColumn('supp_references', function($order) {
                $references = $this->getExpenses($order)
                    ->map(function ($inv) {
                        return [
                            'type' => $inv->type,
                            'trans_no' => $inv->trans_no,
                            'reference' => $inv->reference,
                        ];
                    })
                    ->toArray();

                return $references;
            })
            ->addColumn('inv_references', function ($order) {
                if ($order->inv_status == 'Not Invoiced') {
                    return [];
                }

                $references = $this->getInvoices($order)
                    ->map(function ($inv) {
                        return [
                            'type' => $inv->type,
                            'trans_no' => $inv->trans_no,
                            'reference' => $inv->reference,
                        ];
                    })
                    ->unique('reference')
                    ->toArray();

                return $references;
            })
            ->addColumn('_isCompletable', function($order) {
                return (
                    authUser()->hasPermission(Permissions::SA_SALESDELIVERY)
                    && $order->qty_not_sent > 0
                    && $order->qty_credited == 0
                );
            })
            ->addColumn('_isExpenseAddable', function($order) {
                return (
                    authUser()->hasPermission(Permissions::SA_SUPPLIERINVOICE)
                    && $order->costing_method == COSTING_METHOD_EXPENSE
                    && $order->qty_not_sent > 0
                    && $order->qty_expensed == 0
                    && $order->qty_credited == 0
                );
            })
            ->addColumn('_canSeeExpenses', function($order) {
                return (
                    authUser()->hasPermission(Permissions::SA_SUPPTRANSVIEW)
                    && $order->qty_expensed > 0
                );
            })
            ->rawColumns(['supp_references', 'inv_references']);

        return $dataTable->toJson();
    }

    /**
     * Get the expense from the order line
     *
     * @param  stdClass $line
     * @return float
     */
    public function getExpenseFromOrderLine($line)
    {
        if ($line->costing_method == COSTING_METHOD_EXPENSE) {
            return $this->getExpenses($line)->sum('total_tax_free_price') ?: 0;
        }

        else if ($line->mb_flag == STOCK_TYPE_SERVICE) {
            return $line->expense;
        }

        else if ($line->mb_flag != STOCK_TYPE_FIXED_ASSET) {
            return $line->qty_not_sent > 0
                ? 0
                : ($this->getCompletions($line)->sum('total_standard_cost') ?: 0);
        }

        return 0;
    }

    /**
     * Returns the dataTable api for this resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function expensesDataTable(Request $request)
    {
        abort_unless(
            authUser()->hasPermission(Permissions::SA_SUPPTRANSVIEW),
            403
        );

        $inputs = $request->validate([
            'line_reference' => 'required|regex:/^[A-Z0-9\/]*/',
        ]);

        $dataTable = (new QueryDataTable(DB::query()->fromSub($this->expenseQuery($inputs), 't')));

        return $dataTable->toJson();
    }

    /**
     * Get expenses against the row if any
     *
     * @param stdClass $row
     * @return stdClass[]|\Illuminate\Support\Collection
     */
    private function getExpenses($row)
    {
        return Cache::store('array')->rememberForever(
            "order.details.{$row->line_reference}.expenses",
            function () use ($row) {
                return $this->expenseQuery(['line_reference' => $row->line_reference])->get();
            }
        );
    }

    /**
     * Get the completions against the row if any
     *
     * @param stdClass $row
     * @return stdClass[]|\Illuminate\Support\Collection
     */
    private function getCompletions($row)
    {
        return Cache::store('array')->rememberForever(
            "order.details.{$row->line_reference}.completions",
            function () use ($row) {
                return $this->completionQuery(['line_reference' => $row->line_reference])->get();
            }
        );
    }
    
    /**
     * Get the invoices against the row if any
     *
     * @param stdClass $row
     * @return stdClass[]|\Illuminate\Support\Collection
     */
    private function getInvoices($row)
    {
        return Cache::store('array')->rememberForever(
            "order.details.{$row->line_reference}.invoices",
            function () use ($row) {
                return $this->invoicesQuery(['line_reference' => $row->line_reference])->get();
            }
        );
    }

    /**
     * Returns the builder instance for querying sales order details
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function getBuilder($filters = [])
    {
        $query = DB::table('0_sales_order_details as sod')
            ->leftJoin('0_sales_orders as so', function (JoinClause $join) {
                $join->on('so.order_no', 'sod.order_no')
                    ->on('so.trans_type', 'sod.trans_type');
            })
            ->leftJoin('0_debtors_master as dm', 'dm.debtor_no', 'so.debtor_no')
            ->leftJoin('0_stock_master as sm', 'sm.stock_id', 'sod.stk_code')
            ->leftJoin('0_stock_category as sc', 'sm.category_id', 'sc.category_id')
            ->leftJoin('0_salesman as salesman', 'salesman.salesman_code', 'dm.salesman_id')
            ->leftJoin('0_debtor_trans_details as inv', function ($join) {
                $join->on('inv.line_reference', 'sod.line_reference')
                    ->where('inv.debtor_trans_type', CustomerTransaction::INVOICE)
                    ->where('inv.quantity', '<>', '0');
            })
            ->leftJoin('0_users as asgn', function ($join) {
                $join->whereRaw('ifnull(sod.assignee_id, sod.created_by) = asgn.id');
            })
            ->where('sod.quantity', '<>', 0)
            ->where('so.reference', '<>', 'auto')
            ->where('sod.trans_type', SalesOrder::ORDER)
            ->groupBy('sod.id');

        $query->addSelect(
            'sod.*',
            'inv.qty_done as qty_credited',
            'sm.costing_method',
            'sm.mb_flag',
            'sc.dflt_pending_cogs_act as deferred_cogs_account',
            'sod.stk_code as stock_id',
            'so.dimension_id',
            'so.ord_date as order_date',
            'so.reference as order_reference',
            'sod.ref_name as line_narration',
            'sm.description as stock_name',
            DB::raw("concat_ws(' - ', asgn.user_id, nullif(asgn.real_name, '')) as formatted_assignee_name"),
            DB::raw("concat_ws(' - ', sod.stk_code, sm.description) as formatted_stock_name"),
            'sc.description as category_name',
            'dm.debtor_ref as customer_ref',
            'dm.name as customer_name',
            DB::raw("concat_ws(' - ', dm.debtor_ref, dm.name) as formatted_customer_name"),
            'salesman.salesman_name',
            'so.transacted_at',
            DB::raw(
                "("
                    . "("
                        . "+ sod.unit_price"
                        . "+ sod.returnable_amt"
                        . "+ sod.extra_srv_chg"
                        . "- if(so._tax_included, sod._unit_tax, 0)"
                    . ") * sod.quantity"
                .") as taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ sod.govt_fee"
                        . "+ sod.bank_service_charge"
                        . "+ sod.bank_service_charge_vat"
                        . "- sod.returnable_amt"
                    . ") * sod.quantity"
                .") as non_taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ sod.govt_fee"
                        . "+ sod.bank_service_charge"
                        . "+ sod.bank_service_charge_vat"
                        . "+ sod.pf_amount"
                        . "- sod.returnable_amt"
                        . "- sod.receivable_commission_amount"
                    . ") * sod.quantity"
                .") as expense"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ sod.unit_price"
                        . "+ sod.govt_fee"
                        . "+ sod.bank_service_charge"
                        . "+ sod.bank_service_charge_vat"
                        . "- if(so._tax_included, sod._unit_tax, 0)"
                    . ") * sod.quantity"
                .") as total"
            ),
            DB::raw(
                "("
                    . " CASE"
                        . " WHEN inv.qty_done > 0 THEN 'Cancelled'"
                        . " WHEN sod.qty_not_sent = 0 THEN 'Completed'"
                        . " WHEN sod.qty_expensed > 0 THEN 'Work in Progress'"
                        . " WHEN sod.qty_sent = 0 THEN 'Pending'"
                        . " WHEN sod.qty_sent > 0 and sod.qty_not_sent != 0 THEN 'Partially Completed'"
                    . " END"
                .") as status"
            ),
            DB::raw("if(count(inv.id) > 0, 'Invoiced', 'Not Invoiced') as inv_status")
        );

        if (!empty($filters['line_reference'])) {
            $query->where('sod.line_reference', $filters['line_reference']);
        }
        
        if (!empty($filters['order_reference'])) {
            $query->where('so.reference', $filters['order_reference']);
        }

        if(empty($filters['line_reference']) && empty($filters['order_reference']))
        {
            if (!empty($filters['debtor_no'])) {
                $query->where('so.debtor_no', $filters['debtor_no']);
            }

            if (!empty($filters['salesman_id'])) {
                $query->where('dm.salesman_id', $filters['salesman_id']);
            }
            
            if (!empty($filters['order_date_from'])) {
                $query->where('so.ord_date', '>=', date2sql($filters['order_date_from']));
            }
            
            if (!empty($filters['order_date_till'])) {
                $query->where('so.ord_date', '<=', date2sql($filters['order_date_till']));
            }
        
        }

        if(empty($filters['line_reference'])) {

            if (!empty($filters['category_id'])) {
                $query->where('sc.category_id', $filters['category_id']);
            }
            
            if (!empty($filters['stock_id'])) {
                $query->where('sod.stk_code', $filters['stock_id']);
            }

            if (!empty($filters['line_narration'])) {
                $query->where('sod.ref_name', 'like', '%'.$filters['line_narration'].'%');
            }

            if (!empty($filters['transaction_status'])) {
                switch ($filters['transaction_status']) {
                    case 'Completed':
                        $query->where('sod.qty_not_sent', 0);
                        break;
                    case 'Partially Completed':
                        $query->whereRaw('(sod.qty_sent > 0 and sod.qty_not_sent != 0)');
                        break;
                    case 'Work in Progress':
                        $query->whereRaw('(sod.qty_expensed > 0 and sod.qty_sent = 0)');
                        break;
                    case 'Pending':
                        $query->whereRaw('(sod.qty_sent = 0 and sod.qty_expensed = 0)');
                        break;
                }
            }
        
            if (!empty($filters['invoice_status'])) {
                switch ($filters['invoice_status']) {
                    case 'Invoiced':
                    case 'Not Invoiced':
                        $query->having('inv_status', $filters['invoice_status']);
                        break;
                }
            }

            if (!empty($filters['assignee_id'])) {
                $query->whereRaw('ifnull(sod.assignee_id, sod.created_by) = ?', [$filters['assignee_id']]);
            }
        }
        
        if (!empty($filters['auth'])) {
            $authUser = authUser();

            $canAccess = [
                'ALL' => $authUser->hasPermission(Permissions::SA_SALES_LINE_VIEW),
                'OWN' => $authUser->hasPermission(Permissions::SA_SALES_LINE_VIEW_OWN),
                'DEP' => $authUser->hasPermission(Permissions::SA_SALES_LINE_VIEW_DEP),
            ];

            if (!$canAccess['ALL']) {
                $query->whereRaw(
                    'ifnull(sod.assignee_id, sod.created_by) in (select id from 0_users where dflt_dimension_id = ?)',
                    [$authUser->dflt_dimension_id]
                );

                if (!$canAccess['DEP']) {
                    $query->whereRaw(
                        'ifnull(sod.assignee_id, sod.created_by) = ?',
                        [$authUser->id, $authUser->id]
                    );
                }
            }
        }

        return $query;
    }

    /**
     * Returns the builder instance for querying expense
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function expenseQuery($filters = [])
    {
        $query = DB::table('0_supp_invoice_items as inv_item')
            ->leftJoin('0_supp_trans as inv', function (JoinClause $join) {
                $join->on('inv_item.supp_trans_type', 'inv.type')
                    ->whereColumn('inv_item.supp_trans_no', 'inv.trans_no');
            })
            ->leftJoin('0_suppliers as supp', 'supp.supplier_id', 'inv.supplier_id')
            ->leftJoin('0_stock_master as sm', 'sm.stock_id', 'inv_item.stock_id')
            ->leftJoin('0_stock_category as sc', 'sc.category_id', 'sm.category_id')
            ->where('inv_item.quantity', '<>', 0)
            ->select(
                "inv.type",
                "inv.trans_no",
                "inv.reference",
                "inv.supp_reference",
                "inv.tran_date",
                "inv_item.stock_id",
                DB::raw("concat_ws(' - ', supp.supp_ref, supp.supp_name) as formatted_supplier_name"),
                "sm.mb_flag",
                "inv_item.description as stock_description",
                DB::raw(
                    "("
                        . " + inv_item.unit_price"
                        . " - if(inv.tax_included, inv_item.unit_tax, 0)"
                    . ") as tax_free_unit_price"
                ),
                "inv_item.quantity",
                DB::raw(
                    "("
                        . "("
                            . " + inv_item.unit_price"
                            . " - if(inv.tax_included, inv_item.unit_tax, 0)"
                        . ") * inv_item.quantity"
                    .") as total_tax_free_price"
                )
            );

        if (!empty($filters['line_reference'])) {
            $query->where('inv_item.so_line_reference', $filters['line_reference']);
        }

        return $query;
    }

    /**
     * Returns the builder instance for querying completions
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function completionQuery($filters = [])
    {
        $query = DB::table('0_debtor_trans_details as dtd')
            ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                $join->on('dt.type', 'dtd.debtor_trans_type')
                    ->whereColumn('dt.trans_no', 'dtd.debtor_trans_no');
            })
            ->where('dtd.quantity', '<>', 0)
            ->where('dtd.debtor_trans_type', CustomerTransaction::DELIVERY);

        $query->select(
            'dtd.*',
            'dt.type',
            'dt.trans_no',
            'dt.tran_date',
            'dt.reference',
            DB::raw(
                "("
                    . "("
                        . "+ dtd.unit_price"
                        . "+ dtd.returnable_amt"
                        . "+ dtd.extra_srv_chg"
                        . "- if(dt.tax_included, dtd.unit_tax, 0)"
                    . ") * dtd.quantity"
                .") as taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ dtd.govt_fee"
                        . "+ dtd.bank_service_charge"
                        . "+ dtd.bank_service_charge_vat"
                        . "- dtd.returnable_amt"
                    . ") * dtd.quantity"
                .") as non_taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ dtd.unit_price"
                        . "+ dtd.govt_fee"
                        . "+ dtd.bank_service_charge"
                        . "+ dtd.bank_service_charge_vat"
                        . "- if(dt.tax_included, dtd.unit_tax, 0)"
                    . ") * dtd.quantity"
                .") as tax_free_total"
            ),
            DB::raw("(dtd.standard_cost * dtd.quantity) as total_standard_cost"),
        );

        if ($filters['line_reference']) {
            $query->where('dtd.line_reference', $filters['line_reference']);
        }

        return $query;
    }
    
    /**
     * Returns the builder instance for querying invoices
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function invoicesQuery($filters = [])
    {
        $query = DB::table('0_debtor_trans_details as dtd')
            ->leftJoin('0_debtor_trans as dt', function (JoinClause $join) {
                $join->on('dt.type', 'dtd.debtor_trans_type')
                    ->whereColumn('dt.trans_no', 'dtd.debtor_trans_no');
            })
            ->where('dtd.quantity', '<>', 0)
            ->where('dtd.debtor_trans_type', CustomerTransaction::INVOICE);

        $query->select(
            'dtd.*',
            'dt.type',
            'dt.trans_no',
            'dt.tran_date',
            'dt.reference',
            DB::raw(
                "("
                    . "("
                        . "+ dtd.unit_price"
                        . "+ dtd.returnable_amt"
                        . "+ dtd.extra_srv_chg"
                        . "- if(dt.tax_included, dtd.unit_tax, 0)"
                    . ") * dtd.quantity"
                .") as taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ dtd.govt_fee"
                        . "+ dtd.bank_service_charge"
                        . "+ dtd.bank_service_charge_vat"
                        . "- dtd.returnable_amt"
                    . ") * dtd.quantity"
                .") as non_taxable_amount"
            ),
            DB::raw(
                "("
                    . "("
                        . "+ dtd.unit_price"
                        . "+ dtd.govt_fee"
                        . "+ dtd.bank_service_charge"
                        . "+ dtd.bank_service_charge_vat"
                        . "- if(dt.tax_included, dtd.unit_tax, 0)"
                    . ") * dtd.quantity"
                .") as tax_free_total"
            )
        );

        if ($filters['line_reference']) {
            $query->where('dtd.line_reference', $filters['line_reference']);
        }

        return $query;
    }

    /**
     * Find the undelivered order items
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function undeliveredOrderItemsSelect2(Request $request)
    {
        $inputs = $request->validate([
            'term' => 'nullable',
            'page' => 'nullable|integer|min:1'
        ]);

        $pageLength = 25;
        $page = $inputs['page'] ?? 1;
        $builder = DB::query()
            ->select(
                'sod.line_reference as id',
                DB::raw(
                    "concat_ws("
                        . "' - '"
                        . ", nullif(sod.line_reference, '')"
                        . ", nullif(so.reference, '')"
                        . ", nullif(dm.debtor_ref, '')"
                        . ", nullif(so.contact_phone, '')"
                        . ", ifnull(nullif(so.display_customer, ''), nullif(dm.name, ''))"
                        . ", nullif(sod.stk_code, '')"
                        . ", nullif(round("
                            . "  sod.unit_price"
                            . "+ sod.returnable_amt"
                            . "+ sod.receivable_commission_amount"
                            . ", 2), ''"
                        . ")"
                        . ", nullif(round("
                            . "    sod.govt_fee"
                            . "+ sod.bank_service_charge"
                            . "+ sod.bank_service_charge_vat"
                            . ", 2), ''"
                        . ")"
                        . ", nullif(sod.description, '')"
                    . ") as `text`"
                )
            )
            ->from('0_sales_order_details as sod')
            ->leftJoin('0_sales_orders as so', function (JoinClause $join) {
                $join->on('so.order_no', 'sod.order_no')
                    ->on('so.trans_type', 'sod.trans_type');
            })
            ->leftJoin('0_stock_master as sm', 'sm.stock_id', 'sod.stk_code')
            ->leftJoin('0_debtors_master as dm', 'dm.debtor_no', 'so.debtor_no')
            ->where('sod.quantity', '<>', 0)
            ->where('sod.qty_sent', 0)
            ->where('sm.costing_method', COSTING_METHOD_EXPENSE)
            ->where('sod.trans_type', SalesOrder::ORDER);

        if (!empty($inputs['term'])) {
            $q = "%{$inputs['term']}%";
            $q2 = "\\b{$inputs['term']}";
            $builder->where(function ($query) use ($q, $q2) {
                $query->where('sod.line_reference', 'like', $q)
                    ->orWhere('dm.debtor_ref', 'like', $q)
                    ->orWhere('so.contact_phone', 'like', $q)
                    ->orWhere('sod.stk_code', 'like', $q)
                    ->orWhereRaw('`sod`.`description` REGEXP ?', [$q2])
                    ->orWhereRaw("ifnull(nullif(`so`.`display_customer`, ''), `dm`.`name`) REGEXP ?", [$q2]);
            });
        }

        $totalFiltered = $builder->count();
        $results = $builder->orderBy('name')
            ->offset(($page - 1) * $pageLength)
            ->limit($pageLength)
            ->get();

        return response()->json([
            'results' => $results->toArray(),
            'totalRecords' => $totalFiltered,
            'pagination' => [
                'more' => $totalFiltered > $page * $pageLength
            ]
        ]);
    }
}