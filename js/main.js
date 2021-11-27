$(function() {
  
  // 郵便検索
  $(".post-btn").click(function(e) {
    // ダブルクリック防止（ボタンを無効）
    e.target.disabled = true;
    
    var addressList = $("#address-result"); // 検索結果表示箇所
    
    // 子要素削除（前の検索を削除）
    addressList.children().remove();

    // 住所取得
    $.ajax({
      url: "../app/yubin.php",
      type: "GET",
      data: { "code" : $("#post_code").val() },
      dataType: "json",
      success: function(data){
        // 実行結果
        addressList.append(`<p class="post-message">${ data.message }</p>`);
        
        // 取得データ
        if(data.address !== null) {
          for(var i = 0, len = data.address.length; i < len; i++) {
            if(data.address[i]["address"] === "以下に掲載がない場合") {
              data.address[i]["address"] = "";
            }
            var address = data.address[i]["pref"] + data.address[i]["city"] + data.address[i]["address"];
            addressList.append(`<label><input type="radio" name="search" value="${ address }">${ address }<label><br>`);
          }
          
          // イベント登録(ラジオボタンが押されたとき)
          $("input[type='radio']").change(function() {
            $("#address").val($(this).val());
          });
        }
      },
      error: function(data) {
        addressList.append(`<p class="post-message">検索に失敗しました。</p>`);
      }
    });
    
    // ダブルクリック防止（処理終了後1秒経過でボタンを有効にする）
    setTimeout(function() {
      e.target.disabled = false;
    }, 1000);
    
  });
  
  // ユーザ検索
  $(".address-bth").click(function(e) {
    // ダブルクリック防止（ボタンを無効）
    e.target.disabled = true;
    
    var searchWrapper = $(".search-wrapper"); // 検索結果表示箇所
    var searchResult = $("<div></div>", {"class": "search-result"});
    
    // 子要素削除（前の検索を削除）
    searchWrapper.children().remove();
    
    $.ajax({
      url: "app/search.php",
      type: "POST",
      data: { "name": $("#name").val(), "address": $("#address").val() },
      dataType: "json",
      success: function(data) {
        // 実行結果
        searchResult.append(`<p class="search-message">${ data.message }</p>`);
        
        // 取得データ
        if(data.address !== null) {
          // リスト作成
          var ul = $("<ul></ul>", { "class": "search-list" });
          for(var i = 0, len = data.address.length; i < len; i++) {
            var li = $("<li></li>");
            var href = `view/detail.php?address_id=${data.address[i].address_id}`;
            var myself = "";
            if(typeof data.address[i].address_id === 'undefined') { // 本人の場合
              href = `view/detail.php?myself=true`;
              myself= "（自分）"
            }
            var a = $("<a></a>", { href: `${href}`});
            a.append(`<p>${data.address[i].surname} ${data.address[i].name}${myself}</p>`);
            a.append(`<p>〒${data.address[i].post_code.substr(0, 3)}-${data.address[i].post_code.substr(3, 4)}</p>`);
            a.append(`<p>${data.address[i].address}</p>`);
            li.append(a);
            ul.append(li);
          }
          searchResult.append(ul);
        }
        searchWrapper.append(searchResult);
      },
      error: function(data) {
        searchResult.append(`<p class="search-message">検索に失敗しました。</p>`);
        searchWrapper.append(searchResult);
      }
    });
    
    // ダブルクリック防止（処理終了後1秒経過でボタンを有効にする）
    setTimeout(function() {
      e.target.disabled = false;
    }, 1000);
    
  });
  
  // アコーディオンメニュー（一覧）
  
  // 最初の要素だけonにしておく
  $(".address-item:first-of-type .address-header") .addClass("slideon");
  
  $(".address-header").click(function(e) {
    $(this).toggleClass("slideon");
    
    var target = $(e.target).next();
    
    if(target.is(":visible")) {
      target.slideUp();
    } else {
      target.slideDown();
    }
  });
  
  //ハンバーガーメニュー
  $(".menu-btn").click(function() {
    $("#header").toggleClass("open");
  });
  
    // パスワード表示切替
  $('.passoword-wrapper i').click(function() {
    var input = $(this).prev();
    
    if(input.attr("type") === "text") {
      input.attr("type", "password");
      $(this).attr("class", "fas fa-eye");
    } else {
      input.attr("type", "text");
      $(this).attr("class", "fas fa-eye-slash");
    }
  })
});