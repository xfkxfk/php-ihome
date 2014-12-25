<?php

if(!defined('iBUAA')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$eventid = empty($_GET['eventid'])?0:intval($_GET['eventid']);


/************************************************************************************************************/



$fileac = $_GET['file']?$_GET['file']:0;
$filetype = $_GET['type']?$_GET['type']:0;
if ($filetype == 'pic') {
    if ($fileac == 'upload') {
        $feed_file = pic_save($_FILES['feedfile'], $_POST['albumid'], $_POST['pic_title'], $_POST['topicid']);
        if ($feed_file && is_array($feed_file)) {
            $arr = array(
                'name'=>$feed_file['filename'],
                'pic'=>$feed_file['filepath'],
                'size'=>$feed_file['size'],
                'picid'=>$feed_file['picid']
			);
            echo json_encode($arr);
            exit();
        }
    }
    elseif ($fileac == 'uploadbg') { 
        try 
        {
            $feed_file = pic_save($_FILES['image-upload'], $albumid, 'bg_'.time(), 0);
            if ($feed_file && is_array($feed_file)) {
                $t=getimagesize($feed_file['new_filepath']); 
                $ret = array(
                    'master' => array(
                        'orig_name' => $feed_file['filename'],
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1],
                        'w' => $t[0]
                    ),
                    'thumb' => array(
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1], 
                        'w' => $t[0]
                    ));
                echo json_encode($ret);
                exit();
            }
        }
        catch(Exception $e)
        {}
        if(empty($_FILE['image-upload']))
        {
            showmessage('file_too_large');
        }
    }
    elseif ($fileac == 'upload_ad4app') { 
        try 
        {
            $feed_file = pic_save($_FILES['img'], $albumid, 'ad4app_'.time(), 0);
            if ($feed_file && is_array($feed_file)) {
                $t=getimagesize($feed_file['new_filepath']); 
                $ret = array(
                    'master' => array(
                        'orig_name' => $feed_file['filename'],
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1],
                        'w' => $t[0]
                    ),
                    'thumb' => array(
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1], 
                        'w' => $t[0]
                    ));
                echo json_encode($ret);
                exit();
            }
        }
        catch(Exception $e)
        {}
        if(empty($_FILE['image-upload']))
        {
            showmessage('file_too_large');
        }
    }
    elseif ($fileac == 'upload_loginbg') { 
        try	
        {
            $feed_file = pic_save($_FILES['image-upload'], $albumid, 'login_bg_'.time(), 0);
            if ($feed_file && is_array($feed_file)) {
                $t=getimagesize($feed_file['new_filepath']); 
                $ret = array(
                    'master' => array(
                        'orig_name' => $feed_file['filename'],
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1],
                        'w' => $t[0]
                    ),
                    'thumb' => array(
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1], 
                        'w' => $t[0]
                    ));
                echo json_encode($ret);
                exit();
            }
        }
        catch(Exception $e)
        {}
        if(empty($_FILE['image-upload']))
        {
            showmessage('file_too_large');
        }
    }
    elseif ($fileac == 'uploadbg_index') { 
        try	
        {
            $feed_file = pic_save($_FILES['index_bg_upload'], $albumid, 'index_bg_'.time(), 0);
            if ($feed_file && is_array($feed_file)) {
                $t=getimagesize($feed_file['new_filepath']); 
                $ret = array(
                    'master' => array(
                        'orig_name' => $feed_file['filename'],
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1],
                        'w' => $t[0]
                    ),
                    'thumb' => array(
                        'img_src' => '/attachment/'.$feed_file['filepath'],
                        'size' => round($feed_file['size']/1000, 0).'kb',
                        'h' => $t[1], 
                        'w' => $t[0]
                    ));
                echo json_encode($ret);
                exit();
            }
        }
        catch(Exception $e)
        {}
        if(empty($_FILE['image-upload']))
        {
            showmessage('file_too_large');
        }
    }
    elseif ($fileac == 'delete') {
        $picid = $_GET['picid']?$_GET['picid']:0;
        $delete[$picid] = $picid;
        if($delete) {
            include_once(S_ROOT.'./source/function_delete.php');
            $msg = deletepics($delete);
            if ($msg && is_array($msg)) {
                echo "ok";
                exit();
            }
        }
    }
}

elseif ($filetype == 'attach') {

    if ($fileac == 'upload') {
        $feed_file = uploadFile($_FILES['attachupload']);
        
        if ($feed_file && is_array($feed_file)) {
            $arr = array(
                'name'=>$feed_file['filename'],
                'path'=>$feed_file['filepath'],
                'size'=>$feed_file['size'],
                'aid'=>$feed_file['aid']
			);
            
            //nameºÍsizeÊÇÏÔÊ¾ÓÃµÄ
            //aidÓëpathÊÇ´æ´¢ÓÃµÄ
            echo json_encode($arr);
            exit();
        }
    }
    elseif ($fileac == 'delete') {
        $picid = $_GET['aid']?$_GET['aid']:0;
        $delete[$picid] = $picid;
        if($delete) {
            include_once(S_ROOT.'./source/function_delete.php');
            $msg = deletepics($delete);
            if ($msg && is_array($msg)) {
                echo "ok";
                exit();
            }
        }
    }
}


if($eventid){
    $query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$_GET[eventid]'");
    $event = $_SGLOBAL['db']->fetch_array($query);
    if(empty($event)){
        showmessage('event_does_not_exist');
    }
    if($event['grade'] == -2) {
        showmessage('event_is_closed');
    } elseif ($event['grade'] < 1) {
        showmessage('event_under_verify');
    }
    $query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE uid = '$_SGLOBAL[supe_uid]' AND eventid = '$eventid'");
    $userevent = $_SGLOBAL['db']->fetch_array($query);
    if($event['allowpic'] == 0 && $userevent['status'] < 3){
        showmessage('event_only_allows_admins_to_upload');
    }
    if($event['allowpic'] && $userevent['status'] < 2) {
        showmessage("event_only_allows_members_to_upload");
    }
}

if(submitcheck('albumsubmit')) {
    //´´½¨Ïà²á
    if($_POST['albumop'] == 'creatalbum') {
        $_POST['albumname'] = empty($_POST['albumname'])?'':getstr($_POST['albumname'], 50, 1, 1);
        if(empty($_POST['albumname'])) $_POST['albumname'] = gmdate('Ymd');

        $_POST['friend'] = intval($_POST['friend']);

        //ÒþË½
        $_POST['target_ids'] = '';
        if($_POST['friend'] == 2) {
            //ÌØ¶¨ºÃÓÑ
            $uids = array();
            $names = empty($_POST['target_names'])?array():explode(' ', str_replace(array(cplang('tab_space'), "\r\n", "\n", "\r"), ' ', $_POST['target_names']));
            if($names) {
                $query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE username IN (".simplode($names).")");
                while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                    $uids[] = $value['uid'];
                }
            }
            if(empty($uids)) {
                $_POST['friend'] = 3;//½ö×Ô¼º¿É¼û
            } else {
                $_POST['target_ids'] = implode(',', $uids);
            }
        } elseif($_POST['friend'] == 4) {
            //¼ÓÃÜ
            $_POST['password'] = trim($_POST['password']);
            if($_POST['password'] == '') $_POST['friend'] = 0;//¹«¿ª
        }
        if($_POST['friend'] !== 2) {
            $_POST['target_ids'] = '';
        }
        if($_POST['friend'] !== 4) {
            $_POST['password'] = '';
        }

        //´´½¨Ïà²á
        $setarr = array();
        $setarr['albumname'] = $_POST['albumname'];
        $setarr['uid'] = $_SGLOBAL['supe_uid'];
        $setarr['username'] = $_SGLOBAL['supe_username'];
        $setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
        $setarr['friend'] = $_POST['friend'];
        $setarr['password'] = $_POST['password'];
        $setarr['target_ids'] = $_POST['target_ids'];

        $albumid = inserttable('album', $setarr, 1);

        //¸üÐÂÓÃ»§Í³¼Æ
        if(empty($space['albumnum'])) {
            $space['albumnum'] = getcount('album', array('uid'=>$space['uid']));
            $albumnumsql = "albumnum=".$space['albumnum'];
        } else {
            $albumnumsql = 'albumnum=albumnum+1';
        }
        $_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$albumnumsql}, updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
    } else {
        $albumid = intval($_POST['albumid']);
    }

    $_POST['topicid'] = topic_check($_POST['topicid'], 'pic');

    if($_SGLOBAL['mobile']) {
        showmessage('do_success', 'cp.php?ac=upload');
    } else {
        echo "<script>";
        echo "parent.no_insert = 1;";
        echo "parent.albumid = $albumid;";
        echo "parent.topicid = $_POST[topicid];";
        echo "parent.start_upload();";
        echo "</script>";
    }
    exit();

} elseif(submitcheck('uploadsubmit')) {

    //ÉÏ´«Í¼Æ¬
    $albumid = $picid = 0;

    if(!checkperm('allowupload')) {
        if($_SGLOBAL['mobile']) {
            showmessage(cplang('not_allow_upload'));
        } else {
            echo "<script>";
            echo "alert(\"".cplang('not_allow_upload')."\")";
            echo "</script>";
            exit();
        }
    }

    //ÉÏ´«
    $_POST['topicid'] = topic_check($_POST['topicid'], 'pic');
    $uploadfiles = pic_save($_FILES['attach'], $_POST['albumid'], $_POST['pic_title'], $_POST['topicid']);
    if($uploadfiles && is_array($uploadfiles)) {
        $albumid = $uploadfiles['albumid'];
        $picid = $uploadfiles['picid'];
        $uploadStat = 1;
        if($eventid){
            $arr = array("eventid"=>$eventid, "picid" =>$picid, "uid"=>$_SGLOBAL['supe_uid'], "username"=>$_SGLOBAL['supe_username'], "dateline"=>$_SGLOBAL['timestamp']);
            inserttable("eventpic", $arr);
        }
    } else {
        $uploadStat = $uploadfiles;
    }

    if($_SGLOBAL['mobile']) {
        if($picid) {
            showmessage('do_success', "space.php?do=album&picid=$picid");
        } else {
            showmessage($uploadStat, 'cp.php?ac=upload');
        }
    } else {
        echo "<script>";
        echo "parent.albumid = $albumid;";
        echo "parent.topicid = $_POST[topicid];";
        echo "parent.uploadStat = '$uploadStat';";
        echo "parent.picid = $picid;";
        echo "parent.upload();";
        echo "</script>";
    }
    exit();

} elseif(submitcheck('viewAlbumid')) {
    //ÉÏ´«Íê³É·¢ËÍfeed
    if($eventid){//Ìøµ½»î¶¯Ò³Ãæ

        $imgs = array();
        $imglinks = array();
        $dateline = $_SGLOBAL['timestamp'] - 600;
        $query = $_SGLOBAL['db']->query("SELECT pic.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid=pic.picid WHERE ep.uid='$_SGLOBAL[supe_uid]' AND ep.eventid='$eventid' AND ep.dateline > $dateline ORDER BY ep.dateline DESC LIMIT 4");
        while($value=$_SGLOBAL['db']->fetch_array($query)){
            $imgs[] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
            $imglinks[] = "space.php?do=event&id=$eventid&view=pic&picid=".$value['picid'];
        }
        $picnum = 0;
        if($imgs){
            $picnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("eventpic")." WHERE eventid='$eventid'"), 0);
            feed_add('event', cplang('event_feed_share_pic_title'), '', cplang('event_feed_share_pic_info'),array("eventid"=>$eventid,"title"=>$event['title'],"picnum"=>$picnum)
                ,'',$imgs,$imglinks);
        }
        $_SGLOBAL['db']->query("UPDATE ".tname("event")." SET picnum='$picnum', updatetime='$_SGLOBAL[timestamp]' WHERE eventid='$eventid'");
        showmessage('do_success', 'space.php?do=event&view=pic&id='.$eventid, 0);

    } else {	

        //Ïà²áfeed
        if(ckprivacy('upload', 1)) {
            include_once(S_ROOT.'./source/function_feed.php');
            feed_publish($_POST['opalbumid'], 'albumid');
        }

        //µ¥¸öÍ¼Æ¬feed
        if($_POST['topicid']) {
            topic_join($_POST['topicid'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
            $url = "space.php?do=topic&topicid=$_POST[topicid]&view=pic";
        } else {
            $url = "space.php?uid=$_SGLOBAL[supe_uid]&do=album&id=".(empty($_POST['opalbumid'])?-1:$_POST['opalbumid']);
        }
        showmessage('upload_images_completed', $url, 0);
    }
} else {

    if(!checkperm('allowupload')) {
        ckspacelog();
        showmessage('no_privilege');
    }
    //ÊµÃûÈÏÖ¤
    ckrealname('album');

    //ÊÓÆµÈÏÖ¤
    ckvideophoto('album');

    //ÐÂÓÃ»§¼ûÏ°
    cknewuser();

    $siteurl = getsiteurl();

    //»ñÈ¡Ïà²á
    $albums = getalbums($_SGLOBAL['supe_uid']);

    //¼¤»î
    $actives = ($_GET['op'] == 'flash' || $_GET['op'] == 'cam')?array($_GET['op']=>' class="active"'):array('js'=>' class="active"');

    //¿Õ¼ä´óÐ¡
    $maxattachsize = checkperm('maxattachsize');
    if(!empty($maxattachsize)) {
        $maxattachsize = $maxattachsize + $space['addsize'];//¶îÍâ¿Õ¼ä
        $haveattachsize = formatsize($maxattachsize - $space['attachsize']);
    } else {
        $haveattachsize = 0;
    }

    //ºÃÓÑ×é
    $groups = getfriendgroup();

    //ÈÈÄÖ
    $topic = array();
    $topicid = $_GET['topicid'] = intval($_GET['topicid']);
    if($topicid) {
        $topic = topic_get($topicid);
    }
    if($topic) $actives = array('upload' => ' class="active"');

}

//Ä£°æ
include_once template("cp_upload");

?>
