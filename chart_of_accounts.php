<?php include "header.php" ?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">

                        <!--begin::Form-->

                        <div class="kt-portlet">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">
                                        <?= trans('CHART OF ACCOUNTS') ?>
                                    </h3>
                                </div>
                            </div>
                            <div class="kt-portlet__body">
                                <div id="ap-coa-tree" class="coa-tree">
                                </div>
                            </div>
                        </div>


                        <!--end::Form-->
                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>


<div class="modal fade" id="COA_confirm_new_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="COA_modal_title"><?= trans('New Node') ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="form-control-label"><?= trans('Auto generated next available COA code') ?>:</label>
                        <input type="text" class="form-control" id="new-coa-code">
                    </div>

                    <div class="form-group">
                        <label class="form-control-label"><?= trans('Name') ?>:</label>
                        <input type="text" class="form-control" id="new-coa-node-name">
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" ><?= trans('Close') ?></button>
                <button type="button" class="btn btn-primary"
                        onclick="AxisPro.JSTree.CreateNode(global.node,global.node_data,global.pos)"><?= trans('Ok, Create') ?>
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="COA_change_group_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="COA_change_group_modal_title"><?= trans('Change Account Group') ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="change_parent_form">


                    <input type="hidden" id="COA_CGM_TYPE">
                    <input type="hidden" id="COA_CGM_NODE_ID">

                    <div class="form-group" data-parsley-form-group id="coa-acc-class-div">
                        <label class="form-control-label"><?= trans('Account Class') ?>:</label>
                        <select value="1" id="coa-acc-class" class="form-control ap-select2 coa-acc-class input-large"
                                name="coa-acc-class" onchange="AxisPro.JSTree.COAClassSelectChange(this);" style="width: 100% !important;" required></select>
                    </div>

                    <div class="form-group" data-parsley-form-group>
                        <label class="form-control-label"><?= trans('Account Group') ?>:</label>
                        <select id="coa-acc-groups" class="form-control ap-select2 coa-acc-groups input-large"
                                name="coa-acc-groups" style="width: 100% !important;" required></select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="reset" form="change_parent_form" class="btn btn-secondary" data-dismiss="modal"
                        data-bs-dismiss="modal"><?= trans('Close') ?></button>
                <button type="submit" form="change_parent_form" id="validate" class="btn btn-primary"><?= trans('Save changes') ?></button>
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>

<script>


    $(document).ready(function () {


        AxisPro.JSTree.GenerateCOATree();


        AxisPro.APICall('GET', route('API_Call', {method: 'get_all_coa_groups'}),{}, function (data) {
            AxisPro.PrepareSelectOptions(data, 'id', 'name', 'coa-acc-groups','*None*');
        });


        AxisPro.APICall('GET', route('API_Call', {method: 'get_all_coa_classes'}),{}, function (data) {
            AxisPro.PrepareSelectOptions(data, 'cid', 'class_name', 'coa-acc-class');
        });


    });

    var $form = $('#change_parent_form').parsley();
    $form.on('form:submit', function() {
        AxisPro.JSTree.ChangeCOAGroup();
        return false;
    })


</script>

