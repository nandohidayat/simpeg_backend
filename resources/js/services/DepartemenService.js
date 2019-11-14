import client from "./client";

export default {
    getDepartemens() {
        return client.get("/api/departemen");
    },
    postDepartemen(departemen) {
        return client.post("/api/departemen", departemen);
    },
    deleteDepartemen(id) {
        return client.delete(`/api/departemen/${id}`);
    }
};
