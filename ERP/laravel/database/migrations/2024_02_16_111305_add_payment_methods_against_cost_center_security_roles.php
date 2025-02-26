<?php

use App\Models\Accounting\Dimension;
use App\Permissions;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodsAgainstCostCenterSecurityRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        global $path_to_root;
        global $global_pay_types_array;

        $path_to_root = realpath(__DIR__.'/../../..');
        include_once __DIR__.'/../../../sales/includes/ui/sales_order_ui.inc';
        include_once __DIR__.'/../../../includes/types.inc';
        include_once __DIR__.'/../../../includes/sysnames.inc';

        
        $dimensions = DB::table('0_dimensions')->get();
        foreach ($dimensions as $dim) {
            $availablePaymentMethods = get_available_payment_methods(Dimension::find($dim->id), null);
            if (count($availablePaymentMethods) > 0) 
                $payTypes = implode(',', array_keys($availablePaymentMethods));
                DB::statement('UPDATE 0_dimensions tt SET tt.enabled_payment_methods = "'.$payTypes.'" where tt.id='.$dim->id);  
        }
 
        $permissions = app(App\Permissions::class);
        $security_roles = DB::table('0_security_roles')
            ->whereRaw("FIND_IN_SET(?, REPLACE(areas, ';', ','))"
                . " OR FIND_IN_SET(?, REPLACE(areas, ';', ','))"
                . " OR FIND_IN_SET(?, REPLACE(areas, ';', ','))", 
                [
                    $permissions->getCode(Permissions::SA_SALESINVOICE),
                    $permissions->getCode(Permissions::SA_EDITSALESINVOICE),
                    $permissions->getCode(Permissions::SA_UPDATEINVOICE)]
                )
            ->get();

        $payTypes = implode(',', array_keys($global_pay_types_array));
        foreach ($security_roles as $sr) {
            if (empty($sr->enabled_payment_methods)) {
                DB::statement('UPDATE 0_security_roles tt SET tt.enabled_payment_methods = "'.$payTypes.'" where tt.id='.$sr->id);
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
        

    }

    
}