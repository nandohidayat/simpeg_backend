import Vue from "vue";
import Vuex from "vuex";
import * as pegawai from "../store/modules/pegawai.js";
import * as penilaian from "../store/modules/penilaian.js";
import * as ruang from "../store/modules/ruang.js";
import * as departemen from "../store/modules/departemen.js";
import * as user from "../store/modules/user.js";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        pegawai,
        penilaian,
        ruang,
        departemen,
        user
    },
    state: {},
    mutations: {},
    actions: {}
});
