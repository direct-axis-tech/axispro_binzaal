<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="kt-subheader kt-subheader-custom   kt-grid__item">
                    <div class="kt-container ">
                        <div class="kt-subheader__main">
                            <h3 class="kt-subheader__title"><?= trans('SETTINGS') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?= createMenuTile(
                        'SA_SETUPCOMPANY',
                        trans('Company Setup'),
                        trans('Setup company information'),
                        getRoute('company_setup'),
                        'fa-cogs'
                    ) ?>
                    <?= createMenuTile(
                        'SA_USERS',
                        trans('Users'),
                        trans('Manage Users'),
                        getRoute('user_setup'),
                        'fa-users-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_SECROLES',
                        trans('Access Controls'),
                        trans('Manage Users'),
                        getRoute('access_setup'),
                        'fa-users-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_POSSETUP',
                        trans('Sales Point Settings'),
                        trans('POS settings'),
                        'ERP/sales/manage/sales_points.php?',
                        'fa-users-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_TAXRATES',
                        trans('Tax Types'),
                        trans('Manage Tax Types'),
                        getRoute('tax_types_setup'),
                        'fa-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_GLSETUP',
                        trans('System and General GL Setup'),
                        trans('Accounting Setup'),
                        getRoute('gl_setup'),
                        'fa-cog'
                    ) ?>   
                    <?= createMenuTile(
                        'HRM_SETUP',
                        trans('HR Setup'),
                        trans('HR Setup'),
                        getRoute('hr_setup'),
                        'fa-cog'
                    ) ?>    
                    <?= createMenuTile(
                        'SA_FISCALYEARS',
                        trans('Fiscal Years'),
                        trans('Financial Year Setup'),
                        getRoute('fsy_setup'),
                        'fa-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_ITEMTAXTYPE',
                        trans('Item Tax Types'),
                        trans('Manage Item Tax Types'),
                        getRoute('item_tax_types_setup'),
                        'fa-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_VOIDTRANSACTION',
                        trans('Void Transaction'),
                        trans('Void Transaction'),
                        getRoute('void_trans'),
                        'fa-cog'
                    ) ?>
                    <?= createMenuTile(
                        'SA_VOIDEDTRANSACTIONS',
                        trans('Voided Transactions'),
                        trans('Voided Transactions'),
                        getRoute('voided_trans'),
                        'fa-eraser'
                    ) ?>
                    <?= createMenuTile(
                        'SA_ITEMCATEGORY',
                        trans('Item Categories'),
                        trans('Add and Manage Category'),
                        getRoute('category'),
                        'fa-boxes'
                    ) ?>
                    <?= createMenuTile(
                        'SA_CTGRYGROUP',
                        trans('Category Groups'),
                        trans('Add and Manage Category Groups'),
                        getRoute('category_groups'),
                        'far fa-object-group font-weight-normal'
                    ) ?>
                    <?= createMenuTile(
                        'SA_ATTACHDOCUMENT',
                        trans('Attach Documents'),
                        trans('Attach documents'),
                        getRoute('attach_documents'),
                        'fa-paperclip'
                    ) ?>
                    <?= createMenuTile(
                        'SA_ACL_LIST',
                        trans('ACL List'),
                        trans('Access control list for roles'),
                        getRoute('acl_list'),
                        'fa-user-shield'
                    ) ?>
                    <?= createMenuTile(
                        'SA_USERACTIVITY',
                        trans('User Activity Log'),
                        trans("See the user's login activities"),
                        getRoute('activity_log'),
                        'fa-history'
                    ) ?>
                    <?= createMenuTile(
                        'SA_ENTITY_GROUP',
                        trans('Entity Group'),
                        trans('Create new entity group'),
                        route('entityGroup.index'),
                        'fa-users'
                    ) ?>
                    <?= createMenuTile(
                        'SA_MANAGE_WORKFLOW',
                        trans('Manage Workflow'),
                        trans('Add/Manage Workflow'),
                        route('workflow.index'),
                        'fa-bezier-curve'
                    ) ?>
                    <?= createMenuTile(
                        'SA_MANAGE_DOCUMENT_TYPE',
                        trans('Document Types'),
                        trans("Document Types"),
                        route('documentTypes.index'),
                        'fa fa-business-time'
                    ) ?>
                    <?= createMenuTile(
                        [
                            'SA_MANAGE_TASKS',
                            'SA_MANAGE_TASKS_ALL'
                        ],
                        trans('Manage Tasks'),
                        trans('Approve or reject pending requests'),
                        route('task.index'),
                        'fa-tasks'
                    ) ?>
                    <?= createMenuTile(
                        'SA_MANAGE_GROUP_MEMBERS',
                        trans('Manage Group Members'),
                        trans('Manage System Reserved Group Members'),
                        route('entityGroupMembers.index'),
                        'fa-users'
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>