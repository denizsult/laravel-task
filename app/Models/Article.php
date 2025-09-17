<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory, HasUuids;

  
    protected $fillable = [
        'title',
        'body',
    ];


    /* Relationships */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function publishedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', 'published');
    }
}
