<template>
    <Modal ref="modalRef" title="請求予定額プレビュー" :isFullscreen="true">
        <template v-slot:body>
            <div class="w-100 h-100 p-2">
                <template v-if="embed_url">
                    <embed type="application/pdf" class="w-100 h-100" :src="embed_url" />
                </template>
            </div>
        </template>
        <template v-slot:footer>
            <Button class="btn-sm btn-dark" @click="hide()"><i class="fa-solid fa-xmark"></i> 閉じる</Button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
    import Modal from './Modal.vue';
    import { ref } from 'vue';
    import Button from '../Elements/Button.vue';

    const embed_url = ref<string | null>();
    const modalRef = ref();

    const show = (url: string) => {
        embed_url.value = url;

        modalRef.value.show();
    }

    const hide = () => {
        embed_url.value = null;

        modalRef.value.hide();
    }

    defineExpose({show, hide});
</script>