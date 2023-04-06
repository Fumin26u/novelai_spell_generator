<script setup lang="ts">
import apiPath from '@/assets/ts/apiPath'
import HeaderComponent from '@/components/HeaderComponent.vue'
import '@/assets/scss/manageAccount.scss'
import ApiManager from '@/components/api/apiManager'
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const token = route.query

// 入力フォームのアカウント情報
const account = ref<{ [key: string]: string }>({
    method: 'sendToken',
    email: '',
    user_id: '',
})

// パスワード更新用の情報
const updatePassword = ref<{ [key: string]: string }>({
    method: 'updatePassword',
    user_id: '',
    password: '',
})

// トークンの認証チェック
const isTokenActive = ref<boolean>(false)

const responseMessage = ref<string>('')
const formUrl = apiPath + 'manageAccount.php'
// URLクエリにトークンが設定されている場合、認証を行う
const verifyToken = async () => {
    const sendData = {
        method: 'verifyToken',
        token: token.token,
    }
    const formData = JSON.stringify(sendData)
    const apiManager = new ApiManager()
    const response = await apiManager.post(formUrl, formData)

    if (response.error) {
        responseMessage.value = response.content
        return
    }

    updatePassword.value.user_id = response.user_id
    isTokenActive.value = true
}

// 登録ボタンが押された際、バリデーションを行いアカウント登録またはログイン
const regex = {
    user_id: '^.{6,20}$',
    password: '^([a-zA-Z0-9]{8,20})$',
}

const userIdValidation = () => {
    let errorMessage = ''
    if (!new RegExp(regex.user_id).test(account.value.user_id)) {
        errorMessage = 'ユーザーIDの入力内容が空または不正です。'
    }
    return errorMessage
}

const passwordValidation = () => {
    let errorMessage = ''
    if (!new RegExp(regex.password).test(account.value.password)) {
        errorMessage = 'パスワードの入力内容が空または不正です。'
    }
    return errorMessage
}

const submitAccountData = async () => {
    
    const apiManager = new ApiManager()
    // メール送信
    if (updatePassword.value.password === '') {
        // 入力内容がパターンにマッチしない場合エラーメッセージを出力
        responseMessage.value = userIdValidation()
        if (responseMessage.value !== '') return
    
        // バリデーションを通過したらAPIを叩いてユーザーデータを登録
        const sendData = { ...account.value }
    
        const formData = JSON.stringify(sendData)
        const response = await apiManager.post(formUrl, formData)
        // 入力内容が不正の場合
        if (response.error) {
            responseMessage.value = response.content
            return
        }
    
        // 正しくメール送信された場合
        responseMessage.value =
            'メールを送信しました。10分以内にパスワードの更新を行ってください。'
    } else {
    // パスワード更新
        // 入力内容がパターンにマッチしない場合エラーメッセージを出力
        responseMessage.value = passwordValidation()
        if (responseMessage.value !== '') return

        // APIを叩いてパスワードを更新
        const sendData = { ...updatePassword.value }

        const formData = JSON.stringify(sendData)
        const response = await apiManager.post(formUrl, formData)
        // 入力内容が不正の場合
        if (response.error) {
            responseMessage.value = response.content
            return
        }

        // データ更新成功時ログイン画面に遷移
        alert('パスワードを更新しました。')
        router.push('./login')
    }

}

onMounted(() => {
    if (token.token !== undefined) verifyToken()
})
</script>

<template lang="">
    <HeaderComponent></HeaderComponent>
    <main class="account-manage">
        <div class="title-area" :style="'border-bottom: 1px solid #888'">
            <h2>アカウント情報更新</h2>
            <p>{{ responseMessage }}</p>
        </div>
        <div class="description">
            <p v-if="!isTokenActive">
                以下のフォームにあなたのメールアドレスとユーザーIDを入力し、[送信]ボタンを押してください。<br />
                ボタン押下後、入力したメールアドレスにパスワード更新用のリンクが添付されているので、<br />
                そのリンクからパスワードを登録し直してください。
            </p>
            <p v-else>
                以下のフォームに新しいパスワードを入力し、[更新]ボタンを押してください。<br />
                ボタン押下後、ログインページに遷移するので、新しいパスワードを利用してログインしてください。
            </p>
        </div>
        <div class="form-area">
            <form @submit.prevent="submitAccountData()">
                <dl v-if="!isTokenActive">
                    <div>
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
                </dl>
                <dl v-else>
                    <div>
                        <dt>新規パスワード</dt>
                        <dd>
                            <p class="caption">半角英数字8文字以上20文字以下</p>
                            <input
                                type="password"
                                v-model="updatePassword.password"
                                :pattern="regex.password"
                                required
                            />
                        </dd>
                    </div>
                </dl>
                <button type="submit" class="btn-common green submit">
                    送信
                </button>
            </form>
        </div>
    </main>
    <router-view></router-view>
</template>
