import ShiftService from "../../services/ShiftService.js";

export const namespaced = true;

export const state = {
    shifts: []
};

export const mutations = {
    ADD_SHIFTS(state, shifts) {
        state.shifts = shifts;
    },
    ADD_SHIFT(state, shift) {
        state.shifts.push(shift);
    },
    EDT_SHIFT(state, shift) {
        const idx = state.shifts.findIndex(b => b.id_shift == shift.id_shift);
        state.shifts[idx].mulai = shift.mulai;
        state.shifts[idx].selesai = shift.selesai;
        state.shifts[idx].kode = shift.kode;
    },
    DEL_SHIFT(state, id) {
        state.shifts = state.shifts.filter(b => b.id_shift != id);
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
    },
    async createShift({ commit }, shift) {
        try {
            const res = await ShiftService.postShift(shift);
            commit("ADD_SHIFT", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateShift({ commit }, shift) {
        try {
            await ShiftService.putShift(shift);
            commit("EDT_SHIFT", shift);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteShift({ commit }, id) {
        try {
            await ShiftService.deleteShift(id);
            commit("DEL_SHIFT", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
