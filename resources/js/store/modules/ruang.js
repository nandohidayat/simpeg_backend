import RuangService from "../../services/RuangService.js";

export const namespaced = true;

export const state = {
    ruangs: [],
    loaded: false
};

export const mutations = {
    SET_RUANGS(state, ruangs) {
        state.ruangs = ruangs;
        state.loaded = true;
    },
    ADD_RUANG(state, ruang) {
        state.ruangs.push(ruang);
    },
    EDT_RUANG(state, ruang) {
        const idx = state.ruangs.findIndex(b => b.id_ruang == ruang.id_ruang);
        state.ruangs[idx].ruang = ruang.ruang;
    },
    DEL_RUANG(state, id) {
        state.ruangs = state.ruangs.filter(b => b.id_ruang != id);
    }
};

export const actions = {
    async fetchRuangs({ commit }) {
        try {
            const res = await RuangService.getRuangs();
            commit("SET_RUANGS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createRuang({ commit }, ruang) {
        try {
            const res = await RuangService.postRuang(ruang);
            commit("ADD_RUANG", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateRuang({ commit }, ruang) {
        try {
            await RuangService.putRuang(ruang);
            commit("EDT_RUANG", ruang);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteRuang({ commit }, id) {
        try {
            await RuangService.deleteRuang(id);
            commit("DEL_RUANG", id);
        } catch (err) {
            console.log(err.response);
        }
    }
};
