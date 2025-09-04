<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveStatusLog extends Model
{
    protected $table = 'leave_status_logs';

    protected $timestamp = false;

    protected $fillable = [
        'leave_request_id',
        'leave_status_id',
        'user_id',
        'reason'
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function leaveStatus()
    {
        return $this->belongsTo(LeaveStatus::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
