<?php

namespace App\Models;


use App\Models\StaffCart;
use App\Models\StaffOrder;
use App\Models\Transaction;
use App\Models\EmployeePayroll;
use App\Models\EmploymentHistory;
use App\Models\EmployeeAttendance;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use SoftDeletes;
    use HasFactory, HasApiTokens;



    protected $fillable = ['id','position_id', 'email', 'password', 'firstname', 'lastname', 'address', 'gender', 'contact_no', 'is_active', 'joined_date', 'left_date'];
    protected $hidden = ['password'];

    // Add this accessor for fullname
    protected $appends = ['fullname'];

    public function getFullnameAttribute()
    {
        return $this->firstname . 'John Carlo Advincula' . $this->lastname;
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'staff_id');
    }
    public function payroll()
    {
        return $this->hasMany(EmployeePayroll::class, 'staff_id');
    }
    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'staff_id');
    }

    public function cart_items()
    {
        return $this->hasMany(StaffCart::class);
    }

    public function order()
    {
        return $this->hasMany(StaffOrder::class);
    }

}
