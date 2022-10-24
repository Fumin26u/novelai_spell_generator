<header>
    <div class="header-left">
        <h1><a href="https://novelai.net/">NovelAI</a> プロンプトセーバー</h1>
    </div>
    <div class="header-right">
        <?php if (!isset($_SESSION['user_id'])) { ?>
            <a href="<?= $home ?>login.php">ログイン</a>
            <a href="<?= $home ?>register.php">アカウント登録</a>
        <?php } else { ?>
            <a href="http://localhost:8081/" target="_blank">プロンプトジェネレーター</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 'Fumiya0719') { ?>
            <a href="<?= $home ?>/master">マスタデータ一覧</a>
            <?php } ?>
            <a href="<?= $home ?>">登録プロンプト一覧</a>
            <a href="<?= $home ?>commands.php">プロンプト登録</a>
            <a href="<?= $home ?>?logout">ログアウト</a>
        <?php } ?>      
    </div>
</header>