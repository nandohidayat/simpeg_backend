import client from "./client";

export default {
    getRuangs() {
        return client.get("/api/ruang");
    },
    postRuang(ruang) {
        return client.post("/api/ruang", ruang);
    },
    putRuang(ruang) {
        return client.put(`/api/ruang/${ruang.id_ruang}`, ruang);
    },
    deleteRuang(id) {
        return client.delete(`/api/ruang/${id}`);
    }
};
