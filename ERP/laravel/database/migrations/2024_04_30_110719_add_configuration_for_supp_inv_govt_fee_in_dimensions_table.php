<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationForSuppInvGovtFeeInDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('govt_fee_editable_in_purch')->nullable(false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn(
                'govt_fee_editable_in_purch',
            );
        });
    }
}
