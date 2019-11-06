import client from "./client";

export default {
    getShifts() {
        return client.get("/api/shift");
    }
};
