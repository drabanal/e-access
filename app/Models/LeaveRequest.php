<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $timestamp = false;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'leave_status_id',
        'date_time_from',
        'date_time_to',
        'duration',
        'is_full_shift',
        'remove_break_hours',
        'remarks',
        'approve_reason',
        'disapprove_reason',
        'cancel_reason',
        'reference_id'
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
