import client from "./client";

export default {
    getPegawais() {
        return client.get("/api/pegawai");
    }
};
