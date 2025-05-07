<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecurityQuesAndAns extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'answer_1', 'answer_2', 'answer_3'];


    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
