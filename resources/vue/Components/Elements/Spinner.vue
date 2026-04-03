<template>
    <template v-if="show">
        <span class="spinner-border" :class="{ 'spinner-border-sm': sm }" aria-hidden="true"></span>
    </template>
</template>

<script setup lang="ts">
    defineProps({
        show: {
            type: Boolean,
            required: false,
            default: true
        },
        sm: {
            type: Boolean,
            required: false,
            default: false
        }
    });
</script>