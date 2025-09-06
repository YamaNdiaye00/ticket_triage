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
                    :aria-invalid="!!errors.category"
                    :aria-errormessage="errors.category ? 'category-error' : null"
                    @input="validateField('category')"
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
                    :aria-invalid="!!errors.note"
                    :aria-errormessage="errors.note ? 'note-error' : null"
                    placeholder="Add details for teammates…"
                    @input="validateField('note')"
                ></textarea>

                <small
                    v-if="errors.note"
                    id="note-error"
                    class="ticket-show__error"
                    role="alert"
                >{{ errors.note }}</small>

                <div class="ticket-show__actions">
                    <!-- Single SAVE for category + note -->
                    <button
                        class="ticket-show__btn ui-btn"
                        type="button"
                        :disabled="!canSave"
                        @click="saveBoth"
                    >
                        {{ busy ? 'Saving…' : 'Save' }}
                    </button>

                    <!-- Correctly wired classification -->
                    <button
                        class="ticket-show__btn ui-btn"
                        type="button"
                        :disabled="classifying"
                        @click="runClassify"
                    >
                        {{ classifying ? 'Classifying…' : 'Run Classification' }}
                    </button>

                    <router-link class="ticket-show__link" to="/tickets">Back to list</router-link>
                </div>
            </div>
        </div>
    </section>

    <div v-else-if="error" class="ticket-show__error">
        {{ error }}
    </div>

    <div v-else class="ticket-show__loading">
        Loading…
    </div>
</template>

<script>
export default {
    name: "TicketShow",
    data() {
        return {
            t: null,
            error: null,
            edit: {category: null, note: ""},
            categories: ["Billing", "Technical", "Account", "Other"],
            busy: false,
            classifying: false,
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
        // Save is allowed unless a validation fails or we’re mid-request
        canSave() {
            return !this.busy && !this.errors.category && !this.errors.note;
        },
        fmtConf() {
            return c => (typeof c === "number" ? c.toFixed(2) : "—");
        }
    },
    methods: {
        // -------- data I/O --------
        async load() {
            this.error = null;
            this.t = null;
            try {
                const {data} = await this.$api.get(`/tickets/${this.$route.params.id}`);
                this.t = data;
                // Prefill form from server
                this.edit.category = data.category ?? null;
                this.edit.note = data.note || "";
                // Validate current values so Save reflects state immediately
                this.validateAll();
            } catch (err) {
                if (err.response && err.response.status === 404) {
                    this.error = "Ticket not found";
                } else {
                    this.error = "Failed to load ticket. Please try again.";
                }
            }
        },

        // Single save for category + note
        async saveBoth() {
            this.validateAll();
            if (this.errors.category || this.errors.note) return;

            this.busy = true;
            try {
                const payload = {
                    category: this.edit.category, // null allowed
                    note: this.edit.note || null
                };
                const {data} = await this.$api.patch(`/tickets/${this.t.id}`, payload);
                this.t = data;
                // Re-sync form
                this.edit.category = data.category ?? null;
                this.edit.note = data.note || "";
                this.validateAll();
            } finally {
                this.busy = false;
            }
        },

        // Correct classify call + quick refresh (or polling if you prefer)
        async runClassify() {
            this.validateField('category');
            if (this.errors.category) return;

            this.classifying = true;
            try {
                await this.$api.post(`/tickets/${this.t.id}/classify`);
                // quick sync; if jobs are async you can poll until explanation appears
                await this.load();
            } finally {
                this.classifying = false;
            }
        },

        // -------- validation (live) --------
        validateAll() {
            this.validateField('category');
            this.validateField('note');
        },
        validateField(field) {
            if (field === 'category') {
                const val = this.edit.category;
                this.errors.category =
                    (val === null || this.categories.includes(val)) ? null : "Invalid category.";
            }
            if (field === 'note') {
                const val = this.edit.note || "";
                this.errors.note = (val.length > this.NOTE_MAX)
                    ? `Note is too long (max ${this.NOTE_MAX} characters).`
                    : null;
            }
        },
    },
};

</script>
