<?php

namespace App\Http\Controllers;

use App\Models\LeaveStatus;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Services\EmployeeService;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeamController extends Controller
{
    protected $employeeRepository;
    protected $employeeService;
    protected $leaveService;
    
    public function __construct()
    {
        $this->employeeRepository = new EmployeeRepository();
        $this->employeeService = new EmployeeService();
        $this->leaveService = new LeaveService();
    }

    public function showMembersPage()
    {
        $leaveTypes = [
            [
                'name' => 'All Leaves',
                'id' => 0
            ]
        ];

        foreach (LeaveType::all() as $leaveType) {
            array_push($leaveTypes, [
                'name' => $leaveType->name,
                'id' => $leaveType->id
            ]);
        }

        return Inertia::render('Team/Members', ['leaveTypes' => $leaveTypes, 'user' => Auth::user()->employee]);
    }

    public function getMembers(Request $request)
    {
        try {
            $keyword = ($request->input('keyword')) ? trim($request->input('keyword')) : null;

            $members = $this->employeeRepository->getMembersBySupervisor(Auth::user(), $keyword);

            $members->map(function ($item) {
                $user = $item->user;
                $item->credits = $this->leaveService->getLeaveCredits($user);
            });

            return $members;
        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }

    public function getEmployee($id)
    {
        try {
            $user = $this->employeeService->getUser($id);

            if (!$user) {
                return response()->json('User not found!')->setStatusCode(404);
            }

            $employee = $user->employee;
            $employee->name = ucwords(strtolower($employee->getFullNameAttribute()));

            return response()->json($employee)->setStatusCode(200);

        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }

    public function showTeamRequestsPage()
    {
        $leaveTypes = [
            [
                'name' => 'All Leaves',
                'id' => 0
            ]
        ];

        foreach (LeaveType::all() as $leaveType) {
            array_push($leaveTypes, [
                'name' => $leaveType->name,
                'id' => $leaveType->id
            ]);
        }

        return Inertia::render('Team/Pending', ['leaveTypes' => $leaveTypes, 'user' => Auth::user()->employee]);
    }

    public function getMembersPendingRequests(Request $request)
    {
        try {
            $user = Auth::user();
            $leave_type = ($request->input('leave_type_id')) ? $request->input('leave_type_id') : null;
            $date_range = ($request->input('date_filter')) ? $request->input('date_filter') : null;
            $leave_status = ($user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;

            return $this->leaveService->getMembersPendingRequest($user->userid, $user->userlevel, $leave_type, $leave_status, $date_range);

        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }
}