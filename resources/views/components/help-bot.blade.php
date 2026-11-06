{{-- FAQ chat on the landing page. No AI and no server calls: typed questions are matched to the closest answer by keyword stems. --}}
@php
    $faqs = [
        ['q' => 'რა ღირს?', 'k' => ['ღირ', 'ფას', 'ფულ', 'უფას', 'გადახდ', 'ლარ', '₾', 'pro', 'პრო'],
            'a' => 'ერთი სია ორი ადამიანისთვის სრულიად უფასოა. Pro თვეში '.\App\Support\ProLimits::PRICE.' ღირს: ულიმიტო სია და წევრი, ყველა თემა, დაჯგუფება და ფასების ძებნა. Pro-ს გადახდა მალე ჩაირთვება.'],
        ['q' => 'როგორ დავამატო ოჯახი?', 'k' => ['ოჯახ', 'დავამატ', 'მოსაწვ', 'ბმულ', 'წევრ', 'შეუერთ', 'გავუზიარ', 'ცოლ', 'ქმარ', 'დედ'],
            'a' => 'სიაში დააჭირე „გაზიარებას“ და მოსაწვევი ბმული ოჯახის ჩატში ჩააგდე. ვინც გახსნის და დარეგისტრირდება, მაშინვე სიას შეუერთდება. უფასო ვერსიაში სიაზე 2 წევრია, Pro-ში ულიმიტო.'],
        ['q' => 'აპის გადმოწერა მჭირდება?', 'k' => ['გადმოწ', 'აპ', 'ინსტალ', 'ტელეფონ', 'android', 'iphone', 'ანდროიდ', 'აიფონ', 'store'],
            'a' => 'არა. რა წამოვიღო ტელეფონის ბრაუზერში მუშაობს, არაფრის დაყენება არ არის საჭირო. უბრალოდ დარეგისტრირდი და სია შექმენი.'],
        ['q' => 'სხვები მაშინვე ხედავენ?', 'k' => ['ხედავ', 'მაშინვე', 'ცოცხ', 'განახლ', 'სინქრ', 'რეალურ'],
            'a' => 'კი. როცა ვინმე ნივთს ამატებს ან მონიშნავს, დანარჩენები ამას რამდენიმე წამში ხედავენ, გვერდის განახლება არ სჭირდება.'],
        ['q' => 'რას ნიშნავს „ვიყიდი“?', 'k' => ['ვიყიდი', 'ორჯერ', 'ორმა', 'ერთი და იგივე'],
            'a' => '„ვიყიდი“-თი ნივთს შენზე აიღებ. დანარჩენები ხედავენ, რომ ამას შენ ყიდულობ, და ერთი და იგივე ორჯერ აღარ იყიდება.'],
        ['q' => 'როგორ გავაგებინო, რომ მაღაზიაში მივდივარ?', 'k' => ['მაღაზი', 'მივდივ', 'შეტყობინ', 'გავაგებ', 'ნოტიფ'],
            'a' => 'სიაში დააჭირე „მაღაზიაში მიდიხარ?“, აირჩიე ვის გაეგზავნოს და დაწერე მოკლე ტექსტი. ოჯახი შეტყობინებას აპში და ელფოსტაზე მიიღებს და მოასწრებს, რაც აკლია, დაამატოს.'],
        ['q' => 'ჩემი მონაცემები დაცულია?', 'k' => ['მონაცემ', 'დაც', 'კონფიდ', 'წაშლ', 'ანგარიშ', 'უსაფრ'],
            'a' => 'შენს სიას მხოლოდ მისი წევრები ხედავენ. ანგარიშის წაშლა ნებისმიერ დროს შეგიძლია პროფილიდან. დეტალები კონფიდენციალურობის გვერდზეა, ქვემოთ.'],
    ];
    $contact = config('mail.from.address');
@endphp

<div x-data="{
        open: false,
        draft: '',
        faqs: @js($faqs),
        log: [{ from: 'bot', text: 'გამარჯობა! რა გაინტერესებს რა წამოვიღოზე? აირჩიე კითხვა ან დაწერე.' }],
        ask(index) {
            const faq = this.faqs[index];
            this.say(faq.q, faq.a);
        },
        send() {
            const text = this.draft.trim().slice(0, 200);
            if (! text) return;
            this.draft = '';
            this.say(text, this.answer(text));
        },
        answer(text) {
            const typed = text.toLowerCase();
            let best = null, bestHits = 0;
            this.faqs.forEach((faq) => {
                const hits = faq.k.filter((stem) => typed.includes(stem)).length;
                if (hits > bestHits) { best = faq; bestHits = hits; }
            });
            return best ? best.a : @js('ამაზე პასუხი ჯერ არ ვიცი. აირჩიე ერთ-ერთი კითხვა ქვემოთ ან მოგვწერე: '.$contact);
        },
        say(question, reply) {
            this.log.push({ from: 'me', text: question }, { from: 'bot', text: reply });
            this.$nextTick(() => this.$refs.log.scrollTop = this.$refs.log.scrollHeight);
        },
        toggle() {
            this.open = ! this.open;
            if (this.open) this.$nextTick(() => this.$refs.input.focus());
            else this.$refs.launcher.focus();
        },
     }"
     @keydown.escape.window="open && toggle()"
     class="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-30 flex flex-col items-end gap-3">

    <section x-show="open" x-cloak x-transition.opacity.duration.200ms role="dialog" aria-label="კითხვები და პასუხები"
             class="flex max-h-[min(34rem,80dvh)] w-[min(22rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-3xl border border-ink-700 bg-ink-900 shadow-2xl">
        <header class="flex items-center justify-between border-b border-ink-800 px-4 py-3">
            <p class="font-semibold text-ink-100">კითხვა გაქვს?</p>
            <button type="button" @click="toggle()" class="grid size-11 place-items-center rounded-full text-ink-400 hover:text-ink-100" aria-label="დახურვა">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </header>

        <div x-ref="log" class="flex-1 space-y-2 overflow-y-auto px-4 py-3" aria-live="polite">
            <template x-for="(line, i) in log" :key="i">
                <p x-text="line.text" class="max-w-[85%] rounded-2xl px-3 py-2 text-sm leading-relaxed"
                   :class="line.from === 'me' ? 'ml-auto bg-primary-400 text-ink-950' : 'bg-ink-800 text-ink-100'"></p>
            </template>
        </div>

        <div class="flex gap-2 overflow-x-auto border-t border-ink-800 px-4 py-2">
            <template x-for="(faq, i) in faqs" :key="i">
                <button type="button" @click="ask(i)" x-text="faq.q"
                        class="min-h-11 shrink-0 rounded-full border border-ink-700 px-3 text-sm text-ink-200 hover:border-primary-line"></button>
            </template>
        </div>

        <form @submit.prevent="send()" class="flex gap-2 border-t border-ink-800 p-3">
            <label for="help-bot-input" class="sr-only">შენი კითხვა</label>
            <input id="help-bot-input" x-ref="input" x-model="draft" type="text" maxlength="200" enterkeyhint="send" autocomplete="off"
                   placeholder="დაწერე კითხვა…" class="min-h-11 flex-1 rounded-full border border-ink-700 bg-ink-850 px-4 text-sm text-ink-100 placeholder:text-ink-500">
            <button type="submit" class="grid size-11 place-items-center rounded-full bg-primary-400 text-ink-950" aria-label="გაგზავნა">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
            </button>
        </form>
    </section>

    <button type="button" x-ref="launcher" @click="toggle()" :aria-expanded="open" aria-label="კითხვები და პასუხები"
            class="grid size-14 place-items-center rounded-full bg-primary-400 text-ink-950 shadow-xl">
        <svg x-show="! open" class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.969 5.969 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
        <svg x-show="open" x-cloak class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
</div>
