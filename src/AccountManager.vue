<script setup lang="ts">
import registerPath from '@/assets/ts/registerPath'
import { AccountInfo } from '@/assets/ts/Interfaces/Index'
import HeaderComponent from '@/components/HeaderComponent.vue'
import '@/assets/scss/manageAccount.scss'
import axios from 'axios'
import { ref, computed, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
router
const currentURL = computed(() => route.path)

// ログインページか登録ページかの判定
const currentPath = ref<string>('')
watchEffect(() => {
    currentPath.value = currentURL.value.match(/login/) ? 'login' : 'register'
})

// 入力フォームのアカウント情報
const account = ref<AccountInfo>({
    method: '',
    email: '',
    user_id: '',
    password: '',
})

// 登録ボタンが押された際、バリデーションを行いアカウント登録またはログイン
const responseMessage = ref<string>('')
const formUrl = registerPath + 'api/manageAccount.php'
const regex = {
    user_id: '^.{6,20}$',
    password: '^([a-zA-Z0-9]{8,20})$'
}

const submitAccountData = async() => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    if (!new RegExp(regex.user_id).test(account.value.user_id)) {
        responseMessage.value = 'ユーザーIDの入力内容が空または不正です。'
        return
    }

    if (!new RegExp(regex.password).test(account.value.password)) {
        responseMessage.value = 'パスワードの入力内容が空または不正です。'
        return
    }

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const sendData = {...account.value}
    sendData.method = currentPath.value

    const formData = JSON.stringify(sendData)
    axios.post(formUrl, formData).then((response) => {
        // 返答でエラーが無い場合は指定ページにリダイレクト
        if (!response.data.error) {
            if (currentPath.value === 'register') {
                // ログインページ遷移時メッセージと入力内容を初期化
                responseMessage.value = ''
                account.value.user_id = ''
                account.value.password = ''
                router.push('./login')
            } else if (currentPath.value === 'login') {
                router.push('./')
            }
        } else {
            // エラーが返された場合は内容を画面に表示
            responseMessage.value = response.data.content
        }
    }).catch(error => {
        responseMessage.value = 'データベース接続に失敗しました。お手数ですが、時間を置いて再度お試しください。'
        console.log(error)
    })
}

// ログインユーザーIDを取得
const user_id = ref<string>('')
const getUserInfo = (userId: string) => user_id.value = userId;
</script>

<template lang="">
    <HeaderComponent @getUserInfo="getUserInfo"></HeaderComponent> 
    <main class="account-manage">
        <div class="title-area">
            <h2>{{ currentPath === 'register' ? 'ユーザー新規登録' : 'ユーザーログイン' }}</h2>
            <p>{{ responseMessage }}</p>
        </div>
        <div class="form-area">
            <a href="./#/login" v-if="currentPath === 'register'">既にアカウント登録している場合はこちら</a>
            <a href="./#/register" v-else>アカウント未登録の場合はこちら</a>
            <form @submit.prevent="submitAccountData()">
                <dl>
                    <div v-if="currentPath === 'register'">
                        <dt>メールアドレス</dt>
                        <dd><input type="email" id="email" v-model="account.email" :pattern="regex.email" required></dd>
                    </div>
                    <div>
                        <dt>ユーザーID</dt>
                        <dd>
                            <p class="caption">6文字以上20文字以下</p>
                            <input type="text" id="user_id" v-model="account.user_id" autocomplete="username" :pattern="regex.user_id" required>
                        </dd>
                    </div>
                    <div>
                        <dt>パスワード</dt>
                        <dd>
                            <p class="caption">半角英数字8文字以上20文字以下</p>
                            <input type="password" id="password" v-model="account.password" autocomplete="current-password" :pattern="regex.password" required>
                        </dd>
                    </div>
                </dl>
                <button type="submit" class="btn-common green submit">{{ currentPath === 'register' ? '登録' : 'ログイン' }}</button>
            </form>
        </div>
    </main>
    <router-view></router-view>
</template>