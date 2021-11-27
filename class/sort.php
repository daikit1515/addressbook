<?php

class Sort {
  // あ行～わ行
  const SORTARRAY = [
    "あ"=> array("ア", "イ", "ウ", "エ", "オ"), 
    "か"=> array("カ", "キ", "ク", "ケ", "コ", "ガ", "ギ", "グ", "ゲ", "ゴ"), 
    "さ"=> array("サ", "シ", "ス", "セ", "ソ", "ザ", "ジ", "ズ", "ゼ", "ゾ"),
    "た"=> array("タ", "チ", "ツ", "テ", "ト", "ダ", "ヂ", "ヅ", "デ", "ド"),
    "な"=> array("ナ", "ニ", "ヌ", "ネ", "ノ"),
    "は"=> array("ハ", "ヒ", "フ", "ヘ", "ホ", "バ", "ビ", "ブ", "ベ", "ボ", "パ", "ピ", "プ", "ぺ", "ポ"),
    "ま"=> array("マ", "ミ", "ム", "メ", "モ"),
    "や"=> array("ヤ", "ユ", "ヨ"),
    "ら"=> array("ラ", "リ", "ル", "レ", "ロ"),
    "わ"=> array("ワ", "ヲ", "ン")
  ];
  
  /*
   * 多次元配列ソート関数
   * 引数：ソートキー、フラグ、対象の配列
   * 戻り値：並べ替え結果
   */
  public static function sortByKey(string $key_name, int $sort_order, array $array) {
    foreach($array as $key => $value) {
      $standard_key_array[$key] = $value[$key_name];
    }
    
    array_multisort($standard_key_array, $sort_order, $array);
    return $array;
  }
  
  /*
   * あ行～わ行振り分け用関数
   * 引数：振り分け時キー、対象の配列、取得したい行の文字配列（あ行～わ行）
   * 戻り値：抽出結果（あ行～わ行）
   */
  public static function sortAddress(string $key, array $addresses, array $kanaArray) {
    $ret = null;
    foreach($addresses as $address) {
      foreach($kanaArray as $kana) {
        if(preg_match("/^${kana}/", $address[$key])) {
          $ret[] = $address;
          break;
        }
      }
    }
    return $ret;
  }
}

?>