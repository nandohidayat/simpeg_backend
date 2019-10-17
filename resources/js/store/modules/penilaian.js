import PenilaianService from "../../services/PenilaianService.js";

export const namespaced = true;

export const state = {
    penilaians: [],
    update: {}
};

export const mutations = {
    ADD_PENILAIANS(state, penilaians) {
        state.penilaians = penilaians;
    },
    ADD_UPDATE(state, update) {
        state.update = update;
    }
};

export const actions = {
    async fetchPenilaians({ commit }) {
        try {
            const res = await PenilaianService.getPenilaians();
            commit("ADD_PENILAIANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createPenilaian({}, penilaian) {
        try {
            await PenilaianService.postPenilaian(penilaian);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchUpdate({ commit }, id) {
        try {
            const res = await PenilaianService.getPenilaianUpdate(id);
            commit("ADD_UPDATE", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
