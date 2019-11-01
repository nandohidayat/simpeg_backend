import KaryawanService from "../../services/KaryawanService.js";

export const namespaced = true;

export const state = {
    karyawans: []
};

export const mutations = {
    SET_KARYAWANS(state, karyawans) {
        state.karyawans = karyawans;
    },
    ADD_KARYAWAN(state, karyawan) {
        state.karyawans.push(karyawan);
    }
};

export const actions = {
    async fetchKaryawans({ commit }) {
        try {
            const res = await KaryawanService.getKaryawans();
            commit("SET_KARYAWANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchKaryawan({}, nik) {
        try {
            const res = await KaryawanService.getKaryawan(nik);
            return res.data.data;
        } catch (err) {
            console.log(err.response);
        }
    },
    async createKaryawan({ commit }, karyawan) {
        try {
            await KaryawanService.postKaryawan(karyawan);
            const k = {
                ...karyawan,
                departemen: { id: karyawan.departemen_id },
                ruang: { id: karyawan.ruang_id }
            };
            commit("ADD_KARYAWAN", k);
        } catch (err) {
            console.log(err.response);
        }
    }
};
