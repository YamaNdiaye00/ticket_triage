// Tiny Axios helper, baseURL points at Laravel API.
// Registered this on the Vue app so Options API components can use `this.$api`.
import axios from "axios";

const api = axios.create({
    baseURL: "/api",
    headers: {"Content-Type": "application/json", Accept: "application/json"},
});

export default api;
