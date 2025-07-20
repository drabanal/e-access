<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Repositories\LeaveCreditRepository;
use App\Repositories\LeaveRequestRepository;
use App\Repositories\LeaveStatusLogRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveService
{
    protected $leaveRequestRepository;
    protected $leaveCreditRepository;
    protected $leaveTypeRepository;
    protected $leaveStatusLogRepository;
    protected $userRepository;

    public function __construct()
    {
        $this->leaveRequestRepository = new LeaveRequestRepository();
        $this->leaveCreditRepository = new LeaveCreditRepository();
        $this->leaveTypeRepository = new LeaveTypeRepository();
        $this->leaveStatusLogRepository = new LeaveStatusLogRepository();
        $this->userRepository = new UserRepository();
    }

    public function getLeaveCredits($user)
    {
        $employee = $user->employee;
        $leave_credits = $employee->leaveCredits;
        $leave_consumption = $this->leaveTypeRepository->getLeaveUsageForCurrentYear($user->id);

        foreach ($leave_consumption as $detail) {
            if ($detail->id == 1) {
                $used = (!is_null($detail->used) && $detail->used > 0) ? $detail->used : 0;
                $availableHrs = ($leave_credits->totalsldays * 8) + $leave_credits->totalslhours;
                $available = $leave_credits->totalsldays;
                $remaining = $availableHrs - $used;
            } elseif ($detail->id == 2) {
                $used = (!is_null($detail->used) && $detail->used > 0) ? $detail->used : 0;
                $availableHrs = ($leave_credits->totalvldays * 8) + $leave_credits->totalvlhours;
                $available = $leave_credits->totalvldays;
                $remaining = $availableHrs - $used;
            } else {
                $used = (!is_null($detail->used) && $detail->used > 0) ? $detail->used : 0;
                $availableHrs = 0;
            }

            $usedDays = floor($used / 8) ? floor($used / 8).'d' : '';
            $usedHours = floor($used % 8) ? floor($used % 8).'h' : '';

            $remainingDays = floor($remaining / 8) ? floor($remaining / 8).'d' : '';
            $remainingHours = floor($remaining % 8) ? floor($remaining % 8).'h' : '';
            $detail->available = $available . 'd';
            $detail->remaining = !empty($remainingDays) || !empty($remainingHours) ? "{$remainingDays} {$remainingHours}" : '0h';
            $detail->used = !empty($usedDays) || !empty($usedHours) ? "{$usedDays} {$usedHours}" : '0h';
        }


        return $leave_consumption;
    }

    public function getRequests($user_id, $leave_type_id, $leave_status_id, $date_range)
    {
        return $this->leaveRequestRepository->getRequests($user_id, $leave_type_id, $leave_status_id, $date_range);
    }

    public function createRequest($data)
    {
        return $this->leaveRequestRepository->create($data);
    }

    public function updateRequest($id, $data)
    {
        return $this->leaveRequestRepository->update($id, $data);
    }

    public function addStatusLog($data)
    {
        return $this->leaveStatusLogRepository->create($data);
    }

    public function checkForConflict($id, $data)
    {
        return $this->leaveRequestRepository->checkForConflict($id, $data);
    }

    public function getRemainingLeaveCreditsByType($leave_type_id, $user_id)
    {
        $employee_id = $this->userRepository->find($user_id)->userid;
        $leave_credits = $this->leaveCreditRepository->findBy(['empid' => $employee_id])->first();
        $total_leave_hours = $this->leaveRequestRepository->getApprovedLeavesByLeaveType($leave_type_id, $user_id);
        
        return ($leave_type_id == 1) ? ($leave_credits->totalsldays * 8) - $total_leave_hours : (($leave_credits->totalvldays * 8) + 40) - $total_leave_hours;
    }

    public function getLeaveRequestById($id)
    {
        return $this->leaveRequestRepository->find($id);
    }

    public function getUserById($user_id)
    {
        return $this->userRepository->find($user_id);
    }

    public function getMembersPendingRequest($user_id, $role, $leave_type_id, $leave_status_id, $date_range)
    {
        return $this->leaveRequestRepository->getMembersPendingRequest($user_id, $role, $leave_type_id, $leave_status_id, $date_range);
    }

    public function validateLeave($id, $data)
    {
        if (empty($data['duration']) || $data['duration'] == 0 || !is_numeric($data['duration'])) {
            return (object) [
                'is_valid' => false,
                'message' => "Invalid duration value!"
            ];
        }

        if ($data['leave_type_id'] == LeaveType::SICK_LEAVE && is_float($data['duration'])) {
            return (object) [
                'is_valid' => false,
                'message' => "Sick Leave hours should only be whole hours. Extra minutes can be added via LWOP request."
            ];
        }

        if ($data['leave_type_id'] == LeaveType::VACATION_LEAVE && (!in_array($data['duration'], [4,8]))) {
            return (object) [
                'is_valid' => false,
                'message' => "Vacation Leave hours should only be 4hrs or 8hrs."
            ];
        }

        if ($data['leave_type_id'] == LeaveType::VACATION_LEAVE && !$data['sl_charged_to_vl']) {
            $leave_date = Carbon::parse($data['date_range'][0])->startOfDay();
            $current_date = Carbon::now()->startOfDay();

            $diff = $this->getDateDifferenceExcludingWeekends($current_date, $leave_date);

            if ($diff <= 5) {
                return (object) [
                    'is_valid' => false,
                    'message' => "Vacation Leave should be filed atleast 5 business days ahead."
                ];
            }
        }

        $conflict = $this->checkForConflict($id, $data);

        if ($conflict) {
            $leave_type = $conflict->leaveType->name;
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $conflict->date_time_from)->toFormattedDateString();
            return (object) [
                'is_valid' => false,
                'message' => "You already filed a {$leave_type} on {$date}"
            ];
        }

        if (in_array($data['leave_type_id'], [LeaveType::SICK_LEAVE, LeaveType::VACATION_LEAVE])) {
            $remaining_leaves = $this->getRemainingLeaveCreditsByType($data['leave_type_id'], $data['user_id']);

            if ($remaining_leaves == 0) {
                return (object) [
                    'is_valid' => false,
                    'message' => "You don't have enough leave credits left!"
                ];
            }
        }

        if (!is_null($id)) {
            $inputted_start_date = Carbon::parse($data['date_range'][0]);
            $inputted_end_date = Carbon::parse($data['date_range'][1]);
            $diff = $inputted_end_date->diff($inputted_start_date)->days;

            if ($diff > 0) {
                return (object) [
                    'is_valid' => false,
                    'message' => "You can't update your leave into multiple days."
                ];
            }
        }

        return (object) ['is_valid' => true];
    }

    public function getDateDifferenceExcludingWeekends($dateFrom, $dateTo)
    {
        $period = CarbonPeriod::create($dateFrom, $dateTo);

        $valid_dates = [];
        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                array_push($valid_dates, $date);
            }
        }

        return count($valid_dates);
    }

    public function getAllApprovedRequests()
    {
        return $this->leaveRequestRepository->getAllApprovedRequests();
    }
}