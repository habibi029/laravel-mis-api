<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityQuesAndAns;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SecurityAnswer extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SecurityQuesAndAns::create([
            "admin_id" => 1,
            "answer_1" => "b7d6e89c3f4a59d1e872c5f32b1a4c7e",
            "answer_2" => "9a4bf2837c6e1d04e91f75c28d3b5a60",
            "answer_3" => "d65f1b2e7a4c39e8f04a7f283c91d5b7",
        ]);

    }
}
