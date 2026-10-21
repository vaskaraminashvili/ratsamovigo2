<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" @if (($serverTheme = auth()->user()?->theme) && $serverTheme !== 'system') data-palette="{{ $serverTheme }}" @endif>
<head>
    @include('layouts.partials.head', ['title' => $title ?? null])
</head>
<body class="min-h-dvh">
    {{ $slot }}

    <x-toast />
    <x-offline-banner />
    @livewireScripts
</body>
</html>
