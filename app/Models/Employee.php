<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $primaryKey = 'empid';

    public $timestamps = false;

    protected function casts(): array
{
    return [
        'empid' => 'string'
    ];
}

    const EMPLOYEE_STATUS_REGULAR = 'REGULAR';
    const EMPLOYEE_STATUS_RESIGNED = 'RESIGNED';
    const EMPLOYEE_STATUS_PROBATIONARY = 'PROBATIONARY';

    public function user()
    {
        return $this->hasOne(User::class, 'userid', 'empid');
    }

    public function getFullNameAttribute()
    {
        return $this->empgname . ' ' . $this->empfname;
    }

    public function leaveCredits()
    {
        return $this->hasOne(LeaveCredit::class, 'empid');
    }

    public function supervisor()
    {
        if ($this->posid_man) {
            return Employee::where('empid', $this->posid_man)->first();
        }

        return null;
    }
}
