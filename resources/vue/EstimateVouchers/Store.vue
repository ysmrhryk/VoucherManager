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
            <button class="btn btn-sm rounded-0 w-100" disabled="true">-</button>
        </div>
        <div class="col-2">
            <button class="btn btn-sm btn-success rounded-0 w-100" @click="store()">
                <Spinner :sm="true" :show="isLoading.voucher" />
                <template v-if="!isLoading.voucher">登録</template>
            </button>
        </div>

        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">入力日*</span>
                        <input type="date" class="form-control rounded-0 outline-none" v-model="voucher.date">
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">有効期限*</span>
                        <input type="date" class="form-control rounded-0 outline-none" v-model="voucher.expiry_date">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">担当者*</span>
                        <input 
                            type="text" 
                            class="form-control rounded-0 outline-none" 
                            v-model.lazy="voucher.user.code" 
                            @change="getUser({ code: voucher.user.code })"
                            placeholder="担当者コードを入力"
                        >
                        <button class="btn btn-outline-dark rounded-0" :disabled="isLoading.user" @click="usersModalRef.show();">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border w-100 h-100 d-flex align-items-center ps-2 overflow-hidden">
                        {{ voucher.user_name }}
                    </div>
                </div>

                <div class="col-md-12">
                    <label>社内向け備考欄</label>
                    <textarea class="form-control outline-none rounded-0" rows="4" v-model.lazy="voucher.internal_note"></textarea>
                </div>
                <div class="col-md-12">
                    <label>顧客向け備考欄</label>
                    <textarea class="form-control outline-none rounded-0" rows="4" v-model.lazy="voucher.customer_note"></textarea>
                    <small class="text-danger">伝票に表示されます。社外秘情報などは記載しないでください。</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">顧客番号*</span>
                        <input 
                            type="text" 
                            class="form-control rounded-0 outline-none" 
                            v-model.lazy="voucher.client.code" 
                            @change="getClient({ code: voucher.client.code })"
                            placeholder="顧客番号を入力"
                        >
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

                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">追加顧客名称</span>
                        <input 
                            type="text" 
                            class="form-control rounded-0 outline-none" 
                            v-model="voucher.extension_client_name"
                            :disabled="!voucher.client_id"
                            @change="if(!voucher.extension_client_name) voucher.name_suffix_id = null;"
                        >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text rounded-0">敬称</span>
                        <select class="form-select rounded-0 outline-none" v-model.lazy="voucher.name_suffix_id" :disabled="!voucher.extension_client_name">
                            <option :value="null">-</option>
                            <option v-for="name_suffix in name_suffixes" :key="name_suffix.id" :value="name_suffix.id">{{ name_suffix.value }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12"><hr></div>

        <div class="col-12">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr class="text-center">
                        <th style="width: 12%;">行区分</th>
                        <th style="width: 36%;">内容</th>
                        <th style="width: 10%;">税率区分</th>
                        <th style="width: 10%;">数量</th>
                        <th style="width: 10%;">単価</th>
                        <th style="width: 10%;">合計</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(line, index) in voucher.bodies" :key="line.localUuid">
                        <tr>
                            <td>
                                <select 
                                    class="form-select border-0 outline-none rounded-0" 
                                    v-model.lazy="line.estimate_voucher_row_type_id"
                                    @change="recalculation(index)"
                                >
                                    <option 
                                        v-for="estimate_voucher_row_type in estimate_voucher_row_types"
                                        :key="estimate_voucher_row_type.id"
                                        :value="estimate_voucher_row_type.id"
                                    >
                                        {{ estimate_voucher_row_type.value }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input 
                                    type="text" 
                                    class="form-control border-0 outline-none rounded-0" 
                                    v-model.lazy="line.content"
                                    :disabled="![1,2,3].includes(line.estimate_voucher_row_type_id)"
                                    @change="recalculation(index)"
                                    placeholder="-"
                                >
                            </td>
                            <td>
                                <select 
                                    class="form-select border-0 outline-none rounded-0" 
                                    v-model.lazy="line.tax_rate_id"
                                    :disabled="![1,2].includes(line.estimate_voucher_row_type_id)"
                                    @change="recalculation(index)"
                                >
                                    <option :value="null">-</option>
                                    <option
                                        v-for="tax_rate in tax_rates"
                                        :key="tax_rate.id"
                                        :value="tax_rate.id"
                                    >
                                        {{ tax_rate.name }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    class="form-control border-0 text-end outline-none no-spin-btn rounded-0" 
                                    v-model.lazy="line.quantity"
                                    :disabled="![1,2].includes(line.estimate_voucher_row_type_id)"
                                    @change="recalculation(index)"
                                    placeholder="0"
                                >
                            </td>
                            <td>
                                <input 
                                    type="number" 
                                    class="form-control border-0 text-end outline-none no-spin-btn rounded-0" 
                                    v-model.lazy="line.unit_price"
                                    :disabled="![1,2].includes(line.estimate_voucher_row_type_id)"
                                    @change="recalculation(index)"
                                    placeholder="0"
                                >
                            </td>
                            <td class="text-end pe-3 align-middle">
                                {{ formatNumber(Number(line.quantity) * Number(line.unit_price)) }}
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
                        <th colspan="5" class="text-end">合計</th>
                        <td class="text-end pe-3">{{ formatNumber(Number(voucher.total_net_amount)) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <ClientsModal ref="clientsModalRef" @select="getClient" />
    <UsersModal ref="usersModalRef" @select="getUser" />
</template>

<script setup lang="ts">
    import { onMounted, ref, computed } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import api from '@/ts/api'
    import Request from '@/utils/request'
    import type { NameSuffix, TaxRate, EstimateVoucherRowType } from '@/interfaces/Index';
    import { formatNumber } from '@/utils/format';
    import ClientsModal from '@/vue/Components/Modal/ClientsModal.vue';
    import UsersModal from '../Components/Modal/UsersModal.vue';
    import Spinner from '../Components/Elements/Spinner.vue';

    onMounted(async () => {
        [
            name_suffixes.value,
            estimate_voucher_row_types.value,
            tax_rates.value
        ] = await Promise.all([
            Request.name_suffixes(),
            Request.estimate_voucher_row_types(),
            Request.tax_rates()
        ]);

        overlay.hide();
    });

    const clientsModalRef = ref();
    const usersModalRef = ref();

    const overlay = useOverlayStore();
    const isLoading = ref({
        voucher: false,
        client: false,
        user: false,
    });
    const name_suffixes = ref<NameSuffix[]>();
    const estimate_voucher_row_types = ref<EstimateVoucherRowType[]>();
    const tax_rates = ref<TaxRate[]>();

    const formatted = computed(() => {
        return {
            'date': voucher.value.date,
            'expiry_date': voucher.value.expiry_date,
            'customer_note': voucher.value.customer_note,
            'internal_note': voucher.value.internal_note,
            'user_id': voucher.value.user_id,
            'client_id': voucher.value.client_id,
            'extension_client_name': voucher.value.extension_client_name,
            'name_suffix_id': voucher.value.name_suffix_id,
            'bodies': voucher.value.bodies.map((line, index) => ({
                ...line,
                'line_number': index + 1,
            }))
        }
    });

    interface Voucher {
        date: string | null,
        expiry_date: string | null,
        customer_note: string | null,
        internal_note: string | null,
        user_id: number | null,
        user_name: string | null,
        client_id: number | null,
        client_name: string | null,
        extension_client_name: string | null,
        client_postal: string | null,
        client_address: string | null,
        client_tel: string | null,
        client_fax: string | null,
        name_suffix_id: number | null,
        total_net_amount: number,
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
        estimate_voucher_row_type_id: number,
        line_number: number | null,
        quantity: number | null,
        unit_price: number | null,
        tax_rate_id: number | null,
        content: string | null,
    }

    const nextMonth = () => {
        const date = new Date();
        date.setMonth(date.getMonth() + 1);
        return date.toLocaleDateString('ja-JP', { year: "numeric", month: "2-digit", day: "2-digit" }).replaceAll('/', '-');
    }

    const init = (): Voucher => ({
        date: new Date().toLocaleDateString('ja-JP', { year: "numeric", month: "2-digit", day: "2-digit" }).replaceAll('/', '-'),
        expiry_date: nextMonth(),
        customer_note: null,
        internal_note: null,
        user_id: null,
        user_name: null,
        client_id: null,
        client_name: null,
        client_postal: null,
        client_address: null,
        client_tel: null,
        client_fax: null,
        extension_client_name: null,
        name_suffix_id: null,
        total_net_amount: 0,
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

    const voucher = ref<Voucher>(init());

    const store = async () => {
        if(!window.confirm('伝票を登録しますか？')) return;

        isLoading.value.voucher = true;

        try{
            let res = await api.post(`/estimate-vouchers`, formatted.value);

            alert('伝票を登録しました');

            // 元の状態に戻す
            voucher.value = init();
        }catch(err){
            // エラーのちゃんとした処理は後で書きたい

            alert('エラーが発生しました。\n登録できませんでした。');
        }finally{
            isLoading.value.voucher = false;
        }
    }

    const reset = () => {
        if(!window.confirm('入力中の内容を破棄して元の状態に戻しますか？')) return;

        voucher.value = init();
    }

    const recalculation = (index?: number) => {
        if(index !== undefined){
            let line = voucher.value.bodies[index];

            if(line !== undefined){
                switch(line?.estimate_voucher_row_type_id){
                    case 1: // 見積
                        if(line.quantity) line.quantity = Math.abs(Number(line.quantity));
                        if(line.unit_price) line.unit_price = Math.abs(Number(line.unit_price));
                        break;
                    case 2: // 値引
                        if(line.quantity) line.quantity = Math.abs(Number(line.quantity));
                        if(line.unit_price) line.unit_price = -Math.abs(Number(line.unit_price));
                        break;
                    case 3: // メモ
                        line.quantity = null;
                        line.unit_price = null;
                        line.tax_rate_id = null;
                        break;
                    case 4: // 余白
                        line.quantity = null;
                        line.unit_price = null;
                        line.tax_rate_id = null;
                        line.content = null;
                        break;
                }
            }
        }

        voucher.value.total_net_amount = 0;

        voucher.value.bodies.forEach((line) => {
            voucher.value.total_net_amount += Number(line.quantity) * Number(line.unit_price);
        });
    }

    const addLine = (
        estimate_voucher_row_type_id?: number,
        quantity?: number | null,
        unit_price?: number | null,
        tax_rate_id?: number | null,
        content?: string | null
    ) => {
        voucher.value.bodies.push({
            localUuid: crypto.randomUUID(),
            line_number: null,

            estimate_voucher_row_type_id: estimate_voucher_row_type_id || 1,
            quantity: quantity || null,
            unit_price: unit_price || null,
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

        voucher.value.extension_client_name = null;
        voucher.value.name_suffix_id = null;

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

    const getUser = async (arg: { id?: number | null, code?: string | null }) => {
        isLoading.value.user = true;

        const response = await Request.users.show(arg);

        if(response){
            voucher.value.user_id = response.id;
            voucher.value.user_name = response.name;
            voucher.value.user = response;
        }else{
            voucher.value.user_id = null;
            voucher.value.user_name = null;
            voucher.value.user = {
                code: null
            };
        }

        isLoading.value.user = false;
    }
</script>