<?php
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;

function get_contents($trans_no, $is_emailing = false, $is_watermarked = false)
{
    $GLOBALS['trans'] = $trans = get_customer_trans($trans_no, ST_SALESINVOICE);
    $is_from_labour_contract = !empty($trans['contract_id']);
    if ($is_from_labour_contract) {
        $contract = Contract::find($trans['contract_id']);
    }
    $comments = get_comments(ST_SALESINVOICE, $trans_no)->fetch_assoc()['memo_'];
    $is_voided = false;
    if($trans['Total'] == 0){
        if(!empty(get_voided_entry(ST_SALESINVOICE, $trans_no))){
            $is_voided = true;
            $trans = get_customer_trans($trans_no, ST_SALESINVOICE, null, null, $is_voided);
        }
    }
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcodeImage = base64_encode($generator->getBarcode($trans['barcode'], $generator::TYPE_CODE_128));
    $trans_details = get_customer_trans_details(ST_SALESINVOICE, $trans_no, $is_voided)->fetch_all(MYSQLI_ASSOC);
    $trans_details = array_filter($trans_details, function($line){
        return $line['quantity'] != 0;
    });
    $trans_details = array_values($trans_details);
    $customer = get_customer($trans['debtor_no']);
    $GLOBALS['dimension'] = $dimension = get_dimension($trans['dimension_id']);
    $company_name = $GLOBALS['SysPrefs']->prefs['coy_name'];

    $allocations = get_allocatable_from_cust_transactions($trans['debtor_no'], $trans_no, ST_SALESINVOICE, $is_voided)
        ->fetch_all(MYSQLI_ASSOC);
    $allocations = array_map(function($trans) {
        $created_by = get_entered_by_user($trans['trans_no'], $trans['type']);
        $trans['_created_by'] = '';
        if(!empty($created_by)){
            $trans['_created_by'] = empty(trim($created_by['real_name'])) 
                ? $created_by['user_id']
                : trim($created_by['real_name']);
        }
        return $trans;
    }, $allocations);

    $gross_total = 0;
    $net_total = 0;
    $total_discount = 0;
    $total_tax = 0;
    $total_govt_fee = 0;
    $total_paid = 0;
    $total_payable = 0;
    $total_price = 0;
    $processing_fee = 0;
    $colspan = 6;
    $is_cost_grouped = !!data_get($dimension, 'is_cost_grouped_in_inv');
    $is_insurance_office = false;

    $is_tax_registered = !empty($dimension['gst_no']);
    $trn_no = $dimension['gst_no'];
    $title = $is_tax_registered ? 'TAX INVOICE' : 'INVOICE';
    $title_ar = $is_tax_registered ? 'فاتورة ضريبية' : 'فاتورة';

    $created_at = new DateTime($trans['transacted_at']);
    $created_by = $trans['transacted_user'];

    foreach($trans_details as $i => $line) {
        $refs = [];
        if ($line['transaction_id']) {
            $refs[] = $line['transaction_id'];
        }
        if ($line['application_id']) {
            $refs[] = $line['application_id'];
        }
        if ($line['passport_no']) {
            $refs[] = $line['passport_no'];
        }
        if ($line['ed_transaction_id']) {
            $refs[] = $line['ed_transaction_id'];
        }
        if ($line['ref_name']) {
            $refs[] = $line['ref_name'];
        }

        $taxable_amount = $line['unit_price'] + $line['returnable_amt'] + $line['extra_srv_chg'];
        $unit_price = $taxable_amount - ($trans['tax_included'] ? $line['unit_tax'] : 0);
        $extra_srv_chg = $taxable_amount ? ($line['extra_srv_chg'] / $taxable_amount * $unit_price) : 0;
        $cost = (
            $line['govt_fee']
            + $line['bank_service_charge']
            + $line['bank_service_charge_vat']
            - $line['returnable_amt']
        );
        $line_total  = (
            $unit_price
            + $cost
            + $line['unit_tax']
        ) * $line['quantity'];

        $total_price    += $unit_price * $line['quantity'];
        $gross_total    += $line_total;
        $total_discount += $line['discount_amount'] * $line['quantity'];
        $total_tax      += $line['unit_tax'] * $line['quantity'];
        $total_govt_fee += $cost * $line['quantity']; 
        
        $trans_details[$i]['unit_price']    = $unit_price;
        $trans_details[$i]['_refs']         = implode('&nbsp;&nbsp;&nbsp;', $refs);
        $trans_details[$i]['_cost']         = $cost;
        $trans_details[$i]['_total']        = $line_total;

        $is_insurance_office = $is_insurance_office || ($line['category_id'] == StockCategory::INSURANCE_OFFICE);
    }

    $net_total = $gross_total;
    if($customer['show_discount'] && $total_discount > 0.001){
        $net_total -= $total_discount;
    }

    $tax_items = array_filter(array_map(
        function ($tax_item) {
            if ($tax_item['amount'] == 0) {
                return null;
            }

            $name = 'Total ' . $tax_item['tax_type_name'] . (pref('company.suppress_tax_rates') ? '' : " (".$tax_item['rate']."%)");
            $amount = round2($tax_item['amount'], user_price_dec());

            return compact('name', 'amount');
        },
        get_trans_tax_details(ST_SALESINVOICE, $trans_no)->fetch_all(MYSQLI_ASSOC)
    ));

    $processing_fee = $trans['processing_fee'];
    $round_off = $trans['round_of_amount'];
    
    $net_total += $processing_fee + $round_off;

    $total_payable = $net_total;

    $is_customer_card_payment = $trans['invoice_type'] == 'G2' || $trans['invoice_type'] == 'CustomerCard';
    $is_cost_grouped = $is_cost_grouped || $is_insurance_office;

    if($is_cost_grouped){
        $colspan = 5;
    }
    if($is_customer_card_payment) {
        $total_paid = $is_cost_grouped ? $trans['customer_card_amount'] : $total_govt_fee;
        $total_payable -= $total_paid;
    }

    /** If There is allocation we need to offset the heights accordingly */
    $heights = [0 => 59, 1 => 76, 2 => 82];

    ob_start();
    if ($dimension['pos_type'] != POS_CAFETERIA) {
        include __DIR__ . '/content_pos_1.php';
    } else {
        include __DIR__ . '/content_pos_2.php';
    }
    
    return ob_get_clean();
}