<?php

$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/API/API_Call.php");

$page_security = 'SA_RECEPTION_INVOICE';
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

page(trans($help_context = "Reception Invoice Report"), false, false, "", $js);
div_start('', null, false, 'w-100 p-3');
echo('<h1 class="h3 mb-5">Reception Invoice Report</h1>');

start_form();
 
start_table(TABLESTYLE_NOBORDER);
    start_row();
        users_list_cells2(trans("Select User: "), 'user_id', null, false);
        text_cells(trans("Invoice No").":", "reference", null, 11);
        date_cells(trans("Invoice From Date:"), 'date_from', '', null);
        date_cells(trans("To Date:"), 'date_to', '', null);
        submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');
    end_row();  
end_table();
br(1);

$GLOBALS['Ajax']->activate('reports_tbl');
$sql = get_sql_for_reception_invoice(...array_values($filters));

$cols = [
    trans("Receptionist (User/Employee)") => [
        "name" => "user",
        "align" => "left"
    ],
    trans("Token No.") => [
        "name" => "token_number",
        "align" => "left"
    ],
    trans("Invoice No.") => [
        "name" => "reference",
        "align" => "left"
    ],
    trans("Invoice Date") => [
        "name" => "tran_date",
        "align" => "left"
    ]
];

$table =& new_db_pager('reception_invoice', $sql, $cols);
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

ob_start() ?>
<script type="text/javascript">
    $('.combo').select2();
</script>
<?php 
$GLOBALS['__FOOT__'][] = ob_get_clean(); 

end_page();

function print_report($export_type, $filters) {
    $sql = get_sql_for_reception_invoice(...array_values($filters));
    $result = db_query($sql);
    $path_to_root = $GLOBALS['path_to_root'];
    $_POST['REP_ID'] = '10056';

    if ($export_type == 'excel') {
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    } else {
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    }

    $orientation = 'P';
    $page = 'A4';
    $params = [
        '',
        [
            "text" => trans("Invoice Date"),
            "from" => sql2date($filters['date_from']),
            "to"   => sql2date($filters['date_to'])
        ]
    ];
    
    $columns = [
        [
            "key"   => "user",
            "title" => _('User Name'),
            "align" => "left",
            "width" => 40,
            "type" => "TextCol"
        ],
        [
            "key"   => "token_number",
            "title" => _('Token No.'),
            "align" => "left",
            "width" => 25,
            "type" => "TextCol"
        ],
        [
            "key"   => "reference",
            "title" => _('Invoice No.'),
            "align" => "left",
            "width" => 25,
            "type" => "TextCol"
        ],
        [
            "key"   => "tran_date",
            "title" => _('Invoice Date'),
            "align" => "left",
            "width" => 20,
            "type" => "TextCol",
            "preProcess" => "sql2date"
        ]
        ];

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $rep = new FrontReport(
        trans('Reception Invoice Report'),
        "reception_invoice_report_" . random_id(64),
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
    return get_gl_view_str($row["type"], $row["employee_id"]);
}

function prt_link($row){
    return print_document_link($row['employee_id'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);
}

function fmt_date($row){
    return sql2date($row['inv_date']);
}

function validate_user_input() {
    $userDateFormat = getDateFormatInNativeFormat();
    $metadata = [
        [
            "key"       => "user_id",
            "rules"     => ['nullable', 'p_intiger'],
            "message"   => "Please select a valid User"
        ],
        [
            "key"       => "reference",
            "rules"     => ['nullable', 'reference'],
            "message"   => "Please select a valid Invoice Number"
        ],
        [
            "key"   => 'date_from',
            "rules" => [
                'nullable',
                [
                    "name" => "date",
                    "param" => [
                        $userDateFormat
                    ]
                ]
            ],
            "message" => "Please provide a valid Date from"
        ],
        [
            "key"   => 'date_to',
            "rules" => [
                'nullable',
                [
                    "name" => "date",
                    "param" => [
                        $userDateFormat
                    ]
                ]
            ],
            "message" => "Please provide a valid Date to"
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
    if (!$validData['date_from']){
        $validData['date_from'] = date($userDateFormat, strtotime("-1 months"));
    }

    if (!$validData['date_to']){
        $validData['date_to'] = today();
    }

    /** repopulate the POST variable */
    foreach($validData as $key => $data) {
        $_POST[$key] = $data;
    }

    /** convert the trans date to mysql date format for us to work with */
    $validData['date_from'] = DateTime::createFromFormat($userDateFormat, $validData['date_from'])
         ->format(DB_DATE_FORMAT);

    $validData['date_to'] = DateTime::createFromFormat($userDateFormat, $validData['date_to'])
         ->format(DB_DATE_FORMAT);

    /** returns the validated data in the correct sort order */
    return array_merge(array_flip($filter_keys), $validData);
}