<?php

namespace App\Models;

use App\Models\System\User;
use App\Traits\Notifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EntityGroup extends Model
{
    use Notifiable;

    /**
     * Group where the employee document expiry remiders would be sent to
     */
    const EMP_DOC_EXPIRY_REMINDER = 1;

    /**
     * Group where the labour income recognition notifications would be sent to
     */
    const LBR_INCOME_RECOGNITION_NOTIFICATION = 4;

    /**
     * Group where the labour expense recognition notifications would be sent to
     */
    const LBR_EXPENSE_RECOGNITION_NOTIFICATION = 5;

    /**
     * Group where the Leave accrual notifications would be sent to
     */
    const LEAVE_ACCRUAL_NOTIFICATION = 6;

    /**
     * Group where the Gratuity accrual notifications would be sent to
     */
    const GRATUITY_ACCRUAL_NOTIFICATION = 7;

    /**
     * A Dummy group to reserve the ids upto 1000
     */
    const DUMMY_GROUP = 1000;

    /**
     * Group where the installment reminder notifications would be sent to
     */
    const INSTALLMENT_REMINDER_NOTIFICATION = 8;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_entity_groups';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get all of the users that are members of this group
     */
    public function users()
    {
        return $this->morphedByMany(System\User::class, 'entity', '0_group_members', 'group_id')
            ->as('membership')
            ->using(EntityGroupMember::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Get all of the employees that are members of this group
     */
    public function employees()
    {
        return $this->morphedByMany(Hr\Employee::class, 'entity', '0_group_members', 'group_id')
            ->as('membership')
            ->using(EntityGroupMember::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Get all of the access roles that are members of this group
     */
    public function accessRoles(){
        return $this->morphedByMany(System\AccessRole::class, 'entity', '0_group_members', 'group_id')
            ->as('membership')
            ->using(EntityGroupMember::class)
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Get all users whose flow_group_id is this group
     */
    public function flowGroupMembers() {
        return $this->hasMany(System\User::class, 'flow_group_id');
    }

    /**
     * Get all the members associated with this group
     */
    public function members() {
        return $this->hasMany(EntityGroupMember::class, 'group_id');
    }

    /**
     * Get all the distinct active member users from this group
     *
     * @param User $user
     * @param array $visited This parameter is used for cycle detection
     * @return Collection|User[]
     */
    public function distinctMemberUsers($user = null, &$visited = [])
    {
        $collection = (new Collection())
            ->merge($this->flowGroupMembers);

        foreach ($this->members as $member) {
            // Prevent cycles, infinite recursion
            if ($member->entity_type == Entity::GROUP && in_array($member->entity_id, $visited)) {
                continue;
            }
            
            if (
                ($entity = Entity::find($member->entity_type))
                && ($users = $entity->resolveUsers($member->entity_id, $user, $visited))
                && !blank($users)
            ) {
                $collection = $collection->merge($users);
            }

            if ($member->entity_type == Entity::GROUP) {
                $visited[] = $member->entity_id;
            }
        }

        return $collection->unique()->where('inactive', '0');
    }
}
