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

    <div class="submit-container">
        <div class="submit-card">
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

            <form method="POST" action="{{ route('dashboard.submit.store') }}">
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
        </div>
    </div>
</x-app-layout>