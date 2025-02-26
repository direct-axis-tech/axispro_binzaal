<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnConfigsToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->boolean('is_having_split_govt_fee')->default(0)->nullable(false);
            $table->boolean('is_service_fee_combined')->default(0)->nullable(false);
            $table->boolean('is_govt_bank_editable')->default(0)->nullable(false);
            $table->boolean('is_other_fee_editable')->default(0)->nullable(false);
            $table->boolean('is_passport_col_enabled')->default(0)->nullable(false);
            $table->boolean('is_app_id_col_enabled')->default(0)->nullable(false);
            $table->boolean('is_trans_id_col_enabled')->default(0)->nullable(false);
            $table->boolean('is_narration_col_enabled')->default(0)->nullable(false);
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
                'is_having_split_govt_fee',
                'is_service_fee_combined',
                'is_govt_bank_editable',
                'is_other_fee_editable',
                'is_passport_col_enabled',
                'is_app_id_col_enabled',
                'is_trans_id_col_enabled',
                'is_narration_col_enabled'
            );
        });
    }
}
