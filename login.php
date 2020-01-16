<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// ログイン画面処理
//================================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; //ショートハンド（略記法）という書き方
  

  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email');

  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass');
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if(empty($err_msg)){
    debug('バリデーションOKです。');
    
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      debug('クエリ結果の中身：'.print_r($result,true));
      
      
      //login_failure_count >= 5 のときの処理
      //ここから
      $sql = 'SELECT login_failure_count FROM users WHERE email = :email';
      $data = array(':email' => $email);
      $stmt = queryPost($dbh, $sql, $data);
      $count = $stmt->fetch(PDO::FETCH_ASSOC);

      debug($count['login_failure_count']);

        if($count['login_failure_count'] >= 5){

          $err_msg['common'] = MSG19;

        }else{


          // パスワード照合
       if(!empty($result) && password_verify($pass, array_shift($result))){
          debug('パスワードがマッチしました。');
        
          //ログイン有効期限（デフォルトを１時間とする）
           $sesLimit = 60*60;
          // 最終ログイン日時を現在日時に
           $_SESSION['login_date'] = time(); //time関数は1970年1月1日 00:00:00 を0として、1秒経過するごとに1ずつ増加させた値が入る
        
          // ログイン保持にチェックがある場合
          if($pass_save){
            debug('ログイン保持にチェックがあります。');
            // ログイン有効期限を30日にしてセット
           $_SESSION['login_limit'] = $sesLimit * 24 * 30;
          }else{
             debug('ログイン保持にチェックはありません。');
             // 次回からログイン保持しないので、ログイン有効期限を1時間後にセット
            $_SESSION['login_limit'] = $sesLimit;
         }
         // ユーザーIDを格納
         $_SESSION['user_id'] = $result['id'];

         $sql = 'UPDATE users SET login_failure_count = 0 WHERE email = :email';
         $data = array(':email' => $email);
         $stmt = queryPost($dbh, $sql, $data);
      
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('マイページへ遷移します。');
          header("Location:mypage.php"); //マイページへ

      }else{

        debug('パスワードがアンマッチです。');
        $err_msg['common'] = MSG09;


        $sql = 'UPDATE users SET login_failure_count = login_failure_count + 1 WHERE email = :email';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh, $sql, $data);


        $sql = 'SELECT login_failure_count FROM users WHERE email = :email';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
        //login_failure_countをログに出力する
        debug($result['login_failure_count']);

        //$resultの値で条件分岐
        if($result['login_failure_count'] >= 5){

          $err_msg['common'] = MSG18;

        }

        //   if (!isset($_SESSION['count'])) {
            
        //     $_SESSION['count'] = 1;
        // } else {
           
        //     $_SESSION['count']++;
        // }
         
        
        }
        }

      
      

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'ログイン';
require('head.php'); 
?>

  <body class="page-login page-1colum">

    <!-- ヘッダー -->
    <?php
      require('header.php'); 
    ?>
    <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

       <div class="form-container">
        
         <form action="" method="post" class="form">
           <h2 class="title">ログイン</h2>
           <div class="area-msg">
             <?php 
              if(!empty($err_msg['common'])) echo $err_msg['common'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
             <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
           </label>
           <div class="area-msg">
             <?php 
             if(!empty($err_msg['email'])) echo $err_msg['email'];
             ?>
           </div>
           <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
             パスワード
             <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
           </label>
           <div class="area-msg">
             <?php 
             if(!empty($err_msg['pass'])) echo $err_msg['pass'];
             ?>
           </div>
           <label>
             <input type="checkbox" name="pass_save">次回ログインを省略する
           </label>
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="ログイン">
            </div>
            パスワードを忘れた方は<a href="passRemindSend.php">コチラ</a>
         </form>
       </div>

      </section>

    </div>

    <!-- footer -->
    <?php
    require('footer.php'); 
    ?>
