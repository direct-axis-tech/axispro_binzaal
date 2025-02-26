<?php
/**********************************************************************
Direct Axis Technology L.L.C.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_GLREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	GL Accounts Transactions
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_GL_transactions();

//----------------------------------------------------------------------------------------------------

function print_GL_transactions()
{
    global $path_to_root, $systypes_array;

    $dim = get_company_pref('use_dimension');
    $dimension = $dimension2 = 0;
    $hasMultipleDims = \App\Models\Accounting\Dimension::count() > 1;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $fromacc = $_POST['PARAM_2'];
    $toacc = $_POST['PARAM_3'];

    $memo = isset($_POST['Memo']) ? $_POST['Memo'] : '';
    $amount_min = isset($_POST['PARAM_10']) ? $_POST['PARAM_10'] : '';
    $amount_max = isset($_POST['PARAM_11']) ? $_POST['PARAM_11'] : '';
    $filter_type = isset($_POST['PARAM_12']) ? $_POST['PARAM_12'] : '';

    $subledger = $_POST['SUBLEDGER_CODE'];

    if(empty($toacc))
        $toacc = $fromacc;



    

    if ($dim == 2)
    {
        $dimension = $_POST['PARAM_4'];
        $dimension2 = $_POST['PARAM_5'];
        $comments = $_POST['PARAM_6'];
        $orientation = $_POST['PARAM_7'];
        $destination = isset($_POST['SUBLEDGER_CODE']) ? $_POST['PARAM_13'] : $_POST['PARAM_8'];
    }
    elseif ($dim == 1)
    {
        $dimension = $_POST['PARAM_4'];
        $comments = $_POST['PARAM_5'];
        $orientation = $_POST['PARAM_6'];
        $destination = isset($_POST['SUBLEDGER_CODE']) ? $_POST['PARAM_13'] : $_POST['PARAM_7'];
    }
    else
    {
        $comments = $_POST['PARAM_4'];
        $orientation = $_POST['PARAM_5'];
        $destination = isset($_POST['SUBLEDGER_CODE']) ? $_POST['PARAM_13'] : $_POST['PARAM_6'];
    }
    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    // $orientation = ($orientation ? 'L' : 'P');
    
    $orientation = 'L';
    $page = 'A4';
    $margins = [
        'top' => 30,
        'bottom' => 30,
        'left' => 20,
        'right' => 15,
    ];
    $rep = new FrontReport(trans('GL Account Transactions'), "GLAccountTransactions", $page, 9, $orientation, $margins);
    $dec = user_price_dec();

    // Coulumn Defenitions
    {
        $columns = [
            [
                "key" => 0,
                "align" => 'left',
                "width" => 63,
                "title" => trans('Type')
            ],
            [
                "key" => 1,
                "align" => 'left',
                "width" => 35,
                "title" => trans('Ref')
            ],
            [
                "key" => 2,
                "align" => 'left',
                "width" => 25,
                "title" => trans('#')
            ],
            [
                "key" => 3,
                "align" => 'left',
                "width" => 43,
                "title" => trans('Date')
            ],
        ];

        if ($hasMultipleDims) {
            if ($dim == 2) {
                $columns = array_merge(
                    $columns,
                    [
                        [
                            "key" => 4,
                            "align" => 'left',
                            "width" => 55,
                            "title" => trans('Dimension') . " 1"
                        ],
                        [
                            "key" => 5,
                            "align" => 'left',
                            "width" => 55,
                            "title" => trans('Dimension') . " 2"
                        ]
                    ]
                );
            } else if ($dim == 1) {
                $columns[] = [
                    "key" => 4,
                    "align" => 'left',
                    "width" => 55,
                    "title" => trans('Dimension')
                ];
            }
        }

        $columns = array_merge(
            $columns,
            [
                [
                    "key" => 6,
                    "align" => 'left',
                    "width" => 150,
                    "title" => trans('Person/Item')
                ],
                [
                    "key" => 7,
                    "align" => 'right',
                    "width" => 37,
                    "title" => trans('Debit')
                ],
                [
                    "key" => 8,
                    "align" => 'right',
                    "width" => 37,
                    "title" => trans('Credit')
                ],
                [
                    "key" => 9,
                    "align" => 'right',
                    "width" => 37,
                    "title" => trans('Balance')
                ],
                [
                    "key" => 'spacer_0',
                    "align" => 'center',
                    "width" => 2,
                    "title" => ''
                ],
                [
                    "key" => 10,
                    "align" => 'left',
                    "width" => 45,
                    "title" => 'Memo'
                ],
                // [
                //     "key" => 11,
                //     "align" => 'left',
                //     "width" => 50,
                //     "title" => 'Cheque Date'
                // ],
                // [
                //     "key" => 12,
                //     "align" => 'left',
                //     "width" => 50,
                //     "title" => 'Cheque No'
                // ],
                [
                    "key" => 13,
                    "align" => "center",
                    "width" => 25,
                    "title" => "Color"
                ]
            ]
        );
    }

    $colInfo = new ColumnInfo(
            $columns,
            $page,
            $orientation,
            $margins
        );

    if ($dim == 2)
    {
        $params =   array( 	0 => $comments,
            1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
            2 => array('text' => trans('Accounts'),'from' => $fromacc,'to' => $toacc),
            3 => array('text' => trans('Dimension')." 1", 'from' => get_dimension_string($dimension),
                'to' => ''),
            4 => array('text' => trans('Dimension')." 2", 'from' => get_dimension_string($dimension2),
                'to' => ''));
    }
    elseif ($dim == 1)
    {
        $params =   array( 	0 => $comments,
            1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
            2 => array('text' => trans('Accounts'),'from' => $fromacc,'to' => $toacc),
            3 => array('text' => trans('Dimension'), 'from' => get_dimension_string($dimension),
                'to' => ''));
    }
    else
    {
        $params =   array( 	0 => $comments,
            1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
            2 => array('text' => trans('Accounts'),'from' => $fromacc,'to' => $toacc));
    }

    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();

    $accounts = get_gl_accounts($fromacc, $toacc);

    while ($account=db_fetch($accounts))
    {
        if (is_account_balancesheet($account["account_code"]))
            $begin = "";
        else
        {
            $begin = get_fiscalyear_begin_for_date($from);
            if (date1_greater_date2($begin, $from))
                $begin = $from;
            $begin = add_days($begin, -1);
        }

        $sl_type = is_subledger_account($account["account_code"]);
        $person_type = get_subledger_person_type($sl_type);

        $prev_balance = get_gl_balance_from_to(
            $begin,
            $from,
            $account["account_code"],
            $dimension,
            $dimension2,
            $subledger,
            $person_type,
            $filter_type,
            $amount_min,
            $amount_max,
            $memo
        );
        $trans = get_gl_transactions(
            $from,
            $to,
            -1,
            $account['account_code'],
            $dimension,
            $dimension2,
            $filter_type,
            $amount_min,
            $amount_max,
            $person_type,
            $subledger,
            $memo
        );


        $rows = db_num_rows($trans);
        if ($prev_balance == 0.0 && $rows == 0)
            continue;
        $rep->Font('bold');
        $rep->TextCol($colInfo->x1(0), $colInfo->x2(3),	$account['account_code'] . " " . $account['account_name'], -2);
        $rep->TextCol($colInfo->x2(3), $colInfo->x1(6), trans('Opening Balance'));
        if ($prev_balance > 0.0)
            $rep->AmountCol($colInfo->x1(7), $colInfo->x2(7), abs($prev_balance), $dec);
        else
            $rep->AmountCol($colInfo->x1(8), $colInfo->x2(8), abs($prev_balance), $dec);
        $rep->Font();
        $total = $prev_balance;
        $rep->Line($rep->row - 6);
        $rep->NewLine(2);
        if ($rows > 0)
        {
            $tot_debit=0;
            $tot_credit=0;
            $subledger_disp_label=[];
            $sub_leder_credit_tot_disp='';
            $sub_led_debit_tot_disp='';
            $sub_legd_text='';
            $total_displ_flag='';
            $test_arr=[];
            $flag='';
            $k=1;
            while ($myrow=db_fetch($trans))
            {


                $total += $myrow['amount'];


                if(!empty($myrow['axispro_subledger_code']))
                {
                    array_push($test_arr,$myrow['axispro_subledger_code']);
                    if (!in_array($myrow['axispro_subledger_code'], $subledger_disp_label)) {
                        $flag='1';
                        $rep->TextCol($colInfo->x1(0), $colInfo->x2(0), $myrow['sub_ledger_name'], -2);
                        $rep->NewLine();
                        $total_displ_flag='1';

                        if ($myrow['amount'] > 0.0) {
                            //$sub_leder_credit_tot_disp = abs($myrow['amount']);
                        }
                        else
                        {
                            //$sub_led_debit_tot_disp= abs($myrow['amount']);
                        }
                    }
                }

                $rep->TextCol($colInfo->x1(0), $colInfo->x2(0), $systypes_array[$myrow["type"]], -2);
                $rep->TextCol($colInfo->x1(1), $colInfo->x2(1), $myrow['reference'], -2);
                $rep->TextCol($colInfo->x1(2), $colInfo->x2(2),	$myrow['type_no'], -2);
                $rep->DateCol($colInfo->x1(3), $colInfo->x2(3),	$myrow["tran_date"], true);

                if ($hasMultipleDims) {
                    if ($dim >= 1)
                        $rep->TextCol($colInfo->x1(4), $colInfo->x2(4),	get_dimension_string($myrow['dimension_id'], false, ' ', true));
                    if ($dim > 1)
                        $rep->TextCol($colInfo->x1(5), $colInfo->x2(5),	get_dimension_string($myrow['dimension2_id'], false, ' ', true));
                }

                $txt = payment_person_name($myrow["person_type_id"],$myrow["person_id"], false) ?: $myrow['person_name'];
                if (empty($txt)) {
                    $txt = $myrow['sub_ledger_name'];
                }

                $rep->TextCol($colInfo->x1(6), $colInfo->x2(6),	$txt, -2);
                if ($myrow['amount'] > 0.0)
                {
                    $rep->AmountCol($colInfo->x1(7), $colInfo->x2(7), abs($myrow['amount']), $dec);
                    $rep->TextCol($colInfo->x1(8), $colInfo->x2(8), '');
                }
                else
                {
                    $rep->TextCol($colInfo->x1(7), $colInfo->x2(7), '');
                    $rep->AmountCol($colInfo->x1(8), $colInfo->x2(8), abs($myrow['amount']), $dec);
                }
                $rep->TextCol($colInfo->x1(9), $colInfo->x2(9), number_format2($total, $dec));

                $memo = trim($myrow['transaction_id']);
                if (empty($memo) || preg_match('_^n/?a$_', strtolower($memo))) {
                    $memo = trim($myrow['memo_']);
                }
                if (empty($memo)) {
                    $memo = trim(get_comments_string($myrow['type'], $myrow['type_no']));
                }

                $rep->TextCol($colInfo->x1(10), $colInfo->x2(10), $memo);
                // $rep->TextCol($colInfo->x1(11), $colInfo->x2(11), sql2date($myrow['chq_date']));
                // $rep->TextCol($colInfo->x1(12), $colInfo->x2(12), $myrow['chq_no']);

                if (!empty($myrow['color_code'])) {
                    $rep->SetFillColor(...array_values(hexToRgb($myrow['color_code'])));
                    $rep->ColorCol($colInfo->x1(13), $colInfo->x2(13), $myrow['color_code']);
                }


                $rep->NewLine();
                $sub_legd_text=$myrow['axispro_subledger_code'];
                if($total_displ_flag==$myrow['axispro_subledger_code'])
                {
                    $disp='0';
                }
                else
                {
                    $disp='1';
                }

             /* if((in_array($myrow['axispro_subledger_code'],$test_arr)) && $k!='0' ) {
                    $debitandcredi_sum = debit_and_credi_sum($myrow['axispro_subledger_code'], 1);

                           $rep->TextCol(4, 6, $sub_legd_text.'/'.$flag);
                           $rep->TextCol(7, 8, $debitandcredi_sum);
                           $rep->TextCol(8, 9, $sub_leder_credit_tot_disp, -2);
                           $rep->NewLine(1);
                }*/


                if ($rep->row < $rep->bottomMargin + $rep->lineHeight)
                {
                    $rep->Line($rep->row - 2);
                    $rep->NewPage();
                }

                /*------------------------------TOTAL---------*/

                if ($myrow['amount'] > 0.0)
                {
                    $tot_debit=$tot_debit+abs($myrow['amount']);
                }
                else
                {
                    $tot_credit=$tot_credit+abs($myrow['amount']);
                }

                if(!in_array($myrow['axispro_subledger_code'],$subledger_disp_label))
                {
                    array_push($subledger_disp_label,$myrow['axispro_subledger_code']);
                }



                /*------------------------------END------------*/
                $total_displ_flag=$myrow['axispro_subledger_code'];
                $flag='';
                $k++;

            }

            $rep->NewLine();
            $rep->Line($rep->row + 9 + 4);
        }

        $rep->Font('bold');

        $rep->TextCol($colInfo->x2(3), $colInfo->x1(6), trans("Total"));
        $rep->AmountCol($colInfo->x1(7), $colInfo->x2(7), abs($tot_debit), $dec);
        $rep->AmountCol($colInfo->x1(8), $colInfo->x2(8), abs($tot_credit), $dec);
        $rep->NewLine();
        $rep->TextCol($colInfo->x2(3), $colInfo->x1(6),	trans("Ending Balance"));
        if ($total > 0.0)
            $rep->AmountCol($colInfo->x1(7), $colInfo->x2(7), abs($total), $dec);
        else
            $rep->AmountCol($colInfo->x1(8), $colInfo->x2(8), abs($total), $dec);
        $rep->Font();
        $rep->NewLine(3);
    }
    $rep->End();
}

/**
 * Converts the hexadecimal color code to rgb values
 *
 * @param string $hex
 * @return array an array containing r, g, b values;
 */
function hexToRgb($hex) {
    preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex, $result);
    return [
        "r" => intval($result[1], 16),
        "g" => intval($result[2], 16),
        "b" => intval($result[3], 16)
    ];
}