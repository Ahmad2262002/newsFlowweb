<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employee_id';
    protected $fillable = ['staff_id', 'position', 'hire_date'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'employee_id');
    }
}