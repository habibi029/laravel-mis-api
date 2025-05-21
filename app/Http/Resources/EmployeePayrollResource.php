<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'name' => $this->staff->firstname . ' ' . $this->staff->lastname,
            'salary_rate' => $this->salary_rate,
            'present_days' => $this->present_days,
            'absents' => $this->absents,
            'whole_days' => $this->whole_days,
            'half_days' => $this->half_days,
            'total_salary' => $this->total_salary,
            'whole_day_salary' => $this->whole_day_salary,
            'half_day_salary' => $this->half_day_salary,
            'overtime' => $this->over_time,
            'yearly_bonus' => $this->yearly_bonus,
            'sales_comission' => $this->sales_comission,
            'incentives' => $this->incentives,
            'sss' => $this->sss_deduction,
            'pag_ibig' => $this->pagibig_deduction,
            'philhealth' => $this->philhealth_deduction,
            'net_income' => $this->net_income,
            'total_deductions' => $this->total_deductions,
            'final_salary' => $this->final_salary,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'pay_date' => $this->pay_date
        ];
    }
}
