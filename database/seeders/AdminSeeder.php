<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            "email" => "admin@company.com",
            "password" => bcrypt("password"),
            "Name" => "Admin",
            "is_super_admin" => 1,
            "is_active" => 1
        ]);
    }
}
