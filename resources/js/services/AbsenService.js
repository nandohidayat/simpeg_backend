import client from "./client";

export default {
    getAbsens() {
        return client.get("/api/absen");
    },
    getAbsen(id) {
        console.log(id);
        return client.get(`/api/absen/${id}`);
    },
    postAbsen(absen) {
        return client.post("/api/absen", absen);
    },
    putAbsen(absen) {
        return client.put(`/api/absen/${absen.id_absen}`, absen);
    },
    deleteAbsen(id) {
        return client.delete(`/api/absen/${id}`);
    }
};
