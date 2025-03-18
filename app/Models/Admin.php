<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $primaryKey = 'admin_id';
    protected $fillable = ['staff_id', 'access_level'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function adminActions()
    {
        return $this->hasMany(AdminAction::class, 'admin_id');
    }
}