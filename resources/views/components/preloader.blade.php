<div
    x-cloak
    x-show="loading || progressVisible"
    x-transition.opacity
    class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm"
>
    <template x-if="progressVisible">
        <div class="w-80 max-w-full rounded-xl bg-white/70 p-6 shadow-lg">
            <p class="text-sm font-semibold text-gray-700" x-text="progressMessage || 'در حال آماده‌سازی درخواست...'">
                در حال آماده‌سازی درخواست...
            </p>
            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                    class="h-full rounded-full bg-blue-500 transition-all duration-300"
                    :style="`width: ${progressValue}%`"
                ></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs font-semibold text-gray-600">
                <span x-text="progressStatus"></span>
                <span x-text="`${progressValue}%`"></span>
            </div>
        </div>
    </template>
    <template x-if="!progressVisible">
        <div class="flex flex-col items-center">
            <svg class="h-12 w-12 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="mt-4 text-sm font-semibold text-gray-700">در حال پردازش، لطفاً صبر کنید...</p>
        </div>
    </template>
</div>
