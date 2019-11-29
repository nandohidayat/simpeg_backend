import client from "./client";

export default {
    getKaryawans(select) {
        return client.get(`/api/karyawan?select=${select}`);
    },
    getKaryawan(nik) {
        return client.get(`/api/karyawan/${nik}`);
    },
    postKaryawan(karyawan) {
        return client.post("/api/karyawan", karyawan);
    },
    putKaryawan(karyawan, nik) {
        return client.put(`/api/karyawan/${nik}`, karyawan);
    },
    deleteKaryawan(nik) {
        return client.delete(`/api/karyawan/${nik}`);
    }
};
