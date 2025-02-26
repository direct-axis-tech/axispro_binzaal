<?php

use App\Models\Accounting\Dimension;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCenterTypeColToDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->integer('center_type')->nullable(false)->default(CENTER_TYPES['OTHER'])->after('name');
        });

        try {
            Dimension::query()
                ->update([
                    'center_type' => DB::raw(
                        "(CASE"
                            . " WHEN id = " . Dimension::AMER . " THEN " . CENTER_TYPES['AMER']
                            . " WHEN id = " . Dimension::TASHEEL . " THEN " . CENTER_TYPES['TASHEEL']
                            . " WHEN id = " . Dimension::RTA . " THEN " . CENTER_TYPES['RTA']
                            . " WHEN id = " . Dimension::DHA . " THEN " . CENTER_TYPES['DHA']
                            . " WHEN id = " . Dimension::DUBAI_COURT . " THEN " . CENTER_TYPES['DUBAI_COURT']
                            . " WHEN id = " . Dimension::DED . " THEN " . CENTER_TYPES['DED']
                            . " WHEN id = " . Dimension::ADHEED . " THEN " . CENTER_TYPES['ADHEED']
                            . " WHEN id = " . Dimension::TYPING . " THEN " . CENTER_TYPES['TYPING']
                            . " WHEN id = " . Dimension::EJARI . " THEN " . CENTER_TYPES['EJARI']
                            . " WHEN id = " . Dimension::TAWJEEH . " THEN " . CENTER_TYPES['TAWJEEH']
                            . " WHEN id = " . Dimension::TADBEER . " THEN " . CENTER_TYPES['TADBEER']
                            . " WHEN id = " . Dimension::DOMESTIC_WORKER . " THEN " . CENTER_TYPES['DOMESTIC_WORKER']
                            . " ELSE " . CENTER_TYPES['OTHER']
                        . " END)"
                    )
                ]);
        }

        catch (Throwable $e) {
            $this->down();

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            $table->dropColumn('center_type');
        });
    }
}
