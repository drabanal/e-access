<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getProfile()
    {
        $user = Auth::user();
        $user->employee;
        $user->full_name = $user->employee->empfname . ', ' . $user->employee->empgname;
        return Inertia::render('Profile', ['user' => $user]);
    }

    public function getProfileInfo()
    {
        try {
            $user = Auth::user();
            $user->employee;
            $user->full_name = $user->employee->empfname . ', ' . $user->employee->empgname;
            return $user;
        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }
}