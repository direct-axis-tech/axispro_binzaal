<?php

use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;

/**
 * List of all the options available for reference definitions
 * for each transaction
 *
 * @return array
 */
function refline_options()
{
    return array(
        JournalTransaction::JOURNAL     => array('date', 'user'),
        JournalTransaction::COST_UPDATE => array('date', 'user'),
    
        BankTransaction::CREDIT         => array('date', 'user'),
        BankTransaction::DEBIT          => array('date', 'user'),
        BankTransaction::TRANSFER       => array('date', 'user'),

        ST_SUPPAYMENT                   => array('date', 'user'),

        CustomerTransaction::PAYMENT    => array('date', 'user', 'dimension'),
        SalesOrder::ORDER               => array('date', 'customer', 'branch', 'user', 'pos'),
        SalesOrder::QUOTE               => array('date', 'customer', 'branch', 'user', 'pos'),
        CustomerTransaction::INVOICE    => array('date', 'customer', 'branch', 'user', 'pos', 'dimension'),
        CustomerTransaction::CREDIT     => array('date', 'customer', 'branch', 'user', 'pos'),
        CustomerTransaction::DELIVERY   => array('date', 'customer', 'branch', 'user', 'pos'),
    
        ST_LOCTRANSFER                  => array('date', 'location', 'user'),
        ST_INVADJUST                    => array('date', 'location', 'user'),
    
        ST_PURCHORDER                   => array('date', 'location', 'supplier', 'user'),
        ST_SUPPINVOICE                  => array('date', 'location', 'supplier', 'user'),
        ST_SUPPCREDIT                   => array('date', 'location', 'supplier', 'user'),
        ST_SUPPRECEIVE                  => array('date', 'location', 'supplier', 'user'),
    
        ST_WORKORDER                    => array('date', 'location', 'user'),
        ST_MANUISSUE                    => array('date', 'location', 'user'),
        ST_MANURECEIVE                  => array('date', 'user'),
        ST_DIMENSION                    => array('date','user'),
    );
}

/**
 * List of all placeholders used in the reference definition
 * and their translation
 *
 * @return void
 */
function refline_placeholders()
{
    return array(
        'MM' => 'date',
        'YY' => 'date',
        'YYYY' => 'date',
        'FF' => 'date', // fiscal year
        'FFFF' => 'date',
        'UU' => 'user',
         'P' => 'pos',
        'DIM' => 'dimension',
        'SUB' => 'type_prefix'
    	//  FIXME:  for placeholders below all the code should work, but as the ref length is variable,
    	//  length specification in placeholder format should be implemented.
    	// 'C' => 'customer',
    	// 'B' => 'branch',
    	// 'S' => 'supplier',
    	// 'L' => 'location'
    );
}