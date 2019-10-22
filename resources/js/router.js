import Vue from "vue";
import Router from "vue-router";
import PenilaianList from "./views/PenilaianList.vue";
import PenilaianCreate from "./views/PenilaianCreate.vue";
import AnswerCreate from "./views/AnswerCreate.vue";
import Page404 from "./views/Page404.vue";
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
            path: "/penilaian",
            name: "penilaian",
            component: PenilaianList,
            meta: {
                title: "SP360"
            },
            alias: "/"
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
        },
        {
            path: "/penilaian/answer",
            name: "penilaian-answer",
            component: AnswerCreate,
            meta: {
                title: "SP360 | Answer"
            }
        },
        {
            path: "/404",
            name: "page-404",
            component: Page404,
            meta: {
                title: "SP360 | 404 Not Found"
            }
        },
        {
            path: "*",
            redirect: { name: "page-404" }
        }
    ]
});

router.beforeEach((to, from, next) => {
    NProgress.start();

    const publicPages = ["/login"];
    const commonPages = ["/penilaian/answer", ...publicPages];

    const authRequired = !publicPages.includes(to.path);
    const adminRequired = !commonPages.includes(to.path);

    const loggedIn = localStorage.getItem("user");
    if (authRequired && !loggedIn) {
        return next("/login");
    }
    if (adminRequired && JSON.parse(loggedIn).user.role < 10) {
        return next("/404");
    }
    next();
});

router.afterEach((to, from, next) => {
    document.title = to.meta.title;
    NProgress.done();
});

export default router;
