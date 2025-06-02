<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAction extends Model
{
    use HasFactory;
    protected $table = 'admin_action';

    protected $primaryKey = 'action_id';
    protected $fillable = ['action_type', 'action_date', 'description', 'admin_id', 'target_staff_id'];
// Relationship to Admin (make sure Admin model exists)
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    // Relationship to Staff
    public function targetStaff()
    {
        return $this->belongsTo(Staff::class, 'target_staff_id', 'staff_id');
    }
}