<template>
    <div class="row g-4">
        <div class="col-12"></div>
        <div class="col-12"></div>
        <div class="col-12">
            <template v-if="invoices.length">
                <table class="table table-sm table-bordered mb-0 text-nowrap">
                    <thead class="text-center">
                        <tr>
                            <th class="align-middle" rowspan="2">#</th>
                            <th>顧客</th>
                            <th>前回請求額</th>
                            <th>御入金額</th>
                            <th>繰越額</th>
                            <th>今回分</th>
                            <th>今回合計</th>
                            <th class="align-middle" rowspan="2">操作</th>
                        </tr>
                        <tr>
                            <th>請求日</th>
                            <th></th>
                            <th>返金額</th>
                            <th></th>
                            <th>消費税</th>
                            <th>今回請求額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(invoice, index) in invoices" :key="invoice.client_id">
                            <tr :class="index%2 === 0 && 'table-active'">
                                <td class="text-center align-middle" rowspan="2"><input type="checkbox" :value="invoice.client_id" v-model="checked_client_ids"></td>
                                <td class="d-flex">
                                    <div class="pe-1">[{{ invoice.client_code }}]</div>
                                    <div>{{ invoice.client_name }}</div>
                                </td>
                                <td class="text-end">{{ formatNumber(invoice.previous_invoice_amount) }}</td>
                                <td class="text-end">{{ formatNumber(invoice.total_receipt_amount) }}</td>
                                <td class="text-end">{{ formatNumber(invoice.carried_forward_amount) }}</td>
                                <td class="text-end">{{ formatNumber(invoice.total_net_amount) }}</td>
                                <td class="text-end">{{ formatNumber(invoice.total_gross_amount) }}</td>
                                <td class="p-0 text-center" style="vertical-align: middle;" rowspan="2">
                                    <Button class="btn-sm border-0 outline-none" @click="PdfModalRef.show(`/pdf/issued-invoices/${invoice.id}`);"><i class="fa-solid fa-print"></i></Button>
                                    <Button class="btn-sm border-0 outline-none" @click="destroy(invoice.id, index);"><i class="fa-solid fa-trash"></i></Button>
                                </td>
                            </tr>
                            <tr :class="index%2 === 0 && 'table-active'">
                                <td>{{ invoice.date }}</td>
                                <td></td>
                                <td class="text-end">{{ formatNumber(invoice.total_refund_amount) }}</td>
                                <td></td>
                                <td class="text-end">{{ formatNumber(invoice.total_tax_amount) }}</td>
                                <td class="text-end">{{ formatNumber(invoice.current_invoice_amount) }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
            <template v-else>該当する結果がありません</template>
        </div>
    </div>

    <PdfModal ref="PdfModalRef" />
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import { formatNumber } from '@/utils/format';
    import api from '@/ts/api'
    import PdfModal from '../Components/Modal/PdfModal.vue';
    import Button from '../Components/Elements/Button.vue';

    onMounted(async () => {
        await fetchIndex();

        overlay.hide();
    });

    interface Invoice {
        id: string,
        client_id: number,
        client_code: string,
        client_name: string,
        previous_invoice_amount: number,
        total_receipt_amount: number,
        total_refund_amount: number,
        carried_forward_amount: number,
        total_net_amount: number,
        total_tax_amount: number,
        total_gross_amount: number,
        current_invoice_amount: number,
        date: string,
    }

    const PdfModalRef = ref();
    const overlay = useOverlayStore();
    const checked_client_ids = ref<number[]>([]);
    const invoices = ref<Invoice[]>([]);
    const params = ref();
    const isLoading = ref({
        index: false,
        destroy: false,
    });

    const fetchIndex = async () => {
        isLoading.value.index = true;
        checked_client_ids.value = [];

        try{
            let res = await api.get('/issued-invoices', params.value);

            invoices.value = res.data;
        }catch(err){
            //
        }finally{
            isLoading.value.index = false;
        }
    }

    const destroy = async (id: string, index: number) => {
        if(!window.confirm('請求を取り消ししますか？\n取り消しした請求書は復元することはできません。')) return;

        isLoading.value.destroy = true;

        try{
            let res = await api.delete(`/issued-invoices/${id}`);

            invoices.value.splice(index, 1);

            alert('取り消しました')
        }catch(err){
            //
        }finally{
            isLoading.value.destroy = false;
        }
    }
</script>