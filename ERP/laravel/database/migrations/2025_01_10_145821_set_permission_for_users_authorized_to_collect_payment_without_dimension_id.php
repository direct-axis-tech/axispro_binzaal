<?php

use App\Models\Sales\CustomerTransaction;
use App\Models\System\AccessRole;
use App\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

class SetPermissionForUsersAuthorizedToCollectPaymentWithoutDimensionId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            CustomerTransaction::active()
                ->whereType(CustomerTransaction::PAYMENT)
                ->where(function (Builder $query) {
                    $query->whereNull('dimension_id')
                        ->orWhere('dimension_id', 0);
                })
                ->exists()
        ) {
            foreach (AccessRole::active()->get() as $role) {
                if ($role->hasPermission(Permissions::SA_SALESPAYMNT)) {
                    $role->grandAccess(Permissions::SA_RCVPMTWITHOUTDIM);
                    $role->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
