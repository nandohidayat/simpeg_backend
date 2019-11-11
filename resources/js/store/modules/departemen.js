import DepartemenService from "../../services/DepartemenService.js";

export const namespaced = true;

export const state = {
    departemens: [],
    loaded: false
};

export const mutations = {
    ADD_DEPARTEMENS(state, departemens) {
        state.departemens = departemens;
        state.load = true;
    }
};

export const actions = {
    async fetchDepartemens({ commit }) {
        try {
            const res = await DepartemenService.getDepartemens();
            commit("ADD_DEPARTEMENS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
