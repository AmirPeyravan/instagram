<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'pid',
        'username',
        'profile_image',
        'followers_count',
        'following_count',
        'post_count',
    ];
}
