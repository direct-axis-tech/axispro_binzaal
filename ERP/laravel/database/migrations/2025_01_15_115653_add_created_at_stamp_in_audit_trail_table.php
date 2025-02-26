<?php

use App\Traits\MigratesData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedAtStampInAuditTrailTable extends Migration
{
    use MigratesData;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_audit_trail', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable(false)->after('stamp');
        });

        $this->migrateData(function () {
            DB::table('0_audit_trail')->update([
                'stamp' => DB::raw('stamp'),
                'created_at' => DB::raw('DATE_ADD(stamp, INTERVAL 4 HOUR)'),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_audit_trail', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
}
