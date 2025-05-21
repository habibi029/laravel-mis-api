<?php

namespace App\Http\Controllers\API;

use App\Models\Staff;
use App\Models\Client;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffDashboardController extends Controller
{
    public function index(){
        $staff = Staff::where('position_id', 2)->get();

        $products = Inventory::where('type', 'supplement')->count();

        $equipments = Inventory::where('type', 'equipment')->count();

        $clients = Client::count();

        return response()->json([
            'instructor_list:' => $staff,
            'total_products' => $products,
            'total_equipmen' => $equipments,
            'total_customers' => $clients
        ]);
    }
}
