import axios from "axios";
import NProgress from "nprogress";

const client = axios.create({
    baseURL: "/",
    // timeout: 10000,
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json"
    }
});

client.interceptors.request.use(config => {
    NProgress.start();
    return config;
});

client.interceptors.response.use(response => {
    NProgress.done();
    return response;
});

export default client;
