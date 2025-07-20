<?php

namespace Database\Seeders;

use App\Models\LeaveCredit;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveCreditsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('leave_credits')->truncate();
        $leavecredits = DB::table('leavecredits')->select('*')->get();

        foreach ($leavecredits as $leave_credit) {

            $user = User::where('userid', $leave_credit->empid)->first();

            if ($user) {

                LeaveCredit::create([
                    'user_id' => $user->id,
                    'leave_type_id' => LeaveType::VACATION_LEAVE,
                    'value' => $leave_credit->totalvldays
                ]);

                LeaveCredit::create([
                    'user_id' => $user->id,
                    'leave_type_id' => LeaveType::SICK_LEAVE,
                    'value' => $leave_credit->totalsldays
                ]);
            }
        }
    }
}
