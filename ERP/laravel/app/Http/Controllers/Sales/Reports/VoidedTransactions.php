<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
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

class VoidedTransactions extends Controller {
    /**
     * Returns the view for showing report
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_VOIDEDTRANSACTIONS), 403);

        $inputs = [
            'reference' => '',
            'customer' => [],
            'voided_from' => date(dateformat()),
            'voided_till' => date(dateformat()),
            'voided_by' => '',
            'transaction_type' => '',
        ];
        $users = User::select('id', 'user_id')->get();
        $transactionTypes = $this->getTransactionTypes();

        return view('reports.managementReport.voidedTransactions',
            compact('transactionTypes', 'users', 'inputs')
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
        $transactionTypes = $this->getTransactionTypes();
        
        $casesOfTransactions = array_map(
            function($v, $k) { return "WHEN `voided`.`type` = {$k} THEN '{$v}'";},
            $transactionTypes,
            array_keys($transactionTypes)
        );
        $casesOfTransactions = implode(' ', $casesOfTransactions);

        $builder = DB::query()
            ->addSelect(
                'ref.reference',
                'voided.date_ as voided_date',
                'voided.trans_date',
                'voided.amount',
                'voided.memo_',
                'voider.user_id as voided_by',
                'transactor.user_id as transacted_by'
            )
            ->selectRaw("CASE {$casesOfTransactions} END as `type`")
            ->from('0_voided as voided')
            ->leftJoin('0_refs as ref', function(JoinClause $join) {
                $join->on('ref.type', 'voided.type')
                    ->whereColumn('ref.id', 'voided.id');
            })
            ->leftJoin('0_users as voider', 'voider.id', 'voided.created_by')
            ->leftJoin('0_users as transactor', 'transactor.id', 'voided.transaction_created_by')
            ->whereIn('voided.type', array_keys($transactionTypes));

        if (!empty($filters['transacted_by'])) {
            $builder->where('transactor.id', $filters['transacted_by']);
        }

        if (!empty($filters['voided_by'])) {
            $builder->where('voider.id', $filters['voided_by']);
        }

        if (!empty($filters['voided_from'])) {
            $builder->where('voided.date_', '>=', Carbon::parse($filters['voided_from']));
        }

        if (!empty($filters['voided_till'])) {
            $builder->where('voided.date_', '<=', Carbon::parse($filters['voided_till']));
        }

        if (!empty($filters['transaction_type'])) {
            $builder->where('voided.type', $filters['transaction_type']);
        }
        
        if (!empty($filters['reference'])) {
            $builder->where('ref.reference', $filters['reference']);
        }

        return $builder;
    }

    /**
     * Returns the transaction types enabled for this report
     *
     * @return array
     */
    private function getTransactionTypes()
    {
        return [
            CustomerTransaction::INVOICE => 'Sales Invoice',
            CustomerTransaction::PAYMENT => 'Customer Payment',
            JournalTransaction::JOURNAL => 'Journal Entry',
            BankTransaction::CREDIT => 'Payment Voucher',
            BankTransaction::DEBIT => 'Receipt Voucher',
        ];
    }

    /**
     * Handles the dataTable api requests
     *
     * @param Request $request
     */
    public function dataTable(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_VOIDEDTRANSACTIONS), 403);

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
        abort_unless($request->user()->hasPermission(Permissions::SA_VOIDEDTRANSACTIONS), 403);
        
        $ext = $request->input('to');
        $title = 'Invoices';
        $transactionTypes = $this->getTransactionTypes();

        // Its stated in wkhtmltopd docs that if user input is
        // not sanitized when generating report, it could lead
        // to complete server takedown
        $inputs = $request->validate([
            'to' => 'required|in:pdf,xlsx',
            'voided_from' => 'required|date_format:'.dateformat(),
            'voided_till' => 'required|date_format:'.dateformat(),
            'voided_by' => 'nullable|integer',
            'type' => 'nullable|in:'.implode(',', array_keys($transactionTypes)),
        ]);

        $meta = [
            "Period" => (
                  ($inputs['voided_from'] ?? 'Begning')
                . ' to '
                . ($inputs['voided_till'] ?? date(dateformat()))
            )
        ];

        if (!empty($inputs['voided_by'])) {
            $meta['Voided By'] = User::find($inputs['voided_by'])->name ?? null;
        }

        if (!empty($inputs['type'])) {
            $meta['Type'] = $transactionTypes[$inputs['type']];
        }

        $queryBuilder = $this->getBuilder($request->all());

        // Set Column to be displayed
        $textColumns = [
            'Reference' => 'reference',
            'Voided Date' => 'voided_date',
            'Transaction Date' => 'trans_date',
            'Voided By' => 'voided_by',
            'Transacted By' => 'transacted_by',
            'Memo' => 'memo_',
            'Type' => 'type',
        ];

        $amountColumns = [
            'Amount' => 'amount'
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