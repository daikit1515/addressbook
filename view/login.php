<?php

// ファイル読み込み
require_once("../class/dao.php");

// セッションスタート
session_start();

$error_message = null;    // エラーメッセージ
$success_message = null;  // 成功メッセージ

// 登録が成功した場合（リダイレクトされてくるため、成功メッセージを受け取る）
if(isset($_SESSION["success_message"])) {
  $success_message = $_SESSION["success_message"];
  unset($_SESSION["success_message"]);
} 

// ログイン処理
if($_SERVER["REQUEST_METHOD"] === "POST") {
  
  // バリデーション
  if(empty($_POST["email"]) || empty($_POST["pass"])) {  // 未入力項目確認
    $error_message = "未入力項目があります。";
  }
  
  if(!isset($error_message)) {
    $email = $_POST["email"];   // メールアドレス
    $pass = $_POST["pass"];     // パスワード
    
    try {
      // Daoオブジェクト生成
      $dao = new Dao();
      
      // SQL実行
      $result = $dao->query("select * from userinfos where email = :email", [":email"], [$email]);
      
      if(isset($result[Dao::KEY_DATA])
      && password_verify($pass, $result[Dao::KEY_DATA][0]["password"])) { // 成功：取得結果があり、パスワードが一致
      
        // セッションに値を登録（ユーザID、ユーザ名、成功メッセージ）
        $_SESSION["user_id"] = $result[Dao::KEY_DATA][0]["user_id"];
        $_SESSION["user_name"] = $result[Dao::KEY_DATA][0]["surname"]." ".$result[Dao::KEY_DATA][0]["name"];
        $_SESSION["success_message"] = "ログインに成功しました。";
        
        // リダイレクト(メインページへ)
        header("Location: ../");
        exit();
      } else {
        $error_message = "メールアドレス・パスワードに誤りがあります。";
      }
    } catch (Exception $e) {
      $error_message = $e->getMessage();
    }
  }
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン-AddressBook</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
  </head>
  <body>

    <!--ヘッダー-->
    <header id="header" class="flex-box">
      <div class="header-left">
        <h1 class="site-title"><a href="../">AddressBook</a></h1>
      </div>
      <div class="header-right">
        <a href="login.php">ログイン</a>
        <a href="registration.php">会員登録</a>
      </div>
      
      <div class="menu-btn">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </header>
    
    <!--成功メッセージ-->
<?php if(isset($success_message)) : ?>
    <p class="success-message"><?= $success_message ?></p>
<?php endif; ?>
    
    <!--ログインフォーム-->
    <div class="content-wrapper">
      <h2 class="page-title">LogIn</h2>
      <form class="form-wrapper" action="login.php" method="POST">
<?php if(isset($error_message)) : ?>
        <!--エラーメッセージ-->
        <p class="fail-message"><?= $error_message ?></p>
<?php endif; ?>
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value=<?php if(isset($_POST["email"])) { echo $_POST["email"]; }?>>
        <label for="pass">パスワード</label>
        <div class="passoword-wrapper">
          <input type="password" id="pass" name="pass">
          <i class="fas fa-eye"></i>
        </div>
        
        <input type="submit" value="LogIn">
      </form>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>