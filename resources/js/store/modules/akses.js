import AksesService from "../../services/AksesService.js";

export const namespaced = true;

export const state = {
    aksess: [],
    akses: [],
    loaded: false
};

export const mutations = {
    SET_AKSESS(state, aksess) {
        state.aksess = aksess;
        state.loaded = true;
    },
    SET_AKSES(state, akses) {
        state.akses = akses;
    },
    ADD_AKSES(state, akses) {
        state.aksess.push(akses);
    },
    EDT_AKSES(state, akses) {
        const idx = state.aksess.findIndex(b => b.id_akses == akses.id_akses);
        state.aksess[idx].akses = akses.akses;
    },
    DEL_AKSES(state, id) {
        state.aksess = state.aksess.filter(b => b.id_akses != id);
    }
};

export const actions = {
    async fetchAksess({ commit }) {
        try {
            const res = await AksesService.getAksess();
            commit("SET_AKSESS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchAkses({ commit }, id) {
        try {
            const res = await AksesService.getAkses(id);
            commit("SET_AKSES", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createAkses({}, akses) {
        try {
            await AksesService.postAkses(akses);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateAkses({ commit }, akses) {
        try {
            await AksesService.putAkses(akses);
            commit("EDT_AKSES", akses);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteAkses({ commit }, id) {
        try {
            await AksesService.deleteAkses(id);
            commit("DEL_AKSES", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
