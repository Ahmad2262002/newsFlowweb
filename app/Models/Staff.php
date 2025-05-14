<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $staff_id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property bool $is_locked
 * 
 * @method bool update(array $attributes = [], array $options = [])
 * @method static static|null find($id)
 * @method $this refresh()
 */
class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'staffs';
    protected $primaryKey = 'staff_id';
    protected $fillable = ['role_id', 'username', 'email', 'password_hash', 'is_locked'];
    protected $hidden = ['password_hash'];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'staff_id', 'staff_id');
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
