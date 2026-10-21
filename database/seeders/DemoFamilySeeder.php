<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\Unit;
use App\Models\Dish;
use App\Models\ListItem;
use App\Models\Poll;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A believable family for local and staging use. Every account has the password "password".
 */
class DemoFamilySeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'dato@example.com')->exists()) {
            return;
        }

        $dato = User::factory()->create(['name' => 'დათო', 'email' => 'dato@example.com']);
        $nino = User::factory()->create(['name' => 'ნინო', 'email' => 'nino@example.com']);
        $gio = User::factory()->create(['name' => 'გიო', 'email' => 'gio@example.com']);

        $home = ShoppingList::factory()->create(['owner_id' => $dato->id, 'name' => 'სახლის საყიდლები']);
        $home->members()->attach([$nino->id, $gio->id]);

        $this->homeItems($home, $dato, $nino, $gio);
        $this->khachapuri($home, $nino);
        $this->poll($home, $dato, [$dato, $nino, $gio]);

        $daily = ShoppingList::factory()->daily()->create(['owner_id' => $nino->id, 'name' => 'ყოველდღიური']);
        $daily->members()->attach($dato->id);

        ListItem::factory()->bought()->create(['shopping_list_id' => $daily->id, 'added_by' => $nino->id, 'bought_by' => $dato->id, 'name' => 'კვერცხი', 'quantity' => 10, 'unit' => Unit::Piece]);
        ListItem::factory()->create(['shopping_list_id' => $daily->id, 'added_by' => $dato->id, 'name' => 'მაწონი', 'quantity' => 1, 'unit' => Unit::Piece]);

        $dato->savedItems()->create(['name' => 'პური', 'shopping_list_id' => $home->id]);
        $dato->savedItems()->create(['name' => 'რძე', 'quantity' => 1, 'unit' => Unit::Liter->value, 'shopping_list_id' => $home->id]);
        $dato->savedItems()->create(['name' => 'ყავა']);

        $dish = Dish::factory()->create(['user_id' => $dato->id, 'name' => 'ხინკალი']);
        $dish->ingredients()->createMany([
            ['name' => 'ფქვილი', 'quantity' => 1, 'unit' => Unit::Kilogram->value],
            ['name' => 'საქონლის ხორცი', 'quantity' => 0.5, 'unit' => Unit::Kilogram->value],
            ['name' => 'ხახვი', 'quantity' => 2, 'unit' => Unit::Piece->value],
            ['name' => 'ქინძი', 'quantity' => 1, 'unit' => Unit::Bunch->value],
        ]);
    }

    private function homeItems(ShoppingList $list, User $dato, User $nino, User $gio): void
    {
        $make = fn (array $attributes) => ListItem::factory()->create(['shopping_list_id' => $list->id] + $attributes);

        $make(['added_by' => $dato->id, 'name' => 'რძე', 'quantity' => 2, 'unit' => Unit::Liter, 'priority' => Priority::Urgent]);
        $make(['added_by' => $nino->id, 'name' => 'კარტოფილი', 'quantity' => 2, 'unit' => Unit::Kilogram, 'claimed_by' => $gio->id]);
        $make(['added_by' => $gio->id, 'name' => 'ბანანი', 'quantity' => 1, 'unit' => Unit::Kilogram]);
        $make(['added_by' => $dato->id, 'name' => 'ჩაი', 'priority' => Priority::Maybe]);
        $make(['added_by' => $nino->id, 'name' => 'პური', 'bought_at' => now(), 'bought_by' => $dato->id]);
    }

    private function khachapuri(ShoppingList $list, User $adder): void
    {
        foreach ([['ფქვილი', 1, Unit::Kilogram], ['სულგუნი', 0.5, Unit::Kilogram], ['კვერცხი', 3, Unit::Piece]] as $index => [$name, $quantity, $unit]) {
            ListItem::factory()->create([
                'shopping_list_id' => $list->id,
                'added_by' => $adder->id,
                'name' => $name,
                'group_name' => 'ხაჭაპური',
                'quantity' => $quantity,
                'unit' => $unit,
                'bought_at' => $index === 0 ? now() : null,
                'bought_by' => $index === 0 ? $adder->id : null,
            ]);
        }
    }

    /**
     * @param  list<User>  $voters
     */
    private function poll(ShoppingList $list, User $creator, array $voters): void
    {
        $poll = Poll::factory()->create(['shopping_list_id' => $list->id, 'created_by' => $creator->id, 'question' => 'რა ვივახშმოთ?']);
        $options = collect(['ხინკალი', 'პიცა', 'ხარჩო'])->map(fn (string $label) => $poll->options()->create(['label' => $label]));

        $poll->votes()->create(['user_id' => $voters[0]->id, 'poll_option_id' => $options[0]->id]);
        $poll->votes()->create(['user_id' => $voters[1]->id, 'poll_option_id' => $options[0]->id]);
        $poll->votes()->create(['user_id' => $voters[2]->id, 'poll_option_id' => $options[1]->id]);
    }
}
