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

$user_info = null;      // 取得情報
$error_delete = null;   // 削除エラー
$error_message = null;  // 取得エラー

if(isset($_GET["address_id"]) || isset($_GET["myself"])) {
  $user_id = $_SESSION["user_id"];  // ユーザID
  
  if(isset($_SESSION["delete_error"])) {  // 削除失敗時のメッセージ取得
    $error_delete = $_SESSION["delete_error"];
    unset($_SESSION["delete_error"]);
  }

  try {
    // Daoオブジェクト生成
    $dao = new Dao();
    
    // SQL実行
    $result = null;
    if(isset($_GET["address_id"])) {  // 本人以外
      $address_id = $_GET["address_id"];// アドレスID
      $result = $dao->query(
        "select * from addresses where user_id = :user_id and address_id = :address_id",
        [":user_id", ":address_id"],
        [$user_id, $address_id]
        );
    } else {  // 本人
      $result = $dao->query(
        "select * from userinfos where user_id = :user_id",
        [":user_id"],
        [$user_id]);
    }

    if(!isset($result[Dao::KEY_DATA])) {
      // リダイレクト(取得失敗時は、一覧ページからのリクエストではない（urlの直接入力）)
      header("Location: ../");
      exit();
    }
    
    $user_info = $result[Dao::KEY_DATA][0];
  } catch(Exception $e) {
    // 例外時はエラーメッセージを表示
    $error_message = "システムエラーが発生しているため表示ができません。";
  }
} else {
  // リダイレクト(GETパラメータなし（urlの直接入力）)
  header("Location: ../");
  exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>詳細-AddressBook</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="../css/style.css">
  </head>
  <body>
    <!--ヘッダー-->
    <header id="header" class="flex-box">
      <div class="header-left">
        <h1 class="site-title"><a href="../">AddressBook</a></h1>
      </div>
      <div class="header-right">
        <a href="create.php">新規登録</a>
        <a href="../app/logout.php" onclick="return confirm('本当にログアウトしてもよろしいでしょうか？')">ログアウト</a>
      </div>
      
      <div class="menu-btn">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </header>
    
    <!--ユーザ名-->
    <div class="login-user">
      <p><?= $_SESSION["user_name"] ?>さん、ログイン中</p>
    </div>
    
    <!--登録フォーム-->
    <div class="content-wrapper">
      <h2 class="page-title">User Detail</h2>
      <form class="form-wrapper detail" action="edit.php" method="POST">
<?php if(isset($error_delete)) : ?>
        <!--削除エラーメッセージ-->
        <p class="fail-message"><?= $error_delete ?></p>
<?php endif; ?>
<?php if(isset($error_message)) : ?>
        <!--取得失敗-->
        <p class="detail-error"><?= $error_message ?></p>
<?php else: ?>
        <!--取得成功-->
        <div class="kanji flex-box">
          <div class="surname">
            <label for="surname">姓（漢字）</label>
            <input type="text" id="surname" name="surname" value=<?= $user_info["surname"] ?> readonly>
          </div>
          <div class="name">
            <label for="name">名（漢字）</label>
            <input type="text" id="name" name="name" value=<?= $user_info["name"] ?> readonly>
          </div>
        </div>
        <div class="kana flex-box">
          <div class="surname">
            <label for="surname_kana">姓（カナ）</label>
            <input type="text" id="surname_kana" name="surname_kana" value=<?= $user_info["surname_kana"] ?> readonly>
          </div>
          <div class="name">
            <label for="name_kana">名（カナ）</label>
            <input type="text" id="name_kana" name="name_kana" value=<?= $user_info["name_kana"] ?> readonly>
          </div>
        </div>
        <div class="post-input">
          <label for="post_code">郵便番号</label>
          <input type="text" id="post_code" name="post_code" value=<?= $user_info["post_code"] ?> readonly>
        </div>
        <label for="adddress">住所</label>
        <textarea id="address" name="address" readonly><?= $user_info["address"] ?></textarea>
        <label for="birthday">生年月日</label>
        <input type="text" id="birthday" name="birthday" value=<?= !isset($user_info["birthday"]) ? "未登録" : $user_info["birthday"] ?> readonly>
        <label for="tel">電話番号</label>
        <input type="text" id="tel" name="tel" value=<?= !isset($user_info["tel"]) ? "未登録" : $user_info["tel"] ?> readonly>
        <label for="mobile">携帯番号</label>
        <input type="text" id="mobile" name="mobile" value=<?= !isset($user_info["mobile"]) ? "未登録" : $user_info["mobile"] ?> readonly>
        <label for="email">メールアドレス</label>
        <input type="text" id="email" name="email" value=<?= !isset($user_info["email"]) ? "未登録" : $user_info["email"] ?> readonly>
<?php if(isset($user_info["address_id"])) : ?>
        <input type="hidden" name="address_id" value=<?= $user_info["address_id"] ?>>
<?php else: ?>
        <input type="hidden" name="myself" value=true>
<?php endif; ?>
        <div class="btn-items flex-box">
          <input type="submit" value="Edit">
<?php if(isset($user_info["address_id"])) : ?>
          <a  class="delete" href=<?= "../app/delete.php?address_id=".$user_info["address_id"] ?> onclick="return confirm('本当に削除してもよろしいでしょうか？')">delete</a>
<?php endif; ?>
        </div>
<?php endif; ?>
      </form>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>