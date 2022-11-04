<template>
    <div class="header">
        <h1><a href="https://novelai.net/image">NovelAI</a> プロンプトジェネレーター</h1>
        <div class="link-area">
            <a href="https://nai-pg.com/register/" target="_blank" class="prompt-saver">プロンプトセーバー</a>
            <a href="https://nai-pg.com/register/t/terms_of_use.php" target="_blank">利用規約</a>
            <a href="https://nai-pg.com/register/t/privacy_policy.php" target="_blank">プライバシーポリシー</a>
        </div>
    </div>
</template>
<script>
export default {
    
}
</script>
<style lang="scss" scoped>
.header {
    border-bottom: 2px dashed #888;
    padding: 0.5em 0;
    margin: 0 0 1em;
}
.header > h1, .header > .link-area {
    display: inline-block;
    vertical-align: center;
    margin-right: 3em;
}
.header > .link-area > a {
    margin: 0 1em;
}
.header > .link-area .prompt-saver {
    font-size: 18px;
    font-weight: bold;
}
</style>