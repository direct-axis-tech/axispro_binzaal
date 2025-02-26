<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class MigratePersonNameAndIdToGlTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::unprepared('LOCK TABLES 0_gl_trans as gt WRITE, 0_debtors_master as dm WRITE, 0_suppliers as sup WRITE, 0_sub_ledgers as sub WRITE');
            DB::table('0_gl_trans as gt')
                ->leftJoin('0_debtors_master as dm', function (JoinClause $join) {
                    $join->on('dm.debtor_no', 'gt.person_id')
                        ->where('gt.person_type_id', PT_CUSTOMER);
                })
                ->leftJoin('0_suppliers as sup', function (JoinClause $join) {
                    $join->on('sup.supplier_id', 'gt.person_id')
                        ->where('gt.person_type_id', PT_SUPPLIER);
                })
                ->leftJoin('0_sub_ledgers as sub', 'sub.code', 'gt.axispro_subledger_code')
                ->whereNotNull('dm.debtor_no')
                ->orWhereNotNull('sup.supplier_id')
                ->orWhereNotNull('sub.id')
                ->update([
                    'person_name' => DB::raw(
                        "COALESCE("
                            . "concat(dm.debtor_ref, IF(dm.`name` = '', '', concat(' - ', dm.`name`))), "
                            . "concat(sup.supp_ref, IF(sup.`supp_name` = '', '', concat(' - ', sup.`supp_name`))), "
                            . "concat(sub.code, ' - ', sub.`name`), "
                            . "if(gt.person_type_id = ".PT_MISC.", gt.person_id, NULL)"
                        .")"
                    ),
                    'person_type_id' => DB::raw('if(isnull(gt.person_type_id) AND !isnull(gt.axispro_subledger_code), '.PT_SUBLEDGER.', gt.person_type_id)'),
                    'person_id' => DB::raw('if(isnull(gt.person_type_id) AND !isnull(gt.axispro_subledger_code), gt.axispro_subledger_code, gt.person_id)')
                ]);
            DB::unprepared('UNLOCK TABLES');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_gl_trans')
            ->where('person_type_id', PT_SUBLEDGER)
            ->orWhereNotNull('person_name')
            ->update([
                'person_name' => null,
                'person_type_id' => DB::raw('if(person_type_id = '.PT_SUBLEDGER.', NULL, person_type_id)'),
                'person_id' => DB::raw('if(person_type_id = '.PT_SUBLEDGER.', NULL, person_id)')
            ]);
    }
}
