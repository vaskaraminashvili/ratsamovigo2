<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('ახალი პაროლი')]
class ResetPassword extends Component
{
    public string $token = '';

    #[Url]
    public string $email = '';

    public string $password = '';

    public function mount(string $token): void
    {
        $this->token = $token;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', PasswordRule::min(8)],
        ], [
            'email.required' => 'შეიყვანე ელფოსტა.',
            'email.email' => 'ელფოსტის ფორმატი არასწორია.',
            'password.required' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
            'password.min' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
        ]);

        $throttleKey = 'reset-password|'.Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'ძალიან ბევრი მცდელობა. სცადე '.RateLimiter::availableIn($throttleKey).' წამში.',
            ]);
        }

        $status = Password::reset(
            ['email' => $this->email, 'password' => $this->password, 'token' => $this->token],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'ბმული ვადაგასულია ან არასწორია. მოითხოვე ახალი.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->flash('status', 'პაროლი შეიცვალა. ახლა შეგიძლია შესვლა.');

        $this->redirect(route('login'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
