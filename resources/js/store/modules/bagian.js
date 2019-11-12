import BagianService from "../../services/BagianService.js";

export const namespaced = true;

export const state = {
    bagians: [],
    loaded: false
};

export const mutations = {
    ADD_BAGIANS(state, bagians) {
        state.bagians = bagians;
        state.load = true;
    }
};

export const actions = {
    async fetchBagians({ commit }) {
        try {
            const res = await BagianService.getBagians();
            commit("ADD_BAGIANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
