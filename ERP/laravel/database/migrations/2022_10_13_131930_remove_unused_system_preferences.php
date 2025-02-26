<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnusedSystemPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('0_sys_prefs')
            ->whereIn('name', [
                'payroll_cutoff_date',
                'payroll_emp_pf_percent',
                'payroll_esb_account',
                'payroll_gorss_salary_act',
                'payroll_grace_time',
                'payroll_overtime_rate',
                'payroll_pf_comp_percent',
                'payroll_salary_deduction',
                'payroll_work_hours_to',
                'prefix_asset',
                'prefix_asset_return',
                'prefix_certi',
                'prefix_leave',
                'prefix_loan',
                'prefix_noc',
                'prefix_passport',
                'privacy_policy',
                'leave_request_pfx',
                'loan_request_pfx',
                'asset_request_pfx',
                'asset_return_req_pfx',
                'certif_request_pfx',
                'code_of_conduct',
                'noc_request_pfx',
                'passport_request_pfx'
            ])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('0_sys_prefs')->insert([
            ['name' => 'payroll_cutoff_date',       'category' => '',               'type' => 'int',        'length' => '0',    'value' => '25'     ],
            ['name' => 'payroll_emp_pf_percent',    'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => '5'      ],
            ['name' => 'payroll_esb_account',       'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'payroll_gorss_salary_act',  'category' => '',               'type' => 'int',        'length' => '0',    'value' => ''       ],
            ['name' => 'payroll_grace_time',        'category' => 'setup.axispro',  'type' => 'int',        'length' => '11',   'value' => '10'     ],
            ['name' => 'payroll_overtime_rate',     'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => '1.5'    ],
            ['name' => 'payroll_pf_comp_percent',   'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => '10'     ],
            ['name' => 'payroll_salary_deduction',  'category' => '',               'type' => 'int',        'length' => '0',    'value' => ''       ],
            ['name' => 'payroll_work_hours_to',     'category' => '',               'type' => 'int',        'length' => '0',    'value' => '18'     ],
            ['name' => 'prefix_asset',              'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_asset_return',       'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_certi',              'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_leave',              'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_loan',               'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_noc',                'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'prefix_passport',           'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'privacy_policy',            'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'leave_request_pfx',         'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'loan_request_pfx',          'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'asset_request_pfx',         'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'asset_return_req_pfx',      'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'certif_request_pfx',        'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'code_of_conduct',           'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'noc_request_pfx',           'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
            ['name' => 'passport_request_pfx',      'category' => '',               'type' => 'varchar',    'length' => '0',    'value' => ''       ],
        ]);
    }
}
