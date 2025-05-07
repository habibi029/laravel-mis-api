<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_type' => 'client',
            'id' => $this->id,
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'email' => $this->email
        ];
    }
}
