<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class User extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'staff_id',
        'preferences',
        'profile_picture'
    ];

    // Accessor for profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture 
            ? Storage::url($this->profile_picture)
            : null;
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function shares()
    {
        return $this->hasMany(Share::class, 'user_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'user_id');
    }
}