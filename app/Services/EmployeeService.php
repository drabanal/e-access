<?php

namespace App\Services;

use App\Repositories\UserRepository;

class EmployeeService
{
    protected $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getUser($id)
    {
        return $this->userRepository->find($id);
    }
}