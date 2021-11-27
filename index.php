<?php

// ファイル読み込み
require_once("class/dao.php");
require_once("class/sort.php");

// セッションスタート
session_start();

// ログイン判定
if(!isset($_SESSION["user_id"])) {
  header("Location: view/login.php");
  exit();
}

// 成功メッセージ
$success_message = null;
if(isset($_SESSION["success_message"])) {
  $success_message = $_SESSION["success_message"];
  unset($_SESSION["success_message"]);
} 

$addresses = null;      // 取得結果
$error_message = null;  // 取得エラー（例外）

// 一覧情報の取得
try {
  // Daoオブジェクト生成
  $dao = new Dao();
  
  $user_id = $_SESSION["user_id"]; // ユーザID
  
  // SQL実行
  // 本人以外の情報
  $result1 = $dao->query(
    "select address_id, surname, name, surname_kana, post_code, address from addresses where user_id = :user_id",
    [":user_id"], 
    [$user_id]
    );
  // 本人の情報
  $result2 = $dao->query(
    "select surname, name, surname_kana, post_code, address from userinfos where user_id = :user_id",
    [":user_id"],
    [$user_id]
    );
  
  // SQL取得結果マージ
  $resultMerge = null;
  if(isset($result1[Dao::KEY_DATA])) { // 本人以外取得
    $resultMerge = Sort::sortByKey("surname_kana", SORT_ASC, array_merge($result1[Dao::KEY_DATA], $result2[Dao::KEY_DATA]));
  } else {  // 本人のみ
    $resultMerge = $result2[Dao::KEY_DATA];
  }
  
  // あ行～わ行の振り分け（姓のカタカナから）
  foreach(Sort::SORTARRAY as $key => $kanaArray) {
    $addresses[$key] = Sort::sortAddress("surname_kana", $resultMerge, $kanaArray);
  }

} catch(Exception $e) {
  $error_message = "システムエラーが発生しているため表示ができません。";
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メイン-AddressBook</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <!--ヘッダー-->
    <header id="header" class="flex-box">
      <div class="header-left">
        <h1 class="site-title"><a href="./">AddressBook</a></h1>
      </div>
      <div class="header-right">
        <a href="view/create.php">新規登録</a>
        <a href="app/logout.php" onclick="return confirm('本当にログアウトしてもよろしいでしょうか？')">ログアウト</a>
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
    
<?php if(isset($success_message)) : ?>
    <!--成功メッセージ-->
    <p class="success-message"><?= $success_message ?></p>
<?php endif; ?>
    
    <div class="content-wrapper">
      <h2 class="page-title">Address</h2>
      <!--検索-->
      <form class="form-wrapper">
        <h3 class="sub-title">Search</h3>
        <div class="name">
          <label for="name">名前</label>
          <input type="text" id="name" name="name">
        </div>
        <label for="adddress">住所</label>
        <textarea id="address" name="address"></textarea>
        <input type="button" value="Search" class="address-bth">
      </form>
      
      <!--検索結果-->
      <div class="search-wrapper"></div>
      
      <!--住所一覧-->
      <div class="address-wrapper">
        <h3 class="sub-title">List</h3>
<?php if(isset($error_message)) : ?>
        <!--エラーメッセージ-->
        <p><?= $error_message ?></p>
<?php else: ?>
<?php foreach(Sort::SORTARRAY as $key => $val) : ?>
        <div class="address-item">
          <p class="address-header"><?= $key ?></p>
          <div class="address-list">
<?php if(isset($addresses) && isset($addresses[$key])) : ?>
            <ul>
<?php foreach($addresses[$key] as $address) : ?>
              <li>
                <a href=<?= isset($address["address_id"]) ? "view/detail.php?address_id=".$address["address_id"] : "view/detail.php?myself=true" ?>>
                  <p><?= $address["surname"]." ".$address["name"]?><?= isset($address["address_id"]) ? "": "（自分）"?></p>
                  <p><?= sprintf("〒%s-%s ", substr((string)$address["post_code"],0 ,3), substr((string)$address["post_code"],3 ,4)) ?></p>
                  <p><?= $address["address"] ?></p>
                </a>
              </li>
<?php endforeach; ?>
            </ul>
<?php else: ?>
            <p class="no-address">現在登録がありません。</p>
<?php endif; ?>
          </div>
        </div>
<?php endforeach; ?>
<?php endif; ?>
      </div>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html>