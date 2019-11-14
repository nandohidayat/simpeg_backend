import BagianService from "../../services/BagianService.js";

export const namespaced = true;

export const state = {
    bagians: [],
    loaded: false
};

export const mutations = {
    SET_BAGIANS(state, bagians) {
        state.bagians = bagians;
        state.loaded = true;
    },
    ADD_BAGIAN(state, bagian) {
        state.bagians.push(bagian);
    },
    EDT_BAGIAN(state, bagian) {
        const idx = state.bagians.findIndex(
            b => b.id_bagian == bagian.id_bagian
        );
        state.bagians[idx].bagian = bagian.bagian;
    },
    DEL_BAGIAN(state, id) {
        state.bagians = state.bagians.filter(b => b.id_bagian != id);
    }
};

export const actions = {
    async fetchBagians({ commit }) {
        try {
            const res = await BagianService.getBagians();
            commit("SET_BAGIANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createBagian({ commit }, bagian) {
        try {
            const res = await BagianService.postBagian(bagian);
            commit("ADD_BAGIAN", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateBagian({ commit }, bagian) {
        try {
            await BagianService.putBagian(bagian);
            commit("EDT_BAGIAN", bagian);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteBagian({ commit }, id) {
        try {
            await BagianService.deleteBagian(id);
            commit("DEL_BAGIAN", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
