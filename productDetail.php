<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品データを取得
$viewData = getProductOne($p_id);
debug('bbbbbbbbbbbbbbbbbbbbb');
debug($viewData['delete_flg']);
// print_r($viewData);
// DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getHistory($_SESSION['user_id'], $p_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$history_flg = (empty($dbFormData)) ? false : true;

$detail = 'yyy';
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：'.print_r($viewData,true));

// 閲覧履歴
if(empty($_POST['submit'])){

  try{
    
    $dbh = dbConnect();
    // $sql = 'INSERT INTO history (user_id, product_id, create_date) VALUES(:u_id, :p_id, :date)';
    // $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
    // $browing = queryPost($dbh, $sql, $data);
    // $result = $browing->fetch(PDO::FETCH_ASSOC);

    // $sql = 'SELECT product_id from history WHERE user_id = :u_id';
    // $data = array(':u_id' => $_SESSION['user_id']);
    // $browing = queryPost($dbh, $sql, $data);
    // $result = $browing->fetch(PDO::FETCH_ASSOC);
    // debug('SQL：'.$sql);
    
    if($history_flg){
      debug('DB更新です。');
      $sql = 'UPDATE history SET update_date = :date WHERE user_id = :user_id AND product_id = :p_id';
      $data = array(':user_id' => $_SESSION['user_id'] , ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
    }else{
      debug('DB新規登録です。');
      $sql = 'INSERT INTO history (user_id, product_id, create_date, update_date) VALUES(:user_id, :p_id, :date, :date)';
      $data = array(':user_id' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
    }
    debug('SQL：'.$sql);
    debug('流し込みデータ：'.print_r($data,true));
    // クエリ実行
    $browing = queryPost($dbh, $sql, $data);
    $result = $browing->fetch(PDO::FETCH_ASSOC);


    $sql = 'SELECT product_id from history WHERE user_id = :u_id ORDER BY update_date DESC';
    $data = array(':u_id' => $_SESSION['user_id']);
    $browing = queryPost($dbh, $sql, $data);
    $result = $browing->fetch(PDO::FETCH_ASSOC);
    debug('SQL：'.$sql);

    
    $sql = 'SELECT COUNT(product_id) AS num from history';
    $data = array();
    $browing = queryPost($dbh, $sql, $data);
    $result = $browing->fetch(PDO::FETCH_ASSOC);

    if($result['num'] >= 4){

      $sql = 'DELETE FROM history ORDER BY id ASC LIMIT 1';
      // $data = array(':id' => $);
      $browing = queryPost($dbh, $sql, $data);
      $result = $browing->fetch(PDO::FETCH_ASSOC);

    }

    

  }catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
  
}


// post送信されていた場合
if(!empty($_POST['submit'])){
  debug('POST送信があります。');
  
  //ログイン認証
  require('auth.php');

  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成

    //historyからuser_id とproduct_idを取ってきてfetch->変数に入れる
    //入れた変数をemptyなどで条件分岐させ、買うボタンを消す
    $sql = 'SELECT user_id,product_id FROM sell_history WHERE user_id = :u_id AND product_id = :p_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);
    $detail = $stmt->fetch(PDO::FETCH_ASSOC);

    debug($sql);

    //販売履歴
    if(empty($detail)){

      $dbh = dbConnect();
      $sql = 'INSERT INTO sell_history (user_id, product_id, create_date) VALUES(:u_id, :p_id, :date)';
      // $data = array(':u_id' => $viewData['user_id'], ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
      $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
      $sell_history = queryPost($dbh, $sql, $data);
      $result = $sell_history->fetch(PDO::FETCH_ASSOC);
      debug('SQL：'.$sql);
      
      $sql = 'UPDATE product SET delete_flg = 1 WHERE id = :p_id';
      $data = array(':p_id' => $p_id);
      $sell = queryPost($dbh, $sql, $data);
      $selling = $sell->fetch(PDO::FETCH_ASSOC);
      debug('SQL：'.$sql);

      //入れ替えたらできた（一応想定内）
      $sql = 'INSERT INTO bord (sale_user, buy_user, product_id, create_date) VALUES(:s_uid, :b_uid, :p_id, :date)';
      $data = array(':s_uid' => $viewData['user_id'], ':b_uid' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('SQL：'.$sql);

      if($stmt){
        $_SESSION['msg_success'] = SUC05;
        debug('連絡掲示板へ遷移します。');
        header("Location:msg.php?b_id=".$dbh->lastInsertID()); 

      }else{

        debug('チェック');
        header("Location:tranSale.php");

      }

      // // クエリ成功の場合
      // if($stmt){
      //   $_SESSION['msg_success'] = SUC05;
      //   debug('連絡掲示板へ遷移します。');
      //   header("Location:msg.php?b_id=".$dbh->lastInsertID()); //連絡掲示板へ
      
      // }

    }
    

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品詳細';
require('head.php'); 
?>

  <body class="page-productDetail page-1colum">
    <style>
      .badge{
        padding: 5px 10px;
        color: white;
        background: #7acee6;
        margin-right: 10px;
        font-size: 16px;
        vertical-align: middle;
        position: relative;
        top: -4px;
      }
      #main .title{
        font-size: 28px;
        padding: 10px 0;
      }
      .product-img-container{
        overflow: hidden;
      }
      .product-img-container img{
        width: 100%;
      }
      .product-img-container .img-main{
        width: 750px;
        float: left;
        padding-right: 15px;
        box-sizing: border-box;
      }
      .product-img-container .img-sub{
        width: 230px;
        float: left;
        background: #f6f5f4;
        padding: 15px;
        box-sizing: border-box;
      }
      .product-img-container .img-sub:hover{
        cursor: pointer;
      }
      .product-img-container .img-sub img{
        margin-bottom: 15px;
      }
      .product-img-container .img-sub img:last-child{
        margin-bottom: 0;
      }
      .product-detail{
        background: #f6f5f4;
        padding: 15px;
        margin-top: 15px;
        min-height: 150px;
      }
      .product-buy{
        overflow: hidden;
        margin-top: 15px;
        margin-bottom: 50px;
        height: 50px;
        line-height: 50px;
      }
      .product-buy .item-left{
        float: left;
      }
      .product-buy .item-right{
        float: right;
      }
      .product-buy .price{
        font-size: 32px;
        margin-right: 30px;
      }
      .product-buy .btn{
        border: none;
        font-size: 18px;
        padding: 10px 30px;
      }
      .product-buy .btn:hover{
        cursor: pointer;
      }
      /*お気に入りアイコン*/
      .icn-like{
        float:right;
        color: #ddd;
      }
      .icn-like:hover{
        cursor: pointer;
      }
      .icn-like.active{
        float:right;
        color: #fe8a8b;
      }
    </style>

    <!-- ヘッダー -->
    <?php
      require('header.php'); 
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="title">
          <span class="badge"><?php echo sanitize($viewData['category']); ?></span>
          <?php echo sanitize($viewData['name']); ?>
          <i class="fa fa-heart icn-like js-click-like <?php if(isLike($_SESSION['user_id'], $viewData['id'])){ echo 'active'; } ?>" aria-hidden="true" data-productid="<?php echo sanitize($viewData['id']); ?>" ></i>
        </div>
        <div class="product-img-container">
          <div class="img-main">
            <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
          </div>
          <div class="img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像1：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像2：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="画像3：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
          </div>
        </div>
        <div class="product-detail">
          <p><?php echo sanitize($viewData['comment']); ?></p>
        </div>
        <div class="product-buy">
          <div class="item-left">
            <a href="index.php<?php echo appendGetParam(array('p_id')); ?>">&lt; 商品一覧に戻る</a>
          </div>
          <?php
            debug('aaaaaaaaaaaaaaaaaaaa');
            debug($viewData['delete_flg']);
            if($viewData['delete_flg'] === '0'):
              debug('xxxxx');
            ?>
          <form action="" method="post"> <!-- formタグを追加し、ボタンをinputに変更し、style追加 -->
            <div class="item-right">
              <input type="submit" value="買う!" name="submit" class="btn btn-primary" style="margin-top:0;">
            </div>
          </form>
          <?php
           endif;
          ?>
          <div class="item-right">
            <p class="price">¥<?php echo sanitize(number_format($viewData['price'])); ?>-</p>
          </div>
        </div>

      </section>

    </div>

    <!-- footer -->
    <?php
    require('footer.php'); 
    ?>
