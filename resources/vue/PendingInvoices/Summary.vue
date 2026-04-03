<template>
    <div class="row g-4">
        <div class="col-12">
            <div class="row g-4">
                <!-- 検索パラメーター -->
                <div class="col-4">
                    締め日
                    <Input type="date" v-model="params.date" />
                </div>

                <div class="col-4">
                    支払方法
                    <select 
                        class="form-select rounded-0 outline-none" 
                        v-model="params.payment_method_id"
                    >
                        <option :value="null">-</option>
                        <option 
                            v-for="payment_method in payment_methods" 
                            :key="payment_method.id" 
                            :value="payment_method.id"
                        >{{ payment_method.value }}</option>
                    </select>
                </div>

                <div class="col-4">
                    取引区分
                    <select 
                        class="form-select rounded-0 outline-none" 
                        v-model="params.transaction_type_id"
                    >
                        <option :value="null">-</option>
                        <option 
                            v-for="transaction_type in transaction_types" 
                            :key="transaction_type.id" 
                            :value="transaction_type.id"
                        >{{ transaction_type.value }}</option>
                    </select>
                </div>

                <div class="col-4">
                    締め区分
                    <select 
                        class="form-select rounded-0 outline-none" 
                        v-model="params.billing_cycle_type_id" 
                        @change="if(params.billing_cycle_type_id === 2) params.billing_day = null;"
                    >
                        <option :value="null">-</option>
                        <option 
                            v-for="billing_cycle_type in billing_cycle_types" 
                            :key="billing_cycle_type.id" 
                            :value="billing_cycle_type.id"
                        >{{ billing_cycle_type.value }}</option>
                    </select>
                </div>

                <div class="col-4">
                    日付
                    <Input type="number" v-model="params.billing_day" :disabled="!(params.billing_cycle_type_id === 2)" />
                </div>

                <div class="col-4">
                    ログイン許可
                    <select class="form-select rounded-0 outline-none" v-model="params.allow_login">
                        <option :value="null">-</option>
                        <option :value="true">許可</option>
                        <option :value="false">未許可</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" v-model="params.ignore_if_empty">
                        請求書の内容がない得意先は非表示
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="row g-4">
                <!-- ボタン -->
                <div class="col-3"></div>
                <div class="col-3">
                    <Button 
                        class="btn-success w-100" 
                        @click="fetchSummary()" 
                        :isLoading="isLoading.summary"
                    >
                        <i class="fa-solid fa-arrow-rotate-right"></i> 計算結果を更新
                    </Button>
                </div>
                <div class="col-3">
                    <Button 
                        class="btn-dark w-100" 
                        @click="request_pdf()" 
                        :isLoading="isLoading.request_pdf" 
                        :disabled="checked_client_ids.length === 0"
                    >
                        <i class="fa-solid fa-print"></i> 請求予定額を印刷
                    </Button>
                </div>
                <div class="col-3">
                    <Button 
                        class="btn-primary w-100" 
                        @click="issue()" 
                        :isLoading="isLoading.issue" 
                        :disabled="checked_client_ids.length === 0"
                    >
                        <i class="fa-solid fa-circle-check"></i> 請求書を発行
                    </Button>
                </div>
            </div>
        </div>

        <div class="col-12">
            <table class="table table-sm table-bordered mb-0 text-nowrap">
                <thead class="text-center">
                    <tr>
                        <th rowspan="2" class="align-middle">#</th>
                        <th>顧客</th>
                        <th>前回請求額</th>
                        <th>御入金額</th>
                        <th>繰越額</th>
                        <th>今回分</th>
                        <th>今回合計</th>
                    </tr>
                    <tr>
                        <th>最終請求日</th>
                        <th></th>
                        <th>返金額</th>
                        <th></th>
                        <th>消費税</th>
                        <th>今回請求額</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(row, index) in summary" :key="row.client_id">
                        <tr :class="index%2 === 0 && 'table-active'">
                            <td rowspan="2" class="text-center align-middle"><input type="checkbox" :value="row.client_id" v-model="checked_client_ids"></td>
                            <td class="d-flex">
                                <div class="pe-1">[{{ row.client_code }}]</div>
                                <div>{{ row.client_name }}</div>
                            </td>
                            <td class="text-end">{{ formatNumber(row.previous_invoice_amount) }}</td>
                            <td class="text-end">{{ formatNumber(row.total_receipt_amount) }}</td>
                            <td class="text-end">{{ formatNumber(row.carried_forward_amount) }}</td>
                            <td class="text-end">{{ formatNumber(row.total_net_amount) }}</td>
                            <td class="text-end">{{ formatNumber(row.total_gross_amount) }}</td>
                        </tr>
                        <tr :class="index%2 === 0 && 'table-active'">
                            <td>{{ row.date }}</td>
                            <td></td>
                            <td class="text-end">{{ formatNumber(row.total_refund_amount) }}</td>
                            <td></td>
                            <td class="text-end">{{ formatNumber(row.total_tax_amount) }}</td>
                            <td class="text-end">{{ formatNumber(row.current_invoice_amount) }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <PdfModal ref="PdfModalRef" />
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'
    import { formatNumber } from '@/utils/format';
    import Input from '../Components/Elements/Input.vue';
    import Button from '../Components/Elements/Button.vue';
    import Request from '@/utils/request';
    import type { PaymentMethod, TransactionType, BillingCycleType } from '@/interfaces/Index';
    import PdfModal from '../Components/Modal/PdfModal.vue';

    onMounted(async () => {
        [
            payment_methods.value,
            transaction_types.value,
            billing_cycle_types.value
        ] = await Promise.all([
            Request.payment_methods(),
            Request.transaction_types(),
            Request.billing_cycle_types()
        ]);

        await fetchSummary();

        overlay.hide();
    });

    const overlay = useOverlayStore();
    const PdfModalRef = ref();

    const payment_methods = ref<PaymentMethod[]>();
    const transaction_types = ref<TransactionType[]>();
    const billing_cycle_types = ref<BillingCycleType[]>();

    interface Params {
        date: string,
        billing_cycle_type_id: number | null,
        billing_day: number | null,
        payment_method_id: number | null,
        transaction_type_id: number | null,
        user_id: number | null,
        allow_login: boolean | null,
        ignore_if_empty: boolean
    }

    const params = ref<Params>({
        date: new Date().toLocaleDateString('ja-JP', { year: "numeric", month: "2-digit", day: "2-digit" }).replaceAll('/', '-'),
        billing_cycle_type_id: null,
        billing_day: null,
        payment_method_id: null,
        transaction_type_id: null,
        user_id: null,
        allow_login: null,
        ignore_if_empty: true,
    });

    interface Summary {
        client_id: number,
        client_code: string,
        client_name: string,
        date: string,
        previous_invoice_amount: number,
        total_receipt_amount: number,
        total_refund_amount: number,
        carried_forward_amount: number,
        total_net_amount: number,
        total_tax_amount: number,
        total_gross_amount: number,
        current_invoice_amount: number,
    }

    const summary = ref<Summary[]>();

    const checked_client_ids = ref<number[]>([]);

    const isLoading = ref({
        request_pdf: false,
        summary: false,
        issue: false,
    });

    const fetchSummary = async () => {
        isLoading.value.summary = true;
        checked_client_ids.value = [];

        try{
            let res = await api.post('/pending-invoices/summary', params.value);

            summary.value = res.data;
        }catch(err){
            //
        }finally{
            isLoading.value.summary = false;
        }
    }

    const request_pdf = async () => {
        isLoading.value.request_pdf = true;

        try{
            let res = await api.post('/pending-invoices/request-pdf', {
                date: params.value.date,
                client_ids: checked_client_ids.value
            });

            PdfModalRef.value.show(`/pdf/pending-invoices/${res.data.uuid}`);
        }catch(err){
            //
        }finally{
            isLoading.value.request_pdf = false;
        }
    }

    const issue = async () => {
        isLoading.value.issue = true;

        try{
            let res = await api.post('/pending-invoices/issue', {
                date: params.value.date,
                client_ids: checked_client_ids.value
            });

            alert('請求処理を完了しました');

            fetchSummary();
        }catch(err){
            //
        }finally{
            isLoading.value.issue = false;
        }
    }
</script>