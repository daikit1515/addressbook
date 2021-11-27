<?php

// セッションスタート
session_start();

// ログアウトメッセージ登録
$_SESSION["success_message"] = "ログアウトしました。";

// セッションに登録した値を削除（ユーザID、ユーザ名）
unset($_SESSION["user_id"]);
unset($_SESSION["user_name"]);

// リダイレクト(ログインページへ)
header("Location: ../view/login.php");
exit();

?>