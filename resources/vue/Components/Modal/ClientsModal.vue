<template>
    <Modal ref="modalRef" title="顧客検索">
        <template v-slot:body>
            <div class="row g-4">
                <div class="col-12">
                    <div class="input-group border-bottom">
                        <span class="input-group-text rounded-0 border-0 bg-white">
                            <template v-if="isLoading"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span></template>
                            <template v-else><i class="fa-solid fa-magnifying-glass"></i></template>
                        </span>
                        <input 
                            type="text" 
                            class="form-control rounded-0 border-0 outline-none" 
                            placeholder="検索キーワードを入力" 
                            v-model="keywords" 
                            @change="fetchIndex()"
                        >
                    </div>
                </div>
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>顧客番号</th>
                                <th>名称</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="client in clients">
                                <td>{{ client.code }}</td>
                                <td>{{ client.name }}</td>
                                <td class="p-0 text-center" style="vertical-align: middle;">
                                    <button class="btn btn-sm btn-link rounded-0 w-100" @click="emits('select', { id: client.id }); hide();">
                                        使用する <i class="fa-solid fa-angles-right"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12 text-center">
                    <div><small class="text-muted">{{ formatNumber(pagination?.total || 0) }} 件中 {{ formatNumber(pagination?.to || 0) }} 件を表示中</small></div>
                    <button class="btn btn-dark" @click="fetchMore()" :disabled="pagination?.current_page === pagination?.last_page || isLoading">
                        <template v-if="isLoading"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span></template>
                        <template v-else>
                            <template v-if="pagination?.current_page === pagination?.last_page">これで全てです</template>
                            <template v-else>もっと読み込む</template>
                        </template>
                    </button>
                </div>
            </div>
        </template>
        <template v-slot:footer></template>
    </Modal>
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import Modal from './Modal.vue';
    import Request from '@/utils/request';
    import { formatNumber } from '@/utils/format';
    import type { Client, Pagination } from '@/interfaces/Index';

    const emits = defineEmits(['select']);

    onMounted(async () => {
        await fetchIndex();
    });

    const isLoading = ref<boolean>(false);
    const keywords = ref<string>('');
    const page = ref<number>(1);
    const modalRef = ref();

    const pagination = ref<Pagination>();
    const clients = ref<Client[]>([]);

    const fetchIndex = async () => {
        isLoading.value = true;
        page.value = 1;

        const response = await Request.clients.index({
            keywords: keywords.value,
            page: page.value
        });

        pagination.value = response;
        clients.value = response.data;

        isLoading.value = false;
    }

    const fetchMore = async () => {
        isLoading.value = true;
        page.value++;

        const response = await Request.clients.index({
            keywords: keywords.value,
            page: page.value
        });

        pagination.value = response;
        clients.value = clients.value.concat(response.data);

        isLoading.value = false;
    }

    const hide = () => modalRef.value.hide();
    const show = () => modalRef.value.show();

    defineExpose({show, hide});
</script>