<template>
    <div class="header">
        <h1><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
        <div class="link-area">
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
    </div>
</template>
<script lang="ts">
import { computed } from 'vue'
import '../assets/scss/header.scss'

export default {
    props: {
        user: String,
    },
    setup(props: any) {
        const user_id = computed(() => props.user)

        return {
            user_id,
        }
    }
}
</script>