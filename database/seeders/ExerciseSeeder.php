<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Exercise::create([
            "name" => "Gym per Session",
            "price" => 60.00,
            "tag" => "session",
            'short_description' => 'Gym Workout per Session'
        ]);

        Exercise::create([
            "name" => "Gym per Month",
            "price" => 750.00,
            "tag" => "monthly",
            'short_description' => 'Gym Workout per Month'
        ]);

        Exercise::create([
            "name" => "Monthly Treadmill",
            "price" => 550.00,
            "tag" => "monthly",
            'short_description' => 'Treadmill per Month'
        ]);

        Exercise::create([
            "name" => "Gym + Treadmill",
            "price" => 1200.00,
            "tag" => "monthly",
            'short_description' => 'Gym + Treadmill per Month'
        ]);

        Exercise::create([
            "name" => "P.I per Session",
            "price" => 120.00,
            "tag" => "session",
            'short_description' => 'Gym Daily + Personal Instructor per Session'
        ]);

        Exercise::create([
            "name" => "P.I per Month",
            "price" => 1500.00,
            "tag" => "monthly",
            'short_description' => 'Gym Daily + Personal Instructor per Month'
        ]);

        Exercise::create([
            "name" => "Zumba",
            "price" => 70.00,
            "tag" => "session",
            'short_description' => 'Zumba per Session'
        ]);

        Exercise::create([
            "name" => "Dance Studio for Regular",
            "price" => 30.00,
            "tag" => "session",
            'short_description' => 'Dance Studio for Regular'
        ]);

        Exercise::create([
            "name" => "Dance Studio for Student",
            "price" => 15.00,
            "tag" => "session",
            'short_description' => 'Dance Studio for Student'
        ]);

        Exercise::create([
            "name" => "Muay Thai",
            "price" => 250.00,
            "tag" => "session",
            'short_description' => 'Muay Thai per Session'
        ]);

        Exercise::create([
            "name" => "Taekwando per Session",
            "price" => 400.00,
            "tag" => "session",
            'short_description' => 'Taekwando per Session'
        ]);

        Exercise::create([
            "name" => "Taekwando per Month",
            "price" => 3000.00,
            "tag" => "session",
            'short_description' => 'Taekwando per Month'
        ]);

        Exercise::create([
            "name" => "Boxing",
            "price" => 250.00,
            "tag" => "session",
            'short_description' => 'Boxing per Session'
        ]);
    }
}
