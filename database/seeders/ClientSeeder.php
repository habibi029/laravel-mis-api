<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::create([
            "email" => "client@company.com",
            "firstname" => "Maria",
            "lastname" => "Doe",
            "address" => "Metro Manila",
            "gender" => "female",
            "contact_no" => "1234567890",
            "is_active" => 1
        ]);
    }
}
