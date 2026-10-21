<main class="px-5" x-data="{ tab: ['#saved', '#dishes'].includes(location.hash) ? location.hash.slice(1) : 'account', theme: window.rwTheme ? window.rwTheme.get() : @js($theme) }">
    <a href="{{ route('lists.index') }}" wire:navigate class="-ml-2 grid size-11 place-items-center rounded-full text-ink-300 hover:bg-ink-800" aria-label="ჩემს სიებზე დაბრუნება">
        <svg class="size-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    </a>

    <section class="mt-2 flex items-center gap-4">
        <span class="grid size-16 shrink-0 place-items-center rounded-full text-2xl font-bold text-on {{ auth()->user()->avatarColorClass() }}">{{ auth()->user()->initials() }}</span>
        <div class="min-w-0">
            <h1 class="truncate text-2xl font-extrabold leading-tight">{{ auth()->user()->name }}</h1>
            <p class="truncate text-ink-400">{{ auth()->user()->email }}</p>
        </div>
    </section>

    <div class="mt-7 grid grid-cols-3 rounded-full border border-ink-700 p-1 text-[15px] font-semibold" role="tablist">
        @foreach (['account' => 'პროფილი', 'saved' => 'ნივთები', 'dishes' => 'კერძები'] as $key => $label)
            <button type="button" role="tab" @click="tab = @js($key); history.replaceState(null, '', @js('#'.$key))" :aria-selected="(tab === @js($key)).toString()"
                    class="rounded-full px-3 py-2 transition" :class="tab === @js($key) ? 'bg-primary-400 text-on' : 'text-ink-300'">{{ $label }}</button>
        @endforeach
    </div>

    <div x-show="tab === 'saved'" x-cloak role="tabpanel">
        <section class="mt-7" aria-labelledby="saved-h">
            <h2 id="saved-h" class="text-lg font-bold">შენახული ნივთები</h2>
            <p class="mt-1 text-[14px] text-ink-300">ნივთები, რომლებსაც ხშირად ყიდულობ. სიაში სწრაფად დაამატებ.</p>

            <form wire:submit="addSaved" class="mt-4 space-y-3" novalidate
                  x-data="{ target: { qty: null, unit: @js($savedUnit) }, units: @js($units),
                            fmt(n) { return String(n).replace('.', ','); },
                            unitMeta(key) { return this.units.find((u) => u.key === key) ?? { key, full: key, step: 1, start: 1 }; },
                            unitFull(key) { return this.unitMeta(key).full; },
                            qtyInc(t) { const u = this.unitMeta(t.unit); t.qty = t.qty === null ? u.start : +(t.qty + u.step).toFixed(2); $wire.savedQuantity = t.qty; },
                            qtyDec(t) { if (t.qty === null) return; const n = +(t.qty - this.unitMeta(t.unit).step).toFixed(2); t.qty = n > 0 ? n : null; $wire.savedQuantity = t.qty; },
                            setUnit(t, key) { if (t.unit !== key || t.qty === null) { t.unit = key; t.qty = this.unitMeta(key).start; } $wire.savedUnit = t.unit; $wire.savedQuantity = t.qty; },
                            setQty(t, v) { const n = parseFloat(String(v).replace(',', '.')); t.qty = Number.isFinite(n) && n > 0 ? n : null; $wire.savedQuantity = t.qty; } }"
                  x-on:saved-added.window="target = { qty: null, unit: 'ცალი' }">
                <x-field label="სახელი" name="savedName" placeholder="რძე" autocomplete="off" />
                <x-qty-picker target="target" />
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="addSaved">შენახვა</button>
            </form>

            @if ($savedItems->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-ink-700 p-6 text-center">
                    <p class="font-semibold">შენახული ნივთები ჯერ არ გაქვს</p>
                </div>
            @else
                <ul class="mt-6 divide-y divide-ink-800">
                    @foreach ($savedItems as $savedItem)
                        <li wire:key="saved-{{ $savedItem->id }}" class="flex items-center gap-3 py-3">
                            <span class="min-w-0 flex-1 truncate text-[17px] font-medium">{{ $savedItem->name }}</span>
                            @if ($savedItem->quantity !== null)
                                <span class="shrink-0 rounded-md bg-ink-800 px-1.5 py-0.5 text-[13px] font-semibold tabular-nums">{{ str_replace('.', ',', (string) $savedItem->quantity) }} {{ $savedItem->unit }}</span>
                            @endif
                            <button type="button" wire:click="deleteSaved({{ $savedItem->id }})" class="grid size-11 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-urgent-fg" aria-label="{{ $savedItem->name }} წაშლა">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div x-show="tab === 'dishes'" x-cloak role="tabpanel">
        <section class="mt-7" aria-labelledby="dishes-h"
                 x-data="dishForm({ units: @js($units) })" x-on:dish-saved.window="clear()">
            <h2 id="dishes-h" class="text-lg font-bold">შენახული კერძები</h2>
            <p class="mt-1 text-[14px] text-ink-300">კერძი ინგრედიენტებით. სიაში ერთი შეხებით დაამატებ.</p>

            <form class="mt-4" @submit.prevent="if (!ready) return; $wire.saveDish(id, name.trim(), payload())">
                <p x-show="id !== null" x-cloak class="mb-3 text-[14px] font-semibold text-primary-fg">რედაქტირება</p>
                <x-dish-form>
                    @error('ingredients') <p class="field-error">{{ $message }}</p> @enderror
                    <div class="flex gap-3">
                        <button type="button" x-show="id !== null" x-cloak @click="clear()" class="btn-ghost flex-1">გაუქმება</button>
                        <button type="submit" class="btn-primary flex-1 disabled:opacity-40" :disabled="!ready" x-text="id === null ? 'კერძის შენახვა' : 'განახლება'"></button>
                    </div>
                </x-dish-form>
            </form>

            @if ($dishes->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-ink-700 p-6 text-center">
                    <p class="font-semibold">შენახული კერძები ჯერ არ გაქვს</p>
                </div>
            @else
                <ul class="mt-6 divide-y divide-ink-800">
                    @foreach ($dishes as $dish)
                        <li wire:key="dish-{{ $dish->id }}" class="py-3">
                            <div class="flex items-center gap-3">
                                <p class="min-w-0 flex-1 truncate text-[17px] font-semibold">{{ $dish->name }}</p>
                                <button type="button" class="rounded-full px-3 py-2 text-[14px] font-medium text-primary-fg hover:bg-ink-800"
                                        @click="load(@js(['id' => $dish->id, 'name' => $dish->name, 'rows' => $dish->ingredients->map(fn ($i) => ['name' => $i->name, 'qty' => $i->quantity, 'unit' => $i->unit ?? 'ცალი'])->all()])); window.scrollTo({ top: 0, behavior: 'smooth' })">რედაქტირება</button>
                                <button type="button" wire:click="deleteDish({{ $dish->id }})" wire:confirm="წაიშალოს „{{ $dish->name }}“?" class="grid size-11 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800 hover:text-urgent-fg" aria-label="{{ $dish->name }} წაშლა">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>
                            <p class="mt-1 truncate text-[14px] text-ink-400">{{ $dish->ingredients->pluck('name')->implode(', ') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <div x-show="tab === 'account'" role="tabpanel">
    {{-- Theme --}}
    <section class="mt-9" aria-labelledby="theme-h">
        <h2 id="theme-h" class="text-lg font-bold">თემა</h2>
        <div class="mt-3 grid grid-cols-2 gap-3">
            @foreach ($themes as $value => $label)
                <button type="button" wire:key="theme-{{ $value }}"
                        @click="theme = @js($value); window.rwTheme && window.rwTheme.set(@js($value)); $wire.setTheme(@js($value))"
                        :aria-pressed="(theme === @js($value)).toString()"
                        class="rounded-2xl border p-2.5 text-left transition"
                        :class="theme === @js($value) ? 'border-primary-line bg-primary-400/10' : 'border-ink-700'">
                    @if ($value === 'system')
                        <span class="relative block h-14 overflow-hidden rounded-xl">
                            <span data-palette="night" class="absolute inset-0 bg-ink-950 [clip-path:polygon(0_0,100%_0,0_100%)]"></span>
                            <span data-palette="matsoni" class="absolute inset-0 bg-ink-950 [clip-path:polygon(100%_0,100%_100%,0_100%)]"></span>
                        </span>
                    @else
                        <span data-palette="{{ $value }}" class="block h-14 rounded-xl bg-ink-950 p-2">
                            <span class="block h-2 w-2/3 rounded-full bg-ink-100"></span>
                            <span class="mt-1.5 block h-2 w-1/2 rounded-full bg-ink-500"></span>
                            <span class="mt-1.5 flex gap-1.5"><span class="size-2.5 rounded-full bg-primary-400"></span><span class="size-2.5 rounded-full bg-urgent-400"></span></span>
                        </span>
                    @endif
                    <span class="mt-2 block px-0.5 text-[14px] font-semibold">{{ $label }}</span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Notifications --}}
    <section class="mt-10" aria-labelledby="notify-h">
        <h2 id="notify-h" class="text-lg font-bold">შეტყობინებები</h2>
        <label class="mt-3 flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
            <input type="checkbox" wire:model.live="notifyActivity" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400">
            <span>
                <span class="block font-semibold">აქტივობის შეტყობინებები</span>
                <span class="block text-[13px] text-ink-400">ახალი წევრი, სასწრაფო ნივთი, გამოკითხვა. „მაღაზიაში მივდივარ“ ყოველთვის მოგივა.</span>
            </span>
        </label>
    </section>

    {{-- Account --}}
    <section class="mt-10" aria-labelledby="account-h">
        <h2 id="account-h" class="text-lg font-bold">ანგარიში</h2>
        <form wire:submit="saveProfile" class="mt-3 space-y-5" novalidate>
            <x-field label="სახელი" name="name" autocomplete="name" />
            <x-field label="ელფოსტა" name="email" type="email" autocomplete="email" inputmode="email" />
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="saveProfile">შენახვა</button>
        </form>
    </section>

    <section class="mt-10" aria-labelledby="password-h">
        <h2 id="password-h" class="text-lg font-bold">პაროლის შეცვლა</h2>
        <form wire:submit="changePassword" class="mt-3 space-y-5" novalidate>
            <x-password-field label="მიმდინარე პაროლი" name="currentPassword" autocomplete="current-password" />
            <x-password-field label="ახალი პაროლი" name="newPassword" autocomplete="new-password" hint="მინიმუმ 8 სიმბოლო." />
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="changePassword">პაროლის შეცვლა</button>
        </form>
    </section>

    <section class="mt-10 space-y-3" aria-labelledby="session-h">
        <h2 id="session-h" class="text-lg font-bold">სესია</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost w-full">გასვლა</button>
        </form>
    </section>

    <section class="mt-10 rounded-3xl border border-urgent-400/40 p-4" aria-labelledby="delete-h" x-data="{ open: false }">
        <h2 id="delete-h" class="text-lg font-bold text-urgent-fg">ანგარიშის წაშლა</h2>
        <p class="mt-1 text-[14px] text-ink-300">შენი ანგარიში და შენი ერთპიროვნული სიები სამუდამოდ წაიშლება.</p>
        <button type="button" x-show="!open" @click="open = true" class="btn-danger mt-4 w-full">ანგარიშის წაშლა</button>
        <form x-show="open" x-cloak wire:submit="deleteAccount" class="mt-4 space-y-4" novalidate>
            <x-password-field label="დასადასტურებლად შეიყვანე პაროლი" name="deletePassword" autocomplete="current-password" />
            <div class="flex gap-3">
                <button type="button" @click="open = false" class="btn-ghost flex-1">გაუქმება</button>
                <button type="submit" class="btn-danger flex-1" wire:loading.attr="disabled" wire:target="deleteAccount">სამუდამოდ წაშლა</button>
            </div>
        </form>
    </section>
    </div>
</main>
