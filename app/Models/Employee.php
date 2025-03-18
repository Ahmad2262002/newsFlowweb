<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees'; // table name

    protected $primaryKey = 'employees_id'; // primary key

    public $timestamps = true; // Enable timestamps (created_at, updated_at)

    protected $fillable = [
        'staff_id',  // Foreign key 
        'position',
        'hire_date',
    ];

    // Relationship: Each employee is linked to a staff member
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}