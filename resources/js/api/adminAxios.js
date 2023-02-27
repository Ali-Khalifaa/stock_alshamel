import axios from "axios";
import Cookies from "js-cookie";


const adminApi = axios.create({
    baseURL: `http://127.0.0.1:8000/api/`
});

adminApi.interceptors.request.use(
    function(config) {
        config.headers['lang'] = localStorage.getItem("lang") || 'ar';
        config.headers['Authorization'] = "Bearer " + (Cookies.get("token") || '');
        return config;
    },
    function(error) {
        return Promise.reject(error);
    }
);

adminApi.defaults.headers.common['secretApi'] = 'Snr92EUKCmrE06PiJ';
adminApi.defaults.headers.common['Accept'] = 'application/json';
// end axios

export default adminApi;
