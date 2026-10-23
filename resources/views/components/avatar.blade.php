@props(['user'])
<span {{ $attributes->class(['grid place-items-center overflow-hidden rounded-full font-bold text-on', $user->avatarColorClass() => ! $user->avatarUrl()]) }}>
    @if ($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="" class="size-full object-cover" loading="lazy">
    @else
        {{ $user->initials() }}
    @endif
</span>
