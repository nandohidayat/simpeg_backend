import Vue from "vue";
import Vuex from "vuex";
import * as pegawai from "../store/modules/pegawai.js";
import * as penilaian from "../store/modules/penilaian.js";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        pegawai,
        penilaian
    },
    state: {},
    mutations: {},
    actions: {}
});
