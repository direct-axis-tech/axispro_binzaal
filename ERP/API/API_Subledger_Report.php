<?php
include_once("AxisPro.php");
include_once("PrepareQuery.php");

Class API_Subledger_Report
{
    public function subledger_report()
    {
       /* if($_POST['PARAM_6']=='csv')
        {
            $this->export_csv($_POST);
        }*/

        if($_POST['PARAM_6']=='0')
        {
            $this->export_pdf($_POST);
        }
    }

    public function export_pdf($data)
    {

        $mpdf = new \Mpdf\Mpdf(['setAutoTopMargin' => 'stretch','default_font_size' => 7,'default_font' => 'dejavusans']);
        $mpdf->SetDisplayMode('fullpage');


        $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
        $stylesheet = file_get_contents('style.css');
        $mpdf->WriteHTML($stylesheet, 1);
        //$mpdf->SetColumns(1, 'J', 9);
        $mpdf->list_align_style = 'L';
        $mpdf->falseBoldWeight = 2;
        /*-------------------------GET CAT NAME---------------*/
        $f_year = kv_get_current_fiscalyear();
        $begin1 = $f_year['begin'];
        $today1 =$f_year['end'];
        $prev_balance = get_gl_balance_from_to($begin1, $today1, $_POST["PARAM_2"], '', '');

        $trans = get_gl_transactions($_POST['PARAM_0'],$_POST['PARAM_1'], -1, $_POST["PARAM_2"], '', '',null,null,null,null,'','');


        /*----------------------------END----------------------*/

        $mpdf->setHeader('
<div>
<div align="right">
<span style="font-size: 9pt !important;font-weight: normal !important;">EQC - BOULEVARD BUSINESSMEN SERVICE</span><br/>
</div>
<div align="left" >
<span style="font-size:12pt;">Subledger Report</span><br/>
<span>Print Out Date : '.date('d-m-Y h:i:s').'</span><br/>
<label style="font-weight: normal;">Fiscal Year : '. date('d-m-Y',strtotime($begin1)).' - '.date('d-m-Y',strtotime($today1)) .'</label><br/>
<label style="font-weight: normal;">Period : '.$data['date_from'].' - '.$data['date_to'].'</label><br/>
<label style="font-weight: normal;">Category : </label><br/>
<label></label><br/>
</div>




 </div>

<table style="border-top: 1px solid black;">
                <tr>
                    <td style="width:20%;">Type</td>
                    <td style="width:20%;">Ref#</td>
                    <td style="width:20%;">Date</td>
                    <td style="width:20%;">Subledger</td>
                    <td style="width:20%;">Debit</td>
                    <td style="width:20%;">Credit</td>
                    <td style="width:10%;">Balance</td>
                    </tr> </table>
');

      /*  while ($myrow=db_fetch($trans))
        {

        }*/

        $content="<table>
              ";



        $content.=' </table>';

      /*  $content.='<table style="border-top: 1px solid black;width:100%;"><tr >
 
 <td style="font-weight: bold;">TOTAL :</td>
                         <td style="width: 39%;"></td>
                         <td style="font-weight: bold;">'. number_format($tot_service_chrge,2).'</td>
                         <td style="font-weight: bold;">'. number_format($tot_govt_fee_disp,2).'</td>
                          <td style="width: 25%;"></td>
                         <td style="font-weight: bold;">'.number_format($line_tot,2).'</td>
 
                        
                         
                         </tr></table>';*/

        $mpdf->WriteHTML($content);

        $mpdf->setFooter('<div style="font-weight: normal; font-size: 12px">Powered by - &copy; www.axisproerp.com</div>');


        $mpdf->Output("Category_Report.pdf", \Mpdf\Output\Destination::INLINE);
    }


}