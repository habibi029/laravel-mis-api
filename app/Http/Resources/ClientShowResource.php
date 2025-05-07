<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientShowResource extends JsonResource
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
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'email' => $this->email,
            'gender' => $this->gender,
            'address' => $this->address,
            'contact_no' => $this->contact_no
        ];
    }
}
