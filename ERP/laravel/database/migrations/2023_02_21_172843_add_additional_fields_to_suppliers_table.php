<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsToSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_suppliers', function (Blueprint $table) {
            $table->string('arabic_name')->nullable();
            $table->bigInteger('supplier_type')->default(-1)->after('arabic_name');
            $table->string('location')->nullable()->after('supplier_type');
            $table->string('email')->nullable()->after('location');
            $table->string('photo')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_suppliers', function (Blueprint $table) {
            $table->dropColumn('arabic_name');
            $table->dropColumn('supplier_type');
            $table->dropColumn('location');
            $table->dropColumn('email');
            $table->dropColumn('photo');
        });
    }
}
