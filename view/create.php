<?php

// ファイル読み込み
require_once("../class/dao.php");

// セッションスタート
session_start();

$error_message = null; // エラーメッセージ

// ログイン判定
if(!isset($_SESSION["user_id"])) {
  header("Location: ../view/login.php");
  exit();
}

// 登録処理
if($_SERVER["REQUEST_METHOD"] === "POST") {
  
  // バリデーション
  // 必須項目の確認
  if(empty($_POST["surname"]) || empty($_POST["name"]) || empty($_POST["surname_kana"]) 
  || empty($_POST["name_kana"]) || empty($_POST["post_code"]) || empty($_POST["address"])) { 
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
      $sql = "insert into addresses
      (user_id, surname, name, surname_kana, name_kana, post_code, address, birthday, tel, mobile, email)
      value(:user_id, :surname, :name, :surname_kana, :name_kana, :post_code, :address,
      if(:birthday <> '', :birthday, null), if(:tel <> '', :tel, null),
      if(:mobile <> '', :mobile, null), if(:email <> '', :email, null))";
      
      // bindアイテム
      $tempArray = [":user_id", ":surname", ":name", ":surname_kana", ":name_kana", 
      ":post_code", ":address", ":birthday", ":tel", ":mobile", ":email"];
      $paramArray = [$user_id, $surname, $name, $surname_kana, $name_kana,
      $post_code, $address, $birthday, $tel, $mobile, $email];
      
      // SQL実行
      $result = $dao->query($sql, $tempArray, $paramArray);
          
      if($result[Dao::KEY_FLAG]) {
        // セッションに値を登録（成功メッセージ）
        $_SESSION["success_message"] = "登録に成功しました。";
        
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

/*
 * 入力フォーム値保持用関数
 * 引数：name属性名
 * 戻り値：POST値
 */
function inputValue(string $name) {
  if(isset($_POST[$name])) {
    return $_POST[$name];
  }
  return "";
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録-AddressBook</title>
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
      <h2 class="page-title">Create User</h2>
      <form class="form-wrapper" action="create.php" method="POST">
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
            <input type="text" id="surname" name="surname" placeholder="例：山田" value=<?= inputValue("surname") ?>>
          </div>
          <div class="name">
            <label for="name">名（漢字）</label><span class="sub">※必須</span>
            <input type="text" id="name" name="name" placeholder="例：太郎" value=<?= inputValue("name") ?>>
          </div>
        </div>
        <div class="kana flex-box">
          <div class="surname">
            <label for="surname_kana">姓（カナ）</label><span class="sub">※必須</span>
            <input type="text" id="surname_kana" name="surname_kana" placeholder="例：ヤマダ" value=<?= inputValue("surname_kana") ?>>
          </div>
          <div class="name">
            <label for="name_kana">名（カナ）</label><span class="sub">※必須</span>
            <input type="text" id="name_kana" name="name_kana" placeholder="例：タロウ" value=<?= inputValue("name_kana") ?>>
          </div>
        </div>
        <div class="post flex-box">
          <div class="post-input">
            <label for="post_code">郵便番号</label><span class="sub">※必須</span>
            <input type="text" id="post_code" name="post_code" placeholder="例：0600002" value=<?= inputValue("post_code") ?>>
          </div>
          <div class="post-search">
            <p>数字を7ケタ入力後住所検索できます。</p>
            <input type="button" class="post-btn" value="Search">
          </div>
        </div>
        <div id="address-result">
        </div>
        <label for="adddress">住所</label><span class="sub">※必須</span>
        <textarea id="address" name="address" placeholder="例：北海道札幌市中央区北二条西1丁目1-1" ><?= inputValue("address") ?></textarea>
        <label for="birthday">生年月日</label>
        <input type="date" id="birthday" name="birthday" value=<?= inputValue("birthday") ?>>
        <label for="tel">電話番号</label>
        <input type="text" id="tel" name="tel" placeholder="例：052-111-2222" value=<?= inputValue("tel") ?>>
        <label for="mobile">携帯番号</label>
        <input type="text" id="mobile" name="mobile" placeholder="例：080-1111-2222" value=<?= inputValue("mobile") ?>>
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" placeholder="例：yamada@gmail.com" value=<?= inputValue("email") ?>>
        <input type="submit" value="Create">
      </form>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>