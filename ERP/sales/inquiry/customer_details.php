<?php

$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/API/API_Call.php");

$page_security = 'SA_CUSTDETREP';
$filters = validate_user_input();

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

page(trans($help_context = "Customers Details Report"), false, false, "", $js);
div_start('', null, false, 'w-100 p-3');
echo('<h1 class="h3 mb-5">Customers Details Report</h1>');

start_form();

start_table(TABLESTYLE_NOBORDER);
    start_row();
        customer_list_cells(trans("Customer"), 'customer_id', null, true);
        ref_cells(trans("Mobile No."), 'mobile', '',null, trans('Enter mobile number'));
        ref_cells(trans("Email"), 'email', '',null, trans('Enter email address'));
    end_row();
    start_row();
        ref_cells(trans("TRN No."), 'trn', '',null, trans('Enter TRN number'));
        date_cells(trans("Registration From Date:"), 'created_at_from', '', null);
        date_cells(trans("To Date:"), 'created_at_to', '', null);
        submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');
    end_row();
end_table();
br(1);

$GLOBALS['Ajax']->activate('reports_tbl');
$sql = get_sql_for_customer_detail(...array_values($filters));

$cols = [
    trans("Ref No.") => [
        "name" => "debtor_ref",
        "align" => "left"
    ],
    trans("Customer Name") => [
        "name" => "name",
        "align" => "left"
    ],
    trans("Mobile No.") => [
        "name" => "mobile",
        "align" => "left"
    ],
    trans("Email Address") => [
        "name" => "debtor_email",
        "align" => "left"
    ],
    trans("Contact Person") => [
        "name" => "contact_person",
        "align" => "left"
    ],
    trans("Salesman") => [
        "name" => "Salesman",
        "align" => "left"
    ],
    trans("Customer TRN") => [
        "name" => "tax_id",
        "align" => "left"
    ],
    trans("Reg. Date") =>   [
        "fun" => "fmt_date",
        "align" => "left"
    ],
];

$table =& new_db_pager('cust_tbl', $sql, $cols);
$table->width = "80%";

div_start('reports_tbl');
display_db_pager($table);
div_end();

br(1);

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

end_page();

function print_report($export_type, $filters) {
    $sql = get_sql_for_customer_detail(...array_values($filters));
    $result = db_query($sql);
    $path_to_root = $GLOBALS['path_to_root'];
    $_POST['REP_ID'] = '10054';

    if ($export_type == 'excel') {
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    } else {
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    }

    $orientation = 'L';
    $page = 'A4';
    $params = [
        '',
        [
            "text" => trans("Date"),
            "from" => sql2date($filters['created_at_from']),
            "to"   => sql2date($filters['created_at_to'])
        ]
    ];
    
    $columns = [
        [
            "key"   => "debtor_ref",
            "title" => _('Ref. No.'),
            "align" => "left",
            "width" => 10,
            "type" => "TextCol"
        ],
        [
            "key"   => "name",
            "title" => _('Name'),
            "align" => "left",
            "width" => 50,
            "type" => "TextCol"
        ],
        [
            "key"   => "mobile",
            "title" => _('Contact No.'),
            "align" => "left",
            "width" => 20,
            "type" => "TextCol"
        ],
        [
            "key"   => "debtor_email",
            "title" => _('Email'),
            "align" => "left",
            "width" => 40,
            "type" => "TextCol"
        ],
        [
            "key"   => "contact_person",
            "title" => _('Contact Person'),
            "align" => "left",
            "width" => 35,
            "type" => "TextCol"
        ],
        [
            "key"   => "Salesman",
            "title" => _('Salesman'),
            "align" => "left",
            "width" => 35,
            "type" => "TextCol"
        ],
        [
            "key"   => "tax_id",
            "title" => _('TRN No.'),
            "align" => "left",
            "width" => 25,
            "type" => "TextCol"
        ],
        [
            "key"   => "created_at",
            "title" => _('Date'),
            "align" => "left",
            "width" => 15,
            "type" => "TextCol",
            "preProcess" => "sql2date"
        ]
    ];

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $rep = new FrontReport(
        trans('Customer Details Report'),
        "customer_details_report_" . random_id(64),
        $page,
        9,
        $orientation
    );
    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();
    while($row = db_fetch_assoc($result)) {
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
    $rep->End();
}

function gl_link($row) {
    return get_gl_view_str($row["type"], $row["trans_no"]);
}

function prt_link($row){
    return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);
}

function fmt_date($row){
    return sql2date($row['created_at']);
}

function validate_user_input() {
    $userDateFormat = getDateFormatInNativeFormat();
    $metadata = [
        [
            "key"       => "customer_id",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid Customer"
        ],
        [
            "key"       => "mobile",
            "rules"     => ['nullable', 'whole_number'],
            "message"   => "Please enter valid Mobile number"
        ],
        [
            "key"       => "email",
            "rules"     => ['nullable', 'email'],
            "message"   => "Please enter valid Email"
        ],
        [
            "key"       => "trn",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please enter a valid TRN number"
        ],
        [
            "key"   => 'created_at_from',
            "rules" => [
                'nullable',
                [
                    "name" => "date",
                    "param" => [
                        $userDateFormat
                    ]
                ]
            ],
            "message" => "Please provide a valid date from"
        ],
        [
            "key"   => 'created_at_to',
            "rules" => [
                'nullable',
                [
                    "name" => "date",
                    "param" => [
                        $userDateFormat
                    ]
                ]
            ],
            "message" => "Please provide a valid date to"
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

    /** if trans date is not set set to today date by default */
    if (!$validData['created_at_from']){
        $validData['created_at_from'] = date($userDateFormat, strtotime("-1 months"));
    }

    if (!$validData['created_at_to']){
        $validData['created_at_to'] = today();
    }

    /** repopulate the POST variable */
    foreach($validData as $key => $data) {
        $_POST[$key] = $data;
    }

    /** convert the trans date to mysql date format for us to work with */
    $validData['created_at_from'] = DateTime::createFromFormat($userDateFormat, $validData['created_at_from'])
         ->format(DB_DATE_FORMAT);

    $validData['created_at_to'] = DateTime::createFromFormat($userDateFormat, $validData['created_at_to'])
         ->format(DB_DATE_FORMAT);

    /** returns the validated data in the correct sort order */
    return array_merge(array_flip($filter_keys), $validData);
}


