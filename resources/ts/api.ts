import axios from 'axios';
import router from '../ts/router'

const instance = axios.create({
    baseURL: '/api',
    withCredentials: true // セッション管理
});

instance.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        const currentRoute = router.currentRoute.value.name;

        switch(error.response?.status){
            case 419: 
            case 401: 
                if(!(currentRoute === 'login' || currentRoute === 'logout')){
                    await router.push({ name: 'logout' });
                }
        }

        return Promise.reject(error);
    }
);

export default instance;