<template>
    <section class="dashboard">
        <h1 class="dashboard__title">Dashboard</h1>

        <!-- Cards: by status -->
        <div class="dashboard__group">
            <h2 class="dashboard__subtitle">By Status</h2>
            <div class="dashboard__cards">
                <div
                    v-for="(count, key) in statusCards"
                    :key="'status-' + key"
                    class="dashboard__card"
                >
                    <div class="dashboard__card-key">{{ key }}</div>
                    <div class="dashboard__card-val">{{ count }}</div>
                </div>
            </div>
        </div>

        <!-- Cards: by category -->
        <div class="dashboard__group">
            <h2 class="dashboard__subtitle">By Category</h2>
            <div class="dashboard__cards">
                <div
                    v-for="(count, key) in categoryCards"
                    :key="'category-' + key"
                    class="dashboard__card"
                >
                    <div class="dashboard__card-key">{{ key === '' ? '-' : key }}</div>
                    <div class="dashboard__card-val">{{ count }}</div>
                </div>
            </div>
        </div>

        <!-- Simple chart (vanilla canvas) -->
        <div class="dashboard__chart">
            <h2 class="dashboard__subtitle">Tickets by Category</h2>
            <canvas ref="chart" width="480" height="280"></canvas>
        </div>
    </section>
</template>

<script>
export default {
    name: "Dashboard",
    data() {
        return {
            stats: {status: {}, category: {}},
        };
    },
    computed: {
        statusCards() {
            return this.stats.status || {};
        },
        categoryCards() {
            return this.stats.category || {};
        },
    },
    async created() {
        await this.fetchStats();
        this.$nextTick(() => this.drawChart());
    },
    methods: {
        async fetchStats() {
            const {data} = await this.$api.get("/stats");
            // Defensive: coerce to objects
            this.stats = {
                status: data.status || {},
                category: data.category || {}
            };
        },

        drawChart() {
            const el = this.$refs.chart;
            if (!el) return;
            const ctx = el.getContext("2d");
            const labels = Object.keys(this.categoryCards);
            const values = Object.values(this.categoryCards).map(v => Number(v || 0));

            // Clear
            ctx.clearRect(0, 0, el.width, el.height);

            // Axes/padding
            const pad = {top: 20, right: 20, bottom: 40, left: 40};
            const W = el.width - pad.left - pad.right;
            const H = el.height - pad.top - pad.bottom;

            // Scale
            const max = Math.max(1, ...values);
            const toY = v => pad.top + H - (H * v / max);

            // Bars
            const n = Math.max(1, labels.length);
            const gap = 14;
            const barW = Math.max(10, (W - gap * (n - 1)) / n);

            labels.forEach((label, i) => {
                const x = pad.left + i * (barW + gap);
                const y = toY(values[i]);
                const h = pad.top + H - y;

                // bar
                ctx.fillStyle = "#7aa7ff";
                ctx.fillRect(x, y, barW, h);

                // value
                ctx.fillStyle = "#222";
                ctx.font = "12px system-ui, Arial";
                ctx.textAlign = "center";
                ctx.fillText(values[i], x + barW / 2, y - 6);

                // label
                ctx.fillStyle = "#444";
                ctx.save();
                ctx.translate(x + barW / 2, pad.top + H + 14);
                ctx.rotate(-Math.PI / 12); // slight angle
                ctx.fillText(label, 0, 16);
                ctx.restore();
            });

            // y-axis ticks (0, max)
            ctx.fillStyle = "#666";
            ctx.textAlign = "right";
            ctx.fillText(0, pad.left - 6, pad.top + H);
            ctx.fillText(max, pad.left - 6, pad.top + 8);

            // axes lines
            ctx.strokeStyle = "#ddd";
            ctx.beginPath();
            ctx.moveTo(pad.left, pad.top);
            ctx.lineTo(pad.left, pad.top + H);
            ctx.lineTo(pad.left + W, pad.top + H);
            ctx.stroke();
        }
    }
};
</script>
