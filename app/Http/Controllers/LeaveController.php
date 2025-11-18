<?php

namespace App\Http\Controllers;

use App\Mail\LeaveRequestCreated;
use App\Mail\LeaveRequestCreatedInBehalf;
use App\Mail\LeaveRequestCreatedInBehalfConfirmation;
use App\Mail\LeaveRequestStatusUpdated;
use App\Models\LeaveCreditsDetail;
use App\Models\LeaveRequest;
use App\Models\LeaveStatus;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\LeaveRequestRepository;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class LeaveController extends Controller
{
    protected $leaveService;
    protected $leaveRepository;

    public function __construct()
    {
        $this->leaveService = new LeaveService();
        $this->leaveRepository = new LeaveRequestRepository();
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
        $leaveUser = $request->input('user_id') ? User::find($request->input('user_id')) : Auth::user();
        if (!$leaveUser) {
            abort(404);
        }

        $leaveUser->employee;
        $user = Auth::user();
        $user->employee;
        return Inertia::render('Leaves/Add', [
            'leaveTypes' => LeaveType::all(),
            'leaveUser' => $leaveUser,
            'user' => $user,
            'inBehalf' => $request->input('user_id') ? true : false
        ]);
    }

    public function postRequestLeave(Request $request)
    {
        try {
            $data = $request->all();
            $auth_user = Auth::user();

            if ($data['user_id'] != $auth_user->id) {
                $leave_user = $this->leaveService->getUserById($data['user_id']);
                $data['leave_status_id'] = ($leave_user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;
                $role = ($auth_user->userlevel == User::ADMIN_ROLE) ? 'ADMIN' : 'TL';
                $data['remarks'] = "[{$role} OVERRIDE]: ".$data['remarks'];
                $created_in_behalf = true;
            } else {
                $data['leave_status_id'] = ($auth_user->userlevel == User::ADMIN_ROLE) ? LeaveStatus::TL_APPROVED : LeaveStatus::PENDING;
                $leave_user = Auth::user();
                $created_in_behalf = false;
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
                        'reason' => ($auth_user->id != $leave->user_id) ? 'Leave added by '.$auth_user->employee->getFullNameAttribute() : null
                    ]);

                    if (!$created_in_behalf) {
                        Mail::to($leave_user->employee->supervisor()->user)->send(new LeaveRequestCreated($leave));
                    } else {
                        Mail::to($leave_user)->send(new LeaveRequestCreatedInBehalf($leave, $auth_user));
                        Mail::to($leave_user->employee->supervisor()->user)->send(new LeaveRequestCreatedInBehalfConfirmation($leave));
                    }

                    $this->leaveService->createUpdateReferenceData($leave);

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
        $leaveUser = $leaveRequest->user->employee;
        $user = Auth::user()->employee;
        return Inertia::render('Leaves/[id]', [
            'leaveTypes' => LeaveType::all(),
            'user' => $user,
            'leaveRequest' => $leaveRequest,
            'inBehalf' => ($leaveRequest->user_id != Auth::user()->id) ? true : false,
            'leaveUser' => $leaveUser,
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
                        $leave = $this->leaveService->getLeaveRequestById($id);
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

                    $this->leaveService->createUpdateReferenceData($leave);
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

                $leave_request = $this->leaveRepository->find($leave_id);

                $this->leaveService->createUpdateReferenceData($leave_request);

                if ($status_id == LeaveStatus::ADMIN_APPROVED) {
                    $supervisor = $leave_request->user->employee->supervisor();
                    $cc_recipients = explode(',', env('NOTIFICATION_CC_RECIPIENTS'));

                    if ($supervisor && strtolower($supervisor->empemail) !== strtolower($leave_request->user->email)) {
                        array_push($cc_recipients, strtolower($supervisor->empemail));
                    }

                    Mail::to($leave_request->user)->cc($cc_recipients)->send(new LeaveRequestStatusUpdated($leave_request));
                } else {
                    Mail::to($leave_request->user)->send(new LeaveRequestStatusUpdated($leave_request));
                }
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

    public function getLeaveRequestDetail($id)
    {
        try {
            $leave_request = $this->leaveService->getLeaveRequestById($id);

            if (!$leave_request) {
                return response()->json('Leave request not found!')->setStatusCode(404);
            }

            $additionalRemarks = [];
            $statusLogs = $leave_request->leaveStatusLogs()->where('leave_status_id', '!=', LeaveStatus::PENDING)->get();

            foreach ($statusLogs as $log) {
                array_push($additionalRemarks, [
                    'id' => $log->id,
                    'status' => $log->leaveStatus->name,
                    'changed_by' => ($log->user) ? $log->user->employee->getFullNameAttribute() : 'System',
                    'reason' => $log->reason,
                    'date_changed' => Carbon::parse($log->created_at)->format('M d, Y h:i A')
                ]);
            }

            $leave_request->additional_remarks = $additionalRemarks;

            return response()->json($leave_request)->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage())->setStatusCode(400);
        }
    }
}