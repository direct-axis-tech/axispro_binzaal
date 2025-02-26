
<form method="post" action="">

    <input type="text" name="password">
    <input type="submit" name="submit">

</form>


<?php


function run_delete_qry($tblname)
{
    $db = 'db_invoice_edit';
    global $conn;
    $company_number = 0;
    $sql = "delete from " . $company_number . "_" . $tblname;
    $result = mysql_db_query($db, $sql);
    if ($result != 1) {
        echo "Warning: SQL statement " . $sql . " failed\n";
        echo "with an error message of " . mysql_errno() . mysql_error(mysql_errno());
//        mysql_close($conn);

        return;
    }
    echo "Cleared " . $company_number . "_" . $tblname . "\n";
}

if(isset($_POST['submit'])) {


    if($_POST['password'] != 'daxis@1007') {
        echo "Unauthorized Access"; die;
    }


    $conn = mysql_connect('localhost', 'root', ''); //<---enter your host, user id and password for MySQL here
    if ($conn == null) {
        echo "Could not connect to MySQL with the host/username/password you provided. Try again.\n";
        exit();
    }
    $tbllist = array();
// Here's the magic - read the end of this file into an array that contains the table names
// you want to clear.
    $tbllist = split("\n", file_get_contents(__FILE__, NULL, NULL, __COMPILER_HALT_OFFSET__));

// Remove first entry - it's just a carriage return.
    unset($tbllist[0]);

// Process each table clearing it.
    foreach ($tbllist as $tbl) {
        if (substr($tbl, 0, 1) != "#") run_delete_qry($tbl);
    }
    echo "Finished clearing transaction tables\n";
    exit();
// A function to clear data from a table you specify

}

__HALT_COMPILER();
# Tables you want to clear go here
# Comments start with the pound sign
gl_trans
bank_trans
debtor_trans
debtor_trans_details
trans_tax_details
purch_orders
purch_order_details
sales_orders
sales_order_details
wo_issues
wo_issue_items
wo_manufacture
wo_requirements
supp_invoice_items
trans_tax_details
supp_allocations
grn_batch
grn_items
audit_trail
voided
refs
comments
cust_allocations
stock_moves
journal