<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" @if (($serverTheme = auth()->user()?->theme) && $serverTheme !== 'system') data-palette="{{ $serverTheme }}" @endif>
<head>
    @include('layouts.partials.head', ['title' => $title ?? null])
</head>
<body class="min-h-dvh">
    <div class="mx-auto max-w-md pb-32">
        <header class="sticky top-0 z-20 flex items-center justify-between bg-ink-950/85 px-5 pb-3 pt-[max(1rem,env(safe-area-inset-top))] backdrop-blur">
            <x-app-logo :href="route('lists.index')" />

            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape.window="menu = false">
                <button type="button" @click="menu = !menu" class="relative size-11" aria-label="{{ __('ანგარიშის მენიუ') }}" :aria-expanded="menu">
                    <x-avatar :user="auth()->user()" class="size-11" />
                    @if (auth()->user()->unreadNotifications()->exists())
                        <span class="absolute right-0 top-0 size-3 rounded-full bg-urgent-400 ring-2 ring-ink-950" aria-label="ახალი შეტყობინება"></span>
                    @endif
                </button>
                <div x-show="menu" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-52 overflow-hidden rounded-2xl border border-ink-700 bg-ink-850 py-1 shadow-2xl">
                    <p class="px-4 py-2 text-[13px] text-ink-400">შესული ხარ როგორც {{ auth()->user()->name }}</p>
                    <a href="{{ route('settings.profile') }}#saved" class="block px-4 py-2.5 hover:bg-ink-800">პროფილი და შენახული</a>
                    <a href="{{ route('settings.profile') }}#theme-h" class="block px-4 py-2.5 hover:bg-ink-800">თემა</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2.5 text-left hover:bg-ink-800">გასვლა</button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('status'))
            <p class="mx-5 mb-3 rounded-2xl border border-cart-400/50 bg-cart-400/10 p-3 text-[14px]" role="status">{{ session('status') }}</p>
        @endif

        @unless (auth()->user()->hasVerifiedEmail())
            <form method="POST" action="{{ route('verification.send') }}" class="mx-5 mb-3 flex items-center gap-3 rounded-2xl border border-ink-700 bg-ink-900 p-3">
                @csrf
                <p class="min-w-0 flex-1 text-[14px] leading-snug text-ink-200">დაადასტურე ელფოსტა, რომ პაროლის აღდგენა შეძლო.</p>
                <button type="submit" class="shrink-0 rounded-full border border-ink-700 px-3 py-2.5 text-[13px] font-semibold hover:border-ink-500">გაგზავნა</button>
            </form>
        @endunless

        {{ $slot }}
    </div>

    <x-toast />
    <x-offline-banner />
    @livewireScripts
</body>
</html>
