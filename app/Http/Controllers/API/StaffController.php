<?php

namespace App\Http\Controllers\API;

use App\Models\Staff;
use App\Models\Client;
use App\Models\Exercise;
use App\Models\Inventory;
use App\Models\StaffCart;
use App\Models\StaffOrder;
use Illuminate\Http\Request;
use App\Models\StaffOrderItem;
use App\Models\EmployeeAttendance;
use App\Models\SecurityQuesAndAns;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ClientShowResource;
use App\Http\Responses\ValidationResponse;
use App\Http\Resources\ExerciseShowResource;
use App\Http\Resources\InventoryShowResource;
use App\Http\Resources\EmployeeAttendanceResource;
use App\Http\Resources\StaffShowResource;

class StaffController extends Controller
{
    public function show_clients(){
        $clients = Client::all();
        return ClientShowResource::collection($clients);
    }

    public function show_staff_attendances($id = null)
    {
        if ($id) {
            $attendances = EmployeeAttendance::where('staff_id', $id)->get();
        } else {
            $attendances = EmployeeAttendance::all();
        }

        return EmployeeAttendanceResource::collection($attendances);
    }


    public function store_clients(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $client = Client::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client created successfully'
        ], 201);
    }

    public function show_staffs(Request $request)
    {
        $search = $request->query('search');
        $staffs = Staff::query();
        if ($search) {
            $staffs->where(function ($query) use ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}");
            });
        }
        return StaffShowResource::collection($staffs->get());
    }

    public function edit_clients(Request $request, $id){
        $client = Client::find($id);
        if(!$client){
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client retrieved successfully'
        ], 200);
    }

    public function update_clients(Request $request, $id){
        $client = Client::find($id);

        if(!$client){
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $client->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client updated successfully'
        ], 200);
    }

    public function show_all_clients(){
        $clients = Client::all();

        return response()->json([
            'data' => ClientShowResource::collection($clients),
            'message' => 'Clients retrieved successfully'
        ], 200);
    }

    public function show_all_staffs(){
        $staffs = Staff::where('position_id', 2)->get();

        return response()->json([
            'data' => $staffs,
            'message' => 'Staffs retrieved successfully'
        ], 200);
    }

    public function show_all_exercises(){
        $exercises = Exercise::all();

        return response()->json([
            'data' => $exercises,
            'message' => 'Exercises retrieved successfully'
        ], 200);
    }

    public function show_inventories(){
        $inventories = Inventory::all();

        return InventoryShowResource::collection($inventories);
    }

}
