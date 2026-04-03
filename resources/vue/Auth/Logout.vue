<template>
    <div class="position-absolute top-50 start-50 translate-middle">
        <div class="spinner-border" role="status">
        </div>
    </div>
</template>

<script setup lang="ts">
    import api from '@/ts/api';
    import { onMounted } from 'vue';
    import { useRouter } from 'vue-router';

    onMounted(() => {
        api.post('/logout').then(() => {
            router.push({ name: 'login' });
        });
    });

    const router = useRouter();
</script>