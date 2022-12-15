<script setup lang="ts">
import { ref } from 'vue'
import apiPath from '@/assets/ts/apiPath'
import '@/assets/scss/header.scss'
import ApiManager from './api/apiManager'
import user_id from '@/components/api/getUserId'

// ハンバーガーメニューの表示状態
const isOpenHBGMenu = ref<boolean>(false)

// ページ遷移用のURI
// テストサーバーも含める為パス名を取得して結合
const originPath = new URL(location.href).origin + location.pathname

// ログアウトリンクが押された場合APIに伝える
const apiManager = new ApiManager()
const execLogout = async () => {
    const formUrl = apiPath + 'manageAccount.php'
    const formData = JSON.stringify({
        method: 'logout',
        user_id: user_id,
    })
    await apiManager.post(formUrl, formData)
}
</script>

<template>
    <header class="header">
        <h1>
            <a href="https://novelai.net/image">NovelAI</a>
            プロンプトジェネレーター
        </h1>
        <div :class="[isOpenHBGMenu ? 'link-area open' : 'link-area']">
            <div class="main-link">
                <a href="./" class="prompt-generator"
                    >プロンプトジェネレーター</a
                >
                <a href="./#/saves/" class="prompt-saver">プロンプトセーバー</a>
                <a
                    href="./#/master/"
                    class="master-register"
                    v-if="user_id === 'Fumiya0719'"
                    >マスタデータ登録</a
                >
            </div>
            <div class="sub-link">
                <a href="./#/terms_of_use">利用規約</a>
                <a href="./#/privacy_policy">プライバシーポリシー</a>
            </div>
            <div class="account-link">
                <a :href="originPath + '#/login'" v-if="user_id === ''"
                    >ログイン</a
                >
                <a :href="originPath + '#/register'" v-if="user_id === ''"
                    >アカウント登録</a
                >
                <p v-if="user_id !== ''">{{ user_id }}さん</p>
                <a
                    :href="originPath + '#/login'"
                    @click="execLogout()"
                    v-if="user_id !== ''"
                    >ログアウト</a
                >
            </div>
        </div>
        <div
            :class="[isOpenHBGMenu ? 'hbg-menu open' : 'hbg-menu']"
            @click="isOpenHBGMenu = isOpenHBGMenu ? false : true"
        >
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </header>
</template>
