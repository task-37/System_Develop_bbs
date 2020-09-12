<?php

//必須項目の本文が欠けていたら閲覧画面に戻す(リダイレクト)
if (empty($_POST['body'])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./read.php");
    return;
}

//ログインしてなければ閲覧画面に戻す(リダイレクト)
if (empty($_COOKIE["login_id"])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./read.php");
    return;
}

//データベースハンドラ作成
$dbh = new PDO('mysql:host=mysql;dbname=2020techc_db', '2020techc_username', '2020techc_password');

//投稿保存用テーブル bbs_entries に1行insert
//SQLインジェクションを防ぐためにプレースホルダを使う
$insert_sth = $dbh->prepare("INSERT INTO bbs_entries (name, body) VALUES (:name, :body)");
$insert_sth->execute([
    ':name' => $_COOKIE["login_id"],
    ':body' => $_POST['body'],
]);

//書き込み完了したら閲覧画面に戻す
header("HTTP/1.1 302 Found");
header("Location: ./read.php");
return;
?>
