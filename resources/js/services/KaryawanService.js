import client from "./client";

export default {
    getKaryawans() {
        return client.get("/api/karyawan");
    },
    getKaryawan(nik) {
        return client.get(`/api/karyawan/${nik}`);
    },
    postKaryawan(karyawan) {
        return client.post("/api/karyawan", karyawan);
    },
    putKaryawan(karyawan, nik) {
        return client.put(`/api/karyawan/${nik}`, karyawan);
    }
};
