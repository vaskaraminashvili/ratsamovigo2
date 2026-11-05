<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:pro {email : Email of the user} {--revoke : Remove Pro instead of granting it}')]
#[Description('Grant or revoke the Pro tier for a user')]
class SetUserPro extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $user->forceFill(['is_pro' => ! $this->option('revoke')])->save();

        $this->info("{$user->email} is now ".($user->is_pro ? 'Pro' : 'Free').'.');

        return self::SUCCESS;
    }
}
