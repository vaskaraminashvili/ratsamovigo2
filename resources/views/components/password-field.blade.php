@props(['label', 'name', 'hint' => null, 'autocomplete' => 'current-password', 'aside' => null])

@php($hasError = $errors->has($name))

<div x-data="{ show: false }">
    <div class="flex items-baseline justify-between">
        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
        {{ $aside }}
    </div>
    <div class="relative">
        <input
            id="{{ $name }}"
            :type="show ? 'text' : 'password'"
            wire:model="{{ $name }}"
            autocomplete="{{ $autocomplete }}"
            {{ $attributes->class(['field pr-12', '!border-urgent-400' => $hasError]) }}
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        >
        <button type="button" @click="show = !show" class="absolute inset-y-0 right-1 grid w-11 place-items-center text-ink-400 hover:text-ink-100"
                :aria-label="show ? 'პაროლის დამალვა' : 'პაროლის ჩვენება'">
            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </button>
    </div>
    @if ($hasError)
        <p id="{{ $name }}-error" class="field-error">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
</div>
