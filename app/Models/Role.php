<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles'; // Explicitly define the table name if needed

    protected $primaryKey = 'role_id'; // Define the primary key

    public $timestamps = true; // Enable automatic timestamps (created_at, updated_at)

    protected $fillable = [
        'role_name',
    ];

    // Define a relationship with the Staff model
    public function staffs()
    {
        return $this->hasMany(Staff::class, 'role_id', 'role_id');
    }
}