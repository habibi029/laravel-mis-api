<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Client;
use App\Models\Exercise;
use App\Models\Position;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\EmployeePayroll;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\StaffShowResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ClientShowResource;
use App\Http\Responses\ValidationResponse;
use App\Http\Resources\ExerciseShowResource;
use App\Http\Resources\PositionShowResource;
use App\Http\Resources\InventoryShowResource;
use App\Http\Resources\EmployeePayrollResource;
use App\Http\Resources\EmployeeAttendanceResource;

class AdminController extends Controller
{
    public function show_clients(Request $request)
    {
        $search = $request->query('search');
        $clients = Client::query();

        if ($search) {
            $clients->where(function ($query) use ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }
        return ClientShowResource::collection($clients->get());
    }

    public function markAbsent(Request $request) 
    {
        $request->validate([
            'staff_id' => 'required|integer|exists:staff,id',
        ]);

        $today = now()->setTimezone('Asia/Manila')->toDateString();

        $attendance = EmployeeAttendance::where('staff_id', $request->staff_id)
            ->where('date', $today)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($attendance) {
            if ($attendance->clock_in_time && !$attendance->clock_out_time) {
                return response()->json(['message' => 'Cannot mark as absent. Employee already has time in today.'], 400);
            }

            if ($attendance->clock_in_time && $attendance->clock_out_time) {
                return response()->json(['message' => 'Cannot mark as absent. Employee already has time in and time out today.'], 400);
            }

            if ($attendance->attendance === 'absent') {
                return response()->json(['message' => 'Employee is already marked as absent today.'], 400);
            }
        }


        $attendance = EmployeeAttendance::updateOrCreate(
            [
                'staff_id' => $request->staff_id,
                'date' => $today,
            ],
            [
                'fullname' => $request->fullname,
                'attendance_status' => 'absent',
            ]
        );

        return response()->json(['message' => 'Employee successfully marked as absent.'], 200);
    }


    public function markLeave(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|integer|exists:staff,id',
        ]);

        $today = now()->setTimezone('Asia/Manila')->toDateString();

        $attendance = EmployeeAttendance::where('staff_id', $request->staff_id)
            ->where('date', $today)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($attendance) {
            if ($attendance->clock_in_time && !$attendance->clock_out_time) {
                return response()->json(['message' => 'Cannot mark as leave. Employee already has time in today.'], 400);
            }

            if ($attendance->clock_in_time && $attendance->clock_out_time) {
                return response()->json(['message' => 'Cannot mark as leave. Employee already has time in and time out today.'], 400);
            }

            if ($attendance->attendance === 'leave') {
                return response()->json(['message' => 'Employee is already marked as leave today.'], 400);
            }
        }


        $attendance = EmployeeAttendance::updateOrCreate(
            [
                'staff_id' => $request->staff_id,
                'date' => $today,
            ],
            [
                'fullname' => $request->fullname,
                'attendance_status' => 'leave',
            ]
        );


        return response()->json(['message' => 'Employee successfully marked as leave.'], 200);
    }

    public function employee_clockIn(Request $request)
    {
    $request->validate([
        'staff_id' => 'required|integer|exists:staff,id',
        'fullname' => 'required|string|max:50',
        'date' => 'required|date',
        'clock_type' => 'required|in:clock_in_time,clock_out_time',
        'time' => 'required|date_format:H:i:s',
    ]);

    $today = now()->format('Y-m-d');
    if ($request->date !== $today) {
        return response()->json(['message' => 'You can only time in/out for today.'], 400);
    }

    $attendance = EmployeeAttendance::where('staff_id', $request->staff_id)
    ->where('date', $request->date)
    ->orderBy('created_at', 'desc')
    ->first();

    if (!$attendance) {
        $attendance = new EmployeeAttendance([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
        ]);
    }

    
    if (!$attendance->exists) {
        $attendance->fullname = $request->fullname;
        $attendance->attendance_status = 'Incomplete';
    }

    if ($request->clock_type === 'clock_in_time') {
        return $this->handleClockIn($attendance, $request->time, $request);
    }

    if ($request->clock_type === 'clock_out_time') {
        return $this->handleClockOut($attendance, $request->time);
    }

    return response()->json(['message' => 'Invalid attendance type.'], 400);
}


private function createOrUpdateAttendance($staffId, $status)
{
    $attendance = EmployeeAttendance::firstOrNew(
        [
            'staff_id' => $staffId,
            'date' => now()->toDateString(),
        ]
    );

    $attendance->attendance_status = $status;
    $attendance->save();

    return $attendance;
}

private function handleClockIn($attendance, $time, $request)
{
    if ($attendance->clock_in_time && !$attendance->clock_out_time) {
        return response()->json(['message' => 'Already time in.'], 400);
    } else if ($attendance->clock_in_time && $attendance->clock_out_time) {
        $today = Carbon::today()->toDateString();
        $attendances = EmployeeAttendance::where('staff_id', $attendance->staff_id)
        ->where('date', $today)
        ->get();

        foreach ($attendances as $att) {
            $att->attendance_status = 'Incomplete';
            $att->save();
        }
        
        $attendance = "";
        $attendance = new EmployeeAttendance([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
        ]);
        $attendance->fullname = $request->fullname;
        $attendance->attendance_status = 'Incomplete';
    }

    $attendance->clock_in_time = now()->toDateString() . ' ' . $time;
    $attendance->save();

    return response()->json([
        'message' => 'Time-in recorded.',
        'data' => $attendance
    ]);
}

private function handleClockOut($attendance, $time)
{
    if (!$attendance->clock_in_time || $attendance->clock_out_time) {
        return response()->json(['message' => 'Must time in first.'], 400);
    }

    $attendance->clock_out_time = now()->toDateString() . ' ' . $time;
    $clockIn = Carbon::parse($attendance->clock_in_time);
    $clockOut = Carbon::parse($attendance->clock_out_time);
    $hoursWorked = $clockIn->diffInMinutes($clockOut) / 60;

    $today = Carbon::today()->toDateString();

    $attendances = EmployeeAttendance::where('staff_id', $attendance->staff_id)
        ->where('date', $today)
        ->get();

    $totalWorked = $attendances->sum('total_hours');

    $totalWorked += $hoursWorked;

    foreach ($attendances as $att) {
        if ($totalWorked >= 8) {
            $att->attendance_status = 'Present';
        } elseif ($totalWorked >= 3) {
            $att->attendance_status = 'HalfDay';
        } else {
            $att->attendance_status = 'Undertime';
        }
        $att->save();
    }

    $attendance->total_hours = round($hoursWorked, 2);
    $attendance->save();

    return response()->json([
        'message' => 'Time-out recorded.',
        'data' => $attendance
    ]);
}
//endcode

    public function store_clients(Request $request)
    {
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

    public function edit_clients(Request $request, $id)
    {
        $client = Client::find($id);
        if (!$client) {
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client retrieved successfully'
        ], 200);
    }

    public function update_clients(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
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
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new ClientShowResource($client),
            'message' => 'Client updated successfully'
        ], 200);
    }

    public function soft_delete_clients(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully'], 200);
    }

    public function trashed_record_clients()
    {
        $trashed = Client::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No clients found'
            ], 404);
        }

        return response()->json([
            'data' => ClientShowResource::collection($trashed),
            'message' => 'Clients retrieved successfully'
        ]);
    }

    public function force_delete_clients(Request $request, $id)
    {
        $delete = Client::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Client was permanently deleted'
        ]);
    }

    public function restore_clients(Request $request, $id)
    {
        $restore = Client::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        $restore->restore();

        return response()->json([
            'message' => 'Client restored successfully',
            'data' => new ClientShowResource($restore)
        ]);
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

    public function store_staffs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'address' => 'required',
            'gender' => 'required',
            'contact_no' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $today = Carbon::now('Asia/Manila')->format('Y/m/d');

        $idsig = Carbon::now('Asia/Manila')->format('Ymd');

        $dailyCount = Staff::whereDate('created_at', Carbon::now('Asia/Manila')->toDateString())->count() + 1;
        $customId = $idsig . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

        $staff = Staff::create([
            'id' => (int)$customId,
            'position_id' => $request->position_id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'gender' => $request->gender,
            'joined_date' => $today,
            'contact_no' => $request->contact_no
        ]);


        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff created successfully'
        ], 201);
    }

    public function edit_staffs(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (!$staff) {
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff retrieved successfully'
        ], 200);
    }

    public function update_staffs(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'position_id' => 'required',
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

        $staff->update([
            'position_id' => $request->position_id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no
        ]);

        return response()->json([
            'data' => new StaffShowResource($staff),
            'message' => 'Staff updated successfully'
        ], 200);
    }

    public function soft_delete_staffs(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $staff->delete();

        return response()->json(['message' => 'Staff deleted successfully'], 200);
    }

    public function trashed_record_staffs()
    {
        $trashed = Staff::onlyTrashed()->get();

        return response()->json([
            'data' => StaffShowResource::collection($trashed),
            'message' => 'Trashed records retrieved successfully'
        ]);
    }

    public function force_delete_staffs(Request $request, $id)
    {
        $delete = Staff::onlyTrashed()->find($id);
        $delete->forceDelete();

        return response()->json([
            'message' => 'Staff was permanently deleted'
        ]);
    }

    public function restore_staffs(Request $request, $id)
    {
        $restore = Staff::onlyTrashed()->find($id);
        $restore->restore();

        return response()->json([
            'message' => 'Staff restored successfully',
            'data' => new StaffShowResource($restore)
        ]);
    }

    public function show_exercises()
    {
        $exercises = Exercise::all();
        return ExerciseShowResource::collection($exercises);
    }

    public function store_exercises(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'short_description' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $exercise = Exercise::create([
            'name' => $request->name,
            'price' => $request->price,
            'tag' => $request->tag,
            'short_description' => $request->short_description
        ]);

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise created successfully'
        ], 201);
    }

    public function edit_exercises(Request $request, $id)
    {
        $exercise = Exercise::find($id);
        if (!$exercise) {
            return response()->json([
                'message' => 'Exercise not found'
            ], 404);
        }

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise retrieved successfully'
        ], 200);
    }

    public function update_exercises(Request $request, $id)
    {
        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'message' => 'Exercise not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'short_description' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $exercise->update([
            'name' => $request->name,
            'price' => $request->price,
            'tag' => $request->tag,
            'short_description' => $request->short_description
        ]);

        return response()->json([
            'data' => new ExerciseShowResource($exercise),
            'message' => 'Exercise updated successfully'
        ], 200);
    }

    public function soft_delete_exercises(Request $request, $id)
    {
        $soft = Exercise::find($id);

        if (!$soft) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $soft->delete();

        return response()->json(['message' => 'Exercise deleted successfully'], 200);
    }

    public function trashed_record_exercise()
    {
        $trashed = Exercise::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Exercise found'
            ], 404);
        }

        return response()->json([
            'data' => ExerciseShowResource::collection($trashed),
            'message' => 'Exercises retrieved successfully'
        ]);
    }


    public function hard_delete_exercises(Request $request, $id)
    {
        $delete = Exercise::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Exercise was permanently deleted'
        ]);
    }

    public function restore_exercises(Request $request, $id)
    {
        $restore = Exercise::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Exercise not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Exercise restored successfully',
            'data' => new ExerciseShowResource($restore)
        ]);
    }

    public function show_positions()
    {
        $positions = Position::all();
        return PositionShowResource::collection($positions);
    }

    public function store_positions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $position = Position::create([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position created successfully'
        ], 201);
    }

    public function edit_positions(Request $request, $id)
    {
        $position = Position::find($id);
        if (!$position) {
            return response()->json([
                'message' => 'Position not found'
            ], 404);
        }

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position retrieved successfully'
        ], 200);
    }

    public function update_positions(Request $request, $id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                'message' => 'Position not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $position->update([
            'name' => $request->name
        ]);

        return response()->json([
            'data' => new PositionShowResource($position),
            'message' => 'Position updated successfully'
        ], 200);
    }

    public function soft_delete_positions(Request $request, $id)
    {
        $soft = Position::find($id);

        if (!$soft) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $soft->delete();

        return response()->json(['message' => 'Position deleted successfully'], 200);
    }

    public function trashed_record_positions()
    {
        $trashed = Position::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Position found'
            ], 404);
        }

        return response()->json([
            'data' => PositionShowResource::collection($trashed),
            'message' => 'Postions retrieved successfully'
        ]);
    }

    public function hard_delete_positions(Request $request, $id)
    {
        $delete = Position::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Position was permanently deleted'
        ]);
    }


    public function restore_positions(Request $request, $id)
    {
        $restore = Position::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Position not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Position restored successfully',
        ]);
    }

    public function show_inventories()
    {
        $inventories = Inventory::all();
        return InventoryShowResource::collection($inventories);
    }

    public function store_inventories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'short_description' => 'required',
            'quantity' => 'required',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $inventory = Inventory::create([
            'item_code' => uniqid(),
            'name' => $request->name,
            'type' => $request->type,
            'short_description' => $request->short_description,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory created successfully'
        ], 201);
    }

    public function edit_inventories(Request $request, $id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found'
            ], 404);
        }

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory retrieved successfully'
        ], 200);
    }

    public function update_inventories(Request $request, $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'message' => 'Inventory not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'short_description' => 'required',
            'quantity' => 'required',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $inventory->update([
            'name' => $request->name,
            'type' => $request->type,
            'short_description' => $request->short_description,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        return response()->json([
            'data' => new InventoryShowResource($inventory),
            'message' => 'Inventory updated successfully'
        ], 200);
    }

    public function soft_delete_inventories(Request $request, $id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $inventory->delete();

        return response()->json(['message' => 'Inventory deleted successfully'], 200);
    }

    public function trashed_record_inventories()
    {
        $trashed = Inventory::onlyTrashed()->get();

        if (!$trashed) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        return response()->json([
            'data' => InventoryShowResource::collection($trashed),
            'message' => 'Inventory retrieved successfully'
        ]);
    }

    public function hard_delete_inventories(Request $request, $id)
    {
        $delete = Inventory::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }
        $delete->forceDelete();

        return response()->json([
            'message' => 'Inventory permanently deleted successfully'
        ], 200);
    }

    public function restore_inventories(Request $request, $id)
    {
        $restore = Inventory::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Inventory restored successfully'
        ]);
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

    public function store_staff_attendances(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (!$staff) {
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }

        $today = Carbon::now()->format('Y-m-d');

        if ($request->date != $today) {
            return response()->json([
                'message' => 'Date is not today'
            ], 400);
        }

        $attendance = EmployeeAttendance::where('staff_id', $staff->id)
            ->where('date', $today)->first();

        if ($attendance) {
            return response()->json([
                'message' => 'Attendance already filled'
            ], 400);
        }


        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'date' => 'required',
            'attendance' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }


        $attendance = EmployeeAttendance::create([
            'staff_id' => $request->staff_id,
            'date' => $request->date,
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance created successfully'
        ], 201);
    }

    public function edit_staff_attendances(Request $request, $id)
    {
        $attendance = EmployeeAttendance::find($id);
        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance retrieved successfully'
        ], 200);
    }

    public function update_staff_attendances(Request $request, $id)
    {
        $attendance = EmployeeAttendance::find($id);

        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'attendance' => 'required'
        ]);

        if ($validator->fails()) {
            return new ValidationResponse($validator->errors());
        }

        $attendance->update([
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'data' => new EmployeeAttendanceResource($attendance),
            'message' => 'Attendance updated successfully'
        ], 200);
    }

    public function soft_delete_staff_attendances(Request $request, $id)
    {
        $soft = EmployeeAttendance::find($id);

        if (!$soft) {
            return response()->json([
                'message' => 'Attendance not found'
            ], 404);
        }

        $soft->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully'
        ]);
    }

    public function trashed_record_attendancess()
    {
        $trashed = EmployeeAttendance::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Attendance found'
            ], 404);
        }

        return response()->json([
            'data' => EmployeeAttendanceResource::collection($trashed),
            'message' => 'Attendances retrieved successfully'
        ]);
    }

    public function hard_delete_staff_attendances(Request $request, $id)
    {
        $delete = EmployeeAttendance::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Attendance permanently deleted successfully'
        ]);
    }

    public function restore_staff_attendances(Request $request, $id)
    {
        $restore = EmployeeAttendance::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Attendance restored successfully'
        ]);
    }

    public function show_staff_payrolls()
    {
        $payroll = EmployeePayroll::with('staff')->get();

        return response()->json([
            'data' => EmployeePayrollResource::collection($payroll),
            'message' => 'Payroll retrieved successfully',
        ], 200);
    }

    public function store_staff_payrolls(Request $request, $id)
    {
        $staff = Staff::find($id);
        if (!$staff) {
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }
    
        $validated = $request->validate([
            'week' => ['required', function($attribute, $value, $fail) {
                if (!preg_match('/^\d{4}-W\d{2}$/', $value)) {
                    return $fail('The week format is invalid. It should be in the format YYYY-Wxx.');
                }
                $date = Carbon::now('Asia/Manila')->setISODate(substr($value, 0, 4), substr($value, 6, 2));
                if ($date->isFuture()) {
                    return $fail('The selected week cannot be in the future.');
                }
            }],
            'pay_date' => 'required|date|after_or_equal:' . Carbon::now('Asia/Manila')->toDateString(),
            'sss_rate' => 'required|numeric',
            'philhealth_rate' => 'required|numeric',
            'pagibig_rate' => 'required|numeric',
        ]);
    
        $staff_id = $staff->id;
        $week = $request->week;
        $pay_date = $request->pay_date;
    
        $startOfWeek = Carbon::now('Asia/Manila')->setISODate(substr($week, 0, 4), substr($week, 6, 2))->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $attendances = EmployeeAttendance::where('staff_id', $staff_id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderByRaw("FIELD(attendance_status, 'present', 'halfday', 'absent')")
            ->get()
            ->groupBy('date')
            ->map(function ($items) {
                return $items->first();
            });

        $whole_days = $attendances->where('attendance_status', 'present')->count();
        $half_days = $attendances->where('attendance_status', 'halfday')->count();
        $absents    = $attendances->where('attendance_status', 'absent')->count();
    
        $present_days = $whole_days + $half_days;
    
        $rate_per_day = match ($staff->position_id) {
            2, 3 => 450,
            4 => 500,
            5 => 300,
            default => 300,
        };
    
        $whole_day_salary = $rate_per_day * $whole_days;
        $half_day_rate = $rate_per_day / 2;
        $half_day_salary = $half_day_rate * $half_days;
        $total_salary = $whole_day_salary + $half_day_salary;
    

        $overtime = $request->overtime ?? 0;
        $yearly_bonus = $request->yearly_bonus ?? 0;
        $sales_comission = $request->sales_comission ?? 0;
        $incentives = $request->incentives ?? 0;
    
        $net_income = $total_salary + $overtime + $yearly_bonus + $sales_comission + $incentives;
    
        $sss = $request->sss_rate / 100;
        $pagibig = $request->pagibig_rate / 100;
        $philhealth = $request->philhealth_rate / 100;
        if ($staff->position_id != 5) {
            $sss_deduction = $net_income * $sss;
            $pagibig_deduction = $net_income * $pagibig;
            $philhealth_deduction = $net_income * $philhealth;
        }
    
        $total_deductions = $staff->position_id === 5 ? 0 : ($sss + $pagibig + $philhealth);
        $final_salary = $net_income - $total_deductions;
    
        $payroll = new EmployeePayroll();
        $payroll->staff_id = $staff_id;
        $payroll->present_days = $present_days;
        $payroll->absents = $absents;
        $payroll->whole_days = $whole_days;
        $payroll->half_days = $half_days;
        $payroll->total_salary = $total_salary;
        $payroll->whole_day_salary = $rate_per_day;
        $payroll->half_day_salary = $rate_per_day / 2;
        $payroll->over_time = $overtime; 
        $payroll->yearly_bonus = $yearly_bonus;
        $payroll->sales_comission = $sales_comission;
        $payroll->incentives = $incentives;
        $payroll->sss_deduction = $sss_deduction; 
        $payroll->pagibig_deduction = $pagibig_deduction; 
        $payroll->philhealth_deduction = $philhealth_deduction;
        $payroll->sss_rate = $request->sss_rate;
        $payroll->philhealth_rate = $request->philhealth_rate;
        $payroll->pagibig_rate = $request->pagibig_rate;
        $payroll->total_deductions = $sss_deduction + $pagibig_deduction + $philhealth_deduction;
        $payroll->start_date = $startOfWeek;
        $payroll->end_date = $endOfWeek;
        $payroll->pay_date = $request->pay_date;
        $payroll->net_income = $net_income;
        $payroll->final_salary = $final_salary;
    
        $payroll->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Payroll data saved successfully.',
            'data' => new EmployeePayrollResource($payroll->load('staff')),
        ], 200);
    }
    

    public function soft_delete_staff_payrolls(Request $request, $id)
    {
        $soft = EmployeePayroll::find($id);

        if (!$soft) {
            return response()->json([
                'message' => 'Payroll not found'
            ], 404);
        }

        $soft->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully'
        ]);
    }

    public function trashed_record_payrolls()
    {
        $trashed = EmployeePayroll::onlyTrashed()->get();

        if ($trashed->isEmpty()) {
            return response()->json([
                'message' => 'No Payrolls found'
            ], 404);
        }

        return response()->json([
            'data' => EmployeePayrollResource::collection($trashed),
            'message' => 'Payrolls retrieved successfully'
        ]);
    }

    public function hard_delete_staff_payrolls(Request $request, $id)
    {
        $delete = EmployeePayroll::onlyTrashed()->find($id);

        if (!$delete) {
            return response()->json(['message' => 'Payrolls not found'], 404);
        }

        $delete->forceDelete();

        return response()->json([
            'message' => 'Payrolls permanently deleted successfully'
        ]);
    }

    public function restore_staff_payrolls(Request $request, $id)
    {
        $restore = EmployeePayroll::onlyTrashed()->find($id);

        if (!$restore) {
            return response()->json(['message' => 'Payroll not found'], 404);
        }

        $restore->restore();

        return response()->json([
            'message' => 'Payroll restored successfully'
        ]);
    }

    public function backup()
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $database = env('DB_DATABASE');
            $key = "Tables_in_$database";

            $backupData = '';

            foreach ($tables as $table) {
                $tableName = $table->$key;

                // Fetch table data
                $data = DB::table($tableName)->get();

                // Create a basic SQL insert for each table
                $backupData .= "DROP TABLE IF EXISTS `$tableName`;\n";
                $columns = Schema::getColumnListing($tableName); // Get column names

                // Generate table creation SQL
                $createTableSQL = "CREATE TABLE `$tableName` (\n";
                foreach ($columns as $column) {
                    $columnType = DB::getSchemaBuilder()->getColumnType($tableName, $column);
                    $createTableSQL .= "`$column` $columnType,\n";
                }
                $createTableSQL = rtrim($createTableSQL, ",\n") . "\n);";
                $backupData .= $createTableSQL . "\n\n";

                // Generate insert SQL for each row
                foreach ($data as $row) {
                    $insertSQL = "INSERT INTO `$tableName` (" . implode(', ', $columns) . ") VALUES (";

                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = "'" . addslashes($row->$column) . "'"; // Escape values
                    }

                    $insertSQL .= implode(', ', $values) . ");\n";
                    $backupData .= $insertSQL;
                }

                $backupData .= "\n\n";
            }

            // Define the backup file name
            $filename = 'database_backup_' . date('Y_m_d_H_i_s') . '.sql';

            // Store in local storage (storage/app)
            Storage::put($filename, $backupData);

            return response()->json([
                'status' => 'success',
                'message' => 'Database backup created successfully!',
                'file' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to backup database.',
                'error' => $e->getMessage(),
            ]);
        }
    }

}