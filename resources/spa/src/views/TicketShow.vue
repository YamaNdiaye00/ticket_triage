<template>
    <section class="ticket-show container">
        <h1 class="ticket-show__title">Ticket Detail</h1>

        <div v-if="!t" class="ticket-show__empty">
            /tickets/:id — placeholder (load from API next)
        </div>

        <div v-else class="ticket-show__card">
            <h2 class="ticket-show__subject">{{ t.subject }}</h2>
            <p class="ticket-show__body">{{ t.body }}</p>

            <div class="ticket-show__row">
                <label>Status</label>
                <select v-model="t.status">
                    <option>open</option>
                    <option>in_progress</option>
                    <option>resolved</option>
                    <option>closed</option>
                </select>
            </div>

            <div class="ticket-show__row">
                <label>Category</label>
                <select v-model="t.category">
                    <option disabled value="">— select —</option>
                    <option>Billing</option>
                    <option>Bug</option>
                    <option>Feature</option>
                    <option>Account</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="ticket-show__row">
                <label>Note</label>
                <textarea v-model="t.note" rows="3" class="ticket-show__note"></textarea>
            </div>

            <div class="ticket-show__ai" v-if="t.explanation || t.confidence !== null">
                <strong>AI:</strong>
                <div class="ticket-show__explain">
                    {{ t.explanation || "(no explanation yet)" }}
                </div>
                <div class="ticket-show__conf">conf: {{ t.confidence ?? 0 }}</div>
            </div>

            <div class="ticket-show__actions">
                <button @click="runClassify">Run Classification</button>
                <button @click="save" :disabled="saving">{{ saving ? "Saving…" : "Save" }}</button>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "TicketShow",
    props: { id: { type: String, default: "" } },
    data() {
        return {
            t: null,
            saving: false,
        };
    },
    created() {
        this.load();
    },
    methods: {
        async load() {
            // TODO: load via GET /api/tickets/:id
            this.t = {
                id: this.$route.params.id,
                subject: "Example ticket",
                body: "Body placeholder.",
                status: "open",
                category: "",
                note: "",
                explanation: null,
                confidence: null,
            };
        },
        async save() {
            // TODO: PATCH /api/tickets/:id with { status, category, note }
            this.saving = true;
            setTimeout(() => (this.saving = false), 600);
        },
        async runClassify() {
            // TODO: POST /api/tickets/:id/classify then poll GET /api/tickets/:id
            alert("Classify job queued (placeholder)");
        },
    },
};
</script>

<style>
.ticket-show__card { border:1px solid #ddd; border-radius:.5rem; padding:1rem; }
.ticket-show__row { display:flex; gap:.5rem; align-items:center; margin:.5rem 0; }
.ticket-show__row label { width:7rem; }
.ticket-show__actions { display:flex; gap:.5rem; margin-top:1rem; }
.ticket-show__explain { margin-top:.25rem; }
.ticket-show__conf { opacity:.8; font-size:.9em; }
</style>
