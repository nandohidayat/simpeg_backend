import AbsenService from "../../services/AbsenService.js";

export const namespaced = true;

export const state = {
    absens: [],
    absen: [],
    loaded: false
};

export const mutations = {
    SET_ABSENS(state, absens) {
        state.absens = absens;
        state.loaded = true;
    },
    SET_ABSEN(state, absen) {
        state.absen = absen;
    },
    ADD_ABSEN(state, absen) {
        state.absens.push(absen);
    },
    EDT_ABSEN(state, absen) {
        const idx = state.absens.findIndex(b => b.id_absen == absen.id_absen);
        state.absens[idx].absen = absen.absen;
    },
    DEL_ABSEN(state, id) {
        state.absens = state.absens.filter(b => b.id_absen != id);
    }
};

export const actions = {
    async fetchAbsens({ commit }) {
        try {
            const res = await AbsenService.getAbsens();
            commit("SET_ABSENS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchAbsen({ commit }, { id, year, month }) {
        try {
            const res = await AbsenService.getAbsen(id, year, month);
            commit("SET_ABSEN", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createAbsen({}, absen) {
        try {
            await AbsenService.postAbsen(absen);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateAbsen({ commit }, absen) {
        try {
            await AbsenService.putAbsen(absen);
            commit("EDT_ABSEN", absen);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteAbsen({ commit }, id) {
        try {
            await AbsenService.deleteAbsen(id);
            commit("DEL_ABSEN", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
