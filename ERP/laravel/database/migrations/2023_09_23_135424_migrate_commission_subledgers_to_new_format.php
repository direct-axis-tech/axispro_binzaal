<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class MigrateCommissionSubledgersToNewFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_gl_trans as trans')
            ->leftJoin('0_users as usr', function (JoinClause $join) {
                $join->whereRaw("usr.id = cast(if(trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$', replace(trans.person_id, 'USRCOMM', ''), NULL) as UNSIGNED)");
            })
            ->leftJoin('0_debtors_master as cust', function (JoinClause $join) {
                $join->whereRaw("cust.debtor_no = cast(if(trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$', NULL, replace(trans.person_id, 'CUSTCOMM', '')) as UNSIGNED)");
            })
            ->where('trans.person_type_id', PT_SUBLEDGER)
            ->whereIn('trans.account', [pref('axispro.emp_commission_payable_act', -1), pref('axispro.customer_commission_payable_act', -1)])
            ->whereRaw("(trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$' OR trans.person_id REGEXP '^CUSTCOMM[[:digit:]]{7}$')")
            ->update([
                'trans.person_type_id' => DB::raw("if(trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$', ".quote(PT_USER).", ".quote(PT_CUSTOMER).")"),
                'trans.person_id' => DB::raw("if(trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$', usr.id, cust.debtor_no)"),
                'trans.person_name' => DB::raw(
                    "if("
                        ."trans.person_id REGEXP '^USRCOMM[[:digit:]]{5}$',"
                        ." concat(usr.user_id, if(usr.real_name = '', '', concat(' - ', usr.real_name))),"
                        ." concat(cust.debtor_ref, IF(cust.`name` = '', '', concat(' - ', cust.`name`)))"
                    .")"
                )
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_gl_trans as trans')
            ->leftJoin('0_users as usr', function (JoinClause $join) {
                $join->on('trans.person_id', 'usr.id')
                    ->where('trans.person_type_id', PT_USER);
            })
            ->leftJoin('0_debtors_master as cust', function (JoinClause $join) {
                $join->on('cust.debtor_no', 'trans.person_id')
                    ->where('trans.person_type_id', PT_CUSTOMER);
            })
            ->whereIn('trans.person_type_id', [PT_USER, PT_CUSTOMER])
            ->whereIn('trans.account', [pref('axispro.emp_commission_payable_act', -1), pref('axispro.customer_commission_payable_act', -1)])
            ->update([
                'trans.person_type_id' => PT_SUBLEDGER,
                'trans.person_id' => DB::raw(
                    "if("
                        ."trans.person_type_id = ".quote(PT_USER).","
                        ." concat('USRCOMM', LPAD(usr.id, 5, '0')),"
                        ." concat('CUSTCOMM', LPAD(cust.debtor_no, 7, '0'))"
                    .")"
                ),
                'trans.person_name' => DB::raw(
                    "if("
                        ."trans.person_type_id = ".quote(PT_USER).","
                        ." concat(concat('USRCOMM', LPAD(usr.id, 5, '0')), if(usr.real_name = '', '', concat(' - ', usr.real_name))),"
                        ." concat(concat('CUSTCOMM', LPAD(cust.debtor_no, 7, '0')), IF(cust.`name` = '', '', concat(' - ', cust.`name`)))"
                    .")"
                )
            ]);
    }
}
