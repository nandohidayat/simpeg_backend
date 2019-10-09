import axios from "axios";
import NProgress from "nprogress";

const pegawaiClient = axios.create({
    baseURL: "/",
    withCredentials: false,
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json"
    },
    timeout: 10000
});

pegawaiClient.interceptors.request.use(config => {
    NProgress.start();
    return config;
});

pegawaiClient.interceptors.response.use(response => {
    NProgress.done();
    return response;
});

export default {
    getPegawais() {
        return pegawaiClient.get("/api/pegawais");
    }
};
