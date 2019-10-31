import client from "./client";

export default {
    getKaryawans() {
        return client.get("/api/karyawan");
    },
    postKaryawan(karyawan) {
        return client.post("/api/karyawan", karyawan);
    }
};
