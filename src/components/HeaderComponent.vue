<template>
    <div class="header">
        <h1><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
        <div class="link-area">
            <div class="main-link">
                <a href="https://nai-pg.com/" target="_blank" class="prompt-generator">プロンプトジェネレーター</a>
                <a href="https://nai-pg.com/saves/" target="_blank" class="prompt-saver">プロンプトセーバー</a>
                <a href="https://nai-pg.com/register/commands.php" target="_blank" class="preset-register">プリセット登録</a>
            </div>
            <div class="sub-link">
                <a href="https://nai-pg.com/register/t/terms_of_use.php" target="_blank">利用規約</a>
                <a href="https://nai-pg.com/register/t/privacy_policy.php" target="_blank">プライバシーポリシー</a>
            </div>
        </div>
    </div>
</template>
<script lang="ts">
export default {
    
}
</script>
<style lang="scss" scoped>
.header {
    border-bottom: 2px dashed #888;
    padding: 8px 0;
    margin: 0 0 1em;
    display: flex;
    justify-content: space-between;
    align-items: center;
    > h1 {
        width: 20%;
    }
    > .link-area {
        width: 80%;
        display: flex;
        justify-content: space-between;
        > .main-link {
            width: 60%;
        }
        > .main-link a {
            margin: 0 1em;
            font-weight: bold;
            font-size: 16px;
        }
        > .sub-link a {
            margin: 0 1em;
            font-size: 14px;
        }
    }
}
</style>