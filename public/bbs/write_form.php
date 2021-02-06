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

// セッションからフォームの値を取得
$bbs_temp_values = isset($session_values["bbs_temp_values"])
    ? $session_values["bbs_temp_values"]
    : ["name" => "", "body" => ""];
?>

<html>
<head>
  <title>投稿フォーム</title>
</head>
<body>
  <h1>投稿フォーム</h1>
  <form method="POST" action="./write_confirm.php" style="margin: 2em;">
    <div>
      名前: <input type="text" name="name" value="<?= htmlspecialchars($bbs_temp_values["name"]) ?>" required>
    </div>
    <div>
      <textarea name="body" rows="5" cols="100" required
        ><?= htmlspecialchars($bbs_temp_values["body"]) ?></textarea>
    </div>
    <button type="submit">確認へ</button>
  </form>
</body>
