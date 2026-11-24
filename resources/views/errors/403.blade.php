<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900 text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Forbidden — {{ config('app.name', 'AI Builds My Website') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-6 shadow-xl">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <p class="text-sm font-semibold uppercase tracking-wider text-amber-400 mb-2">403 Forbidden</p>
        <h1 class="text-3xl font-bold tracking-tight text-white mb-3">Access Denied</h1>
        <p class="text-slate-400 mb-8 leading-relaxed">
            You do not have permission to view or manage this resource. Business assets and websites are isolated to their verified owners.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-medium text-white bg-blue-600 hover:bg-blue-500 transition shadow-lg shadow-blue-500/20">
                Return to My Dashboard
            </a>
        </div>
    </div>
</body>
</html>
