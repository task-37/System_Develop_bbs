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

$csrf_tokens = isset($session_values["csrf_tokens"]) ? $session_values["csrf_tokens"] : []; 
if (!in_array($_POST['csrf_token'], $csrf_tokens, true)) {
  header("HTTP/1.1 302 Found");
  header("Location: ./read.php");
  return;
}

//データベースハンドラ作成
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');

// 主キーのIDが一致する1行だけ取得
$select_user_sth = $dbh->prepare('SELECT id, login_id, password FROM users WHERE id = :id LIMIT 1');
$select_user_sth->execute([
    ':id' => $session_values["login_user_id"],
]);
$login_user = $select_user_sth->fetch();

if (empty($login_user) || empty($_POST['body'])) { 
  header("HTTP/1.1 302 Found");
  header("Location: ./read.php");
  return;
}

//投稿保存用テーブル bbs_entries に1行insert
//SQLインジェクションを防ぐためにプレースホルダを使う
$insert_sth = $dbh->prepare("INSERT INTO bbs_entries (name, body, user_id) VALUES (:name, :body, :user_id)");
$insert_sth->execute([
    ':name' => $login_user["login_id"],
    ':body' => $_POST['body'],
    ':user_id' => $login_user["id"],
]);

//書き込み完了したら閲覧画面に戻す
header("HTTP/1.1 302 Found");
header("Location: ./read.php");
return;
?>
