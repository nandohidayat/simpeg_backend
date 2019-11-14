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
    DEL_BAGIAN(state, id) {
        state.bagians = state.bagians.filter(b => b.id_bagian != id);
    },
    ADD_DEPARTEMEN(state, departemen) {
        const idxBagian = state.bagians.findIndex(
            b => b.id_bagian === departemen.id_bagian
        );
        state.bagians[idxBagian].departemens.push(departemen);
    },
    DEL_DEPARTEMEN(state, id) {
        const idxBagian = state.bagians.findIndex(b =>
            b.departemens.findIndex(d => d.id_departemen == id)
        );
        state.bagians[idxBagian].departemens = state.bagians[
            idxBagian
        ].departemens.filter(d => d.id_departemen != id);
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
    async deleteBagian({ commit }, id) {
        try {
            await BagianService.deleteBagian(id);
            commit("DEL_BAGIAN", id);
        } catch (err) {
            console.log(err.response);
        }
    },
    createDepartemen({ commit }, departemen) {
        commit("ADD_DEPARTEMEN", departemen);
    },
    deleteDepartemen({ commit }, id) {
        commit("DEL_DEPARTEMEN", id);
    }
};
