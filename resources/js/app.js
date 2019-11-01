import "./bootstrap";

import Vue from "vue";
import Vuetify from "vuetify";
import axios from "axios";

Vue.use(Vuetify);

import App from "./App.vue";
import router from "./router";
import store from "./store";
import upperFirst from "lodash/upperFirst";
import camelCase from "lodash/camelCase";
import "nprogress/nprogress.css";
import vuetify from "./plugins/vuetify";

const requireComponent = require.context(
    "./components",

    false,

    /Base[A-Z]\w+\.(vue|js)$/
);

requireComponent.keys().forEach(fileName => {
    const componentConfig = requireComponent(fileName);

    const componentName = upperFirst(
        camelCase(
            fileName
                .split("/")
                .pop()
                .replace(/\.\w+$/, "")
        )
    );

    Vue.component(
        componentName,

        componentConfig.default || componentConfig
    );
});

Vue.config.productionTip = false;

new Vue({
    router,
    store,
    vuetify,
    render: h => h(App),
    async beforeCreate() {
        // const userString = localStorage.getItem("user");
        // if (userString) {
        //     const userData = JSON.parse(userString);
        //     console.log(userData);
        //     this.$store.commit("user/SET_USER", userData, { root: true });
        // }
        // //
        // axios.interceptors.response.use(
        //     response => response,
        //     error => {
        //         console.log(error.response);
        //         if (error.response.status === 401) {
        //             this.$router.push("/login");
        //             this.$store.dispatch("user/logout");
        //         }
        //         return Promise.reject(error);
        //     }
        // );
        await Promise.all([
            store.dispatch("departemen/fetchDepartemens"),
            store.dispatch("ruang/fetchRuangs")
        ]);
    }
}).$mount("#app");
