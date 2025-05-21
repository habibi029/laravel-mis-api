<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class EmployeeAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        Log::info('EmployeeAttendanceResource full data:', $this->resource->toArray());

        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'fullname' => $this->fullname,
            // 'gender' => $this->staff->gender,
            // 'contact_no' => $this->staff->contact_no,
            // 'email' => $this->staff->email,
            'attendance_status' => $this->attendance_status,
            'workhour' => $this->total_hours,
            'date' => $this->date,
            'clock_in_time' => $this->clock_in_time ? $this->clock_in_time->format('H:i:s') : null,
            'clock_out_time' => $this->clock_out_time ? $this->clock_out_time->format('H:i:s') : null,
        ];
    }
}
