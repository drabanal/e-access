<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveStatus extends Model
{
    protected $table = 'leave_statuses';

    protected $primaryKey = 'id';

    protected $timestamp = false;

    const PENDING = 1;
    const TL_APPROVED = 2;
    const ADMIN_APPROVED = 3;
    const DISAPPROVED = 4;
    const CANCELLED = 5;

    protected $fillable = [
        'name'
    ];
}
