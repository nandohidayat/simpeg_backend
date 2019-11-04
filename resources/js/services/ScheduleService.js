import client from "./client";

export default {
    getSchedules() {
        return client.get("/api/schedule/1/1");
    }
};
