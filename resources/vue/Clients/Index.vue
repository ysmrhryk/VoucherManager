<template>
    <div class="container">
        <div class="row g-4">
            <div class="col-8">
                <div class="input-group border-bottom">
                    <span class="input-group-text rounded-0 bg-white border-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <Input class="border-0" placeholder="検索キーワードを入力" v-model="filter.keywords" @change="fetchIndex()" />
                </div>
            </div>
            <div class="col-4">
                <router-link :to="{ name: 'clients.store' }" class="btn btn-primary w-100 rounded-0">新規追加</router-link>
            </div>

            <div class="col-12">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>呼出番号</th>
                            <th>名称</th>
                            <th>メールアドレス</th>
                            <th>ログイン許可</th>
                            <th>追加日</th>
                            <th>更新日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in data" :key="row.id">
                            <td>{{ row.code }}</td>
                            <td>{{ row.name }}</td>
                            <td>{{ row.email }}</td>
                            <td>{{ row.allow_login }}</td>
                            
                            <td>{{ formatDate(row.created_at) }}</td>
                            <td>{{ formatDate(row.updated_at) }}</td>
                            <td class="p-0 text-center" style="vertical-align: middle;">
                                <router-link 
                                    :to="{ name: 'clients.update', params: { id: row.id } }"
                                    class="btn btn-sm rounded-0"
                                    title="編集"
                                ><i class="fa-regular fa-pen-to-square"></i></router-link>
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
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'
    import stringify from '@/utils/query';
    import { formatDate, formatNumber } from '@/utils/format'
    import Input from '../Components/Elements/Input.vue';
    import Spinner from '@/vue/Components/Elements/Spinner.vue';

    onMounted(async () => {
        await fetchIndex();

        overlay.hide();
    });

    const overlay = useOverlayStore();

    interface Data {
        id: number,
        code: string,
        name: string,
        email: string | null,
        allow_login: boolean,
        created_at: string,
        updated_at: string
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

        await api.get(`/clients${stringify(filter.value, page.value)}`)
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

        await api.get(`/clients${stringify(filter.value, page.value)}`)
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