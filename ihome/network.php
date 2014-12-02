<?php

include_once('./common.php');

//是否关闭站点
checkclose();

//空间被锁定
if($_SGLOBAL['supe_uid']) {
	$space = getspace($_SGLOBAL['supe_uid']);
	
	if($space['flag'] == -1) {
		showmessage('space_has_been_locked');
	}
	
	//禁止访问
	if(checkperm('banvisit')) {
		ckspacelog();
		showmessage('you_do_not_have_permission_to_visit');
	}
}
	
include_once(S_ROOT.'./source/network.php');

?>