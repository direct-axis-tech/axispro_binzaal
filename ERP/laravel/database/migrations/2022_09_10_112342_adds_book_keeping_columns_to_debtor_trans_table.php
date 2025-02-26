<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddsBookKeepingColumnsToDebtorTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->bigInteger('updated_by')->nullable();
            $table->dateTime('transacted_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->index('created_by');
            $table->index('updated_by');
        });

        // update the created_by from debtor_trans_details
        // for invoice and delivery
        DB::statement(
            'UPDATE `0_debtor_trans` as `trans`'
            . ' INNER JOIN `0_debtor_trans_details` as `detail` ON'
                . ' `detail`.`debtor_trans_no` = `trans`.`trans_no`'
                . ' AND `detail`.`debtor_trans_type` = `trans`.`type`'
                . ' AND `detail`.`id` IN ('
                    . ' SELECT min(`_detail`.`id`)'
                    . ' FROM `0_debtor_trans_details` as `_detail`'
                    . ' GROUP BY `_detail`.`debtor_trans_type`, `_detail`.`debtor_trans_no`'
                . ' )'
            . ' SET `trans`.`created_by` = IFNULL(`trans`.`created_by`, `detail`.`created_by`)'
        );

        // update the values
        DB::statement(
            'UPDATE `0_debtor_trans` as `trans`'
            . ' INNER JOIN `0_audit_trail` as `created` ON'
                . ' `created`.`trans_no` = `trans`.`trans_no`'
                . ' AND `created`.`type` = `trans`.`type`'
                . ' AND `created`.`id` IN ('
                    . ' SELECT min(`_created`.`id`)'
                    . ' FROM `0_audit_trail` as `_created`'
                    . ' GROUP BY `_created`.`type`, `_created`.`trans_no`'
                . ' )'
            . ' INNER JOIN `0_audit_trail` as `updated` ON'
                . ' `updated`.`trans_no` = `trans`.`trans_no`'
                . ' AND `updated`.`type` = `trans`.`type`'
                . ' AND `updated`.`gl_seq` = 0'
            . ' SET `trans`.`updated_by` = `updated`.`user`,'
                . ' `trans`.`updated_at` = `updated`.`stamp`,'
                . ' `trans`.`created_by` = ifnull(`trans`.`created_by`, `created`.`user`),'
                . ' `trans`.`transacted_at` = `created`.`stamp`'
        );

        // remove the nullable
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->bigInteger('created_by')->nullable(false)->default(null)->change();
            $table->bigInteger('updated_by')->nullable(false)->default(null)->change();
            $table->dateTime('transacted_at')->nullable(false)->default(null)->change();
            $table->dateTime('updated_at')->nullable(false)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_debtor_trans', function (Blueprint $table) {
            $table->dropColumn('updated_by');
            $table->dropColumn('transacted_at');
            $table->dropColumn('updated_at');
        });
    }
}
