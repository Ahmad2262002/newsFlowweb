<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens; // Add this import
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable; // Change base class

class User extends Model 
{
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'staff_id',
        'preferences',
        'profile_picture',
        'email', // Add this if storing email directly in users table
        'wants_notifications' // Add this for notification preferences
        
    ];
    protected $hidden = [];
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    protected $appends = ['profile_picture_url', 'is_admin'];
    // Add this method to get the user's email
public function getEmailAttribute()
{
    // If email is stored directly in users table
    if (isset($this->attributes['email'])) {
        return $this->attributes['email'];
    }
    
    // If email is stored in staff table
    if ($this->staff) {
        return $this->staff->email;
    }
    
    return null;
}

    // Accessor for profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture 
            ? Storage::url($this->profile_picture)
            : null;
    }

    // Check if user is admin
    public function getIsAdminAttribute()
    {
        // Assuming you have a 'role' column in your staff table
        // or some other way to determine admin status
        return $this->staff && $this->staff->role === 'admin';
    }

    // Alias method for easier use in controllers
    public function isAdmin()
    {
        return $this->is_admin;
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