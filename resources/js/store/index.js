import Vue from "vue";
import Vuex from "vuex";
import * as pegawai from "../store/modules/pegawai.js";
import * as penilaian from "../store/modules/penilaian.js";
import * as user from "../store/modules/user.js";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        pegawai,
        penilaian,
        user
    },
    state: {},
    mutations: {},
    actions: {}
});
