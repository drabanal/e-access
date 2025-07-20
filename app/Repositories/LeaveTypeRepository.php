<?php

namespace App\Repositories;


use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;

class LeaveTypeRepository extends AbstractRepository
{
    public $model;

    public function __construct()
    {
        $this->model = new LeaveType();
    }

    public function getLeaveUsageForCurrentYear($user_id)
    {
        return $this->model->select('id', 'name')
            ->addSelect(DB::raw("(SELECT SUM(duration) FROM leave_requests WHERE user_id = {$user_id} AND leave_type_id = leave_types.id AND leave_status_id = 3 AND YEAR(date_time_from) = YEAR(CURDATE())) AS 'used'"))
            ->orderBy('id')
            ->get();
    }
}