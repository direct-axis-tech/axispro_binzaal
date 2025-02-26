<?php

namespace App\Models\System;

use App\Permissions;
use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AccessRole extends Model
{
    use InactiveModel;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_security_roles';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The permissions assigned for this role
     *
     * @var array|null
     */
    private $permissions = null;

    /**
     * Get the array of permissions assigned for this role
     *
     * @return string
     */
    public function getPermissionsAttribute($value)
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $this->setPermissions();
        
        return $this->permissions;
    }

    /**
     * Get the name of this role
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        return $this->role;
    }

    /**
     * Checks if the role has a given permission or not
     *
     * @param string $id
     * @return boolean
     */
    public function hasPermission($id) {
        if ($this->permissions === null) {
            $this->setPermissions();
        }

        if (!preg_match('/^\d+$/', $id)) {
            $id = app(\App\Permissions::class)->getCode($id);
        }

        return isset($this->permissions[$id]);
    }

    /**
     * Initialize and store the permissions
     *
     * @return void
     */
    private function setPermissions() {
        $areas = explode(";", $this->areas);
        $sections = explode(";", $this->sections);
        $permissions = [];

        [
            'excludedHeads' => $excludedHeads, 
            'excludedGroups' => $excludedGroups, 
            'excludedGroupKeys' => $excludedGroupKeys
        ] = getExcludedModuleConfigurations();

        foreach($areas as $code) {
            // filter only area codes for enabled security sections
            if (
                in_array($code&~0xff, $sections)
                && !in_array($code, $excludedHeads)
                && (
                    !in_array(($code&~0xff), $excludedGroupKeys)
                    || in_array($code, $excludedGroups[($code&~0xff)])
                )
            ) 
                $permissions[$code] = $code;
        }

        $this->permissions = $permissions;
    }

    /**
     * The list of users who are assigned this access role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users() {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Grand access to given permissions
     *
     * @param array $permissions
     * @return void
     */
    public function grandAccess($permissions = null) {
        if (!is_array($permissions)) {
            $permissions = array_filter([$permissions]);
        }

        $areas = explode(";", $this->areas);
        $sections = explode(";", $this->sections);
        $permissionInstance = app(Permissions::class);

        [
            'excludedHeads' => $excludedHeads, 
            'excludedGroups' => $excludedGroups, 
            'excludedGroupKeys' => $excludedGroupKeys
        ] = getExcludedModuleConfigurations();

        foreach ($permissions as $permission) {
            $code = $permissionInstance->getCode($permission);
            $section = $code&~0xff;

            // Check if the permission can be granted
            if (!in_array($section, $excludedGroupKeys) || in_array($code, $excludedGroups[$section])) {
                if (!in_array($section, $sections)) {
                    $sections[] = $section;
                }
    
                if (!in_array($code, $areas)) {
                    $areas[] = $code;
                }
            }
        }

        $this->areas = implode(';', $areas);
        $this->sections = implode(';', $sections);
    }

    public function revokeAccess($permissions = null)
    {
        if (!is_array($permissions)) {
            $permissions = array_filter([$permissions]);
        }

        $areas = array_flip(explode(";", $this->areas));
        $permissionInstance = app(Permissions::class);

        foreach ($permissions as $permission) {
            $code = preg_match('/^\d+$/', $permission) ? $permission : $permissionInstance->getCode($permission);
            $section = $code&~0xff;

            if (isset($areas[$code])) {
                unset($areas[$code]);
            }
        }

        $this->areas = implode(';', array_keys($areas));
    }

    /**
     * Query the model that are granted with some of the given permissions
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $permissions
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGrantedWithAnyPermissions($builder, $permissions)
    {
        if (!is_array($permissions)) {
            $permissions = array_filter([$permissions]);
        }
        
        $instance = app(\App\Permissions::class);

        $query = [];
        $bindings = [];

        foreach ($permissions as $p) {
            if (!preg_match('/^\d+$/', $p)) {
                $p = $instance->getCode($p);
            }

            if (!$p) {
                continue;
            }

            $query[] = "FIND_IN_SET(?, REPLACE(areas, ';', ','))";
            $bindings[] = $p;
        }
        
        $builder->whereRaw('(' . implode(' OR ', $query) . ')', $bindings);

        return $builder;
    }
}
