<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Dumbbell",
            "type" => "equipment",
            "short_description" => "Dumbbell",
            "quantity" => 10,
            "price" => 100.00
        ]);

        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Whey Protein",
            "type" => "supplement",
            "short_description" => "Whey Protein",
            "quantity" => 10,
            "price" => 100.00
        ]);

        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Barbell",
            "type" => "equipment",
            "short_description" => "Barbell",
            "quantity" => 10,
            "price" => 100.00
        ]);

        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Mass Gainer",
            "type" => "supplement",
            "short_description" => "Mass Gainer",
            "quantity" => 10,
            "price" => 100.00
        ]);

        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Resistance Bands",
            "type" => "equipment",
            "short_description" => "Resistance Bands",
            "quantity" => 10,
            "price" => 100.00
        ]);

        Inventory::create([
            "item_code" => uniqid(),
            "name" => "Creatinine",
            "type" => "supplement",
            "short_description" => "Creatinine",
            "quantity" => 10,
            "price" => 100.00
        ]);


    }
}
