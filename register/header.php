<header>
    <div class="header-left">
        <h1><a href="https://novelai.net/">NovelAI</a> プロンプトセーバー</h1>
    </div>
    <div class="tou-area">
        <a href="<?= $home ?>t/terms_of_use.php">利用規約</a>
        <a href="<?= $home ?>t/privacy_policy.php">プライバシーポリシー</a>
    </div>
    <div class="header-right">
            <a href="https://nai-pg.com/" target="_blank">プロンプトジェネレーター</a>
        <?php if (!isset($_SESSION['user_id'])) { ?>
            <a href="<?= $home ?>login.php">ログイン</a>
            <a href="<?= $home ?>register.php">アカウント登録</a>
        <?php } else { ?>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 'Fumiya0719') { ?>
            <a href="<?= $home ?>master">マスタデータ一覧</a>
            <?php } ?>
            <a href="<?= $home ?>../#/saves/">登録プロンプト一覧</a>
            <a href="<?= $home ?>commands.php">プロンプト登録</a>
            <a href="<?= $home ?>?logout">ログアウト</a>
        <?php } ?>      
    </div>
</header>