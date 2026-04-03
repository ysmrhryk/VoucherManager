<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-6">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.name">
                            <label>名称</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.postal">
                            <label>郵便番号</label>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.address">
                            <label>住所</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.tel">
                            <label>電話番号</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.fax">
                            <label>FAX番号</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.bank_name">
                            <label>銀行名</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.branch_name">
                            <label>支店名</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.account_number">
                            <label>口座番号</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.account_type">
                            <label>口座種目</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                            <input type="url" class="form-control" placeholder="" v-model="data.account_holder">
                            <label>口座名義</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">最終更新: {{ formatDate(data.updated_at) }}</small>
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-success w-25" @click="update()" :disabled="isLoading">
                            <template v-if="isLoading"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span></template>
                            保存
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
    import api from '@/ts/api'
    import { formatDate } from '@/utils/format';

    onMounted(async () => {
        await show();

        overlay.hide();
    });

    const overlay = useOverlayStore();

    interface Data {
        name: string | null,
        postal: string | null,
        address: string | null,
        tel: string | null,
        fax: string | null,
        bank_name: string | null,
        branch_name: string | null,
        account_type: string | null,
        account_number: string | null,
        account_holder: string | null,
        updated_at: string | null,
        is_tax_exempt: boolean | null
    }

    const data = ref<Data>({
        name: null,
        postal: null,
        address: null,
        tel: null,
        fax: null,
        bank_name: null,
        branch_name: null,
        account_type: null,
        account_number: null,
        account_holder: null,
        updated_at: null,
        is_tax_exempt: null,
    });

    const isLoading = ref<boolean>(false);

    const show = async () => {
        isLoading.value = true;

        await api.get('/settings')
        .then((res) => {
            data.value = res.data;
        })
        .catch((err) => {
            //
        })
        .finally(() => {
            isLoading.value = false;
        });
    }

    const update = async () => {
        isLoading.value = true;

        await api.put('/settings', data.value)
        .then((res) => {
            data.value = res.data;
        })
        .catch((err) => {
            //
        })
        .finally(() => {
            isLoading.value = false;
        });
    }
</script>