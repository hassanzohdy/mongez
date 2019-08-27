<?php
namespace App\Modules\Users\Events;

use Illuminate\Http\Request;
use App\Modules\Users\Models\User;
use App\Modules\Departments\Models\Department as DepartmentModel;

class Department
{
    /**
     * Add task to its participants and supervisors
     * 
     * @param   \App\Modules\Departments\Models\Department $task
     * @param   \Illuminate\Http\Request $request
     * @return  void
     */
    public function onDepartmentUpdate(DepartmentModel $department, Request $request)
    {
        User::where('department.id', $department->id)->update([
            'department' => $department->sharedInfo(),
        ]);
    }
}