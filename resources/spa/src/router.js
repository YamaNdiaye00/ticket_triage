import { createRouter, createWebHistory } from "vue-router";
import Tickets from "./views/Tickets.vue";
import TicketShow from "./views/TicketShow.vue";
import Dashboard from "./views/Dashboard.vue";

export default createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", redirect: "/tickets" },
        { path: "/tickets", component: Tickets },
        { path: "/tickets/:id", component: TicketShow, props: true },
        { path: "/dashboard", component: Dashboard },
    ],
});
