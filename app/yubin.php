<?php

// ファイル読み込み
require_once("../class/dao.php");

// セッションスタート
session_start();

$message = null;  // 実行結果
$data = null;     // 取得レコード

if($_SERVER["REQUEST_METHOD"] === "GET") {
  $post_code = mb_convert_kana($_GET["code"], "n"); // 全角を半角に変換(全角でも数字なら検索可能)
  if(is_numeric($post_code) && strlen($post_code) === 7) {  // 数字かつ7ケタ
    try {
      // Daoオブジェクト生成
      $dao = new Dao();
      
      // SQL実行
      $result = $dao->query(
        "select pref, city, address from KEN_ALL where postal7 = :post_code", 
        ["post_code"], 
        [$post_code]
        );
      
      if(isset($result[Dao::KEY_DATA])) {
        $count = count($result[Dao::KEY_DATA]);
        $message = "${count}件該当する住所が見つかりました。";
        $data = $result[Dao::KEY_DATA];
      } else {
        $message = "該当する住所が見当たりませんでした。";
      }
    } catch(Exception $e) {
      $message = $e->getMessage();
    }
  } else {
    $message = "入力が正しくありません。<br>数字7ケタで入力ください。";
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