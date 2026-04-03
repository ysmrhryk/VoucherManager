import { createRouter, createWebHistory, type RouteRecordRaw } from "vue-router"
import { useOverlayStore } from "@/stores/overlay";
import { useTitleStore } from "@/stores/title";
import api from "@/ts/api";
import { useUserStore } from "@/stores/user";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('../vue/Layout.vue'),
            children: [
                {
                    path: '/',
                    redirect: { name: 'dashboard' }
                },
                {
                    path: 'dashboard',
                    name: 'dashboard',
                    component: () => import('../vue/Dashboard.vue'),
                    meta: { title: 'ダッシュボード' }
                },
                {
                    path: 'settings',
                    name: 'settings',
                    component: () => import('../vue/Settings.vue'),
                    meta: { title: '設定' }
                },
                {
                    path: 'logs',
                    name: 'logs',
                    component: () => import('../vue/Logs.vue'),
                    meta: { title: 'ログ' }
                },
                {
                    path: 'service-vouchers',
                    children: [
                        {
                            path: '',
                            name: 'service_vouchers.index',
                            component: () => import('../vue/ServiceVouchers/Index.vue'),
                            meta: { title: '売上管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'service_vouchers.update',
                            component: () => import('../vue/ServiceVouchers/Update.vue'),
                            meta: { title: '売上更新' }
                        },
                        {
                            path: 'store',
                            name: 'service_vouchers.store',
                            component: () => import('../vue/ServiceVouchers/Store.vue'),
                            meta: { title: '売上登録' }
                        },
                    ]
                },
                {
                    path: 'estimate-vouchers',
                    children: [
                        {
                            path: '',
                            name: 'estimate_vouchers.index',
                            component: () => import('../vue/EstimateVouchers/Index.vue'),
                            meta: { title: '見積管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'estimate_vouchers.update',
                            component: () => import('../vue/EstimateVouchers/Update.vue'),
                            meta: { title: '見積更新' }
                        },
                        {
                            path: 'store',
                            name: 'estimate_vouchers.store',
                            component: () => import('../vue/EstimateVouchers/Store.vue'),
                            meta: { title: '見積登録' }
                        },
                    ]
                },
                {
                    path: 'receipt-vouchers',
                    children: [
                        {
                            path: '',
                            name: 'receipt_vouchers.index',
                            component: () => import('../vue/ReceiptVouchers/Index.vue'),
                            meta: { title: '入金管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'receipt_vouchers.update',
                            component: () => import('../vue/ReceiptVouchers/Update.vue'),
                            meta: { title: '入金更新' }
                        },
                        {
                            path: 'store',
                            name: 'receipt_vouchers.store',
                            component: () => import('../vue/ReceiptVouchers/Store.vue'),
                            meta: { title: '入金登録' }
                        },
                    ]
                },
                {
                    path: 'refund-vouchers',
                    children: [
                        {
                            path: '',
                            name: 'refund_vouchers.index',
                            component: () => import('../vue/RefundVouchers/Index.vue'),
                            meta: { title: '返金管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'refund_vouchers.update',
                            component: () => import('../vue/RefundVouchers/Update.vue'),
                            meta: { title: '返金更新' }
                        },
                        {
                            path: 'store',
                            name: 'refund_vouchers.store',
                            component: () => import('../vue/RefundVouchers/Store.vue'),
                            meta: { title: '返金登録' }
                        },
                    ]
                },
                {
                    path: 'issued-invoices',
                    children: [
                        {
                            path: '',
                            name: 'issued_invoices.index',
                            component: () => import('../vue/IssuedInvoices/Index.vue'),
                            meta: { title: '発行済み請求書管理' }
                        }
                    ]
                },
                {
                    path: 'pending-invoices',
                    children: [
                        {
                            path: '',
                            name: 'pending_invoices.index',
                            component: () => import('../vue/PendingInvoices/Summary.vue'),
                            meta: { title: '未発行請求書管理' }
                        }
                    ]
                },
                {
                    path: 'clients',
                    children: [
                        {
                            path: '',
                            name: 'clients.index',
                            component: () => import('../vue/Clients/Index.vue'),
                            meta: { title: '顧客管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'clients.update',
                            component: () => import('../vue/Clients/Update.vue'),
                            meta: { title: '顧客更新' }
                        },
                        {
                            path: 'store',
                            name: 'clients.store',
                            component: () => import('../vue/Clients/Store.vue'),
                            meta: { title: '顧客登録' }
                        },
                    ]
                },
                {
                    path: 'payment-methods',
                    children: [
                        {
                            path: '',
                            name: 'payment_methods.index',
                            component: () => import('../vue/PaymentMethods/Index.vue'),
                            meta: { title: '支払方法管理' }
                        },
                        {
                            path: 'store',
                            name: 'payment_methods.store',
                            component: () => import('../vue/PaymentMethods/Store.vue'),
                            meta: { title: '支払方法登録' }
                        },
                    ]
                },
                {
                    path: 'users',
                    children: [
                        {
                            path: '',
                            name: 'users.index',
                            component: () => import('../vue/Users/Index.vue'),
                            meta: { title: '担当者管理' }
                        },
                        {
                            path: ':id/update',
                            name: 'users.update',
                            component: () => import('../vue/Users/Update.vue'),
                            meta: { title: '担当者更新' }
                        },
                        {
                            path: ':id/update-password',
                            name: 'users.update_password',
                            component: () => import('../vue/Users/UpdatePassword.vue'),
                            meta: { title: '担当者パスワード更新' }
                        },
                        {
                            path: 'store',
                            name: 'users.store',
                            component: () => import('../vue/Users/Store.vue'),
                            meta: { title: '担当者登録' }
                        },
                    ]
                },
            ]
        },

        {
            path: '/login',
            name: 'login',
            component: () => import('../vue/Auth/Login.vue'),
            meta: { title: 'ログイン' }
        },
        {
            path: '/logout',
            name: 'logout',
            component: () => import('../vue/Auth/Logout.vue'),
            meta: { title: 'ログアウト' }
        },

        
        {
            path: '/:pathMatch(.*)*',
            name: '404',
            component: () => import('../vue/404.vue')
        }
    ] as RouteRecordRaw[],
});

router.beforeEach(async (to, from, next) => {
    const overlay = useOverlayStore();
    const title = useTitleStore();
    const user = useUserStore();

    overlay.show();

    if(user.get()?.id === null && to.name !== 'login' && to.name !== 'logout'){
        // ユーザーのデータを取得
        // セッションが切れてた場合はaxiosのエラーハンドリング側でページ遷移が走る
        try{
            await api.get('/user').then((res) => user.set(res.data));
        }catch(err){
            // エラーは捨てる
        }
    }

    title.set(to.meta.title as string);
    next();
});

export default router;