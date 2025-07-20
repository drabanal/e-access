<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->employee) {
                $user->email = strtolower($user->employee->empemail);
                $user->password = bcrypt($user->userid);
                $user->save();
            }
        }
    }
}
