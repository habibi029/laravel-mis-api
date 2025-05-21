<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeAttendance;
use App\Models\Staff;
use Carbon\Carbon;

class MarkIncompleteAttendance extends Command
{
    protected $signature = 'attendance:mark-incomplete';
    protected $description = 'Mark incomplete past attendance and insert absent for today if no record exists';

    public function handle()
    {
        $today = Carbon::today('Asia/Manila');

        $attendances = EmployeeAttendance::where('date', '<', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->attendance_status = 'Absent';
            $attendance->save();
        }

        $this->info("Updated {$attendances->count()} past record(s) to 'Absent'.");

        $staffList = Staff::all();
        $absentCount = 0;

        foreach ($staffList as $staff) {
            $hasTodayRecord = EmployeeAttendance::where('staff_id', $staff->id)
                ->whereDate('date', $today)
                ->exists();

            if (!$hasTodayRecord) {
                EmployeeAttendance::create([
                    'staff_id' => $staff->id,
                    'fullname' => $staff->firstname . " " . $staff->lastname,
                    'date' => $today,
                    'attendance_status' => 'Absent',
                ]);
                $absentCount++;
            }
        }

        $this->info("Inserted {$absentCount} 'Absent' record(s) for today.");
    }
}
