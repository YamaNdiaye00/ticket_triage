<template>
    <div class="modal" v-if="open" @click.self="$emit('close')">
        <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="export-title">
            <h2 id="export-title" class="modal__title">Export to CSV</h2>

            <label class="modal__label">Search</label>
            <input class="modal__input" v-model.trim="form.q" placeholder="e.g. refund"/>

            <label class="modal__label">Status</label>
            <select class="modal__input" v-model="form.status">
                <option value="">Any</option>
                <option>new</option>
                <option>open</option>
                <option>pending</option>
                <option>closed</option>
            </select>

            <label class="modal__label">Category</label>
            <select class="modal__input" v-model="form.category">
                <option value="">Any</option>
                <option>Billing</option>
                <option>Technical</option>
                <option>Account</option>
                <option>Other</option>
            </select>

            <div class="modal__actions">
                <button class="modal__btn" @click="$emit('close')">Cancel</button>
                <button class="modal__btn" @click="submit">Export CSV</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ExportCsvModal",
    props: {
        open: {type: Boolean, default: false},
        defaults: {type: Object, default: () => ({})}
    },
    data() {
        return {
            form: {
                q: this.defaults.q || "",
                status: this.defaults.status || "",
                category: this.defaults.category || ""
            }
        };
    },
    methods: {
        submit() {
            this.$emit("export", {
                q: this.form.q || undefined,
                status: this.form.status || undefined,
                category: this.form.category || undefined,
            });
        }
    }
};
</script>
