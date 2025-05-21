<?php

namespace App\Models;

use App\Models\SecurityQuesAndAns;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $fillable = ['email', 'password', 'name', 'is_active'];
    protected $hidden = ['password'];

    public function securityQuestionAnswers()
    {
        return $this->hasOne(SecurityQuesAndAns::class);
    }
}
