<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermittedCategoriesForInvoicingToSecurityRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_security_roles', function (Blueprint $table) {
            $table->longText('permitted_categories')->nullable(false)->default('')->after('areas');
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
            $table->dropColumn('permitted_categories');
        });
    }
}
