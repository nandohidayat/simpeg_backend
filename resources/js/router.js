import Vue from "vue";
import Router from "vue-router";
import PenilaianList from "./views/PenilaianList.vue";
import PenilaianCreate from "./views/PenilaianCreate.vue";
import Login from "./views/Login.vue";
import NProgress from "nprogress";

Vue.use(Router);

const router = new Router({
    mode: "history",
    base: process.env.BASE_URL,
    routes: [
        {
            path: "/login",
            name: "login",
            component: Login,
            meta: {
                title: "SP360 | Login"
            }
        },
        {
            path: "/",
            name: "penilaian",
            component: PenilaianList,
            meta: {
                title: "SP360"
            },
            alias: "/penilaian"
        },
        {
            path: "/penilaian/create",
            name: "penilaian-create",
            component: PenilaianCreate,
            meta: {
                title: "SP360 | Create"
            }
        },
        {
            path: "/penilaian/:id/update",
            name: "penilaian-update",
            component: PenilaianCreate,
            meta: {
                title: "SP360 | Update"
            }
        }
    ]
});

router.beforeEach((to, from, next) => {
    NProgress.start();

    const publicPages = ["/login"];
    const authRequired = !publicPages.includes(to.path);
    const loggedIn = localStorage.getItem("user");

    if (authRequired && !loggedIn) {
        return next("/login");
    }

    next();
});

router.afterEach((to, from, next) => {
    document.title = to.meta.title;
    NProgress.done();
});

export default router;
