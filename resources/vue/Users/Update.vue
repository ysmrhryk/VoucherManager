<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">呼出番号 *</span>
                            <Input placeholder="" v-model="user.code" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">名前 *</span>
                            <Input placeholder="" v-model="user.name" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text rounded-0">ログイン用メールアドレス *</span>
                            <Input placeholder="" v-model="user.email" />
                        </div>
                    </div>
                    
                    <div class="col-4">
                        <router-link :to="{ name: 'users.index' }" class="btn btn-outline-dark w-100 rounded-0"><i class="fa-solid fa-angles-left"></i> 一覧へ戻る</router-link>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-warning w-100 rounded-0" @click="reset()"><i class="fa-solid fa-eraser"></i> 入力中の内容をクリア</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-primary w-100 rounded-0" @click="update()" :disabled="isLoading">
                            <Spinner :sm="true" :show="isLoading" />
                            <template v-if="!isLoading"><i class="fa-solid fa-floppy-disk"></i> 更新</template>
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
    import { useRoute } from 'vue-router'
    import { useOverlayStore } from '@/stores/overlay';
    import Input from '../Components/Elements/Input.vue';

    onMounted(async () => {
        await show();

        overlay.hide();
    });

    const overlay = useOverlayStore();
    const isLoading = ref<boolean>(false);
    const route = useRoute();

    interface User {
        code: string | null,
        name: string | null,
        email: string | null,
    }

    const user = ref<User>({
        code: null,
        name: null,
        email: null,
    });

    const show = async () => {
        try{
            let res = await api.get(`/users/${route.params.id}`);

            user.value = res.data;
        }catch(err){
            // エラー処理は後で書くかも

            alert('読み込み時にエラーが発生しました。\n再度お試しください。');
        }
    }

    const update = async () => {
        isLoading.value = true;

        try{
            let res = await api.put(`/users/${route.params.id}`, user.value);

            alert('保存しました');
        }catch(err){
            // エラーのちゃんとした処理は後で書きたい

            alert('エラーが発生しました。\n登録できませんでした。');
        }finally{
            isLoading.value = false;
        }
    }

    const reset = () => {
        if(!window.confirm('入力中の内容を破棄しますか？')) return;

        show();
    }
</script>