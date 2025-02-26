<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerTransaction;
use App\Permissions as P;
use Illuminate\Http\Request;

class InvoiceController extends Controller {
    /**
     *  Find the invoice specified by the reference
     */
    public function findByReference(Request $request, $reference)
    {
        abort_unless($request->user()->hasPermission(P::SA_DSH_FIND_INV), 403);

        $invoice  = CustomerTransaction::active()
            ->ofType(CustomerTransaction::INVOICE)
            ->where('reference', $reference)
            ->first();
        
        abort_unless($invoice, 404);

        $invoice
            ->append('print_link')
            ->append('update_transaction_id_link');

        return ["data" => $invoice];
    }
}