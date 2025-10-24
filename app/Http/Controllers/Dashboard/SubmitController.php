<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\InstagramProfile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
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

        InstagramProfile::updateOrCreate(
            ['username' => $username],
            [
                'user_id'     => Arr::get($data, 'user_id'),
                'full_name'   => Arr::get($data, 'full_name'),
                'bio'         => Arr::get($data, 'bio'),
                'profile_pic' => Arr::get($data, 'profile_pic'),
                'followers'   => Arr::get($data, 'followers'),
                'following'   => Arr::get($data, 'following'),
                'posts'       => Arr::get($data, 'posts'),
                'exists'      => (bool) Arr::get($data, 'exists', false),
                'is_private'  => (bool) Arr::get($data, 'is_private', false),
                'is_verified' => (bool) Arr::get($data, 'is_verified', false),
                'has_stories' => (bool) Arr::get($data, 'has_stories', false),
            ]
        );

        return redirect()->back()
            ->with('success', '✅ پروفایل با موفقیت بررسی و ذخیره شد!')
            ->with('data', $data);
    }
}