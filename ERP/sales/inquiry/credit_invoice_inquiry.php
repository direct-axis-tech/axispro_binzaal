<?php

$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/API/API_Call.php");

$canAccess = [
    "ALL" => user_check_access('SA_CRSALESREP_ALL'),
    "DEP" => user_check_access('SA_CRSALESREP_DEP'),
    "OWN" => user_check_access('SA_CRSALESREP_OWN')
];
$page_security = in_array(true, $canAccess, true) ? 'SA_OPEN' : 'SA_DENIED';
$filters = validate_user_input($canAccess);

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

if(isset($_POST['EXPORT'])) {
    $export_type = isset($_POST['export_type']) && $_POST['export_type'] == 'excel' ? 'excel' : 'pdf';
    print_report($export_type, $filters);
    exit();
}

page(trans($help_context = "Credit Invoice Report"), false, false, "", $js);

div_start('', null, false, 'w-100 p-3');
echo('<h1 class="h3 mb-5">Credit Invoice Report</h1>');
start_form();

start_table(TABLESTYLE_NOBORDER);
    start_row();
        customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);
        dimensions_list_cells(trans('Filter Invoice By Cost Center'),'invoice_cost_center',null,true,'--All--');
        if ($canAccess['ALL'] || $canAccess['DEP']) {
            users_list_cells2(trans("Select a user: "), 'user_id', $_POST['user_id'], true);
        }
        date_cells(trans("Date:"), 'trans_date', '', null);
    end_row();
    start_row();
        if ($canAccess['ALL']) {
            dimensions_list_cells(trans('Filter Users By Cost Center'),'user_cost_center',null,true,'--All--');
            bank_accounts_list_cells("Bank Account", 'bank_account', $_POST['bank_account'], false,"ALL");
        }
        submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');
        set_global_customer($_POST['customer_id']);
    end_row();
end_table();
br(2);

$sql = get_sql_for_credit_invoice_inquiry(...array_values($filters));

//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Date") => array('align' => 'center', 'fun' => 'fmt_date'),
    trans("Invoice Number") => array('align' => 'center'),
    trans("Customer") => array('align' => 'center'),
    trans("Contact Person") => array('align' => 'center'),
    trans("User") => array('align' => 'center'),
    trans("Tot. Inv. Amt.") => array('align' => 'center', 'fun' => 'fmt_price'),
    array('insert' => true, 'fun' => 'gl_link'),
    array('insert' => true, 'fun' => 'prt_link')
);


$table =& new_db_pager('trans_tbl', $sql, $cols);

$gross_total = db_query("SELECT SUM(inv_total) as `inv_total` from ($sql) as MyTable", "Transactions could not be calculated")->fetch_assoc();
$table->set_marker('check_redeemed', trans("Total Invoice Collection: " . price_format($gross_total ? $gross_total['inv_total'] : 0)));

$table->width = "80%";

display_db_pager($table);

div_start('', null, false, 'w-100 text-center');
echo array_selector("export_type", 'pdf', [
    "pdf"   => "Export to PDF",
    "excel" => "Export to EXCEL"
]);
br(2);
submit_cells('EXPORT', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');
div_end();

end_form();
div_end();

ob_start() ?>
<script type="text/javascript">
    $('.combo').select2();
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); 

end_page();

//------------------------------------------------------------------------------------------------

function print_report($export_type, $filters) {
    $sql = get_sql_for_credit_invoice_inquiry(...array_values($filters));
    $result = db_query($sql);
    $total = 0;
    $path_to_root = $GLOBALS['path_to_root'];

    if ($export_type == 'excel') {
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    } else {
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    }

    $orientation = 'P';
    $page = 'A4';
    $user_price_dec = user_price_dec();
    $params = [
        '',
        [
            "text" => trans("Date"),
            "from" => sql2date($filters['trans_date']),
            "to"   => ''
        ]
    ];
    if ($filters['user_id']) {
        $user = get_user($filters['user_id']);
        $params[] = [
            "text" => trans("User"),
            "from" => empty($user['real_name']) ? $user['user_id'] : "{$user['real_name']} ({$user['user_id']})",
            "to"   => ''
        ];
    }
    if ($filters['invoice_cost_center']) {
        $dep = get_dimension($filters['invoice_cost_center']);
        $params[] = [
            "text" => trans("Department"),
            "from" => $dep['name'],
            "to"   => ''
        ];
    }
    if ($filters['bank_account']) {
        $bank = get_bank_account($filters['bank_account']);
        $params[] = [
            "text" => trans("Bank Account"),
            "from" => "{$bank['account_code']} {$bank['bank_account_name']})",
            "to"   => ''
        ];
    }
    $columns = [
        [
            "key"   => "tran_date",
            "title" => _('Date'),
            "align" => "left",
            "width" => 20,
            "type" => "TextCol",
            "preProcess" => "sql2date"
        ],
        [
            "key"   => "reference",
            "title" => _('Invoice Number'),
            "align" => "left",
            "width" => 20,
            "type" => "TextCol"
        ],
        [
            "key"   => "name",
            "title" => _('Name'),
            "align" => "left",
            "width" => 35,
            "type" => "TextCol"
        ],
        [
            "key"   => "display_customer",
            "title" => _('Contact Person'),
            "align" => "left",
            "width" => 35,
            "type" => "TextCol"
        ],
        [
            "key"   => "user_id",
            "title" => _('User'),
            "align" => "left",
            "width" => 35,
            "type" => "TextCol"
        ],
        [
            "key"   => "inv_total",
            "title" => _('Total'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 30
        ]
    ];

    $colInfo = new ColumnInfo($columns, $page, $orientation);
    $rep = new FrontReport(trans('Credit Invoice Report'), "credit_invoice_report_" . random_id(64), $page, 9, $orientation);
    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();
    while($row = db_fetch_assoc($result)) {
        $total += $row['inv_total'];
        foreach ($columns as $col) {
            $_key = $col['key'];
            $_data = isset($col['preProcess']) 
                ? $col['preProcess']($row[$_key])
                : $row[$_key];
            
            $_type = $col['type'];
            isset($col['additionalParam'])
                ? $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data,
                    ...$col['additionalParam']
                ) : $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data
                );
        }
        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }
    $rep->NewLine();
    $rep->Line($rep->row + 14);
    $rep->TextCol(
        $colInfo->x1('tran_date'),
        $colInfo->x2('user_id'),
        'Total'
    );
    $rep->AmountCol(
        $colInfo->x1('inv_total'),
        $colInfo->x2('inv_total'),
        $total,
        $user_price_dec
    );
    $rep->End();

}

function check_redeemed($row)
{
    return false;
}

function gl_link($row) {
    return get_gl_view_str($row["type"], $row["trans_no"]);
}

function prt_link($row){
    return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);
}

function fmt_date($row){
    return sql2date($row['tran_date']);
}

function fmt_price($row){
    return price_format($row['inv_total']);
}

function validate_user_input($canAccess) {
    $userDateFormat = getDateFormatInNativeFormat();
    $metadata = [
        [
            "key"   => 'trans_date',
            "rules" => [
                'nullable',
                [
                    "name" => "date",
                    "param" => [
                        $userDateFormat
                    ]
                ]
            ],
            "message" => "Please provide a valid date"
        ],
        [
            "key"       => "user_id",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid user"
        ],
        [
            "key"       => "customer_id",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid customer"
        ],
        [
            "key"       => "bank_account",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid bank account"
        ],
        [
            "key"       => "invoice_cost_center",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid cost center of invoice"
        ],
        [
            "key"       => "user_cost_center",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid cost center of user"
        ],
    ];

    $filter_keys = array_column($metadata, 'key');

    /** Check if filters are set using query parameters and shift them to post variable */
    foreach ($filter_keys as $filter) {
        if(isset($_GET[$filter]) && !isset($_POST[$filter])) {
            $_POST[$filter] = $_GET[$filter];
        }
    }

    /** validate the data */
    $api = new API_Call();
    $validator = $api->validate(array_column($metadata, "rules", "key"), $_POST);
    $validData = $validator->data;
    
    /** if having any invalid data display the proper error message and exit */
    if($validator->fails) {
        $errMessages = array_column($metadata, "message", "key");
        foreach($validator->invalid as $filter) {
            display_error($errMessages[$filter]);
        }
        display_footer_exit();
    }

    /** ensure all parameters are set if not set so that we can blindly access the index without checking */
    foreach($filter_keys as $filter) {
        if(!isset($validData[$filter])){
            $validData[$filter] = null;
        }
    }

    /** enforce the access control for the user and set or unset the variables that the user is not having access to */
    if(!$canAccess['ALL']) {
        $validData['bank_account']  = null;

        if ($canAccess['DEP']) {
            $validData['user_cost_center'] = $_SESSION['wa_current_user']->default_cost_center;
        } else {
            $validData['user_id'] = $_SESSION['wa_current_user']->user;
        }
    }

    /** if trans date is not set set to today date by default */
    if (!$validData['trans_date']){
        $validData['trans_date'] = Today();
    }

    /** repopulate the POST variable */
    foreach($validData as $key => $data) {
        $_POST[$key] = $data;
    }

    /** convert the trans date to mysql date format for us to work with */
    $validData['trans_date'] = DateTime::createFromFormat($userDateFormat, $validData['trans_date'])
        ->format(DB_DATE_FORMAT);

    /** returns the validated data in the correct sort order */
    return array_merge(array_flip($filter_keys), $validData);
}

//------------------------------------------------------------------------------------------------