# Feature Ideas

Backlog of ideas, not commitments. Effort is a rough guess.

## Top picks

Build these first. They reuse existing models and give Pro users a clear reason to pay.

1. Dish → list in one tap (#1)
2. Dish ingredient check on add (#13)
3. Weekly meal planner (#2)
4. Recurring items (#3)

## High value, small effort

1. **Dish → list in one tap.** Add all of a dish's ingredients to a list, skipping items already on it. Uses `Dish`, `DishIngredient`, `ListItem`.
2. **Weekly meal planner.** Assign dishes to days, then generate one combined shopping list. Good Pro feature.
3. **Recurring items.** Mark an item weekly or monthly (milk, bread) so it comes back after being bought. `SavedItem` is likely the base.
4. **Aisle or category sorting.** Group items by store section so people shop in order.

## Medium effort

5. **Price history and budget.** Price trend per item, list total, "cheapest last time at X". Builds on the existing price suggestions.
6. **Multiple stores per list.** Tag items by store and filter by it on arrival.
7. **Location-based reminder.** Remind about the list near a saved store. Needs a PWA with geolocation permission.
8. **Barcode scan.** Scan a product to add it. `ProductSearch` could match barcodes.

## Pro and growth

9. **Spending stats.** Monthly spend per list or category, as a simple chart.
10. **Share via link or QR code.** `JoinListController` suggests invites already exist. Add a QR code for the join link.
11. **Receipt photo import.** Photograph a receipt to mark items bought and log prices. Needs OCR. Highest cost and risk.
12. **Item notes and photos.** For example "the blue-cap brand".

## Added later

13. **Dish ingredient check on add.** When the user adds a dish to a list, show a pop-up with all of the dish's ingredients. Ingredients already on the list are shown as present. The user keeps or removes what they are missing, then confirms, and only the chosen ingredients are added.
    - Example: adding "mushrooms with cheese" lists mushrooms, cheese, butter, garlic. The user already has cheese and garlic, so they untick those and add the rest.
    - Needs: the dish's ingredient list (`Dish` → `DishIngredient`) and the list's current items, so the pop-up can tell which ingredients are already on the list.
    - Overlaps with idea #1. This is the interactive version, so build them together.
