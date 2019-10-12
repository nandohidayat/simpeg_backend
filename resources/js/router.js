import Vue from "vue";
import Router from "vue-router";
import PenilaianList from "./views/PenilaianList.vue";
import PenilaianCreate from "./views/PenilaianCreate.vue";
import NProgress from "nprogress";

Vue.use(Router);

const router = new Router({
    mode: "history",
    base: process.env.BASE_URL,
    routes: [
        {
            path: "/",
            name: "penilaian",
            component: PenilaianList,
            meta: {
                title: "SP360"
            }
        },
        {
            path: "/create",
            name: "penilaian-create",
            component: PenilaianCreate,
            meta: {
                title: "SP360 | Create"
            }
        }
    ]
});

router.beforeEach((to, from, next) => {
    NProgress.start();
    next();
});

router.afterEach((to, from, next) => {
    document.title = to.meta.title;
    NProgress.done();
});

export default router;
