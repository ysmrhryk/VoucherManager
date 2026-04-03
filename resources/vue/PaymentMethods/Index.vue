<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="row g-4">
                    <div class="col-12">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>内容</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(payment_mathod, index) in payment_methods" :key="payment_mathod.id">
                                    <td>{{ payment_mathod.sort_id }}</td>
                                    <td>{{ payment_mathod.value }}</td>
                                    <td class="p-0 text-center" style="vertical-align: middle;">
                                        <button @click="moveUp(index)" class="btn btn-sm border-0" :disabled="index === 0"><i class="fa-solid fa-caret-up"></i></button>
                                        <button @click="moveDown(index)" class="btn btn-sm border-0" :disabled="index === payment_methods.length - 1"><i class="fa-solid fa-caret-down"></i></button>
                                        <button @click="remove(index, payment_mathod.id, payment_mathod.value)" class="btn btn-sm border-0"><i class="fa-solid fa-delete-left"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-3">
                        <button class="btn btn-success rounded-0 btn-sm w-100" @click="reorder()">並び順を更新</button>
                    </div>
                    <div class="col-3">
                        <router-link 
                            :to="{ name: 'payment_methods.store' }"
                            class="btn btn-primary rounded-0 btn-sm w-100"
                        >新しい項目を追加</router-link>
                    </div>
                </div>
            </div>
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

    const overlay = useOverlayStore();

    interface PaymentMethod {
        id: number,
        sort_id: number | null,
        value: string,
    }

    const payment_methods = ref<PaymentMethod[]>([]);
    const isLoading = ref<boolean>(false);

    const fetchIndex = async () => {
        isLoading.value = true;

        await api.get('/payment-methods')
        .then((res) => {
            payment_methods.value = res.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
    }

    const reorder = async () => {
        if(!window.confirm(`並び順を更新しますか？`)) return;

        isLoading.value = true;

        try{
            let params: number[] = payment_methods.value.map((item) => {
                return item.id;
            });

            let res = await api.post(`/payment-methods/reorder`, { ids: params });

            payment_methods.value = res.data;

            alert('更新しました');
        }catch(err){
            //
        }finally{
            isLoading.value = false;
        }
    }

    const moveUp = (index: number) => {
        const temp = [
            payment_methods.value[index],
            payment_methods.value[index-1]
        ];

        if(temp[0] !== undefined && temp[1] !== undefined){
            payment_methods.value[index-1] = temp[0];
            payment_methods.value[index] = temp[1];
        }
    }

    const moveDown = (index: number) => {
        const temp = [
            payment_methods.value[index],
            payment_methods.value[index+1]
        ];

        if(temp[0] !== undefined && temp[1] !== undefined){
            payment_methods.value[index+1] = temp[0];
            payment_methods.value[index] = temp[1];
        }
    }

    const remove = async (index: number, id: number, value: string) => {
        if(!window.confirm(`${value} を削除しますか？`)) return;

        isLoading.value = true;

        try{
            let res = await api.delete(`/payment-methods/${id}`);

            payment_methods.value.splice(index, 1);

            alert('削除しました');
        }catch(err){
            //
        }finally{
            isLoading.value = false;
        }
    }
</script>