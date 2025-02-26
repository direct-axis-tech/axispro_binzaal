<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Hr\PayElement;

class CreateTableSubElements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_sub_elements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->integer('type');
            $table->integer('seq_no')->default(0);
            $table->boolean('inactive')->nullable(false)->default(0);
            $table->timestamps();
        });
       
        DB::table('0_sub_elements')->insert([
            [
                'name'     => 'Warning',
                'type'     => PayElement::TYPE_DEDUCTION,
                'seq_no'   => 1,
                'inactive' => 0
            ],
            [
                'name'     => 'Mistakes',
                'type'     => PayElement::TYPE_DEDUCTION,
                'seq_no'   => 2,
                'inactive' => 0
            ],
            [
                'name'     => 'Loan',
                'type'     => PayElement::TYPE_DEDUCTION,
                'seq_no'   => 3,
                'inactive' => 0
            ],
            [
                'name'     => 'Other Deductions',
                'type'     => PayElement::TYPE_DEDUCTION,
                'seq_no'   => 4,
                'inactive' => 0
            ],
            [
                'name'     => 'Reward',
                'type'     => PayElement::TYPE_ALLOWANCE,
                'seq_no'   => 5,
                'inactive' => 0
            ],
            [
                'name'     => 'Bonus',
                'type'     => PayElement::TYPE_ALLOWANCE,
                'seq_no'   => 6,
                'inactive' => 0
            ],
            [
                'name'     => 'Gift',
                'type'     => PayElement::TYPE_ALLOWANCE,
                'seq_no'   => 7,
                'inactive' => 0
            ],
            [
                'name'     => 'Certificate',
                'type'     => PayElement::TYPE_ALLOWANCE,
                'seq_no'   => 8,
                'inactive' => 0
            ],
            [
                'name'     => 'Other Allowances',
                'type'     => PayElement::TYPE_ALLOWANCE,
                'seq_no'   => 9,
                'inactive' => 0
            ]
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_sub_elements');
    }
}
