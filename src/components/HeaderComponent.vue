<template>
    <div class="header">
        <h1><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
        <div :class="[isOpenHBGMenu ? 'link-area open':'link-area' ]">
            <div class="main-link">
                <a href="https://nai-pg.com/" class="prompt-generator">プロンプトジェネレーター</a>
                <a href="https://nai-pg.com/#/saves/" class="prompt-saver">プロンプトセーバー</a>
                <a href="https://nai-pg.com/register/commands.php" class="preset-register">プリセット登録</a>
            </div>
            <div class="sub-link">
                <a href="https://nai-pg.com/register/t/terms_of_use.php" target="_blank">利用規約</a>
                <a href="https://nai-pg.com/register/t/privacy_policy.php" target="_blank">プライバシーポリシー</a>
            </div>
            <div class="account-link">
                <a href="./register/login.php" v-if="user_id === ''">ログイン</a>
                <a href="./register/register.php" v-if="user_id === ''">アカウント登録</a>
                <p v-if="user_id !== ''">{{ user_id }}さん</p>
                <a href="./register/index.php?logout" v-if="user_id !== ''">ログアウト</a>
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
    </div>
</template>
<script lang="ts">
import { computed, ref } from 'vue'
import '../assets/scss/header.scss'

export default {
    props: {
        user: String,
    },
    setup(props: any) {
        const user_id = computed(() => props.user)
        const isOpenHBGMenu = ref<boolean>(false)

        return {
            user_id,
            isOpenHBGMenu: isOpenHBGMenu,
        }
    }
}
</script>