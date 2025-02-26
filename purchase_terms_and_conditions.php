<?php
include "header.php";
?>
<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">


                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('Create New PO - Terms & Conditions') ?>
                                </h3>
                            </div>
                        </div>


                        <!--begin::Form-->
                        <form class="kt-form kt-form--label-right" id="po_tc_form">
                            <div class="kt-portlet__body" style="padding: 20px !important;">
                                <div class="Msg"></div>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label><?= trans('Title') ?>:
                                        </label>
                                        <input type="text" id="title" name="title" class="form-control" />
                                    </div>
                                    <div class="col-lg-5">
                                        <label class=""><?= trans('Description') ?>:</label>
                                        <textarea class="form-control" id="desc" name="desc"></textarea>
                                    </div>


                                    <div class="col-lg-3">
                                        <label style="height: 13px;"></label>
                                        <div class="input-group">
                                            <button type="button" onclick="SaveTC();" class="btn btn-primary">
                                                <?= trans('SAVE') ?>
                                            </button>&nbsp;&nbsp;
                                        </div>
                                    </div>
                                </div>


                        </form>
                        <!--end::Form-->
                    </div>
                </div>

                <div class="row" style="width: 100%;">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('List of Terms & Conditions') ?>
                                </h3>
                            </div>

                        </div>
                        <!--begin::Table-->
                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">

                            <table class="table table-bordered" id="list_documntes">
                                <thead>
                                <th><?= trans('Title') ?></th>
                                <th><?= trans('Description') ?></th>
                                <th></th>
                                </thead>
                                <tbody id="tbody">

                                <?php
                                    $terms = $api->get_records_from_table('0_po_terms_and_conditions',['*']);

                                    foreach ($terms as $row) {

                                        echo "<tr>";
                                        echo "<td>".$row['title']."</td>";
                                        echo "<td>".$row['description']."</td>";
                                        echo "<td><a href='#' data-id='".$row['id']."' class='deleteTC btn btn-sm btn-danger'> DELETE</a></td>";
                                        echo "</tr>";

                                    }

                                ?>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>

<script>

    function SaveTC() {

        var edit_id = $("#edit_id").val();

        var params = {};

        params.title = $("#title").val();
        params.desc = $("#desc").val();

        var cond = false;

        if (cond) {
            swal.fire(
                'Check form data',
                'Invalid data input. Please check',
                'warning'
            )
        }
        else {

            AxisPro.APICall('POST', route('API_Call', {method: 'handleNewPOTermsAndCondition'}), params, function (data) {

                if (data && data.status === 'FAIL') {
                    toastr.error(data.msg);
                } else {

                    swal.fire(
                        data.status === 'SUCCESS' ? 'Success!' : 'FAILED',
                        data.msg,
                        data.status === 'SUCCESS' ? 'success' : 'warning'
                    ).then(function () {
                        window.location.reload();
                    });

                }

            });
        }

    }


    $(document).ready(function (e) {

        
        $(document).on("click",".deleteTC", function () {

            var id= $(this).data('id');


            var params = {
                id : id
            };


            swal.fire({
                title: 'Are you sure?',
                text: "Confirm whether you want to DELETE this Terms & Condition",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, DELETE!'
            }).then(function (result) {
                if (result.value) {

                    AxisPro.APICall('POST', route('API_Call', {method: 'handleDeletePOTermsAndCondition'}), params, function (data) {

                        if (data && data.status === 'FAIL') {
                            toastr.error(data.msg);
                        } else {

                            swal.fire(
                                data.status === 'SUCCESS' ? 'Success!' : 'FAILED',
                                data.msg,
                                data.status === 'SUCCESS' ? 'success' : 'warning'
                            ).then(function () {
                                window.location.reload();
                            });

                        }

                    });

                }
            });


        })

    });



</script>
