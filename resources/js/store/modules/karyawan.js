import KaryawanService from "../../services/KaryawanService.js";

export const namespaced = true;

export const state = {
    karyawans: [],
    karyawan: {}
};

export const mutations = {
    SET_KARYAWANS(state, karyawans) {
        state.karyawans = karyawans;
    },
    SET_KARYAWAN(state, karyawan) {
        state.karyawan = karyawan;
    },
    ADD_KARYAWAN(state, karyawan) {
        state.karyawans.push(karyawan);
    }
};

export const actions = {
    async fetchKaryawans({ commit }, { select }) {
        try {
            const res = await KaryawanService.getKaryawans(select);
            commit("SET_KARYAWANS", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async fetchKaryawan({ commit }, nik) {
        try {
            const res = await KaryawanService.getKaryawan(nik);
            commit("SET_KARYAWAN", res.data.data);
        } catch (err) {
            console.log(err.response);
        }
    },
    async createKaryawan({ commit }, karyawan) {
        try {
            await KaryawanService.postKaryawan(karyawan);

            const k = {
                ...karyawan,
                departemen: rootState.departemen.departemens.find(
                    d => d.id_departemen == karyawan.id_departemen
                ).departemen,
                ruang: rootState.ruang.ruangs.find(
                    d => d.id_ruang == karyawan.id_ruang
                ).ruang
            };

            commit("ADD_KARYAWAN", k);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateKaryawan({ commit, rootState }, karyawan) {
        try {
            await KaryawanService.putKaryawan(karyawan, karyawan.nik);

            const k = {
                ...karyawan,
                departemen: rootState.departemen.departemens.find(
                    d => d.id_departemen == karyawan.id_departemen
                ).departemen,
                ruang: rootState.ruang.ruangs.find(
                    d => d.id_ruang == karyawan.id_ruang
                ).ruang
            };

            commit("SET_KARYAWAN", k);
        } catch (err) {
            console.log(err.response);
        }
    },
    async deleteKaryawan({}, nik) {
        try {
            await KaryawanService.deleteKaryawan(nik);
        } catch (err) {
            console.log(err.response);
        }
    }
};
