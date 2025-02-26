<?php

namespace App\Http\Controllers\Labour\Reports;

use App\Http\Controllers\Controller;
use App\Models\Labour\Contract;
use App\Models\Sales\Customer;
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


class Installments extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_INSTALLMENT_REPORT), 403);

        $customers = Customer::all();
        $defaultFilters = [
            'maid' => '',
            'from' => Carbon::now()->subWeek()->format(dateformat()),
            'till' => date(dateformat())
        ];

        return view('labours.installmentReport',
            compact('customers','defaultFilters')
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

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);
        $dimension_id = Contract::make()->dimension_id ?: -1;
        
        $builder = DB::query()
            ->select(
                'detail.id',
                'contract.id as contract_id',
                'contract.reference as contract_ref',
                'contract.contract_from',
                'contract.contract_till',
                'debtor.name as customer_name',
                'inst.trans_date',
                'detail.due_date',
                'detail.payee_name',
                'detail.bank_id',
                'detail.cheque_no',
                'bank.name as bank_name' ,
                'detail.amount',   
                'detail.invoice_ref'    
            )
            ->selectRaw("'$dimension_id' as dimension_id")
            ->from('0_contract_installment_details as detail')
            ->leftJoin('0_contract_installments as inst', 'inst.id', 'detail.installment_id')
            ->leftJoin('0_banks as bank', 'bank.id', 'detail.bank_id')
            ->leftJoin('0_debtors_master as debtor', function (JoinClause $join) {
                $join->whereColumn('debtor.debtor_no', 'inst.person_id')
                    ->where('inst.person_type_id', PT_CUSTOMER);
            })
            ->leftJoin('0_labour_contracts as contract', 'contract.id', 'inst.contract_id');

        if (!empty($filters['from'])) {  
            $builder->where('detail.due_date', '>=', date2sql($filters['from']));
        }

        if (!empty($filters['till'])) {
            $builder->where('detail.due_date', '<=', date2sql($filters['till']));
        }

        if (!empty($filters['debtor_no'])) {
            $builder->where('inst.person_id', $filters['debtor_no']);
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
        abort_unless($request->user()->hasPermission(Permissions::SA_LBR_INSTALLMENT_REPORT), 403);

        $dataTable = (new QueryDataTable(DB::query()->fromSub($this->getBuilder($request->all()), 't')))
            ->addColumn('due_date_difference', function($installment) {
                return (
                    Carbon::parse($installment->due_date)->diffForHumans()
                );
            });
        
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
        ]);

        $ext = $inputs['to'];
        $title = 'Installment Report';
        $meta = [
            "Period" => (
                  ($inputs['from'] ?? 'Beginning')
                . ' to '
                . ($inputs['till'] ?? date(dateformat()))
            )
        ];

        // Set Column to be displayed
        $columns = [
            'Contract Ref' => 'contract_ref',
            'Entry Date' => 'trans_date',
            'Customer' => 'customer_name',
            'Due Date' => 'due_date',
            'Payee Name' => 'payee_name',
            'Bank' => 'bank_name',
            'Cheque No' => 'cheque_no',
            'Amount' => 'amount',
            'Invoice Ref' => 'invoice_ref',
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
            "redirect_to" => url(route("file.download", ['type' => 'installment-report', 'file' => basename($file)]))
        ];
    }

}