<?php

namespace App\Jobs;

use App\Models\InstagramProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DownloadProfilePicture implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public string $queue = 'profile-media';

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public $timeout = 120;


    public $profile;

    public function __construct(InstagramProfile $profile)
    {
        $this->profile = $profile;

        // ✅ به روش تمیز صف را ست کن
        $this->onQueue('profile-media');
    }

public function handle(): void
{
    $username  = $this->profile->username;
    $profileId = $this->profile->id;
    $imageUrl  = $this->profile->profile_pic_url; // توجه کن نام فیلد درست باشه
    $proxy     = 'socks5://127.0.0.1:10808';

    logger()->info('Starting profile picture download', [
        'profile_id' => $profileId,
        'username'   => $username,
        'url'        => $imageUrl,
        'proxy'      => $proxy,
    ]);

    // 🧩 1. بررسی وجود URL
    if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        logger()->warning('Profile picture URL is missing or invalid', [
            'profile_id' => $profileId,
            'username'   => $username,
        ]);

        $this->profile->forceFill([
            'profile_pic_status'  => 'skipped',
            'profile_pic_progress' => 100,
            'profile_pic_error'   => 'Invalid or missing profile_pic_url',
        ])->save();

        return;
    }

    try {
        // 🧩 2. دانلود از طریق Proxy
        $response = Http::withOptions([
            'proxy'   => $proxy,
            'timeout' => 25,
            'verify'  => false,
        ])->retry(2, 300)->get($imageUrl);

        if ($response->failed()) {
            throw new \Exception("HTTP request failed with status {$response->status()}");
        }

        $body = $response->body();
        if (empty($body) || strlen($body) < 1024) {
            throw new \Exception('Received invalid or empty image data');
        }

        // 🧩 3. ذخیره فایل با نام یکتا
        $filename = sha1($profileId . microtime(true)) . '.jpg';
        $path     = "instagram/{$filename}";

        Storage::disk('public')->put($path, $body);

        $this->profile->forceFill([
            'profile_pic'            => $path,
            'profile_pic_disk'       => 'public',
            'profile_pic_status'     => 'completed',
            'profile_pic_progress'   => 100,
            'profile_pic_downloaded_at' => now(),
            'profile_pic_error'      => null,
        ])->save();

        logger()->info('Profile picture downloaded and stored', [
            'profile_id' => $profileId,
            'username'   => $username,
            'path'       => $path,
            'proxy'      => $proxy,
        ]);

    } catch (\Throwable $e) {
        logger()->error('Profile picture download failed', [
            'profile_id' => $profileId,
            'username'   => $username,
            'url'        => $imageUrl,
            'error'      => $e->getMessage(),
        ]);

        $this->profile->forceFill([
            'profile_pic_status'   => 'failed',
            'profile_pic_progress' => 0,
            'profile_pic_error'    => $e->getMessage(),
        ])->save();

        throw $e; // اجازه بده Laravel retry کنه
    }
}

    public function failed(Throwable $exception): void
    {
        $profile = $this->profile->fresh();

        if (!$profile) {
            return;
        }

        $profile->forceFill([
            'profile_pic_status' => 'failed',
            'profile_pic_progress' => 0,
            'profile_pic_error' => $exception->getMessage(),
        ])->save();

        Log::error('Profile picture job failed after retries', [
            'profile_id' => $profile->id,
            'username' => $profile->username,
            'error' => $exception->getMessage(),
        ]);
    }
}
