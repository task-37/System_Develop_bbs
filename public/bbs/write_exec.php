<?php
/* セッションについて共通処理 ここから */
$redis = new Redis();
$redis->connect("redis", 6379);
$session_id_cookie_key = "session_id";
$session_id = isset($_COOKIE[$session_id_cookie_key]) ? ($_COOKIE[$session_id_cookie_key]) : null;
if ($session_id === null) {
    $session_id = bin2hex(random_bytes(25));
    setcookie($session_id_cookie_key, $session_id);
}
$redis_session_key = "session-" . $session_id; 
$session_values = $redis->exists($redis_session_key)
    ? json_decode($redis->get($redis_session_key), true)
    : []; 
/* ここまで */

// 確認画面で送られてきたCSRFトークンのチェック
$csrf_tokens = isset($session_values["csrf_tokens"]) ? $session_values["csrf_tokens"] : [];
if (!in_array($_POST['csrf_token'], $csrf_tokens, true)) {
  header("HTTP/1.1 302 Found");
  header("Location: ./write_form.php");
  return;
}

// セッションからフォーム入力値取得
$bbs_temp_values = isset($session_values["bbs_temp_values"])
    ? $session_values["bbs_temp_values"]
    : ["name" => "", "body" => ""];
// 内容チェック
if (empty($bbs_temp_values['name']) || empty($bbs_temp_values['body'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./write_finish.php");
  return;
}

// セッションのフォーム入力値リセット
unset($session_values["bbs_temp_values"]);
$redis->set($redis_session_key, json_encode($session_values));


/* 書き込み処理 ここから */
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');
$insert_sth = $dbh->prepare("INSERT INTO bbs_entries (name, body) VALUES (:name, :body)");
$insert_sth->execute([
    ':name' => $bbs_temp_values['name'],
    ':body' => $bbs_temp_values['body'],
]);
/* ここまで */


header("HTTP/1.1 302 Found");
header("Location: ./write_finish.php");
return;
?>
