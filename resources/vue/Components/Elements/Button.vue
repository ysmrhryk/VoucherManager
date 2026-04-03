<template>
    <button :class="class" class="btn rounded-0" :disabled="isLoading">
        <template v-if="!(isLoading)"><slot></slot></template>
        <Spinner sm :show="isLoading" />
    </button>
</template>

<script setup lang="ts">
    import Spinner from './Spinner.vue';

    defineProps<{
        isLoading?: boolean,
        class?: string
    }>();
</script>