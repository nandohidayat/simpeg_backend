import client from "./client";

export default {
    getKaryawans() {
        return client.get("/api/karyawan");
    }
};
