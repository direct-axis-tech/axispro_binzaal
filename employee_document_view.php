<?php include "header.php" ;
$today = Today();
$sql = "SELECT * FROM 0_employees";
$result = db_query($sql);

            $all_employees = [];
            while ($myrow = db_fetch($result)) {

                $all_employees[] = $myrow;

            }
$sql1 = "SELECT * from 0_document_types where 1=1";
$res = db_query($sql1);
$all_category=[];
while($row = db_fetch($res)){
    $all_category[]= $row;
}
?>

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
                                            <h4>Employees Document View</h4>
                                        </div>
                                    </div>

                                    <div class="row my-5">
                                        <div class="col-lg-12">
                                            <form id="form-filter" class="form-horizontal" style="width:100%;">
                                                <table id="filter_table" style="width:100%;margin-bottom:10px;">
                                                    <tr>
                                           
                                                        <td id="" width="8%">
                                                        <label for="fname">Employee:</label><br>
                                                            <select class="form-control" style="width:100%;" id="employee">
                                                            <option value="">--All Employees</option>
                                                            <?php foreach ($all_employees as $value) { ?>

                                                                <option value="<?php echo $value['id'] ;?>"><?php echo $value['name'] ?></option>
                                                                <?php } ?> 
                                                            </select>
                                                        </td>

                                                        <td id="" width="6%">
                                                        <label for="fname">Category:</label><br>
                                                            <select class="form-control" style="width:100%;" id="category_type">
                                                            <option value="">--All Category Type</option>
                                                            <?php foreach ($all_category as $data) { ?>

                                                                <option value="<?php echo $data['id'] ;?>"><?php echo $data['name'] ?></option>
                                                                <?php } ?> 
                                                            </select>
                                                        </td>

                                                        <td width="8%">
                                                        <label for="fname">Created Date:</label><br>
                                                            <input type="text" class="form-control ap-datepicker" placeholder="IssuedDate" id="created_date">
                                                            
                                                        </td>
                                                        
                                                        <td width="7%">
                                                        <label for="fname">Expire in:</label><br>
                                                            <select class="form-control" style="width:100%;" id="expire_on">
                                                                <option value="" selected disabled>--Select</option>
                                                                <option value="1">1 month</option>
                                                                <option value="2">2 month</option>
                                                                <option value="6">6 month</option>
                                                                <option value="12">12 month</option>
                                                            </select>
                                                        </td>
                                                
                                                        <td width="15%">
                                                            <div class="dropdown show" id="main_export_div" data-toggle-second="tooltip">
                                                                
                                                                <input class="btn btn-primary mx-5 mt-8" type="button" value="Search" id="search">
                                                                
                                                            </div>

                                                        </td>
                                                    </tr>
                                                </table>
                                            </form>
                                            <table id="customer_docs_table" class="table dataTable table-responsive" width="100%" cellspacing="0">
                                                <thead>
                                                <tr>
                                                    <th style="min-width: 250PX" data-field="stock_id">EMPLOYEE NAME</th>
                                                    <th style="min-width: 250px" data-field="invoice_date">DOCUMENT TYPE</th>
                                                    <th style="min-width: 200px" data-field="service">ISSUED ON</th>
                                                    <th style="min-width: 400px" data-field="category">EXPIRE ON</th>
                                                    <th style="min-width: 400px" data-field="customer">REFERENCE</th>
                                             
                                                    <th style="min-width: 200px" data-field="total_price">DOCUMENT
                                                    </th>
                                                    
                                          
                                                </tr>
                                                </thead>
                                                <tbody id="customer_docs_table_tbody"></tbody>

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


    <script src="assets/plugins/general/jquery/dist/jquery.js" type="text/javascript"></script>
<!--     <script src="assets/numpad/jquery.numpad.js"></script>-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="assets/plugins/general/popper.js/dist/umd/popper.js" type="text/javascript"></script>
    <script src="assets/js/config.js" type="text/javascript"></script>

    <script src="assets/plugins/general/sticky-js/dist/sticky.min.js" type="text/javascript"></script>

    <script src="assets/js/scripts.bundle.js" type="text/javascript"></script>
    <script src="assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"
            type="text/javascript"></script>

<!--    <script src="assets/js/pages/crud/forms/widgets/bootstrap-datepicker.js" type="text/javascript"></script>-->
    <script src="assets/js/pages/crud/forms/widgets/bootstrap-datepicker.js" type="text/javascript"></script>

    <script src="assets/js/axispro.js" type="text/javascript"></script>
    <script src="assets/js/jquery-dateformat.min.js" type="text/javascript"></script>
    <script src="assets/js/jquery.doubleScroll.js" type="text/javascript"></script>


    <!--    <script src="assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>-->
    <script src="assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
    <script src="assets/plugins/general/js/global/integration/plugins/sweetalert2.init.js" type="text/javascript"></script>
    <!-- <script src="https://cdn.datatables.net/fixedcolumns/3.3.2/js/dataTables.fixedColumns.min.js" type="text/javascript"></script> -->

    <!-- jQuery.NumPad -->
    <script type="text/javascript">
        

        $(document).ready(function(){
            $('#customer_docs_table').DataTable({
                "paging" :true,
            });
            // get_report();
        });
        

        // function get_report(){

        //     var employee_id = $('#employee').trigger('change').val();
           
            

        // }


        $('#search').click(function(){
            var employee_id = $('#employee').trigger('change').val();
            var category_type = $('#category_type').trigger('change').val();
            var created_date = $('#created_date').val();
            var expire_on = $('#expire_on').trigger('change').val();


            ajaxRequest({
                method: 'POST',
                dataType: 'json',
                url: route('API_Call', {method: 'get_employees_document_details'}),
                data: {
                    employee_id: employee_id,
                    category_type :category_type,
                    created_date:created_date,
                    expire_on:expire_on
                }
               
            }).done(function(data) {
                $('#customer_docs_table').dataTable().fnDestroy();
                var html = '';
                $.each(data, function(index, value) {
                    console.log(value['employee_name']);
                   
                    html +="<tr>";
                    html+="<td>" + value['employee_name'] + "</td>";
                    html+="<td>" + value['category_name'] + "</td>";
                    html+="<td>" + value['issued_on'] + "</td>";
                    html+="<td>" + value['expires_on'] + "</td>";
                    html+="<td>" + value['reference'] + "</td>";
                    html+="<td>" + `<a href="${url('/v3/download/' + value['file'])}" target="_blank">Download</a></td>`;

                    html +='</tr>';
                    // C:\xampp\htdocs\php74\estemarat\ERP\laravel\storage\app\docs\employees\1\YB0QuNTfP7ciJ3fwoabhGeO2UszicMJirhJyWHif.pdf
                    });

                    $("#customer_docs_table_tbody").html(html);
                    $("#customer_docs_table").dataTable({
                        // dom: 'Bfrtip',
                        // buttons: [

                        //                 {
                        //     extend: 'excelHtml5',
                        //     title: 'Employee_Document',
                        //     exportOptions: {
                        //             columns: [0,1,2,3,4]
                        //         }
                        // },
                        // {
                        //     extend: 'pdfHtml5',
                        //     title: 'Employee_Document',
                        //     exportOptions: {
                        //             columns: [0,1,2,3,4]
                        //         }
                        // }
                           
                        // ],
                        "paging" :true,
                        "pageLength": 10,
                    });
               
         
            })

        //    get_report();
         
        });
           
        

    </script>


<?php include "footer.php"; ?>