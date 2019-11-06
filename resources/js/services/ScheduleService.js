import client from "./client";

export default {
    getSchedules(year, month) {
        return client.get(`/api/schedule/${year}/${month}`);
    }
};
