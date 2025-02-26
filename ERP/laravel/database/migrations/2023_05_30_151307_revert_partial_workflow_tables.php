<?php

use App\Models\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RevertPartialWorkflowTables extends Migration
{
    const SUPERVISOR = 2;
    const PURCHASE_MANAGER = 3;
    const ISMAIL = 402;
    const ITEM_REQUEST = 1;
    const LEVEL_ONE = 1;
    const LEVEL_TWO = 2;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('0_activities');
        Schema::dropIfExists('0_activity_details');
        Schema::dropIfExists('0_activity_flow');
        Schema::dropIfExists('0_activity_levels');
        Schema::dropIfExists('0_activity_log');
        Schema::dropIfExists('0_activity_types');
        Schema::dropIfExists('0_rfq');
        
        Schema::dropIfExists('0_rfq_details');
        Schema::dropIfExists('0_rfq_suppliers');
        Schema::table('0_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('activity_id');
            Schema::table('0_attendance_metrics', function (Blueprint $table) {
                $table->dropColumn('request');
                $table->dropColumn('request_by');
                $table->dropColumn('request_date');
                $table->dropColumn('status_l1');
                $table->dropColumn('reviewed_by_l1');
                $table->dropColumn('reviewed_at_l1');
            });
        });

        DB::transaction(function () {
            DB::table('0_entity_groups')
                ->whereIn('id', [self::PURCHASE_MANAGER, self::SUPERVISOR])
                ->delete();
            DB::table('0_group_members')
                ->where('group_id', '=', self::PURCHASE_MANAGER)
                ->where('entity_type', '=', Entity::USER)
                ->where('entity_id', '=', self::ISMAIL)->delete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('0_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('activity_type_id');
            $table->bigInteger('user_id');
            $table->dateTime('date');
            $table->longText('remarks')->nullable()->default(null);
            $table->smallInteger('isdone')->default(0);
            $table->smallInteger('inactive')->default(0);
            $table->timestamps();
        });
        Schema::create('0_activity_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('activity_id');
            $table->bigInteger('activity_flow_id');
            $table->bigInteger('user_id');
            $table->smallInteger('isdone')->default(0);
            $table->dateTime('isdone_at')->nullable()->default(null);
            $table->smallInteger('is_rejected')->default(0);
            $table->bigInteger('is_rejected_at')->nullable()->default(null);
            $table->timestamps();
        });
        Schema::create('0_activity_flow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('activity_type_id');
            $table->bigInteger('activity_level_id');
            $table->smallInteger('is_user')->default(0);
            $table->bigInteger('user_id')->nullable()->default(null);
            $table->bigInteger('entity_group_id')->nullable()->default(null);
            $table->text('notification_heading')->nullable()->default(NULL);
            $table->longText('notification')->nullable()->default(NULL);
            $table->mediumText('approve_msg')->nullable()->default(null);
            $table->mediumText('reject_msg')->nullable()->default(null);
            $table->decimal('auto_remind_after', 8, 2)->nullable()->default(null);
            $table->mediumText('auto_remind_msg')->nullable()->default(null);
            $table->decimal('auto_reject_after', 8, 2)->nullable()->default(null);
            $table->mediumText('auto_reject_msg')->nullable()->default(null);
            $table->smallInteger('inactive')->default(0);
            $table->timestamps();
        });
        Schema::create('0_activity_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('level');
        });
        Schema::create('0_activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('activity_id');
            $table->bigInteger('user_id');
            $table->text('description')->nullable()->default(null);
            $table->dateTime('created_at');
        });
        Schema::create('0_activity_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('activity');
            $table->longText('description')->nullable()->default(null);
            $table->smallInteger('inactive')->default(0);
            $table->dateTime('created_at');
        });
        Schema::create('0_rfq', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('rfq_no');
            $table->date('rfq_date')->nullable()->default(null);
            $table->longText('memo')->nullable()->default(null);
            $table->text('supplier_memo')->nullable()->default(null);
            $table->boolean('inactive')->default(false);
            $table->timestamps();
        });
        Schema::create('0_rfq_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('rfq_no');
            $table->text('item_code');
            $table->text('item_desc');
            $table->text('quantity');
            $table->longText('detail_memo')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->boolean('inactive')->default(false);
            $table->timestamps();
        });
        Schema::create('0_rfq_suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('rfq_no');
            $table->bigInteger('supplier_id');
            $table->boolean('inactive')->default(false);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('0_attendance_metrics', function (Blueprint $table) {
            $table->string('request', 500)->nullable()->after('amount');
            $table->bigInteger('request_by')->nullable()->after('request');
            $table->dateTime('request_date')->nullable()->after('request_by');
            $table->char('status_l1', 1)->nullable()->default('V')->after('request_date');
            $table->bigInteger('reviewed_by_l1')->nullable()->after('status_l1');
            $table->dateTime('reviewed_at_l1')->nullable()->after('reviewed_by_l1');
        });
        Schema::table('0_purchase_requests', function (Blueprint $table) {
            $table->text('activity_id')->nullable()->default(NULL)->after('id');
        });
        DB::transaction(function () {
            DB::table('0_activity_levels')->insert([
                ['level' => 'Level 1'],
                ['level' => 'Level 2'],
                ['level' => 'Level 3'],
                ['level' => 'Level 4'],
                ['level' => 'Level 5'],
                ['level' => 'Level 6'],
            ]); 
            DB::table('0_activity_types')->insert(
                array(
                    'id' => self::ITEM_REQUEST,
                    'activity' => 'Item Request',
                    'description' => 'Send notification to request items from Purchase department',
                    'created_at' => date(DB_DATETIME_FORMAT)
                )
            );
            DB::table('0_entity_groups')->insert([
                [
                    'id' => self::SUPERVISOR,
                    'name' => 'User Manager',
                    'description' => 'User Manager (This entity group member will be zero and the system will auto detect his manager once it is zero)',
                    'created_at' => date(DB_DATETIME_FORMAT),
                    'updated_at' => date(DB_DATETIME_FORMAT)
                ],
                [
                    'id' => self::PURCHASE_MANAGER,
                    'name' => 'Purchase Manager',
                    'description' => 'The Purchase Manager',
                    'created_at' => date(DB_DATETIME_FORMAT),
                    'updated_at' => date(DB_DATETIME_FORMAT)
                ],
            ]);
            DB::table('0_activity_flow')->insert(
                array(
                    [
                        'activity_type_id' => self::ITEM_REQUEST,
                        'activity_level_id' => self::LEVEL_ONE,
                        'entity_group_id' => self::SUPERVISOR,
                        'created_at' => date(DB_DATETIME_FORMAT),
                        'updated_at' => date(DB_DATETIME_FORMAT)
                    ],
                    [
                        'activity_type_id' => self::ITEM_REQUEST,
                        'activity_level_id' => self::LEVEL_TWO,
                        'entity_group_id' => self::PURCHASE_MANAGER,
                        'created_at' => date(DB_DATETIME_FORMAT),
                        'updated_at' => date(DB_DATETIME_FORMAT)
                    ]
                )
            );
        });
    }
}
