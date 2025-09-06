<template>
    <button class="app-header__btn ui-btn" @click="toggle" :aria-pressed="isDark ? 'true' : 'false'">
        {{ isDark ? 'Light' : 'Dark' }}
    </button>
</template>

<script>
const KEY = "theme"; // 'light' | 'dark'
export default {
    name: "ThemeToggle",
    data() {
        return { isDark: false };
    },
    created() {
        // Initial: localStorage -> prefers-color-scheme -> light
        const stored = localStorage.getItem(KEY);
        if (stored === "dark" || stored === "light") {
            this.apply(stored);
        } else {
            const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
            this.apply(prefersDark ? "dark" : "light");
        }
    },
    methods: {
        apply(theme) {
            this.isDark = theme === "dark";
            const root = document.documentElement;
            if (this.isDark) root.setAttribute("data-theme", "dark");
            else root.removeAttribute("data-theme");
            localStorage.setItem(KEY, theme);
        },
        toggle() {
            this.apply(this.isDark ? "light" : "dark");
        },
    },
};
</script>
