<?php

// ファイル読み込み
require_once("../class/dao.php");

// セッションスタート
session_start();

$error_message = null; // エラーメッセージ

// 登録処理
if($_SERVER["REQUEST_METHOD"] === "POST") {
  
  // バリデーション
  // 必須項目の確認
  if(empty($_POST["surname"]) || empty($_POST["name"]) || empty($_POST["surname_kana"]) 
  || empty($_POST["name_kana"]) || empty($_POST["email"]) || empty($_POST["post_code"]) 
  || empty($_POST["address"]) || empty($_POST["pass"]) || empty($_POST["pass_confirm"])) {
    $error_message[] = "必須欄の中に未入力箇所があります。";
  } else {
    // カタカナ確認
    if(!preg_match("/^[ァ-ヾ]+$/u", $_POST["surname_kana"]) 
    || !preg_match("/^[ァ-ヾ]+$/u", $_POST["name_kana"]) ) { 
      $error_message[] = "カナ欄には全てカタカナで入力ください。";
    }
    
    // パスワード一致確認
    if($_POST["pass"] !== $_POST["pass_confirm"]) {  
      $error_message[] = "パスワードが確認欄と一致しません。";
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
    $surname = $_POST["surname"];           // 姓
    $name = $_POST["name"];                 // 名
    $surname_kana = $_POST["surname_kana"]; // 姓（カタカナ）
    $name_kana = $_POST["name_kana"];       // 名（カタカナ）
    $email = $_POST["email"];               // メールアドレス
    $birthday = $_POST["birthday"];         // 生年月日
    $tel = $_POST["tel"];                   // 電話番号
    $mobile = $_POST["mobile"];             // 携帯番号
    $post_code = $_POST["post_code"];       // 郵便番号
    $address = $_POST["address"];           // 住所
    $pass = password_hash($_POST["pass"], PASSWORD_DEFAULT);  // パスワード
        
    try {
      // Daoオブジェクト生成
      $dao = new Dao();
      
      // SQL文作成
      $sql = "insert into userinfos
      (surname, name, surname_kana, name_kana, email, birthday, tel, mobile, post_code, address, password)
      value(:surname, :name, :surname_kana, :name_kana, :email, if(:birthday <> '', :birthday, null), 
      if(:tel <> '', :tel, null),if(:mobile <> '', :mobile, null), :post_code, :address, :pass)";
      
      // bindアイテム
      $tempArray = [":surname", ":name", ":surname_kana", ":name_kana", ":email", ":birthday",
      ":tel", ":mobile", ":post_code", ":address", ":pass"];
      $paramArray = [$surname, $name, $surname_kana, $name_kana, $email, $birthday, 
      $tel, $mobile, $post_code, $address, $pass];
      
      // SQL実行
      $result = $dao->query($sql, $tempArray, $paramArray);
          
      if($result[Dao::KEY_FLAG]) {  // 成功
      
        // セッションに値を登録（成功メッセージ）
        $_SESSION["success_message"] = "会員登録に成功しました。";
        
        // リダイレクト(ログインページへ)
        header("Location: login.php");
        exit();
      } else {
        $error_message[] = "会員登録に失敗しました。(既に登録されている可能性があります。)";
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
    <title>会員登録-AddressBook</title>
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
    
    <!--登録フォーム-->
    <div class="content-wrapper">
      <h2 class="page-title">Registration</h2>
      <form class="form-wrapper" action="registration.php" method="POST">
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
        <label for="email">メールアドレス</label><span class="sub">※必須</span>
        <input type="email" id="email" name="email" placeholder="例：yamada@gmail.com" value=<?= inputValue("email") ?>>
        <label for="birthday">生年月日</label>
        <input type="date" id="birthday" name="birthday" value=<?= inputValue("birthday") ?>>
        <label for="tel">電話番号</label>
        <input type="text" id="tel" name="tel" placeholder="例：052-111-2222" value=<?= inputValue("tel") ?>>
        <label for="mobile">携帯番号</label>
        <input type="text" id="mobile" name="mobile" placeholder="例：080-1111-2222" value=<?= inputValue("mobile") ?>>
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
        <label for="pass">パスワード</label><span class="sub">※必須</span>
        <div class="passoword-wrapper">
          <input type="password" id="pass" name="pass" placeholder="例：xY12ie-d9irtKr">
          <i class="fas fa-eye"></i>
        </div>
        <label for="pass_confirm">パスワード(確認用)</label><span class="sub">※必須</span>
        <div class="passoword-wrapper">
          <input type="password" id="pass_confirm" name="pass_confirm" placeholder="上記と同じ">
          <i class="fas fa-eye"></i>
        </div>
        <input type="submit" value="Register">
      </form>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../js/main.js"></script>
  </body>
</html>