<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Models\ShoppingList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JoinListController extends Controller
{
    /**
     * Invite landing page. Guests get the token remembered so register/login can join them afterwards.
     */
    public function show(Request $request, string $token): View
    {
        $list = ShoppingList::query()->where('invite_token', $token)->firstOrFail();

        if (! $request->user()) {
            $request->session()->put('invite_token', $token);
        }

        $items = $list->items()->whereNull('bought_at');

        $members = $list->members()->get();

        return view('join', [
            'list' => $list->load('owner'),
            'members' => $members,
            'isMember' => $request->user() !== null && $members->contains('id', $request->user()->id),
            'remaining' => (clone $items)->count(),
            'urgent' => (clone $items)->where('priority', Priority::Urgent)->count(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $list = ShoppingList::query()->where('invite_token', $token)->firstOrFail();

        $changes = $list->members()->syncWithoutDetaching([$request->user()->id]);

        if ($changes['attached'] !== []) {
            $list->notifyMembers($request->user(), "{$request->user()->name} შეუერთდა სიას");
        }

        return redirect()->route('lists.index')->with('status', "შეუერთდი სიას „{$list->name}“.");
    }
}
