<?php

namespace Database\Seeders;

use App\Models\LeaveStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveStatus::create([
            'name' => 'Pending'
        ]);
        LeaveStatus::create([
            'name' => 'TL Approved'
        ]);
        LeaveStatus::create([
            'name' => 'Admin Approved'
        ]);
        LeaveStatus::create([
            'name' => 'Disapproved'
        ]);
        LeaveStatus::create([
            'name' => 'Cancelled'
        ]);
    }
}
