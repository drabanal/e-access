<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';

    protected $timestamp = false;

    protected $fillable = [
        'name'
    ];

    const SICK_LEAVE = 1;
    const VACATION_LEAVE = 2;
    const LWOP = 3;
    const MATERNITY_LEAVE = 4;
    const PATERNITY_LEAVE = 5;
    const BEREAVEMENT_LEAVE = 6;
    const UNDERTIME = 7;

    public function leaveRequest()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
