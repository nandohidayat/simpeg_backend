import client from "./client";

export default {
    getAksess() {
        return client.get("/api/akses");
    },
    getAkses(id) {
        return client.get(`/api/akses/${id}`);
    },
    postAkses(akses) {
        return client.post("/api/akses", akses);
    },
    putAkses(akses) {
        return client.put(`/api/akses/${akses.id_akses}`, akses);
    },
    deleteAkses(id) {
        return client.delete(`/api/akses/${id}`);
    }
};
