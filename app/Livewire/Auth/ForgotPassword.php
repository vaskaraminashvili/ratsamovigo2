<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('პაროლის აღდგენა')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function sendLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'შეიყვანე ელფოსტა.',
            'email.email' => 'ელფოსტის ფორმატი არასწორია.',
        ]);

        $throttleKey = 'forgot-password|'.Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => 'ძალიან ბევრი მცდელობა. სცადე '.RateLimiter::availableIn($throttleKey).' წამში.',
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        Password::sendResetLink(['email' => $this->email]);

        // Same answer whether or not the address exists, so accounts cannot be probed.
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
