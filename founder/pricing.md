# Pricing: რა წამოვიღო Pro

**Status: hypothesis.** The skill expects a buyer panel (`founder/panel/answers`) to build a Van Westendorp curve. That panel does not exist, so I did not generate `pricing-curve.md` and did not invent an acceptable range. `founder/numbers.json` also does not exist, so margin and break-even are not computed. Run `/founder-consumer --quick` and `/founder-cfo` to fill these in. Better still, ask 20 real families the four price questions.

## 1. What competitors charge (see `competitors.md`)
- Bring! $8.99/yr, $1.99/mo
- AnyList $9.99/yr, $14.99/yr household (includes meal planning, the closest to your Pro)
- OurGroceries ad removal $0.99 to $17.99 lifetime
- At about 2.7 GEL per USD (approximate): 24 to 40 GEL per year.

Your earlier draft in `brand.md` (5 GEL/mo, 39 GEL/yr) is **high on monthly** (60 GEL/yr equivalent, up to 2.5 times the benchmark) and fine on yearly. Local purchasing power is also lower than the US, and no Georgian competitor price was found.

## 2. Recommended price (to test, not final)
| Plan | Price | Why |
|---|---|---|
| Pro monthly | 3.99 GEL | Entry point, near Bring!'s $1.99 |
| **Pro yearly (push this)** | **29 GEL** (about $11) | Between Bring! and AnyList household; about 40 percent below 12 months at monthly |
| Founder offer, first 100 buyers | 19 GEL for the first year | Funds launch, ends at a stated date. Do not show a fake "was" price |

Pro covers the whole household because the owner's Pro lifts the member cap. Say "one price for the whole family" and compare with AnyList's household plan.

## 3. The ladder
Free (1 list, 2 members) then Pro (unlimited, dishes, planner, recurring). Step-up reason: dishes and the weekly plan, not themes. A "Family+" tier is not justified yet; keep one paid plan.

## 4. Opening offer
14-day trial of full Pro, no card, plus the 19 GEL founder year for the first 100 buyers, ending on a fixed date (set it 30 days after launch). Do not run repeated discounts.

## 5. What to test with real buyers
Two yearly prices inside the plausible range: **19 GEL vs 29 GEL** (then 39 GEL if both convert well). Method: two landing pages or two Facebook ad sets pointing at pre-sale pages with equal traffic; compare checkout starts. Measure trial-to-paid at day 14.

## 6. Verbatim buyer objections
None yet (no panel). Likely objections to confirm in interviews, **not quotes**: "my family uses Viber", "why pay for a list", "will my data be lost if I stop paying".

## 7. Costs not yet known
Payment processor fees, hosting and Facebook ad cost per install are unknown. Do not price below your variable cost; at current scale hosting is small, but verify with `/founder-cfo`.

Next: `/founder-offer`.
