<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends AbstractRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new User();
    }
}