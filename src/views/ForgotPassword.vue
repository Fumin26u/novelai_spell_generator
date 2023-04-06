<script setup lang="ts">
import apiPath from '@/assets/ts/apiPath'
import HeaderComponent from '@/components/HeaderComponent.vue'
import '@/assets/scss/manageAccount.scss'
import ApiManager from '@/components/api/apiManager'
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const currentURL = computed(() => route.path)
const token = route.query

// 入力フォームのアカウント情報
const account = ref<{ [key: string]: string }>({
    method: 'sendToken',
    email: '',
    user_id: '',
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

    return errorMessage
}

const submitAccountData = async () => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    responseMessage.value = inputValidation()
    if (responseMessage.value !== '') return

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const sendData = { ...account.value }

    const formData = JSON.stringify(sendData)
    const apiManager = new ApiManager()
    const response = await apiManager.post(formUrl, formData)
    // 入力内容が不正の場合
    if (response.error) {
        responseMessage.value = response.content
        return
    }

    // 正しくメール送信された場合
    responseMessage.value = 'メールを送信しました。10分以内にパスワードの更新を行ってください。'

    // データ更新成功時ログイン画面に遷移
    // router.push('./login')
}
</script>

<template lang="">
    <HeaderComponent></HeaderComponent>
    <main class="account-manage">
        <div class="title-area">
            <h2>アカウント情報更新</h2>
            <p>{{ responseMessage }}</p>
        </div>
        <div class="description" v-if="token.token === undefined">
            以下のフォームにあなたのメールアドレスとユーザーIDを入力し、[送信]ボタンを押してください。<br />
            ボタン押下後、入力したメールアドレスにパスワード更新用のリンクが添付されているので、<br />
            そのリンクからパスワードを登録し直してください。
        </div>
        <div class="form-area">
            <form @submit.prevent="submitAccountData()">
                <dl>
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
                <button type="submit" class="btn-common green submit">
                    送信
                </button>
            </form>
        </div>
    </main>
    <router-view></router-view>
</template>
