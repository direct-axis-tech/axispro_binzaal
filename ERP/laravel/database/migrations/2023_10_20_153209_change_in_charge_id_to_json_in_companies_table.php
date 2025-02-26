<?php

use App\Models\Hr\Company;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInChargeIdToJsonInCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = DB::table('0_companies')->get();
        
        Schema::table('0_companies', function (Blueprint $table) {
            $table->json('in_charge_id')->nullable()->default('[]')->change();
        });

        $companies->each(function ($item) {
            DB::table('0_companies')
                ->where('id', $item->id)
                ->update(['in_charge_id' => json_encode(array_filter(["{$item->in_charge_id}"]))]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $companies = DB::table('0_companies')->get();;
        
        Company::query()->update(['in_charge_id' => null]);

        Schema::table('0_companies', function (Blueprint $table) {
            $table->bigInteger('in_charge_id')->nullable()->default(null)->charset(null)->collation(null)->change();
        });

        $companies->each(function ($item) {
            DB::table('0_companies')
                ->where('id', $item->id)
                ->update(['in_charge_id' => Arr::first(json_decode($item->in_charge_id))]);
        });
    }
}
