import client from "./client";

export default {
    getPenilaians() {
        return client.get("/api/penilaian");
    },
    postPenilaian(penilaian) {
        return client.post("/api/penilaian", penilaian);
    },
    getPenilaianUpdate(id) {
        return client.get(`/api/penilaian/${id}/update`);
    },
    putPenilaian(id, penilaian) {
        return client.put(`/api/penilaian/${id}`, penilaian);
    }
};
