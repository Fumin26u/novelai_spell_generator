<script setup lang="ts">
import apiPath from '@/assets/ts/apiPath'
import { AccountInfo } from '@/assets/ts/Interfaces/Index'
import HeaderComponent from '@/components/HeaderComponent.vue'
import '@/assets/scss/manageAccount.scss'
import ApiManager from '@/components/api/apiManager'
import { ref, computed, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

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
const formUrl = apiPath + 'manageAccount.php'
const regex = {
    user_id: '^.{6,20}$',
    password: '^([a-zA-Z0-9]{8,20})$',
}

const inputValidation = () => {
    let errorMessage = ''
    if (!new RegExp(regex.user_id).test(account.value.user_id)) {
        errorMessage = 'ユーザーIDの入力内容が空または不正です。'
    }

    if (!new RegExp(regex.password).test(account.value.password)) {
        errorMessage = 'パスワードの入力内容が空または不正です。'
    }
    return errorMessage
}

const submitAccountData = async () => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    responseMessage.value = inputValidation()
    if (responseMessage.value !== '') return

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const sendData = { ...account.value }
    sendData.method = currentPath.value

    const formData = JSON.stringify(sendData)
    const apiManager = new ApiManager()
    const response = await apiManager.post(formUrl, formData)
    // 入力内容が不正の場合
    if (response.error) {
        responseMessage.value = response.content
        return
    }

    // 返答でエラーが無い場合は指定ページにリダイレクト
    if (currentPath.value === 'register') {
        // ログインページ遷移時メッセージと入力内容を初期化
        responseMessage.value = ''
        account.value.user_id = ''
        account.value.password = ''
        router.push('./login')
    } else if (currentPath.value === 'login') {
        router.push('./')
    }
}
</script>

<template lang="">
    <HeaderComponent></HeaderComponent>
    <main class="account-manage">
        <div class="title-area">
            <h2>
                {{
                    currentPath === 'register'
                        ? 'ユーザー新規登録'
                        : 'ユーザーログイン'
                }}
            </h2>
            <p>{{ responseMessage }}</p>
        </div>
        <div class="form-area">
            <div v-if="currentPath === 'register'">
                <a href="./#/login">既にアカウント登録している場合はこちら</a>
            </div>
            <div v-else>
                <a href="./#/register">アカウント未登録の場合はこちら</a>
                <a href="./#/forgotPassword">パスワードを忘れた場合はこちら</a>
            </div>
            <form @submit.prevent="submitAccountData()">
                <dl>
                    <div v-if="currentPath === 'register'">
                        <dt>メールアドレス</dt>
                        <dd>
                            <input
                                type="email"
                                id="email"
                                v-model="account.email"
                                :pattern="regex.email"
                                required
                            />
                        </dd>
                    </div>
                    <div>
                        <dt>ユーザーID</dt>
                        <dd>
                            <p class="caption">6文字以上20文字以下</p>
                            <input
                                type="text"
                                id="user_id"
                                v-model="account.user_id"
                                autocomplete="username"
                                :pattern="regex.user_id"
                                required
                            />
                        </dd>
                    </div>
                    <div>
                        <dt>パスワード</dt>
                        <dd>
                            <p class="caption">半角英数字8文字以上20文字以下</p>
                            <input
                                type="password"
                                id="password"
                                v-model="account.password"
                                autocomplete="current-password"
                                :pattern="regex.password"
                                required
                            />
                        </dd>
                    </div>
                </dl>
                <button type="submit" class="btn-common green submit">
                    {{ currentPath === 'register' ? '登録' : 'ログイン' }}
                </button>
            </form>
        </div>
    </main>
    <router-view></router-view>
</template>
