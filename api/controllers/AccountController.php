<?php

class AccountController {
    private $response;

    public function __construct() {
        $this->response = [
            'error' => false,
            'content' => '',
            'user_id' => '',
        ];
    }

    // 不正なデータ送信が行われた場合のエラーログ出力処理
    public function sendErrorLog($content = '不正な送信が行われました。') {
        $this->response['error'] = true;
        $this->response['content'] = $content;
        return $this->response;
    }

    // 送られてきたメアドとユーザーIDが既存かどうか調べる
    private function confirmIsExistSameData($email, $user_id) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT email, user_id FROM users WHERE user_id = :user_id OR email = :email');
            $st->bindValue(':email', $email, PDO::PARAM_STR);
            $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $st->execute();
            
            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
            return !empty($rows) ? '既に使用されているメールアドレスまたはユーザーIDです。' : '';
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // アカウントの登録処理
    public function register($post) {
        $error = $this->confirmIsExistSameData(h($post['email']), h($post['user_id']));

        if ($error !== '') {
            $this->response['error'] = true;
            $this->response['content'] = $error;
            return $this->response;
        }; 

        $pdo = dbConnect();
        $pdo->beginTransaction();

        $st = $pdo->prepare('INSERT INTO users (email, user_id, password) VALUES (:email, :user_id, :password)');
        $st->bindValue(':email', h($post['email']), PDO::PARAM_STR);
        $st->bindValue(':user_id', h($post['user_id']), PDO::PARAM_STR);
        $st->bindValue(':password', password_hash(h($post['password']), PASSWORD_DEFAULT), PDO::PARAM_STR);
        $st->execute();

        $pdo->commit();
        $this->response['content'] = 'アカウント登録が完了しました。';
        return $this->response;
    }

    // アカウントのログイン処理
    public function login($post) {
        $error = '';

        $pdo = dbConnect();
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $st->bindValue(':user_id', h($post['user_id']), PDO::PARAM_STR);
        $st->execute();

        $rows = $st->fetch(PDO::FETCH_ASSOC);
        $pdo->commit();

        // 返された配列が空の場合、ユーザ名が存在しない
        if (empty($rows)) {
            $error = '入力されたユーザー名は存在しません。';
            // パスワードの照合
        } else if(!password_verify(h($post['password']), $rows['password'])) {
            $error = '入力されたパスワードが間違っています。';
        }

        if ($error !== '') {
            $this->response['error'] = true;
            $this->response['content'] = $error;
        } else {
            session_start();
            $_SESSION['user_id'] = h($post['user_id']);
            $this->response['content'] = 'ログイン認証が完了しました。';
        }
        return $this->response;
    }

    // ユーザーIDを取得する
    public function getUserData() {
        if ($_SERVER['HTTP_HOST'] === 'localhost') {
            $this->response['user_id'] = 'Fumiya0719';
            $this->response['content'] = 'ユーザーIDを取得しました。';
        } else {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
                $this->response['error'] = true;
                $this->response['content'] = 'ユーザーIDの取得に失敗しました。';
            } else {
                $this->response['user_id'] = h($_SESSION['user_id']);
                $this->response['content'] = 'ユーザーIDを取得しました。';
            }
        }
        return $this->response;
    }

    // ログアウト処理
    public function logout() {
        $_SESSION = [];
        $this->response['content'] = 'ログアウトしました。';
        return $this->response;
    }

    // パスワード更新用のトークン送信
    public function sendToken($post) {
        // 指定されたメアドとユーザーIDが存在するか調べる
        $isExistUserData = $this->confirmIsExistSameData(h($post['email']), h($post['user_id'])) === '' ? false : true;

        // メアドとユーザーIDが存在しない場合、エラーを返して終了
        if (!$isExistUserData) {
            $this->response['error'] = true;
            $this->response['content'] = '指定したメールアドレスまたはユーザーIDは存在しません。';
            return $this->response;
        }

        // ワンタイムトークンの生成
        $token = bin2hex(random_bytes(16));
        $url = 'https://nai-pg.com/#/forgotPassword?token=' + $token;

        // トークンをDBに登録
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            // トークンが既存の場合の更新のため一度削除
            $st = $pdo->prepare('DELETE FROM update_token WHERE user_id = :user_id');
            $st->bindValue(':user_id', h($post['user_id']), PDO::PARAM_STR);
            $st->execute();

            $sql = <<< SQL
            INSERT INTO update_token 
            (user_id, token, is_token_active, created_at)
            VALUES
            (:user_id, :token, 1, NOW())
            SQL;
            $st = $pdo->prepare($sql);
            $st->bindValue(':user_id', h($post['user_id']), PDO::PARAM_STR);
            $st->bindValue(':token', $token, PDO::PARAM_STR);
            $st->execute();

            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }

        // メールの送信
        $mail_content = <<<EOM

        ＝＝＝＝＝＝＝＝＝＝パスワードの更新＝＝＝＝＝＝＝＝＝＝

        NovelAI プロンプトジェネレーターのご利用ありがとうございます。
        10分以内に、以下のリンクからパスワードの更新をお願いします。

        $url

        本メールは送信専用です。返信は受付できませんのでご了承ください。

        EOM;
        
        $to = h($post['email']);
        $from = 'no-reply@nai-sg.com';

        // メールヘッダ
        $header = 'From: ' . mb_encode_mimeheader('NovelAI スペルジェネレーター', 'UTF-8') . '<' . $from . '>';
    
        // タイトル
        $title = 'パスワードの更新 | NovelAI スペルジェネレーター';
    
        // 本文
        $message = '';
        $message .= brReplace(periodReplace($mail_content));
    
        // 送信＋判定
        $isSentMail = mb_send_mail($to, $title, $message, $header);

        if ($isSentMail) {
            $this->response['content'] = 'メール送信に成功しました。';
        } else {
            $this->response['error'] = true;
            $this->response['content'] = 'メール送信に失敗しました。お手数ですが、時間を置いて再度お試しいただけますようよろしくお願いします。';
        }
    }

    // パスワード更新
    public function updatePassword() {

    }
}