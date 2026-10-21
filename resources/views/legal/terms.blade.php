<x-layouts::guest title="გამოყენების პირობები">
    <main class="mx-auto max-w-md px-5 pb-16 pt-[max(1.25rem,env(safe-area-inset-top))]">
        <x-app-logo :href="route('home')" />

        <h1 class="mt-10 text-[2rem] font-extrabold leading-[1.15]">გამოყენების პირობები</h1>
        <p class="mt-3 text-ink-300">ბოლო განახლება: {{ now()->format('d.m.Y') }}</p>

        <div class="mt-8 space-y-7 text-[16px] leading-relaxed text-ink-200">
            <section>
                <h2 class="text-lg font-bold text-ink-100">სერვისი</h2>
                <p class="mt-2">„რა წამოვიღო?“ არის საერთო სასყიდლო სია ოჯახისთვის და მეგობრებისთვის. ის უფასოა და როგორც არის, ისე გთავაზობთ.</p>
            </section>
            <section>
                <h2 class="text-lg font-bold text-ink-100">შენი ანგარიში</h2>
                <p class="mt-2">მართალი ელფოსტა მიუთითე და პაროლი არავის გაუზიარო. შენი ანგარიშიდან შესრულებულ მოქმედებებზე შენ ხარ პასუხისმგებელი.</p>
            </section>
            <section>
                <h2 class="text-lg font-bold text-ink-100">მოსაწვევი ბმულები</h2>
                <p class="mt-2">ბმულის გახსნა და შეერთება სიაზე სრულ წვდომას იძლევა: ნივთების დამატება, შეცვლა და წაშლა. ბმული მხოლოდ იმას გაუგზავნე, ვისაც ენდობი. მფლობელს შეუძლია ბმულის განახლება, რომ ძველი აღარ იმუშავოს.</p>
            </section>
            <section>
                <h2 class="text-lg font-bold text-ink-100">რისი გაკეთება არ შეიძლება</h2>
                <p class="mt-2">სერვისის გამოყენება კანონსაწინააღმდეგო მიზნით, სხვისი ანგარიშის გატეხვა, სერვისის გადატვირთვა ავტომატური მოთხოვნებით, სხვისთვის შეურაცხმყოფელი ტექსტის ან სურათის განთავსება.</p>
            </section>
            <section>
                <h2 class="text-lg font-bold text-ink-100">შეზღუდვა</h2>
                <p class="mt-2">ვცდილობთ, სერვისი სწორად და უწყვეტად მუშაობდეს, მაგრამ ამას გარანტიას ვერ ვაძლევთ. სია მნიშვნელოვანი ინფორმაციის ერთადერთ ადგილად არ გამოიყენო. პირობები შეიძლება შეიცვალოს; ცვლილება ამ გვერდზე გამოჩნდება.</p>
            </section>
            <section>
                <h2 class="text-lg font-bold text-ink-100">ანგარიშის დახურვა</h2>
                <p class="mt-2">ანგარიშს ნებისმიერ დროს წაშლი პროფილის გვერდიდან. წესების დარღვევისას ანგარიშს შეგვიძლია შევუზღუდოთ წვდომა.</p>
            </section>
        </div>

        <p class="mt-10"><a href="{{ route('privacy') }}" wire:navigate class="font-semibold text-primary-fg">კონფიდენციალურობის პოლიტიკა</a></p>
    </main>
</x-layouts::guest>
