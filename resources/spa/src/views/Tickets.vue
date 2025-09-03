<template>
    <section class="ticket-list container">
        <h1 class="ticket-list__title">Tickets</h1>

        <div class="ticket-list__controls">
            <input
                v-model="q"
                @input="onSearch"
                class="ticket-list__search"
                type="text"
                placeholder="Search tickets…"
            />
            <select v-model="status" @change="onFilter" class="ticket-list__filter">
                <option value="">All statuses</option>
                <option value="open">open</option>
                <option value="in_progress">in_progress</option>
                <option value="resolved">resolved</option>
                <option value="closed">closed</option>
            </select>
            <button class="ticket-list__new" @click="openNew">New Ticket</button>
        </div>

        <ul class="ticket-list__items">
            <li v-for="t in items" :key="t.id" class="ticket-list__item">
                <div class="ticket-list__subject">
                    {{ t.subject || "(placeholder subject)" }}
                </div>
                <div class="ticket-list__meta">
                    <span class="ticket-list__badge">{{ t.status || "open" }}</span>
                    <span v-if="t.category" class="ticket-list__badge">
            {{ t.category }} ({{ t.confidence ?? 0 }})
          </span>
                    <span v-if="t.note" class="ticket-list__icon" title="Has note">📝</span>
                </div>
                <div class="ticket-list__actions">
                    <router-link :to="`/tickets/${t.id || 'placeholder'}`">Open</router-link>
                    <button @click="classify(t)" :disabled="loadingId === t.id">
                        <span v-if="loadingId === t.id">⏳</span>
                        <span v-else>Classify</span>
                    </button>
                </div>
            </li>
        </ul>

        <div class="ticket-list__empty" v-if="!items.length">
            /tickets — placeholder list (wire API next)
        </div>
    </section>
</template>

<script>
export default {
    name: "Tickets",
    data() {
        return {
            items: [
                // placeholder item so the list renders before API wiring
                { id: "demo-1", subject: "Example ticket", status: "open", note: null },
            ],
            q: "",
            status: "",
            page: 1,
            perPage: 10,
            loadingId: null,
            showNew: false,
        };
    },
    created() {
        this.fetch();
    },
    methods: {
        async fetch() {
            // TODO: replace with API call: GET /api/tickets?q=&status=&page=&per_page=
            // const { data } = await api.get('/tickets', { params: {...} })
            // this.items = data.data ?? data
        },
        onSearch() {
            this.page = 1;
            this.fetch();
        },
        onFilter() {
            this.page = 1;
            this.fetch();
        },
        openNew() {
            // TODO: open "New Ticket" modal/form
            alert("New Ticket modal (to implement)");
        },
        async classify(t) {
            // TODO: POST /api/tickets/{id}/classify then poll GET /api/tickets/{id}
            this.loadingId = t.id;
            setTimeout(() => (this.loadingId = null), 800); // placeholder spinner
        },
    },
};
</script>

<style>
/* BEM-only demo styles (plain CSS) */
.ticket-list__controls { display:flex; gap:.5rem; margin:.75rem 0; }
.ticket-list__items { list-style:none; padding:0; margin:0; }
.ticket-list__item { display:flex; align-items:center; justify-content:space-between; border:1px solid #ddd; padding:.75rem; border-radius:.5rem; margin-bottom:.5rem; }
.ticket-list__subject { font-weight:600; }
.ticket-list__badge { background:#eee; padding:.1rem .4rem; border-radius:.25rem; margin-left:.25rem; }
.ticket-list__icon { margin-left:.25rem; }
.ticket-list__actions button { margin-left:.5rem; }
</style>
