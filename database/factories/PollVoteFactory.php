<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PollVote>
 */
class PollVoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'poll_option_id' => fn (array $attributes) => PollOption::factory()->create(['poll_id' => $attributes['poll_id']])->id,
            'user_id' => User::factory(),
        ];
    }
}
