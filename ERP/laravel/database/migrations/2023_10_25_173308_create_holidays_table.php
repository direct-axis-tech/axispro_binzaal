<?php

use App\Models\Hr\Holiday;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class CreateHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('num_of_days');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('inactive')->nullable(false)->default(0);
            $table->smallInteger('created_by')->nullable();
            $table->smallInteger('updated_by')->nullable();
            $table->timestamps();
        });

        $now = date(DB_DATETIME_FORMAT);
        $baseQuery = DB::query()
            ->select(
                'c.date',
                'c.holiday_name',
                DB::raw('min(`holidays`.`date`) as start_date'),
                DB::raw('max(`holidays`.`date`) as end_date')
            )
            ->from('0_calendar as c')
            ->join('0_calendar as holidays', function (JoinClause $join) {
                $join->on('c.holiday_name', 'holidays.holiday_name')
                    ->whereRaw('c.`date` between date_sub(c.`date`, interval 10 day) and date_add(c.`date`, interval 10 day)')
                    ->whereRaw('`holidays`.`is_holiday`');
            })
            ->groupBy('c.date');
        
        Holiday::insertUsing(
            ['name', 'num_of_days', 'start_date', 'end_date', 'created_at', 'updated_at'],
            DB::query()
                ->select(
                    'holiday.holiday_name as name',
                    DB::raw('datediff(`holiday`.`end_date`, `holiday`.`start_date`) + 1 as num_of_days'),
                    'holiday.start_date',
                    'holiday.end_date',
                    DB::raw("'{$now}' as `created_at`"),
                    DB::raw("'{$now}' as `updated_at`"),
                )
                ->fromSub($baseQuery, 'holiday')
                ->groupBy('holiday.holiday_name', 'holiday.start_date', 'holiday.end_date')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_holidays');
    }
}
