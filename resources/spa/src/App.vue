<template>
    <div class="app">
        <header class="app-header">
            <h1 class="app-header__title">Smart Ticket Triage</h1>
            <nav class="app-header__nav" aria-label="Primary">
                <router-link class="app-header__link" to="/dashboard">Dashboard</router-link>
                <router-link class="app-header__link" to="/tickets">Tickets</router-link>
                <button class="app-header__btn" @click="showExport = true">Export CSV</button>
            </nav>
        </header>

        <router-view/>

        <!-- Export Modal -->
        <ExportCsvModal
            :open="showExport"
            :defaults="$route.query"
            @export="onExport"
            @close="showExport = false"
        />
    </div>
</template>

<script>

import ExportCsvModal from "./components/ExportCsvModal.vue";
import {exportTicketsCsv} from "../utils/exportCsv";

export default {
    name: "App",
    components: {ExportCsvModal},
    data() {
        return {showExport: false};
    },
    methods: {
        async onExport(filters) {
            try {
                await exportTicketsCsv(this.$api, filters); // uses your global Axios
            } finally {
                this.showExport = false;
            }
        },
    },
};
</script>
