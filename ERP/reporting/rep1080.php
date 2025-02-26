<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_SALESTRANSVIEW';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//------------------------------------------------------------------

print_report();

function get_report($customer_id,$from,$to)
{

    $from = date2sql($from);
    $to = date2sql($to);

    $sql = "
SELECT reference,type,tran_date, 
item_desc,category,quantity,
IF(type=10,payment_status,NULL) payment_status,

memo_, CASE WHEN type=10 THEN service_total ELSE 
SUM(debit) END as debit, SUM(credit) credit,

type_no,debtor_no,customer FROM 

( SELECT a.tran_date,a.type,a.type_no,b.debtor_no, b.name AS customer,c.reference, 

CASE WHEN a.amount > 0 THEN ABS(a.amount)
ELSE 0 END AS debit, CASE WHEN a.amount < 0 THEN ABS(a.amount) ELSE 0 END AS credit, 

f.description as item_desc, h.description as category,f.quantity,

((f.quantity*f.unit_price) - (f.quantity*f.discount_amount))+
            (f.unit_tax * f.quantity)+
            ((f.govt_fee + f.bank_service_charge + f.bank_service_charge_vat) *
                                    f.quantity) AS service_total,
                                    
(CASE WHEN (e.alloc >= (e.ov_amount + e.ov_gst)) THEN 'Fully Paid' 
        WHEN (e.alloc = 0) THEN 'Not Paid' WHEN (e.alloc < (e.ov_amount + e.ov_gst)) 
		  THEN 'Partially Paid' END) AS payment_status,    
		  
		  
		  CASE WHEN e.type = 10 THEN group_concat(f.ref_name) ELSE 
(select group_concat(memo_) from 0_comments where id=e.trans_no and type = e.type) 
	END as memo_ 
	
	
FROM 0_gl_trans a
LEFT JOIN 0_debtors_master b ON b.debtor_no=a.person_id
LEFT JOIN 0_refs c ON c.id=a.type_no AND c.type=a.type
LEFT JOIN 0_comments d ON d.id=a.type_no AND d.type=a.type
LEFT JOIN 0_debtor_trans e ON e.trans_no=a.type_no AND e.type=a.type 

LEFT JOIN  0_debtor_trans_details f ON f.debtor_trans_no=e.trans_no and f.debtor_trans_type=10 and e.type=10 
LEFT JOIN 0_stock_master g ON g.stock_id=f.stock_id 
LEFT JOIN 0_stock_category h ON h.category_id=g.category_id

WHERE a.account = 1200 AND a.amount <> 0 AND b.debtor_no = $customer_id AND a.tran_date >= '$from' 
AND a.tran_date <= '$to' 

GROUP BY a.type,a.type_no

ORDER BY a.tran_date  ) AS MyTable
GROUP BY TYPE,type_no
ORDER BY tran_date";

    return db_query($sql);
}

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root,$systypes_array;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $customer_id = $_POST['PARAM_2'];
    $comments = "";
    $destination =$_POST['PARAM_3'];


    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "P";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Customer Report - Service wise'), "Customer Report - Service wise", "A4", 9, $orientation);

    $params = array(0 => $comments,
        1 => array('text' => trans('Customer'), 'from' => $customer_id, 'to' => ""),
    );

    $cols = array(0, 50, 100, 160, 220, 290,320,380,420,460,520);//6
    $headers = array(trans('Reference'), trans('Type'), trans('Date'), trans('Service'),
        trans('Category'),trans('Qty'),trans('Payment Status'),trans('Debit'),trans('Credit'),trans('Memo'));//5
    $aligns = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');



    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($customer_id,$from,$to);

    $debit_total = 0;
    $credit_total = 0;

    while ($trans = db_fetch($transactions)) {

        $debit_total += $trans['debit'];
        $credit_total += $trans['credit'];

        $rep->TextCol(0, 1, $trans['reference']);
        $rep->TextCol(1, 2, $systypes_array[$trans['type']]);
        $rep->TextCol(2, 3, $trans['tran_date']);
        $rep->TextCol(3, 4, $trans['item_desc']);
        $rep->TextCol(4, 5, $trans['category']);
        $rep->TextCol(5, 6, $trans['quantity']);
        $rep->TextCol(6, 7, $trans['payment_status']);
        $rep->AmountCol(7, 8, $trans['debit'],2);
        $rep->AmountCol(8, 9, $trans['credit'],2);
        $rep->TextCol(9, 10, $trans['memo_']);

//        get_open_balance()

        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }



    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1,'');
    $rep->TextCol(1, 2, '');
    $rep->TextCol(2, 3, '');
    $rep->TextCol(3, 4, '');
    $rep->TextCol(4, 5, '');
    $rep->TextCol(5, 6, '');
    $rep->TextCol(6, 7, '');
    $rep->AmountCol(7, 8, $debit_total,2);
    $rep->AmountCol(8, 9, $credit_total,2);
    $rep->TextCol(9, 10, '');


    $rep->Line($rep->row - 5);
    $rep->Font();


    hook_tax_report_done();

    $rep->End();
}


