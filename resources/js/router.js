import Vue from "vue";
import Router from "vue-router";
import NProgress from "nprogress";
import store from "./store";

import Dashboard from "./views/Dashboard.vue";
import PenilaianList from "./views/PenilaianList.vue";
import PenilaianCreate from "./views/PenilaianCreate.vue";
import AnswerCreate from "./views/AnswerCreate.vue";

import KaryawanList from "./views/KaryawanList.vue";
import KaryawanDetail from "./views/KaryawanDetail.vue";

import ScheduleList from "./views/ScheduleList.vue";

import DataBagian from "./views/DataBagian.vue";
import AksesList from "./views/AksesList.vue";

import Page404 from "./views/Page404.vue";
import Login from "./views/Login.vue";

import WhereToTry from "./views/_WhereToTry.vue";

const originalPush = Router.prototype.push;
Router.prototype.push = function push(location) {
    return originalPush.call(this, location).catch(err => err);
};

Vue.use(Router);

const router = new Router({
    mode: "history",
    base: process.env.BASE_URL,
    routes: [
        {
            path: "/",
            name: "dashboard",
            component: Dashboard,
            meta: {
                title: "Alpha System"
            }
        },
        {
            path: "/login",
            name: "login",
            component: Login,
            meta: {
                title: "SP360 | Login"
            }
        },
        {
            path: "/karyawan",
            name: "karyawan-list",
            component: KaryawanList,
            meta: {
                title: "Alpha System | Daftar Karyawan"
            }
        },
        {
            path: "/karyawan/:id",
            name: "karyawan-detail",
            component: KaryawanDetail,
            meta: {
                title: "Alpha System | Data Karyawan"
            }
        },
        {
            path: "/schedule",
            name: "schedule-list",
            component: ScheduleList,
            meta: {
                title: "Alpha System | Schedule List"
            }
        },
        {
            path: "/data/karyawan",
            name: "data-karyawan",
            component: DataBagian,
            meta: {
                title: "Alpha System | Data Karyawan"
            }
        },
        {
            path: "/data/akses",
            name: "akses-list",
            component: AksesList,
            meta: {
                title: "Alpha System | Akses List"
            }
        },
        {
            path: "/penilaian",
            name: "penilaian",
            component: PenilaianList,
            meta: {
                title: "SP360"
            }
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
            path: "/try",
            name: "try",
            component: WhereToTry,
            meta: {
                title: "TRY HERE"
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

    const publicPages = ["login"];
    const adminPages = ["page-404", "dashboard", "karyawan-detail"];
    const authRequired = !publicPages.includes(to.name);
    const loggedIn = localStorage.getItem("user");

    if (loggedIn && store.state.user.loaded === false) {
        const userData = JSON.parse(loggedIn);
        store.commit("user/SET_USER", userData);
    }

    if (authRequired) {
        if (!loggedIn) {
            return next({ name: "login" });
        }

        if (adminPages.includes(to.name)) {
            return next();
        }

        if (!store.state.user.akses.includes(to.path)) {
            return next("/404");
        }
    }

    next();
});

router.afterEach((to, from) => {
    document.title = to.meta.title;
    NProgress.done();
});

export default router;
