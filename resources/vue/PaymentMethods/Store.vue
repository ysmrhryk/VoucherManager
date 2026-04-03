<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="row g-4">
                    <div class="col-12">
                        <label>支払方法項目名</label>
                        <Input v-model="value" />
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary rounded-0" @click="store()" :disabled="isLoading">
                            <Spinner :sm="true" :show="isLoading" />
                            <template v-if="!isLoading">登録</template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import Input from '../Components/Elements/Input.vue';
    import Spinner from '../Components/Elements/Spinner.vue';
    import api from '@/ts/api'
    import { useRouter } from 'vue-router';

    onMounted(() => {
        overlay.hide();
    });

    const overlay = useOverlayStore();
    const value = ref<string | null>(null);
    const isLoading = ref<boolean>(false);
    const router = useRouter();

    const store = async () => {
        isLoading.value = true;

        try{
            let res = await api.post('/payment-methods', {
                value: value.value
            });

            alert('登録しました');

            router.push({ name: 'payment_methods.index' });
        }catch(err){
            //
        }finally{
            isLoading.value = false;
        }
    }
</script>