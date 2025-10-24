<?php

namespace App\Http\Controllers;

use App\Models\InstagramProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // لیست کاربران با جستجو و صفحه‌بندی (۸ تا در هر صفحه)
    public function index(Request $request)
    {
        $search = $request->input('q');

        $profiles = InstagramProfile::query()
            ->when($search, fn ($query) => $query->where('username', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->paginate(8)
            ->withQueryString();

        return view('dashboard.users.index', [
            'profiles' => $profiles,
            'search' => $search,
        ]);
    }

    // نمایش جزئیات کاربر
    public function show(InstagramProfile $profile)
    {
        return view('dashboard.users.show', [
            'profile' => $profile,
        ]);
    }
}
