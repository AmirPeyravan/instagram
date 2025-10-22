<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstagramUser;

class UserController extends Controller
{
    // لیست کاربران با جستجو و صفحه‌بندی (۸ تا در هر صفحه)
    public function index(Request $request)
    {
        $search = $request->input('q');

        $users = InstagramUser::query();

        if ($search) {
            $users = $users->where('username', 'like', "%{$search}%");
        }

        $users = $users->orderBy('id', 'asc')->paginate(8);

        return view('dashboard.users.index', compact('users', 'search'));
    }

    // نمایش جزئیات کاربر
    public function show($pid)
    {
        $user = InstagramUser::where('pid', $pid)->firstOrFail();

        return view('dashboard.users.show', compact('user'));
    }
}
