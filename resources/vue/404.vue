<template>
    <div class="w-100 vh-100 d-flex justify-content-center align-items-center">
        <div class="row g-4 text-center">
            <div class="col-12">
                <h1>404 Not Found</h1>
            </div>
            <div class="col-12">
                <router-link class="btn btn-lg btn-primary" :to="{ name: 'dashboard' }">ダッシュボードに戻る</router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';

    onMounted(() => {
        overlay.hide();
    });

    const overlay = useOverlayStore();
</script>