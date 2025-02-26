<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigsForRequiredParamsInStockCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dropColumns = [];
        if (Schema::hasColumn('0_stock_category', 'srq_app_id_required')) {
            $dropColumns[] = 'srq_app_id_required';
        }

        if (Schema::hasColumn('0_stock_category', 'srq_trans_id_required')) {
            $dropColumns[] = 'srq_trans_id_required';
        }

        if (!empty($dropColumns)) {
            Schema::table('0_stock_category', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn(...$dropColumns);
            });
        }

        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->boolean('srq_app_id_required')->default(0)->nullable(false);
            $table->boolean('srq_trans_id_required')->default(0)->nullable(false);
            $table->boolean('inv_app_id_required')->default(0)->nullable(false);
            $table->boolean('inv_trans_id_required')->default(0)->nullable(false);
            $table->boolean('inv_narration_required')->default(0)->nullable(false);
            $table->boolean('is_app_id_unique')->default(0)->nullable(false);
            $table->boolean('is_trans_id_unique')->default(0)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_stock_category', function (Blueprint $table) {
            $table->dropColumn(
                'srq_app_id_required',
                'srq_trans_id_required',
                'inv_app_id_required',
                'inv_trans_id_required',
                'inv_narration_required',
                'is_app_id_unique',
                'is_trans_id_unique'
            );
        });
    }
}
