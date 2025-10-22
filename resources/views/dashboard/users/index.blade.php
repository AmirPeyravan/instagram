<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        
        /* 2x4 GRID - تضمینی! */
        .user-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            grid-template-rows: repeat(2, auto) !important;
            gap: 20px !important;
            width: 100% !important;
        }
        
        .user-card {
            width: 100% !important;
        }
        
        @media (max-width: 768px) {
            .user-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                grid-template-rows: repeat(4, auto) !important;
                gap: 16px !important;
            }
        }
    </style>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
        <div class="container mx-auto px-4">
            
            <!-- === HEADER === -->
            <div class="text-center mb-12 fade-in-up">
                <h1 class="text-5xl font-black text-gray-800 mb-2">جستجوی کاربران</h1>
                <p class="text-xl text-gray-600">
                    صفحه {{ $users->currentPage() }} از {{ $users->lastPage() }} • 
                    {{ $users->total() }} کاربر • 
                    {{ $users->count() }} کاربر در این صفحه
                </p>
            </div>

            <!-- === SEARCH BAR === -->
            <div class="max-w-md mx-auto mb-12 fade-in-up" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('users.index') }}" class="flex">
                    <input type="hidden" name="page" value="1">
                    <input type="text" 
                           name="q" 
                           value="{{ $search ?? '' }}" 
                           placeholder="🔍 نام کاربری..." 
                           class="flex-1 px-6 py-4 rounded-l-full border-2 border-gray-300 focus:border-blue-500 focus:outline-none text-lg">
                    <button type="submit" 
                            class="px-8 py-4 bg-blue-600 text-white rounded-r-full font-bold hover:bg-blue-700 transition-colors">
                        جستجو
                    </button>
                </form>
            </div>

            @if($users->count())
                <!-- === 2x4 GRID - 8 USERS === -->
                <div class="user-grid fade-in-up" style="animation-delay: 0.4s;">
                    @foreach($users as $user)
                        <a href="{{ route('users.show', $user->pid) }}" 
                           class="user-card group bg-white rounded-2xl p-6 text-center shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-gray-200">
                            
                            <!-- Profile Image -->
                            <div class="relative mx-auto mb-4">
                                <img src="{{ $user->profile_image }}" 
                                     alt="{{ $user->username }}"
                                     class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md mx-auto">
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                    <span class="text-white text-xs">●</span>
                                </div>
                            </div>
                            
                            <!-- Username -->
                            <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">
                                {{ $user->username }}
                            </h3>
                            
                            <!-- Followers -->
                            <p class="text-sm text-gray-600 font-medium">
                                {{ number_format($user->followers_count) }} 
                                <span class="text-blue-600">دنبال‌کننده</span>
                            </p>
                        </a>
                    @endforeach
                </div>

                <!-- === PAGINATION 7 صفحه === -->
                <div class="flex justify-center mb-12 fade-in-up" style="animation-delay: 0.6s;">
                    <nav class="bg-white rounded-full p-2 shadow-lg">
                        <ul class="flex items-center gap-1">
                            <!-- Previous -->
                            @if($users->onFirstPage())
                                <li><span class="px-4 py-2 text-gray-400 rounded-full">⬅</span></li>
                            @else
                                <li><a href="{{ $users->previousPageUrl() }}" class="px-4 py-2 text-blue-600 font-bold rounded-full hover:bg-blue-50">⬅</a></li>
                            @endif

                            <!-- 7 Page Numbers -->
                            @for($i = 1; $i <= $users->lastPage(); $i++)
                                @if($i == $users->currentPage())
                                    <li><span class="px-3 py-2 bg-blue-600 text-white rounded-full font-bold w-8 text-center">{{ $i }}</span></li>
                                @else
                                    <li><a href="{{ $users->url($i) }}" class="px-3 py-2 text-gray-700 hover:bg-blue-50 rounded-full w-8 text-center">{{ $i }}</a></li>
                                @endif
                            @endfor

                            <!-- Next -->
                            @if($users->hasMorePages())
                                <li><a href="{{ $users->nextPageUrl() }}" class="px-4 py-2 text-blue-600 font-bold rounded-full hover:bg-blue-50">➡</a></li>
                            @else
                                <li><span class="px-4 py-2 text-gray-400 rounded-full">➡</span></li>
                            @endif
                        </ul>
                    </nav>
                </div>
                
            @else
                <div class="text-center py-20 fade-in-up">
                    <p class="text-gray-500 text-xl">کاربری یافت نشد</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>