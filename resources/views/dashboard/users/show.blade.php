<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        body { background-color: #fafafa; }
        
        /* Teva Exact Layout */
        .container { max-width: 935px !important; margin: 0 auto !important; }
        
        /* Profile Section */
        .profile-section { 
            padding: 30px 0 !important; 
            border-bottom: 1px solid #dbdbdb !important;
        }
        
        /* Follow Button */
        .btn-follow { 
            background: #0095f6 !important; 
            border: 1px solid #0095f6 !important; 
            color: white !important; 
            font-weight: 600 !important;
            padding: 5px 20px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
        }
        
        /* Profile Picture */
        .profile-pic {
            width: 150px !important;
            height: 150px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 3px solid white !important;
        }
        
        /* Stats */
        .stats { 
            gap: 40px !important; 
            margin: 20px 0 !important;
        }
        .stat-number { 
            font-weight: 600 !important; 
            font-size: 16px !important;
        }
        .stat-label { 
            font-size: 12px !important; 
            color: #8e8e8e !important;
            margin-top: 4px !important;
        }
        
        /* Posts Grid - Teva Style */
        .posts-grid { 
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 2px !important;
            max-width: 618px !important;
            margin: 30px auto !important;
        }
        .post-item { 
            aspect-ratio: 1/1 !important;
            width: 100% !important;
            height: 206px !important;
            overflow: hidden !important;
        }
        .post-img { 
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: transform 0.3s ease !important;
        }
        .post-item:hover .post-img { 
            transform: scale(1.05) !important;
        }
    </style>

    <div class="min-h-screen bg-[#fafafa]">
        <div class="container px-0">
            
            <!-- === HEADER === -->
            <header class="bg-white border-b border-[#dbdbdb] sticky top-0 z-10">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <a href="{{ route('users.index') }}" class="p-2 hover:bg-[#efefef] rounded-full mr-4" data-preload-click>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                            <h2 class="text-base font-semibold">{{ $profile->username }}</h2>
                        </div>
                        <button class="p-2 hover:bg-[#efefef] rounded-full">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- === PROFILE SECTION === -->
            <section class="bg-white profile-section">
                <div class="px-4">
                    <div class="flex items-center justify-between">
                        <!-- Profile Picture + Username + Follow -->
                        <div class="flex items-center">
                            <img src="{{ $profile->profile_image_url }}"
                                 alt="{{ $profile->username }}"
                                 class="profile-pic mr-4">

                            <div>
                                <div class="flex items-center mb-3">
                                    <h1 class="text-xl font-semibold mr-4">{{ $profile->username }}</h1>
                                    <button class="btn-follow">دنبال کردن</button>
                                </div>

                                <!-- Stats -->
                                <div class="flex items-center stats">
                                    <div class="text-center">
                                        <div class="stat-number">{{ number_format($profile->posts ?? 0) }}</div>
                                        <div class="stat-label">پست‌ها</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="stat-number">{{ number_format($profile->followers ?? 0) }}</div>
                                        <div class="stat-label">دنبال‌کننده</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="stat-number">{{ number_format($profile->following ?? 0) }}</div>
                                        <div class="stat-label">دنبال‌کردن</div>
                                    </div>
                                </div>

                                @if($profile->bio)
                                    <p class="mt-4 text-sm text-gray-700 leading-relaxed max-w-xl">{{ $profile->bio }}</p>
                                @endif

                                <div class="mt-4 text-xs text-gray-500 space-y-1">
                                    <div>وضعیت حساب: {{ $profile->exists ? 'فعال' : 'نامشخص' }}</div>
                                    <div>خصوصی: {{ $profile->is_private ? 'بله' : 'خیر' }} • تایید شده: {{ $profile->is_verified ? 'بله' : 'خیر' }}</div>
                                    <div>استوری فعال: {{ $profile->has_stories ? 'بله' : 'خیر' }}</div>
                                    <div>آخرین بروزرسانی: {{ optional($profile->updated_at)->format('Y-m-d H:i') }}</div>
                                    @if($profile->profile_pic_downloaded_at)
                                        <div>دانلود تصویر: {{ optional($profile->profile_pic_downloaded_at)->format('Y-m-d H:i') }}</div>
                                    @endif
                                    @if($profile->profile_pic_status !== 'completed')
                                        <div class="text-amber-600 font-semibold">
                                            تصویر پروفایل: {{ $profile->profile_pic_status }} ({{ $profile->profile_pic_progress }}٪)
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- === POSTS GRID === -->
            <section>
                <div class="posts-grid">
                    @for($i = 1; $i <= 12; $i++)
                        <div class="post-item">
                            <img src="https://picsum.photos/400/400?random={{ $i }}" 
                                 alt="Post {{ $i }}"
                                 class="post-img">
                        </div>
                    @endfor
                </div>
            </section>
        </div>
    </div>
</x-app-layout>