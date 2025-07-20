<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveStatus;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveController extends Controller
{
    protected $leaveService;

    public function __construct()
    {
        $this->leaveService = new LeaveService();
    }

    public function showLeavesPage($status)
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
        return Inertia::render('Leaves/'.ucfirst($status), [
            'leaveTypes' => $leaveTypes,
            'employee' => Auth::user()->employee
        ]);
    }
    
    public function getLeaveCredits(Request $request)
    {
        try {
            $user = ($request->input('user_id')) ? $this->leaveService->getUserById($request->input('user_id')) : Auth::user();

            return $this->leaveService->getLeaveCredits($user);

        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }

    public function getLeaveRequests(Request $request)
    {
        try {
            $user_id = ($request->input('user_id')) ? $request->input('user_id') : Auth::user()->id;
            $leave_type = ($request->input('leave_type_id')) ? $request->input('leave_type_id') : null;
            $leave_status = ($request->input('leave_status')) ? explode(',', $request->input('leave_status')) : null;
            $date_range = ($request->input('date_filter')) ? $request->input('date_filter') : null;
            
            return $this->leaveService->getRequests($user_id, $leave_type, $leave_status, $date_range);
        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }
    
    public function addLeaveRequestPage(Request $request)
    {
        $user = $request->input('user_id') ? User::find($request->input('user_id')) : Auth::user();
        if (!$user) {
            abort(404);
        }

        $user->employee;
        return Inertia::render('Leaves/Add', [
            'leaveTypes' => LeaveType::all(),
            'user' => $user,
            'inBehalf' => $request->input('user_id') ? true : false
        ]);
    }

    public function postRequestLeave(Request $request)
    {
        try {
            $data = $request->all();
            $authUser = Auth::user();

            if ($data['user_id'] != $authUser->id) {
                $leave_user = $this->leaveService->getUserById($data['user_id']);
                $data['leave_status_id'] = ($leave_user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;
                $data['remarks'] = '[TL OVERRIDE]: '.$data['remarks'];
            } else {
                $data['leave_status_id'] = ($authUser->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;
            }

            $validate = $this->leaveService->validateLeave(null, $data);

            if (!$validate->is_valid) {
                return response()->json(['message' => $validate->message])->setStatusCode(400);
            }

            $inputted_start_date = Carbon::parse($data['date_range'][0]);
            $inputted_end_date = Carbon::parse($data['date_range'][1]);
            $diff = $inputted_end_date->diff($inputted_start_date);

            unset($data['date_range']);

            $num_of_days = $diff->days + 1;

            for ($i = 0; $i < $num_of_days; $i++) {
                $end_date_format = "Y-m-d {$inputted_end_date->format('H:i')}";

                if ((int) $inputted_start_date->format('H') > (int) $inputted_end_date->format('H')) {
                    $start_date = $inputted_start_date;
                    $data['date_time_from'] = $start_date->format('Y-m-d H:i');
                    $end_date = $start_date->addDay();
                    $data['date_time_to'] = $end_date->format($end_date_format);
                    $end_date_day_name = $end_date->format('l');
                } else {
                    $start_date = ($i == 0) ? $inputted_start_date : $inputted_start_date->addDay();
                    $end_date = $start_date;
                    $data['date_time_from'] = $start_date->format('Y-m-d H:i');
                    $data['date_time_to'] = $inputted_start_date->format($end_date_format);
                    $end_date_day_name = $end_date->format('l');
                }

                if (($end_date_day_name == 'Saturday' && (int) $inputted_end_date->format('H') < 7) ||
                !in_array($end_date_day_name, ['Saturday', 'Sunday'])) {
                    $leave = $this->leaveService->createRequest($data);

                    $this->leaveService->addStatusLog([
                        'leave_request_id' => $leave->id,
                        'leave_status_id' => $data['leave_status_id'],
                        'reason' => ($authUser->id != $leave->user_id) ? 'Leave added by '.$authUser->employee->getFullNameAttribute() : null
                    ]);
                }
            }

            return response()->json(['message' => 'Leave Request successfully added!'])->setStatusCode(200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()])->setStatusCode(400);
        }
    }

    public function showEditLeaveRequestPage($id)
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            abort(404);
        }

        $leaveRequest->leaveType;

        return Inertia::render('Leaves/[id]', [
            'leaveTypes' => LeaveType::all(),
            'user' => Auth::user(),
            'leaveRequest' => $leaveRequest
        ]);
    }

    public function postUpdateLeave($id, Request $request)
    {
        try {
            $data = $request->all();
            $data['duration'] = ($data['is_full_shift']) ? 8 : $data['duration'];
            $leave_request = $this->leaveService->getLeaveRequestById($id);

            if (!$leave_request) {
                return response()->json(['message' => 'Leave request not found!'])->setStatusCode(404);
            }

            $validate = $this->leaveService->validateLeave($id, $data);

            if (!$validate->is_valid) {
                return response()->json(['message' => $validate->message])->setStatusCode(400);
            }

            $inputted_start_date = Carbon::parse($data['date_range'][0]);
            $inputted_end_date = Carbon::parse($data['date_range'][1]);
            $diff = $inputted_end_date->diff($inputted_start_date);

            unset($data['date_range']);
            unset($data['/leaves/'.$id]);

            $num_of_days = $diff->days + 1;

            for ($i = 0; $i < $num_of_days; $i++) {
                $end_date_format = "Y-m-d {$inputted_end_date->format('H:i:s')}";

                if ((int) $inputted_start_date->format('H') > (int) $inputted_end_date->format('H')) {
                    $start_date = $inputted_start_date;
                    $data['date_time_from'] = $start_date->format('Y-m-d H:i:s');
                    $end_date = $start_date->addDay();
                    $data['date_time_to'] = $end_date->format($end_date_format);
                    $end_date_day_name = $end_date->format('l');
                } else {
                    $start_date = ($i == 0) ? $inputted_start_date : $inputted_start_date->addDay();
                    $end_date = $start_date;
                    $data['date_time_from'] = $start_date->format('Y-m-d H:i:s');
                    $data['date_time_to'] = $inputted_start_date->format($end_date_format);
                    $end_date_day_name = $end_date->format('l');
                }

                if (($end_date_day_name == 'Saturday' && (int) $inputted_end_date->format('H') < 7) ||
                    !in_array($end_date_day_name, ['Saturday', 'Sunday'])) {

                    if ($i == 0) {
                        $this->leaveService->updateRequest($id, $data);
                        $leave_id = $id;
                        $reason = 'Leave updated!';
                    } else {
                        $leave = $this->leaveService->createRequest($data);
                        $leave_id = $leave->id;
                        $reason = null;
                    }

                    $this->leaveService->addStatusLog([
                        'leave_request_id' => $leave_id,
                        'leave_status_id' => LeaveStatus::PENDING,
                        'reason' => $reason
                    ]);
                }
            }


            return response()->json('Leave Request successfully updated!')->setStatusCode(200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()])->setStatusCode(400);
        }
    }
    
    public function postUpdateLeaveStatus(Request $request)
    {
        try {
            foreach ($request->input('id') as $leave_id) {
                $leave_request = $this->leaveService->getLeaveRequestById($leave_id);

                if (!$leave_request) {
                    return response()->json(['message' => 'Leave request not found!'])->setStatusCode(400);
                }

                $user = Auth::user();
                $send_notification = ($user->id !== $leave_request->user_id) ? true : false;
                $status_id = ($user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;

                if ($request->input('action') == 'approve') {
                    $status_id = ($user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::ADMIN_APPROVED : LeaveStatus::TL_APPROVED;
                    $data = [
                        'leave_status_id' => $status_id,
                        'approve_reason' => $request->input('reason')
                    ];
                }

                if ($request->input('action') == 'disapprove') {
                    $status_id = LeaveStatus::DISAPPROVED;
                    $data = [
                        'leave_status_id' => $status_id,
                        'disapprove_reason' => $request->input('reason')
                    ];
                }

                if ($request->input('action') == 'cancel') {
                    $status_id = LeaveStatus::CANCELLED;
                    $data = [
                        'leave_status_id' => $status_id,
                        'cancel_reason' => $request->input('reason')
                    ];
                }

                $this->leaveService->updateRequest($leave_id, $data);

                $this->leaveService->addStatusLog([
                    'leave_request_id' => $leave_id,
                    'leave_status_id' => $status_id,
                    'reason' => $request->input('reason')
                ]);
            }

            $message = '';

            if ($status_id == LeaveStatus::ADMIN_APPROVED || $status_id == LeaveStatus::TL_APPROVED) {
                $message = 'Leave Request successfully approved!';
            }

            if ($status_id == LeaveStatus::DISAPPROVED) {
                $message = 'Leave Request successfully disapproved!';
            }

            if ($status_id == LeaveStatus::CANCELLED) {
                $message = 'Leave Request successfully cancelled!';
            }

            return response()->json(['message' => $message])->setStatusCode(200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()])->setStatusCode(400);
        }
    }
}