<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Sales\AutofetchedTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Models\System\User;
use App\Permissions;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jimmyjs\ReportGenerator\ReportMedia\ExcelReport;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Yajra\DataTables\QueryDataTable;
use Illuminate\Support\Facades\Cache;

class AutoFetchTransactions extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_AUTOFETCHREPORT), 403);

        $inputs = [
            'transaction_from' => date(dateformat()),
            'transaction_to' => date(dateformat()),
            'application_id' => '',
            'transaction_id' => '',
        ];

        $users = User::select('id', 'user_id')->get();
       
        return view('reports.managementReport.autoFetchTransactions',
            compact('users', 'inputs')
        );
    }
    /**
     * Returns the query builder instance
     *
     * @param array $filters
     * @return Builder
     */
    public function getBuilder($filters = [] , $fullQuery = true)
    {
        $builder = DB::query()
            ->select(
                'autof.id',
                'autof.system_id',
                'autof.type',
                'autof.service_chg',
                'autof.processing_chg',
                'autof.total',
                'autof.transaction_id',
                'autof.application_id',
                'autof.web_user',
                'autof.company',
                'autof.contact_name',
                'autof.contact_no',
                 DB::raw("DATE_FORMAT(autof.created_at, '%d-%b-%Y') as created_at")
            )
            ->selectRaw("CONCAT_WS(' - ', autof.service_en, autof.service_ar) as service_name")
            ->from('0_autofetched_trans as autof');

        if ($fullQuery) {
            $this->attachInvoicesQuery($builder, $filters);
        }
        
        if (!empty($filters['application_id'])) {
            $builder->where('autof.application_id', $filters['application_id']);
        }
        
        if (!empty($filters['transaction_id'])) {
            $builder->where('autof.transaction_id', $filters['transaction_id']);
        }

        if (!empty($filters['transaction_from'])) {
            $builder->whereDate('autof.created_at', '>=', Carbon::parse($filters['transaction_from']));
        }

        if (!empty($filters['transaction_to'])) {
            $builder->whereDate('autof.created_at', '<=', Carbon::parse($filters['transaction_to']));
        }

        return $builder;
    }

    /**
     * Attach the invoices query to the contract inquiry builder
     *
     * @param Builder $builder
     * @param array $inputs
     * @return void
     */
    public function attachInvoicesQuery($builder, $inputs)
    {
         $builder
            ->leftJoinSub(
                $this->invoicesQuery($inputs),
                'invoice',
                'invoice.application_id',
                'autof.application_id'
            )
            ->selectRaw("if(invoice.application_id, 'Yes', 'No') as invoiced");
    }

    /**
     * Returns the query for attaching invoices to the builder
     *
     * @param array $inputs
     * @return Builder
     */
    public function invoicesQuery()
    {
        $invoices = DB::table('0_debtor_trans_details as inv')
            ->select('inv.application_id')
            ->where('inv.debtor_trans_type', CustomerTransaction::INVOICE)
            ->where('inv.quantity', '>', 0)
            ->where('inv.application_id', '!=', '');

        return $invoices;
    }

     /**
     * Get the invoice information from the autofetch
     *
     * @param object $contract
     * @param array $inputs
     * @return object
     */
    private function getInvoiceInfo($autofetch)
    {
        return Cache::store('array')->rememberForever(
            "autofetch.{$autofetch->application_id}.invoices",
            function () use ($autofetch) {
                if (isset($autofetch->invoiced)) {
                    return (object)[
                        'application_id' => $autofetch->application_id,
                        'invoiced' => $autofetch->invoiced
                    ];
                }

                $invoice = $this->invoicesQuery()
                    ->where('inv.application_id', $autofetch->application_id)
                    ->first();
               
                return (object)['invoiced' => ($invoice ? 'Yes' : 'No')];
            }
        );
    }

    /**
     * Handles the dataTable api requests
     *
     * @param Request $request
     */
    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_AUTOFETCHREPORT), 403);

        $getFromInvoice = function ($key){
            return function ($autofetch) use ($key) {
                return data_get($this->getInvoiceInfo($autofetch), $key);
            };
        };

        $dataTable = (new QueryDataTable(DB::query()->fromSub($this->getBuilder($request->all(), false), 't')))
            ->addColumn('invoiced', $getFromInvoice('invoiced'));
        
        return $dataTable->toJson(); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hr\AutofetchedTransaction $payElement
     * @return \Illuminate\Http\Response
     */
    public function destroy(AutofetchedTransaction $AutofetchedTransaction)
    {
        abort_unless(authUser()->hasPermission(Permissions::SA_AUTOFETCHREPORT), 403);

        abort_if($AutofetchedTransaction->is_used, 422, 'This Autofetch is already invoiced.');
       
        $AutofetchedTransaction->delete();

        return response()->json(['message' => 'AutoFetch Deleted Successfully']);
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
            'transaction_from' => 'nullable|date_format:'.dateformat(),
            'transaction_to' => 'nullable|date_format:'.dateformat()
        ]);

        $ext = $inputs['to'];
        $title = 'AutoFetch Transaction Report';
        $meta = [
            "Period" => (
                  ($inputs['transaction_from'] ?? 'Beginning')
                . ' to '
                . ($inputs['transaction_to'] ?? date(dateformat()))
            )
        ];

        // Set Column to be displayed
        $columns = [
            'System Id' => 'system_id',
            'Type' => 'type',
            'Service' => 'service_name',
            'Service Charge' => 'service_chg',
            'Processing Charge' => 'processing_chg',
            'Total' => 'total',
            'Transaction Id' => 'transaction_id',
            'Application Id' => 'application_id',
            'Web user' => 'web_user',
            'Created Date' => 'created_at',
            'Invoiced' => 'invoiced'
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
            "redirect_to" => url(route("file.download", ['type' => 'autofetch-transactions', 'file' => basename($file)]))
        ];
    }
}