<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryShowResource extends JsonResource
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
            'item_code' => $this->item_code,
            'name' => $this->name,
            'type' => $this->type,
            'short_description' => $this->short_description,
            'quantity' => $this->quantity,
            'price' => $this->price
        ];
    }
}
