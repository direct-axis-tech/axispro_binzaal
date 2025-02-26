<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodsToSecurityRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_security_roles', function (Blueprint $table) {
            $table->string('enabled_payment_methods', 255)->after('permitted_categories')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_security_roles', function (Blueprint $table) {
            $table->dropColumn('enabled_payment_methods');
        });
    }
}
