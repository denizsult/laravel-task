<?php

namespace App\Models;

use App\Events\CommentCreating;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'article_id',
        'user_id',
        'content',
        'status',
    ];


    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($comment) {
            CommentCreating::dispatch($comment);
        });
    }


    /* Relationships */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* Scopes */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }


    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
