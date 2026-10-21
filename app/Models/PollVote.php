<?php

namespace App\Models;

use Database\Factories\PollVoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $poll_id
 * @property int $poll_option_id
 * @property int $user_id
 */
#[Fillable(['poll_id', 'poll_option_id', 'user_id'])]
class PollVote extends Model
{
    /** @use HasFactory<PollVoteFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Poll, $this>
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * @return BelongsTo<PollOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
