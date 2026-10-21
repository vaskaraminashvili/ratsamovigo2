<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthMail();

        Model::preventLazyLoading(! $this->app->isProduction());
    }

    /**
     * Georgian wording for the framework's password reset and email verification mails.
     */
    protected function configureAuthMail(): void
    {
        ResetPassword::toMailUsing(function (CanResetPassword $notifiable, string $token): MailMessage {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('პაროლის აღდგენა')
                ->line('მივიღეთ მოთხოვნა შენი პაროლის შესაცვლელად.')
                ->action('პაროლის შეცვლა', $url)
                ->line("ბმული {$minutes} წუთის განმავლობაში მუშაობს.")
                ->line('თუ შენ არ მოგითხოვია, უბრალოდ დაივიწყე ეს წერილი.');
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('ელფოსტის დადასტურება')
                ->line('დააჭირე ღილაკს, რომ დაადასტურო შენი ელფოსტა.')
                ->action('დადასტურება', $url)
                ->line('თუ ანგარიში შენ არ შეგიქმნია, ეს წერილი უგულებელყავი.');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
