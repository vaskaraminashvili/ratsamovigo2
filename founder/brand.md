# Brand and Pro plan: რა წამოვიღო (ratsamovigo)

Status: draft. The `founder/` brief files (idea, marketing, competitors, panel) did not exist, so the positioning below is inferred from the code, IDEAS.md and the UI. Confirm it, or run `/founder-marketing` and `/founder-pricing` to firm it up.

## 1. What the project is today

A Georgian-language shared shopping-list app (Laravel + Livewire, Georgian UI, 4 themes + glass/night). Households share a list, add items, mark bought, save dishes whose ingredients go onto a list.

Pro already exists as a flag (`users.is_pro`, set by the `SetUserPro` artisan command) but **there is no payment system**. Nothing can be bought yet. This is the first blocker to profit.

### What is gated today (`ProLimits`, Livewire actions)
| Free | Pro |
|---|---|
| 1 list | Unlimited lists |
| 2 members per list | More members (owner Pro lifts the cap) |
| Themes: system, night | All themes |
| Strike-through bought only | Other bought behaviours, "reopen bought" |
| No saved dishes (`saveDish` locked) | Saved dishes, ingredients |
| No remembered items / best-price fetch / priority grouping | All three |

### Assessment
- Free is generous enough to hook a couple (1 list, 2 people) and the cap hits exactly when a family of 3+ shares. That is a good natural upgrade moment.
- Themes are a weak reason to pay on their own. Dishes and recurring/planning are the strong ones: they save weekly effort.
- The ideas already in IDEAS.md (dish to list, ingredient check, meal planner, recurring items) are the right Pro core. Build them first and keep them Pro.

## 2. Positioning (inferred, to confirm)

For Georgian households who plan meals and shop together, **რა წამოვიღო is the shared list that remembers what your family buys and cooks**, so nobody asks "what do we need?" again. Competitors are generic apps (Google Keep, AnyList, Bring!, WhatsApp/Viber messages) with no Georgian-first product, Georgian product names or local price data. Verify with `/founder-competitors`.

## 3. Pro offer (the part that makes money)

**Name: Pro.** One plan, kept simple.

**What Pro sells**: "Plan the week once, shop in one tap." Core bundle:
1. Saved dishes + one-tap "add to list" with ingredient check (ideas #1, #13), skipping what is already there.
2. Weekly meal planner that generates one combined list (#2).
3. Recurring items (#3).
4. Unlimited lists and bigger households.
5. Price history / best price (#5, existing price suggestions) and spending stats (#9) as the second wave.
6. All themes (a bonus, not the sales line).

**Price (starting hypothesis, test it)**: around 5 GEL/month or 39 GEL/year (about 35 percent off, push yearly). Check willingness to pay with `/founder-pricing` and 5 to 10 real users before fixing it.

**Free trial**: 14 days of Pro for every new user, no card. Today the free tier gives no taste of dishes. A trial lets people save a few dishes and plan one week, and losing that at day 15 is the conversion lever. On downgrade keep their data read-only, never delete it.

**Upgrade moments** (put the paywall exactly here, with the benefit, not a lock):
- Inviting a third member to a full list ("ეს სია სავსეა" already exists, add a Pro button).
- Tapping "Dishes" / "Add dish".
- Creating a second list.
- Week 2: "you bought milk 3 weeks in a row. Make it recurring? (Pro)".

## 4. Making it chargeable (engineering to-do, in order)

1. **Payments**: Stripe is a poor fit for Georgian cards; consider a local processor (Bank of Georgia iPay, TBC Pay, Flitt) for GEL cards, Stripe/Paddle later for the diaspora. Add subscription data (plan, status, renews_at, provider ref) and a webhook that sets Pro. Replace the manual `SetUserPro` flow. Adding a payment dependency needs your approval, so pick the processor with the user first.
2. **Pro-until date**: swap the boolean for `pro_until` (keep `isPro()` as the single check, so every gate keeps working).
3. **Pricing page + in-app upgrade sheet**, Georgian copy, shown at the moments above.
4. **Instrument**: events for paywall seen, trial started, converted, churned. Without this you cannot tune price.
5. **Referral**: a Pro month for each household that joins and stays active (the invite link already exists in `JoinListController`; add QR, idea #10).
6. **Founder offer** for the first 100 buyers (lifetime or discounted year) to fund launch and prove demand.

## 5. Back-of-envelope economics (hypothesis only)

| Active users | Paying (4 percent) | MRR at 5 GEL |
|---|---|---|
| 1,000 | 40 | 200 GEL |
| 10,000 | 400 | 2,000 GEL |
| 50,000 | 2,000 | 10,000 GEL |

Takeaway: a Georgia-only market caps this at a few thousand paying users. For a real business, plan for (a) low running cost (single server), (b) Georgian diaspora and similar small-language markets (extract strings first), and (c) a family-size price lift once planner and price features ship. Run `/founder-cfo` for real numbers. Conversion rates here are placeholders, not data.

## 6. Name check

Current name: **რა წამოვიღო** ("what should I bring"), handle form **ratsamovigo**. The "2" in the project folder must not leak into the brand.

Strengths: says the job in plain Georgian, memorable as a question, fits the "I'm in the store, what did we need?" moment.
Risks: Georgian script is hard to type and search for abroad; the Latin form is long and odd for non-Georgian speakers.

Candidates considered (all unchecked):
| Candidate | Style | Note |
|---|---|---|
| რა წამოვიღო / Ratsamovigo | Descriptive | Keep as primary |
| ჩემი სია (Chemi Siya) | Descriptive | Generic, hard to own |
| კალათა (Kalata, "basket") | Evocative | Short, easy Latin spelling, strong icon |
| საჭმელი (Sachmeli) | Descriptive | Food-centric, fits dishes |
| Supra Plan | Culture | Fits the meal-planner story, may read as an event app |

Recommendation: keep **რა წამოვიღო**, add a short Latin wordmark **Ratsamovigo**, and consider "Kalata" only if you go abroad.

Checks you must finish yourself (I can only run public searches, a trademark lawyer confirms, and nothing is registered until you register it):
- Trademark: SAKPATENTI (sakpatenti.gov.ge), EUIPO eSearch, USPTO TMSearch. Classes 9 (app software) and 42 (SaaS).
- Domain: ratsamovigo.ge, .com, .app (I did not check).
- Handles: Instagram, TikTok, Facebook, YouTube, X.
- Confusion: compare with competitor names once `founder/competitors.md` exists.

## 7. Voice and promise

**Promise**: "What the family needs, always on one list, always up to date."

**Taglines**
1. ერთი სია, მთელი ოჯახისთვის. ("One list for the whole family.")
2. აღარ დაგავიწყდება. ("You won't forget again.")
3. დაგეგმე კვირა, იყიდე ერთხელ. ("Plan the week, shop once.") Best for Pro.

**Voice**: warm, quick, practical.
- Warm: talk like a family member. Do: "დაგვრჩა რძე?" Don't: formal "მომხმარებელო, გთხოვთ..."
- Quick: one action per sentence. Do: "დაამატე კერძი". Don't: long explanation before the button.
- Practical: say what it saves. Do: "ეს 6 ინგრედიენტი უკვე სიაშია." Don't: superlatives.

**Before/after**: today's lock message "ეს Pro ვერსიაშია" ("This is in Pro"). After: "კერძების შენახვა Pro-შია. სცადე 14 დღე უფასოდ." ("Saving dishes is in Pro. Try 14 days free.") It names the benefit and the way forward.

## 8. The look (matches what already ships)

**Colour** (from `app.css`)
| Role | Hex | Use | Contrast |
|---|---|---|---|
| Ink | `#1A1621` | Dark surfaces, text on light | Mist text on Ink: 15.9:1 |
| Sun (primary) | `#F6C744` | Buttons, the "+", Pro accent | Ink on Sun about 10:1, passes AA and AAA |
| Mist | `#F4F1F8` | Light backgrounds | Ink on Mist: 15.9:1 |

Sun is the Pro colour: use it for the Pro badge and upgrade button. Never put Sun text on white (fails AA); the light themes already use a darker `--p-fg` for that.

**Type**: Bricolage Grotesque (Latin) and Noto Sans Georgian (Georgian). Both are open-licence (SIL OFL) and already self-hosted in `/fonts`. No new font needed.

**Logo brief**: must say "basket + list/check" at a glance; work as a 32px app icon, a one-colour stamp, and in Georgian and Latin lockups. Avoid shopping-cart clichés and any competitor's marks. I can generate concepts with the logo tool if you want them, marked as concepts, not a finished logo.

**First five touchpoints**
1. Landing page (Georgian, hero: tagline 3, screenshot of a planned week).
2. App icon + install prompt.
3. Invite message/QR (the viral loop: "Gio added you to Family list").
4. Upgrade sheet + receipt email ("Welcome to Pro: here is your first week plan").
5. First complaint reply: "ბოდიში, ახლავე გავასწორებთ. თუ გინდა, თანხას დაგიბრუნებთ." Fast, human, refund offered.

## 9. 30-day plan

| Week | Do |
|---|---|
| 1 | Pick payment processor, add `pro_until` + webhook, give 20 beta families Pro via `SetUserPro` |
| 2 | Ship dish-to-list with ingredient check (#1/#13) as the headline Pro feature |
| 3 | Upgrade sheet at the 4 moments, trial, event tracking |
| 4 | Landing page, QR invite, first launch in Georgian parent/food Facebook groups and Viber communities |

Success test: 10 paying households and a trial-to-paid rate above 3 percent before building the planner.

## 10. Not done / needs you
- No trademark, domain or handle checks were run.
- Prices and conversion rates are assumptions, not data.
- Competitor and panel files do not exist yet.
- Next: `/founder-pricing`, `/founder-competitors`, `/founder-ops`, `/founder-launch`.
