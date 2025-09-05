// Entry point. Creates the Vue app, wires the router, registers $api, loads global CSS.
// Mounts the SPA into <div id="app"> from index.html.
import {createApp} from "vue";
import App from "./App.vue";
import router from "./router";
import api from "./api";               // Axios instance
import "../assets/styles/index.css";   // Global CSS imports

const app = createApp(App);
app.config.globalProperties.$api = api; // enables this.$api (Options API)
app.use(router);
app.mount("#app");                      // index.html contains #app
