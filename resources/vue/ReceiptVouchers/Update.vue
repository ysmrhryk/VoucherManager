<template>
    <div class="row g-4">
        <div class="col-2">
            <button class="btn btn-primary btn-sm rounded-0 w-100" @click="addLine()">行追加</button>
        </div>
        <div class="col-2">
            <button class="btn btn-warning btn-sm rounded-0 w-100" @click="reset()">リセット</button>
        </div>
        <div class="col-2">
            <button class="btn btn-sm rounded-0 w-100" disabled="true">-</button>
        </div>
        <div class="col-2">
            <button class="btn btn-sm rounded-0 w-100" disabled="true">-</button>
        </div>
        <div class="col-2">
            <button class="btn btn-sm btn-danger rounded-0 w-100" @click="destroy()">伝票を削除</button>
        </div>
        <div class="col-2">
            <button class="btn btn-sm btn-success rounded-0 w-100" @click="update()">
                <Spinner :sm="true" :show="isLoading.voucher" />
                <template v-if="!isLoading.voucher">更新</template>
            </button>
        </div>

        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-12">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">伝票日付*</span>
                        <input type="date" class="form-control rounded-0 outline-none" v-model="voucher.date">
                    </div>
                </div>

                <div class="col-md-12">
                    <label>社内向け備考欄</label>
                    <textarea class="form-control outline-none rounded-0" rows="4" v-model.lazy="voucher.internal_note"></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">顧客番号*</span>
                        <input type="text" class="form-control rounded-0 outline-none" v-model.lazy="voucher.client.code" @change="getClient({ code: voucher.client.code })">
                        <button class="btn btn-outline-dark rounded-0" :disabled="isLoading.client" @click="clientsModalRef.show();">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-12">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">顧客情報</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th class="w-25 text-end">名前</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client_name }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">郵便番号</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client_postal }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">住所</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client_address }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">電話番号</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client_tel }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">FAX番号</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client_fax }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">取引形態</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client.transaction_type.value }}</template>
                            </td></tr>
                            <tr><th class="w-25 text-end">支払方法</th><td>
                                <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                <template v-else>{{ voucher.client.payment_method.value }}</template>
                            </td></tr>
                            <tr>
                                <th class="w-25 text-end">締め区分</th>
                                <td>
                                    <template v-if="isLoading.client"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></template>
                                    <template v-else>
                                        {{ voucher.client.billing_cycle_type.value }}
                                        <template v-if="voucher.client.billing_day">(毎月{{ voucher.client.billing_day }}日)</template>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12"><hr></div>

        <div class="col-12">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr class="text-center">
                        <th style="width: 12%;">支払方法</th>
                        <th style="width: 36%;">内容</th>
                        <th style="width: 10%;">金額</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(line, index) in voucher.bodies" :key="line.localUuid">
                        <tr>
                            <td>
                                <select 
                                    class="form-select border-0 outline-none rounded-0" 
                                    v-model.lazy="line.payment_method_id"
                                    @change="recalculation(index)"
                                >
                                    <option 
                                        v-for="payment in payment_methods"
                                        :key="payment.id"
                                        :value="payment.id"
                                    >
                                        {{ payment.value }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input 
                                    type="text" 
                                    class="form-control border-0 outline-none rounded-0" 
                                    v-model.lazy="line.content"
                                    @change="recalculation(index)"
                                    placeholder="-"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    class="form-control border-0 text-end outline-none no-spin-btn rounded-0" 
                                    v-model.lazy="line.amount"
                                    @change="recalculation(index)"
                                    placeholder="0"
                                >
                            </td>
                            <td class="p-0 text-center" style="vertical-align: middle;">
                                <button @click="moveUpLine(index)" class="btn btn-sm border-0" :disabled="index === 0"><i class="fa-solid fa-caret-up"></i></button>
                                <button @click="moveDownLine(index)" class="btn btn-sm border-0" :disabled="index === voucher.bodies.length - 1"><i class="fa-solid fa-caret-down"></i></button>
                                <button @click="removeLine(index)" class="btn btn-sm border-0"><i class="fa-solid fa-delete-left"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">合計</th>
                        <td class="text-end pe-3">{{ formatNumber(Number(voucher.total_amount)) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <ClientsModal ref="clientsModalRef" @select="getClient" />
</template>

<script setup lang="ts">
    import { onMounted, ref, computed } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'
    import { useRoute, useRouter } from 'vue-router';
    import Request from '@/utils/request'
    import type { NameSuffix, TaxRate, PaymentMethod } from '@/interfaces/Index';
    import { formatNumber } from '@/utils/format';
    import ClientsModal from '@/vue/Components/Modal/ClientsModal.vue';
    import Spinner from '../Components/Elements/Spinner.vue';

    onMounted(async () => {
        [
            name_suffixes.value,
            payment_methods.value,
            tax_rates.value
        ] = await Promise.all([
            Request.name_suffixes(),
            Request.payment_methods(),
            Request.tax_rates()
        ]);

        await show();

        overlay.hide();
    });

    const clientsModalRef = ref();

    const overlay = useOverlayStore();
    const isLoading = ref({
        voucher: false,
        client: false,
        user: false,
    });
    const route = useRoute();
    const name_suffixes = ref<NameSuffix[]>();
    const payment_methods = ref<PaymentMethod[]>();
    const tax_rates = ref<TaxRate[]>();
    const router = useRouter();

    const formatted = computed(() => {
        return {
            'date': voucher.value.date,
            'internal_note': voucher.value.internal_note,
            'client_id': voucher.value.client_id,
            'bodies': voucher.value.bodies.map((line, index) => ({
                ...line,
                'line_number': index + 1,
            }))
        }
    });

    interface Voucher {
        id: string | null,
        date: string | null,
        internal_note: string | null,
        client_id: number | null,
        client_name: string | null,
        client_postal: string | null,
        client_address: string | null,
        client_tel: string | null,
        client_fax: string | null,
        total_amount: number,
        client: { 
            code: null | string,
            billing_day: number | null,
            transaction_type: { value: string | null },
            billing_cycle_type: { value: string | null },
            payment_method: { value: string | null },
        },
        user: { code: null | string },
        name_suffix: { value: string | null },
        bodies: Line[]
    }

    interface Line {
        localUuid: string,
        id: number | null,
        payment_method_id: number,
        line_number: number | null,
        amount: number | null,
        tax_rate_id: number | null,
        content: string | null,
    }

    const voucher = ref<Voucher>({
        id: null,
        date: null,
        internal_note: null,
        client_id: null,
        client_name: null,
        client_postal: null,
        client_address: null,
        client_tel: null,
        client_fax: null,
        total_amount: 0,
        client: { 
            code: null,
            billing_day: null,
            transaction_type: { value: null },
            billing_cycle_type: { value: null },
            payment_method: { value: null },
        },
        user: { code: null },
        name_suffix: { value: null },
        bodies: []
    });

    const show = async () => {
        isLoading.value.voucher = true;

        try{
            let res = await api.get(`/receipt-vouchers/${route.params.id}`);

            voucher.value = <Voucher>res.data;

            voucher.value.bodies = voucher.value.bodies.map(body => {
                return { ...body, localUuid: crypto.randomUUID() }
            });
        }catch(err){
            // エラー処理は後で書くかも

            alert('読み込み時にエラーが発生しました。\n再度お試しください。');
        }finally{
            isLoading.value.voucher = false;
        }
    }

    const update = async () => {
        if(!window.confirm('伝票を更新しますか？')) return;

        isLoading.value.voucher = true;

        try{
            let res = await api.put(`/receipt-vouchers/${route.params.id}`, formatted.value);

            alert('伝票を更新しました');
        }catch(err){
            // エラーのちゃんとした処理は後で書きたい

            alert('エラーが発生しました。\n更新できませんでした。');
        }finally{
            isLoading.value.voucher = false;
        }
    }

    const destroy = async () => {
        if(!window.confirm('伝票を削除しますか？\n削除された伝票は売上から除外され、復元することはできません。')) return;

        isLoading.value.voucher = true;

        try{
            let res = await api.delete(`/receipt-vouchers/${route.params.id}`);

            alert('伝票を削除しました');

            router.push({ name: 'receipt_vouchers.index' });
        }catch(err){
            // エラー処理は後で書きたい

            alert('エラーが発生しました。\n削除できませんでした。');
        }finally{
            isLoading.value.voucher = false;
        }
    }

    const reset = () => {
        if(!window.confirm('入力中の内容を破棄して元の状態に戻しますか？')) return;

        show();
    }

    const recalculation = (index?: number) => {
        if(index !== undefined){
            let line = voucher.value.bodies[index];

            if(line !== undefined){
                if(line.amount) line.amount = Math.abs(Number(line.amount));
            }
        }

        voucher.value.total_amount = 0;

        voucher.value.bodies.forEach((line) => {
            voucher.value.total_amount += Number(line.amount);
        });
    }

    const addLine = (
        payment_method_id?: number,
        amount?: number | null,
        tax_rate_id?: number | null,
        content?: string | null
    ) => {
        voucher.value.bodies.push({
            localUuid: crypto.randomUUID(),
            id: null,
            line_number: null,

            payment_method_id: payment_method_id || 1,
            amount: amount || null,
            tax_rate_id: tax_rate_id || 1,
            content: content || null
        });
    }

    const removeLine = (index: number) => {
        voucher.value.bodies.splice(index, 1);

        recalculation();
    }

    const moveUpLine = (index: number) => {
        const temp = [
            voucher.value.bodies[index],
            voucher.value.bodies[index-1]
        ];

        if(temp[0] !== undefined && temp[1] !== undefined){
            voucher.value.bodies[index-1] = temp[0];
            voucher.value.bodies[index] = temp[1];
        }
    }

    const moveDownLine = (index: number) => {
        const temp = [
            voucher.value.bodies[index],
            voucher.value.bodies[index+1]
        ];

        if(temp[0] !== undefined && temp[1] !== undefined){
            voucher.value.bodies[index+1] = temp[0];
            voucher.value.bodies[index] = temp[1];
        }
    }

    const getClient = async (arg: { id?: number | null, code?: string | null }) => {
        isLoading.value.client = true;

        const response = await Request.clients.show(arg);

        if(response){
            voucher.value.client_id = response.id;
            voucher.value.client_name = response.name;
            voucher.value.client_postal = response.postal;
            voucher.value.client_address = response.address;
            voucher.value.client_tel = response.tel;
            voucher.value.client_fax = response.fax;
            voucher.value.client = response;
        }else{
            voucher.value.client_id = null;
            voucher.value.client_name = null;
            voucher.value.client_postal = null;
            voucher.value.client_address = null;
            voucher.value.client_tel = null;
            voucher.value.client_fax = null;
            voucher.value.client = {
                code: null,
                billing_day: null,
                transaction_type: { value: null },
                billing_cycle_type: { value: null },
                payment_method: { value: null }
            };
        }

        isLoading.value.client = false;
    }
</script>