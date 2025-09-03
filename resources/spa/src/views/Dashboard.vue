<template>
    <section class="dashboard container">
        <h1 class="dashboard__title">Dashboard</h1>

        <div class="dashboard__cards">
            <div class="dashboard__card">
                <div class="dashboard__card-title">Tickets by Status</div>
                <ul class="dashboard__list">
                    <li v-for="s in byStatus" :key="s.status">
                        {{ s.status }}: {{ s.count }}
                    </li>
                </ul>
            </div>

            <div class="dashboard__card">
                <div class="dashboard__card-title">Tickets by Category</div>
                <ul class="dashboard__list">
                    <li v-for="c in byCategory" :key="c.category">
                        {{ c.category || "(uncategorized)" }}: {{ c.count }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="dashboard__chart">
            <!-- Later: add Chart.js here -->
            <div class="dashboard__placeholder">
                One simple chart (pie or bar) goes here.
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "Dashboard",
    data() {
        return {
            byStatus: [
                // placeholder; replace with GET /api/stats
                { status: "open", count: 10 },
                { status: "in_progress", count: 3 },
                { status: "resolved", count: 6 },
                { status: "closed", count: 2 },
            ],
            byCategory: [
                { category: "Bug", count: 8 },
                { category: "Billing", count: 5 },
                { category: "Feature", count: 4 },
                { category: "Account", count: 2 },
                { category: "Other", count: 2 },
            ],
        };
    },
    created() {
        this.fetch();
    },
    methods: {
        async fetch() {
            // TODO: GET /api/stats and populate byStatus/byCategory
        },
    },
};
</script>

<style>
.dashboard__cards { display:grid; gap:.75rem; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); }
.dashboard__card { border:1px solid #ddd; border-radius:.5rem; padding:1rem; }
.dashboard__card-title { font-weight:600; margin-bottom:.5rem; }
.dashboard__chart { margin-top:1rem; }
.dashboard__placeholder { border:1px dashed #bbb; border-radius:.5rem; padding:2rem; text-align:center; }
</style>
