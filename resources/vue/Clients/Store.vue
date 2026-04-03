<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="row g-4">
                    <div class="col-12">
                        <h4>基本情報</h4>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">呼出番号 *</span>
                            <Input v-model="client.code" />
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">名前 *</span>
                            <Input v-model="client.name" />
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">継承 *</span>
                            <select class="form-select rounded-0 outline-none" v-model="client.name_suffix_id">
                                <option :value="null">-</option>
                                <option v-for="name_suffix in name_suffixes" :key="name_suffix.id" :value="name_suffix.id">{{ name_suffix.id }}: {{ name_suffix.value }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <h4>詳細情報</h4>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">郵便番号</span>
                            <Input v-model="client.postal" />
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">住所</span>
                            <Input v-model="client.address" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">電話番号</span>
                            <Input type="tel" v-model="client.tel" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">FAX番号</span>
                            <Input type="tel" v-model="client.fax" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">メールアドレス</span>
                            <Input type="email" v-model="client.email" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">ウェブサイト</span>
                            <Input type="url" v-model="client.website" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text rounded-0">担当者</span>
                                    <input 
                                        type="text" 
                                        class="form-control rounded-0 outline-none" 
                                        v-model.lazy="client.user.code" 
                                        @change="getUser({ code: client.user.code })"
                                        placeholder="担当者コードを入力"
                                    >
                                    <button class="btn btn-outline-dark rounded-0" :disabled="isLoading.user" @click="usersModalRef.show();">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="border w-100 h-100 d-flex align-items-center ps-2 overflow-hidden">
                                    {{ client.user.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">初回前回請求額</span>
                            <Input type="number" v-model="client.initial_previous_invoice_amount" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">請求区分</span>
                            <select class="form-select rounded-0 outline-none" v-model="client.billing_cycle_type_id" @change="if(client.billing_cycle_type_id !== 2) client.billing_day = null;">
                                <option :value="null">-</option>
                                <option v-for="billing_cycle_type in billing_cycle_types" :key="billing_cycle_type.id" :value="billing_cycle_type.id">{{ billing_cycle_type.id }}: {{ billing_cycle_type.value }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">請求日</span>
                            <Input type="number" v-model="client.billing_day" :disabled="client.billing_cycle_type_id != 2" min="1" max="31" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">支払方法</span>
                            <select class="form-select rounded-0 outline-none" v-model="client.payment_method_id">
                                <option :value="null">-</option>
                                <option v-for="payment_method in payment_methods" :key="payment_method.id" :value="payment_method.id">{{ payment_method.value }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">取引区分</span>
                            <select class="form-select rounded-0 outline-none" v-model="client.transaction_type_id">
                                <option :value="null">-</option>
                                <option v-for="transaction_type in transaction_types" :key="transaction_type.id" :value="transaction_type.id">{{ transaction_type.id }}: {{ transaction_type.value }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" v-model="client.allow_login">
                            <label class="form-check-label">Web請求システムへのログインを許可する</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <router-link :to="{ name: 'clients.index' }" class="btn btn-outline-dark w-100 rounded-0"><i class="fa-solid fa-angles-left"></i> 一覧へ戻る</router-link>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-warning w-100 rounded-0" @click="reset()"><i class="fa-solid fa-eraser"></i> 入力中の内容をクリア</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-primary w-100 rounded-0" @click="store()" :disabled="isLoading.store">
                            <Spinner :sm="true" :show="isLoading.store" />
                            <template v-if="!isLoading.store">
                                <i class="fa-solid fa-floppy-disk"></i> 登録
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <UsersModal ref="usersModalRef" @select="getUser" />
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue'
    import Request from '@/utils/request'
    import api from '@/ts/api';
    import { useRouter } from 'vue-router'
    import { useOverlayStore } from '@/stores/overlay';
    import type { NameSuffix, PaymentMethod, TransactionType, BillingCycleType } from '@/interfaces/Index'
    import UsersModal from '../Components/Modal/UsersModal.vue';
    import Input from '../Components/Elements/Input.vue';
    import Spinner from '@/vue/Components/Elements/Spinner.vue';

    onMounted(async () => {
        [
            name_suffixes.value,
            payment_methods.value,
            transaction_types.value,
            billing_cycle_types.value
        ] = await Promise.all([
            Request.name_suffixes(),
            Request.payment_methods(),
            Request.transaction_types(),
            Request.billing_cycle_types()
        ]);

        overlay.hide();
    });

    const usersModalRef = ref();
    const billing_cycle_types = ref<BillingCycleType[]>([]);
    const payment_methods = ref<PaymentMethod[]>([]);
    const transaction_types = ref<TransactionType[]>([]);
    const name_suffixes = ref<NameSuffix[]>([]);
    const router = useRouter();
    const overlay = useOverlayStore();
    const isLoading = ref({
        user: false,
        store: false,
    });

    interface Client {
        code: string | null,
        name: string | null,
        postal: string | null,
        address: string | null,
        tel: string | null,
        fax: string | null,
        email: string | null,
        website: string | null,
        initial_previous_invoice_amount: number | null,
        billing_cycle_type_id: number | null,
        billing_day: number | null,
        payment_method_id: number | null,
        transaction_type_id: number | null,
        name_suffix_id: number | null,
        user_id: number | null,
        allow_login: boolean | null,
        user: {
            code: string | null,
            name: string | null,
        }
    }

    const init = (): Client => ({
        code: null,
        name: null,
        postal: null,
        address: null,
        tel: null,
        fax: null,
        email: null,
        website: null,
        initial_previous_invoice_amount: 0,
        billing_cycle_type_id: null,
        billing_day: null,
        payment_method_id: null,
        transaction_type_id: null,
        name_suffix_id: null,
        user_id: null,
        allow_login: false,

        user: { code: null, name: null }
    });

    const client = ref(init());

    const store = async () => {
        isLoading.value.store = true;

        try{
            let res = await api.post(`/clients`, client.value);

            alert('登録しました');

            router.push({ name: 'clients.update', params: { id: res.data.id } });
        }catch(err){
            // エラーのちゃんとした処理は後で書きたい

            alert('エラーが発生しました。\n登録できませんでした。');
        }finally{
            isLoading.value.store = false;
        }
    }

    const reset = () => {
        if(!window.confirm('入力中の内容を破棄しますか？')) return;

        client.value = init();
    }

    const getUser = async (arg: { id?: number | null, code?: string | null }) => {
        isLoading.value.user = true;

        const response = await Request.users.show(arg);

        if(response){
            client.value.user_id = response.id;
            client.value.user = response;
        }else{
            client.value.user_id = null;
            client.value.user = {
                code: null,
                name: null,
            };
        }

        isLoading.value.user = false;
    }
</script>