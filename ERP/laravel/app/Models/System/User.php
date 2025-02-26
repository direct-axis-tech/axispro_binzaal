<?php

namespace App\Models\System;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Permissions;
use App\Traits\InactiveModel;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable, InactiveModel;

    const SYSTEM_USER = 32767;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_users';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'language' => 'C',
        'date_format' => '4',
        'date_sep' => '2',
        'tho_sep' => '0',
        'dec_sep' => '0',
        'theme' => 'daxis',
        'page_size' => 'A4',
        'prices_dec' => '2',
        'qty_dec' => '2',
        'rates_dec' => '4',
        'percent_dec' => '1',
        'show_gl' => '1',
        'show_codes' => '1',
        'show_hints' => '1',
        'query_size' => '10',
        'graphic_links' => '1',
        'pos' => '2',
        'print_profile' => '',
        'rep_popup' => '1',
        'sticky_doc_date' => '0',
        'startup_tab' => 'sales',
        'transaction_days' => '0',
        'save_report_selections' => '0',
        'use_date_picker' => '1',
        'def_print_destination' => '0',
        'def_print_orientation' => '0',
        'user_language' => '',
    ];

    /**
     * Get a fullname combination of first_name and last_name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->real_name ?: $this->user_id;
    }

    /**
     * Get the formatted name
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        return $this->user_id . ' - ' . $this->name;
    }

    /**
     * Get the authorized departments for this user
     */
    public function getAuthorizedDimensionsAttribute()
    {
        $defaultDim = $this->dflt_dimension_id;
        $allowedDims = explode(',', $this->allowed_dims ?: '');
        $all = array_merge([$defaultDim], $allowedDims);

        return array_filter(array_unique($all));
    }

    /**
     * Get the authorized categories for invoicing
     *
     * @return array
     */
    public function getAuthorizedCategoriesAttribute()
    {
        return explode(',', $this->permitted_categories ?: '');
    }

    /**
     * Prepare proper error handling for url attribute
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if (
            $this->employee
            && !empty($this->employee->profile_photo)
            && Storage::exists($this->employee->profile_photo)
        ) {
            return url(Storage::url($this->employee->profile_photo));
        }
        return media('avatars/blank.png');
    }

    /**
     * Apply the default for missing home url
     */
    public function getHomeUrlAttribute($value)
    {
        if (!empty($value) && !Str::startsWith($value, 'http')) {
            return '/' . ltrim($value, '/');
        }
        return '/v3/dashboard';
    }
    
    /**
     * Check if the user is having the specified permission
     *
     * @param string $key
     * @return boolean
     */
    public function hasPermission($key)
    {
        if (empty($key)) {
            return false;
        }

		if (in_array($key, [Permissions::OPEN, Permissions::ALLOWED], true)) {
			return true;
        }

		if (in_array($key, [Permissions::DENIED], true)) {
			return false;
        }

		$permissionId = app(Permissions::class)->getCode($key);

        return $this->role->hasPermission($permissionId);
    }

    /**
     * Check if the user is not having the specified permission
     * 
     * @param string $key
     * @return boolean
     */
    public function doesntHavePermission($key)
    {
        return !$this->hasPermission($key);
    }

    /**
     * Check if the user is having any of the specified permissions
     * 
     * @param string[] $permissions
     * @return boolean
     */
    public function hasAnyPermission($permissions = [])
    {
        if (!is_array($permissions)) {
            $permissions = func_get_args();
        }
        
        foreach($permissions as $p) {
            if ($this->hasPermission($p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The access role assigned to this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(\App\Models\System\AccessRole::class);
    }

    /**
     * The emoloyee associated with this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }

    /**
     * Get all of the groups where this user is a member
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
     * Get channels through the user needs to be notified
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return array
     */
    public function notificationsVia($notification = null) {
        return ['database', 'broadcast'];
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return "user.{$this->id}";
    }

    /**
     * Queries the authorized users list
     *
     * @param array $canAccess
     * @return Builder
     */
    public function scopeAuthorized(Builder $query, $canAccess = [], $user = null)
    {
        if (is_null($user)) {
            $user = authUser();
        }

        $query->where(function (Builder $query) use ($canAccess, $user) {
            $query->whereRaw('true');
            if (!$canAccess['ALL']) {
                $query->where('dflt_dimension_id', $user->dflt_dimension_id);

                if (!$canAccess['DEP']) {
                    $query->where('id', $user->id);
                }
            }
        });

        return $query;
    }
}
