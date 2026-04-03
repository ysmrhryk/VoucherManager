<template>
    <div class="row g-4">
        <div class="col-12">
            <input type="text" class="form-control">
        </div>
        <div class="col-12 overflow-auto">
            <table class="table table-bordered">
                <thead>
                    <tr class="text-nowrap">
                        <th>created_at</th>
                        <th>user_id</th>
                        <th>Method</th>
                        <th>path</th>
                        <th>IP Address</th>
                        <th>UserAgent</th>
                        <th>StatusCode</th>
                        <th>ProcessingTime (ms)</th>
                        <th>Referer</th>
                        <th>payload</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in list.data" :key="row.id">
                        <td class="text-nowrap">{{ row.created_at }}</td>
                        <td class="text-nowrap">{{ row.user_id }}</td>
                        <td class="text-nowrap">{{ row.method }}</td>
                        <td class="text-nowrap">{{ row.path }}</td>
                        <td class="text-nowrap">{{ row.ip_address }}</td>
                        <td class="text-nowrap">{{ row.user_agent }}</td>
                        <td class="text-nowrap">{{ row.status_code }}</td>
                        <td class="text-nowrap">{{ row.processing_time }}</td>
                        <td class="text-nowrap">{{ row.referer }}</td>
                        <td><code><pre>{{ JSON.stringify(row.payload, null, 2) }}</pre></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'

    onMounted(async () => {
        await fetchIndex();

        overlay.hide();
    });

    interface Data {
        id: number,
        created_at: string,
        user_id: number,
        method: string,
        path: string,
        ip_address: string,
        user_agent: string,
        payload: object,
        status_code: string,
        processing_time: string,
        referer: string,
    }

    interface Response {
        data?: Data[],
        // 他のキーが増える予定なので一旦この状態に
    }

    const overlay = useOverlayStore();
    const isLoading = ref<boolean>(false);
    const list = ref<Response>([]);

    const fetchIndex = async () => {
        isLoading.value = true;

        await api.get('/request-logs')
        .then((res) => {
            list.value = res.data;
        })
        .catch((err) => {
            //
        })
        .finally(() => {
            isLoading.value = false;
        });
    }
</script>