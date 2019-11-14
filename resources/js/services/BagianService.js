import client from "./client";

export default {
    getBagians() {
        return client.get("/api/bagian");
    },
    postBagian(bagian) {
        return client.post("/api/bagian", bagian);
    },
    putBagian(bagian) {
        return client.put(`/api/bagian/${bagian.id_bagian}`, bagian);
    },
    deleteBagian(id) {
        return client.delete(`/api/bagian/${id}`);
    }
};
