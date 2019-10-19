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
    },
    UPDATE_PENILAIANS(state, id) {
        state.penilaians = state.penilaians.filter(p => p.id != id);
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
    },
    async updatePenilaian({}, { id, penilaian }) {
        try {
            await PenilaianService.putPenilaian(id, penilaian);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deletePenilaian({ commit }, id) {
        try {
            await PenilaianService.deletePenilaian(id);
            commit("UPDATE_PENILAIANS", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
