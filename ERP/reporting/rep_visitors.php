<?php

$page_security = 'SA_CUSTOMERS_VISITED';

$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/reporting/includes/excel_report.inc");

$columns = [
    [
        'key' => 'name',
        'title' => trans('Name'),
        'align' => 'left',
        'width' => 60,
    ],
    [
        'key' => 'type',
        'title' => trans('Type'),
        'align' => 'left',
        'width' => 30,
    ],
    [
        'key' => 'mobile',
        'title' => trans('Mobile No.'),
        'align' => 'left',
        'width' => 40,
    ],
    [
        'key' => 'email',
        'title' => trans('Email Addr.'),
        'align' => 'left',
        'width' => 40,
    ],
];

$colInfo = new ColumnInfo($columns);

$rep = new FrontReport('Customers List', 'CustomersList_' . date('dmYHis'));
$rep->Font();
$rep->Info([""], $colInfo->cols(), $colInfo->headers(), $colInfo->aligns());
$rep->NewPage();

$sql = (
    "SELECT
        max(tbl.name) 'name',
        max(tbl.type) 'type',
        tbl.mobile,
        tbl.email
    FROM (
        SELECT
            dt.display_customer 'name',
            IF(dt.debtor_no = 1, 'Walk-in', 'Registered') 'type',
            CONCAT(
                '+971',
                IF(
                    dt.customer_mobile REGEXP '^((\\\\+|00)?971|0)?(5[024568]|[1234679])[0-9]{7}$',
                    IF(
                        dt.customer_mobile REGEXP '^((\\\\+|00)?971|0)?(5[024568])',
                        RIGHT(dt.customer_mobile, 9),
                        RIGHT(dt.customer_mobile, 8)
                    ),
                    NULL
                )
            ) 'mobile',
            IF(dt.customer_email REGEXP '^[^@[:space:]]+@[^@[:space:]]+\\.[^@[:space:]]+$', LOWER(dt.customer_email), NULL) 'email'
        FROM `0_debtor_trans` dt
        WHERE
            dt.`type` = 10
            AND (
                dt.customer_mobile REGEXP '^((\\\\+|00)?971|0)?(5[024568]|[1234679])[0-9]{7}$'
                OR dt.customer_email REGEXP '^[^@[:space:]]+@[^@[:space:]]+\\.[^@[:space:]]+$'
            )
            AND display_customer != ' '
            AND display_customer != 'Walk-in Customer'
    ) tbl
    GROUP BY tbl.mobile, tbl.email"
);
$visitors = db_query($sql, "Could not get the list of customers");
while ($visitor = $visitors->fetch_assoc()) {
    $rep->TextCol($colInfo->x1('name'), $colInfo->x2('name'), $visitor['name']);
    $rep->TextCol($colInfo->x1('type'), $colInfo->x2('type'), $visitor['type']);
    $rep->TextCol($colInfo->x1('mobile'), $colInfo->x2('mobile'), $visitor['mobile']);
    if (!filter_var($visitor['email'], FILTER_VALIDATE_EMAIL)) {
        $visitor['email'] = null;
    }
    $rep->TextCol($colInfo->x1('email'), $colInfo->x2('email'), $visitor['email']);
    $rep->NewLine();
}

$rep->End();