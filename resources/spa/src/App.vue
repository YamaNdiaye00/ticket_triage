<template>
    <div class="app">
        <!-- Top bar -->
        <header class="app-header">
            <h1 class="app-header__title">Smart Ticket Triage</h1>

            <nav class="app-header__nav" aria-label="Primary">
                <router-link class="app-header__link" to="/dashboard">Dashboard</router-link>
                <router-link class="app-header__link" to="/tickets">Tickets</router-link>
                <button class="app-header__btn" @click="exportCsv">Export CSV</button>
            </nav>
        </header>

        <!-- Page content -->
        <router-view/>
    </div>
</template>

<script>
export default {
    name: "App",
    methods: {
        exportCsv() {
            // Placeholder – will be wired later.
            alert("CSV export coming soon.");
        },
    },
};
</script>
