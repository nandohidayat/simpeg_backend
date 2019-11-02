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
    async fetchKaryawans({ commit }) {
        try {
            const res = await KaryawanService.getKaryawans();
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
            commit("ADD_KARYAWAN", k);
        } catch (err) {
            console.log(err.response);
        }
    },
    async updateKaryawan({ commit }, karyawan) {
        try {
            await KaryawanService.putKaryawan(karyawan, karyawan.nik);
            const k = {
                ...karyawan,
                departemen: { id: karyawan.departemen_id },
                ruang: { id: karyawan.ruang_id }
            };
            commit("SET_KARYAWAN", k);
        } catch (err) {
            console.log(err.response);
        }
    }
};
