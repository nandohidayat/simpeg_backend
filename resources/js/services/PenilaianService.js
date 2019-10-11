import axios from "axios";
import NProgress from "nprogress";

const penilaianClient = axios.create({
    baseURL: "/",
    withCredentials: false,
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json"
    },
    timeout: 10000
});

penilaianClient.interceptors.request.use(config => {
    NProgress.start();
    return config;
});

penilaianClient.interceptors.response.use(response => {
    NProgress.done();
    return response;
});

export default {
    getPenilaians() {
        return {};
        // return penilaianClient.get("/api/pegawais");
    }
};
