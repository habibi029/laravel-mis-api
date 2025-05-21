<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class EmployeeAttendance extends Model
{
    use HasFactory;
    use SoftDeletes;
    
   // Make sure to include attendance_status in the fillable array
    protected $fillable = [
        'staff_id', 
        'fullname', 
        'date', 
        'total_hours', 
        'clock_in_time', 
        'clock_out_time',
        'attendance_status',
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    // Method to calculate attendance status based on work hours
    public function calculateAttendanceStatus()
    {
        $workHours = $this->calculateWorkHours();

        if ($workHours >= 8) {
            $this->attendance_status = 'Present';
        } elseif ($workHours >= 4) {
            $this->attendance_status = 'Halfday';
        } else {
            $this->attendance_status = 'Absent';
        }

        $this->save();
    }

    // Method to calculate the number of work hours
    public function calculateWorkHours()
    {
        $clockIn = $this->clock_in_time ? new Carbon($this->clock_in_time) : null;
        $clockOut = $this->clock_out_time ? new Carbon($this->clock_out_time) : null;

        if ($clockIn && $clockOut) {
            return $clockOut->diffInHours($clockIn);
        }

        return 0;
    }
}