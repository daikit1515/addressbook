<?php

class dao {
  // DB関連
  const DB_HOST = "mysql:dbname=tanaka;host:tanaka.naviiiva.work";
  const DB_USER = "naviiiva_user";
  const DB_PASS = "!Samurai1234";
  
  // 配列キー値
  const KEY_FLAG = "FLAG";
  const KEY_DATA = "DATA";
  
  /*
   * DB処理（取得、登録、更新、削除）
   * 引数：SQL文、bindアイテム、bindアイテム
   * 戻り値：true/falue、取得結果（取得件数が0件以上のとき）
   * 例外：PDOException
   */
  public function query(string $sql, array $tempArray, array $paramArray) {
    $ret[self::KEY_FLAG] = true;  // 戻り値
    
    try {
      // DB接続
      $dbh = new PDO(self::DB_HOST, self::DB_USER, self::DB_PASS);
      
      // SQL文のセット
      $stmt = $dbh->prepare($sql);
      
      // SQLインジェクション対策
      $stmt = $this->bind($stmt, $tempArray, $paramArray);
      
      // SQL実行
      if($stmt->execute()) { // 成功
        
        // データの抽出
        $decords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($decords as $record) {
          $ret[self::KEY_DATA][] = $record;
        }
        
      } else {  // 失敗
        $ret[self::KEY_FLAG] = false;
      }
      
      // DB切断
      $dbh = null;
      
      return $ret;
    } catch (PDOException $e) {
      throw new Exception("システムエラーのため、正常に動作が行われませんでした。");
    }
  }
  
  /*
   * SQLインジェクション対策
   * 引数：bind対象、bindアイテム前、bindアイテム
   * 戻り値：bind後
   */
  private function bind($prepare, array $tempArray, array $paramArray) {
    foreach($tempArray as $key => $value) {
      $prepare->bindParam($value, $paramArray[$key]);
    }
    return $prepare;
  }
}
?>