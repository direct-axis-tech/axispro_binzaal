

<?php ob_start(); ?>
<link href="assets/plugins/general/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css"/>
<?php 

$GLOBALS['__HEAD__'][] = ob_get_clean();

include "header.php";

error_reporting(E_ALL);

$application = isset($_GET['application']) ? $_GET['application'] : "dashboard";

//var_dump($application); die;


switch ($application) {

    case "dashboard" :

        $type = isset($_GET['dashboard']) ? $_GET['dashboard'] : '';

        if (in_array($type, ['cashier', 'cashier_new'])) {
            //Cashier
            include_once "cashier_window.php";
            break;
        }
        else {
            $dashboard = url('/dashboard');
            header("Location: $dashboard");
            break;
        }

        break;

    case "sales" :
        include_once "sales.php";
        break;
    case "reports" :
        include_once "reports.php";
        break;
    case "services" :
        include_once "services.php";
        break;

    case "finance" :
        include_once "finance.php";
        break;
    case "purchase" :
        include_once "purchase.php";
        break;

    case "settings" :
        include_once "settings.php";
        break;

    case "hrm" :
        include_once "hrm.php";
        break;
		
	case "hr_admin" :
        include_once "hr_admin.php";
        break;
    
    case "hr":
        include_once "hr.php";
        break;
        
    case "labour":
        include_once "labour.php";
        break;
        
    case "fixed_assets" :
        include_once "fixed_assets.php";
        break;

    default:
        $dashboard = url('/dashboard');
        header("Location: $dashboard");
        break;
}


?>

<?php ?>
<?php include "footer.php"; ?>


<script>
    $(function (e) {
        if($('#tbl-dashboard-cust-bal').length) {
            $('#tbl-dashboard-cust-bal').DataTable({
                dom: '<<"float-left"l><"float-right"f>><t>ip',
                order: [[ 4, "desc" ]],
                scrollY: "350px",
                scrollX: true,
                scrollCollapse: true,
                paging:         false
            });
        }

        $(".qms-stop-token").click(function() {
            var this_btn = $(this);
            var curr_token_id = $("#curr_token_id").val();
            var params = {method: 'end_token', token_id: curr_token_id};

            AxisPro.APICall('POST', QMS_API_ROOT + "deftoken/TOKEN_API.php", params, function (data) {
                this_btn.attr('disabled', 'disabled');
                this_btn.css('cursor', 'default');

                get_current_QMS_token();

                setTimeout(function () {
                    this_btn.removeAttr('disabled');
                    this_btn.css('cursor', 'pointer');
                }, 10000)
            });
        });

        $(".qms-call-next").click(function (e) {
            var this_btn = $(this);
            var qms_user = $("#qms_user").val();
            var params = {method: 'call_next', user_id: qms_user};

            AxisPro.APICall('POST', QMS_API_ROOT + "deftoken/TOKEN_API.php", params, function (data) {
                this_btn.attr('disabled', 'disabled');
                this_btn.css('cursor', 'default');

                get_current_QMS_token(function (data) {
                    if(data.msg === "NOT_FOUND" || data.msg === "NO_TOKENS") {
                        swal.fire(
                            'Warning!',
                            'No Tokens found!',
                            'warning'
                        );

                    }
                    else {
                        toastr.success("Token Called");
                        window.location.href = erpUrl("sales/sales_order_entry.php") + "?NewInvoice=0";
                    }
                });

                setTimeout(function () {
                    this_btn.removeAttr('disabled');
                    this_btn.css('cursor', 'pointer');
                }, 10000)
            });
        });

        $(".tptc_filter").change(function () {
            getTopTenCustomerTransaction();
        });


        $("#btn_load_manager_report").click(function() {
            var date = $("#inp_manager_report_date").val();
            window.location.href = url('/')+"?application=dashboard&filter_date="+date
        });

    });

    $(document).on("click", ".qms-recall", function () {
        var this_btn = $(this);
        var qms_user = $("#qms_user").val();
        var token = this_btn.data("token");
        var params = {method: 're_call', token_id: token, user_id: qms_user};

        AxisPro.APICall('POST', QMS_API_ROOT + "deftoken/TOKEN_API.php", params, function (data) {
            if (data.msg === "SUCCESS") {
                this_btn.attr("disabled","disabled");
                toastr.success("Token Recalled");
                this_btn.css('cursor', 'default');
            }

            get_current_QMS_token();
            setTimeout(function () {
                this_btn.removeAttr('disabled');
                this_btn.css('cursor', 'default');
            },5000)
        });
    });

    $(document).on("click", ".edit_or_print", function () {
        var this_btn = $(this);
        var invoice_number = this_btn.data('ref');
        var type = this_btn.data('type');

        $.ajax({
            url: erpUrl("sales/read_sales_invoice.php"),
            type: "post",
            dataType: 'JSON',
            data: {
                invoice_ref: invoice_number
            },
            success: function(response) {
                KTApp.unblockPage();

                if(response != 'false' && response.trans_no) {
                    toastr.success("Invoice found");
                    var edit_url = erpUrl("sales/customer_invoice.php")+"?ModifyInvoice="+response.trans_no;

                    if(response.payment_flag != "0" && response.payment_flag != "3") {
                        edit_url += "&is_tadbeer=1&show_items=ts";
                    }

                    if(response.payment_flag == "4" || response.payment_flag == "5") {
                        edit_url += "&is_tadbeer=1&show_items=tb";
                    }

                    if(type == 'edit') {
                        window.location.href = edit_url;
                    }
                    else{
                        var print_params = "PARAM_0="+response.trans_no+"-10&PARAM_1="+
                            response.trans_no+"-10&PARAM_2=&PARAM_3=0&PARAM_4=&PARAM_5=&PARAM_6=&PARAM_7=0&REP_ID=107";

                        var print_link = erpUrl("invoice_print") +"?"+print_params;

                        window.open(
                            print_link,
                            '_blank'
                        );
                    }
                }
                else {
                    toastr.error("No invoice found!");
                }
            },
            error: function(xhr) {}
        });
    });

    function getTopTenCustomerTransaction() {
        <?= user_check_access('SA_DSH_TOP_10_CUST') ? '' : 'return;' ?>
        var cat_id = $("#topf_cat_id").val();
        var from_date = $("#topf_from_date").val();
        var to_date = $("#topf_to_date").val();

        var params = {
            cat_id:cat_id,
            from_date:from_date,
            to_date:to_date,
        };

        AxisPro.APICall('GET', route('API_Call', {method: 'getTopTenCustomerTransaction'}), params, function (data) {
            var tbody_html = "";

            $.each(data, function (key, value) {
                tbody_html += "<tr>";
                tbody_html += "<td>"+value.customer_name+"</td>";
                tbody_html += "<td>"+value.qty+"</td>";
                tbody_html += "</tr>";
            });

            if(tbody_html.length === 0)
                tbody_html+="<tr><td colspan='2'>No Data Found</td></tr>";

            $("#tbl_dboard_top_ten_customers_tbody").html(tbody_html);
        });
    }

</script>
