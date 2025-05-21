<?php

namespace App\Models;

use App\Models\StaffCart;
use App\Models\StaffOrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['item_code', 'name', 'type', 'short_description', 'quantity', 'price'];

    public function cart_items()
    {
        return $this->hasMany(StaffCart::class);
    }

    public function order_items()
    {
        return $this->hasMany(StaffOrderItem::class);
    }


}
