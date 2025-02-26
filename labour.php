<?php use \App\Permissions as P; ?>
<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Begin: Manage Section-->
            <div class="w-100 p-3">
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('Manage') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?= createMenuTile(
                        P::SA_LBR_CREATE,
                        trans('New Maid'),
                        trans('Create new Maid'),
                        route('labour.create'),
                        'fa-user'
                    ) ?>

                    <?= createMenuTile(
                        P::SA_CREATE_AGENT,
                        trans('New Agent'),
                        trans('Create new agent'),
                        route('agent.create'),
                        'fa-user-tie'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::SA_LBR_CONTRACT,
                        trans('Create new contract'),
                        trans('Create new labour contract'),
                        route('contract.create'),
                        'fa-file-contract'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::SA_STOCK_RETURN,
                            P::SA_MAID_RETURN
                        ],
                        trans('Maid Return'),
                        trans('Maid Return Request'),
                        route('contract.maidReturn.create'),
                        'fa-reply'
                    ) ?>

                    <?= createMenuTile(
                        [
                            P::SA_STOCK_REPLACEMENT,
                            P::SA_MAID_RETURN
                        ],
                        trans('Maid Replacement'),
                        trans('Maid Replacement Request'),
                        route('contract.maidReplacement.create'),
                        'fa-exchange-alt'
                    ) ?>

                </div>
            </div>
            
            <!-- Begin: Inquiry Section-->
            <div class="w-100 p-3">
                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('Inquiry') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?= createMenuTile(
                        P::SA_LBR_VIEW,
                        trans('Maid List'),
                        trans('View all maids'),
                        route('labour.index'),
                        'fa-users'
                    ) ?>
                    
                    <?= createMenuTile(
                        P::SA_AGENT_LIST,
                        trans('Agent List'),
                        trans('View all agents'),
                        route('agent.index'),
                        'fa-users-cog'
                    ) ?>

                    <?= createMenuTile(
                        P::SA_LBR_CONTRACT_INQ,
                        trans('Contract Inquiry'),
                        trans('View all contracts'),
                        route('contract.index'),
                        'fa-chart-bar'
                    ) ?>

                    <?= createMenuTile(
                        P::SA_MAID_MOVEMENT_REPORT,
                        trans('Maid Movements'),
                        trans('Maid Movement Report'),
                        route('labour.reports.maidMovements'),
                        'fa-chart-bar'
                    ) ?>

                    <?= createMenuTile(
                        P::SA_LBR_INSTALLMENT_REPORT,
                        trans('Installment Enquiry'),
                        trans('Installment Report'),
                        route('labour.reports.installmentReport'),
                        'fa-money-bill'
                    ) ?>

                </div>
            </div>
            <!-- End: Inquiry Section-->
        </div>
    </div>
</div>