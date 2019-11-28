import client from "./client";

export default {
    getSchedules(year, month) {
        return client.get(`/api/schedule?year=${year}&month=${month}`);
    },
    postSchedules(schedule, year, month) {
        return client.post(
            `/api/schedule?year=${year}&month=${month}`,
            schedule
        );
    }
};
