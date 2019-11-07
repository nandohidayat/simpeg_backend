import ScheduleService from "../../services/ScheduleService.js";

export const namespaced = true;

export const state = {
    schedules: []
};

export const mutations = {
    ADD_SCHEDULES(state, schedules) {
        state.schedules = schedules;
    }
};

export const actions = {
    async fetchSchedules({ commit }, { year, month }) {
        try {
            const res = await ScheduleService.getSchedules(year, month);
            commit("ADD_SCHEDULES", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createSchedules({}, { schedules, year, month }) {
        try {
            await ScheduleService.postSchedules(schedules, year, month);
        } catch (err) {
            console.log(err.response);
        }
    }
};
