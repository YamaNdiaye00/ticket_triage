<template>
    <section class="ticket-show" v-if="t">
        <!-- Header -->
        <header class="ticket-show__header">
            <h1 class="ticket-show__subject">{{ t.subject }}</h1>
            <p class="ticket-show__body">{{ t.body }}</p>
        </header>

        <!-- Read-only AI info -->
        <div class="ticket-show__ai">
            <div class="ticket-show__field">
                <span class="ticket-show__label">Explanation</span>
                <div class="ticket-show__value ticket-show__explain">
                    {{ t.explanation || '—' }}
                </div>
            </div>
            <div class="ticket-show__field">
                <span class="ticket-show__label">Confidence</span>
                <span class="ticket-show__value">{{ fmtConf(t.confidence) }}</span>
            </div>
        </div>

        <!-- Editable fields (with live validation) -->
        <div class="ticket-show__edit">
            <!-- Category -->
            <div
                class="ticket-show__field"
                :class="{ 'ticket-show__field--error': !!errors.category }"
            >
                <label class="ticket-show__label" for="category">Category</label>
                <select
                    id="category"
                    class="ticket-show__select"
                    v-model="edit.category"
                    :disabled="busy"
                    @change="onCategoryChange"
                    aria-invalid="true"
                    :aria-errormessage="errors.category ? 'category-error' : null"
                >
                    <option :value="null">—</option>
                    <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                </select>
                <small
                    v-if="errors.category"
                    id="category-error"
                    class="ticket-show__error"
                    role="alert"
                >{{ errors.category }}</small>
            </div>

            <!-- Note -->
            <div
                class="ticket-show__field ticket-show__note"
                :class="{ 'ticket-show__field--error': !!errors.note }"
            >
                <label class="ticket-show__label" for="note">
                    Internal note
                    <span class="ticket-show__counter">
            {{ edit.note.length }} / {{ NOTE_MAX }}
          </span>
                </label>

                <textarea
                    id="note"
                    class="ticket-show__textarea"
                    rows="5"
                    v-model.trim="edit.note"
                    :disabled="busy"
                    @input="validateField('note')"
                    :aria-invalid="!!errors.note"
                    :aria-errormessage="errors.note ? 'note-error' : null"
                    placeholder="Add details for teammates…"
                ></textarea>

                <small
                    v-if="errors.note"
                    id="note-error"
                    class="ticket-show__error"
                    role="alert"
                >{{ errors.note }}</small>

                <div class="ticket-show__actions">
                    <button
                        class="ticket-show__btn"
                        :disabled="busy || !!errors.note || !isNoteDirty"
                        @click="saveNote"
                        title="Save note"
                    >
                        {{ busy && lastAction === 'note' ? 'Saving…' : 'Save note' }}
                    </button>

                    <button
                        class="ticket-show__btn"
                        :disabled="busy || !!errors.category"
                        @click="runClassify"
                        title="Run classification"
                    >
                        {{ busy && lastAction === 'classify' ? 'Classifying…' : 'Run Classification' }}
                    </button>

                    <router-link class="ticket-show__link" to="/tickets">Back to list</router-link>
                </div>
            </div>
        </div>
    </section>

    <div v-else class="ticket-show__loading">Loading…</div>
</template>

<script>
export default {
    name: "TicketShow",
    data() {
        return {
            t: null,
            edit: {category: null, note: ""},
            categories: ["Billing", "Technical", "Account", "Other"],
            busy: false,
            lastAction: null,
            errors: {category: null, note: null},
            NOTE_MAX: 500,
        };
    },
    created() {
        this.load();
    },
    watch: {
        "$route.params.id"() {
            this.load();
        }
    },
    computed: {
        isNoteDirty() {
            return (this.t ? (this.edit.note !== (this.t.note || "")) : false);
        }
    },
    methods: {
        // -------- data I/O --------
        async load() {
            const {data} = await this.$api.get(`/tickets/${this.$route.params.id}`);
            this.t = data;
            this.edit.category = data.category || null;
            this.edit.note = data.note || "";
            this.validateAll();
        },
        async onCategoryChange() {
            // Live-validate first; only PATCH if valid and changed
            this.validateField('category');
            if (this.errors.category) return;
            if ((this.t.category || null) === (this.edit.category || null)) return;

            this.busy = true;
            this.lastAction = "category";
            try {
                await this.$api.patch(`/tickets/${this.t.id}`, {category: this.edit.category});
                await this.load();
            } finally {
                this.busy = false;
                this.lastAction = null;
            }
        },
        async saveNote() {
            this.validateField('note');
            if (this.errors.note) return;
            if (!this.isNoteDirty) return;

            this.busy = true;
            this.lastAction = "note";
            try {
                await this.$api.patch(`/tickets/${this.t.id}`, {note: this.edit.note || null});
                await this.load();
            } finally {
                this.busy = false;
                this.lastAction = null;
            }
        },
        async runClassify() {
            // You can still classify with an empty/unchanged note; category must be valid.
            this.validateField('category');
            if (this.errors.category) return;

            this.busy = true;
            this.lastAction = "classify";
            try {
                await this.$api.post(`/tickets/${this.t.id}/classify`);
                await this.load(); // sync queue updates immediately
            } finally {
                this.busy = false;
                this.lastAction = null;
            }
        },

        // -------- formatting --------
        fmtConf(c) {
            return typeof c === "number" ? c.toFixed(2) : "—";
        },

        // -------- validation (live) --------
        validateAll() {
            this.validateField('category');
            this.validateField('note');
        },
        validateField(field) {
            if (field === 'category') {
                // allowed: null/empty OR one of categories
                const val = this.edit.category;
                if (val === null || val === "" || this.categories.includes(val)) {
                    this.errors.category = null;
                } else {
                    this.errors.category = "Invalid category.";
                }
            }
            if (field === 'note') {
                const val = this.edit.note || "";
                if (val.length > this.NOTE_MAX) {
                    this.errors.note = `Note is too long (max ${this.NOTE_MAX} characters).`;
                } else {
                    this.errors.note = null;
                }
            }
        },
    },
};
</script>
