<?php include "header.php" ?>

<style>

    #btn_get_data:focus {
        background: #5867dd !important;
        color: white !important;
    }

    .setted_jv_link {
        cursor: pointer;
        text-decoration: underline;
        font-weight: bold;
    }

    table td, table th {
        border: 1px solid black !important;
    }

    form label {
        font-size: 11px !important;
        font-weight: bold !important;
    }

    #reconcile-form {
        color: black !important;
    }

    form input, form select {
        border: 1px solid #575757 !important;
    }

    .jumbotron {
        border: 1px solid #ccc;
    }

</style>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head d-flex justify-content-center" style="width: 100%">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('AUTO BANK RECONCILIATION') ?>
                                </h3>
                            </div>
                        </div>

                        <form method="post" action="#" id="reconcile-form" enctype="multipart/form-data"
                              class=" kt-form kt-form--fit kt-form--label-right">

                            <div class="kt-portlet__body">

                                <div class="jumbotron" style="padding-top: 12px !important;">

                                    <div class="row d-flex justify-content-center">

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="from_date" class="">FROM DATE:</label>
                                                <input type="text" name="from_date" id="from_date"
                                                       class="form-control ap-datepicker"
                                                       readonly placeholder="Select date"
                                                       value="<?= add_days(Today(),-1) ?>"/>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="to_date" class="">TO DATE:</label>
                                                <input type="text" name="to_date" id="to_date"
                                                       class="form-control ap-datepicker"
                                                       readonly placeholder="Select date"
                                                       value="<?= add_days(Today(),-1) ?>"/>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row d-flex justify-content-center">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="mobile" class="">BANK:</label>
                                                <select class="form-control kt-select2 ev-input"
                                                        name="bank" id="bank">

                                                    <?= prepareSelectOptions(
                                                        $api->get_records_from_table('0_bank_accounts', ['id', 'bank_account_name']),
                                                        'id', 'bank_account_name', '', "--"
                                                    ) ?>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="statement_csv" class="">CSV:</label>
                                                <input type="file" class="form-control"
                                                       name="statement_csv"
                                                       id="statement_csv">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row d-flex justify-content-center">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="date_col" class="">DATE COL:</label>
                                                <input type="text" class="form-control"
                                                       name="date_col"
                                                       id="date_col">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="statement_csv" class="">Date Format:</label>
                                                <select name="date_format" id="date_format" class="custom-select">
                                                    <option value="">-- select date format --</option>
                                                    <option value="d/m/Y">d/m/Y - 30/12/1975</option>
                                                    <option value="m/d/Y">m/d/Y - 12/30/1975</option>
                                                    <option value="Y/m/d">Y/m/d - 1975/12/30</option>
                                                    <option value="d/m/y">d/m/y - 30/12/75</option>
                                                    <option value="m/d/y">m/d/y - 12/30/75</option>
                                                    <option value="y/m/d">y/m/d - 75/12/30</option>
                                                    <option value="d-m-Y">d-m-Y - 30-12-1975</option>
                                                    <option value="m-d-Y">m-d-Y - 12-30-1975</option>
                                                    <option value="Y-m-d">Y-m-d - 1975-12-30</option>
                                                    <option value="d-m-y">d-m-y - 30-12-75</option>
                                                    <option value="m-d-y">m-d-y - 12-30-75</option>
                                                    <option value="y-m-d">y-m-d - 75-12-30</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div> 

                                    <div class="row d-flex justify-content-center">
                                         <input type="checkbox" id="appl_col" name="appl_col" value="true">&nbsp&nbsp
                                          <p style="background: #dbdbdb;padding: 5px;border-radius: 5px;">Check if Reconciliation on Application Id</p>
                                    </div><br>

                                    <div class="row d-flex justify-content-center">
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="ref_col" id="label_ref_col" class="">TRANS/APPL ID COL:</label>
                                                <input type="text" class="form-control"
                                                       name="ref_col"
                                                       id="ref_col">
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="desc_col" class="">DESC COL:</label>
                                                <input type="text" class="form-control"
                                                       name="desc_col"
                                                       id="desc_col">
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="date_col" class="">AMOUNT COL:</label>
                                                <input type="text" class="form-control"
                                                       name="amount_col"
                                                       id="amount_col">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row d-flex justify-content-center">


                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="bank_charge_col" class="">BANK CHRG COL:</label>
                                                <input type="text" class="form-control"
                                                       name="bank_charge_col"
                                                       id="bank_charge_col">
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="vat_col" class="">VAT COL:</label>
                                                <input type="text" class="form-control"
                                                       name="vat_col"
                                                       id="vat_col">
                                            </div>
                                        </div>


                                    </div>

                                    <div class="row d-flex justify-content-center">

                                        <div class="col-md-1 text-center">
                                            <div class="form-group">
                                                <button class="button btn btn-sm btn-success" id="btn-process"
                                                        type="submit">UPLOAD
                                                </button>
                                            </div>
                                        </div>

                                    </div>


                                    <div class="kt-widget6__foot text-center">

                                        <p><small>TRANS_COL : Transaction ID or Bank reference column specified in the
                                                bank statement</small></p>
                                        <p><small>DESC_COL : If transaction reference is inside the description column
                                                of bank statement,
                                                specify the description column. Else leave it blank Eg: NOQODI
                                                statement</small></p>

                                    </div>

                                </div>


                            </div>
                        </form>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>


    $("#reconcile-form").on('submit', function (e) {
        e.preventDefault();
        AxisPro.BlockDiv("#kt_content", "Comparing transactions. Please wait...");
        ajaxRequest({
            method: 'POST',
            url: route('API_Call', {method: 'processAutoReconciliation'}),
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            blocking: false
        })
        .done(function (response) {
            if (response.status === "OK") {
                swal.fire('Reconciliation process completed', response.msg, 'success')
                    .then(function () {
                            window.location.href = "auto_reconciled_list.php?bank=" + $("#bank").val();
                        }
                    );
            } else {
                if (response.msg) {
                    swal.fire('Warning', response.msg, 'warning')
                } else {
                    defaultErrorHandler();
                }
            }
        })
        .fail(defaultErrorHandler)
        .always(() => AxisPro.UnBlockDiv("#kt_content"));
    });

    $("#bank").change(function () {
        $('#date_col, #ref_col, #desc_col, #amount_col, #bank_charge_col, #vat_col').each((i, el) => {
            el.value = '';
        })
        ajaxRequest({
            method: 'GET',
            url: route('API_Call', {method: 'getBankAccount'}),
            data: { id: $(this).val() },
        }).done(function (data) {
            if (data) {
                let excel_cols = data.bank_address;

                if ($.trim(excel_cols) !== "") {
                    let split_excel_cols = excel_cols.split(",");

                    if(split_excel_cols[0])
                        $("#date_col").val(split_excel_cols[0]);

                    if(split_excel_cols[1])
                        $("#ref_col").val(split_excel_cols[1]);

                    if(split_excel_cols[2])
                        $("#desc_col").val(split_excel_cols[2]);

                    if(split_excel_cols[3])
                        $("#amount_col").val(split_excel_cols[3]);

                    if(split_excel_cols[4])
                        $("#bank_charge_col").val(split_excel_cols[4]);

                    if(split_excel_cols[5])
                        $("#vat_col").val(split_excel_cols[5]);
                }
            }
        }).fail(defaultErrorHandler);
    });


    function showAlert(alert_class, title, description) {
        var alert_div = $(".top-msg");

        alert_div.removeClass("alert-warning");
        alert_div.removeClass("alert-danger");
        alert_div.removeClass("alert-success");
        alert_div.addClass(alert_class);

        alert_div.show();

        $(".top-msg-title").html(title);
        $(".top-msg-description").html(description);

        window.scrollTo({top: 0, behavior: 'smooth'});
    }

</script>
