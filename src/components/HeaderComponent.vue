<template>
    <header class="header">
        <h1><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
        <div :class="[isOpenHBGMenu ? 'link-area open':'link-area' ]">
            <div class="main-link">
                <a href="./" class="prompt-generator">プロンプトジェネレーター</a>
                <a href="./#/saves/" class="prompt-saver">プロンプトセーバー</a>
                <a href="https://nai-pg.com/register/master/" class="master-register" v-if="user_id === 'Fumiya0719'">マスタデータ登録</a>
            </div>
            <div class="sub-link">
                <a href="https://nai-pg.com/register/t/terms_of_use.php" target="_blank">利用規約</a>
                <a href="https://nai-pg.com/register/t/privacy_policy.php" target="_blank">プライバシーポリシー</a>
            </div>
            <div class="account-link">
                <a :href="originPath + '#/login'" v-if="user_id === ''">ログイン</a>
                <a :href="originPath + '#/register'" v-if="user_id === ''">アカウント登録</a>
                <p v-if="user_id !== ''">{{ user_id }}さん</p>
                <a href="#" @click.prevent.stop="execLogout()" v-if="user_id !== ''">ログアウト</a>
            </div>
        </div>
        <div 
            :class="[isOpenHBGMenu ? 'hbg-menu open':'hbg-menu']" 
            @click="isOpenHBGMenu = isOpenHBGMenu ? false:true"
        >
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </header>
</template>
<script lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import registerPath from '@/assets/ts/registerPath'
import '../assets/scss/header.scss'
import axios from 'axios'

export default {
    props: {
        user: String,
    },
    setup(props: any) {
        const router = useRouter()
        const user_id = computed(() => props.user)
        const isOpenHBGMenu = ref<boolean>(false)

        // ページ遷移用のURI
        // テストサーバーも含める為パス名を取得して結合
        const originPath = new URL(location.href).origin + location.pathname

        // ログアウトリンクが押された場合APIに伝える
        const formUrl = registerPath + 'api/manageAccount.php'
        const execLogout = async() => {
            const formData = JSON.stringify({
                method: 'logout',
                user_id: user_id.value,
            })
            await axios.post(formUrl, formData).then(() => {
                router.push(originPath + '#/login')
            })
        }

        return {
            user_id,
            isOpenHBGMenu: isOpenHBGMenu,
            originPath,

            execLogout,
        }
    }
}
</script>