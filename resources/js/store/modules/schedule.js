import ScheduleService from "../../services/ScheduleService.js";

export const namespaced = true;

export const state = {
    schedules: [],
    schedule: [],
    ruang: undefined
};

export const mutations = {
    SET_SCHEDULES(state, schedules) {
        state.schedules = schedules.schedule;
        state.ruang = schedules.ruang;
    },
    SET_SCHEDULE(state, schedule) {
        state.schedule = schedule;
    }
};

export const actions = {
    async fetchSchedules({ commit }, { year, month }) {
        try {
            const res = await ScheduleService.getSchedules(year, month);
            commit("SET_SCHEDULES", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchSchedule({ commit }, { year, month, id }) {
        try {
            const res = await ScheduleService.getSchedule(year, month, id);
            commit("SET_SCHEDULE", res.data.data);
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
