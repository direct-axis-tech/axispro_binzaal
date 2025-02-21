<?php

namespace App\Http\Controllers\Labour;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\FiscalYear;
use App\Models\Inventory\StockItem;
use App\Models\Labour\Contract;
use App\Models\Labour\Labour;
use App\Models\MetaReference;
use App\Models\Sales\CustomerTransaction;
use App\Models\Inventory\StockMove;
use App\Permissions;
use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\QueryDataTable;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Installment;
use UnexpectedValueException;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = authUser();

        abort_unless($user->hasPermission(Permissions::SA_LBR_CONTRACT_INQ), 403);

        $inputs = array_merge(
            array_fill_keys(array_keys($this->validationRules()), ''),
            [
                'contract_from_start' => sql2date(FiscalYear::find(pref('company.f_year'))->begin)
            ],
        );

        return view('labours.contract.index', [
            'labour_contract_types' => labour_contract_types(),
            'labours' => Labour::all(),
            'categories' => labour_invoice_categories(),
            'inputs' => $inputs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_CONTRACT), 403);

        return view('labours.contract.create', [
            'reference' => MetaReference::getNext(Contract::CONTRACT),
            'labour_contract_types' => labour_contract_types(),
            'labours' => Labour::all(),
            'categories' => labour_invoice_categories(),
            'stockItems' => StockItem::whereIn('category_id', array_keys(labour_invoice_categories()))->get(),
            'dimension' => Dimension::whereCenterType(CENTER_TYPES['DOMESTIC_WORKER'])->first()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        throw new BadMethodCallException("Function moved to labour_contract::create_contract");
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Contract $contract
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Contract $contract)
    {
        $contract->load('customer', 'maid', 'stock', 'category');

        return response()->json(compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Contract $contract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contract $contract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Labour\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Contract $contract)
    {
        //
    }

    /**
     * Get the validation rules for the filters
     * 
     * @return array
     */
    public function validationRules()
    {
        return [
            'reference'             => 'nullable|string',
            'debtor_no'             => 'nullable|integer',
            'type'                  => 'nullable|integer',
            'labour_id'             => 'nullable|integer',
            'category_id'           => 'nullable|integer',
            'invoice_status'        => 'nullable|in:Fully Invoiced,Partially Invoiced,Not Invoiced',
            'payment_status'        => 'nullable|in:Fully Paid,Partially Paid,Not Paid',
            'contract_from_start'   => 'nullable|date_format:'. dateformat(),
            'contract_from_end'     => 'nullable|date_format:'. dateformat(),
            'contract_till_start'   => 'nullable|date_format:'. dateformat(),
            'contract_till_end'     => 'nullable|date_format:'. dateformat(),
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
        $user = authUser();
        abort_unless($user->hasPermission(Permissions::SA_LBR_CONTRACT_INQ), 403);

        $inputs = $request->validate($this->validationRules());

        $dataTable = (new QueryDataTable(DB::query()->fromSub($this->getQuery($inputs), 't')))
            ->addColumn('_isContractInvoicable', function($contract) use ($user) {
                return (
                    floatcmp($contract->amount, $contract->invoiced_amount) > 0
                    && $user->hasPermission(Permissions::SA_SALESINVOICE)
                    && !$contract->inactive
                    && !$contract->is_on_installment
                );
            })
            ->addColumn('_isContractCreditable', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_SALESCREDIT)
                    && $contract->invoiced_amount > 0
                    && !$contract->inactive
                );
            })
            ->addColumn('_isContractDeliverable', function($contract) use ($user) {
                return (
                    empty($contract->maid_delivered_at)
                    && !$contract->inactive
                );
            })
            ->addColumn('_isPaymentReceivable', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_SALESPAYMNT)
                    && round2($contract->order_total, user_price_dec()) > round2($contract->total_payment, user_price_dec())
                    && !$contract->inactive
                    && !$contract->is_on_installment
                );
            })
            ->addColumn('_isConvertibleToInstallments', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_LBR_CONTRACT_INSTALLMENT)
                    && !$contract->is_on_installment
                    && !in_array($contract->category_id, [StockCategory::DWD_PACKAGEONE])  
                    && !($contract->total_payment > 0)
                    && !($contract->invoiced_amount > 0)
                );
            })
            ->addColumn('_isContractMaidReturnable', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_MAID_RETURN)
                    && !$contract->inactive
                    && is_null($contract->return_trans_id)
                    && !is_null($contract->delivery_id)
                );
            })
            ->addColumn('_isContractMaidReplaceable', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_MAID_REPLACEMENT)
                    && !$contract->inactive
                    && is_null($contract->return_trans_id)
                    && !is_null($contract->delivery_id)
                    && $contract->category_id != StockCategory::DWD_PACKAGEONE
                );
            })
            ->addColumn('_isInstallmentDeletable', function($contract) use ($user) {
                return (
                    $user->hasPermission(Permissions::SA_INSTALLMENT_DELETE)
                    && !$contract->inactive
                    && !is_null($contract->installment_id)
                    && !(Installment::make(['id' => $contract->installment_id])->is_used)
                );
            });
        
        return $dataTable->toJson();
    }

    /**
     * Returns the list of category ids where labour contract is enabled for
     *
     * @return array
     */
    public function getAvailableCategories()
    {
        return array_keys(labour_invoice_categories());
    }

    /**
     * Handle the request to print the resource
     *
     * @param  \App\Models\Labour\Contract  $contract
     * @return \Illuminate\Http\Response
     */
    public function print(Contract $contract, \Mpdf\Mpdf $mpdf)
    {
        $pageTitle = 'Labour Contract';
        $company = [
            'name' => pref('company.coy_name'),
            'mobile_no' => pref('company.phone'),
            'email' => pref('company.email'),
            'address' => pref('company.postal_address'),
        ];

        switch ($contract->category_id) {
            case StockCategory::DWD_PACKAGEONE:
                $title = 'Employment Contract - Traditional Hiring for Domestic Worker';
                $title_ar = 'عقد العمل - التوظيف التقليدي للعمالة المنزلية';
                $terms = view('labours.contract.terms.packageOne', compact('contract'))->render();
                break;

            case StockCategory::DWD_PACKAGETWO:
                $title = 'Employment Contract - Probation Package for Domestic Workers';
                $title_ar = 'عقد العمل - حزمة الاختبار للعمالة المنزلية';
                $terms = view('labours.contract.terms.packageTwo', compact('contract'))->render();
                break;

            case StockCategory::DWD_PACKAGETHREE:
                $title = 'Employment Contract - Temporary Package for Hiring Domestic Worker';
                $title_ar = 'عقد العمل - الباقة المؤقتة لاستقدام عاملة منزلية';
                $terms = view('labours.contract.terms.packageThree', compact('contract'))->render();
                break;

            case StockCategory::DWD_PACKAGEFOUR:
                $title = 'Employment Contract - Flexi Package for Hiring Domestic Worker (Hour|Day|Week|Month)';
                $title_ar = 'عقد العمل - الباقة المرنة لتوظيف العمالة المنزلية (ساعة|يوم|أسبوع|شهر)';
                $terms = view('labours.contract.terms.packageFour', compact('contract'))->render();
                break;

            default:
                throw new UnexpectedValueException("Could not find the terms defined for this package");
        }

        $mpdf->SetTitle($pageTitle);
        $mpdf->WriteHTML(view('labours.contract.print', compact(
            'title',
            'title_ar',
            'contract',
            'company',
            'terms'
        ))->render());

        $fileName = Str::orderedUuid() . '_contract.pdf';
        $filePath = storage_path("download/{$fileName}");
        $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);
        
        return response()->file($filePath, [
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function deliverMaid(Request $request, Contract $contract)
    {
        throw new BadMethodCallException("Function moved to labour_contract::deliver_maid");
    }

    /**
     * Returns the query builder for inquiry
     *
     * @param array $inputs
     * @return Builder
     */
    public function getQuery($inputs = [])
    {
        $total = function($key) {
            return "`{$key}`.`ov_amount` + `{$key}`.`ov_gst` + `{$key}`.`ov_freight` + `{$key}`.`ov_freight_tax` + `{$key}`.`ov_discount`";
        };

        $invoices = DB::table('0_debtor_trans as inv')
            ->select('inv.contract_id')
            ->selectRaw("ifnull(group_concat(`inv`.`reference` separator ', '), '') as refs")
            ->selectRaw("ifnull(count(`inv`.`id`), 0) as no_of_invoices")
            ->selectRaw("ifnull(round(sum({$total('inv')} - `inv`.`processing_fee`), 2), 0) as total_invoiced")
            ->selectRaw("ifnull(round(sum(`inv`.`processing_fee`), 2), 0) as processing_fee")
            ->selectRaw("ifnull(max(`inv`.`period_till`), '') as last_invoiced_till")
            ->whereNotNull('inv.contract_id')
            ->where('inv.type', CustomerTransaction::INVOICE)
            ->whereRaw("{$total('inv')} <> 0")
            ->groupBy('inv.contract_id');

        $payments = DB::table('0_debtor_trans as pmt')
            ->leftJoin('0_cust_allocations as alloc', function(JoinClause $join) {
                $join->on('alloc.person_id', 'pmt.debtor_no')
                    ->whereColumn('alloc.trans_type_from', 'pmt.type')
                    ->whereColumn('alloc.trans_no_from', 'pmt.trans_no');
            })
            ->leftJoin('0_debtor_trans as inv', function(JoinClause $join) use ($total) {
                $join->on('alloc.trans_type_to', 'inv.type')
                    ->whereColumn('alloc.trans_no_to', 'inv.trans_no')
                    ->whereColumn('alloc.person_id', 'inv.debtor_no')
                    ->whereNotNull('inv.contract_id')
                    ->whereRaw("({$total('inv')}) <> 0");
            })
            ->leftJoin('0_sales_orders as so', function(JoinClause $join) {
                $join->on('alloc.trans_type_to', 'so.trans_type')
                    ->whereColumn('alloc.trans_no_to', 'so.order_no')
                    ->whereColumn('alloc.person_id', 'so.debtor_no')
                    ->whereNotNull('so.contract_id');
            })
            ->selectRaw("ifnull(`so`.`contract_id`, `inv`.`contract_id`) as contract_id")
            ->selectRaw("ifnull(group_concat(DISTINCT `pmt`.`reference` separator ', '), '') as refs")
            ->selectRaw("ifnull(count(DISTINCT `pmt`.`id`), 0) as no_of_payments")
            ->selectRaw('ifnull(round(sum(`alloc`.`amt`), 2), 0) as total_payment')
            ->whereRaw("{$total('pmt')} <> 0")
            ->where(function (Builder $query) {
                $query->whereNotNull('inv.id')
                    ->orWhereNotNull('so.id');
            })
            ->groupBy(DB::raw('ifnull(`so`.`contract_id`, `inv`.`contract_id`)'));
            
        $dimension_id = Contract::make()->dimension_id ?: -1;
        $contracts = DB::query()
            ->select(
                'contract.id',
                'contract.type',
                'contract.contract_no',
                'contract.reference',
                'contract.debtor_no',
                'contract.category_id',
                'contract.labour_id',
                'contract.order_date',
                'contract.contract_from',
                'contract.contract_till',
                'contract.maid_expected_by',
                'contract.maid_delivered_at',
                'contract.amount',
                'contract.prep_amount',
                'contract.memo',
                'contract.inactive',
                'order.reference as order_reference',
                'order.order_no',
                'order.total as order_total',
                'labour.maid_ref',
                'labour.name as labour_name',
                'agent.supp_name as agent_name',
                'customer.name as customer_name',
                'creator.real_name as creator_name',
                'stock.description as stock_name',
                'delivery.id as delivery_id',
                'return.trans_id as return_trans_id',
                'labour.category as maid_category'
            )
            ->selectRaw("contract.dimension_id")
            ->selectRaw("ifnull(round(order.total - contract.amount, 2), 0) as added_tax")
            ->selectRaw(
                '('
                    .'case'
                        . ' when `contract`.`inactive` THEN "Discontinued"'
                        . ' when isnull(contract.maid_delivered_at) THEN "Not Delivered"'
                        . ' when current_date() > contract.contract_till THEN "Expired"'
                        . ' when current_date() = contract.contract_till THEN "Expires Today"'
                        . ' when datediff(contract.contract_till, current_date()) = 1 THEN "Expires Tomorrow"'
                        . ' else '
                            . 'concat_ws('
                                . "' ',"
                                . "'Expires in',"
                                . "concat(nullif(timestampdiff(YEAR, current_date(), contract.contract_till), 0), ' year/s'),"
                                . "concat(nullif(mod(timestampdiff(MONTH, current_date(), contract.contract_till), 12), 0), ' month/s'),"
                                . "concat(nullif(mod(datediff(contract.contract_till, current_date()), 30), 0), ' days')"
                            .')'
                    . 'end'
                .') as `status`'
            )
            ->selectRaw("replace(`category`.`description`, 'DOMESTIC WORKERS DIVISION - ', '') as category")
            ->selectRaw('date(contract.created_at) as created_at')
            ->selectRaw("ifnull(`invoice`.`refs`, '') as invoices")
            ->selectRaw("ifnull(`invoice`.`no_of_invoices`, 0) as no_of_invoices")
            ->selectRaw("ifnull(`invoice`.`total_invoiced`, 0) as invoiced_amount")
            ->selectRaw("ifnull(`invoice`.`processing_fee`, 0) as processing_fee")
            ->selectRaw("ifnull(`invoice`.`last_invoiced_till`, '') as last_invoiced_till")
            ->selectRaw("ifnull(`payment`.`refs`, '') as payments")
            ->selectRaw("ifnull(`payment`.`no_of_payments`, 0) as no_of_payments")
            ->selectRaw('ifnull(`payment`.`total_payment`, 0) as total_payment')
            ->selectRaw('ROUND(order.total - ifnull(`payment`.`total_payment`, 0), 2) as balance_payment')
            ->selectRaw('!isnull(`installment`.`id`) as is_on_installment')
            ->addSelect(
                'installment.id as installment_id',
                'installment.no_installment',
                'installment.installment_amount'
            )
            ->from('0_labour_contracts as contract')
            ->leftJoinSub($invoices, 'invoice', 'invoice.contract_id', 'contract.id')
            ->leftJoinSub($payments, 'payment', 'payment.contract_id', 'contract.id')
            ->leftJoin('0_contract_installments as installment', 'installment.contract_id', 'contract.id')
            ->leftJoin('0_labours as labour', 'labour.id', 'contract.labour_id')
            ->leftJoin('0_suppliers as agent', 'labour.agent_id', 'agent.supplier_id')
            ->leftJoin('0_debtors_master as customer', 'customer.debtor_no', 'contract.debtor_no')
            ->leftJoin('0_stock_category as category', 'category.category_id', 'contract.category_id')
            ->leftJoin('0_stock_master as stock', 'stock.stock_id', 'contract.stock_id')
            ->leftJoin('0_sales_orders as order', 'order.contract_id', 'contract.id')
            ->leftJoin('0_users as creator', 'creator.id', 'contract.created_by')
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('voided.type', 'contract.type')
                    ->whereColumn('voided.id', 'contract.contract_no');
            })
            ->leftJoin('0_stock_moves as return', function (JoinClause $join) {
                $join->on('return.contract_id', 'contract.id')
                    ->where('return.type', StockMove::STOCK_RETURN);
            })
            ->leftJoin('0_debtor_trans as delivery', function (JoinClause $join) {
                $join->on('delivery.contract_id', 'contract.id')
                    ->where('delivery.type', CustomerTransaction::DELIVERY)
                    ->whereRaw('`delivery`.`ov_amount`  + `delivery`.`ov_gst` + `delivery`.`ov_freight` + `delivery`.`ov_discount` + `delivery`.`ov_freight_tax` <> 0');
            }) 
            ->whereNull('voided.id')
            ->groupBy('contract.id')
            ->orderBy(DB::raw("date_format(`contract`.`contract_from`, '%Y')"), 'desc')
            ->orderBy('contract.contract_no', 'desc');

        if (!empty($inputs['reference'])) {
            $contracts->where('contract.reference', $inputs['reference']);
        }
        
        if (!empty($inputs['debtor_no'])) {
            $contracts->where('contract.debtor_no', $inputs['debtor_no']);
        }
        
        if (!empty($inputs['type'])) {
            $contracts->where('contract.type', $inputs['type']);
        }
        
        if (!empty($inputs['labour_id'])) {
            $contracts->where('contract.labour_id', $inputs['labour_id']);
        }
        
        if (!empty($inputs['category_id'])) {
            $contracts->where('contract.category_id', $inputs['category_id']);
        }

        if (!empty($inputs['contract_from_start'])) {
            $contracts->where(
                'contract.contract_from',
                '>=',
                Carbon::createFromFormat(dateformat(), $inputs['contract_from_start'])->toDateString()
            );
        }

        if (!empty($inputs['contract_from_end'])) {
            $contracts->where(
                'contract.contract_from',
                '<=',
                Carbon::createFromFormat(dateformat(), $inputs['contract_from_end'])->toDateString()
            );
        }

        if (!empty($inputs['contract_till_start'])) {
            $contracts->where(
                'contract.contract_till',
                '>=',
                Carbon::createFromFormat(dateformat(), $inputs['contract_till_start'])->toDateString()
            );
        }

        if (!empty($inputs['contract_till_end'])) {
            $contracts->where(
                'contract.contract_till',
                '<=',
                Carbon::createFromFormat(dateformat(), $inputs['contract_till_end'])->toDateString()
            );
        }
        
        if (!empty($inputs['invoice_status'])) {
            switch ($inputs['invoice_status']) {
                case 'Fully Invoiced':
                    $contracts->havingRaw('(`order_total` = `invoiced_amount` and `order_total` > 0)');
                    break;
                case 'Partially Invoiced':
                    $contracts->havingRaw('(`order_total` > `invoiced_amount` and `invoiced_amount` > 0)');
                    break;
                case 'Not Invoiced':
                    $contracts->havingRaw('(`invoiced_amount` = 0 and `order_total` > 0)');
                    break;
            }
        }
        
        if (!empty($inputs['payment_status'])) {
            switch ($inputs['payment_status']) {
                case 'Fully Paid':
                    $contracts->havingRaw('(`order_total` = `total_payment` and `order_total` > 0)');
                    break;
                case 'Partially Paid':
                    $contracts->havingRaw('(`order_total` > `total_payment` and `total_payment` > 0)');
                    break;
                case 'Not Paid':
                    $contracts->havingRaw('(`total_payment` = 0 and `order_total` > 0)');
                    break;
            }
        }

        return $contracts;
    }
}