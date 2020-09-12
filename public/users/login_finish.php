<html>
<head>
  <title>ログイン 完了</title>
</head>
<body>
  <h1>ログイン 完了</h1>
  <p>
    ログインid: <?= htmlspecialchars($_COOKIE["login_id"]) ?> でログインできました。<br>
    <a href="/bbs/read.php">掲示板へ</a>
  </p>
</body>
