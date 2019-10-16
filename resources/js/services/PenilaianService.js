import client from "./client";

export default {
    getPenilaians() {
        return client.get("/api/penilaian");
    },
    postPenilaian(penilaian) {
        return client.post("/api/penilaian", penilaian);
    }
};
