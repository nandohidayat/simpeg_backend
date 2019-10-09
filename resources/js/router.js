import Vue from "vue";
import Router from "vue-router";
import ScheduleList from "./views/ScheduleList.vue";
import NProgress from "nprogress";

Vue.use(Router);

const router = new Router({
    mode: "history",
    base: process.env.BASE_URL,
    routes: [
        {
            path: "/",
            name: "schedule",
            component: ScheduleList,
            meta: {
                title: "SP360"
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
