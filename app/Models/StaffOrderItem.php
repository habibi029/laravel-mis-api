<?php

namespace App\Models;

use App\Models\Inventory;
use App\Models\StaffOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffOrderItem extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = ['order_id', 'inventory_id', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(StaffOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Inventory::class,'inventory_id', 'id');
    }
}
