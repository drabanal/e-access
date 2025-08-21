<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCreditsDetail extends Model
{
    protected $table = 'leavecredits_details';

    public $timestamps = false;

    protected $fillable = [
        'empid',
        'actualdateadded',
        'ldate',
        'vlday',
        'vlhours',
        'slday',
        'slhours',
        'lwopday',
        'lwophours',
        'mlday',
        'mlhours',
        'plday',
        'plhours',
        'blday',
        'blhours',
        'utday',
        'uthours',
        'fromtime',
        'totime',
        'remarks',
        'status',
        'cancel_reason',
        'disapprove_reason',
        'approve_reason',
        'tagvl'
    ];
}
