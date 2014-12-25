<?php

if(!defined('iBUAA')) {
	exit('Access Denied');
}

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
if($uid) {
	$touid = $uid;
} else {
	$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
}
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

include_once S_ROOT.'./uc_client/client.php';

if($_GET['op'] == 'checknewpm') {

	//¼ì²éµ±Ç°ÓÃ»§
	if($_SGLOBAL['supe_uid']) {
		$ucnewpm = uc_pm_checknew($_SGLOBAL['supe_uid']);
		if($_SGLOBAL['member']['newpm'] != $ucnewpm) {
			updatetable('space', array('newpm'=>$ucnewpm), array('uid'=>$_SGLOBAL['supe_uid']));
		}
	}
	ssetcookie('checkpm', 1, 30);
	exit();

} elseif($_GET['op'] == 'delete') {

	$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
	$folder = $_GET['folder']=='inbox'?'inbox':'outbox';

	if(submitcheck('deletesubmit')) {
		$retrun = uc_pm_delete($_SGLOBAL['supe_uid'], $folder, array($pmid));
		if($retrun>0) {
			showmessage('do_success', $_POST['refer'], 0);
		} else {
			showmessage('this_message_could_not_be_deleted');
		}
	}
	
} elseif($_GET['op'] == 'send') {

	//ÅÐ¶ÏÊÇ·ñ·¢²¼Ì«¿ì
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	//ÐÂÓÃ»§¼ûÏ°
	cknewuser();
	
	//ºÚÃûµ¥
	if($touid) {
		if(isblacklist($touid)) {
			showmessage('is_blacklist');
		}
	}

	if(submitcheck('pmsubmit')) {

		//·¢ËÍÏûÏ¢
		$username = empty($_POST['username'])?'':$_POST['username'];

		$message = trim($_POST['message']);
		if(empty($message)) {
			showmessage('unable_to_send_air_news');
		}
		$subject = '';

		$return = 0;
		if($touid) {
			//Ö±½Ó¸øÒ»¸öÓÃ»§·¢PM
			$return = uc_pm_send($_SGLOBAL['supe_uid'], $touid, $subject, $message, 1, $pmid, 0);

			//·¢ËÍÓÊ¼þÍ¨Öª
			if($return > 0) {
				smail($touid, '', cplang('friend_pm',array($_SN[$space['uid']], getsiteurl().'space.php?do=pm')), '', 'friend_pm');
			}

		} elseif($username) {
			$newusers = array();
			$users = explode(',', $username);
			foreach ($users as $value) {
				$value = trim($value);
				if($value) {
					$newusers[] = $value;
				}
			}
			if($newusers) {
				$return = uc_pm_send($_SGLOBAL['supe_uid'], implode(',', $newusers), $subject, $message, 1, $pmid, 1);
			}

			//·¢ËÍÓÊ¼þÍ¨Öª
			$touid = 0;
			if($return > 0) {
				$query = $_SGLOBAL['db']->query('SELECT uid FROM '.tname('space').' WHERE username IN ('.simplode($users).')');
				while($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(empty($touid)) $touid = $value['uid'];
					smail($value['uid'], '', cplang('friend_pm',array($_SN[$space['uid']], getsiteurl().'space.php?do=pm')), '', 'friend_pm');
				}
			}
		}

		if($return > 0) {
			//¸üÐÂ×îºó·¢²¼Ê±¼ä
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastpost='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
            
            showmessage('do_success', $_POST['url'],2);
		} else {
			if(in_array($return, array(-1,-2,-3,-4))) {
				showmessage('message_can_not_send'.abs($return));
			} else {
				showmessage('message_can_not_send');
			}
		}
	}

} elseif($_GET['op'] == 'ignore') {
	
	if(submitcheck('ignoresubmit')) {
		uc_pm_blackls_set($_SGLOBAL['supe_uid'], $_POST['ignorelist']);
		showmessage('do_success', 'space.php?do=pm&view=ignore');
	}
	
} else {
	
	//ÐÂÓÃ»§¼ûÏ°
	cknewuser();

	if(!checkperm('allowpm')) {
		ckspacelog();
		showmessage('no_privilege');
	}
	//·¢ËÍ
	$friends = array();
	if($space['friendnum']) {
		$query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username FROM ".tname('friend')." WHERE uid=$_SGLOBAL[supe_uid] AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,100");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['username'] = saddslashes($value['username']);
			$friends[] = $value;
		}
	}
}

include_once template("cp_pm");

?>
