{{-- Shown while the device has no connection; cached lists stay readable. --}}
<div x-data="{ offline: !navigator.onLine }" @online.window="offline = false" @offline.window="offline = true"
     x-show="offline" x-cloak role="status"
     class="fixed inset-x-0 bottom-[max(6.5rem,calc(env(safe-area-inset-bottom)+5.5rem))] z-30 mx-auto w-fit max-w-[90%] rounded-full border border-ink-700 bg-ink-850 px-4 py-2 text-[14px] font-medium text-ink-200 shadow-xl">
    ოფლაინ ხარ · ჩანს ბოლო შენახული ვერსია
</div>
