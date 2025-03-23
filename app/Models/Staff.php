<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    protected $table = 'staffs';
    protected $primaryKey = 'staff_id';
    protected $fillable = ['role_id', 'username', 'email', 'password_hash', 'is_locked'];
    // Hide the password hash from the response
    protected $hidden = ['password_hash'];

    
    // Override the default password field
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'staff_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'staff_id');
    }
}