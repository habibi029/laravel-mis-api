<?php

namespace App\Models;

use App\Models\Staff;
use App\Models\Client;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'exercise_id',
        'instructor_id',
        'isMainPlan',
        'expire_date',
        'price',
        'transaction_code',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(Staff::class, 'instructor_id');
    }
}
