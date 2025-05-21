<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\ExerciseTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffCart extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'inventory_id', 'quantity', 'price'];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }

    public function exerciseTransactions()
    {
        return $this->hasMany(ExerciseTransaction::class, 'instructor_id');
    }
}
