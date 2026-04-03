<template>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="menu" aria-labelledby="menuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="menuLabel">メニュー</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex flex-column h-100">
                <div class="row g-4">
                    <div class="col-12">
                        <router-link :to="{ name: 'dashboard' }" data-bs-dismiss="offcanvas" aria-label="Close">
                            <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'dashboard' }).meta.title }}</span>
                        </router-link>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">業務</div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'service_vouchers.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'service_vouchers.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'estimate_vouchers.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'estimate_vouchers.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'receipt_vouchers.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'receipt_vouchers.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'refund_vouchers.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'refund_vouchers.index' }).meta.title }}</span>
                                    </router-link>
                                </li>

                                <li class="list-group-item">
                                    <router-link :to="{ name: 'pending_invoices.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'pending_invoices.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'issued_invoices.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'issued_invoices.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">マスタ</div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'clients.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'clients.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'users.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'users.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'payment_methods.index' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'payment_methods.index' }).meta.title }}</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">その他</div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'settings' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'settings' }).meta.title }}</span>
                                    </router-link>
                                </li>
                                <li class="list-group-item">
                                    <router-link :to="{ name: 'logs' }" data-bs-dismiss="offcanvas" aria-label="Close">
                                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'logs' }).meta.title }}</span>
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-auto mt-4 overflow-auto">
                    ユーザー: {{ user.get()?.name }} ({{ user.get()?.code }}) / 
                    <router-link :to="{ name: 'logout' }" data-bs-dismiss="offcanvas" aria-label="Close">
                        <span data-bs-dismiss="offcanvas" aria-label="Close">{{ router.resolve({ name: 'logout' }).meta.title }}</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between flex-row border-bottom">
        <div>
            <button class="btn border-0 m-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu" aria-controls="menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div>{{ title.get() }}</div>

        <div></div>
    </div>


    <div class="p-4 overflow-auto">
        <router-view />
    </div>

    <template v-if="overlay.isLoading">
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-50 bg-dark"></div>
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="spinner-border text-light" style="width: 5em; height: 5em;" role="status"></div>
        </div>
    </template>
</template>

<script setup lang="ts">
    import { onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    import { useOverlayStore } from '@/stores/overlay';
    import { useTitleStore } from '@/stores/title';
    import { useUserStore } from '@/stores/user';

    const overlay = useOverlayStore();
    const title = useTitleStore();
    const router = useRouter();
    const user = useUserStore();
</script>