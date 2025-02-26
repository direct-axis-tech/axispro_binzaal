<?php

namespace App\Http\Middleware\Hr;

use App\Http\Controllers\Hr\EmployeeController;
use App\Models\Hr\Employee;
use App\Permissions;
use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class EmployeeAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = authUser();

        // Make sure the employee exists
        abort_unless(($employee = Employee::find(Route::current()->parameter('employee'))), 404);

        // Make sure the user is authorized to view the profile of this employee
        abort_unless(
            app(EmployeeController::class)->builder(
                [
                    'auth' => true,
                    'employee_id' => $employee->id
                ],
                [
                    'OWN' => true,
                    'DEP' => $user->hasPermission(Permissions::HRM_VIEWEMPLOYEES_DEP),
                    'ALL' => $user->hasPermission(Permissions::HRM_VIEWEMPLOYEES_ALL),
                ],
                data_get($user->employee, 'id', -1)
            )->exists(),
            403
        );

        View::share('employee', $employee);

        return $next($request);
    }
}
