<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('the forgot password page renders and login links to it', function () {
    $this->get(route('password.request'))->assertOk();
    $this->get(route('login'))->assertSee(route('password.request'), false);
});

test('a reset link is sent to a registered email', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'dato@example.com']);

    Livewire::test(ForgotPassword::class)
        ->set('email', 'dato@example.com')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('an unknown email gets the same answer and no mail', function () {
    Notification::fake();

    Livewire::test(ForgotPassword::class)
        ->set('email', 'nobody@example.com')
        ->call('sendLink')
        ->assertSet('sent', true);

    Notification::assertNothingSent();
});

test('the email is validated when requesting a link', function () {
    Livewire::test(ForgotPassword::class)->set('email', 'nope')->call('sendLink')->assertHasErrors('email');
});

test('requesting links is throttled', function () {
    Notification::fake();
    User::factory()->create(['email' => 'dato@example.com']);

    $component = Livewire::test(ForgotPassword::class)->set('email', 'dato@example.com');
    $component->call('sendLink')->call('sendLink')->call('sendLink')->assertHasNoErrors();
    $component->call('sendLink')->assertHasErrors('email');
});

test('the reset mail is in georgian and links to the reset page', function () {
    $user = User::factory()->create();
    $mail = (new ResetPasswordNotification('abc'))->toMail($user);

    expect($mail->subject)->toBe('პაროლის აღდგენა')
        ->and($mail->actionUrl)->toContain('/reset-password/abc')
        ->and($mail->actionUrl)->toContain(urlencode($user->email));
});

test('a password can be reset with a valid token', function () {
    $user = User::factory()->create(['email' => 'dato@example.com']);
    $token = Password::createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', 'dato@example.com')
        ->set('password', 'brand-new-pass')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('brand-new-pass', $user->fresh()->password))->toBeTrue();
});

test('a reset token can only be used once', function () {
    $user = User::factory()->create(['email' => 'dato@example.com']);
    $token = Password::createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])->set('email', 'dato@example.com')->set('password', 'brand-new-pass')->call('resetPassword');
    Livewire::test(ResetPassword::class, ['token' => $token])->set('email', 'dato@example.com')->set('password', 'another-pass-1')->call('resetPassword')->assertHasErrors('email');

    expect(Hash::check('brand-new-pass', $user->fresh()->password))->toBeTrue();
});

test('an invalid token is rejected', function () {
    $user = User::factory()->create(['email' => 'dato@example.com']);

    Livewire::test(ResetPassword::class, ['token' => 'bogus'])
        ->set('email', 'dato@example.com')
        ->set('password', 'brand-new-pass')
        ->call('resetPassword')
        ->assertHasErrors('email');

    expect(Hash::check('brand-new-pass', $user->fresh()->password))->toBeFalse();
});

test('an expired token is rejected', function () {
    $user = User::factory()->create(['email' => 'dato@example.com']);
    $token = Password::createToken($user);

    $this->travel(61)->minutes();

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', 'dato@example.com')
        ->set('password', 'brand-new-pass')
        ->call('resetPassword')
        ->assertHasErrors('email');
});

test('the new password must be long enough', function () {
    $user = User::factory()->create(['email' => 'dato@example.com']);

    Livewire::test(ResetPassword::class, ['token' => Password::createToken($user)])
        ->set('email', 'dato@example.com')
        ->set('password', 'short')
        ->call('resetPassword')
        ->assertHasErrors('password');
});

test('wrong reset attempts are throttled', function () {
    User::factory()->create(['email' => 'dato@example.com']);
    $component = Livewire::test(ResetPassword::class, ['token' => 'bogus'])->set('email', 'dato@example.com')->set('password', 'brand-new-pass');

    foreach (range(1, 5) as $ignored) {
        $component->call('resetPassword');
    }

    $component->call('resetPassword');

    expect($component->errors()->first('email'))->toContain('ძალიან ბევრი მცდელობა');
});

test('registering sends a verification mail', function () {
    Notification::fake();

    Livewire::test(Register::class)
        ->set('name', 'Dato')
        ->set('email', 'dato@example.com')
        ->set('password', 'password-123')
        ->call('register')
        ->assertHasNoErrors();

    Notification::assertSentTo(User::where('email', 'dato@example.com')->first(), VerifyEmail::class);
});

test('registration is throttled per ip', function () {
    $component = Livewire::test(Register::class);

    foreach (range(1, 5) as $i) {
        $component->set('name', 'User')->set('email', "user{$i}@example.com")->set('password', 'password-123')->call('register');
        auth()->logout();
    }

    $component->set('email', 'extra@example.com')->call('register')->assertHasErrors('email');
    expect(User::where('email', 'extra@example.com')->exists())->toBeFalse();
});

test('a signed link verifies the email', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    $this->actingAs($user)->get($url)->assertRedirect(route('lists.index'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('an unsigned or tampered verification link is rejected', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('the verification mail can be resent and is throttled', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    foreach (range(1, 3) as $ignored) {
        $this->post(route('verification.send'))->assertRedirect();
    }

    $this->post(route('verification.send'))->assertStatus(429);
    Notification::assertSentToTimes($user, VerifyEmail::class, 3);
});

test('unverified users see the banner and verified users do not', function () {
    $this->actingAs(User::factory()->unverified()->create())->get(route('lists.index'))->assertSee('დაადასტურე ელფოსტა');
    $this->actingAs(User::factory()->create())->get(route('lists.index'))->assertDontSee('დაადასტურე ელფოსტა');
});

test('unverified users can still use the app', function () {
    $this->actingAs(User::factory()->unverified()->create())->get(route('lists.index'))->assertOk();
});
