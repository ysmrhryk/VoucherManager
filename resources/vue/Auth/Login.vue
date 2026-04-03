<template>
    <div class="position-absolute top-50 start-50 translate-middle">
        <div class="row g-4">
            <div class="col-12">
                <h2 class="fs-3">ログイン</h2>
            </div>
            <div class="col-12">
                <div class="form-floating">
                    <input type="email" class="form-control" placeholder="" v-model="data.email">
                    <label>メールアドレス</label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-floating">
                    <input type="password" class="form-control" placeholder="" v-model="data.password">
                    <label>パスワード</label>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" @click="login()" :disabled="isLoading">
                    <template v-if="isLoading">
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    </template>
                    ログイン
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { useUserStore } from '@/stores/user';
    import api from '@/ts/api'
    import { ref } from 'vue';
    import { useRouter } from 'vue-router';

    const router = useRouter();
    const user = useUserStore();

    const isLoading = ref<boolean>(false);

    interface Data {
        email: string | null,
        password: string | null,
    }

    const data = ref<Data>({
        email: null,
        password: null
    });

    const login = async () => {
        isLoading.value = true;

        await api.get('/sanctum/csrf-cookie', { baseURL: '/' });

        await api.post('/login', data.value)
        .then((res) => {
            // ユーザーデータをストアに保存
            user.set(res.data);

            // ページ移動
            router.push({ name: 'dashboard'});
        })
        .catch((err) => {
            console.log(err);
        })
        .finally(() => {
            isLoading.value = false;
        });
    }
</script>