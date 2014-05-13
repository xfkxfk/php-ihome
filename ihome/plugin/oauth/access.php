<?php
include_once(dirname(__FILE__) . '/config.php');
include_once(dirname(__FILE__) . '/iauth_core.php');

 /*################ 从Header中提取参数 ################*/
    if (function_exists('apache_request_headers')){
	$headers=getallheaders();
	if (empty($headers['Authorization'])) die('need header Authorization');
	$oauth_params=$headers['Authorization'];
	}
    else{
	if (empty($_SERVER['HTTP_AUTHORIZATION'])) die('need header Authorization');
	$oauth_params=$_SERVER['HTTP_AUTHORIZATION'];
	}
$oauth_arr_tmp=explode(',',$oauth_params);

//print_r($oauth_arr_tmp);

$sig='';
$oauth_array=array();
foreach ($oauth_arr_tmp as $str_tmp)
  {
    $pos=strpos( $str_tmp, '=' );
    $val_tmp = rawurldecode( substr( $str_tmp, $pos+2, strlen( $str_tmp )-$pos-3 ));
    $key_tmp = rawurldecode( substr( $str_tmp, 0, $pos ));

    if ($key_tmp=='oauth_signature')
      {
	$sig = rawurldecode( $val_tmp );
      }
    else{
      $oauth_array[$key_tmp]=rawurldecode( $val_tmp );
    }

  }
//print_r($oauth_array);

/*################ 检查oauth数组中的参数是否合法 ################*/
if ($sig=='') die('缺少参数0');
if (strlen($sig)<=10) die ('非法的签名');
if (empty($oauth_array['oauth_nonce'])) die('缺少参数1');
if (empty($oauth_array['oauth_timestamp'])) die('缺少参数2');
if (empty($oauth_array['oauth_signature_method'])) die('缺少参数3');
if (empty($oauth_array['oauth_consumer_key'])) die('缺少参数4');
if (empty($oauth_array['oauth_version'])) die('缺少参数5');
if (empty($oauth_array['oauth_token'])) die('缺少参数6');

/*################ 过滤1-时间戳 ################*/

$their_time=$oauth_array['oauth_timestamp'];
$now=time();
/* echo $their_time . '<br>'; */
/* echo $now . '<br>'; */
if (($their_time <= $now-480)||($their_time >= $now+480)) {die("时间与服务器不同步");}

/*################ 检查oauth数组中的参数是否合法 ################*/

$tobechecked=$oauth_array['oauth_consumer_key'];
if (! preg_match("/^[a-fA-F0-9]{40}$/",$tobechecked)) die ('非法的consumer_key');
$tobechecked=$oauth_array['oauth_token'];
if (! preg_match("/^[a-fA-F0-9]{40}$/",$tobechecked)) die ('非法的token');
$tobechecked=$oauth_array['oauth_nonce'];
if (! preg_match("/^[a-fA-F0-9]{16}$/",$tobechecked)) die ('非法的nonce');
/* if ($oauth_array['oauth_signature_method']!='HMAC-SHA1') die ('不支持的签名方式'); */
if ($oauth_array['oauth_version']!='1.0') die ('不支持的版本');
if ($oauth_array['oauth_signature_method']!='HMAC-SHA1' &&
    $oauth_array['oauth_signature_method']!='MD5')
    die ('不支持的签名方式');


/*################ 设定参数 ################*/

$method  ="GET";
$url_path='http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
$app_consumer_key=$oauth_array['oauth_consumer_key'];
$request_token=$oauth_array['oauth_token'];

$con=mysql_connect(OAUTH_DB_DOMAIN,OAUTH_DB_USER,OAUTH_DB_PASSWD);
if (!$con) die(mysql_error());
mysql_select_db(OAUTH_DB_DB);
$sql="SELECT app_secret,app_info.app_id,request_secret FROM app_info,token_info WHERE app_key='$app_consumer_key' and request_token='$request_token' and app_info.app_id=token_info.app_id";
$tmp=mysql_query($sql);

  echo mysql_error();

$sql_result=mysql_fetch_array($tmp);
if ($sql_result=='') die("应用不存在！");  //echo $sql_result . '<br />';

$app_consumer_secret=$sql_result['app_secret'];
$request_secret=$sql_result['request_secret'];  //echo 'app_secret: ' . $app_consumer_secret . '<br />';

$app_id=$sql_result['app_id'];
$params =$oauth_array;


/*################ 生成BASE String ################*/

$base_str = strtoupper($method) . '&' . rawurlencode ( $url_path ) . '&';
ksort ( $params );
$str_tmp='';
foreach ( $params as $key => $val ) 
  {
    $str_tmp .= "$key=$val&";
  }
$base_str.= rawurlencode( substr( $str_tmp, 0, strlen( $str_tmp ) -1 )); /* 删去最后多出来的一个'&' */



/*################ 检查签名 ################*/

$secret=$app_consumer_key.'&'.$app_consumer_secret . '&' . $request_token . '&' . $request_secret;
/* $sign = hash_hmac ( "sha1", $base_str, $secret, true ); */
/* $sign = base64_encode ( $sign ); */
/* echo $sign ."\n"; */
/* echo $sig  ."\n"; */
$sign = signature($oauth_array['oauth_signature_method'], $base_str, $secret);
if (strcmp($sig,$sign)!=0) die("签名不匹配！");




/*################ 过滤2：重放攻击 ################*/

$app_token=$oauth_array['oauth_consumer_key'];
$req_token=$oauth_array['oauth_token'];
$nonce=$oauth_array['oauth_nonce'];
$time=$oauth_array['oauth_timestamp'];

/* echo $app_token . '<br />' . $req_token . '<br />' . $nonce .'<br /><br />' . $time; */

$sql="SELECT app_key FROM token_nonce WHERE app_key='$app_token' and token='$req_token' and token_type='request' and nonce='$nonce' and create_t='$time'";
$sql_tmp=mysql_query($sql);
$sql_result=mysql_fetch_array($sql_tmp);
if ($sql_result!='') die("重复请求！");

/*-----------------  记录请求以备查询重放攻击  -----------------*/
$t_stamp=date('Y-m-d H:i:s',$time);
$sql="INSERT INTO token_nonce (app_key,token,token_type,nonce,create_t) VALUES('$app_token','$req_token','request','$nonce','$t_stamp')";
mysql_query($sql);
echo mysql_error();

/*################ 检索 access 信息 ################*/

$verifier=$oauth_array['oauth_verifier'];
$sql="SELECT user_id,access_token,exchanged,access_secret FROM token_info WHERE request_token='$req_token' and verify_code='$verifier'";
$sql_tmp=mysql_query($sql);

echo mysql_error();

$sql_result=mysql_fetch_array($sql_tmp);
$access_token=$sql_result['access_token'];
$access_secret=$sql_result['access_secret'];
$status=$sql_result['exchanged'];
if ($status=='1') die("不允许重复token认证");
$uid = $sql_result['user_id'];
/*################ 更新token状态 ################*/

$sql="UPDATE token_info SET exchanged=true,faile_t='20361231235959' WHERE request_token='$req_token'";
mysql_query($sql);

echo mysql_error();


/*################ 返回token ################*/
echo 'oauth_uid='.$uid.'&';
echo 'oauth_token='. $access_token .'&';
echo 'oauth_token_secret='. $access_secret;
/* exit("调试信息 on 82：access.php 此处退出"); */
?>
