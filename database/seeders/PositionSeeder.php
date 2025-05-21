<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Position::Create([
            'name' => 'Admin'
        ]);

        Position::Create([
            'name' => 'Instructor'
        ]);

        Position::Create([
            'name' => 'Cashier'
        ]);

        Position::Create([
            'name' => 'Manager'
        ]);

        Position::Create([
            'name' => 'Utility'
        ]);
    }
}
