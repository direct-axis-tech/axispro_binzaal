<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImmRelatedColumnsToAutofetchedTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->change();
            $table->decimal('amount_paid', 14, 2)->nullable()->after('total');
            $table->dateTime('paid_at')->nullable()->after('webuser_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('0_autofetched_trans', function (Blueprint $table) {
            $table->dropColumn('amount_paid', 'paid_at');
        });
    }
}
