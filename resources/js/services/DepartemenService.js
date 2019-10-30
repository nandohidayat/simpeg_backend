import client from "./client";

export default {
    getDepartemens() {
        return client.get("/api/departemen");
    }
};
