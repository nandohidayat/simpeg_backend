import client from "./client";

export default {
    getBagians() {
        return client.get("/api/bagian");
    }
};
