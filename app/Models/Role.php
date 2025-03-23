<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';
    public $timestamps = true; // Enable automatic timestamps (created_at, updated_at)

    protected $fillable = ['role_name'];

    public function staffs()
    {
        return $this->hasMany(Staff::class, 'role_id');
    }
    
}