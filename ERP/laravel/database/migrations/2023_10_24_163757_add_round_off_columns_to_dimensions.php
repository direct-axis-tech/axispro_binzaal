<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoundOffColumnsToDimensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_dimensions', function (Blueprint $table) {
            if (!Schema::hasColumn('0_dimensions', 'round_off_to')) {
                $table->decimal('round_off_to', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('0_dimensions', 'round_off_algorithm')) {
                $table->integer('round_off_algorithm')->nullable();
            }
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
            $table->dropColumn('round_off_to');
            $table->dropColumn('round_off_algorithm');
        });
    }
}
