<header>
    <div class="header-left">
        <h1>NovelAI コマンド登録機</h1>
    </div>
    <div class="header-right">
        <?php if (!isset($_SESSION['user_id'])) { ?>
            <a href="<?= $home ?>login.php">ログイン</a>
            <a href="<?= $home ?>register.php">アカウント登録</a>
        <?php } else { ?>
            <a href="<?= $home ?>?logout">ログアウト</a>
        <?php } ?>
            
    </div>
</header>