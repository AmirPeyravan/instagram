<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstagramProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'user_id',
        'full_name',
        'bio',
        'profile_pic',
        'profile_pic_url',
        'profile_pic_status',
        'profile_pic_progress',
        'profile_pic_disk',
        'profile_pic_downloaded_at',
        'profile_pic_error',
        'followers',
        'following',
        'posts',
        'exists',
        'is_private',
        'is_verified',
        'has_stories'
    ];

    protected $casts = [
        'exists' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'has_stories' => 'boolean',
        'profile_pic_progress' => 'integer',
        'profile_pic_downloaded_at' => 'datetime',
    ];

    protected $appends = [
        'profile_image_url',
    ];

    public function getProfileImageUrlAttribute(): string
    {
        $disk = $this->profile_pic_disk ?: 'public';
        $path = $this->profile_pic;

        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->url($path);
        }

        if ($this->profile_pic_url) {
            return $this->profile_pic_url;
        }

        $username = $this->username ?: 'unknown';

        return sprintf(
            'https://ui-avatars.com/api/?name=%s&background=0D8ABC&color=fff',
            urlencode(Str::limit($username, 20, ''))
        );
    }
}