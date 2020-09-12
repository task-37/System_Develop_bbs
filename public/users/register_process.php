<?php
//必須項目のログインIDとパスワードのどちらでも欠けていたら閲覧画面に戻す

if (empty($_POST['login_id']) || empty($_POST['password'])) {
	  header("HTTP/1.1 302 Found");
	    header("Location: ./register.php");
	    return;
}

//データベースハンドラ作成
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');

//会員テーブルusersに1行insert
//SQLインジェクションを防ぐためにプレースフォルダを使う

$insert_sth = $dbh->prepare("INSERT INTO users (login_id, password) VALUES (:login_id, :password)");
$insert_sth->execute([
  ':login_id' => $_POST['login_id'],
  // パスワードは暗号化して保存
  ':password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
]);

//登録完了したら会員登録完了画面に飛ばす
header("HTTP/1.1 302 Found");
header("Location: ./register_finish.php");
return;
?>
