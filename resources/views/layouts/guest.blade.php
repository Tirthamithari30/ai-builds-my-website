<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AI Builds My Website' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans antialiased bg-slate-50 selection:bg-blue-600 selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="/" class="inline-flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-xs font-bold text-lg">
                AI
            </div>
            <span class="text-xl font-bold text-slate-900 tracking-tight">AI Builds My Website</span>
        </a>
        <p class="mt-2 text-xs font-medium text-slate-500">
            Your Business. Your Information. Your Website.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-sm border border-slate-200 rounded-2xl sm:px-10">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
