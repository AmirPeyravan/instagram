<x-app-layout>
    <style>
        body { background-color: #f8f9fa; }
        .submit-container { max-width: 500px; margin: 0 auto; padding: 40px 20px; }
        .submit-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 40px; }
        .form-group { margin-bottom: 20px; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 16px; }
        .form-control:focus { border-color: #0095f6; outline: none; }
        .btn-submit { background: #0095f6; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background: #1877f2; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .result-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .result-table th, .result-table td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .result-table th { background: #f8f9fa; font-weight: bold; }
    </style>

    @php
        $profileId = session('profile_id');
    @endphp

    <div class="submit-container">
        <div class="submit-card"
             x-data="submitDashboard({
                 profileId: {{ $profileId ? (int) $profileId : 'null' }},
                 progressEndpoint: '{{ $profileId ? route('dashboard.submit.progress', $profileId) : '' }}'
             })"
             x-init="init()"
        >
            <h2 class="text-center mb-4">🔍 بررسی پروفایل اینستاگرام</h2>
            <p class="text-center text-gray-600 mb-4">نام کاربری را وارد کنید تا اطلاعات پروفایل بررسی و ذخیره شود</p>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>

                @if (session('data'))
                    <h3 class="text-center mb-3">📊 اطلاعات دریافتی:</h3>
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>فیلد</th>
                                <th>مقدار</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('data') as $key => $value)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                    <td>{{ is_null($value) ? 'نامشخص' : $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.submit.store') }}" data-preload>
                @csrf
                <div class="form-group">
                    <label for="username" class="block mb-2 font-semibold">نام کاربری اینستاگرام</label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="{{ old('username') }}" 
                           placeholder="مثال: instagram" 
                           class="form-control">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn-submit">ثبت و بررسی</button>
                </div>
            </form>

            <div class="mt-8" x-show="hasProgress" x-transition>
                <div class="mb-2 flex items-center justify-between text-sm font-semibold text-gray-700">
                    <span x-text="progressMessage"></span>
                    <span x-text="progressValueLabel"></span>
                </div>
                <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-blue-500 transition-all duration-300" :style="`width: ${progressValue}%`"></div>
                </div>
                <template x-if="imageUrl">
                    <div class="mt-6 flex items-center rounded-xl bg-blue-50 p-4">
                        <img :src="imageUrl" alt="پروفایل" class="h-16 w-16 rounded-full border-4 border-white object-cover shadow" />
                        <div class="mr-4 text-sm text-gray-700">
                            <div class="font-semibold">تصویر پروفایل آماده است.</div>
                            <div class="text-xs text-gray-500" x-text="statusLabel"></div>
                        </div>
                    </div>
                </template>
                <template x-if="status === 'failed'">
                    <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                        مشکلی در دانلود تصویر پروفایل رخ داد. لطفاً مجدداً تلاش کنید.
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.submitDashboard = function (options) {
                return {
                    profileId: options.profileId,
                    endpoint: options.progressEndpoint,
                    progressValue: 0,
                    status: null,
                    message: '',
                    imageUrl: '',
                    pollTimer: null,
                    init() {
                        if (this.profileId && this.endpoint) {
                            this.status = 'queued';
                            this.message = 'در انتظار دانلود تصویر...';
                            this.progressValue = 5;

                            this.dispatchProgress({ active: true, progress: this.progressValue, status: this.status, message: this.message });
                            this.fetchProgress();
                            this.pollTimer = setInterval(() => this.fetchProgress(), 2500);
                        }
                    },
                    fetchProgress() {
                        if (!this.endpoint) {
                            return;
                        }

                        fetch(this.endpoint, {
                            headers: { 'Accept': 'application/json' }
                        })
                            .then(response => response.ok ? response.json() : Promise.reject(response))
                            .then(data => {
                                this.progressValue = data.progress ?? 0;
                                this.status = data.status ?? null;
                                this.message = data.message ?? '';
                                this.imageUrl = data.image_url ?? '';

                                const active = Boolean(data.active);

                                this.dispatchProgress({
                                    active,
                                    progress: this.progressValue,
                                    status: this.status,
                                    message: this.message,
                                });

                                if (!active && this.pollTimer) {
                                    clearInterval(this.pollTimer);
                                    this.pollTimer = null;
                                }
                            })
                            .catch(() => {
                                this.status = 'failed';
                                this.message = 'عدم موفقیت در واکشی وضعیت.';
                                this.progressValue = 0;

                                this.dispatchProgress({ active: false, progress: this.progressValue, status: this.status, message: this.message });

                                if (this.pollTimer) {
                                    clearInterval(this.pollTimer);
                                    this.pollTimer = null;
                                }
                            });
                    },
                    dispatchProgress(detail) {
                        if (!detail.label) {
                            detail.label = this.statusLabel;
                        }

                        window.dispatchEvent(new CustomEvent('dashboard:progress', { detail }));
                    },
                    get hasProgress() {
                        return Boolean(this.status);
                    },
                    get progressValueLabel() {
                        return `${this.progressValue}%`;
                    },
                    get progressMessage() {
                        return this.message || 'در انتظار دریافت وضعیت...';
                    },
                    get statusLabel() {
                        switch (this.status) {
                            case 'completed':
                                return 'دانلود با موفقیت انجام شد.';
                            case 'failed':
                                return 'دانلود تصویر با خطا مواجه شد.';
                            case 'processing':
                                return 'در حال پردازش تصویر پروفایل';
                            case 'downloading':
                                return 'در حال دریافت تصویر پروفایل';
                            case 'queued':
                                return 'در صف دانلود قرار دارد';
                            case 'skipped':
                                return 'تصویری برای دانلود ارائه نشده است';
                            default:
                                return 'منتظر وضعیت پردازش';
                        }
                    }
                };
            };
        });
    </script>
@endpush