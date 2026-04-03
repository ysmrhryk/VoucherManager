<template>
    <div class="row g-4">
        <div class="col-3">
            <div class="card">
                <div class="card-header">業務</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <router-link :to="{ name: 'service_vouchers.index' }">
                            {{ router.resolve({ name: 'service_vouchers.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'estimate_vouchers.index' }">
                            {{ router.resolve({ name: 'estimate_vouchers.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'receipt_vouchers.index' }">
                            {{ router.resolve({ name: 'receipt_vouchers.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'refund_vouchers.index' }">
                            {{ router.resolve({ name: 'refund_vouchers.index' }).meta.title }}
                        </router-link>
                    </li>

                    <li class="list-group-item">
                        <router-link :to="{ name: 'pending_invoices.index' }">
                            {{ router.resolve({ name: 'pending_invoices.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'issued_invoices.index' }">
                            {{ router.resolve({ name: 'issued_invoices.index' }).meta.title }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-header">マスタ</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <router-link :to="{ name: 'clients.index' }">
                            {{ router.resolve({ name: 'clients.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'users.index' }">
                            {{ router.resolve({ name: 'users.index' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'payment_methods.index' }">
                            {{ router.resolve({ name: 'payment_methods.index' }).meta.title }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-header">その他</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <router-link :to="{ name: 'settings' }">
                            {{ router.resolve({ name: 'settings' }).meta.title }}
                        </router-link>
                    </li>
                    <li class="list-group-item">
                        <router-link :to="{ name: 'logs' }">
                            {{ router.resolve({ name: 'logs' }).meta.title }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted } from 'vue';
    import { useOverlayStore } from '@/stores/overlay';
    import { useRouter } from 'vue-router';

    onMounted(() => {
        overlay.hide();
    });

    const overlay = useOverlayStore();
    const router = useRouter();
</script>