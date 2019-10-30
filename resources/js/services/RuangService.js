import client from "./client";

export default {
    getRuangs() {
        return client.get("/api/ruang");
    }
};
