<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;
    protected $primaryKey = 'article_id';


    protected $fillable = [
        'title',
        'content',
        'source_name',
        'published_date',
        'author_name',
        'status', //  TINYINT 0, 1, 2
        'employee_id',
    ];

    protected $casts = [
        'status' => 'integer', //integer confirmaion
    ];

    // Relationship with Employee many to one
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'draft',
            self::STATUS_PUBLISHED => 'published',
            self::STATUS_ARCHIVED => 'archived',
            default => 'unknown',
        };
    }
}