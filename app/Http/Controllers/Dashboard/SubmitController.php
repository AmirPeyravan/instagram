<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadProfilePicture;
use App\Models\InstagramProfile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubmitController extends Controller
{
    public function index()
    {
        return view('dashboard.submit.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:30|regex:/^[a-zA-Z0-9._]+$/',
        ], [
            'username.required' => 'نام کاربری الزامی است',
            'username.regex' => 'نام کاربری فقط حروف، اعداد، نقطه و آندرلاین مجاز است',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $username = trim($request->input('username'));

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->post('http://45.14.224.225:5000/check', [
                    'username' => $username,
                ])
                ->throw();
        } catch (ConnectionException | RequestException $exception) {
            return back()->withErrors([
                'username' => '❌ برقراری ارتباط با سرویس بررسی پروفایل با خطا مواجه شد. لطفاً دوباره تلاش کنید.',
            ])->withInput();
        }

        $data = $response->json();

        if (!is_array($data) || empty($data)) {
            return back()->withErrors([
                'username' => '❌ پاسخی از سرویس دریافت نشد یا اطلاعات نامعتبر است.',
            ])->withInput();
        }

        $profile = InstagramProfile::firstOrNew(['username' => $username]);

        $incomingProfilePicUrl = Arr::get($data, 'profile_pic');
        $profilePicUrlChanged = $incomingProfilePicUrl !== $profile->profile_pic_url;

        $profile->fill([
            'user_id'    => Arr::get($data, 'user_id'),
            'full_name'  => Arr::get($data, 'full_name'),
            'bio'        => Arr::get($data, 'bio'),
            'followers'  => Arr::get($data, 'followers'),
            'following'  => Arr::get($data, 'following'),
            'posts'      => Arr::get($data, 'posts'),
            'exists'     => (bool) Arr::get($data, 'exists', false),
            'is_private' => (bool) Arr::get($data, 'is_private', false),
            'is_verified'=> (bool) Arr::get($data, 'is_verified', false),
            'has_stories'=> (bool) Arr::get($data, 'has_stories', false),
        ]);

        if ($incomingProfilePicUrl) {
            $shouldResetImage = $profilePicUrlChanged
                || empty($profile->profile_pic)
                || $profile->profile_pic_status === 'failed';

            if ($shouldResetImage) {
                if ($profile->profile_pic) {
                    Storage::disk($profile->profile_pic_disk ?: 'public')->delete($profile->profile_pic);
                }

                $profile->profile_pic = null;
                $profile->profile_pic_downloaded_at = null;
                $profile->profile_pic_status = 'queued';
                $profile->profile_pic_progress = 5;
                $profile->profile_pic_error = null;
            }

            $profile->profile_pic_url = $incomingProfilePicUrl;
        } else {
            $profile->profile_pic_url = null;
            $profile->profile_pic = null;
            $profile->profile_pic_status = 'skipped';
            $profile->profile_pic_progress = 100;
            $profile->profile_pic_error = null;
        }

        $profile->save();

        if ($incomingProfilePicUrl && ($profilePicUrlChanged || !$profile->profile_pic || $profile->profile_pic_status === 'failed')) {
            DownloadProfilePicture::dispatch($profile);
        }

        return redirect()->back()
            ->with('success', '✅ پروفایل با موفقیت بررسی و ذخیره شد!')
            ->with('data', $data)
            ->with('profile_id', $profile->id)
            ->with('profile_status', $profile->profile_pic_status);
    }

    public function progress(InstagramProfile $profile)
    {
        $profile->refresh();

        $status = $profile->profile_pic_status;
        $activeStatuses = ['queued', 'downloading', 'processing'];
        $isActive = in_array($status, $activeStatuses, true);

        return response()->json([
            'id' => $profile->id,
            'username' => $profile->username,
            'progress' => (int) $profile->profile_pic_progress,
            'status' => $status,
            'message' => $this->progressMessage($profile),
            'active' => $isActive,
            'image_url' => $profile->profile_image_url,
            'error' => $profile->profile_pic_error,
        ]);
    }

    protected function progressMessage(InstagramProfile $profile): string
    {
        return match ($profile->profile_pic_status) {
            'queued' => 'صف در انتظار دانلود تصویر پروفایل قرار گرفت.',
            'downloading' => 'در حال دانلود تصویر پروفایل...',
            'processing' => 'در حال پردازش و ذخیره‌سازی تصویر پروفایل...',
            'completed' => 'تصویر پروفایل با موفقیت ذخیره شد.',
            'skipped' => 'تصویر پروفایلی برای دانلود ارائه نشده است.',
            'failed' => 'دانلود تصویر پروفایل با خطا مواجه شد.',
            default => 'وضعیت نامشخص است.',
        };
    }
}