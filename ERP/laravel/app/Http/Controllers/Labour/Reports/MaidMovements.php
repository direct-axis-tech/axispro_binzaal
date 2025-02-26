<?php

namespace App\Http\Controllers\Labour\Reports;

use App\Http\Controllers\Controller;
use App\Models\Labour\Labour;
use Carbon\Carbon as Carbon;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Yajra\DataTables\QueryDataTable;

class MaidMovements extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_MAID_MOVEMENT_REPORT), 403);

        $labours = Labour::all();
        $defaultFilters = [
            'maid' => '',
            'from' => Carbon::now()->subWeek()->format(dateformat()),
            'till' => date(dateformat())
        ];

        return view('labours.maidMovements',
            compact('labours','defaultFilters')
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
        $builder = DB::query()
            ->select(
                'move.type',
                'move.trans_id',
                'move.tran_date',
                'meta.name as type_name',
                'move.loc_code',
                DB::raw("CONCAT_WS(' - ', NULLIF(`labour`.`maid_ref`, ''), `labour`.`name`) as maid_name"),
                DB::raw("IF(`move`.`qty` > 0, 'IN', 'OUT') AS `status`"),
                DB::raw(
                    "COALESCE("
                        . "NULLIF(CONCAT_WS(' - ', NULLIF(`supplier`.`supp_ref`, ''), `supplier`.`supp_name`), ''), "
                        . "NULLIF(CONCAT_WS(' - ', NULLIF(`debtor`.`debtor_ref`, ''), `debtor`.`name`), '')"
                    .") as `counter_party_name`"
                ),
                'contract.reference as contract_ref',
                DB::raw(
                    "COALESCE("
                        . "NULLIF(NULLIF(trim(`move`.`reference`), ''), 'auto'), "
                        . "NULLIF(`grn`.`reference`, 'auto'), "
                        . "NULLIF(`supp_trans`.`reference`, 'auto'), "
                        . "NULLIF(`cust_trans`.`reference`, 'auto'), "
                        . "`move`.`reference`"
                    .") as `reference`"
                )
            )
            ->from('0_stock_moves as move')
            ->leftJoin('0_labours as labour', 'labour.id', 'move.maid_id')
            ->leftJoin('0_meta_transactions as meta', 'meta.id', 'move.type')
            ->leftJoin('0_grn_items as grn_item', function (JoinClause $join) {
                $join->on('grn_item.grn_batch_id', 'move.trans_no')
                    ->where('move.type', ST_SUPPRECEIVE)
                    ->whereColumn('grn_item.maid_id', 'move.maid_id')
                    ->where('grn_item.qty_recd', '<>', 0);
            })
            ->leftJoin('0_supp_invoice_items as supp_inv_itm', function (JoinClause $join) {
                $join->on('supp_inv_itm.grn_item_id', 'grn_item.id')
                    ->whereColumn('supp_inv_itm.po_detail_item_id', 'grn_item.po_detail_item')
                    ->where('supp_inv_itm.supp_trans_type', ST_SUPPINVOICE)
                    ->where('supp_inv_itm.quantity', '<>', 0);
            })
            ->leftJoin('0_grn_batch as grn', 'grn.id', 'grn_item.grn_batch_id')
            ->leftJoin('0_supp_trans as supp_trans', function (JoinClause $join) {
                $join->where(function (Builder $query) {
                    $query->whereColumn('supp_inv_itm.supp_trans_type', 'supp_trans.type')
                        ->whereColumn('supp_inv_itm.supp_trans_no', 'supp_trans.trans_no')
                        ->where('supp_trans.ov_amount', '<>', 0);
                })->orWhere(function (Builder $query) {
                    $query->whereColumn('move.type', 'supp_trans.type')
                        ->whereColumn('move.trans_no', 'supp_trans.trans_no')
                        ->where('supp_trans.ov_amount', '<>', 0);
                });
            })
            ->leftJoin('0_suppliers as supplier', 'supplier.supplier_id', 'supp_trans.supplier_id')
            ->leftJoin('0_debtor_trans as cust_trans', function (JoinClause $join) {
                $join->on('cust_trans.trans_no', 'move.trans_no')
                    ->whereColumn('cust_trans.type', 'move.type')
                    ->whereRaw('(cust_trans.ov_amount + cust_trans.ov_gst + cust_trans.ov_discount + cust_trans.ov_freight + cust_trans.ov_freight_tax) <> 0');
            })
            ->leftJoin('0_labour_contracts as contract', function (JoinClause $join) {
                $join->whereColumn('contract.id', 'move.contract_id')
                    ->orWhereColumn('contract.id', 'cust_trans.contract_id');
            })
            ->leftJoin('0_debtors_master as debtor', function (JoinClause $join) {
                $join->whereColumn('debtor.debtor_no', 'cust_trans.debtor_no')
                    ->orWhereColumn('debtor.debtor_no', 'contract.debtor_no');
            })
            ->whereNotNull('move.maid_id')
            ->orderByDesc('move.tran_date');

        if (!empty($filters['from'])) {
            $builder->where('move.tran_date', '>=', date2sql($filters['from']));
        }

        if (!empty($filters['till'])) {
            $builder->where('move.tran_date', '<=', date2sql($filters['till']));
        }

        if (!empty($filters['maid_id'])) {
            $builder->where('move.maid_id', $filters['maid_id']);
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
        abort_unless($request->user()->hasPermission(Permissions::SA_MAID_MOVEMENT_REPORT), 403);

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
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'from' => 'required|date_format:'.dateformat(),
            'till' => 'required|date_format:'.dateformat(),
            'maid_id' => 'nullable|integer'
        ]);

        $ext = $inputs['to'];
        $title = 'Maid Movement Report';
        $meta = [
            "Period" => (
                  ($inputs['from'] ?? 'Beginning')
                . ' to '
                . ($inputs['till'] ?? date(dateformat()))
            )
        ];

        // Set Column to be displayed
        $columns = [
            'Transaction Type' => 'type_name',
            'Reference' => 'reference',
            'Contract Ref' => 'contract_ref',
            'Transaction Date' => 'tran_date',
            'Maid' => 'maid_name',
            'Customer/Supplier' => 'counter_party_name',
            'Status' => 'status'
        ];
        
        $builder = $this->getBuilder($request->all());

        $generator = app($ext == 'xlsx' 
            ? ExcelReport::class
            : PdfReport::class
        )->of($title, $meta, $builder, $columns)
        ->setPaper('a4');

        $generator->simple();
    
        $file = 'download/'.Str::orderedUuid().".$ext";
        $generator->store($file);

        return [
            "redirect_to" => url(route("file.download", ['type' => 'maid-movements', 'file' => basename($file)]))
        ];
    }

}