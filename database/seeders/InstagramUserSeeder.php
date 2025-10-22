<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstagramUser;

class InstagramUserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            InstagramUser::create([
                'pid' => 1000 + $i, // PID یکتا
                'username' => "user{$i}",
                'profile_image' => "https://via.placeholder.com/150?text=User{$i}",
                'followers_count' => rand(100, 10000),
                'following_count' => rand(50, 5000),
                'post_count' => rand(10, 500),
            ]);
        }
    }
}
