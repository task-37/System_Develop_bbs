<?php
/* セッションについて共通処理 ここから */
$redis = new Redis();
$redis->connect("redis", 6379);
$session_id_cookie_key = "session_id";
$session_id = isset($_COOKIE[$session_id_cookie_key]) ? ($_COOKIE[$session_id_cookie_key]) : null;
if ($session_id === null) {
    $session_id = bin2hex(random_bytes(25));
    setcookie($session_id_cookie_key, $session_id, 0, '/');
}
$redis_session_key = "session-" . $session_id; 
$session_values = $redis->exists($redis_session_key)
    ? json_decode($redis->get($redis_session_key), true)
    : []; 
/* ここまで */

//ログインしている自分のデータの取得
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');
$select_sth = $dbh->prepare('SELECT id, login_id, password, display_name FROM users WHERE id = :id LIMIT 1');
$select_sth->execute([
    ':id' => $session_values["login_user_id"],
]);
$login_user = $select_sth->fetch();

//フォームから値が送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 更新
    $update_sth = $dbh->prepare('UPDATE users SET display_name = :display_name WHERE id = :id LIMIT 1');
    $update_sth->execute([
         ':display_name' => $_POST['display_name'],
         ':id' => $login_user['id'],
    ]); 
    header("HTTP/1.1 302 Found");
    header("Location: ./setting.php?finish=1");
    return;
}
?>




<html>
<head>
  <title>設定</title>
</head>
<body>
  <h1>ユーザー情報 設定</h1>
  <?php if(!empty($_GET['finish'])): ?>
  <div style="margin: 2em;">
    設定が完了しました。
  </div>
  <?php endif; ?>
  <form method="post">
    <dl>

      <dt>
        ログインID
      </dt>
      <dd>
        <?= htmlspecialchars($login_user["login_id"]) ?>  
      </dd>
      <hr>

      <dt>
        表示名
      </dt>
      <dd>
         <input type="text" name="display_name" value="<?= htmlspecialchars($login_user["display_name"]) ?>">
      </dd>
      <hr>

    </dl>
    <div>
      <button type="submit">決定</button>
    </div>
  </form>
  <div style="text-align: right; margin-top: 1em;">
    <a href="/bbs/read.php">掲示板へ</a>
  </div>
</body>
