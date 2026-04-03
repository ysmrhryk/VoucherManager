<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">呼出番号 *</span>
                            <Input v-model="user.code" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">名前 *</span>
                            <Input v-model="user.name" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">ログイン用メールアドレス *</span>
                            <Input v-model="user.email" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text rounded-0">ログイン用パスワード *</span>
                                    <Input type="password" v-model="user.password" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text rounded-0">【確認用】パスワードを再度入力してください *</span>
                                    <Input type="password" v-model="user.password_confirmation" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <router-link :to="{ name: 'users.index' }" class="btn btn-outline-dark w-100 rounded-0"><i class="fa-solid fa-angles-left"></i> 一覧へ戻る</router-link>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-warning w-100 rounded-0" @click="reset()"><i class="fa-solid fa-eraser"></i> 入力中の内容をクリア</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-primary w-100 rounded-0" @click="store()" :disabled="isLoading">
                            <template v-if="isLoading">
                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            </template>
                            <template v-else>
                                <i class="fa-solid fa-floppy-disk"></i> 登録
                            </template>

                            <Spinner :sm="true" :show="isLoading" />
                            <template v-if="!isLoading">
                                <i class="fa-solid fa-floppy-disk"></i> 登録
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue'
    import api from '@/ts/api';
    import { useRouter } from 'vue-router'
    import { useOverlayStore } from '@/stores/overlay';
    import Spinner from '@/vue/Components/Elements/Spinner.vue';

    onMounted(async () => {
        overlay.hide();
    });

    const router = useRouter();
    const overlay = useOverlayStore();
    const isLoading = ref<boolean>(false);

    interface User {
        code: string | null,
        name: string | null,
        email: string | null,
        password: string | null,
        password_confirmation: string | null,
    }

    const init = (): User => ({
        code: null,
        name: null,
        email: null,
        password: null,
        password_confirmation: null,
    });

    const user = ref(init());

    const store = async () => {
        isLoading.value = true;

        try{
            let res = await api.post(`/users`, user.value);

            alert('登録しました');

            router.push({ name: 'users.update', params: { id: res.data.id } });
        }catch(err){
            // エラーのちゃんとした処理は後で書きたい

            alert('エラーが発生しました。\n登録できませんでした。');
        }finally{
            isLoading.value = false;
        }
    }

    const reset = () => {
        if(!window.confirm('入力中の内容を破棄しますか？')) return;

        user.value = init();
    }
</script>