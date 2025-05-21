<?php

namespace App\Models;

use App\Models\Transaction;
use App\Models\ExerciseTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exercise extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'tag',
        'short_description',
        'image'
    ];

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'exercise_id');
    }

    public function exerciseTransactions()
    {
        return $this->hasMany(ExerciseTransaction::class, 'exercise_id', 'id');
    }
}
