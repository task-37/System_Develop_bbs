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

// CSRF対策のトークンの生成 & セッションに保存
 $csrf_token = bin2hex(random_bytes(25));
 $csrf_tokens = isset($session_values["csrf_tokens"]) ? $session_values["csrf_tokens"] : []; 
 array_push($csrf_tokens, $csrf_token);
 $session_values["csrf_tokens"] = $csrf_tokens;
 $redis->set($redis_session_key, json_encode($session_values));

// データベースハンドラ作成
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');


// 主キーのIDが一致する1行だけ取得
$select_user_sth = $dbh->prepare('SELECT id, login_id, password FROM users WHERE id = :id LIMIT 1');
$select_user_sth->execute([
    ':id' => $session_values["login_user_id"],
]);
$login_user = $select_user_sth->fetch();

// 全行取得 id降順(新しい投稿が上にくる)
$select_sth = $dbh->prepare('
    SELECT
        bbs_entries.name AS bbs_entries__name,
        bbs_entries.body AS bbs_entries__body,
        bbs_entries.created_at AS bbs_entries__created_at,
        users.id AS users__id,
        users.login_id AS users__login_id,
        users.created_at AS users__created_at,
        users.display_name AS users__display_name
    FROM bbs_entries
    LEFT OUTER JOIN users
        ON bbs_entries.user_id = users.id
    ORDER BY bbs_entries.id DESC
 ');
$select_sth->execute();
$rows = $select_sth->fetchAll();
?>
<!DOCTYPE html>
<head>
  <title>掲示板</title>
</head>
<body>
  <h1>掲示板</h1>
<!-- 投稿用フォーム -->
<!--ログインしている場合にのみ表示する-->
  <?php if(!empty($session_values["login_user_id"])): ?>
  <form method="POST" action="./write.php" style="margin: 2em;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <div>
      <?php if(empty($login_user["display_name"])): ?>
      名前(ログインid): <?= htmlspecialchars($login_user["login_id"]) ?>
      <?php else: ?>
      名前: <?= htmlspecialchars($login_user["display_name"]) ?>
      <?php endif; ?>
      <small><a href="/users/setting.php">設定はこちら</a></small>
    </div>
    <div>
      <textarea name="body" rows="5" cols="100" required></textarea>
    </div>
    <button type="submit">投稿</button>
  </form>
  <?php else: ?>
   投稿するには<a href="/users/login.php">ログイン</a>してください。
  <?php endif; ?>
<!-- 投稿用フォームはここまで　-->
<hr>

<!-- 投稿の表示 -->

  <?php foreach ($rows as $row) : ?>
  <div style="margin: 2em;">
    <span>
      <?php if(empty($row['users__display_name'])): ?>
      <?= htmlspecialchars($row['bbs_entries__name']) ?>さん
      <?php else: ?>
      <?= htmlspecialchars($row['users__display_name']) ?>さん
      <?php endif; ?>
      (<?= $row['users__created_at'] ?>登録) 
      の投稿
    </span>
    <span>(投稿日: <?= $row['bbs_entries__created_at'] ?>)</span>
    <div>
      <?= nl2br(htmlspecialchars($row['bbs_entries__body'])) ?>
    </div>
  </div>
  <hr>
  <?php endforeach; ?>
  <!-- 投稿の表示はここまで -->
</body>
</html>
