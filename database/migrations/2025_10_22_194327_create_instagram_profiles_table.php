<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instagram_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();  // username اینستاگرام
            $table->string('user_id')->nullable(); // از API
            $table->string('full_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_pic')->nullable();
            $table->integer('followers')->nullable();
            $table->integer('following')->nullable();
            $table->integer('posts')->nullable();
            $table->boolean('exists')->default(false);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('has_stories')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('instagram_profiles');
    }
};