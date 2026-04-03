<template>
    <input :type="type" :class="class" class="form-control rounded-0 outline-none" v-model="value">
</template>

<script setup lang="ts">
    const value = defineModel({ required: true });

    defineProps({
        type: {
            type: String,
            required: false,
            default: 'text'
        },
        class: {
            type: String,
            required: false,
        },
    });
</script>