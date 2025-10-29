<?php

namespace App\Repositories;


use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Employee();
    }

    public function getMembersBySupervisor($user, $keyword)
    {
        $query = $this->model->select('employees.empid','users.id AS user_id','empid AS employee_id', 'empstatus AS status', 'empfname', 'empgname')
            ->addSelect(DB::raw('CONCAT(empfname,", ",empgname) AS full_name'))
            ->join('users', 'users.userid', '=', 'employees.empid')
            ->whereIn('empstatus', [Employee::EMPLOYEE_STATUS_REGULAR, Employee::EMPLOYEE_STATUS_PROBATIONARY])
            ->orderBy('full_name', 'ASC');

        if ($user->userlevel == User::TL_ROLE) {
            $query->where('posid_man', $user->userid);
        }

        if (!is_null($keyword) && !empty($keyword)) {
            $query->where(function ($qry) use ($keyword) {
                $qry->where('empid', 'LIKE', "%{$keyword}%")
                    ->orWhere('empfname', 'LIKE', "%{$keyword}%")
                    ->orWhere('empgname', 'LIKE', "%{$keyword}%");
            });
        }
        return $query->get();
    }
}