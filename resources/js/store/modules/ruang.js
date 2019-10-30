import RuangService from "../../services/RuangService.js";

export const namespaced = true;

export const state = {
    ruangs: []
};

export const mutations = {
    ADD_RUANGS(state, ruangs) {
        state.ruangs = ruangs;
    }
};

export const actions = {
    async fetchRuangs({ commit }) {
        try {
            const res = await RuangService.getRuangs();
            commit("ADD_RUANGS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
