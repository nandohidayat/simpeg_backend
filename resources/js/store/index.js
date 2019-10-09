import Vue from "vue";
import Vuex from "vuex";
import * as pegawai from "../store/modules/pegawai.js";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        pegawai
    },
    state: {},
    mutations: {},
    actions: {}
});
