<?php

namespace App\Models;

use App\Models\System\User;
use Arr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class SpecialEntities extends Model
{
    use Sushi;

    const LINE_SUPERVISOR = 1;
    const DEPARTMENT_HEAD = 2;
    const WORKING_COM_IN_CHARGE = 3;
    const VISA_COM_IN_CHARGE = 4;
    const APPLICANT = 5;
    const AUTO_APPROVER = 6;
    
    /**
     * The schema of this table
     *
     * @var array
     */
    protected $schema = [
        'id' => 'integer',
        'name' => 'string'
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
            'name' => 'Line Supervisor'
        ],
        [
            'id' => 2,
            'name' => 'Department Head'
        ],
        [
            'id' => 3,
            'name' => 'Working Company In Charge'
        ],
        [
            'id' => 4,
            'name' => 'Visa Company In Charge'
        ],
        [
            'id' => 5,
            'name' => 'Applicant'
        ],
        [
            'id' => 6,
            'name' => 'Auto Approver'
        ]
    ];

    /**
     * Resolve the users from the given entity
     *
     * @param User $currentUser
     * @return Collection|User[]|null
     */
    public function resolveUsers(User $currentUser = null)
    {
        if (!data_get($currentUser, 'employee')) {
            return null;
        }

        $job = $currentUser->employee->currentJob;
        switch ($this->id) {
            case SpecialEntities::LINE_SUPERVISOR:
                $employeeIds = json_decode($job->supervisor_id, true);
                break;

            case SpecialEntities::DEPARTMENT_HEAD:
                $employeeIds = $job->department->hod_id;
                break;

            case SpecialEntities::WORKING_COM_IN_CHARGE:
                $employeeIds = $job->workingCompany->in_charge_id;
                break;

            case SpecialEntities::VISA_COM_IN_CHARGE:
                $employeeIds = $job->visaCompany->in_charge_id;
                break;

            case SpecialEntities::APPLICANT:
                return new Collection([$currentUser]);

            case SpecialEntities::AUTO_APPROVER:
                return new Collection();

            default:
                return null;
        }

        return User::whereType(Entity::EMPLOYEE)
            ->whereIn('employee_id', Arr::wrap($employeeIds))
            ->get();
    } 
}
