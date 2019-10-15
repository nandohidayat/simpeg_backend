import client from "./client";

export default {
    getPenilaians() {
        return client.get("/api/penilaian");
    }
};
