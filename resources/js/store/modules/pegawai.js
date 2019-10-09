import PegawaiService from "../../services/PegawaiService.js";

export const namespaced = true;

export const state = {
    pegawais: [],
    pegawaistotal: 0
};

export const mutations = {
    ADD_PEGAWAIS(state, pegawais) {
        state.pegawais = pegawais;
    }
};

export const actions = {
    fetchPegawais({ commit }) {
        return PegawaiService.getPegawais()
            .then(res => {
                commit("ADD_PEGAWAIS", res.data.data);
            })
            .catch(err => {
                console.log(err.response);
            });
    }
};
