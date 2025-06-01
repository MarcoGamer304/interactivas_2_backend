<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $casts = [
        'comments' => 'array',
    ];

    protected $fillable = [
        "user_id",
        "avatar",
        "message",
        "image",
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
