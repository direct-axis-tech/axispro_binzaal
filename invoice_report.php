<?php include "header.php" ?>
<?php //$isPDCWindow = true;
$customer_types = array_keys($GLOBALS['customer_types']);
$today = Today();
$one_month_before = date('d-M-Y', strtotime("-1 month", strtotime($today)));

$customers = db_query(
    "SELECT debtor_no, debtor_ref, `name` FROM 0_debtors_master",
    $err_string
)->fetch_all(MYSQLI_ASSOC);
?>
<?php if($_SESSION['wa_current_user']->can_access_page('SA_INVOICEREPORT')){?>
<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="col-md-12">
                        <!-- form card cc payment -->
                        <div class="card">
                            <div class="card-body" id="cashier-form-div">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3>Invoice Report</h3>
                                    </div>
                                </div>

                                <div class="row">
                                 <div class="col-lg-12">
                                    <form id="form-filter" class="form-horizontal" style="width:100%;">
                                       <table style="width:100%;margin-bottom:10px;">
                                           <tr>
                                            <td width="10%">
                                                <input type="text" class="form-control" placeholder="Reference No." id="reference_no_filter" value="<?php echo ($_GET['reference_no']!='') ? $_GET['reference_no']: '';?>">
                                            </td>
                                            <td width="10%">
                                                <input type="text" class="form-control" placeholder="# from" id="trans_no_from_filter" value="<?php echo ($_GET['trans_no_from']!='') ? $_GET['trans_no_from']: '1';?>">
                                            </td>
                                            <td width="10%">
                                                <input type="text" class="form-control" placeholder="# to" id="trans_no_to_filter" value="<?php echo ($_GET['trans_no_to']!='') ? $_GET['trans_no_to']: '999999';?>">
                                            </td>
                                            <td width="10%">
                                                <input type="text" class="form-control ap-datepicker" placeholder="Date from" id="trans_date_from_filter" readonly value="<?php echo ($_GET['trans_date_from']!='') ? sql2date($_GET['trans_date_from']): $one_month_before;?>">
                                            </td>
                                            <td width="10%">
                                                <input type="text" class="form-control ap-datepicker" placeholder="Date to" id="trans_date_to_filter" readonly value="<?php echo ($_GET['trans_date_to']!='') ? sql2date($_GET['trans_date_to']): $today;?>">
                                            </td>
                                            <td width="10%">
                                                <div id="customerSelect">
                                                    <select class="form-control" name="customer_id" id="customer_id_for_filter">
                                                        <option value="" selected disabled>Select a Customer</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td width="10%">
                                                <button type="button" name="submit_filter" id="submit_filter" class="btn btn-info" onclick="reloadDatatable();">
                                                    Submit
                                                </button>
                                            </td>
                                            <td width="1%">
                                                <div class="dropdown show" id="main_export_div" data-toggle-second="tooltip">
                                                  <a class="btn btn-primary dropdown-toggle btn-block" href="#" role="button" id="dropdownMenuLinkExportReport" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Export Report to
                                                </a>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLinkExportReport">
                                                    <a class="dropdown-item" target="_blank" href="#" id="export_to_excel_btn"><i class="fa fa-file-excel"></i>Excel</a>
                                                    <a class="dropdown-item" onclick="return confirm('Are you sure to export to PDF? We recommend you to choose EXCEL because PDF report may be congested due to large data and columns')" target="_blank" href="#" id="export_to_pdf_btn"><i class="fa fa-file-pdf"></i>Pdf</a>
                                                </div>
                                            </div>
                                        </td>
                                  </tr>
                              </table>
                          </form>
                          <table id="invoice_report" class="table dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <!-- <th>#</th> -->
                                    <th>Reference</th>
                                    <th width="10%">Date</th>
                                    <th width="8%">Time</th>
                                    <th>Customer</th>
                                    <th>Payment Status</th>
                                    <th>Customer Type</th>
                                    <th>Total Amount</th>
                                    <th>Cash</th>
                                    <th>Debit/Credit Card</th>
                                    <th>Bank Transfer</th>
                                    <th>Others</th>
                                    <th>Amount Received</th>
                                    <th>Balance Amount</th>
                                    <!-- <th width="20%">Receipts & Payment Methods</th> -->
                                    <th>GL</th>
                                    <th>Print</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="6" style="text-align:right;">Total:</th>
                                    <th id="total_sum"></th>
                                    <th id="pay_cash_sum"></th>
                                    <th id="pay_creditcard_sum"></th>
                                    <th id="pay_bank_sum"></th>
                                    <th id="pay_other_sum"></th>
                                    <th id="total_received_sum"></th>
                                    <th id="total_balance_sum"></th>
                                    <th id="payment_status"></th>
                                    <th id="customer_type"></th>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>

            </div>
        </div>
        <!-- /card  -->

    </div>
</div>









</div>

<!-- end:: Content -->
</div>
</div>
</div>
<?php
}else{
?>
<div>
    <?php
        echo "<center><br><br><br><b>";
        echo trans("The security settings on your account do not permit you to access this function");
        echo "</b>";
        echo "<br><br><br><br></center>";
    ?>
</div>
<?php
}
?>
<?php include "footer.php"; ?>
<script type="text/javascript">
assign_export_link = () => {
 let dropdownMenuLinkExportReport = document.getElementById("dropdownMenuLinkExportReport");
 let exportMainDiv = document.getElementById("main_export_div");
 let pdf_btn = document.getElementById("export_to_pdf_btn");
 let excel_btn = document.getElementById("export_to_excel_btn");
 excel_btn.href = pdf_btn.href = "javascript:;";
 let 
 export_title,
 export_link = '<?= erp_url('/ERP/API/hub.php?method=export_invoice_report') ?>',
 reference_no = $('#reference_no_filter').val(),
 trans_date_from = $('#trans_date_from_filter').val(),
 trans_date_to = $('#trans_date_to_filter').val(),
 trans_no_from = $('#trans_no_from_filter').val(),
 trans_no_to = $('#trans_no_to_filter').val();
 customer_id = $("#customer_id_for_filter").val();
 customer_type = $("#customer_type_for_filter").val();
 let d1 = new Date(trans_date_from), d2 = new Date(trans_date_to);
 if(d1 > d2){
    let temp = d2;
    d2 = d1;
    d1 = temp;
 }

 if(Math.round((d2 - d1)/(1000*60*60*24))<=31){
    export_link += `&&reference_no=${reference_no}&&trans_no_to=${trans_no_to}&&trans_date_from=${trans_date_from}&&trans_date_to=${trans_date_to}&&trans_no_from=${trans_no_from}&&customer_id=${customer_id}`;
    pdf_btn.href = export_link+'&&export_type=pdf'; 
    excel_btn.href = export_link+'&&export_type=excel';   
    dropdownMenuLinkExportReport.classList.remove("disabled","export_disabled");
    pdf_btn.classList.remove("disabled");
    excel_btn.classList.remove("disabled");
    export_title='Export Report to Excel / PDF';
}else{
   dropdownMenuLinkExportReport.classList.add("disabled","export_disabled");
   pdf_btn.classList.add("disabled");
   excel_btn.classList.add("disabled");
   export_title = 'Sorry !!! You can export maximum 31 Days\' data';

   }

   // exportMainDiv.setAttribute('title', export_title);
   exportMainDiv.setAttribute('data-original-title', export_title);
}


$(document).ready(function(){
    assign_export_link();
    initializeCustomersSelect2('#customer_id_for_filter');
    invoice_report_table = $('#invoice_report').dataTable({
    dom: 'Bfrtip',
    stateSave: true,
    "bLengthChange": false,
    "ordering": false,
    buttons: [
    {
        text: '<i class="menu-icon flaticon-refresh"></i>',
            // className:'btn btn-secondary',
            action: function ( e, dt, node, config ) {
                // dt.ajax.reload();
                $('#form-filter')[0].reset();
                // $('#customer_id_for_filter').trigger('change');
                // $('#customer_id_for_filter').val(null).trigger('change');
                $('.select2').trigger("change");
                // $('#supplier_id_for_filter').trigger("change");
                invoice_report_table.api().search( '' ).columns().search( '' ).draw();
                invoice_report_table.api().ajax.reload();
            }
        },
        {
            extend:'colvis',
            text:'<i class="fa fa-eye"></i>'
            
        }
        ],
        // "order": [[ 5, "desc" ]],
        // "columnDefs": [
        // { 
        //     "orderable": false, 
        //     "targets": [6,12] 
        // }
        // ],
        "bProcessing": true,
        "serverSide": true,
        // 'searching': false,
        "ajax":{
            url :route('API_Call', {method: 'get_invoice_list_for_datatable'}),
            type: "POST",
            "data": function ( data ) {
                data.reference_no = $('#reference_no_filter').val();
                data.trans_date_from = $('#trans_date_from_filter').val();
                data.trans_date_to = $('#trans_date_to_filter').val();
                data.trans_no_from = $('#trans_no_from_filter').val();
                data.trans_no_to = $('#trans_no_to_filter').val();
                data.customer_id = $('#customer_id_for_filter').val();
                data.customer_type = $('#customer_type_for_filter').val();

            // data.date = $('#date').val();
                // data.address = $('#address').val();
            },
            dataSrc: function ( data ) {
           total_sum = data.total_sum;
           pay_cash_sum = data.pay_cash_sum;
           pay_creditcard_sum = data.pay_creditcard_sum;
           pay_bank_sum = data.pay_bank_sum;
           pay_other_sum = data.pay_other_sum;
           total_balance_sum = data.total_balance_sum;
           total_received_sum = data.total_received_sum;
           payment_status = data.payment_status;
           customer_type = data.customer_type;

           return data.data;
         },    
            error: function(){
              $("#invoice_report_processing").css("display","none");
          }
      },
      drawCallback: function( settings ) {
        var api = this.api();
        // alert(total_sum);
        $('#total_sum').html(total_sum);
        $('#pay_cash_sum').html(pay_cash_sum);
        $('#pay_creditcard_sum').html(pay_creditcard_sum);
        $('#pay_bank_sum').html(pay_bank_sum);
        $('#pay_other_sum').html(pay_other_sum);
        $('#total_received_sum').html(total_received_sum);
        $('#total_balance_sum').html(total_balance_sum);
        $('#payment_status').html(payment_status);
        $('#customer_type').html(customer_type);

        }
  });

// $(".select2").select2();

$('#trans_no_from_filter,#reference_no_filter,#trans_date_from_filter,#trans_date_to_filter,#trans_no_to_filter,customer_id_for_filter').on("keyup change",function(){
    // invoice_report_table.ajax.reload();
    reloadDatatable();
});
});
function reloadDatatable(){
    assign_export_link();
    invoice_report_table.api().ajax.reload();
}

function monthsDiff(d1, d2) {
  let date1 = new Date(d1);
  let date2 = new Date(d2);
  let years = yearsDiff(d1, d2);
  let months =(years * 12) + (date2.getMonth() - date1.getMonth()) ;
  return months;
}
function yearsDiff(d1, d2) {
    let date1 = new Date(d1);
    let date2 = new Date(d2);
    let yearsDiff =  date2.getFullYear() - date1.getFullYear();
    return yearsDiff;
}
function initialiseDatepicker(){

    $('.ap-datepicker').datepicker({format: $('#date_format').val()});
}
</script>