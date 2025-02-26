<?php

use App\Models\System\AccessRole;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class ChangeThePermissionCodeForGovtFeeEditable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AccessRole::grantedWithAnyPermissions(Permissions::SA_SUDOEDITNARTN)
            ->get()
            ->each(function (AccessRole $role) {
                $role->revokeAccess(Permissions::SA_SUDOEDITNARTN);
                $role->grandAccess(Permissions::SA_GOVBNKACTEDITABLE);

                $role->save();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AccessRole::grantedWithAnyPermissions(Permissions::SA_GOVBNKACTEDITABLE)
            ->get()
            ->each(function (AccessRole $role) {
                $role->revokeAccess(Permissions::SA_GOVBNKACTEDITABLE);
                $role->grandAccess(Permissions::SA_SUDOEDITNARTN);

                $role->save();
            });
    }
}
