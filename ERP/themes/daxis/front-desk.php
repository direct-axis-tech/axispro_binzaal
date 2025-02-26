<?php
$page_security = 'SA_OPEN';
$path_to_root = "../..";

include_once($path_to_root . "/includes/lang/language.inc");

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/themes/daxis/kvcodes.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
include_once($path_to_root . "/sales/includes/cart_class.inc");


$sql = "
CREATE TABLE IF NOT EXISTS `0_axis_front_desk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT '0',
  `token` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_customer` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_trn` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_ref` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";
db_query($sql);


if (isset($_POST['submit_options'])) {

    $customer_id = $_POST['customer_id'];
    $token = $_POST['token'];
    $display_customer = $_POST['display_customer'];
    $customer_mobile = $_POST['customer_mobile'];
    $customer_email = $_POST['customer_email'];
    $customer_trn = $_POST['customer_trn'];
    $customer_ref = $_POST['customer_ref'];
    $sql = "INSERT INTO 0_axis_front_desk 
(token,customer_id,display_customer,customer_mobile,customer_email,customer_trn,customer_ref) 
VALUES (
" . db_escape($token) . ",$customer_id,
" . db_escape($display_customer) . ",
" . db_escape($customer_mobile) . ",
" . db_escape($customer_email) . ",
" . db_escape($customer_trn) . ",
" . db_escape($customer_ref) . ")";

    db_query($sql, "could not insert to front desk");

    $id = db_insert_id();

    meta_forward($_SERVER['PHP_SELF'] . '?inserted=yes');
    //meta_forward($path_to_root."/voucher_print/token_print.php?token_id=$id&");

}

page(trans($help_context = "Front Desk"));

if (isset($_GET['inserted'])) {
    display_notification("Information saved successfully!");
}


start_form(true);
start_table(TABLESTYLE, "width='80%'");
table_section_title(trans("Customer Information"));

$customer_id = 1;
if(isset($_POST['customer_id']) && $_POST['customer_id'] != 1) {
    $customer_id = $_POST['customer_id'];
}

$_POST['display_customer'] = "";
$_POST['customer_email'] = "";
$_POST['customer_mobile'] = "";
$_POST['customer_trn'] = "";

if ($customer_id > 1) {
    $data = get_customer($_POST['customer_id']);
    $_POST['display_customer'] = $data['name'];
    $_POST['customer_email'] = $data['debtor_email'];
    $_POST['customer_mobile'] = $data['mobile'];
    $_POST['customer_trn'] = $data['tax_id'];
 }


/** Only for developer */
text_row(trans("Mobile:"), 'customer_mobile', null, 28, 100);
text_row(trans("Token No:"), 'token', null, 50, 100);
customer_list_row(trans("Customer:"), 'customer_id', $customer_id, false, true, false, true);
text_row(trans("Display Customer:"), 'display_customer', null, 50, 100);
text_row(trans("Email:"), 'customer_email', null, 28, 100);
text_row(trans("TRN:"), 'customer_trn', null, 28, 100);
text_row(trans("Company.:"), 'customer_ref', null, 28, 100);


//$Ajax->activate('_page_body');

end_table();
br();
submit_center('submit_options', trans("Create Token"), trans('Create Token'));

end_form();

br();
br();
end_page(); ?>
<script src="js/jquery.js"></script>
<script>
    $(document).on("change", "input[name='customer_mobile']", function () {
        var mob = $(this).val();
        $.ajax({
            url: "includes/ajax.php?GetCustomerByMobile="+1,
            type: "post",
            dataType: 'json',
            data: {
                mobile: mob
            },
            success: function (response) {
                if (response != false) {

                    $("#customer_id").val(response.debtor_no);
                    $("input[name='display_customer']").val(response.name);
                    $("input[name='customer_email']").val(response.debtor_email);
                    $("input[name='customer_trn']").val(response.tax_id);
                    $("input[name='customer_ref']").val(response.ref);
                }
            },
            error: function (xhr) {
                console.log(xhr);
            }
        });


    });
</script>
<style>
    .label {
        color: #6b6565;
        text-align: right;
    }
</style>