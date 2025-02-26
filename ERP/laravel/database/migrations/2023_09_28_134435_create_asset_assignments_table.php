<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('0_asset_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stock_id', 50);
            $table->integer('assignee');
            $table->integer('is_employee')->comment('1- Employee, 2- Department');
            $table->integer('status')->comment('1- Active, 0- Returned/Inacive')->default(1);
            $table->datetime('assigned_date');
            $table->datetime('returned_date');
            $table->string('remarks', 250)->nullable();
            $table->integer('assigned_by')->default(0);
            $table->integer('returned_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('0_asset_assignments');
    }
}
