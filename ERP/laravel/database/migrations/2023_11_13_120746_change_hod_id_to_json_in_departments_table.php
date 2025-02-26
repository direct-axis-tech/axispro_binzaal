<?php

use App\Models\Hr\Department;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeHodIdToJsonInDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $departments = DB::table('0_departments')->get();
        
        Schema::table('0_departments', function (Blueprint $table) {
            $table->json('hod_id')->nullable()->default('[]')->change();
        });

        $departments->each(function ($item) {
            DB::table('0_departments')
                ->where('id', $item->id)
                ->update(['hod_id' => json_encode(array_filter(["{$item->hod_id}"]))]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $departments = DB::table('0_departments')->get();;
        
        Department::query()->update(['hod_id' => null]);

        Schema::table('0_departments', function (Blueprint $table) {
            $table->bigInteger('hod_id')->nullable()->default(null)->charset(null)->collation(null)->change();
        });

        $departments->each(function ($item) {
            DB::table('0_departments')
                ->where('id', $item->id)
                ->update(['hod_id' => Arr::first(json_decode($item->hod_id))]);
        });
    }
}
