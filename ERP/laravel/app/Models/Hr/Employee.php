<?php

namespace App\Models\Hr;

use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    use Notifiable;

    /** @var int ES_ALL Employment Status - All */
    const ES_ALL = 'all';

    /** @var int ES_ACTIVE Employment status - Active */
    const ES_ACTIVE = 1;

    /** @var int ES_RESIGNED Employment status - Resigned */
    const ES_RESIGNED = 2;

    /** @var int ES_TERMINATED Employment status - Terminated */
    const ES_TERMINATED = 3;

    /** @var int ES_RETIRED Employment status - Retired */
    const ES_RETIRED = 4;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_employees';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Cache for storing leave history
     *
     * @var array
     */
    private $leaveHistories = [];

    /**
     * Returns the transactions belonging to this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(\App\Models\Hr\EmployeeTransaction::class);
    }

    /**
     * The user associated with this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(\App\Models\System\User::class);
    }

    /**
     * The jobs this employee has been doing
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany(\App\Models\Hr\EmployeeJob::class);
    }

    /**
     * This employee's current job
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentJob()
    {
        return $this->hasOne(\App\Models\Hr\EmployeeJob::class)->where('is_current', 1);
    }

    /**
     * The salaries that was given to this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salaries()
    {
        return $this->hasMany(\App\Models\Hr\EmployeeSalary::class);
    }

    /**
     * This employee's current salary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentSalary()
    {
        return $this->hasOne(\App\Models\Hr\EmployeeSalary::class)->where('is_current', 1);
    }

    /**
     * This employee's nationality
     *
     * @return void
     */
    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class, 'nationality', 'code');
    }
    
    /**
     * This employee's nationality
     *
     * @return void
     */
    public function bank()
    {
        return $this->belongsTo(\App\Models\Bank::class);
    }

    /**
     * All the leaves taken by this employee
     *
     * @return void
     */
    public function leaves()
    {
        return $this->hasMany(\App\Models\Hr\EmployeeLeave::class);
    }

    /**
     * Shortcut for current salary
     *
     * @return float
     */
    public function getSalaryAttribute()
    {
        if (!$this->relationLoaded('currentSalary')) {
            $this->load('currentSalary');
        }

        return $this->currentSalary->gross_salary;
    }
    
    /**
     * Shortcut for current working company
     *
     * @return @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getWorkingCompanyAttribute()
    {
        return $this->currentJob->workingCompany;
    }

    /**
     * Shortcut for current visa company
     *
     * @return @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getVisaCompanyAttribute()
    {
        return $this->currentJob->visaCompany;
    }

    /**
     * Shortcut for formattedName
     */
    public function getFormattedNameAttribute()
    {
        return $this->emp_ref . ' - ' . $this->name;
    }

    /**
     * Shortcut for isActive
     */
    public function getIsActiveAttribute()
    {
        return $this->status == static::ES_ACTIVE;
    }

    /**
     * Prepare proper error handling for url attribute
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        return media('avatars/blank.png');
    }

    /**
     * Scopes this query with status active
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get all of the groups where this employee is a member
     */
    public function groups()
    {
        return $this->morphToMany(
            \App\Models\EntityGroup::class,
            'entity',
            '0_group_members',
            'entity_id',
            'group_id'
        )
            ->as('membership')
            ->using(\App\Models\EntityGroupMember::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Get channels through the employee needs to be notified
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return array
     */
    public function notificationsVia($notification = null) {
        $via = [];

        if (!empty($this->user)) {
            $via[] = 'database';
            $via[] = 'broadcast';
        }

        return $via;
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return $this->user ? "user.{$this->user->id}" : "employee.{$this->id}";
    }
}