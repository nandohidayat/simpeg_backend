import Vue from "vue";
import Vuex from "vuex";
import * as pegawai from "../store/modules/pegawai.js";
import * as penilaian from "../store/modules/penilaian.js";
import * as ruang from "../store/modules/ruang.js";
import * as departemen from "../store/modules/departemen.js";
import * as karyawan from "../store/modules/karyawan.js";
import * as user from "../store/modules/user.js";
import * as schedule from "../store/modules/schedule.js";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        pegawai,
        penilaian,
        ruang,
        departemen,
        karyawan,
        user,
        schedule
    },
    state: {},
    mutations: {},
    actions: {}
});
