<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveType::create([
            'name' => 'Sick Leave'
        ]);
        LeaveType::create([
            'name' => 'Vacation Leave'
        ]);
        LeaveType::create([
            'name' => 'LWOP'
        ]);
        LeaveType::create([
            'name' => 'Maternity Leave'
        ]);
        LeaveType::create([
            'name' => 'Paternity Leave'
        ]);
        LeaveType::create([
            'name' => 'Bereavement Leave'
        ]);
        LeaveType::create([
            'name' => 'Undertime'
        ]);
    }
}
