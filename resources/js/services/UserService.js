import client from "./client";

export default {
    register(user) {
        return client.post("/api/register", user);
    },
    login(user) {
        return client.post("/api/login", user);
    },
    logout() {
        return client.get("/api/logout");
    }
};
