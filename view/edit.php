<?php

// ファイル読み込み
require_once("../class/dao.php");

// 期限切れ防止
session_cache_limiter("none");

// セッションスタート
session_start();

$error_message = null; // エラーメッセージ

// ログイン判定
if(!isset($_SESSION["user_id"])) {
  header("Location: ../view/login.php");
  exit();
}

if($_SERVER["REQUEST_METHOD"] === "POST") {

  // 更新時
  if(isset($_POST["update"])) {
    
    // バリデーション
    // 必須項目の確認
    if(empty($_POST["surname"]) || empty($_POST["name"]) || empty($_POST["surname_kana"]) 
    || empty($_POST["name_kana"]) || empty($_POST["post_code"]) || empty($_POST["address"])
    || (isset($_POST["myself"]) && empty($_POST["email"]))) {
      $error_message[] = "必須欄の中に未入力箇所があります。";
    } else {
      // カタカナ確認
      if(!preg_match("/^[ァ-ヾ]+$/u", $_POST["surname_kana"]) 
      || !preg_match("/^[ァ-ヾ]+$/u", $_POST["name_kana"]) ) { 
        $error_message[] = "カナ欄には全てカタカナで入力ください。";
      }
  
      // 郵便番号確認
      if(!is_numeric($_POST["post_code"]) || strlen($_POST["post_code"]) !== 7){  
        $error_message[] = "郵便番号が正しくありません。7桁の半角数字で入力ください。";
      }
      
      // 電話番号確認
      if(!empty($_POST["tel"])) {
        if (!is_numeric(str_replace("-", "", $_POST["tel"]))) {
          $error_message[] = "電話番号が正しくありません。半角数字および「-」以外は無効です。";
        }
      }
      
      // 携帯番号確認
      if(!empty($_POST["mobile"])) {
        if(!is_numeric(str_replace("-", "", $_POST["mobile"]))) {
          $error_message[] = "携帯番号が正しくありません。半角数字および「-」以外は無効です。";
        }
      }
    }
    
    if(!isset($error_message)) {
      $user_id = $_SESSION["user_id"];        // ユーザID
      $surname = $_POST["surname"];           // 姓
      $name = $_POST["name"];                 // 名
      $surname_kana = $_POST["surname_kana"]; // 姓（カタカナ）
      $name_kana = $_POST["name_kana"];       // 名（カタカナ）
      $email = $_POST["email"];               // メールアドレス
      $birthday = $_POST["birthday"];         // 誕生日
      $tel = $_POST["tel"];                   // 電話番号
      $mobile = $_POST["mobile"];             // 携帯番号
      $post_code = $_POST["post_code"];       // 郵便番号
      $address = $_POST["address"];           // 住所
          
      try {
        // Daoオブジェクト生成
        $dao = new Dao();
        
        // SQL文作成
        $sql = null;
        $tempArray = null;  // bind用
        $paramArray = null; // bind用
        if(isset($_POST["address_id"])) { // 本人以外
          // SQL文
          $sql = "update addresses set surname = :surname, name = :name, surname_kana = :surname_kana,
          name_kana = :name_kana, post_code = :post_code, address = :address,
          birthday = if(:birthday <> '', :birthday, null), tel = if(:tel <> '', :tel, null),
          mobile = if(:mobile <> '', :mobile, null), email = if(:email <> '', :email, null)
          where address_id = :address_id and user_id = :user_id";
          
          $address_id = $_POST["address_id"];// アドレスID
          
          // bindアイテム
          $tempArray = [":surname", ":name", ":surname_kana", ":name_kana", ":post_code", 
          ":address", ":birthday", ":tel", ":mobile", ":email", ":address_id", ":user_id"];
          $paramArray = [$surname, $name, $surname_kana, $name_kana, $post_code, 
          $address, $birthday, $tel, $mobile, $email, $address_id, $user_id];
        } else {  // 本人
          // SQL文
          $sql = "update userinfos set surname = :surname, name = :name, surname_kana = :surname_kana,
          name_kana = :name_kana, post_code = :post_code, address = :address,
          birthday = if(:birthday <> '', :birthday, null), tel = if(:tel <> '', :tel, null),
          mobile = if(:mobile <> '', :mobile, null), email = :email where user_id = :user_id";
          
          // bindアイテム
          $tempArray = [":surname", ":name", ":surname_kana", ":name_kana", ":post_code",
          ":address", ":birthday", ":tel", ":mobile", ":email", ":user_id"];
          $paramArray = [$surname, $name, $surname_kana, $name_kana, $post_code, $address,
          $birthday, $tel, $mobile, $email, $user_id];
        }
  
        // SQL実行
        $result = $dao->query($sql, $tempArray, $paramArray);
            
        if($result[Dao::KEY_FLAG]) {
          // セッションに値を登録（成功メッセージ）
          $_SESSION["success_message"] = "編集に成功しました。";
          
          // リダイレクト(メインページへ)
          header("Location: ../");
          exit();
        } else {
          $error_message[] = "登録に失敗しました。";
        }
      } catch(Exception $e) {
        $error_message[] = $e->getMessage();
      }
    }
  }
} else {
  // リダイレクト(POSTでない（urlの直接入力）)
  header("Location: ../");
  exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編集-AddressBook</title>
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
      <h2 class="page-title">User Edit</h2>
      <form class="form-wrapper" action="edit.php" method="POST">
<?php if(isset($error_message)) : ?>
        <!--エラーメッセージ-->
        <ul class="fail-message-list">
<?php foreach($error_message as $message) : ?>
          <li class="fail-message-item"><?= $message ?></li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <div class="kanji flex-box">
          <div class="surname">
            <label for="surname">姓（漢字）</label><span class="sub">※必須</span>
            <input type="text" id="surname" name="surname" value=<?= $_POST["surname"] ?>>
          </div>
          <div class="name">
            <label for="name">名（漢字）</label><span class="sub">※必須</span>
            <input type="text" id="name" name="name" value=<?= $_POST["name"] ?>>
          </div>
        </div>
        <div class="kana flex-box">
          <div class="surname">
            <label for="surname_kana">姓（カナ）</label><span class="sub">※必須</span>
            <input type="text" id="surname_kana" name="surname_kana" value=<?= $_POST["surname_kana"] ?>>
          </div>
          <div class="name">
            <label for="name_kana">名（カナ）</label><span class="sub">※必須</span>
            <input type="text" id="name_kana" name="name_kana" value=<?= $_POST["name_kana"] ?>>
          </div>
        </div>
        <div class="post flex-box">
          <div class="post-input">
            <label for="post_code">郵便番号</label><span class="sub">※必須</span>
            <input type="text" id="post_code" name="post_code" value=<?= $_POST["post_code"] ?>>
          </div>
          <div class="post-search">
            <p>数字を7ケタ入力後住所検索できます。</p>
            <input type="button" class="post-btn" value="Search">
          </div>
        </div>
        <div id="address-result">
        </div>
        <label for="adddress">住所</label><span class="sub">※必須</span>
        <textarea id="address" name="address" placeholder="例：北海道札幌市中央区北二条西1丁目1-1" ><?= $_POST["address"] ?></textarea>
        <label for="birthday">生年月日</label>
        <input type="date" id="birthday" name="birthday" value=<?= $_POST["birthday"] === "未登録" ? "" : $_POST["birthday"] ?>>
        <label for="tel">電話番号</label>
        <input type="text" id="tel" name="tel" value=<?= $_POST["tel"] === "未登録" ? "" : $_POST["tel"] ?>>
        <label for="mobile">携帯番号</label>
        <input type="text" id="mobile" name="mobile" value=<?= $_POST["mobile"] === "未登録" ? "" : $_POST["mobile"] ?>>
        <label for="email">メールアドレス</label>
<?php if(isset($_POST["myself"])) :?>
        <span class="sub">※必須</span>
<?php endif; ?>
        <input type="email" id="email" name="email" value=<?= $_POST["email"] === "未登録" ? "" : $_POST["email"] ?>>
<?php if(isset($_POST["address_id"])) :?>
        <input type="hidden" name="address_id" value=<?= $_POST["address_id"] ?>>
<?php else : ?>
        <input type="hidden" name="myself" value=true>
<?php endif; ?>
        <input type="hidden" name="update" value="on">
        <input type="submit" value="Update">
      </form>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>