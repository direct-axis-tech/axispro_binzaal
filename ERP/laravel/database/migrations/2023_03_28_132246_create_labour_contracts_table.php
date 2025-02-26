<?php

use App\Models\Labour\Contract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabourContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('0_labour_contracts')) {
            Schema::create('0_labour_contracts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('type');
                $table->bigInteger('contract_no');
                $table->string('reference');
                $table->bigInteger('debtor_no')->index();
                $table->integer('category_id');
                $table->bigInteger('labour_id')->index();
                $table->date('contract_from')->index();
                $table->date('contract_till')->index();
                $table->decimal('amount', 14);
                $table->longText('memo')->nullable();
                $table->boolean('inactive')->default(false);
                $table->timestamps();
            });
        }

        DB::table('0_meta_transactions')->insert([
            [
                'id' => Contract::CONTRACT,
                'name' => 'Labour Contract',
                'table' => '0_labour_contracts',
                'col_type' => 'type',
                'col_trans_no' => 'contract_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'contract_from',
                'next_trans_no' => 0
            ],
            [
                'id' => Contract::TEMPORARY_CONTRACT,
                'name' => 'Trial Labour Contract',
                'table' => '0_labour_contracts',
                'col_type' => 'type',
                'col_trans_no' => 'contract_no',
                'col_reference' => 'reference',
                'col_trans_date' => 'contract_from',
                'next_trans_no' => 0
            ],
        ]);

        DB::table('0_reflines')->insert([
            [
                "trans_type" => Contract::TEMPORARY_CONTRACT,
                "prefix" => '',
                "pattern" => 'TLC{YY}{0001}',
                "description" => '',
                "default" => 1,
                "inactive" => 0
            ],
            [
                "trans_type" => Contract::CONTRACT,
                "prefix" => '',
                "pattern" => 'LC{YY}{0001}',
                "description" => '',
                "default" => 1,
                "inactive" => 0
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_labour_contracts');
        DB::table('0_reflines')->whereIn('trans_type', [Contract::CONTRACT, Contract::TEMPORARY_CONTRACT])->delete();
        DB::table('0_meta_transactions')->whereIn('id', [Contract::CONTRACT, Contract::TEMPORARY_CONTRACT])->delete();
        DB::table('0_meta_references')->whereIn('type', [Contract::CONTRACT, Contract::TEMPORARY_CONTRACT])->delete();
    }
}
