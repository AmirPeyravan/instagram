<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body
        x-data="{
            loading: false,
            progressVisible: false,
            progressValue: 0,
            progressStatus: '',
            progressMessage: '',
            showLoader() { this.loading = true; this.progressVisible = false },
            hideLoader() { this.loading = false },
            updateProgress(detail) {
                if (!detail) {
                    return;
                }

                this.progressValue = detail.progress ?? 0;
                this.progressStatus = detail.label ?? detail.status ?? '';
                this.progressMessage = detail.message ?? detail.label ?? '';
                this.progressVisible = Boolean(detail.active);

                if (!detail.active) {
                    setTimeout(() => {
                        this.progressVisible = false;
                    }, 800);
                }
            }
        }"
        x-on:page-loading-start.window="showLoader()"
        x-on:page-loading-stop.window="hideLoader()"
        x-on:beforeunload.window="showLoader()"
        x-on:dashboard-progress.window="updateProgress($event.detail)"
        x-bind:class="{ 'overflow-hidden': loading }"
        class="font-sans antialiased"
    >
        <x-banner />
        <x-preloader />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const startLoading = () => window.dispatchEvent(new CustomEvent('page-loading-start'));
                const stopLoading = () => window.dispatchEvent(new CustomEvent('page-loading-stop'));

                window.addEventListener('load', stopLoading);

                document.body.addEventListener('submit', (event) => {
                    if (event.target?.closest('form[data-preload]')) {
                        startLoading();
                    }
                }, true);

                document.body.addEventListener('click', (event) => {
                    const actionable = event.target.closest('[data-preload-click]');
                    if (!actionable) {
                        return;
                    }

                    if (actionable.tagName === 'A' && actionable.getAttribute('target') === '_blank') {
                        return;
                    }

                    startLoading();
                });

                if (window.Livewire) {
                    window.Livewire.hook('message.sent', startLoading);
                    window.Livewire.hook('message.processed', stopLoading);
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>
