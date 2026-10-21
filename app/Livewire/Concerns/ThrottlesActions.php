<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;

trait ThrottlesActions
{
    /**
     * Allow an action only $maxAttempts times per window for the signed-in user.
     * Over the limit the request is answered with HTTP 429.
     */
    protected function throttle(string $action, int $maxAttempts, int $decaySeconds = 60): void
    {
        $key = "action:{$action}:".auth()->id();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429);
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
