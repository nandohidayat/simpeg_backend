import client from "./client";

export default {
    getBagians() {
        return client.get("/api/bagian");
    },
    postBagian(bagian) {
        return client.post("/api/bagian", bagian);
    },
    putBagian(id, bagian) {
        return client.put(`/api/bagian/${id}`, bagian);
    },
    deleteBagian(id) {
        return client.delete(`/api/bagian/${id}`);
    }
};
