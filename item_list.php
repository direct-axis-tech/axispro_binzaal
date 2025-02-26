<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">




            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">





                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('SERVICE LIST') ?>
                                    <a class="btn btn-sm btn-success" href="items.php?action=new"><?= trans('New Item') ?></a>
                                </h3>
                            </div>

<!--                            <div class="kt-portlet__head-label">-->
<!--                                <a class="btn btn-sm btn-success" href="items.php?action=new">--><?//= trans('New Item') ?><!--</a>-->
<!--                            </div>-->
                        </div>

                        <!--begin::Table-->


                        <div class="table-responsive" style="padding: 7px 7px 7px 7px;">

                            <table class="table table-bordered" id="service_list_table">
                                <thead>
                                    <th><?= trans('STOCK ID') ?></th>
                                    <th><?= trans('Category') ?></th>
                                    <th><?= trans('Service Name') ?></th>
                                    <th><?= trans('Arabic Name') ?></th>
                                    <th><?= trans('Service Charge') ?></th>
                                    <th><?= trans('Govt. Fee') ?></th>
                                    <th><?= trans('Govt. Bank') ?></th>
                                    <th><?= trans('Bank Service Charge') ?></th>
                                    <th><?= trans('(VAT)Bank Service Charge') ?></th>
                                    <th><?= trans('Other Charge') ?></th>
                                    <th><?= trans('Local Commission') ?></th>
                                    <th><?= trans('Non-Local Commission') ?></th>
                                    <th><?= trans('Receivable Commission') ?></th>
                                    <th></th>
                                </thead>
                                <tbody id="service_list_tbody">

                                </tbody>
                            </table>

                        </div>


                        <!--end::Form-->
                    </div>


                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>




