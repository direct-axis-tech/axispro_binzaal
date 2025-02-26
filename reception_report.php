<?php

$page_security = "SA_RECEPTION_REPORT";
$__ROOT_DIR__  = '.';
$path_to_root = "$__ROOT_DIR__/ERP";
include_once($path_to_root . "/includes/session.inc");

ob_start(); ?>

<!-- Head block: Start -->
<link href="<?= $__ROOT_DIR__ ?>/assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>

<style>
    #cust-bal-rep-tbl_length {
        margin-right: 0.5rem;
        margin-left: 0.5rem;
    }
    #cust-bal-rep-tbl_length select {
        width: unset;
    }
    select.custom-select-sm {
        height: calc(1.5em + 1rem + 2px) !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
    }

    .busy:before {
        content: "";
        background: rgba(255, 255, 255, 0.5);
        position: fixed;
        z-index: 2048;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
    .busy:after {
        content: "";
        position: fixed;
        top: 50%;
        right: 50%;
        z-index: 2058;
        display: inline-block;
        animation: bubble-circle 1.3s infinite linear;
        width: 1em;
        height: 1em;
        border-radius: 50%;
        color: #1dc9b7;
    }

    @keyframes bubble-circle {
        0%,to {
            box-shadow: 0 -3em 0 .2em,2em -2em 0 0,3em 0 0 -1em,2em 2em 0 -1em,0 3em 0 -1em,-2em 2em 0 -1em,-3em 0 0 -1em,-2em -2em 0 0
        }

        12.5% {
            box-shadow: 0 -3em 0 0,2em -2em 0 .2em,3em 0 0 0,2em 2em 0 -1em,0 3em 0 -1em,-2em 2em 0 -1em,-3em 0 0 -1em,-2em -2em 0 -1em
        }

        25% {
            box-shadow: 0 -3em 0 -.5em,2em -2em 0 0,3em 0 0 .2em,2em 2em 0 0,0 3em 0 -1em,-2em 2em 0 -1em,-3em 0 0 -1em,-2em -2em 0 -1em
        }

        37.5% {
            box-shadow: 0 -3em 0 -1em,2em -2em 0 -1em,3em 0 0 0,2em 2em 0 .2em,0 3em 0 0,-2em 2em 0 -1em,-3em 0 0 -1em,-2em -2em 0 -1em
        }

        50% {
            box-shadow: 0 -3em 0 -1em,2em -2em 0 -1em,3em 0 0 -1em,2em 2em 0 0,0 3em 0 .2em,-2em 2em 0 0,-3em 0 0 -1em,-2em -2em 0 -1em
        }

        62.5% {
            box-shadow: 0 -3em 0 -1em,2em -2em 0 -1em,3em 0 0 -1em,2em 2em 0 -1em,0 3em 0 0,-2em 2em 0 .2em,-3em 0 0 0,-2em -2em 0 -1em
        }

        75% {
            box-shadow: 0 -3em 0 -1em,2em -2em 0 -1em,3em 0 0 -1em,2em 2em 0 -1em,0 3em 0 -1em,-2em 2em 0 0,-3em 0 0 .2em,-2em -2em 0 0
        }

        87.5% {
            box-shadow: 0 -3em 0 0,2em -2em 0 -1em,3em 0 0 -1em,2em 2em 0 -1em,0 3em 0 -1em,-2em 2em 0 0,-3em 0 0 0,-2em -2em 0 .2em
        }
}
</style>
<!-- Head block: End -->

<?php $GLOBALS['__HEAD__'][] = ob_get_clean();
page(trans($help_context = "Reception report - Axispro ERP"));

// validate customer id
if(
    empty($_GET['cust_id']) 
    || !preg_match('/^[1-9][0-9]*$/', $_GET['cust_id'])
) {
    $_GET['cust_id'] = '';
}

// validate customer filter
if(
    empty($_GET['cust_filter']) 
    || !preg_match('/[a-zA-Z_ 0-9]*$/', $_GET['cust_filter'])
) {
    $_GET['cust_filter'] = '';
}

// validate date from
if(
    empty($_GET['dt_from']) 
    || !($dt = DateTime::createFromFormat('d/m/Y', $_GET['dt_from'])) 
    || $dt->format('d/m/Y') != $_GET['dt_from']
) {
    $_GET['dt_from'] = date('d/m/Y');
}

// validate date to
if(
    empty($_GET['dt_to']) 
    || !($dt = DateTime::createFromFormat('d/m/Y', $_GET['dt_to'])) 
    || $dt->format('d/m/Y') != $_GET['dt_to']
) {
    $_GET['dt_to'] = date('d/m/Y');
}

$customers = db_query("SELECT debtor_no id, name FROM 0_debtors_master")->fetch_all(MYSQLI_ASSOC);

?>

<div class="w-100 p-3" id="doc--body">
    <div class="card p-5 shadow">
        <form action="./reception_report.php" id="filter-form" method="GET">
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group-sm row">
                        <label for="cust_id" class="col-4 col-form-label-sm">Customers:</label>
                        <div class="col-8">
                            <select 
                                class="custom-select custom-select-sm"
                                name="cust_id" 
                                id="cust_id">
                                <option value="">--select--</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $_GET['cust_id'] ? 'selected' : '' ?>><?= $customer['name'] ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group-sm row">
                        <label for="cust_filter" class="col-4 col-form-label-sm">
                            <span class="text-nowrap">Cust. Name</span> / <span class="text-nowrap">Mob. No.</span>
                        </label>
                        <div class="col-8">
                            <input 
                                name="cust_filter"
                                type="text" 
                                class="form-control form-control-sm" 
                                value="<?= $_GET['cust_filter'] ?>">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group-sm row">
                        <label for="date-range" class="col-4 col-form-label-sm">Date Range:</label>
                        <div class="col-8">
                            <div id="date-range" class="input-group input-daterange">
                                <input 
                                    data-provide="datepicker" 
                                    data-date-format="dd/mm/yyyy"
                                    data-date-autoclose="true"
                                    data-date-end-date="0d"
                                    name="dt_from"
                                    type="text" 
                                    class="form-control form-control-sm" 
                                    value="<?= $_GET['dt_from'] ?>">
                                <div class="input-group-addon text-center bg-secondary">-</div>
                                <input 
                                    data-provide="datepicker" 
                                    data-date-format="dd/mm/yyyy"
                                    data-date-end-date="0d"
                                    data-date-autoclose="true"
                                    name="dt_to"
                                    type="text" 
                                    class="form-control form-control-sm" 
                                    value="<?= $_GET['dt_to'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-success">
                    <span class="fa fa-search mr-2"></span>Search
                </button>
            </div>
        </form>
        <hr>
        <div>
            <table id="reception-rep-tbl" class="table table-sm table-bordered">
                <thead class="bg-success">
                    <tr>
                        <th>Token</th>
                        <th>Customer</th>
                        <th>Mobile No.</th>
                        <th>Email</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- generated using javascript -->
                </tbody>
            </table>
            <div id="pg-link">
                <!-- generated using javascript -->
            </div>
        </div>
    </div>
</div>

<?php ob_start() ?>
<!-- Foot Block: Start -->
<script src="<?= $__ROOT_DIR__ ?>/assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script>
    /** @type {HTMLTableSectionElement} */
    var tbody_receptionRep = document.getElementById('reception-rep-tbl').tBodies[0];
    /** @type {HTMLDivElement} */
    var div_pagination = document.getElementById('pg-link');
    /** @type {HTMLFormElement} */
    var form  = document.getElementById('filter-form');

    $(getReport);
    $(div_pagination).on("click", ".pg-link", function (e) {
        e.preventDefault();
        documentIsBusy(true);
        var req_url = $(this).attr("href");
        $(".error_note").hide();
        $.get(req_url, DisplayReport, 'json');
    });

    $(form).on('submit', function(evnt){
        evnt.preventDefault();
        getReport();
    })

    function getReport() {
        documentIsBusy(true);
        $(".error_note").hide();

        var formData = $(form).serialize();

        $.ajax({
            method: form.getAttribute('method'),
            url: route('API_Call', {method: 'getReceptionReport'}),
            data: formData,
            dataType: 'json'
        }).done(DisplayReport);
    }

    function DisplayReport(response) {
        var rep = response.data;
        var tbody_html = "";

        $.each(rep, function (key, value) {
            tbody_html += (
                    "<tr>\n"
                +   "    <td>" + value.token        + "</td>\n"
                +   "    <td>" + value.display_name + '<br><span class="small text-muted pt-2">' + value.real_name + "</span></td>\n"
                +   "    <td>" + value.mobile_no    + "</td>\n"
                +   "    <td>" + value.email        + "</td>\n"
                +   "    <td>" + value.date         + "</td>\n"
                +   "</tr>"
            );
        });

        $(tbody_receptionRep).html(tbody_html);
        $(div_pagination).html(response.pagination_links);
        documentIsBusy(false);
    }

    /**
     * Make the document unaccessible
     * 
     * @param bool isBusy
     */
    function documentIsBusy(busy) {
        var $body = $("#doc--body");
        if (busy && !$body.hasClass("busy")) {
            $body.addClass('busy');
        }
        if (!busy && $body.hasClass("busy")) {
            $body.removeClass('busy');
        }
    }
</script>
<!--  Foot Block: End -->
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();?>