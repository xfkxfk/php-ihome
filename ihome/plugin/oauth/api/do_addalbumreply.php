﻿<?php
/*
     addalbumreply.php评论某个分享
     Add by am@ihome.2012-12-12  11:34

*/
    include_once('../data_oauth_check.php');	
    $userid = intval(oauth_check());
	include_once('../../../common.php');
	//$userid =100;
	//$username = '1号';
	$wheresql = "uid = $userid";
	$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('space')." WHERE  ".$wheresql);	
	$value = $_SGLOBAL['db']->fetch_array($query);
	$username = $value[username];

	$Message =empty($_POST['message'])?'':getstr($_POST['message']);
	$Pic =empty($_POST['picid'])?0:intval($_POST['picid']);
    //$Message = '哈哈 我是i相册的评论';
	//$Pic = 135 ;
	if(trim($Message)==null){
	    $arrs = array('flag'=>'null');
	}else if(strlen($Message) < 2){
		$arrs = array('flag'=>'content_is_too_short');
	}else{
			//处理评论的@功能 Add by am 2013-12-07 start
		//提取AT用户
		preg_match_all("/[@](.*)[(]([\d]+)[)]\s/U",$Message, $matches, PREG_SET_ORDER);

		foreach($matches as $value){

			$TmpString = $value[0]; 
			$TmpName = $value[1]; 
			$UserId = $value[2];

			$result = $_SGLOBAL['db']->query("select uid,username,name from ".tname('space')." where uid=$UserId");

			if($rs = $_SGLOBAL['db']->fetch_array($result)){
				$realname = $rs['name'];
				if(empty($realname)){
					$realname = $rs['username'];
				}

				//调用检查函数将@后的内容进行验证，为UID对应的姓名相同则返回@与姓名，不相同则继续判断下一个@，没有找到匹配的最终将返回false
				$ValidValue = getAtName($TmpString, $TmpName, $realname);
				$ValidValue = trim($ValidValue);
				$at_friend= "space.php?uid=".$UserId;

				if($ValidValue != false){
					$Message = str_replace($ValidValue, "<a href=$at_friend>@".$realname."</a> ", $Message);
					$UserIds[] = $UserId;

				}
			}
		}	
		//Add by Add by am 2013-12-07  end

		$arr = array(
		"id" => intval($Pic),
		"uid" => intval($userid), 
		"idtype" => 'picid',
		"message" => getstr($Message, 5000, 1, 1, 1),
		"authorid" => intval($userid), 
		"author" => getstr($username, 15, 1, 1, 1),
		"ip" => getonlineip(),
		'dateline' => $_SGLOBAL['timestamp'],
		'magicflicker' => 0,
		);
		$shareid = inserttable('comment', $arr,1);
		if($shareid){
		$arrs = array('flag'=>'success');
		}else{
		$arrs = array('flag'=>'fail');
	}
}
	   $result = json_encode($arrs);
	   $result = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $result);
	echo $result;
	exit();
	
	
	
	
?>