<x-guest-layout title="Create an Account — AI Builds My Website">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Create your account</h2>
        <p class="text-xs text-slate-500 mt-1">Start generating authentic, AI-powered business websites</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g. John Doe">
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="you@company.com">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
            <input type="password" id="password" name="password" required
                class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Minimum 8 characters">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Re-type your password">
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-xs text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Create Account
            </button>
        </div>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500">
            Sign in
        </a>
    </div>
</x-guest-layout>
