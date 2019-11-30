import client from "./client";

export default {
    getAbsens() {
        return client.get("/api/absen");
    },
    getAbsen(id, year, month) {
        return client.get(`/api/absen/${id}?year=${year}&month=${month}`);
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
