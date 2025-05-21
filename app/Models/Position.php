<?php

namespace App\Models;

use App\Models\EmploymentHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name'];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'position_id');
    }
    public function history()
    {
        return $this->hasMany(EmploymentHistory::class, 'position_id');
    }
}
