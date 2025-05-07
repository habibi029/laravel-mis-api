<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\StaffOrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffOrder extends Model
{

    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['transaction_code', 'total_amount', 'status'];

    public function items()
    {
        return $this->hasMany(StaffOrderItem::class, 'order_id')->withTrashed();
    }

    protected static function booted()
    {
        static::deleting(function ($order) {
            $order->items()->delete(); // Soft delete related items
        });

        static::restoring(function ($order) {
            $order->items()->restore(); // Restore related items
        });

        static::deleting(function ($order) {
            if ($order->isForceDeleting()) {
                $order->items()->forceDelete(); // Permanently delete related items
            }
        });
    }

}
