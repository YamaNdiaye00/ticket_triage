<template>
    <section class="ticket-list">
        <!-- Top toolbar: search + filters + create -->
        <div class="ticket-list__toolbar">
            <input
                class="ticket-list__search"
                v-model="q"
                placeholder="Search subject/body…"
                @input="debouncedFetch"
            />
            <select class="ticket-list__select" v-model="status" @change="resetAndFetch">
                <option value="">All status</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
            <select class="ticket-list__select" v-model="category" @change="resetAndFetch">
                <option value="">All categories</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
            <button class="ticket-list__new" @click="showNew = true">New Ticket</button>
        </div>

        <!-- List -->
        <div v-if="loading" class="ticket-list__loading">Loading…</div>
        <ul v-else class="ticket-list__items">
            <li
                v-for="t in items"
                :key="t.id"
                class="ticket-list__item"
                :class="{ 'ticket-list__item--busy': loadingId === t.id }"
            >
                <div class="ticket-list__subject">{{ t.subject }}</div>

                <div class="ticket-list__meta">
                    <span class="ticket-list__badge">{{ t.status }}</span>
                    <span class="ticket-list__badge">{{ t.category || '—' }}</span>
                    <span class="ticket-list__muted">conf: {{ fmtConf(t.confidence) }}</span>
                    <span v-if="t.note" class="ticket-list__note-badge">
                        <img src="../../assets/icons/note.png" alt="Note" class="ticket-list__note-icon"/>
                            <span class="ticket-list__note-text">Note</span>
                        </span>
                    <span v-if="t.explanation" class="ticket-list__info">
                          <img src="../../assets/icons/tooltip.png" alt="Info" class="ticket-list__info-icon"/>
                          <div class="tooltip">
                            <img
                                v-if="t.image"
                                :src="t.image"
                                alt="Ticket related"
                                class="tooltip__image"
                            />
                            <p class="tooltip__text">{{ t.explanation }}</p>
                          </div>
                    </span>
                </div>

                <div class="ticket-list__actions">
                    <router-link class="ticket-list__link" :to="`/tickets/${t.id}`">Open</router-link>
                    <button class="ticket-list__btn" :disabled="loadingId===t.id" @click="classify(t.id)">
                        {{ loadingId === t.id ? 'Classifying…' : 'Classify' }}
                    </button>
                </div>
            </li>
        </ul>

        <!-- Pagination -->
        <div class="ticket-list__pager" v-if="meta && meta.last_page > 1">
            <button :disabled="page<=1" @click="goto(page-1)">Prev</button>
            <span>Page {{ meta.current_page }} / {{ meta.last_page }}</span>
            <button :disabled="page>=meta.last_page" @click="goto(page+1)">Next</button>
        </div>

        <!-- New Ticket modal (no UI libs) -->
        <div v-if="showNew" class="modal">
            <div class="modal__dialog">
                <h3 class="modal__title">New Ticket</h3>
                <form @submit.prevent="create">
                    <label class="modal__label">Subject
                        <input class="modal__input" v-model.trim="form.subject" maxlength="200" required/>
                    </label>
                    <label class="modal__label">Body
                        <textarea class="modal__textarea" v-model.trim="form.body" required></textarea>
                    </label>
                    <div class="modal__actions">
                        <button type="button" @click="showNew=false">Cancel</button>
                        <button type="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<script>
// Options API: data() returns reactive state; methods are component functions.
// We use this.$api (registered in main.js) to call the Laravel API.
export default {
    name: "Tickets",
    data() {
        return {
            q: "", status: "", category: "",
            page: 1, items: [], meta: null,
            loading: false, loadingId: null,
            showNew: false,
            form: {subject: "", body: ""},
            // You can derive these from backend later; hard-coded here for clarity
            statuses: ["new", "open", "pending", "closed"],
            categories: ["Billing", "Technical", "Account", "Other"],
            debounceTimer: null,
        };
    },
    created() {
        // Lifecycle: created() runs once when component is created.
        this.fetch();
    },
    methods: {
        async fetch() {
            this.loading = true;
            try {
                const {data} = await this.$api.get("/tickets", {
                    params: {
                        q: this.q || undefined,
                        status: this.status || undefined,
                        category: this.category || undefined,
                        page: this.page,
                    },
                });
                this.items = data.data; // Laravel paginator items
                this.meta = data.meta; // { current_page, last_page, ... }
            } finally {
                this.loading = false;
            }
        },
        resetAndFetch() {
            this.page = 1;
            this.fetch();
        },
        goto(p) {
            if (!this.meta) return;
            this.page = Math.min(Math.max(1, p), this.meta.last_page);
            this.fetch();
        },
        fmtConf(c) {
            return (typeof c === "number") ? c.toFixed(2) : "—";
        },
        debouncedFetch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.resetAndFetch(), 300);
        },
        async classify(id) {
            this.loadingId = id;
            try {
                await this.$api.post(`/tickets/${id}/classify`);
                // With QUEUE_CONNECTION=sync, job completes instantly; just refetch:
                await this.fetch();
                // If you switch to async queue later, you can add a short poll here
                // (GET /tickets/:id until `explanation`/`classified_at` updates).
            } finally {
                this.loadingId = null;
            }
        },
        async create() {
            if (!this.form.subject || !this.form.body) return;
            const {data} = await this.$api.post("/tickets", {
                subject: this.form.subject, body: this.form.body,
            });
            this.items.unshift(data); // optimistic add to top
            this.showNew = false;
            this.form = {subject: "", body: ""};
        },
    },
};
</script>
