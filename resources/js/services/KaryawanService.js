import client from "./client";

export default {
    getKaryawans(select = undefined) {
        let params = "?";
        if (select !== undefined) params += `select=${select}`;

        return client.get(`/api/karyawan${params}`);
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
