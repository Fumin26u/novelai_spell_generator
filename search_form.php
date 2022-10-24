<form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" id="search-form" style="display:none;">
    <dl>
        <div>
            <dt>年齢制限</dt>
            <dd>
                <input type="radio" name="age" value="all" id="all" <?= !isset($_GET['age']) || $_GET['age'] === 'all' ? ' checked' : '' ?>>
                <label for="all">なし</label>
                <input type="radio" name="age" value="nolimit" id="nolimit" <?= isset($_GET['age']) && $_GET['age'] === 'nolimit' ? ' checked' : '' ?>>
                <label for="nolimit">全年齢のみ</label>
                <input type="radio" name="age" value="nsfw" id="nsfw" <?= isset($_GET['age']) && $_GET['age'] === 'nsfw' ? ' checked' : '' ?>>
                <label for="nsfw">R-18のみ</label>
                <small>※プロンプト内に「nsfw」ワードが存在する場合R-18、しない場合全年齢として扱う</small>
            </dd>
        </div>
        <div>
            <dt>ワード検索</dt>
            <dd>
                <div>
                    <label>検索項目:</label>
                    <input 
                        type="checkbox"
                        id="allitem" 
                        onclick="checkAllBox()" 
                        <?= !isset($_GET['search_item']) || array_search('allitem', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="allitem">全て選択</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="description" 
                        id="description" 
                        <?= isset($_GET['search_item']) && array_search('description', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="description">説明</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="commands" 
                        id="commands" 
                        <?= isset($_GET['search_item']) && array_search('commands', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="commands">プロンプト</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="commands_ban" 
                        id="commands_ban" 
                        <?= isset($_GET['search_item']) && array_search('commands_ban', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="commands_ban">BANプロンプト</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="seed" 
                        id="seed" 
                        <?= isset($_GET['search_item']) && array_search('seed', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="seed">シード値</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="resolution" 
                        id="resolution" 
                        <?= isset($_GET['search_item']) && array_search('resolution', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="resolution">解像度</label>
                    <input 
                        type="checkbox" 
                        name="search_item[]" 
                        value="others" 
                        id="others" 
                        <?= isset($_GET['search_item']) && array_search('others', $_GET['search_item']) !== false ? ' checked' : '' ?>
                    >
                    <label for="others">備考</label>
                </div>
                <div>
                    <label for="search_word">検索ワード:</label>
                    <input type="search" name="search_word" value="<?= isset($_GET['search_word']) ? h($_GET['search_word']) : '' ?>" id="search_word">
                </div>
            </dd>
        </div>
        <div>
            <dt>ソート</dt>
            <dd>
                <div>
                    <input type="radio" name="sort" value="description" id="sort_description" <?= isset($_GET['sort']) && $_GET['sort'] === 'description' ? ' checked' : '' ?>>
                    <label for="sort_description">説明</label>
                    <input type="radio" name="sort" value="seed" id="sort_seed" <?= isset($_GET['sort']) && $_GET['sort'] === 'seed' ? ' checked' : '' ?>>
                    <label for="sort_seed">シード値</label>
                    <input type="radio" name="sort" value="resolution" id="sort_resolution" <?= isset($_GET['sort']) && $_GET['sort'] === 'resolution' ? ' checked' : '' ?>>
                    <label for="sort_resolution">解像度</label>
                    <input type="radio" name="sort" value="updated_at" id="updated_at" <?= !isset($_GET['sort']) || $_GET['sort'] === 'updated_at' ? ' checked' : '' ?>>
                    <label for="updated_at">日付</label>
                </div>
                <div>
                    <input type="radio" name="order" value="asc" id="asc"<?= !isset($_GET['order']) || $_GET['order'] === 'asc' ? ' checked' : '' ?>>
                    <label for="asc">昇順</label>
                    <input type="radio" name="order" value="desc" id="desc" <?= isset($_GET['order']) && $_GET['order'] === 'desc' ? ' checked' : '' ?>>
                    <label for="desc">降順</label>
                </div>
            </dd>
        </div>
        <input type="submit" value="検索" class="btn-common submit">
    </dl>
</form>
