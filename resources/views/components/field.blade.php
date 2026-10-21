@props(['label', 'name', 'hint' => null, 'type' => 'text'])

@php($hasError = $errors->has($name))

<div>
    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    <input
        id="{{ $name }}"
        type="{{ $type }}"
        wire:model="{{ $name }}"
        {{ $attributes->class(['field', '!border-urgent-400' => $hasError]) }}
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
    >
    @if ($hasError)
        <p id="{{ $name }}-error" class="field-error">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
</div>
