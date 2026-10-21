<?php

namespace App\Support;

use App\Models\ListItem;
use App\Models\Poll;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds the list's activity feed from data that is already stored (items and polls),
 * so no separate history table is needed. Deleted items disappear from the feed with them.
 */
class ListActivity
{
    public const LIMIT = 40;

    /**
     * @param  Collection<int, ListItem>  $items  Loaded with adder, claimer and buyer.
     * @param  Collection<int, Poll>  $polls  Loaded with creator and options.votes.user.
     * @return Collection<int, array{at: CarbonInterface, user: User, text: string}>
     */
    public static function events(Collection $items, Collection $polls): Collection
    {
        $events = collect();

        foreach ($items->whereNull('group_name') as $item) {
            self::push($events, $item->created_at, $item->adder, 'დაამატა '.$item->name);
        }

        foreach ($items->whereNotNull('group_name')->groupBy(fn (ListItem $item) => $item->group_name.'|'.$item->added_by) as $ingredients) {
            $first = $ingredients->first();
            self::push($events, $first->created_at, $first->adder, 'დაამატა კერძი „'.$first->group_name.'“');
        }

        foreach ($items as $item) {
            if ($item->claimed_by !== null) {
                self::push($events, $item->updated_at, $item->claimer, 'იღებს '.$item->name);
            }

            if ($item->bought_at !== null) {
                self::push($events, $item->bought_at, $item->buyer, 'იყიდა '.$item->name);
            }
        }

        foreach ($polls as $poll) {
            self::push($events, $poll->created_at, $poll->creator, 'შექმნა გამოკითხვა: '.$poll->question);

            foreach ($poll->options as $option) {
                foreach ($option->votes as $vote) {
                    self::push($events, $vote->updated_at, $vote->user, 'ხმა მისცა: '.$poll->question);
                }
            }
        }

        return $events->sortByDesc(fn (array $event) => $event['at']->getTimestamp())->take(self::LIMIT)->values();
    }

    /**
     * @param  Collection<int, array{at: CarbonInterface, user: User, text: string}>  $events
     */
    private static function push(Collection $events, ?CarbonInterface $at, ?User $user, string $text): void
    {
        if ($at === null || $user === null) {
            return;
        }

        $events->push(['at' => $at, 'user' => $user, 'text' => $text]);
    }
}
