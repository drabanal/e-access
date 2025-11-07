<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use App\Models\LeaveStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveRequestRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveRequest();
    }

    public function getRequests($user_id, $leave_type_id, $leave_status_id, $date_range)
    {
        $query = $this->model->select('leave_requests.id', 'leave_requests.remarks', 'leave_requests.approve_reason', 'leave_requests.disapprove_reason', 'leave_requests.cancel_reason')
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.created_at, "%b %e, %Y") AS date_added'))
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_from, "%m/%d/%Y %h:%i %p") AS date_from'))
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_to, "%m/%d/%Y %h:%i %p") AS date_to'))
            ->addSelect(DB::raw('CONCAT(duration," hour(s)") AS duration'))
            ->addSelect('leave_types.name AS leave_type_name')
            ->addSelect(DB::raw("IF(DATEDIFF(date_time_from, CURDATE()) >= 0, TRUE, FALSE) 'editable'"))
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->where('user_id', $user_id);

        if (in_array(LeaveStatus::PENDING, $leave_status_id) && in_array(LeaveStatus::TL_APPROVED, $leave_status_id)) {
            $query->addSelect(DB::raw('IF(leave_status_id = 1,"Pending TL approval", IF(leave_status_id = 2,"Pending Admin approval","")) AS status'));
        }

        if (!is_null($leave_type_id)) {
            $query->where('leave_type_id', $leave_type_id);
        }

        if (!is_null($leave_status_id)) {
            $query->whereIn('leave_status_id', $leave_status_id);
        }

        if (!is_null($date_range)) {
            $query->whereBetween('date_time_from', $date_range);
        } else {
            $query->whereRaw(DB::raw("YEAR(date_time_from) = YEAR(CURDATE())"));
        }
        $order = (in_array(LeaveStatus::ADMIN_APPROVED,$leave_status_id)) ? 'DESC' : 'ASC';
        return $query->orderBy('date_time_from', $order)->get();
    }

    public function checkForConflict($id, $data)
    {
        $startDateTime = Carbon::parse($data['date_range'][0])->format('Y-m-d H:i');
        $endDateTime = Carbon::parse($data['date_range'][1])->format('Y-m-d H:i');

        $conflict = $this->model
            ->where('user_id', $data['user_id'])
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where('date_time_from', '<', $endDateTime)
                      ->where('date_time_to', '>', $startDateTime);
            })
            ->whereNotIn('leave_status_id', [4, 5]);

        if (!is_null($id)) {
            $conflict->where('id', '!=', $id);
        }
        
        return $conflict->first();
    }

    public function getApprovedLeavesByLeaveType($leave_type_id, $user_id)
    {
        $query = $this->model->select(DB::raw("SUM(duration) as 'leave_count'"))
            ->where('leave_status_id', '=', 3)
            ->where('user_id', $user_id)
            ->where('leave_type_id', $leave_type_id)
            ->whereRaw(DB::raw("YEAR(date_time_from) = YEAR(CURDATE())"))
            ->first();

        return $query ? $query->leave_count : 0;
    }

    public function getMembersPendingRequest($user_id, $role, $leave_type_id, $leave_status_id, $date_range)
    {
        
        $query = $this->model->select('leave_requests.id', 'leave_requests.remarks')
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.created_at, "%b %e, %Y") AS date_added'))
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_from, "%m/%d/%Y %h:%i %p") AS date_from'))
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_to, "%m/%d/%Y %h:%i %p") AS date_to'))
            ->addSelect(DB::raw('CONCAT(duration," hour(s)") AS duration'))
            ->addSelect('leave_types.name AS leave_type_name')
            ->addSelect(DB::raw('CONCAT(empfname,", ",empgname) AS full_name'))
            ->addSelect(DB::raw("TRUE AS 'editable'"))
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->join('users', 'users.id', '=', 'leave_requests.user_id')
            ->join('employees', 'employees.empid', '=', 'users.userid')
            ->whereRaw(DB::raw("YEAR(date_time_from) >= YEAR(CURDATE())"))
            ->addSelect(DB::raw('IF(leave_status_id = 1,"Pending TL approval", IF(leave_status_id = 2,"Pending Admin approval","")) AS status'));

        if ($role == User::TL_ROLE) {
            $query->where('employees.posid_man', $user_id);
        }

        if (!is_null($leave_type_id)) {
            $query->where('leave_type_id', $leave_type_id);
        }

        if ($role == User::ADMIN_ROLE) {
            $query->where(function ($qry) use ($leave_status_id, $user_id) {
                $qry->whereIn('leave_requests.leave_status_id', [$leave_status_id])
                    ->orWhere(function ($qry2) use ($user_id) {
                        $qry2->where('employees.posid_man', $user_id)
                            ->where('leave_requests.leave_status_id', LeaveStatus::PENDING);
                    });
            });
        } else {
            $query->whereIn('leave_requests.leave_status_id', [$leave_status_id]);
        }

        if (!is_null($date_range)) {
            $query->whereBetween('date_time_from', $date_range);
        }

        return $query->orderBy('date_time_from', 'ASC')->get();
    }

    public function getAllApprovedRequests()
    {
        $currentYear = Carbon::now()->format('Y');
        $yearRange = implode(',', [$currentYear - 1, $currentYear, $currentYear + 1]);

        $query = $this->model->select('leave_requests.id', 'leave_requests.leave_type_id', 'leave_requests.remarks', 'employees.teamname')
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_from, "%Y-%m-%d") AS start'))
            ->addSelect(DB::raw('DATE_FORMAT(leave_requests.date_time_to, "%Y-%m-%d") AS end'))
            ->addSelect('leave_types.name AS leave_type_name')
            ->addSelect(DB::raw('CONCAT(employees.empfname,", ",employees.empgname," (",DATE_FORMAT(leave_requests.date_time_from, "%H:%i: %p")," - ",DATE_FORMAT(leave_requests.date_time_to, "%H:%i: %p"),")") AS title'))
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->join('users', 'users.id', '=', 'leave_requests.user_id')
            ->join('employees', 'employees.empid', '=', 'users.userid')
            ->where('leave_status_id', 3)
            ->whereRaw(DB::raw("YEAR(date_time_from) IN ({$yearRange})"));

        return $query->orderBy('date_time_from', 'ASC')->get();
    }
}