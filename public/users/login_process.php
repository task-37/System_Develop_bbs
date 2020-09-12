<?php
// 必須項目のログインIDとパスワードのどちらでも欠けていたら
// 何も処理せずログインフォームに戻す (リダイレクト)

if (empty($_POST['login_id']) || empty($_POST['password'])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php");
    return;
}

//データベースハンドラ作成
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');

//ログインIDが一致する1行だけ取得

$select_sth = $dbh->prepare('SELECT login_id, password FROM users WHERE login_id = :login_id LIMIT 1');
$select_sth->execute([
    ':login_id' => $_POST['login_id'],
]);
$row = $select_sth->fetch();

//行を取得できなかった場合はエラー

if (!$row) {
    print('ログインIDがみつかりませんでした。<a href="./login.php">戻る</a>');
    return;
}

//パスワードチェック //違う場合はエラー

if (!password_verify($_POST['password'], $row['password'])) {
    print('パスワードが間違っています。<a href="./login.php">戻る</a>');
    return;
}

//ログイン状態をCookieに保存
//セキュリティに難あり

setcookie('login_id', $row['login_id'], 0, '/');
//vvsでも使うCookieなので、パスを指定

//ログイン完了したらログイン完了画面に飛ばす

header("HTTP/1.1 302 Found");
header("Location: ./login_finish.php");
return;
?>



