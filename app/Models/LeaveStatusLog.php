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
        'reason'
    ];
}
