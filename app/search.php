<?php

// ファイル読み込み
require_once("../class/dao.php");
require_once("../class/sort.php");

// セッションスタート
session_start();

$message = null;  // 実行結果
$data = null;     // 取得レコード

if($_SERVER["REQUEST_METHOD"] === "POST") {
  if(!empty($_POST["name"]) || !empty($_POST["address"])) {
    $user_id = $_SESSION["user_id"];              // ユーザID
    $name = $_POST["name"];                       // 検索（名前）
    $address = $_POST["address"];                 // 検索（住所）
    $convert_name = mb_convert_kana($name, "C");  // 検索（カタカナ）
    
    try {
      // Daoオブジェクト生成
      $dao = new Dao();
      
      $sql1 = null;  // addressesテーブル検索用
      $sql2 = null;  // userinfosテーブル検索用
      
      $tempArray = null;  // bind用
      $paramArray = null; // bind用
      
      // SQL文作成
      if(!empty($name) && !empty($address)) { // 名前、住所ともに入力あり
        // SQL文
        $sql1 = "select address_id, surname, name, surname_kana, post_code, address from addresses 
        where user_id = :user_id and (concat(surname,name) like :name or concat(surname_kana,name_kana) like :convert_name)
        and address like :address";
        $sql2 = "select surname, name, surname_kana, post_code, address from userinfos 
        where user_id = :user_id and (concat(surname,name) like :name or concat(surname_kana,name_kana) like :convert_name)
        and address like :address";
 
        // bindアイテム
        $tempArray = [":user_id", ":name", ":convert_name", ":address"];
        $paramArray = [$user_id, "%".$name."%", "%".$convert_name."%", "%".$address."%"];
      } else if(!empty($name)) {  // 名前のみ入力あり
        // SQL文
        $sql1 = "select address_id, surname, surname_kana, name, post_code, address from addresses 
        where user_id = :user_id and (concat(surname,name) like :name or concat(surname_kana,name_kana) like :convert_name)";
        $sql2 = "select surname, name, surname_kana, post_code, address from userinfos 
        where user_id = :user_id and (concat(surname,name) like :name or concat(surname_kana,name_kana) like :convert_name)";
        
        // bindアイテム
        $tempArray = [":user_id", ":name", ":convert_name"];
        $paramArray = [$user_id, "%".$name."%", "%".$convert_name."%"];
      } else {  // 住所のみ入力あり
        // SQL文
        $sql1 = "select address_id, surname, name, surname_kana, post_code, address from addresses 
        where user_id = :user_id and address like :address";
        $sql2 = "select surname, name, surname_kana, post_code, address from userinfos 
        where user_id = :user_id and address like :address";
        
        // bindアイテム
        $tempArray = [":user_id", ":address"];
        $paramArray = [$user_id, "%".$address."%"];
      }
      
      // SQL実行
      $result1 = $dao->query($sql1, $tempArray, $paramArray);
      $result2 = $dao->query($sql2, $tempArray, $paramArray);
      
      // SQL取得結果マージ
      $resultMerge = null;  
      if(isset($result1[Dao::KEY_DATA]) && isset($result2[Dao::KEY_DATA])) {  // addressesテーブル、userinfosテーブル両方から取得あり
        $resultMerge = array_merge($result1[Dao::KEY_DATA], $result2[Dao::KEY_DATA]);
      } else if(isset($result1[Dao::KEY_DATA])) { // addressesテーブルのみ取得あり
        $resultMerge = $result1[Dao::KEY_DATA];
      } else if(isset($result2[Dao::KEY_DATA])) { // addressesテーブルのみ取得あり
        $resultMerge = $result2[Dao::KEY_DATA];
      }
      
      if(isset($resultMerge)) {
        $count = count($resultMerge);
        $message = "${count}件該当する住所が見つかりました。";
        $data = Sort::sortByKey("surname_kana", SORT_ASC, $resultMerge);
      } else {
        $message = "該当するデータは見つかりませんでした。";
      }
    } catch(Exception $e) {
      $message = $e->getMessage();
    }
  } else {
    $message = "名前および住所に入力がありません。<br>両方もしくはどちらかに何か入力ください。";
  }
  
  // 戻り値作成
  $returnList = array(
    "address" => $data,
    "message" => $message
  );
  
  // JSON化
  header("Content-type: application/json");
  echo json_encode($returnList, JSON_UNESCAPED_UNICODE);
}

?>