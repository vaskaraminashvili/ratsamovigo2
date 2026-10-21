<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\JoinsPendingInvite;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('შესვლა')]
class Login extends Component
{
    use JoinsPendingInvite;

    public string $email = '';

    public string $password = '';

    public bool $remember = true;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'შეიყვანე ელფოსტა.',
            'email.email' => 'ელფოსტის ფორმატი არასწორია.',
            'password.required' => 'შეიყვანე პაროლი.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'ძალიან ბევრი მცდელობა. სცადე '.RateLimiter::availableIn($throttleKey).' წამში.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'ელფოსტა ან პაროლი არასწორია.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->joinPendingInvite(Auth::user());

        $this->redirectIntended(route('lists.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
