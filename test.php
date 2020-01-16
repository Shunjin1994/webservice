<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　テスト　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$data = array(
  'pic1' => 
      array(
          'name' => 'abc',
          'type' => 'png',
          'tmp_name' => '',
          'error' => 4,
          'size' => 0
      ),
  'pic2' => 
      array(
          'name' => 'efg',
          'type' => 'jpeg',
          'tmp_name' => '',
          'error' => 4,
          'size' => 0
      ),
  'pic3' => 
      array(
          'name' => 'hij',
          'type' => '',
          'tmp_name' => '',
          'error' => 4,
          'size' => 0
      )
);
$i = 0;
foreach($data as $key => $val){
  $i++;
  debug('========================'.$i.'回目のループ処理==================');
  debug('$key：'.print_r($key,true));
  debug('$val：'.print_r($val,true));
}