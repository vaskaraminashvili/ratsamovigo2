<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\JoinsPendingInvite;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('რეგისტრაცია')]
class Register extends Component
{
    use JoinsPendingInvite;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public function register(): void
    {
        $throttleKey = 'register|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'ძალიან ბევრი მცდელობა. სცადე '.RateLimiter::availableIn($throttleKey).' წამში.',
            ]);
        }

        RateLimiter::hit($throttleKey, 600);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'დაწერე სახელი, რომ ოჯახმა იცოდეს, ვინ ითხოვს.',
            'email.required' => 'შეიყვანე ელფოსტა, რომლითაც შეხვალ.',
            'email.email' => 'ელფოსტის ფორმატი არასწორია.',
            'email.unique' => 'ეს ელფოსტა უკვე გამოყენებულია.',
            'password.required' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
            'password.min' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
        ]);

        $user = User::create($validated);

        event(new Registered($user));

        Auth::login($user, remember: true);
        session()->regenerate();

        $this->joinPendingInvite($user);

        $this->redirect(route('lists.index'), navigate: true);
    }

    public function render(): View
    {
        $token = session('invite_token');

        return view('livewire.auth.register', [
            'invitedTo' => $token
                ? ShoppingList::query()->with('owner')->where('invite_token', $token)->first()
                : null,
        ]);
    }
}
