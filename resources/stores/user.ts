import { defineStore } from "pinia";
import { ref } from 'vue'

interface User {
    id: number | null,
    code: string | null,
    email: string | null,
    name: string | null,
}

export const useUserStore = defineStore("user", () => {
    const user = ref<User>({
        id: null,
        code: null,
        email: null,
        name: null,
    });

    const set = (arg: User) => user.value = arg;
    const get = () => user.value;

    return { set, get }
});