import client from "./client";

export default {
    getSchedules(year, month) {
        return client.get(`/api/schedule/${year}/${month}`);
    },
    postSchedules(schedule, year, month) {
        return client.post(`/api/schedule/${year}/${month}`, schedule);
    }
};
