<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $primaryKey = 'article_id';
    protected $fillable = [
        'title',
        'content',
        'source_name',
        'published_date',
        'author_name',
        'status',
        'employee_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category', 'article_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'article_id');
    }

    public function shares()
    {
        return $this->hasMany(Share::class, 'article_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'article_id');
    }
}