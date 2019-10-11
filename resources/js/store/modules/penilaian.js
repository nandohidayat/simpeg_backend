import PenilaianService from "../../services/PegawaiService.js";

export const namespaced = true;

export const state = {
    penilaians: []
};

export const mutations = {
    ADD_PENILIAINS(state, penilaians) {
        state.penilaians = penilaians;
    }
};

export const actions = {
    fetchPenilaians({ commit }) {
        return PenilaianService.getPenilaians()
            .then(res => {
                commit("ADD_PENILAIANS", res.data);
            })
            .catch(err => {
                console.log(err.response);
            });
    }
};
