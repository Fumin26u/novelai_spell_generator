<?php
$home = './';
require_once($home . 'database/commonlib.php');

$user_id = isset($_SESSION['user_id']) ? h($_SESSION['user_id']) : ''; 

$presets = [];
$presets_json = [];
// ログインしている場合、ログインユーザーの登録プロンプト一覧を取得
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();

        if (!isset($_GET['order'])) {
            $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id');
            $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $st->execute();
    
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $pdo->commit();
            $presets = $rows;          
        } else {
            // 更に、検索ボックスで検索された場合内容に沿ってプロンプトを絞り込む
            $search_age = '';
            // 年齢制限項目
            if ($_GET['age'] === 'nolimit') {
                $search_age .= "commands NOT LIKE '%nsfw%'";
            } else if ($_GET['age'] === 'nsfw') {
                $search_age .= "commands LIKE '%nsfw%'";
            }

            // ワード検索項目
            $search_words = [];
            if ($_GET['search_word'] !== '' && isset($_GET['search_item']) && !empty($_GET['search_item'])) {
                foreach ($_GET['search_item'] as $item) {
                    $search_words[] = $item . " LIKE '%" . h($_GET['search_word']) . "%'";
                }
            }
            // ソート項目
            $order = " ORDER BY " . $_GET['sort'] . " " . $_GET['order'];
            // 配列にまとめた検索文字列を整形
            $search_str = $search_age === '' ? '' : ' AND ' . $search_age ;
            $search_str .= empty($search_words) ? '' : ' AND (' . implode(' OR ', $search_words) . ')';
            $search_str .= $order;

            // DBから検索文字列に該当する値のみを取得
            $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id' . $search_str);
            $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $st->execute();
    
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $pdo->commit();
            $presets = $rows;
        }
        
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }      
}
// jsに渡す為のJSON文字列を作成
$presets_json = json_encode($presets, JSON_UNESCAPED_UNICODE);
$title = 'NovelAI プロンプトセーバー';
// デスクリプション
$description = "AIによるイラスト自動生成サービスである「NovelAI」の呪文(プロンプト)の生成・管理を補助するサービスです。マウスクリックによる簡単操作で呪文を生成することや、お気に入りの画像を生成する呪文を保存することが可能です。";
$keywords = "NovelAI,NAI,プロンプト,登録,セーブ,保存,生成,自動";
$canonical = "https://nai-pg.com/register/";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<?php include_once $home . './gtag.inc'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= $home ?>top.css">
<title><?= $title ?></title>
<link rel="canonical" href="<?= $canonical ?>">
<meta property="og:description" content="<?= $description ?>">
<meta name="keywords" content="<?= $keywords ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="NovelAI プロンプトセーバー">
<meta property="og:url" content="<?= $canonical ?>">
<meta property="og:title" content="<?= $title ?>">
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <?php if (isset($_SESSION['user_id'])) { ?>
    <section class="spell-list">
        <h2><?= $_SESSION['user_id'] ?>の登録プロンプト一覧</h2>
        <div class="search-area">
            <div class="open-searchbox">
                <p>検索ボックス</p>
                <button class="btn-common green" onclick="openMenu()" id="open-menu">▽開く</button>
                <button class="btn-common red" onclick="closeMenu()" id="close-menu" style="display:none;">△閉じる</button>
            </div>
            <?php include($home . 'search_form.php') ?>
        </div>
        <div class="description">
            <p><span>オレンジ色</span>の項目をクリックするとデータをコピーできます。</p>
            <span id="copy-alert"></span>
        </div>
        <?php if (isset($_GET['order'])) { ?>
            <p>該当のデータが<?= count($presets) ?>件存在します。</p>
        <?php } ?> 
        <div class="preset-top">
            <div class="preset-list">
                <?php if (!empty($presets)) { ?>
                <div class="preset-content">
                    <?php foreach ($presets as $index => $preset) { ?>
                        <div onclick="selectPreset(<?= $index ?>)" class="preset-card">
                            <img 
                                src="<?= $preset['image'] === null ? $home . 'images/preset/noimage.png' : $home . 'images/preset/thumbnail/' . $preset['image'] ?>" 
                                alt="<?= $preset['description'] ?>"
                            >
                            <p><?= $preset['description'] ?></p>
                        </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <div id="preset-detail" style="display:none;">
                <div id="preset-title-area">
                    <p id="preset-title"></p>
                    <a href="" id="preset-edit">編集</a>
                </div>
                <ul id="select-preset">
                    <li class="preset-image">
                        <img src="" alt="" id="preset-image">
                    </li>
                    <li>
                        <p class="title">nsfw</p>
                        <p class="detail" id="preset-nsfw"></p>
                    </li>
                    <li>
                        <p class="title">プロンプト</p>
                        <p class="detail copy" id="preset-prompt" onclick="copyPreset('preset-prompt')"></p>
                    </li>
                    <li>
                        <p class="title">BANプロンプト</p>
                        <p class="detail copy" id="preset-prompt_ban" onclick="copyPreset('preset-prompt_ban')"></p>
                    </li>
                    <li>
                        <p class="title">シード値</p>
                        <p class="detail copy" id="preset-seed" onclick="copyPreset('preset-seed')"></p>
                    </li>
                    <li>
                        <p class="title">解像度</p>
                        <p class="detail" id="preset-resolution"></p>
                    </li>
                    <li>
                        <p class="title">備考</p>
                        <p class="detail" id="preset-others"></p>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <?php } ?>
</main>
</body>
<script lang="js">
{
    // プリセット内のデータがクリックされた際、その値をコピーする
    function copyPreset(id) {
        const title = document.getElementById('preset-title').innerHTML;
        const text = document.getElementById(id).innerHTML;
        const href = location.href.substr(0,5);
        if (href === 'https') {
            navigator.clipboard.writeText(text);
        } else if (href === 'http:') {
            const input = document.createElement('input');
            document.body.appendChild(input);
            input.value = text;
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        }
        const copyAlert = document.getElementById('copy-alert');
        copyAlert.innerHTML = title + 'をコピーしました。'
    };

    // 検索ボックスの表示可否
    const om = document.getElementById('open-menu');
    const cm = document.getElementById('close-menu');
    const sf = document.getElementById('search-form');
    function openMenu() {
        om.style.display = "none";
        cm.style.display = "inline-block";
        sf.style.display = "block";
    }

    function closeMenu() {
        om.style.display = "inline-block";
        cm.style.display = "none";
        sf.style.display = "none";
    }

    function checkAllBox() {
        const ai = document.getElementById('allitem');
        const si = document.getElementsByName('search_item[]');
        if (ai.checked) {
            for (let i = 0; i < si.length; i++) {
                si[i].checked = true;
            }
        } else {
            for (let i = 0; i < si.length; i++) {
                si[i].checked = false;
            }
        }
    }

    // PHPから文字列化したjsonを受け取り、JSオブジェクトに変換
    const pl = <?= $presets_json ?>;
    function selectPreset(index) {
        const pc = document.getElementsByClassName('preset-card');
        for (let i = 0; i < pc.length; i++) {
            pc[i].classList.remove('selected');
        }
        pc[index].classList.add('selected');

        document.getElementById('preset-detail').style.display = 'block';
        selectedIndex = index;
        document.getElementById('preset-title').innerHTML = pl[index].description;
        document.getElementById('preset-edit').href = './commands.php?preset_id=' + pl[index].preset_id;
        document.getElementById('preset-image').src = pl[index].image === null ? './images/preset/noimage.png' : './images/preset/original/' + pl[index].image;
        document.getElementById('preset-nsfw').innerHTML = pl[index].nsfw === null || !pl[index].nsfw ? 'なし' : 'あり';
        document.getElementById('preset-prompt').innerHTML = pl[index].commands;
        document.getElementById('preset-prompt_ban').innerHTML = pl[index].commands_ban;
        document.getElementById('preset-seed').innerHTML = pl[index].seed;
        document.getElementById('preset-resolution').innerHTML = pl[index].resolution;
        document.getElementById('preset-others').innerHTML = pl[index].others;
    }

    // データ検索がされていない場合検索ボックスの再リロードを行う
    <?php if(!isset($_GET['order'])) { ?>
    window.onload = () => {
        checkAllBox();
    }
    <?php } else { ?>
    // データ検索がされている場合初期状態で検索ボックスを表示
    window.onload = () => {
        openMenu();
    }
    <?php } ?>
}
</script>
</html>