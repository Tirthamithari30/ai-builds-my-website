<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900 text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found — {{ config('app.name', 'AI Builds My Website') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-6 shadow-xl">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-400 mb-2">404 Error</p>
        <h1 class="text-3xl font-bold tracking-tight text-white mb-3">Page Not Found</h1>
        <p class="text-slate-400 mb-8 leading-relaxed">
            The page you requested could not be found, is unpublished, or the URL address has moved.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-medium text-white bg-blue-600 hover:bg-blue-500 transition shadow-lg shadow-blue-500/20">
                Return to Home
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 transition border border-slate-700">
                    Go to Dashboard
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
