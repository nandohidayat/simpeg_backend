import DepartemenService from "../../services/DepartemenService.js";
import { cpus } from "os";

export const namespaced = true;

export const state = {
    departemens: [],
    loaded: false
};

export const mutations = {
    SET_DEPARTEMENS(state, departemens) {
        state.departemens = departemens;
        state.load = true;
    },
    ADD_DEPARTEMEN(state, departemen) {
        state.departemens.push(departemen);
    },
    DEL_DEPARTEMEN(state, id) {
        state.departemens = state.departemens.filter(
            d => d.id_departemen != id
        );
    }
};

export const actions = {
    async fetchDepartemens({ commit }) {
        try {
            const res = await DepartemenService.getDepartemens();
            commit("SET_DEPARTEMENS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createDepartemen({ commit, dispatch }, departemen) {
        try {
            const res = await DepartemenService.postDepartemen(departemen);
            commit("ADD_DEPARTEMEN", res.data.data);
            dispatch("bagian/createDepartemen", res.data.data, { root: true });
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteDepartemen({ commit }, id) {
        try {
            await DepartemenService.deleteDepartemen(id);
            commit("DEL_DEPARTEMEN", id);
            dispatch("bagian/deleteDepartemen", id, { root: true });
        } catch (err) {
            console.log(err.response);
        }
    }
};
