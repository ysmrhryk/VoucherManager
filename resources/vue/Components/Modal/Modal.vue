<template>
    <template v-if="isShowing">
        <div class="modal-backdrop fade show"></div>

        <div class="modal fade show d-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="false">
            <div class="modal-dialog modal-dialog-scrollable" :class="isFullscreen ? 'modal-fullscreen' : 'modal-lg'">
                <div class="modal-content rounded-0">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">{{ title }}</h1>
                        <button type="button" class="btn-close" @click="hide()"></button>
                    </div>
                    <div class="modal-body">
                        <slot name="body"></slot>
                    </div>
                    <div class="modal-footer">
                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>

<script setup lang="ts">
    import { ref, watchEffect } from 'vue';

    defineProps<{
        title: string,
        isFullscreen?: boolean
    }>();

    const isShowing = ref<boolean>(false);

    watchEffect(() => {
        if(isShowing.value) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
    });

    const show = () => isShowing.value = true;
    const hide = () => isShowing.value = false;

    defineExpose({show, hide});
</script>