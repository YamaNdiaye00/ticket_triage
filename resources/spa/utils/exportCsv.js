export async function exportTicketsCsv($api, filters = {}) {
    const params = {};
    if (filters.q) params.q = filters.q;
    if (filters.status) params.status = filters.status;
    if (filters.category) params.category = filters.category;

    const res = await $api.get("/tickets/export", {
        params,
        responseType: "blob",
    });

    const cd = res.headers["content-disposition"] || "";
    const m = cd.match(/filename="([^"]+)"/i);
    const filename = m ? m[1] : "tickets_export.csv";

    const blobUrl = URL.createObjectURL(res.data);
    const a = document.createElement("a");
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(blobUrl);
}
