<template lang="">
    <HeaderComponent :user="user_id"></HeaderComponent> 
    <main>
        <div class="title-area">
            <p></p>
            <h2>{{ currentPath === 'register' ? 'ユーザー新規登録' : 'ユーザーログイン' }}</h2>
        </div>
        <div class="form-area">
            <a href="./#/login" v-if="currentPath === 'register'">既にアカウント登録している場合はこちら</a>
            <a href="./#/register" v-else>アカウント未登録の場合はこちら</a>
            <form :action="currentURL">
                <dl>
                    <div v-if="currentPath === 'register'">
                        <dt>メールアドレス</dt>
                        <dd><input type="text" id="email" v-model="account.email" required></dd>
                    </div>
                    <div>
                        <dt>ユーザーID</dt>
                        <dd>
                            <p class="caption">6文字以上20文字以下</p>
                            <input type="text" id="user_id" v-model="account.user_id" autocomplete="username" pattern="^.{6,20}$" required>
                        </dd>
                    </div>
                    <div>
                        <dt>パスワード</dt>
                        <dd>
                            <p class="caption">半角英数字8文字以上20文字以下</p>
                            <input type="password" id="password" v-model="account.password" autocomplete="current-password" pattern="^([a-zA-Z0-9]{8,20})$" required>
                        </dd>
                    </div>
                </dl>
                <button @click="submitAccountData()" class="btn-common green submit">{{ currentPath === 'register' ? '登録' : 'ログイン' }}</button>
            </form>
        </div>
    </main>
    <router-view></router-view>
</template>
<script lang="ts">
import { ref, watch, watchEffect, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import registerPath from '@/assets/ts/registerPath'
import '../assets/scss/accountManage.scss'
import HeaderComponent from './HeaderComponent.vue'

export default {
    components: {
        HeaderComponent,
    },
    setup() {
        // ログインページか登録ページかの判定
        const currentURL = ref<string>('')
        watch(useRoute(), (route) => currentURL.value = route.path)
        const currentPath = ref<string>('')
        watchEffect(() => {
            currentPath.value = currentURL.value.match(/login/) ? 'login' : 'register'
        })

        // 入力フォームのアカウント情報
        const account = ref<{[key: string]: string}>({
            email: '',
            user_id: '',
            password: '',
        })

        // 登録ボタンが押された際、バリデーションを行いアカウント登録またはログイン
        const submitAccountData = async() => {

        }

        // ログインユーザーIDを取得
        const user_id = ref<string>('')
        const getUserInfo = async() => {
            const url = registerPath + 'api/getUserInfo.php'
            axios.get(url)
                .then(response => user_id.value = response.data.user_id)
                .catch(error => console.log(error))
        }

        onMounted(() => getUserInfo())

        return {
            currentURL,
            currentPath,
            account,
            user_id,

            submitAccountData,
        }
    }
}
</script>
<style lang="">
    
</style>