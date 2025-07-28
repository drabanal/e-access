<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Carbon\Carbon;
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

        $user->employee->formatted_hired_date = Carbon::parse($user->employee->empdatehired)->format('M j, Y');
        $user->employee->formatted_probationary_date_from = Carbon::parse($user->employee->empprobfrom)->format('M j, Y');
        $user->employee->formatted_probationary_date_to = Carbon::parse($user->employee->empprobto)->format('M j, Y');
        $user->employee->formatted_regularization_date = $user->employee->empdateofreg ? Carbon::parse($user->employee->empdateofreg)->format('M j, Y') : '--';

        return Inertia::render('Profile', ['user' => $user]);
    }
}