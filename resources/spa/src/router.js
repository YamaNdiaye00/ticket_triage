// SPA router: maps URL paths to view components.
// History mode gives clean URLs (no hash).
import {createRouter, createWebHistory} from "vue-router";
import Tickets from "./views/Tickets.vue";
import TicketShow from "./views/TicketShow.vue";
import Dashboard from "./views/Dashboard.vue";

const routes = [
    {path: "/", redirect: "/tickets"},
    {path: "/tickets", component: Tickets},
    {path: "/tickets/:id", component: TicketShow, props: true},
    {path: "/dashboard", component: Dashboard},
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
