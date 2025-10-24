<?php

namespace App\Jobs;

use App\Models\InstagramProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DownloadProfilePicture implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public $timeout = 120;

    public function __construct(public InstagramProfile $profile)
    {
        $this->queue = 'profile-media';
    }

    public function handle(): void
    {
        $profile = $this->profile->fresh();

        if (!$profile) {
            return;
        }

        if (!$profile->profile_pic_url) {
            $profile->forceFill([
                'profile_pic_status' => 'skipped',
                'profile_pic_progress' => 100,
            ])->save();

            return;
        }

        $disk = $profile->profile_pic_disk ?: 'public';
        $existingPath = $profile->profile_pic;

        if ($existingPath && Storage::disk($disk)->exists($existingPath)) {
            $profile->forceFill([
                'profile_pic_status' => 'completed',
                'profile_pic_progress' => 100,
                'profile_pic_error' => null,
            ])->save();

            return;
        }

        $profile->forceFill([
            'profile_pic_status' => 'downloading',
            'profile_pic_progress' => 10,
            'profile_pic_error' => null,
        ])->save();

        $temporaryFile = tempnam(sys_get_temp_dir(), 'insta_pic_');

        try {
            Http::timeout(45)
                ->withOptions(['stream' => true])
                ->sink($temporaryFile)
                ->get($profile->profile_pic_url)
                ->throw();

            $profile->forceFill([
                'profile_pic_status' => 'processing',
                'profile_pic_progress' => 65,
            ])->save();

            $extension = strtolower(pathinfo(parse_url($profile->profile_pic_url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg');
            $hashSeed = implode('|', [
                $profile->id,
                $profile->username,
                (string) $profile->profile_pic_url,
                microtime(true),
            ]);
            $filename = Str::of(hash('sha256', $hashSeed))->substr(0, 48).'.'.$extension;
            $storagePath = 'instagram/'.$filename;

            $stream = fopen($temporaryFile, 'r');

            Storage::disk($disk)->put($storagePath, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $profile->forceFill([
                'profile_pic' => $storagePath,
                'profile_pic_status' => 'completed',
                'profile_pic_progress' => 100,
                'profile_pic_downloaded_at' => now(),
                'profile_pic_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $profile->forceFill([
                'profile_pic_status' => 'failed',
                'profile_pic_progress' => 0,
                'profile_pic_error' => $exception->getMessage(),
            ])->save();

            throw $exception;
        } finally {
            if (is_file($temporaryFile)) {
                @unlink($temporaryFile);
            }
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
    }
}
