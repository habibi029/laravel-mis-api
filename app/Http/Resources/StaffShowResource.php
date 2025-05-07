<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffShowResource extends JsonResource
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
            'position' => $this->position->name,
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'email' => $this->email,
            'gender' => $this->gender,
            'address' => $this->address,
            'joined_date' => $this->joined_date,
            'contact_no' => $this->contact_no
        ];
    }
}
