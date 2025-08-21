<?php

namespace App\Repositories;

use App\Models\LeaveCreditsDetail;

class LeaveCreditsDetailRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveCreditsDetail();
    }
}