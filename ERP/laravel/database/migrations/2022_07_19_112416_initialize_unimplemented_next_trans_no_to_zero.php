<?php

use App\Models\MetaTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InitializeUnimplementedNextTransNoToZero extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_meta_transactions')
            ->whereNotIn('id', MetaTransaction::getImplemented())
            ->update(['next_trans_no' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to default 1
        DB::table('0_meta_transactions')
            ->whereNotIn('id', MetaTransaction::getImplemented())
            ->update(['next_trans_no' => 1]);
    }
}
