@props(['code', 'title', 'text', 'action' => 'მთავარ გვერდზე'])

<x-layouts::guest :title="$title">
    <main class="mx-auto flex min-h-dvh max-w-md flex-col px-5 pb-8 pt-[max(1.25rem,env(safe-area-inset-top))]">
        <x-app-logo :href="route('home')" />

        <div class="my-auto py-10">
            <p class="text-[5rem] font-extrabold leading-none text-primary-fg" aria-hidden="true">{{ $code }}</p>
            <h1 class="mt-6 text-[2rem] font-extrabold leading-[1.15]">{{ $title }}</h1>
            <p class="mt-3 text-ink-300">{{ $text }}</p>
        </div>

        <div class="space-y-3">
            <a href="{{ auth()->check() ? route('lists.index') : route('home') }}" class="btn-primary w-full">{{ auth()->check() ? 'ჩემს სიებზე' : $action }}</a>
            <button type="button" onclick="history.back()" class="btn-ghost w-full">უკან დაბრუნება</button>
        </div>
    </main>
</x-layouts::guest>
