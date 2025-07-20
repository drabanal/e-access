<?php

namespace App\Repositories;


use App\Models\LeaveStatusLog;

class LeaveStatusLogRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveStatusLog();
    }
}