@props(['remember' => false])

{{-- Fields for a dish and its ingredient rows. Needs `dishForm()` state in the surrounding Alpine scope. --}}
<div class="space-y-5">
    <div>
        <label class="field-label">კერძის სახელი</label>
        <input class="field" x-model="name" maxlength="120" autocomplete="off" enterkeyhint="next" placeholder="ხინკალი">
    </div>

    <div>
        <p class="field-label">ინგრედიენტები</p>
        <ul class="space-y-2">
            <template x-for="(row, index) in rows" :key="row.key">
                <li class="flex items-center gap-2">
                    <input class="field min-w-0 flex-1" x-model="row.name" maxlength="120" autocomplete="off" enterkeyhint="next" placeholder="ინგრედიენტი" aria-label="ინგრედიენტი">
                    <input class="field !w-16 shrink-0 !px-2 text-center" x-model="row.qty" inputmode="decimal" autocomplete="off" placeholder="–" aria-label="რაოდენობა">
                    <select class="field !w-[5.5rem] shrink-0 !px-2" x-model="row.unit" aria-label="ერთეული">
                        <template x-for="u in units" :key="u.key">
                            <option :value="u.key" x-text="u.key"></option>
                        </template>
                    </select>
                    <button type="button" @click="removeRow(index)" class="grid size-11 shrink-0 place-items-center rounded-full text-ink-400 hover:bg-ink-800" aria-label="ინგრედიენტის წაშლა">
                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </li>
            </template>
        </ul>
        <button type="button" @click="addRow()" class="btn-ghost mt-3 w-full">+ ინგრედიენტის დამატება</button>
    </div>

    @if ($remember)
        <label class="flex items-center gap-3 rounded-2xl border border-ink-700 p-3.5">
            <input type="checkbox" class="size-5 rounded-md border-ink-600 bg-ink-900 accent-primary-400" x-model="remember">
            <span>
                <span class="block font-semibold">კერძის დამახსოვრება</span>
                <span class="block text-[13px] text-ink-400">შემდეგ „შენახულიდან“ სწრაფად დაამატებ.</span>
            </span>
        </label>
    @endif

    {{ $slot }}
</div>
