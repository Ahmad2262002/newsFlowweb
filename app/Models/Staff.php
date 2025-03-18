<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'staffs'; // Explicitly define table name
    protected $primaryKey = 'staff_id'; // Add if using non-default primary key

    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password_hash',
        'is_locked' 

    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}