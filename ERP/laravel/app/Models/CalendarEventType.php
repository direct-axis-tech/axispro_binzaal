<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class CalendarEventType extends Model
{
    use Sushi;

    /** @var string EMPDOC_EXPIRING Employee Document is expiring */
    const EMPDOC_EXPIRING = 1;

    /** @var string EMPDOC_EXPIRED Employee Document is expired */
    const EMPDOC_EXPIRED = 2;
    
    /** @var string LBRINCOME_RECOGNIZED Recognized income on labour invoice */
    const LBRINCOME_RECOGNIZED = 3;

    /** @var string LBREXPENSE_RECOGNIZED Recognized expense on labour purchase */
    const LBREXPENSE_RECOGNIZED = 4;

    /** @var string INSTALLMENT_REMINDER Reminder for installment on labour invoice */
    const INSTALLMENT_REMINDER = 5;

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The schema of this table
     *
     * @var array
     */
    protected $schema = [
        'id' => 'integer',
        'desc' => 'string',
        'class' => 'string'
    ];

    /**
     * The table
     * 
     * The reason for keeping this here is because
     * this data is highly cohesive with the source code.
     * So It doesn't make any sense to store it in a normal
     * database.
     *  
     * @var array
     */
    protected $rows = [
        [
            'id' => 1,
            'description' => "Employee document is near expiry",
            'class' => \App\Events\Hr\DocumentExpiring::class
        ],
        [
            'id' => 2,
            'description' => "Employee document is expired",
            'class' => \App\Events\Hr\DocumentExpired::class
        ],
        [
            'id' => 3,
            'description' => "Recognized income on maid invoice",
            'class' => \App\Events\Labour\IncomeRecognized::class
        ],
        [
            'id' => 4,
            'description' => "Recognized expense on maid purchase",
            'class' => \App\Events\Labour\ExpenseRecognized::class
        ],
        [
            'id' => 5,
            'description' => "Reminder for installment on maid invoice",
            'class' => \App\Events\Labour\InstallmentReminder::class
        ],
    ];
}
