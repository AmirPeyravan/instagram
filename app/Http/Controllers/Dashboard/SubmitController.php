<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\InstagramProfile;
use Illuminate\Http\Request;
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
            'username' => 'required|string|max:30|regex:/^[a-zA-Z0-9._]+$/', // فقط کاراکترهای مجاز IG
        ], [
            'username.required' => 'نام کاربری الزامی است',
            'username.regex' => 'نام کاربری فقط حروف، اعداد، نقطه و آندرلاین مجاز است',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $username = trim($request->username);

        // درخواست به API Python
        $response = Http::timeout(30)->post('http://45.14.224.225:5000/check', [
            'username' => $username,
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data)) {
            // ذخیره در دیتابیس
            InstagramProfile::updateOrCreate(
                ['username' => $username],
                [
                    'user_id'       => $data['user_id'] ?? null,
                    'full_name'     => $data['full_name'] ?? null,
                    'bio'           => $data['bio'] ?? null,
                    'profile_pic'   => $data['profile_pic'] ?? null,
                    'followers'     => $data['followers'] ?? null,
                    'following'     => $data['following'] ?? null,
                    'posts'         => $data['posts'] ?? null,
                    'exists'        => $data['exists'] ?? false,
                    'is_private'    => $data['is_private'] ?? false,
                    'is_verified'   => $data['is_verified'] ?? false,
                    'has_stories'   => $data['has_stories'] ?? false,
                ]
            );

            return redirect()->back()
                ->with('success', '✅ پروفایل با موفقیت بررسی و ذخیره شد!')
                ->with('data', $data);
        } else {
            return back()->withErrors([
                'username' => '❌ خطا در اتصال به سرور یا پروفایل یافت نشد'
            ])->withInput();
        }
    }
}