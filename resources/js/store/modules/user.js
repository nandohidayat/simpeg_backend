import UserService from "../../services/UserService.js";
import client from "../../services/client";

export const namespaced = true;

export const state = {
    user: undefined,
    menu: [],
    akses: [],
    karyawan: {}
};

export const mutations = {
    SET_USER(state, user) {
        localStorage.setItem("user", JSON.stringify(user));
        client.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${user.token}`;
        state.user = user.user;
        state.menu = user.menu;
        state.akses = user.akses;
    },
    REMOVE_USER() {
        localStorage.removeItem("user");
        location.reload();
    },
    SET_KARYAWAN(state, user) {
        state.karyawan = user;
    }
};

export const actions = {
    async register({}, user) {
        try {
            await UserService.register(user);
        } catch (err) {
            console.log(err);
        }
    },
    async login({ commit }, user) {
        try {
            const res = await UserService.login(user);
            commit("SET_USER", res.data);
        } catch (err) {
            console.log(err);
        }
    },
    logout({ commit }) {
        try {
            UserService.logout();
            commit("REMOVE_USER");
        } catch (err) {
            console.log(err);
        }
    },
    async fetchUser({ commit }, nik) {
        try {
            const res = await UserService.user(nik);
            commit("SET_KARYAWAN", res.data.data);
        } catch (err) {
            console.log(err);
        }
    }
};
