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
            let arr = [];

            const last = new Date(
                new Date().getFullYear(),
                new Date().getMonth() + 1,
                0
            ).getDate();

            res.data.data.forEach(s => {
                let obj = {};
                obj.nik = s.nik;
                obj.nama = s.nama;
                for (let i = 0; i < last; i++) {
                    obj[`day${i + 1}`] = s.schedules[i]
                        ? s.schedules[i].kode
                        : undefined;
                }
                arr.push(obj);
            });

            commit("ADD_SCHEDULES", arr);
        } catch (err) {
            console.log(err.response);
        }
    }
};
