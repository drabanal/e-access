<?php

namespace App\Repositories;


use App\Models\LeaveCredit;

class LeaveCreditRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveCredit();
    }
}