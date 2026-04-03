<template>
    <div class="container">
        <div class="row g-4">
            <div class="col-8">
                <div class="input-group border-bottom">
                    <span class="input-group-text rounded-0 bg-white border-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control outline-none rounded-0 border-0" placeholder="検索キーワードを入力" v-model="filter.keywords" @change="fetchIndex()">
                </div>
            </div>
            <div class="col-4">
                <router-link :to="{ name: 'service_vouchers.store' }" class="btn btn-primary w-100 rounded-0">新規追加</router-link>
            </div>

            <div class="col-12">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>伝票日付</th>
                            <th>得意先名</th>
                            <th>担当者</th>
                            <th>合計</th>
                            <th>作成日</th>
                            <th>更新日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in data" :key="row.id">
                            <td>{{ row.date }}</td>
                            <td>
                                <div class="d-flex">
                                    <div class="pe-1">[{{ row.client.code }}]</div>
                                    <div>
                                        {{ row.client_name }}
                                        <template v-if="row.extension_client_name">
                                            <br>{{ row.extension_client_name }} <small class="text-muted">{{ row.name_suffix.value }}</small>
                                        </template>
                                        <template v-else> <small class="text-muted">{{ row.client.name_suffix.value }}</small></template>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div class="pe-1">[{{ row.user.code }}]</div>
                                    <div>{{ row.user_name }}</div>
                                </div>
                            </td>
                            <td class="text-end" :class="row.total_net_amount < 0 && 'text-danger'">{{ formatNumber(row.total_net_amount) }}</td>
                            <td>{{ formatDate(row.created_at) }}</td>
                            <td>{{ formatDate(row.updated_at) }}</td>
                            <td class="p-0 text-center" style="vertical-align: middle;">
                                <router-link 
                                    :to="{ name: 'service_vouchers.update', params: { id: row.id } }"
                                    class="btn btn-sm rounded-0"
                                    title="編集"
                                ><i class="fa-regular fa-pen-to-square"></i></router-link>

                                <Button class="btn-sm border-0 outline-none" @click="PdfModalRef.show(`/pdf/service-vouchers/${row.id}`);"><i class="fa-solid fa-print"></i></Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="col-12 text-center">
                <div><small class="text-muted">{{ formatNumber(response?.total) }} 件中 {{ formatNumber(response?.to) }} 件を表示中</small></div>
                <button class="btn btn-dark rounded-0" @click="fetchMore()" :disabled="response?.current_page === response?.last_page || isLoading">
                    <Spinner :sm="true" :show="isLoading" />
                    <template v-if="!isLoading">
                        <template v-if="response?.current_page === response?.last_page">これで全てです</template>
                        <template v-else>もっと読み込む</template>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <PdfModal ref="PdfModalRef" />
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'
    import stringify from '@/utils/query';
    import { formatDate, formatNumber } from '@/utils/format'
    import Spinner from '@/vue/Components/Elements/Spinner.vue';
    import PdfModal from '../Components/Modal/PdfModal.vue';
    import Button from '../Components/Elements/Button.vue';

    onMounted(async () => {
        await fetchIndex();

        overlay.hide();
    });

    const overlay = useOverlayStore();
    const PdfModalRef = ref();

    interface Data {
        id: string,
        client_id: number,
        client_name: string,
        extension_client_name: string | null,
        created_at: string,
        updated_at: string,
        customer_note: string | null,
        date: string,
        internal_note: string | null,
        invoice_id: string | null,
        total_net_amount: number,
        user_id: number,
        user_name: string,
        
        bodies: object,
        footers: object,

        client: { code: string, name_suffix: { value: string | null } },
        user: { code: string },
        name_suffix: { value: string }
    }

    interface Response {
        total: number,
        to: number,
        current_page: number,
        last_page: number,
    }

    interface Filter {
        keywords: string | null,
    }

    const filter = ref<Filter>({
        keywords: null,
    });

    const isLoading = ref<boolean>(false);
    const page = ref<number>(1);
    const data = ref<Data[]>([]);
    const response = ref<Response>({
        total: 0,
        to: 0,
        current_page: 0,
        last_page: 0
    });

    const fetchIndex = async () => {
        isLoading.value = true;
        page.value = 1;

        await api.get(`/service-vouchers${stringify(filter.value, page.value)}`)
        .then((res) => {
            response.value = <Response>res.data;
            data.value = <Data[]>res.data.data;
        })
        .catch(() => {
            //
        })
        .finally(() => {
            isLoading.value = false;
        });
    }

    const fetchMore = async () => {
        isLoading.value = true;
        page.value++;

        await api.get(`/service-vouchers${stringify(filter.value, page.value)}`)
        .then((res) => {
            response.value = <Response>res.data;
            data.value = data.value.concat(<Data[]>res.data.data);
        })
        .catch(() => {
            //
        })
        .finally(() => {
            isLoading.value = false;
        });
    }
</script>