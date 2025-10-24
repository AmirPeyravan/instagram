<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_profiles', function (Blueprint $table) {
            $table->text('profile_pic_url')->nullable()->after('profile_pic');
            $table->string('profile_pic_status', 32)->default('idle')->after('profile_pic_url');
            $table->unsignedTinyInteger('profile_pic_progress')->default(0)->after('profile_pic_status');
            $table->string('profile_pic_disk')->default('public')->after('profile_pic_progress');
            $table->timestamp('profile_pic_downloaded_at')->nullable()->after('profile_pic_disk');
            $table->text('profile_pic_error')->nullable()->after('profile_pic_downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('instagram_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'profile_pic_url',
                'profile_pic_status',
                'profile_pic_progress',
                'profile_pic_disk',
                'profile_pic_downloaded_at',
                'profile_pic_error',
            ]);
        });
    }
};
