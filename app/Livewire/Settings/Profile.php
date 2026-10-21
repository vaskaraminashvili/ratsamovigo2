<?php

namespace App\Livewire\Settings;

use App\Enums\Theme;
use App\Enums\Unit;
use App\Livewire\Concerns\ValidatesIngredients;
use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('პროფილი')]
class Profile extends Component
{
    use ValidatesIngredients;

    public string $name = '';

    public string $email = '';

    public string $theme = 'system';

    public bool $notifyActivity = true;

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $deletePassword = '';

    public string $savedName = '';

    public ?float $savedQuantity = null;

    public string $savedUnit = 'ცალი';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->theme = $user->theme ?? Theme::System->value;
        $this->notifyActivity = $user->notify_activity;
    }

    public function setTheme(string $theme): void
    {
        $validated = validator(['theme' => $theme], ['theme' => [Rule::enum(Theme::class)]])->validate();

        auth()->user()->update(['theme' => $validated['theme']]);

        $this->theme = $validated['theme'];
    }

    public function updatedNotifyActivity(): void
    {
        auth()->user()->update(['notify_activity' => $this->notifyActivity]);
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required' => 'დაწერე სახელი.',
            'email.required' => 'შეიყვანე ელფოსტა.',
            'email.email' => 'ელფოსტის ფორმატი არასწორია.',
            'email.unique' => 'ეს ელფოსტა უკვე გამოყენებულია.',
        ]);

        $validated['name'] = trim($validated['name']);

        if ($validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $emailChanged = $validated['email'] !== $user->email;

        $user->forceFill($validated)->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        $this->dispatch('toast', message: 'პროფილი შენახულია');
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'string', Password::min(8)],
        ], [
            'currentPassword.required' => 'შეიყვანე მიმდინარე პაროლი.',
            'currentPassword.current_password' => 'მიმდინარე პაროლი არასწორია.',
            'newPassword.required' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
            'newPassword.min' => 'გამოიყენე მინიმუმ 8 სიმბოლო.',
        ]);

        auth()->user()->update(['password' => $this->newPassword]);

        $this->reset('currentPassword', 'newPassword');
        $this->dispatch('toast', message: 'პაროლი შეიცვალა');
    }

    public function addSaved(): void
    {
        $validated = $this->validate([
            'savedName' => ['required', 'string', 'max:120'],
            'savedQuantity' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
            'savedUnit' => [Rule::enum(Unit::class)],
        ], [
            'savedName.required' => 'დაწერე ნივთის სახელი.',
        ]);

        auth()->user()->savedItems()->updateOrCreate(
            ['name_key' => SavedItem::keyFor($validated['savedName'])],
            [
                'name' => trim($validated['savedName']),
                'quantity' => $validated['savedQuantity'],
                'unit' => $validated['savedQuantity'] === null ? null : $validated['savedUnit'],
            ],
        );

        $this->reset('savedName', 'savedQuantity');
        $this->savedUnit = Unit::Piece->value;
        $this->dispatch('saved-added');
    }

    /**
     * @param  array<int, mixed>  $ingredients
     */
    public function saveDish(?int $dishId, string $name, array $ingredients): void
    {
        $dishName = validator(['name' => trim($name)], [
            'name' => ['required', 'string', 'max:120'],
        ], ['name.required' => 'დაწერე კერძის სახელი.'])->validate()['name'];

        $rows = $this->validatedIngredients($ingredients);

        $dish = $dishId === null
            ? auth()->user()->dishes()->create(['name' => $dishName])
            : tap(auth()->user()->dishes()->findOrFail($dishId))->update(['name' => $dishName]);

        $dish->ingredients()->delete();
        $dish->ingredients()->createMany(array_map(fn (array $row) => [
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'unit' => $row['quantity'] === null ? null : $row['unit'],
        ], $rows));

        $this->dispatch('dish-saved');
    }

    public function deleteDish(int $dishId): void
    {
        auth()->user()->dishes()->whereKey($dishId)->delete();
    }

    public function deleteSaved(int $savedItemId): void
    {
        auth()->user()->savedItems()->whereKey($savedItemId)->delete();
    }

    public function deleteAccount(): void
    {
        $user = auth()->user();

        $this->validate([
            'deletePassword' => ['required', 'current_password'],
        ], [
            'deletePassword.required' => 'დასადასტურებლად შეიყვანე პაროლი.',
            'deletePassword.current_password' => 'პაროლი არასწორია.',
        ]);

        $sharedLists = $user->shoppingLists()
            ->where('owner_id', $user->id)
            ->whereHas('members', fn ($query) => $query->where('users.id', '!=', $user->id))
            ->pluck('shopping_lists.name');

        if ($sharedLists->isNotEmpty()) {
            $this->addError('deletePassword', 'ჯერ გადასცე სიების მფლობელობა ან წაშალე ისინი: '.$sharedLists->implode(', ').'.');

            return;
        }

        $this->deleteUser($user);

        $this->redirect(route('home'));
    }

    protected function deleteUser(User $user): void
    {
        Auth::logout();

        $user->shoppingLists()->where('owner_id', $user->id)->get()->each->delete();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();
    }

    public function render(): View
    {
        return view('livewire.settings.profile', [
            'savedItems' => auth()->user()->savedItems()->orderBy('name')->get(),
            'dishes' => auth()->user()->dishes()->with('ingredients')->orderBy('name')->get(),
            'units' => Unit::options(),
            'themes' => [
                'system' => 'სისტემის',
                'night' => 'ღამე',
                'matsoni' => 'მაწონი',
                'tarkhuna' => 'ტარხუნა',
                'churchkhela' => 'ჩურჩხელა',
            ],
        ]);
    }
}
