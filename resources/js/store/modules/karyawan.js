import KaryawanService from "../../services/KaryawanService.js";

export const namespaced = true;

export const state = {
    karyawans: []
};

export const mutations = {
    ADD_KARYAWANS(state, karyawans) {
        state.karyawans = karyawans;
    }
};

export const actions = {
    async fetchKaryawans({ commit }) {
        try {
            const res = await KaryawanService.getKaryawans();
            commit("ADD_KARYAWANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    }
};
