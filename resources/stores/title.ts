import { defineStore } from "pinia";
import { ref } from 'vue'

export const useTitleStore = defineStore("title", () => {
    const title = ref<string>('');
    const set = (arg: string) => title.value = arg;
    const get = () => title.value;

    return { set, get }
});