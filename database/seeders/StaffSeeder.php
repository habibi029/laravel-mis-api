<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Staff::create([
            "position_id" => 2,
            "email" => "staff@company.com",
            "password" => bcrypt("password"),
            "firstname" => "John Carlo",
            "lastname" => "Advincula",
            "address" => "Paterno B St. Cavite City",
            "gender" => "male",
            "contact_no" => "09363613990",
            "joined_date" => Carbon::now()->format('Y/m/d'),
            "is_active" => 1
        ]);
    }
}
