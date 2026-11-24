<x-guest-layout title="Sign In — AI Builds My Website">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Sign in to your account</h2>
        <p class="text-xs text-slate-500 mt-1">Access and manage your generated business websites</p>
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

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                placeholder="you@company.com">
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
            </div>
            <div class="mt-1 relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                    class="block w-full px-3 py-2 pr-10 text-sm border border-slate-300 rounded-lg shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Your account password">
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden transition-colors"
                        title="Toggle password visibility"
                        aria-label="Toggle password visibility"
                        data-testid="toggle-password">
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded-sm border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>Remember this device</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-xs text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Sign In
            </button>
        </div>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500">
        Don't have an account yet?
        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500">
            Create an account
        </a>
    </div>
</x-guest-layout>
