import PenilaianService from "../../services/PenilaianService.js";

export const namespaced = true;

export const state = {
    penilaians: []
};

export const mutations = {
    ADD_PENILAIANS(state, penilaians) {
        state.penilaians = penilaians;
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
    }
};
