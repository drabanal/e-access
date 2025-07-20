<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $attributes = [
        'employee_basic_info'
    ];

    const ADMIN_ROLE = 1;
    const TL_ROLE = 2;
    const MEMBER_ROLE = 3;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'userid', 'empid');
    }

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    public function getEmployeeBasicInfoAttribute()
    {
        $employee = $this->employee;

        return (object) [
            'firstname' => $employee->empgname,
            'lastname' => $employee->lastname,
            'gender' => $employee->empgender
        ];
    }
}
