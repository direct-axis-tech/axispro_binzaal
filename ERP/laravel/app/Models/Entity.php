<?php

namespace App\Models;

use App\Models\Hr\Employee;
use App\Models\System\AccessRole;
use App\Models\System\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Sushi\Sushi;

class Entity extends Model
{
    use Sushi;

    const USER = 1;
    const EMPLOYEE = 2;
    const DOCUMENT = 3;
    const GROUP = 4;
    const LABOUR = 5;
    const SPECIAL_GROUP = 6;
    const CUSTOMER = 7;
    const ACCESS_ROLE = 8;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The schema of this table
     *
     * @var array
     */
    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'table' => 'string',
        'primary_key' => 'string',
        'model' => 'string'
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
            'name' => 'User',
            'table' => '0_users',
            'primary_key' => 'id',
            'model' => System\User::class
        ],
        [
            'id' => 2,
            'name' => 'Employee',
            'table' => '0_employees',
            'primary_key' => 'id',
            'model' => Hr\Employee::class
        ],
        [
            'id' => 3,
            'name' => 'Document',
            'table' => '0_documents',
            'primary_key' => 'id',
            'model' => Document::class
        ],
        [
            'id' => 4,
            'name' => 'EntityGroup',
            'table' => '0_groups',
            'primary_key' => 'id',
            'model' => EntityGroup::class
        ],
        [
            'id' => 5,
            'name' => 'DomesticWorker',
            'table' => '0_labours',
            'primary_key' => 'id',
            'model' => Labour\Labour::class
        ],
        [
            'id' => 6,
            'name' => 'SpecialEntities',
            'table' => 'special_entities',
            'primary_key' => 'id',
            'model' => SpecialEntities::class
        ],
        [
            'id' => 7,
            'name' => 'Customer',
            'table' => '0_debtors_master',
            'primary_key' => 'debtor_no',
            'model' => Sales\Customer::class
        ],
        [
            'id' => 8,
            'name' => 'AccessRole',
            'table' => '0_security_roles',
            'primary_key' => 'id',
            'model' => System\AccessRole::class
        ]
    ];

    /**
     * Resolve the users from the given entity
     *
     * @param string $entityId
     * @param User $currentUser
     * @param array $visited This parameter is used for cycle detection
     * 
     * @return Collection|User[]|null
     */
    public function resolveUsers($entityId, User $currentUser = null, &$visited = [])
    {
        switch ($this->id) {
            case static::USER:
                $collection = new Collection(Arr::wrap(User::find($entityId)));
                break;

            case static::EMPLOYEE:
                $collection = new Collection(Arr::wrap(data_get(Employee::find($entityId), 'user')));
                break;

            case static::GROUP:
                $group = EntityGroup::find($entityId);
                $collection = $group ? $group->distinctMemberUsers($currentUser, $visited) : null;
                break;

            case static::SPECIAL_GROUP:
                $specialEntity = SpecialEntities::find($entityId);
                $collection = $specialEntity ? $specialEntity->resolveUsers($currentUser) : null;
                break;

            case static::ACCESS_ROLE:
                $users = data_get(AccessRole::find($entityId), 'users');
                $collection = $users ? $users->where('inactive', '0') : null;
                break;
                
            default:
                $collection = null;
        }

        return is_null($collection) ? null : $collection->where('inactive', 0);
    } 
}
