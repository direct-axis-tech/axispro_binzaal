<?php

use App\Models\Labour\Agent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixMissingRequiredDataInAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_suppliers')
            ->where('supplier_type', Agent::TYPE_AGENT)
            ->update([
                'tax_group_id' => DB::table('0_tax_groups as taxGroup')
                    ->leftJoin('0_tax_group_items as taxes', 'taxes.tax_group_id', 'taxGroup.id')
                    ->select('taxGroup.id')
                    ->groupBy('taxGroup.id')
                    ->havingRaw(DB::raw('count(`taxes`.`tax_type_id`) = 0'))
                    ->value('id'),
                'payment_terms' => DB::table('0_payment_terms')
                    ->where('days_before_due', '>', '0')
                    ->where('inactive', '0')
                    ->orderBy('days_before_due', 'desc')
                    ->take(1)
                    ->value('terms_indicator')
            ]);
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
