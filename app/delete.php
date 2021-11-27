<?php

// ファイル読み込み
require_once("../class/dao.php");

// セッションスタート
session_start();

// ログイン判定
if(!isset($_SESSION["user_id"])) {
  header("Location: ../view/login.php");
  exit();
}

if(isset($_GET["address_id"])) {
  $user_id = $_SESSION["user_id"];    // ユーザID
  $address_id = $_GET["address_id"];  // アドレスID
  
  try {
    // Daoオブジェクト生成
    $dao = new Dao();
    
    // SQL実行
    $result = $dao->query(
      "select *  from addresses where user_id = :user_id and address_id = :address_id",
      ["user_id", "address_id"],
      [$user_id, $address_id]
      );
    
    if(isset($result[Dao::KEY_DATA])) { // 削除可能なユーザであるか
      // 削除の実行
      $dao->query(
        "delete from addresses where user_id = :user_id and address_id = :address_id", 
        ["user_id", "address_id"], 
        [$user_id, $address_id]);
      
      // セッションに値を登録（成功メッセージ）
      $_SESSION["success_message"] = "削除に成功しました。";
      
      // リダイレクト(メインページへ)
      header("Location: ../");
      exit();
    } else {
      // リダイレクト(削除不可能な場合は、セッションユーザIDが一致しない場合を想定（urlの直接入力）)
      header("Location: ../");
      exit();
    }
  } catch(Exception $e) {
    // リダイレクト(例外時は詳細ページへ）
    $_SESSION["delete_error"] = "システムエラーのため、削除が正常に行われませんでした。";
    header("Location: ../view/detail.php?address_id=${address_id}");
    exit();
  }
} else {
  // リダイレクト（詳細ページからの削除リクエストでない、urlの直接入力）
  header("Location: ../");
  exit();
}

?>