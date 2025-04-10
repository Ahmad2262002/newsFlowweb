<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $primaryKey = 'article_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'title',
        'content',
        'source_name',
        'published_date',
        'author_name',
        'status',
        'employee_id'
    ];

    protected $casts = [
        'published_date' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'article_category',
            'article_id',
            'category_id'
        )->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id', 'article_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'article_id', 'article_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'article_id', 'article_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'article_id', 'article_id');
    }
}