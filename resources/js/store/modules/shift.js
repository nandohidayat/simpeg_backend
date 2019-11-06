import ShiftService from "../../services/ShiftService.js";

export const namespaced = true;

export const state = {
    shifts: []
};

export const mutations = {
    ADD_SHIFTS(state, shifts) {
        state.shifts = shifts;
    }
};

export const actions = {
    async fetchShifts({ commit }) {
        try {
            const res = await ShiftService.getShifts();
            commit("ADD_SHIFTS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
