import { defineStore } from "pinia";
import { ref } from 'vue'

export const useOverlayStore = defineStore("overlay", () => {
    const isLoading = ref(true);
    const show = () => isLoading.value = true;
    const hide = () => isLoading.value = false;

    return { isLoading, show, hide }
});