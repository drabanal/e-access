<?php

namespace Database\Seeders;

use App\Models\LeaveCreditsDetail;
use App\Models\LeaveRequest;
use App\Models\LeaveStatus;
use App\Models\LeaveStatusLog;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('leave_status_logs')->truncate();
        DB::table('leave_requests')->truncate();
        $leave_credits_details = LeaveCreditsDetail::all();

        foreach ($leave_credits_details as $leave_detail) {
            $user = User::where('userid', $leave_detail->empid)->first();
            $leave_status = LeaveStatus::find(($leave_detail->status + 1));
            $leave_type = LeaveType::find(($leave_detail->tagvl + 1));
            $duration = $this->getDuration($leave_detail);
            $status_log_notes = $this->getStatusLogNotes($leave_detail);

            if ($user) {
                $leave_request_data = [
                    'user_id' => $user->id,
                    'leave_type_id' => $leave_type->id,
                    'leave_status_id' => $leave_status->id,
                    'date_time_from' => $leave_detail->fromtime,
                    'date_time_to' => $leave_detail->totime,
                    'duration' => $duration,
                    'is_full_shift' => ($duration == 8) ? true : false,
                    'remove_break_hours' => ($duration == 8) ? true : false,
                    'remarks' => $leave_detail->remarks,
                    'approve_reason' => $leave_detail->approve_reason,
                    'disapprove_reason' => $leave_detail->disapprove_reason,
                    'cancel_reason' => $leave_detail->cancel_reason,
                    'reference_id' => $leave_detail->id,
                    'created_at' => $leave_detail->actualdateadded
                ];

                $leave_request = LeaveRequest::create($leave_request_data);

                $status_log_data = [
                    'leave_request_id' => $leave_request->id,
                    'leave_status_id' => $leave_status->id,
                    'reason' => $status_log_notes
                ];

                LeaveStatusLog::create($status_log_data);
            }

        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function getDuration($leave_detail)
    {
        $duration = 0;
        if ($leave_detail->tagvl == 0) {
            $duration = ($leave_detail->slday == 1) ? 8 : $leave_detail->slhours;
        }
        if ($leave_detail->tagvl == 1) {
            $duration = ($leave_detail->vlday == 1) ? 8 : $leave_detail->vlhours;
        }
        if ($leave_detail->tagvl == 2) {
            $duration = ($leave_detail->lwopday == 1) ? 8 : $leave_detail->lwophours;
        }
        if ($leave_detail->tagvl == 3) {
            $duration = ($leave_detail->mlday == 1) ? 8 : $leave_detail->mlhours;
        }
        if ($leave_detail->tagvl == 4) {
            $duration = ($leave_detail->plday == 1) ? 8 : $leave_detail->plhours;
        }
        if ($leave_detail->tagvl == 5) {
            $duration = ($leave_detail->blday == 1) ? 8 : $leave_detail->blhours;
        }
        if ($leave_detail->tagvl == 6) {
            $duration = ($leave_detail->utday == 1) ? 8 : $leave_detail->uthours;
        }

        return $duration;
    }

    public function getStatusLogNotes($leave_detail)
    {
        $notes = null;
        if ($leave_detail->status == 1 || $leave_detail->status == 2) {
            $notes = $leave_detail->approve_reason;
        }
        if ($leave_detail->status == 3) {
            $notes = $leave_detail->disapprove_reason;
        }
        if ($leave_detail->status == 4) {
            $notes = $leave_detail->cancelled_reason;
        }

        return $notes;
    }
}
