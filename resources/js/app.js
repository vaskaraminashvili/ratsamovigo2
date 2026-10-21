/**
 * Alpine state for the single list screen. Drafts (new item, edit item) live on the
 * client so the quantity stepper is instant; Livewire is only called on submit.
 */
window.listPage = function ({ units }) {
    const blank = () => ({ id: null, name: '', qty: null, unit: 'ცალი', urgent: false, priority: 'normal', saved: false, group: null, catalog: null, meta: '' });

    return {
        units,
        suggestions: [],
        searchSeq: 0,
        searchTimer: null,
        sheet: null,
        view: 'list',
        addMenu: false,
        showHidden: false,
        pick: [],
        savedTab: 'items',
        openPoll: null,
        going: { to: [], message: '', remember: false },
        openDish: null,
        dishPick: [],
        copied: false,
        draft: blank(),
        edit: blank(),
        settings: { name: '', group: true, behavior: 'strike', fetchPrice: false },

        init() {
            this.$watch('draft.name', (value) => this.onDraftName(value));
        },

        /**
         * Price lookup is opt-in per list (data-fetch-price on the root element) and only starts
         * from 3 characters, after a short pause in typing.
         */
        onDraftName(value) {
            clearTimeout(this.searchTimer);
            this.searchSeq++;

            const typed = value.trim();

            if (this.draft.catalog && this.draft.catalog.name !== typed) {
                this.draft.catalog = null;
            }

            if (this.draft.catalog || typed.length < 3 || this.$root.dataset.fetchPrice !== '1') {
                this.suggestions = [];

                return;
            }

            this.searchTimer = setTimeout(() => this.searchProducts(typed), 400);
        },

        async searchProducts(query) {
            const seq = ++this.searchSeq;

            try {
                const rows = await this.$wire.searchProducts(query);

                if (seq === this.searchSeq) {
                    this.suggestions = Array.isArray(rows) ? rows : [];
                }
            } catch (e) {
                if (seq === this.searchSeq) {
                    this.suggestions = [];
                }
            }
        },

        pickSuggestion(row) {
            this.draft.name = row.name;
            this.draft.catalog = row;
            this.suggestions = [];
        },

        fmt(n) {
            return String(n).replace('.', ',');
        },

        unitMeta(key) {
            return this.units.find((u) => u.key === key) ?? { key, full: key, step: 1, start: 1 };
        },

        unitFull(key) {
            return this.unitMeta(key).full;
        },

        qtyInc(target) {
            const u = this.unitMeta(target.unit);
            target.qty = target.qty === null ? u.start : +(target.qty + u.step).toFixed(2);
        },

        qtyDec(target) {
            if (target.qty === null) {
                return;
            }

            const next = +(target.qty - this.unitMeta(target.unit).step).toFixed(2);
            target.qty = next > 0 ? next : null;
        },

        setUnit(target, key) {
            if (target.unit !== key || target.qty === null) {
                target.unit = key;
                target.qty = this.unitMeta(key).start;
            }
        },

        setQty(target, value) {
            const n = parseFloat(String(value).replace(',', '.'));
            target.qty = Number.isFinite(n) && n > 0 ? n : null;
        },

        async add() {
            const name = this.draft.name.trim();

            if (!name) {
                return;
            }

            const { qty, unit, priority, catalog } = this.draft;
            this.draft = blank();
            this.addMenu = false;

            await this.$wire.addItem(name, qty, unit, priority === 'urgent', priority, catalog);
        },

        matchesDraft(key) {
            const typed = this.draft.name.trim().toLowerCase();

            return typed.length > 0 && key !== typed && key.includes(typed);
        },

        openGoing(saved, memberIds) {
            this.going = { to: [...memberIds], message: saved || 'მაღაზიაში მივდივარ, რამე გინდათ?', remember: false };
            this.sheet = 'going';
        },

        toggleGoing(id) {
            this.going.to = this.going.to.includes(id) ? this.going.to.filter((p) => p !== id) : [...this.going.to, id];
        },

        togglePick(id) {
            this.pick = this.pick.includes(id) ? this.pick.filter((p) => p !== id) : [...this.pick, id];
        },

        async addPicked() {
            const ids = this.pick;
            this.pick = [];
            this.sheet = null;

            await this.$wire.addSavedItems(ids);
        },

        showDish(id, ingredientIds) {
            this.openDish = this.openDish === id ? null : id;
            this.dishPick = this.openDish === null ? [] : [...ingredientIds];
        },

        toggleDishPick(id) {
            this.dishPick = this.dishPick.includes(id) ? this.dishPick.filter((p) => p !== id) : [...this.dishPick, id];
        },

        async addDishPicked() {
            const dish = this.openDish;
            const ids = this.dishPick;
            this.openDish = null;
            this.dishPick = [];
            this.sheet = null;

            await this.$wire.addDishIngredients(dish, ids);
        },

        async reopenNow(id) {
            this.draft = blank();

            await this.$wire.toggleBought(id);
        },

        async addSavedNow(id) {
            this.draft = blank();

            await this.$wire.addSavedItems([id]);
        },

        openItem(item) {
            this.edit = {
                id: item.id,
                name: item.name,
                qty: item.qty,
                unit: item.unit,
                priority: item.priority,
                saved: item.saved,
                group: item.group,
                meta: item.meta ?? '',
            };
            this.sheet = 'item';
        },

        async saveItem() {
            if (!this.edit.name.trim()) {
                return;
            }

            const { id, name, qty, unit, priority, saved } = this.edit;
            this.sheet = null;

            await this.$wire.saveItem(id, name.trim(), qty, unit, priority, saved);
        },

        async deleteItem() {
            const { id } = this.edit;
            this.sheet = null;

            await this.$wire.deleteItem(id);
        },

        openSettings(name, group, behavior, fetchPrice) {
            this.settings = { name, group, behavior, fetchPrice };
            this.sheet = 'settings';
        },

        async saveSettings() {
            const { name, group, behavior, fetchPrice } = this.settings;
            this.sheet = null;

            await this.$wire.saveSettings(name, group, behavior, fetchPrice);
        },

        async copyInvite(url) {
            try {
                await navigator.clipboard.writeText(url);
                this.copied = true;
                setTimeout(() => (this.copied = false), 2000);
            } catch (e) {
                window.prompt('ბმული', url);
            }
        },
    };
};

/**
 * Local state for the dish form (name + ingredient rows). Used in the list "dish" sheet and in
 * the profile editor; the parent decides what to do with the payload.
 */
window.dishForm = function ({ units, dish = null }) {
    let seq = 0;

    const makeRow = (r = {}) => ({
        key: ++seq,
        name: r.name ?? '',
        qty: r.qty === null || r.qty === undefined ? '' : String(r.qty).replace('.', ','),
        unit: r.unit ?? 'ცალი',
    });

    const parseQty = (value) => {
        const n = parseFloat(String(value).replace(',', '.'));

        return Number.isFinite(n) && n > 0 ? n : null;
    };

    return {
        units,
        id: dish?.id ?? null,
        name: dish?.name ?? '',
        remember: false,
        rows: dish?.rows?.length ? dish.rows.map(makeRow) : [makeRow(), makeRow()],

        load(dish) {
            this.id = dish.id;
            this.name = dish.name;
            this.rows = dish.rows.length ? dish.rows.map(makeRow) : [makeRow()];
        },

        clear() {
            this.id = null;
            this.name = '';
            this.remember = false;
            this.rows = [makeRow(), makeRow()];
        },

        addRow() {
            this.rows.push(makeRow());
        },

        removeRow(index) {
            this.rows.splice(index, 1);

            if (!this.rows.length) {
                this.addRow();
            }
        },

        payload() {
            return this.rows
                .filter((r) => r.name.trim())
                .map((r) => ({ name: r.name.trim(), qty: parseQty(r.qty), unit: r.unit }));
        },

        get ready() {
            return this.name.trim() !== '' && this.payload().length > 0;
        },
    };
};

/**
 * PWA: register the service worker and drop cached pages when the user logs out,
 * so a shared device never keeps someone else's lists.
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });

    document.addEventListener('submit', (event) => {
        const action = event.target?.getAttribute?.('action') ?? '';

        if (action.endsWith('/logout')) {
            navigator.serviceWorker.controller?.postMessage('clear-pages');
        }
    });
}

/**
 * wire:navigate swaps the <html> element's attributes, which drops the palette.
 * Re-apply the saved theme after every navigation.
 */
document.addEventListener('livewire:navigated', () => window.rwTheme?.apply());
