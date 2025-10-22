<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'username', 'user_id', 'full_name', 'bio', 'profile_pic',
        'followers', 'following', 'posts', 'exists', 'is_private',
        'is_verified', 'has_stories'
    ];

    protected $casts = [
        'exists' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'has_stories' => 'boolean',
    ];
}