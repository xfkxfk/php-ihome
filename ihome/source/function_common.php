<?php

if(!defined('iBUAA')) {
	exit('Access Denied');
}

//SQL ADDSLASHES 
function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

//取消HTML代码
function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

//字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;	// 随机密钥长度 取值 0-32;
	// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	// 当此值为 0 时，则不产生随机密钥

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

//清空cookie
function clearcookie() {
	global $_SGLOBAL;

	obclean();
	ssetcookie('auth', '', -86400 * 365);
	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = '';
	$_SGLOBAL['member'] = array();
}

//cookie设置
function ssetcookie($var, $value, $life=0) {
	global $_SGLOBAL, $_SC, $_SERVER;
	setcookie($_SC['cookiepre'].$var, $value, $life?($_SGLOBAL['timestamp']+$life):0, $_SC['cookiepath'], $_SC['cookiedomain'], $_SERVER['SERVER_PORT']==443?1:0);
}

//数据库连接
function dbconnect() {
	global $_SGLOBAL, $_SC;

	include_once(S_ROOT.'./source/class_mysql.php');
	if(empty($_SGLOBAL['db'])) {
            $_SGLOBAL['dbr'] = new dbstuff;
            $_SGLOBAL['dbr']->charset = $_SC['dbcharset'];
            $_SGLOBAL['dbr']->connect($_SC['dbrhost'], $_SC['dbruser'], $_SC['dbrpw'], $_SC['dbrname'], $_SC['rpconnect']);
            $_SGLOBAL['db'] = new mysqlproxy;
            $_SGLOBAL['db']->rdb = $_SGLOBAL['dbr'];
            $_SGLOBAL['db']->charset = $_SC['dbcharset'];
            $_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']);
    }
}

//获取在线IP
function getonlineip($format=0) {
	global $_SGLOBAL;

	if(empty($_SGLOBAL['onlineip'])) {
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	if($format) {
		$ips = explode('.', $_SGLOBAL['onlineip']);
		for($i=0;$i<3;$i++) {
			$ips[$i] = intval($ips[$i]);
		}
		return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	} else {
		return $_SGLOBAL['onlineip'];
	}
}

//判断当前用户登录状态
function checkauth() {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $_SN;

	if($_SGLOBAL['mobile'] && $_GET['m_auth']) $_SCOOKIE['auth'] = $_GET['m_auth'];
	if($_SCOOKIE['auth']) {
		@list($password, $uid) = explode("\t", authcode($_SCOOKIE['auth'], 'DECODE'));
		$_SGLOBAL['supe_uid'] = intval($uid);
		if($password && $_SGLOBAL['supe_uid']) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid='$_SGLOBAL[supe_uid]'");
			if($member = $_SGLOBAL['db']->fetch_array($query)) {
				if($member['password'] == $password) {
					$_SGLOBAL['supe_username'] = addslashes($member['username']);
					$_SGLOBAL['session'] = $member;
				} else {
					$_SGLOBAL['supe_uid'] = 0;
				}
			} else {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('member')." WHERE uid='$_SGLOBAL[supe_uid]'");
				if($member = $_SGLOBAL['db']->fetch_array($query)) {
					if($member['password'] == $password) {
						$_SGLOBAL['supe_username'] = addslashes($member['username']);
						$session = array('uid' => $_SGLOBAL['supe_uid'], 'username' => $_SGLOBAL['supe_username'], 'password' => $password);
						include_once(S_ROOT.'./source/function_space.php');
						insertsession($session);//登录
					} else {
						$_SGLOBAL['supe_uid'] = 0;
					}
				} else {
					$_SGLOBAL['supe_uid'] = 0;
				}
			}
		}
	}
	if(empty($_SGLOBAL['supe_uid'])) {
		clearcookie();
	} else {
		$_SGLOBAL['username'] = $member['username'];
        $query = $_SGLOBAL['db']->query("select * from ".tname("powerlevel")." where dept_uid=$_SGLOBAL[supe_uid]");
        if ($result = $_SGLOBAL['db']->fetch_array($query)) {
            $_SGLOBAL['isdept'] = $result['isdept'];
        } else {
            $_SGLOBAL['isdept'] = 0;
        }
	}
}

//获取用户app列表
function getuserapp() {
    global $_SGLOBAL, $_SCONFIG;

    $_SGLOBAL['my_userapp'] = $_SGLOBAL['my_menu'] = array();
    $_SGLOBAL['my_menu_more'] = 0;

    if($_SGLOBAL['supe_uid'] && $_SCONFIG['my_status']) {
        $space = getspace($_SGLOBAL['supe_uid']);
        $showcount=0;
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('userapp')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY menuorder DESC", 'SILENT');
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
            $_SGLOBAL['my_userapp'][$value['appid']] = $value;
            if($value['allowsidenav'] && !isset($_SGLOBAL['userapp'][$value['appid']])) {
                if($space['menunum'] < 5) $space['menunum'] = 10;
                if($space['menunum'] > 100 || $showcount < $space['menunum']) {
                    $_SGLOBAL['my_menu'][] = $value;
                    $showcount++;
                } else {
                    $_SGLOBAL['my_menu_more'] = 1;
                }
            }
        }
    }
}

//获取到表名
function tname($name) {
    global $_SC;
    return $_SC['tablepre'].$name;
}
function showmessage($msgkey, $url_forward='', $second=1, $values=array()) {
    global $_SGLOBAL, $_SC, $_SCONFIG, $_TPL, $space, $_SN;

    obclean();
    $_SGLOBAL['ad'] = array();

    include_once(S_ROOT.'./language/lang_showmessage.php');
    if(isset($_SGLOBAL['msglang'][$msgkey])) {
        $message = lang_replace($_SGLOBAL['msglang'][$msgkey], $values);
    } else {     
        $message = $msgkey;
    }    

    if($_SGLOBAL['mobile']) {
        include template('showmessage');
        exit();
    }            

    if(empty($_SGLOBAL['inajax']) && $url_forward && empty($second)) {

        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $url_forward");
    } else {     

        if($_SGLOBAL['inajax']) {
            if($url_forward) {
                $message = "<a href=\"$url_forward\">$message</a><ajaxok>";                  
            }            

            echo $message;
            ob_out();
        } else {     
            if($url_forward) {
                $message = "<a href=\"$url_forward\">$message</a><script>setTimeout(\"window.location.href ='$url_forward';\", ".($second*1000).");</script>";                       
            }
            include template('showmessage');
        }            
    }                    
    exit();      
}

//判断提交是否正确
function submitcheck($var) {
    if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['formhash'] == formhash()) {
            return true;
        } else {
            showmessage('submit_invalid');
        }
    } else {
        return false;
    }
}

//添加数据
function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) {
    global $_SGLOBAL;
    $insertkeysql = $insertvaluesql = $comma = '';
    foreach ($insertsqlarr as $insert_key => $insert_value) {
        $insertkeysql .= $comma.'`'.$insert_key.'`';
	        $insertvaluesql .= $comma.'\''.$insert_value.'\'';
        $comma = ', ';
    }
    $method = $replace?'REPLACE':'INSERT';
	$sql = $method.' INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')';
    $_SGLOBAL['db']->query($sql, $silent?'SILENT':'');
    if($returnid && !$replace) {
        return $_SGLOBAL['db']->insert_id();
    }
}

//更新数据
function updatetable($tablename, $setsqlarr, $wheresqlarr, $silent=0) {
    global $_SGLOBAL;

    $setsql = $comma = '';
    foreach ($setsqlarr as $set_key => $set_value) {//fix
        $setsql .= $comma.'`'.$set_key.'`'.'=\''.$set_value.'\'';
        $comma = ', ';
    }
    $where = $comma = '';
    if(empty($wheresqlarr)) {
        $where = '1';
    } elseif(is_array($wheresqlarr)) {
        foreach ($wheresqlarr as $key => $value) {
            $where .= $comma.'`'.$key.'`'.'=\''.$value.'\'';
            $comma = ' AND ';
        }
    } else {
        $where = $wheresqlarr;
    }
    $result = $_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.$where, $silent?'SILENT':'');
    return $result;
    //	echo 'UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.$where, $silent?'SILENT':'';
}

//获取用户空间信息
function getspace($key, $indextype='uid', $auto_open=0) {
    global $_SGLOBAL, $_SCONFIG, $_SN;

    $var = "space_{$key}_{$indextype}";
    if(empty($_SGLOBAL[$var])) {
        $space = array();
        $query = $_SGLOBAL['db']->query("SELECT sf.*, s.* FROM ".tname('space')." s LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid WHERE s.{$indextype}='$key'");
        if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
            $space = array();
            if($indextype=='uid' && $auto_open) {
                //自动开通空间
                include_once(S_ROOT.'./uc_client/client.php');
                if($user = uc_get_user($key, 1)) {
                    include_once(S_ROOT.'./source/function_space.php');
                    $space = space_open($user[0], addslashes($user[1]), 0, addslashes($user[2]));
                }
 			//隐私设置更新
			$query=$_SGLOBAL['db']->query('SELECT uid,privacy FROM '.tname('spacefield').' where uid='.$space[uid]);
			while($res=$_SGLOBAL['db']->fetch_array($query))	{
				$privacy=$res['privacy'];
				$privacy=stripslashes($i);	
				$arr=unserialize($paivacy);
				foreach ($arr as $key=>$j) {
					foreach ($j as $k=>$value)	{
						$arr[$key][$k] = 0;
					}
				}
				updatetable('spacefield', array('privacy'=>addslashes(serialize($arr))), array('uid'=>$res['uid']));
			}
           }
        }
        if($space) {			
            $_SN[$space['uid']] = ($_SCONFIG['realname'] && $space['name'] && $space['namestatus'])?$space['name']:$space['username'];
            $space['self'] = ($space['uid']==$_SGLOBAL['supe_uid'])?1:0;

            //好友缓存
            $space['friends'] = array();
            if(empty($space['friend'])) {
                if($space['friendnum']>0) {
                    $fstr = $fmod = '';
                    $query = $_SGLOBAL['db']->query("SELECT fuid FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1'");
                    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        $space['friends'][] = $value['fuid'];
                        $fstr .= $fmod.$value['fuid'];
                        $fmod = ',';
                    }
                    $space['friend'] = $fstr;
                }
            } else {
                $space['friends'] = explode(',', $space['friend']);
            }

            $space['username'] = addslashes($space['username']);
            $space['name'] = addslashes($space['name']);
            $space['privacy'] = empty($space['privacy'])?(empty($_SCONFIG['privacy'])?array():$_SCONFIG['privacy']):unserialize($space['privacy']);

            //通知数
            $space['allnotenum'] = 0;
            foreach (array('notenum','pokenum','addfriendnum','mtaginvitenum','eventinvitenum','myinvitenum') as $value) {
                $space['allnotenum'] = $space['allnotenum'] + $space[$value];
            }
            if($space['self']) {
                $_SGLOBAL['member'] = $space;
            }
            
            $space['alias'] = split(',', $space['alias']);
            $space['identity'] = split(',', $space['identity']);
            $space['iden_t'] = split(',', $space['iden_t']);

            for($i = 0; $i < count($space['iden_t']) - 1; $i++){
                for($j = $i + 1; $j < count($space['iden_t']) - 1; $j++){
                    if($space['iden_t'][$j] < $space['iden_t'][$i]){
                        $temp = $space['iden_t'][$i];
                        $space['iden_t'][$i] = $space['iden_t'][$j];
                        $space['iden_t'][$j] = $temp;
                        $temp = $space['identity'][$i];
                        $space['identity'][$i] = $space['identity'][$j];
                        $space['identity'][$j] = $temp;
                    }
                }
            }
		}
		$_SGLOBAL[$var] = $space;
	}
	return $_SGLOBAL[$var];
}

//获得用户UID
function getuid($name) {
	global $_SGLOBAL, $_SCONFIG;

	$wherearr[] = "(username='$name')";
	if($_SCONFIG['realname']) {
		$wherearr[] = "(name='$name' AND namestatus = 1)";
	}
	$uid = 0;
	$query = $_SGLOBAL['db']->query("SELECT uid,username,name,namestatus FROM ".tname('space')." WHERE ".implode(' OR ', $wherearr)." LIMIT 1");
	if($space = $_SGLOBAL['db']->fetch_array($query)) {
		$uid = $space['uid'];
	}
	return $uid;
}

//获取IP对应的国家,判断是否为国外用户
function getIpDetails(){
    $onlineip = getonlineip();
    //include("geoip.inc.php");
    //include_once(S_ROOT.'./source/geoip.inc.php');
    //$gi = geoip_open(S_ROOT."./source/GeoIP.dat",GEOIP_STANDARD);
    //$country_code = geoip_country_code_by_addr($gi, $onlineip);
    //$result = file_get_contents($get_ip_url);
    //$result = json_decode($result,1);
    //if ($country_code == 'CN'){
    //    $result = False;
    //}else{
    //    $result = True;
    //}
    $opts = array(
        'http'=>array(
        'method'=>"GET",
        'timeout'=>1,
        'header'=>'Connection: close\r\n',
        )
    );
    $context = stream_context_create($opts);
    $get_ip_url = 'http://freegeoip.net/json/'.$onlineip;
    $result = file_get_contents($get_ip_url,false,$context);
    $result = json_decode($result,1);
    return $result;
}
function is_overseas()	{
	$var = getIpDetails();
	if($var && $var['country_code']!='CN' && $var['country_code']!='RD')	{
		return true;
	}
	return false;
}
//获取当前用户信息
function getmember() {
	global $_SGLOBAL, $space;

	if(empty($_SGLOBAL['member']) && $_SGLOBAL['supe_uid']) {
		if($space['uid'] == $_SGLOBAL['supe_uid']) {
			$_SGLOBAL['member'] = $space;
		} else {
			$_SGLOBAL['member'] = getspace($_SGLOBAL['supe_uid']);
		}
	}
}

//检查隐私
function ckprivacy($type, $feedmode=0) {
	global $_SGLOBAL, $space, $_SCONFIG;

	$var = "ckprivacy_{$type}_{$feedmode}";
	if(isset($_SGLOBAL[$var])) {
		return $_SGLOBAL[$var];
	}
	$result = false;
	if($feedmode) {
		if($type == 'spaceopen') {
			if(!empty($_SCONFIG['privacy']['feed'][$type])) {
				$result = true;
			}
		} elseif(!empty($space['privacy']['feed'][$type])) {
			$result = true;
		}
	} elseif($space['self']){
		//自己
		$result = true;
	} else {
		if(empty($space['privacy']['view'][$type])) {
			$result = true;
		}
		if(!$result && $space['privacy']['view'][$type] == 1) {
			//是否好友
			if(!isset($space['isfriend'])) {
				$space['isfriend'] = $space['self'];
				if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
					$space['isfriend'] = 1;//是好友
				}
			}
			if($space['isfriend']) {
				$result = true;
			}
		}
	}
	$_SGLOBAL[$var] = $result;//当前页面缓存
	return $result;
}

//检查APP隐私
function app_ckprivacy($privacy) {
	global $_SGLOBAL, $space;

	$var = "app_ckprivacy_{$privacy}";
	if(isset($_SGLOBAL[$var])) {
		return $_SGLOBAL[$var];
	}
	$result = false;
	switch ($privacy) {
	case 0://公开
		$result = true;
		break;
	case 1://好友
		if(!isset($space['isfriend'])) {
			$space['isfriend'] = $space['self'];
			if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
				$space['isfriend'] = 1;//是好友
			}
		}
		if($space['isfriend']) {
			$result = true;
		}
		break;
	case 2://部分好友
		break;
	case 3://自己
		if($space['self']) {
			$result = true;
		}
		break;
	case 4://加密
		break;
	case 5://没有人
		break;
	default:
		$result = true;
		break;
	}
	$_SGLOBAL[$var] = $result;
	return $result;
}

//获取用户组
function getgroupid($experience, $gid=0) {
	global $_SGLOBAL;

	$needfind = false;
	if($gid) {
		if(@include_once(S_ROOT.'./data/data_usergroup_'.$gid.'.php')) {
			$group = $_SGLOBAL['usergroup'][$gid];
			if(empty($group['system'])) {
				if($group['exphigher']<$experience || $group['explower']>$experience) {
					$needfind = true;
				}
			}
		}
	} else {
		$needfind = true;
	}
	if($needfind) {
		$query = $_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE explower<='$experience' AND system='0' ORDER BY explower DESC LIMIT 1");
		$gid = $_SGLOBAL['db']->result($query, 0);
	}
	return $gid;
}

//检查权限
function checkperm($permtype) {
	global $_SGLOBAL, $space;

	if($permtype == 'admin') $permtype = 'manageconfig';

	$var = 'checkperm_'.$permtype;
	if(!isset($_SGLOBAL[$var])) {
		if(empty($_SGLOBAL['supe_uid'])) {
			$_SGLOBAL[$var] = '';
		} else {
			if(empty($_SGLOBAL['member'])) getmember();
			$gid = getgroupid($_SGLOBAL['member']['experience'], $_SGLOBAL['member']['groupid']);
			if(!@include_once(S_ROOT.'./data/data_usergroup_'.$gid.'.php')) {
	            include_once(S_ROOT.'./source/function_cache.php');
				usergroup_cache();
				@include_once(S_ROOT.'./data/data_usergroup_'.$gid.'.php');
			}

			if($gid != $_SGLOBAL['member']['groupid']) {
				updatetable('space', array('groupid'=>$gid), array('uid'=>$_SGLOBAL['supe_uid']));
				//赠送道具
				if($_SGLOBAL['usergroup'][$gid]['magicaward']) {
					include_once(S_ROOT.'./source/inc_magicaward.php');
				}
			}

			$_SGLOBAL[$var] = empty($_SGLOBAL['usergroup'][$gid][$permtype])?'':$_SGLOBAL['usergroup'][$gid][$permtype];
			if(substr($permtype, 0, 6) == 'manage' && empty($_SGLOBAL[$var])) {
				$_SGLOBAL[$var] = $_SGLOBAL['usergroup'][$gid]['manageconfig'];//权限覆盖
				if(empty($_SGLOBAL[$var])) {
					$_SGLOBAL[$var] = ckfounder($_SGLOBAL['supe_uid'])?1:0;//创始人
				}
			}
		}
	}
	return $_SGLOBAL[$var];
}

//写运行日志
function runlog($file, $log, $halt=0) {
	global $_SGLOBAL, $_SERVER;

	$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$log = sgmdate('Y-m-d H:i:s', $_SGLOBAL['timestamp'])."\t$type\t".getonlineip()."\t$_SGLOBAL[supe_uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($log))."\n";
	$yearmonth = sgmdate('Ym', $_SGLOBAL['timestamp']);
	$logdir = './data/log/';
	if(!is_dir($logdir)) mkdir($logdir, 0777,true);
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);
		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n");
		fclose($fp);
	}
	if($halt) exit();
	}

	//获取字符串
	function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0) {
		global $_SC, $_SGLOBAL;

		$string = trim($string);

		if($in_slashes) {
			//传入的字符有slashes
			$string = sstripslashes($string);
		}
		if($html < 0) {
			//去掉html标签
			$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
			$string = shtmlspecialchars($string);
		} elseif ($html == 0) {
			//转换html标签
			$string = shtmlspecialchars($string);
		}
		if($censor) {
			//词语屏蔽
			@include_once(S_ROOT.'./data/data_censor.php');
			if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $string)) {
				showmessage('information_contains_the_shielding_text');
			} else {
				$string = empty($_SGLOBAL['censor']['filter']) ? $string :
					@preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $string);
			}
		}

		$cut_word = 0;
		if($length && strlen($string) > $length) {
			$cut_word = 1;
			//截断字符
			$wordscut = '';
			if(strtolower($_SC['charset']) == 'utf-8') {
				//utf8编码
				$n = 0;
				$tn = 0;
				$noc = 0;
				while ($n < strlen($string)) {
					$t = ord($string[$n]);
					if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
						$tn = 1;
						$n++;
						$noc++;
					} elseif(194 <= $t && $t <= 223) {
						$tn = 2;
						$n += 2;
						$noc += 2;
					} elseif(224 <= $t && $t < 239) {
						$tn = 3;
						$n += 3;
						$noc += 2;
					} elseif(240 <= $t && $t <= 247) {
						$tn = 4;
						$n += 4;
						$noc += 2;
					} elseif(248 <= $t && $t <= 251) {
						$tn = 5;
						$n += 5;
						$noc += 2;
					} elseif($t == 252 || $t == 253) {
						$tn = 6;
						$n += 6;
						$noc += 2;
					} else {
						$n++;
					}
					if ($noc >= $length) {
						break;
					}
				}
				if ($noc > $length) {
					$n -= $tn;
				}
				$wordscut = substr($string, 0, $n);
			} else {
				for($i = 0; $i < $length - 1; $i++) {
					if(ord($string[$i]) > 127) {
						$wordscut .= $string[$i].$string[$i + 1];
						$i++;
					} else {
						$wordscut .= $string[$i];
					}
				}
			}
			$string = $wordscut;
		}
		if($bbcode) {
			include_once(S_ROOT.'./source/function_bbcode.php');
			$string = bbcode($string, $bbcode);
		}
		if($out_slashes) {
			$string = saddslashes($string);
		}

		$string = trim($string);
		if($cut_word) {
			$string = $string."...";
		}
		return $string;
	}

	//时间格式化
	function sgmdate($dateformat, $timestamp='', $format=0) {
		global $_SCONFIG, $_SGLOBAL;
		if(empty($timestamp)) {
			$timestamp = $_SGLOBAL['timestamp'];
		}
		$timeoffset = strlen($_SGLOBAL['member']['timeoffset'])>0?intval($_SGLOBAL['member']['timeoffset']):intval($_SCONFIG['timeoffset']);
		$result = '';
		if($format) {
			$time = $_SGLOBAL['timestamp'] - $timestamp;
			if($time > 24*3600) {
				$result = gmdate($dateformat, $timestamp + $timeoffset * 3600);
			} elseif ($time > 3600) {
				$result = intval($time/3600).lang('hour').lang('before');
			} elseif ($time > 60) {
				$result = intval($time/60).lang('minute').lang('before');
			} elseif ($time > 0) {
				$result = $time.lang('second').lang('before');
			} else {
				$result = lang('now');
			}
		} else {
			$result = gmdate($dateformat, $timestamp + $timeoffset * 3600);
		}
		return $result;
	}

	//字符串时间化
	function sstrtotime($string) {
		global $_SGLOBAL, $_SCONFIG;
		$time = '';
		if($string) {
			$time = strtotime($string);
			if(gmdate('H:i', $_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset'] * 3600) != date('H:i', $_SGLOBAL['timestamp'])) {
				$time = $time - $_SCONFIG['timeoffset'] * 3600;
			}
		}
		return $time;
	}

	//分页
	function multi($num, $perpage, $curpage, $mpurl, $ajaxdiv='', $todiv='') {
		global $_SCONFIG, $_SGLOBAL;

		if(empty($ajaxdiv) && $_SGLOBAL['inajax']) {
			$ajaxdiv = $_GET['ajaxdiv'];
		}

		$page = 5;
		if($_SGLOBAL['showpage']) $page = $_SGLOBAL['showpage'];

		$multipage = '';
		$mpurl .= strpos($mpurl, '?') ? '&' : '?';
		$realpages = 1;
		if($num > $perpage) {
			$offset = 2;
			$realpages = @ceil($num / $perpage);
			$pages = $_SCONFIG['maxpage'] && $_SCONFIG['maxpage'] < $realpages ? $_SCONFIG['maxpage'] : $realpages;
			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $curpage - $offset;
				$to = $from + $page - 1;
				if($from < 1) {
					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}
			$multipage = '';
			$urlplus = $todiv?"#$todiv":'';
			if($curpage - $offset > 1 && $pages > $page) {
				$multipage .= "<a ";
				if($_SGLOBAL['inajax']) {
					$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=1&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
				} else {
					$multipage .= "href=\"{$mpurl}page=1{$urlplus}\"";
				}
				$multipage .= " class=\"first\">1 ...</a>";
			}
			if($curpage > 1) {
				$multipage .= "<a ";
				if($_SGLOBAL['inajax']) {
					$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=".($curpage-1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
				} else {
					$multipage .= "href=\"{$mpurl}page=".($curpage-1)."$urlplus\"";
				}
				$multipage .= " class=\"prev\">&lsaquo;&lsaquo;</a>";
			}
			for($i = $from; $i <= $to; $i++) {
				if($i == $curpage) {
					$multipage .= '<strong>'.$i.'</strong>';
				} else {
					$multipage .= "<a ";
					if($_SGLOBAL['inajax']) {
						$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=$i&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
					} else {
						$multipage .= "href=\"{$mpurl}page=$i{$urlplus}\"";
					}
					$multipage .= ">$i</a>";
				}
			}
			if($curpage < $pages) {
				$multipage .= "<a ";
				if($_SGLOBAL['inajax']) {
					$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=".($curpage+1)."&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
				} else {
					$multipage .= "href=\"{$mpurl}page=".($curpage+1)."{$urlplus}\"";
				}
				$multipage .= " class=\"next\">&rsaquo;&rsaquo;</a>";
			}
			if($to < $pages) {
				$multipage .= "<a ";
				if($_SGLOBAL['inajax']) {
					$multipage .= "href=\"javascript:;\" onclick=\"ajaxget('{$mpurl}page=$pages&ajaxdiv=$ajaxdiv', '$ajaxdiv')\"";
				} else {
					$multipage .= "href=\"{$mpurl}page=$pages{$urlplus}\"";
				}
				$multipage .= " class=\"last\">... $realpages</a>";
			}
			if($multipage) {
				$multipage = '<em>&nbsp;'.$num.'&nbsp;</em>'.$multipage;
			}
		}
		return $multipage;
	}


	//处理分页
	function smulti($start, $perpage, $count, $url, $ajaxdiv='') {
		global $_SGLOBAL;

		$multi = array('last'=>-1, 'next'=>-1, 'begin'=>-1, 'end'=>-1, 'html'=>'');
		if($start > 0) {
			if(empty($count)) {
				showmessage('no_data_pages');
			} else {
				$multi['last'] = $start - $perpage;
			}
		}

		$showhtml = 0;
		if($count == $perpage) {
			$multi['next'] = $start + $perpage;
		}
		$multi['begin'] = $start + 1;
		$multi['end'] = $start + $count;

		if($multi['begin'] >= 0) {
			if($multi['last'] >= 0) {
				$showhtml = 1;
				if($_SGLOBAL['inajax']) {
					$multi['html'] .= "<a href=\"javascript:;\" onclick=\"ajaxget('$url&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">|&lt;</a> <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[last]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&lt;</a> ";
				} else {
					$multi['html'] .= "<a href=\"$url\">|&lt;</a> <a href=\"$url&start=$multi[last]\">&lt;</a> ";
				}
			} else {
				$multi['html'] .= "&lt;";
			}
			$multi['html'] .= " $multi[begin]~$multi[end] ";
			if($multi['next'] >= 0) {
				$showhtml = 1;
				if($_SGLOBAL['inajax']) {
					$multi['html'] .= " <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[next]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&gt;</a> ";
				} else {
					$multi['html'] .= " <a href=\"$url&start=$multi[next]\">&gt;</a>";
				}
			} else {
				$multi['html'] .= " &gt;";
			}
		}

		return $showhtml?$multi['html']:'';
	}

	//ob
	function obclean() {
		global $_SC;

		ob_end_clean();
		if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
			ob_start('ob_gzhandler');
		} else {
			ob_start();
		}
	}

	//模板调用
	function template($name) {
		global $_SCONFIG, $_SGLOBAL;
		//exit(S_ROOT);

		if($_SGLOBAL['mobile']) {
			$objfile = S_ROOT.'./api/mobile/tpl_'.$name.'.php';
			if (!file_exists($objfile)) {
				showmessage('m_function_is_disable_on_wap');
			}
		} else {
			if(strexists($name,'/')) {
				$tpl = $name;
			} else {
				$tpl = "template/$_SCONFIG[template]/$name";
			}
			$objfile = S_ROOT.'./data/tpl_cache/'.str_replace('/','_',$tpl).'.php';
			//if(!file_exists($objfile)) {
				include_once(S_ROOT.'./source/function_template.php');
				parse_template($tpl);
			//}
		}
		return $objfile;
	}

	//子模板更新检查
	function subtplcheck($subfiles, $mktime, $tpl) {
		global $_SC, $_SCONFIG;

		if($_SC['tplrefresh'] && ($_SC['tplrefresh'] == 1 || mt_rand(1, $_SC['tplrefresh']) == 1)) {
			$subfiles = explode('|', $subfiles);
			foreach ($subfiles as $subfile) {
				$tplfile = S_ROOT.'./'.$subfile.'.htm';
				if(!file_exists($tplfile)) {
					$tplfile = str_replace('/'.$_SCONFIG['template'].'/', '/default/', $tplfile);
				}
				@$submktime = filemtime($tplfile);
				if($submktime > $mktime) {
					include_once(S_ROOT.'./source/function_template.php');
					parse_template($tpl);
					break;
				}
			}
		}
	}

	//模块
	function block($param) {
		global $_SBLOCK;

		include_once(S_ROOT.'./source/function_block.php');
		block_batch($param);
	}

	//获取数目
	function getcount($tablename, $wherearr=array(), $get='COUNT(*)') {
		global $_SGLOBAL;
		if(empty($wherearr)) {
			$wheresql = '1';
		} else {
			$wheresql = $mod = '';
			foreach ($wherearr as $key => $value) {
				$wheresql .= $mod."`$key`='$value'";
				$mod = ' AND ';
			}
		}
		return $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT $get FROM ".tname($tablename)." WHERE $wheresql LIMIT 1"), 0);
	}

	//调整输出
	function ob_out() {
		global $_SGLOBAL, $_SCONFIG, $_SC;

		$content = ob_get_contents();

		$preg_searchs = $preg_replaces = $str_searchs = $str_replaces = array();

		if($_SCONFIG['allowrewrite']) {
			$preg_searchs[] = "/\<a href\=\"space\.php\?(uid|do)+\=([a-z0-9\=\&]+?)\"/ie";
			$preg_searchs[] = "/\<a href\=\"space.php\"/i";
			$preg_searchs[] = "/\<a href\=\"network\.php\?ac\=([a-z0-9\=\&]+?)\"/ie";
			$preg_searchs[] = "/\<a href\=\"network.php\"/i";

			$preg_replaces[] = 'rewrite_url(\'space-\',\'\\2\')';
			$preg_replaces[] = '<a href="space.html"';
			$preg_replaces[] = 'rewrite_url(\'network-\',\'\\1\')';
			$preg_replaces[] = '<a href="network.html"';
		}
		if($_SCONFIG['linkguide']) {
			$preg_searchs[] = "/\<a href\=\"http\:\/\/(.+?)\"/ie";
			$preg_replaces[] = 'iframe_url(\'\\1\')';
		}

		if($_SGLOBAL['inajax']) {
			$preg_searchs[] = "/([\x01-\x09\x0b-\x0c\x0e-\x1f])+/";
			$preg_replaces[] = ' ';

			$str_searchs[] = ']]>';
			$str_replaces[] = ']]&gt;';
		}

		if($preg_searchs) {
			$content = preg_replace($preg_searchs, $preg_replaces, $content);
		}
		if($str_searchs) {
			$content = trim(str_replace($str_searchs, $str_replaces, $content));
		}

		obclean();
		if($_SGLOBAL['inajax']) {
			xml_out($content);
		} else{
			if($_SCONFIG['headercharset']) {
				@header('Content-Type: text/html; charset='.$_SC['charset']);
			}
			echo $content;
			if(D_BUG) {
				@include_once(S_ROOT.'./source/inc_debug.php');
			}
		}
	}

	function xml_out($content) {
		global $_SC;
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("Content-type: application/xml; charset=$_SC[charset]");
		echo '<'."?xml version=\"1.0\" encoding=\"$_SC[charset]\"?>\n";
		echo "<root><![CDATA[".trim($content)."]]></root>";
		exit();
	}

	//rewrite链接
	function rewrite_url($pre, $para) {
		$para = str_replace(array('&','='), array('-', '-'), $para);
		return '<a href="'.$pre.$para.'.html"';
	}

	//外链
	function iframe_url($url) {
		$url = rawurlencode($url);
		return "<a href=\"link.php?url=http://$url\"";
	}

	//处理搜索关键字
	function stripsearchkey($string) {
		$string = trim($string);
		$string = str_replace('*', '%', addcslashes($string, '%_'));
		$string = str_replace('_', '\_', $string);
		return $string;
	}

	//检查搜索
	function cksearch($theurl) {
		global $_SGLOBAL, $_SCONFIG, $space;

		$theurl = stripslashes($theurl)."&page=".$_GET['page'];
		if($searchinterval = checkperm('searchinterval')) {
			$waittime = $searchinterval - ($_SGLOBAL['timestamp'] - $space['lastsearch']);
			if($waittime > 0) {
				showmessage('search_short_interval', '', 1, array($waittime, $theurl));
			}
		}
		if(!checkperm('searchignore')) {
			$reward = getreward('search', 0);
			if($reward['credit'] || $reward['experience']) {
				if(empty($_GET['confirm'])) {
					$theurl .= '&confirm=yes';
					showmessage('points_deducted_yes_or_no', '', 1, array($reward['credit'], $reward['experience'], $theurl));
				} else {
					if($space['credit'] < $reward['credit'] || $space['experience'] < $reward['experience']) {
						showmessage('points_search_error');
					} else {
						//扣分
						$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastsearch='$_SGLOBAL[timestamp]', credit=credit-$reward[credit], experience=experience-$reward[experience] WHERE uid='$_SGLOBAL[supe_uid]'");
					}
				}
			}
		}
	}

	//是否屏蔽二级域名
	function isholddomain($domain) {
		global $_SCONFIG;

		$domain = strtolower($domain);

		if(preg_match("/^[^a-z]/i", $domain)) return true;
		$holdmainarr = empty($_SCONFIG['holddomain'])?array('www'):explode('|', $_SCONFIG['holddomain']);
		$ishold = false;
		foreach ($holdmainarr as $value) {
			if(strpos($value, '*') === false) {
				if(strtolower($value) == $domain) {
					$ishold = true;
					break;
				}
			} else {
				$value = str_replace('*', '', $value);
				if(@preg_match("/$value/i", $domain)) {
					$ishold = true;
					break;
				}
			}
		}
		return $ishold;
	}

	//连接字符
	function simplode($ids) {
		return "'".implode("','", $ids)."'";
	}

	//显示进程处理时间
	function debuginfo() {
		global $_SGLOBAL, $_SC, $_SCONFIG;

		if(empty($_SCONFIG['debuginfo'])) {
			$info = '';
		} else {
			$mtime = explode(' ', microtime());
			$totaltime = number_format(($mtime[1] + $mtime[0] - $_SGLOBAL['supe_starttime']), 4);
			$info = 'Processed in '.$totaltime.' second(s), '.$_SGLOBAL['db']->querynum.' queries'.
				($_SC['gzipcompress'] ? ', Gzip enabled' : NULL);
		}

		return $info;
	}

	//格式化大小函数
	function formatsize($size) {
		$prec=3;
		$size = round(abs($size));
		$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
		if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
		$unit = min(4, floor(log($size)/log(2)/10));
		$size = $size * pow(2, -10*$unit);
		$digi = $prec - 1 - floor(log($size)/log(10));
		$size = round($size * pow(10, $digi)) * pow(10, -$digi);
		return $size.$units[$unit];
	}

	//获取文件内容
	function sreadfile($filename) {
		$content = '';
		if(function_exists('file_get_contents')) {
			@$content = file_get_contents($filename);
		} else {
			if(@$fp = fopen($filename, 'r')) {
				@$content = fread($fp, filesize($filename));
				@fclose($fp);
			}
		}
		return $content;
	}

	//写入文件
	function swritefile($filename, $writetext, $openmod='w') {
		if(@$fp = fopen($filename, $openmod)) {
			flock($fp, 2);
			fwrite($fp, $writetext);
			fclose($fp);
			return true;
		} else {
			runlog('error', "File: $filename write error.");
			return false;
		}
	}

	//产生随机字符
	function random($length, $numeric = 0) {
		PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
		$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		$hash = '';
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}

	//判断字符串是否存在
	function strexists($haystack, $needle) {
		return !(strpos($haystack, $needle) === FALSE);
	}

	//获取数据
	function data_get($var, $isarray=0) {
		global $_SGLOBAL;

		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('data')." WHERE var='$var' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			return $isarray?$value:$value['datavalue'];
		} else {
			return '';
		}
	}

	//更新数据
	function data_set($var, $datavalue, $clean=0) {
		global $_SGLOBAL;

		if($clean) {
			$_SGLOBAL['db']->query("DELETE FROM ".tname('data')." WHERE var='$var'");
		} else {
			if(is_array($datavalue)) $datavalue = serialize(sstripslashes($datavalue));
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('data')." (var, datavalue, dateline) VALUES ('$var', '".addslashes($datavalue)."', '$_SGLOBAL[timestamp]')");
		}
	}

	//检查站点是否关闭
	function checkclose() {
		global $_SGLOBAL, $_SCONFIG;

		//站点关闭
		if($_SCONFIG['close'] && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) {
			if(empty($_SCONFIG['closereason'])) {
				showmessage('site_temporarily_closed');
			} else {
				showmessage($_SCONFIG['closereason']);
			}
		}
		//IP访问检查
		if((!ipaccess($_SCONFIG['ipaccess']) || ipbanned($_SCONFIG['ipbanned'])) && !ckfounder($_SGLOBAL['supe_uid']) && !checkperm('closeignore')) {
			showmessage('ip_is_not_allowed_to_visit');
		}
	}

	//站点链接
	function getsiteurl() {
		global $_SCONFIG;

		if(empty($_SCONFIG['siteallurl'])) {
			$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
			return shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1));
		} else {
			return $_SCONFIG['siteallurl'];
		}
	}

	//获取文件名后缀
	function fileext($filename) {
		return strtolower(trim(substr(strrchr($filename, '.'), 1)));
	}

	function getFileExt($filename)
	{
		$x = explode('.', $filename);
		return '.'.end($x);
	}

	//获取上传路径
	function getfilepath($fileext, $mkdir=false) {
		global $_SGLOBAL, $_SC;

		$filepath = "{$_SGLOBAL['supe_uid']}_{$_SGLOBAL['timestamp']}".random(4).".$fileext";
		$name1 = gmdate('Ym');
		$name2 = gmdate('j');

		if($mkdir) {
			$newfilename = $_SC['attachdir'].'./'.$name1;
			if(!is_dir($newfilename)) {
				if(!@mkdir($newfilename)) {
					runlog('error', "DIR: $newfilename can not make");
					return $filepath;
				}
			}
			$newfilename .= '/'.$name2;
			if(!is_dir($newfilename)) {
				if(!@mkdir($newfilename)) {
					runlog('error', "DIR: $newfilename can not make");
					return $name1.'/'.$filepath;
				}
			}
		}
		return $name1.'/'.$name2.'/'.$filepath;
	}


	//去掉slassh
	function sstripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = sstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}

	//显示广告
	function adshow($pagetype) {
		global $_SGLOBAL;

		@include_once(S_ROOT.'./data/data_ad.php');
		if(empty($_SGLOBAL['ad']) || empty($_SGLOBAL['ad'][$pagetype])) return false;
		$ads = $_SGLOBAL['ad'][$pagetype];
		$key = mt_rand(0, count($ads)-1);
		$id = $ads[$key];
		$file = S_ROOT.'./data/adtpl/'.$id.'.htm';
		echo sreadfile($file);
	}

	//编码转换
	function siconv($str, $out_charset, $in_charset='') {
		global $_SC;

		$in_charset = empty($in_charset)?strtoupper($_SC['charset']):strtoupper($in_charset);
		$out_charset = strtoupper($out_charset);
		if($in_charset != $out_charset) {
			if (function_exists('iconv') && (@$outstr = iconv("$in_charset//IGNORE", "$out_charset//IGNORE", $str))) {
				return $outstr;
			} elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($str, $out_charset, $in_charset))) {
				return $outstr;
			}
		}
		return $str;//转换失败
	}

	//获取用户数据
	function getpassport($username, $password) {
		global $_SGLOBAL, $_SC;

		$passport = array();
		if(!@include_once S_ROOT.'./uc_client/client.php') {
			showmessage('system_error');
		}

		$ucresult = uc_user_login($username, $password);
		if($ucresult[0] > 0) {
			$passport['uid'] = $ucresult[0];
			$passport['username'] = $ucresult[1];
			$passport['email'] = $ucresult[3];
		}
		return $passport;
	}

function notifyUserLocked($username) {
    global $_SGLOBAL;
    $email=NULL;
    $mobile=NULL;

    if (isemail($username)){
	    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacefield')." WHERE email='$username'");
		$value = $_SGLOBAL['db']->fetch_array($query);
		if (empty($value)){
            return;
		}

        $email = $value['email'];
        $mobile = $value['mobile'];
    } else {
        $sql = "SELECT b.email as email , b.mobile as mobile FROM ".tname('member')." as a, ".tname('spacefield')." as b WHERE username='$username' and a.uid = b.uid"; 
	    $query = $_SGLOBAL['db']->query($sql);
		$value = $_SGLOBAL['db']->fetch_array($query);
        if (empty($value)) {
            return;
        }

        $email = $value['email'];
        $mobile = $value['mobile'];
    }
    if ($email) {
        echo $email;
        smail(NULL, $email, '密码错误次数太多', '密码错误次数太多，您的账号已被锁定30分钟，请在30分钟之后再次尝试登录。');
    } else if ($mobile) {
        echo $mobile;
        sendsms($mobile, '密码错误次数太多', '密码错误次数太多，您的账号已被锁定30分钟，请在30分钟之后再次尝试登录。');
    }
}

	//用户操作时间间隔检查
	function interval_check($type) {
		global $_SGLOBAL, $space;

		$intervalname = $type.'interval';
		$lastname = 'last'.$type;

		$waittime = 0;
		if($interval = checkperm($intervalname)) {
			$lasttime = isset($space[$lastname])?$space[$lastname]:getcount('space', array('uid'=>$_SGLOBAL['supe_uid']), $lastname);
			$waittime = $interval - ($_SGLOBAL['timestamp'] - $lasttime);
		}
		return $waittime;
	}

	//处理上传图片连接
	function pic_get($filepath, $thumb, $remote, $return_thumb=1) {
		global $_SCONFIG, $_SC;

		if(empty($filepath)) {
			$url = 'image/nopic.gif';
		} else {
			$url = $filepath;
			if($return_thumb && $thumb) $url .= '.thumb.jpg';
			if($remote) {
				$url = $_SCONFIG['ftpurl'].$url;
			} else {
				$url = $_SC['attachurl'].$url;
			}
		}

		return $url;
	}

	//获得封面图片链接
	function pic_cover_get($pic, $picflag) {
		global $_SCONFIG, $_SC;

		if(empty($pic)) {
			$url = 'image/nopic.gif';
		} else {
			if($picflag == 1) {//本地
				$url = $_SC['attachurl'].$pic;
			} elseif ($picflag == 2) {//远程
				$url = $_SCONFIG['ftpurl'].$pic;
			} else {//网络
				$url = $pic;
			}
		}

		return $url;
	}

	//处理积分星星
	function getstar($experience) {
		global $_SCONFIG;

		$starimg = '';
		if($_SCONFIG['starcredit'] > 1) {
			//计算星星数
			$starnum = intval($experience/$_SCONFIG['starcredit']) + 1;
			if($_SCONFIG['starlevelnum'] < 2) {
				if($starnum > 10) $starnum = 10;
				for($i = 0; $i < $starnum; $i++) {
					$starimg .= '<img src="image/star_level10.gif" align="absmiddle" />';
				}
			} else {
				//计算等级(10个)
				for($i = 10; $i > 0; $i--) {
					$numlevel = intval($starnum / pow($_SCONFIG['starlevelnum'], ($i - 1)));
					if($numlevel > 10) $numlevel = 10;
					if($numlevel) {
						for($j = 0; $j < $numlevel; $j++) {
							$starimg .= '<img src="image/star_level'.$i.'.gif" align="absmiddle" />';
						}
						break;
					}
				}
			}
		}
		if(empty($starimg)) $starimg = '<img src="image/credit.gif" alt="'.$experience.'" align="absmiddle" alt="'.$experience.'" title="'.$experience.'" />';
		return $starimg;
	}

	//获取好友状态
	function getfriendstatus($uid, $fuid) {
		global $_SGLOBAL;

		$query = $_SGLOBAL['db']->query("SELECT status FROM ".tname('friend')." WHERE uid='$uid' AND fuid='$fuid' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			return $value['status'];
		} else {
			return -1;//没有记录
		}
	}

	//重新组建
	function renum($array) {
		$newnums = $nums = array();
		foreach ($array as $id => $num) {
			$newnums[$num][] = $id;
			$nums[$num] = $num;
		}
		return array($nums, $newnums);
	}

	//检查定向
	function ckfriend($touid, $friend, $target_ids='') {
		global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $space;

		//游客
		if(empty($_SGLOBAL['supe_uid'])) {
			return $friend?false:true;
		}

		//自己
		if($touid == $_SGLOBAL['supe_uid']) return true;//自己

		$var = 'ckfriend_'.md5($touid.'_'.$friend.'_'.$target_ids);
		if(isset($_SGLOBAL[$var])) return $_SGLOBAL[$var];

		$_SGLOBAL[$var] = false;
		switch ($friend) {
		case 0://全站用户可见
			$_SGLOBAL[$var] = true;
			break;
		case 1://全好友可见
			if($space['uid'] == $touid) {
				if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
					$_SGLOBAL[$var] = true;
				}
			} else {
				$_SGLOBAL[$var] = getfriendstatus($_SGLOBAL['supe_uid'], $touid)==1?true:false;
			}
			break;
		case 2://仅指定好友可见
			if($target_ids) {
				$target_ids = explode(',', $target_ids);
				if(in_array($_SGLOBAL['supe_uid'], $target_ids)) $_SGLOBAL[$var] = true;
			}
			break;
		case 3://仅自己可见
			break;
		case 4://凭密码查看
			$_SGLOBAL[$var] = true;
			break;
		default:
			break;
		}
		return $_SGLOBAL[$var];
	}

	//整理feed
	function mkfeed($feed, $actors=array()) {
		global $_SGLOBAL, $_SN, $_SCONFIG;

		$feed['title_data'] = empty($feed['title_data'])?array():unserialize($feed['title_data']);
		if(!is_array($feed['title_data'])) $feed['title_data'] = array();
		$feed['body_data'] = empty($feed['body_data'])?array():unserialize($feed['body_data']);
		if(!is_array($feed['body_data'])) $feed['body_data'] = array();

		//title
		$searchs = $replaces = array();
		if($feed['title_data'] && is_array($feed['title_data'])) {
			foreach (array_keys($feed['title_data']) as $key) {
				$searchs[] = '{'.$key.'}';
				$replaces[] = $feed['title_data'][$key];
			}
		}

		$searchs[] = '{actor}';
		$replaces[] = empty($actors)?"<a href=\"space.php?uid=$feed[uid]\">".$_SN[$feed['uid']]."</a>":implode(lang('dot'), $actors);

		$searchs[] = '{app}';
		if(empty($_SGLOBAL['app'][$feed['appid']])) {
			$replaces[] = '';
		} else {
			$app = $_SGLOBAL['app'][$feed['appid']];
			$replaces[] = "<a href=\"$app[url]\">$app[name]</a>";
		}
		$feed['title_template'] = mktarget(str_replace($searchs, $replaces, $feed['title_template']));

		//body
		$searchs = $replaces = array();
		if($feed['body_data'] && is_array($feed['body_data'])) {
			foreach (array_keys($feed['body_data']) as $key) {
				$searchs[] = '{'.$key.'}';
				$replaces[] = $feed['body_data'][$key];
			}
		}

		$feed['magic_class'] = '';
		if($feed['appid']) {
			if(!empty($feed['body_data']['magic_color'])) {
				$feed['magic_class'] = 'magiccolor'.$feed['body_data']['magic_color'];
			}
			if(!empty($feed['body_data']['magic_thunder'])) {
				$feed['magic_class'] = 'magicthunder';
			}
		}

		$searchs[] = '{actor}';
		$replaces[] = "<a href=\"space.php?uid=$feed[uid]\">$feed[username]</a>";
		$feed['body_template'] = mktarget(str_replace($searchs, $replaces, $feed['body_template']));

		$feed['body_general'] = mktarget($feed['body_general']);

		//icon
		if($feed['appid']) {
			$feed['icon_image'] = "image/icon/{$feed['icon']}.gif";
		} else {
			$feed['icon_image'] = "http://appicon.manyou.com/icons/{$feed['icon']}";
		}

		//阅读
		$feed['style'] = $feed['target'] = '';
	/*if($_SCONFIG['feedread'] && empty($feed['id'])) {
		$read_feed_ids = empty($_COOKIE['read_feed_ids'])?array():explode(',',$_COOKIE['read_feed_ids']);
		if($read_feed_ids && in_array($feed['feedid'], $read_feed_ids)) {
			$feed['style'] = " class=\"feedread\"";
		} else {
			$feed['style'] = " onclick=\"readfeed(this, $feed[feedid]);\"";
		}
	}*/
		if($_SCONFIG['feedtargetblank']) {
			$feed['target'] = ' target="_blank"';
		}

		//管理
		if(in_array($feed['idtype'], array('blogid','picid','sid','pid','eventid','videoid'))) {
			$feed['showmanage'] = 1;
		}

		//是否自身应用
		$feed['thisapp'] = 0;
		if($feed['appid'] == UC_APPID) {
			$feed['thisapp'] = 1;
		}

		return $feed;
	}

	//整理feed的链接
	function mktarget($html) {
		global $_SCONFIG;

		if($html && $_SCONFIG['feedtargetblank']) {
			$html = preg_replace("/<a(.+?)href=([\'\"]?)([^>\s]+)\\2([^>]*)>/i", '<a target="_blank" \\1 href="\\3" \\4>', $html);
		}
		return $html;
	}

	//整理分享
	function mkshare($share) {
		$share['body_data'] = unserialize($share['body_data']);

		//body
		$searchs = $replaces = array();
		if($share['body_data']) {
			foreach (array_keys($share['body_data']) as $key) {
				$searchs[] = '{'.$key.'}';
				$replaces[] = $share['body_data'][$key];
			}
		}
		$share['body_template'] = str_replace($searchs, $replaces, $share['body_template']);

		return $share;
	}

	//ip访问允许
	function ipaccess($ipaccess) {
		return empty($ipaccess)?true:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipaccess, '/')).")/", getonlineip());
	}

	//ip访问禁止
	function ipbanned($ipbanned) {
		return empty($ipbanned)?false:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipbanned, '/')).")/", getonlineip());
	}

	//检查start
	function ckstart($start, $perpage) {
		global $_SCONFIG;

		$maxstart = $perpage*intval($_SCONFIG['maxpage']);
		if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
			showmessage('length_is_not_within_the_scope_of');
		}
	}

	//处理头像
	function avatar($uid, $size='small', $returnsrc = FALSE, $round=0,$summary='',$lazy='') {
        if (empty($uid)) {
            return fallback_avatar();
        }

		global $_SCONFIG, $_SN, $_SGLOBAL;
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'small';
		$avatarfile = avatar_file($uid, $size);
		$query = $_SGLOBAL['db']->query("SELECT sex FROM ".tname('spacefield')." WHERE uid='$uid' LIMIT 1");
		if($gd = $_SGLOBAL['db']->result($query))
		{
			if($gd==1) $gender = 'm'; else $gender='f';
		}
		else
		{
			$gender = "m";
		}
		$randavatar = '/images/avatar/'.$gender.'_'.$size.'_1.png';
		if($lazy=='lazy'){
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img class="lazy" width="160" height="160" data-original="'.UC_API.'/data/avatar/'.$avatarfile.'" src="\''.UC_API.$randavatar.'\'" onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'">';
		}
		if($summary=='summary')	{
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img style="height:32px;width:32px;" src="'.UC_API.'/data/avatar/'.$avatarfile.'" onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'">';
		}
		else if($summary=='feed') {
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img src="'.UC_API.'/data/avatar/'.$avatarfile.'" class=\'img-circle feed_avatar\' onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'">';
		}
		if($round==0)
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img src="'.UC_API.'/data/avatar/'.$avatarfile.'" onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'">';
		else if($round==1)	
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img src="'.UC_API.'/data/avatar/'.$avatarfile.'" onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'" class=\'img-circle\' style=\'height:36px; box-shadow: 1px 2px 2px rgba(0, 0, 0, .2);\'/>';
		else
		{
			return $returnsrc ? UC_API.'/data/avatar/'.$avatarfile : '<img src="'.UC_API.'/data/avatar/'.$avatarfile.'" onerror="this.onerror=null;this.src=\''.UC_API.$randavatar.'\'" class=\'img-circle shadow_avatar\' style=\'height:131px; width:131px;\'/>';

		}
	}

    function fallback_avatar() {
		$randavatar = '';
        return '<img src="'.UC_API.'/images/avatar/m_small_1.png" class="img-circle shadow_avatar">';
    }

	//得到头像
	function avatar_file($uid, $size) {
		global $_SGLOBAL, $_SCONFIG;
		$type = empty($_SCONFIG['avatarreal'])?'virtual':'real';
		$var = "avatarfile_{$uid}_{$size}_{$type}";
		if(empty($_SGLOBAL[$var])) {
			$uid = abs(intval($uid));
			$uid = sprintf("%09d", $uid);
			$dir1 = substr($uid, 0, 3);
			$dir2 = substr($uid, 3, 2);
			$dir3 = substr($uid, 5, 2);
			$typeadd = $type == 'real' ? '_real' : '';
			$_SGLOBAL[$var] = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
		}
		return $_SGLOBAL[$var];
	}

	//检查是否登录
	function checklogin() {
		global $_SGLOBAL, $_SCONFIG;

		if(empty($_SGLOBAL['supe_uid'])) {
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
			//showmessage('to_login', 'index.php');
		}
	}

	//获得前台语言
	function lang($key, $vars=array()) {
		global $_SGLOBAL;

		include_once(S_ROOT.'./language/lang_source.php');
		if(isset($_SGLOBAL['sourcelang'][$key])) {
			$result = lang_replace($_SGLOBAL['sourcelang'][$key], $vars);
		} else {
			$result = $key;
		}
		return $result;
	}

	//获得后台语言
	function cplang($key, $vars=array()) {
		global $_SGLOBAL;

		include_once(S_ROOT.'./language/lang_cp.php');
		if(isset($_SGLOBAL['cplang'][$key])) {
			$result = lang_replace($_SGLOBAL['cplang'][$key], $vars);
		} else {
			$result = $key;
		}
		return $result;
	}

	//语言替换
	function lang_replace($text, $vars) {
		if($vars) {
			foreach ($vars as $k => $v) {
				$rk = $k + 1;
				$text = str_replace('\\'.$rk, $v, $text);
			}
		}
		return $text;
	}

	//获得用户组名
	function getfriendgroup() {
		global $_SCONFIG, $space;

		$groups = array();
		$spacegroup = empty($space['privacy']['groupname'])?array():$space['privacy']['groupname'];
		for($i=0; $i<$_SCONFIG['groupnum']; $i++) {
			if($i == 0) {
				$groups[0] = lang('friend_group_default');
			} else {
				if(!empty($spacegroup[$i])) {
					$groups[$i] = $spacegroup[$i];
				} else {
					if($i<8) {
						$groups[$i] = lang('friend_group_'.$i);
					} else {
						$groups[$i] = lang('friend_group').$i;
					}
				}
			}
		}
		return $groups;
	}

	//截取链接
	function sub_url($url, $length) {
		if(strlen($url) > $length) {
			$url = str_replace(array('%3A', '%2F'), array(':', '/'), rawurlencode($url));
			$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return $url;
	}

	//获取用户名
	function realname_set($uid, $username='', $name='', $namestatus=0) {
		global $_SGLOBAL, $_SN, $_SCONFIG;
		if($name)
		{
			$_SN[$uid] = ($_SCONFIG['realname'] && $namestatus)?$name:$username;
		} 
		elseif(empty($_SN[$uid])) {
			$_SN[$uid] = $username;
			$_SGLOBAL['select_realname'][$uid] = $uid;//需要检索
		}
	}

	//获取实名
	function realname_get() {
		global $_SGLOBAL, $_SCONFIG, $_SN, $space;

		if(empty($_SGLOBAL['_realname_get']) && $_SCONFIG['realname'] && $_SGLOBAL['select_realname'])
		{
			//禁止重复调用
			$_SGLOBAL['_realname_get'] = 1;

			//已经有的
			if($space && isset($_SGLOBAL['select_realname'][$space['uid']]))
			{
				unset($_SGLOBAL['select_realname'][$space['uid']]);
			}
			if($_SGLOBAL['member']['uid'] && isset($_SGLOBAL['select_realname'][$_SGLOBAL['member']['uid']]))
			{
				unset($_SGLOBAL['select_realname'][$_SGLOBAL['member']['uid']]);
			}
			//获得实名
			$uids = empty($_SGLOBAL['select_realname'])?array():array_keys($_SGLOBAL['select_realname']);
			if($uids)
			{
				$query = $_SGLOBAL['db']->query("SELECT uid, name, namestatus FROM ".tname('space')." WHERE uid IN (".simplode($uids).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query))
				{
					if($value['name'] && $value['namestatus'])
					{
						$_SN[$value['uid']] = $value['name'];
					}
				}
			}
		}
	}

	//群组信息
	function getmtag($id) {
		global $_SGLOBAL;

		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$id'");
		if(!$mtag = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('designated_election_it_does_not_exist');
		}
		//空群组
		if($mtag['membernum']<1 && ($mtag['joinperm'] || $mtag['viewperm'])) {
			$mtag['joinperm'] = $mtag['viewperm'] = 0;
			updatetable('mtag', array('joinperm'=>0, 'viewperm'=>0), array('tagid'=>$id));
		}

		//处理
		include_once(S_ROOT.'./data/data_profield.php');
		$mtag['field'] = $_SGLOBAL['profield'][$mtag['fieldid']];
		$mtag['title'] = $mtag['field']['title'];
		if(empty($mtag['pic'])) {
			$mtag['pic'] = 'image/nologo.jpg';
		}

		//成员级别
		$mtag['ismember'] = 0;
		$mtag['grade'] = -9;//-9 非成员 -2 申请 -1 禁言 0 普通 1 明星 8 副群主 9 群主
		$query = $_SGLOBAL['db']->query("SELECT grade FROM ".tname('tagspace')." WHERE tagid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			$mtag['grade'] = $value['grade'];
			$mtag['ismember'] = 1;
		}
		if($mtag['grade'] < 9 && checkperm('managemtag')) {
			$mtag['grade'] = 9;
		}
		$mtag['allowthread'] = $mtag['grade']>=0?1:$mtag['threadperm'];
		$mtag['allowpost'] = $mtag['grade']>=0?1:$mtag['postperm'];
		$mtag['allowview'] = ($mtag['viewperm'] && $mtag['grade'] < -1)?0:1;

		$mtag['allowinvite'] = $mtag['grade']>=0?1:0;
		if($mtag['joinperm'] && $mtag['grade'] < 8) {
			$mtag['allowinvite'] = 0;
		}

		if($mtag['close']) {
			$mtag['allowpost'] = $mtag['allowthread'] = 0;
		}


		return $mtag;
	}

	//取数组中的随机个
	function sarray_rand($arr, $num=1) {
		$r_values = array();
		if($arr && count($arr) > $num) {
			if($num > 1) {
				$r_keys = array_rand($arr, $num);
				foreach ($r_keys as $key) {
					$r_values[$key] = $arr[$key];
				}
			} else {
				$r_key = array_rand($arr, 1);
				$r_values[$r_key] = $arr[$r_key];
			}
		} else {
			$r_values = $arr;
		}
		return $r_values;
	}

	//获得用户唯一串
	function space_key($space, $appid=0) {
		global $_SCONFIG;
		return substr(md5($_SCONFIG['sitekey'].'|'.$space['uid'].(empty($appid)?'':'|'.$appid)), 8, 16);
	}

	//获得用户URL
	function space_domain($space) {
		global $_SCONFIG;

		if($space['domain'] && $_SCONFIG['allowdomain'] && $_SCONFIG['domainroot']) {
			$space['domainurl'] = 'http://'.$space['domain'].'.'.$_SCONFIG['domainroot'];
		} else {
			if($_SCONFIG['allowrewrite']) {
				$space['domainurl'] = getsiteurl().$space[uid];
			} else {
				$space['domainurl'] = getsiteurl()."?$space[uid]";
			}
		}
		return $space['domainurl'];
	}

	//产生form防伪码
	function formhash() {
		global $_SGLOBAL, $_SCONFIG;

		if(empty($_SGLOBAL['formhash'])) {
			$hashadd = defined('IN_ADMINCP') ? 'Only For UCenter Home AdminCP' : '';
			$_SGLOBAL['formhash'] = substr(md5(substr($_SGLOBAL['timestamp'], 0, -7).'|'.$_SGLOBAL['supe_uid'].'|'.md5($_SCONFIG['sitekey']).'|'.$hashadd), 8, 8);
		}
		return $_SGLOBAL['formhash'];
	}

	//检查邮箱是否有效
	function isemail($email) {
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}

	//验证提问
	function question() {
		global $_SGLOBAL;

		include_once(S_ROOT.'./data/data_spam.php');
		if($_SGLOBAL['spam']['question']) {
			$count = count($_SGLOBAL['spam']['question']);
			$key = $count>1?mt_rand(0, $count-1):0;
			ssetcookie('seccode', $key);
			echo $_SGLOBAL['spam']['question'][$key];
		}
	}

	//输出MYOP升级信息脚本
	function my_checkupdate() {
		global $_SGLOBAL, $_SCONFIG;
		if($_SCONFIG['my_status'] && empty($_SCONFIG['my_closecheckupdate']) && checkperm('admin')) {
			$sid = $_SCONFIG['my_siteid'];
			$ts = $_SGLOBAL['timestamp'];
			$key = md5($sid.$ts.$_SCONFIG['my_sitekey']);
			echo '<script type="text/javascript" src="http://notice.uchome.manyou.com/notice?sId='.$sid.'&ts='.$ts.'&key='.$key.'" charset="UTF-8"></script>';
		}
	}

	//获得用户组图标
	function g_icon($gid) {
		global $_SGLOBAL;
		include_once(S_ROOT.'./data/data_usergroup.php');
		if(empty($_SGLOBAL['grouptitle'][$gid]['icon'])) {
			echo '';
		} else {
			echo ' <img src="'.$_SGLOBAL['grouptitle'][$gid]['icon'].'" align="absmiddle"> ';
		}
	}

	//获得用户颜色
	function g_color($gid) {
		global $_SGLOBAL;
		include_once(S_ROOT.'./data/data_usergroup.php');
		if(empty($_SGLOBAL['grouptitle'][$gid]['color'])) {
			echo '';
		} else {
			echo ' style="color:'.$_SGLOBAL['grouptitle'][$gid]['color'].';"';
		}
	}

	//检查是否操作创始人
	function ckfounder($uid) {
		global $_SC;

		$founders = empty($_SC['founder'])?array():explode(',', $_SC['founder']);
		if($uid && $founders) {
			return in_array($uid, $founders);
		} else {
			return false;
		}
	}

	//获取目录
	function sreaddir($dir, $extarr=array()) {
		$dirs = array();
		if($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(!empty($extarr) && is_array($extarr)) {
					if(in_array(strtolower(fileext($file)), $extarr)) {
						$dirs[] = $file;
					}
				} else if($file != '.' && $file != '..') {
					$dirs[] = $file;
				}
			}
			closedir($dh);
		}
		return $dirs;
	}

	//获取指定动作能获得多少积分
	function getreward($action, $update=1, $uid=0, $needle='', $setcookie = 1) {
		global $_SGLOBAL, $_SCOOKIE;

		$credit = 0;
		$reward = array(
			'credit' => 0,
			'experience' => 0
		);
		$creditlog = array();
		@include_once(S_ROOT.'./data/data_creditrule.php');
		$rule = $_SGLOBAL['creditrule'][$action];

		if($rule['credit'] || $rule['experience']) {
			$uid = $uid ? intval($uid) : $_SGLOBAL['supe_uid'];
			if($rule['rewardtype']) {
				//增加积分
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditlog')." WHERE uid='$uid' AND rid='$rule[rid]'");
				$creditlog = $_SGLOBAL['db']->fetch_array($query);

				if(empty($creditlog)) {
					$reward['credit'] = $rule['credit'];
					$reward['experience'] = $rule['experience'];
					$setarr = array(
						'uid' => $uid,
						'rid' => $rule['rid'],
						'total' => 1,
						'cyclenum' => 1,
						'credit' => $rule['credit'],
						'experience' => $rule['experience'],
						'dateline' => $_SGLOBAL['timestamp']
					);
					//判断是否需要去重
					if($rule['norepeat']) {
						if($rule['norepeat'] == 1) {
							$setarr['info'] = $needle;
						} elseif($rule['norepeat'] == 2) {
							$setarr['user'] = $needle;
						} elseif($rule['norepeat'] == 3) {
							$setarr['app'] = $needle;
						}
					}

					if(in_array($rule['cycletype'], array(2,3))) {
						$setarr['starttime'] = $_SGLOBAL['timestamp'];
					}
					$clid = inserttable('creditlog', $setarr, 1);
				} else {
					$newcycle = false;
					$setarr = array();
					$clid = $creditlog['clid'];
					switch($rule['cycletype']) {
					case 0:		//一次性奖励
						break;
					case 1:		//每天限次数
						case 4:		//不限周期
							$sql = 'cyclenum+1';
							if($rule['cycletype'] == 1) {
								$today = sstrtotime(sgmdate('Y-m-d'));
								//判断是否为昨天
								if($creditlog['dateline'] < $today && $rule['rewardnum']) {
									$creditlog['cyclenum'] =  0;
									$sql = 1;
									$newcycle = true;
								}
							}
							if(empty($rule['rewardnum']) || $creditlog['cyclenum'] < $rule['rewardnum']) {
								//验证是否为需要去重操作
								if($rule['norepeat']) {
									$repeat = checkcheating($creditlog, $needle, $rule['norepeat']);
									if($repeat && !$newcycle) {
										return $reward;
									}
								}
								$reward['credit'] = $rule['credit'];
								$reward['experience'] = $rule['experience'];
								//更新次数
								$setarr = array(
									'cyclenum' => "cyclenum=$sql",
									'total' => 'total=total+1',
									'dateline' => "dateline='$_SGLOBAL[timestamp]'",
									'credit' => "credit='$reward[credit]'",
									'experience' => "experience='$reward[experience]'",
								);
							}
							break;

						case 2:		//整点
							case 3:		//间隔分钟
								$nextcycle = 0;
								if($creditlog['starttime']) {
									if($rule['cycletype'] == 2) {
										//上一次执行时间
										$start = sstrtotime(sgmdate('Y-m-d H:00:00', $creditlog['starttime']));
										$nextcycle = $start+$rule['cycletime']*3600;
									} else {
										$nextcycle = $creditlog['starttime']+$rule['cycletime']*60;
									}
								}
								if($_SGLOBAL['timestamp'] <= $nextcycle && $creditlog['cyclenum'] < $rule['rewardnum']) {
									//验证是否为需要去重操作
									if($rule['norepeat']) {
										$repeat = checkcheating($creditlog, $needle, $rule['norepeat']);
										if($repeat && !$newcycle) {
											return $reward;
										}
									}
									$reward['experience'] = $rule['experience'];
									$reward['credit'] = $rule['credit'];

									$setarr = array(
										'cyclenum' => "cyclenum=cyclenum+1",
										'total' => 'total=total+1',
										'dateline' => "dateline='$_SGLOBAL[timestamp]'",
										'credit' => "credit='$reward[credit]'",
										'experience' => "experience='$reward[experience]'",
									);
								} elseif($_SGLOBAL['timestamp'] >= $nextcycle) {
									$newcycle = true;
									$reward['experience'] = $rule['experience'];
									$reward['credit'] = $rule['credit'];

									$setarr = array(
										'cyclenum' => "cyclenum=1",
										'total' => 'total=total+1',
										'dateline' => "dateline='$_SGLOBAL[timestamp]'",
										'credit' => "credit='$reward[credit]'",
										'starttime' => "starttime='$_SGLOBAL[timestamp]'",
										'experience' => "experience='$reward[experience]'",
									);
								}
								break;
					}

					//记录操作历史
					if($rule['norepeat'] && $needle) {
						switch($rule['norepeat']) {
						case 0:
							break;
						case 1:		//信息去重
							$info = empty($creditlog['info'])||$newcycle ? $needle : $creditlog['info'].','.$needle;
							$setarr['info'] = "`info`='$info'";
							break;
						case 2:		//用户去重
							$user = empty($creditlog['user'])||$newcycle ? $needle : $creditlog['user'].','.$needle;
							$setarr['user'] = "`user`='$user'";
							break;
						case 3:		//应用去重
							$app = empty($creditlog['app'])||$newcycle ? $needle : $creditlog['app'].','.$needle;
							$setarr['app'] = "`app`='$app'";
							break;
						}
					}
					if($setarr) {
						$_SGLOBAL['db']->query("UPDATE ".tname('creditlog')." SET ".implode(',', $setarr)." WHERE clid='$creditlog[clid]'");
					}

				}
				if($setcookie && $uid = $_SGLOBAL['supe_uid']) {
					//其中有新值时才重写cookie值
					if($reward['credit'] || $reward['experience']) {
						$logstr = $action.','.$clid;
						ssetcookie('reward_log', $logstr);
						$_SCOOKIE['reward_log'] = $logstr;
					}
				}
			} else {
				//扣除积分
				$reward['credit'] = "-$rule[credit]";
				$reward['experience'] = "-$rule[experience]";
			}
			if($update && ($reward['credit'] || $reward['experience'])) {
				$setarr = array();
				if($reward['credit']) {
					$setarr['credit'] = "credit=credit+$reward[credit]";
				}
				if($reward['experience']) {
					$setarr['experience'] = "experience=experience+$reward[experience]";
				}
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$uid'");
			}
		}
		return array('credit'=>abs($reward['credit']), 'experience' => abs($reward['experience']));
	}

	//防积分重复奖励同个人或同信息
	function checkcheating($creditlog, $needle, $norepeat) {

		$repeat = false;
		switch($norepeat) {
		case 0:
			break;
		case 1:		//信息去重
			$infoarr = explode(',', $creditlog['info']);
			if(in_array($needle, $infoarr)) {
				$repeat = true;
			}
			break;
		case 2:		//用户去重
			$userarr = explode(',', $creditlog['user']);
			if(in_array($needle, $userarr)) {
				$repeat = true;
			}
			break;
		case 3:		//应用去重
			$apparr = explode(',', $creditlog['app']);
			if(in_array($needle, $apparr)) {
				$repeat = true;
			}
			break;
		}

		return $repeat;
	}

	//获得热点
	function topic_get($topicid) {
		global $_SGLOBAL;
		$topic = array();
		if($topicid) {
			$typearr = array('blog','pic','thread','poll','event','share');
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." WHERE topicid='$topicid'");
			if($topic = $_SGLOBAL['db']->fetch_array($query)) {
				$topic['pic'] = $topic['pic']?pic_get($topic['pic'], $topic['thumb'], $topic['remote'], 0):'';
				$topic['joingid'] = empty($topic['joingid'])?array():explode(',', $topic['joingid']);
				$topic['jointype'] = empty($topic['jointype'])? $typearr:explode(',', $topic['jointype']);
				$topic['lastpost'] = sgmdate('Y-m-d H:i', $topic['lastpost']);
				$topic['dateline'] = sgmdate('Y-m-d H:i', $topic['dateline']);
				$topic['allowjoin'] = $topic['endtime'] && $_SGLOBAL['timestamp']>$topic['endtime']?0:1;
				$topic['endtime'] = $topic['endtime']?sgmdate('Y-m-d H:i', $topic['endtime']):'';

				include_once(S_ROOT.'./source/function_bbcode.php');
				$topic['message'] = bbcode($topic['message'], 1);
				$topic['joinurl'] = '';
				foreach ($typearr as $value) {
					if(in_array($value, $topic['jointype'])) {
						if($value == 'pic') $value = 'upload';
						$topic['joinurl'] = "cp.php?ac=$value&topicid=$topicid";
						break;
					}
				}
			}
		}
		return $topic;
	}

	//自定义分页
	function mob_perpage($perpage) {
		global $_SGLOBAL;

		$newperpage = isset($_GET['perpage'])?intval($_GET['perpage']):0;
		if($_SGLOBAL['mobile'] && $newperpage>0 && $newperpage<500) {
			$perpage = $newperpage;
		}
		return $perpage;
	}

	//检查用户的特殊身份
	function ckspacelog() {
		global $_SGLOBAL;

		if(empty($_SGLOBAL['supe_uid'])) return false;
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$_SGLOBAL[supe_uid]'");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($value['expiration'] && $value['expiration'] <= $_SGLOBAL['timestamp']) {//到期
				$_SGLOBAL['db']->query("DELETE FROM ".tname('spacelog')." WHERE uid='$_SGLOBAL[supe_uid]'");
			}
			$expiration = sgmdate('Y-m-d H:i', $value['expiration']);
			showmessage('no_authority_expiration'.($value['expiration']?'_date':''), '', 1, array($expiration));
		}
	}

	//取得字符串的长度
	function dstrlen($str) {
		if(strtolower(UC_CHARSET) != 'utf-8') {
			return strlen($str);
		}
		$count = 0;
		for($i = 0; $i < strlen($str); $i++){
			$value = ord($str[$i]);
			if($value > 127) {
				$count++;
				if($value >= 192 && $value <= 223) $i++;
				elseif($value >= 224 && $value <= 239) $i = $i + 2;
				elseif($value >= 240 && $value <= 247) $i = $i + 3;
			}
			$count++;
		}
		return $count;
	}

	//验证学号/职工号
	function verifycollegeid($collegeid, $password){
		//echo 'in verify';
		if(empty($collegeid)){//学号/职工号为空
			return -1;
		}
		if(empty($password)){//密码为空
			return -2;
		}
		header("content-type:text/html;charset=utf-8");
		$client = new SoapClient("http://202.112.132.233/services/AccountService?wsdl");
		//var_dump($client->__getFunctions());
		//print("<br/>");
		//var_dump($client->__getTypes());
		//print("<br/>");
		$params = array('in0' => '0001', 'in1' => $collegeid, 'in2' => $password);
		$ret = $client->__soapCall('getAccountService', array('parameters' => $params));
		//var_dump($ret);
		//exit();
		return $ret;
	}

	function getaliasemail($defaultemail){
		$ret = '';
		if(!empty($defaultemail)){
			//根据邮箱取得学生/教工号的别名邮箱
			list($number, $domain) = explode("@", $defaultemail);
			$alias = file_get_contents("http://mail.buaa.edu.cn/getalias.php?uid=".$number."&domain=".$domain);
			$alias = trim($alias);
			if(!empty($alias)){
				$a_array = array();
				$a_array =  split("[ \t\r\n]+",$alias);
				$alias = $a_array[0];
				$alias = trim($alias);
				$domain = trim($domain);
				$ret = $alias."@".$domain;
			}
		}
		return $ret;
	}


	function getcommonfriendarray($uid) {
		$list = array();
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("SELECT fuid, fusername FROM ".tname('friend')." WHERE uid=".$uid." AND status=1 AND fusername != '' LIMIT 0,200");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[$value['fuid']] = $value['fusername'];
		}
		return $list;
	}


/*function getcommonfriendcount($recommenduid,$recommendeduid) {


	if(empty($recommenduid)&&empty($recommendeduid))
	{
		echo "error";
		return null;
	} else {


		$recommendlist = array();
		$recommendedlist = array();

		$recommendlist = getcommonfriendarray($recommenduid);
		$recommendedlist = getcommonfriendarray($recommendeduid);
		$countarray = array_intersect_key($recommendlist,$recommendedlist);
		$count = count($countarray);
		if(empty($count)) {
			$count = 0;
		}
		return $count;
	}
}*/


	function getcommonfriend($recommenduid,$recommendeduid) {


		if(empty($recommenduid)&&empty($recommendeduid))
		{
			echo "error";
			return null;
		} else {


			$recommendlist = array();
			$recommendedlist = array();

			$recommendlist = getcommonfriendarray($recommenduid);
			$recommendedlist = getcommonfriendarray($recommendeduid);
			$commonfriendarray = array_intersect_key($recommendlist,$recommendedlist);
			//var_dump($commonfriendarray);
			return $commonfriendarray;
		}
	}

	//getcommfriendlist is to get the common friends between two uid, $type=0, get the array, $type=1, get the count.
	// by xuxing@ihome 2013-4-27.
	function getcommonfriendlist($uid,$touid,$type=0){
		$uidfriend = array();
		$touidfriend = array();
		$cofriend = array();

		if($uid==$space['uid']){
			$uidfriend = empty($space['friend'])?array():explode(',',$space['friend']);
		}else{
			$friendlist = getuidfriend($uid);
			$uidfriend = empty($friendlist)?array():explode(',',$friendlist);
		}
		if($touid==$space['uid']){
			$touidfriend = empty($space['friend'])?array():explode(',',$space['friend']);
		}else{
			$friendlist = getuidfriend($touid);
			$touidfriend = empty($friendlist)?array():explode(',',$friendlist);
		}
		if($uidfriend && $touidfriend) {
			$cofriend = array_intersect($uidfriend, $touidfriend);
		}
		if($type==0){
			return $cofriend;

		}else{
			return count($cofriend);
		}
	}

	//getuidfriend() is to get the friendlist of the uid. by xuxing@ihome 2013-4-27.
	function getuidfriend($uid){
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("SELECT friend  FROM ".tname('spacefield')." WHERE uid='".$uid."'");	
		$value = $_SGLOBAL['db']->fetch_array($query);
		return $value['friend'];
	}


	//如果手机号为对的，返回正确的手机号，否则返回空值！
	function ismobile($mobile)
	{
		return (strlen($mobile) == 11 && (preg_match("/^13\d{9}$/", $mobile) || preg_match("/^15\d{9}$/", $mobile) || preg_match("/^18\d{9}$/", $mobile)))?$mobile:'';
	}

	function auto_join($uid,$tagname , $db) {

		$tagid=sactag ($tagname,$db);
		jointag ( $uid , $tagid ,$db);
		return $tagid;
	}

	function joinArea($uid,$tagname , $db) {

		$tagid=tagArea ($tagname,$db);
		jointag ( $uid , $tagid ,$db);
	}

	function joinGrade($uid,$tagname , $db) {

		$tagid=tagGrade ($tagname,$db);
		jointag ( $uid , $tagid ,$db);
	}

	//将uid的用户加入到tagid的群组中
	function jointag ( $uid , $tagid , $db) {	
		sleep(2);
		$query =$db->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$tagid'"); 	
		$rs=($db->fetch_array($query));

		//如果有该群组
		if($rs) { 
			//查询是否已加入
			$query = $db->query("SELECT * FROM " .tname('tagspace'). " WHERE tagid='$tagid' AND uid='$uid'");
			//echo $tagid.$uid;
			$f=$db->fetch_array($query);		
			//未加入做如下处理
			if(!$f){
				$sql1 = "SELECT * FROM " .tname('member'). " WHERE  uid='$uid'";
				$query = $db->query($sql1);
				$useinfo=$db->fetch_array($query);
				$username=$useinfo['username'];
				$setarr=array('membernum'=>$rs['membernum']+1);//更新群组人数
				updatetable('mtag',$setarr,array('tagid'=>$tagid));
				unset($setarr);

				if($rs['membernum'] >= 1) {
					$gd = 0; 
				}else {
					$gd = 9;
				}
				$setarr = array( 'tagid' => $tagid, 'uid' => $uid, 'username' => $username, 'grade' => $gd );
				$tagspaceid=inserttable('tagspace',$setarr,1); //加入群组

			}else{
				if($f['grade'] != 9 && $f['grade'] != 0){
					$s=array('grade' => 0);
					updatetable('tagspace',$s,array('tagid'=>$tagid , 'uid' =>$uid));
				}
			}//if-else
		}//if
	}//function

	//已知群组名，查找群组，如果不存在，建立新群组，返回群组号
	function sactag ($mtagname ,$db) {

		$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
		$r=($db->fetch_array($query));
		if ($r) {
			return $r['tagid'];
		}else{
			$announcement = sprintf("欢迎加入%s，大家常联系哦！",$mtagname);
			if (strpos($mtagname, "大班")) {
				$setarr = array(  'tagname' => $mtagname, 'fieldid' => 2, 'announcement' => $announcement ,'joinperm' => 1 , 'viewperm' => 1 ,'threadperm' => 0 , 'postperm' => 0);
			} else {
				$setarr = array(  'tagname' => $mtagname, 'fieldid' => 4, 'announcement' => $announcement ,'joinperm' => 1 , 'viewperm' => 1 ,'threadperm' => 0 , 'postperm' => 0);
			}
			$tagspaceid=inserttable('mtag',$setarr,1);
			$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
			$r=($db->fetch_array($query));
			if ($r) {
				$tagid = $r['tagid'];
			}else{
				$tagid = -1;
			}
			return $tagid;
		}
	}

	/*用入学年份跟学院找群*/
	function tagGrade4 ($school, $db) {
		$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='".$school."'");
		$r=($db->fetch_array($query));
		if ($r) {
			return $r['tagid'];
		}else{
			$mtagname = $school;
			$announcement = sprintf("欢迎加入%s，大家常联系哦！",$mtagname);
			$setarr = array(  
				'tagname' => $mtagname, 
				'fieldid' => 9, 
				'announcement' => $announcement ,
				'joinperm' => 1 , 
				'viewperm' => 1 ,
				'threadperm' => 0 , 
				'postperm' => 0
				);
			$tagspaceid=inserttable('mtag',$setarr,1);
			$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
			$r=($db->fetch_array($query));
			if ($r) {
				$tagid = $r['tagid'];
			}else{
				$tagid = -1;
			}
			return $tagid;
		}
	}
	function tagGrade3 ($startyear, $academy ,$db) {
		$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE startyear='$startyear' AND academy='$academy'");
		$r=($db->fetch_array($query));
		if ($r) {
			return $r['tagid'];
		}else{
			$mtagname = $academy.$startyear."级";
			$announcement = sprintf("欢迎加入%s，大家常联系哦！",$mtagname);
			$setarr = array(  
				'tagname' => $mtagname, 
				'fieldid' => 8, 
				'announcement' => $announcement ,
				'joinperm' => 1 , 
				'viewperm' => 1 ,
				'threadperm' => 0 , 
				'postperm' => 0,
				'startyear'=>$startyear,
				'academy'=>$academy
				);
			$tagspaceid=inserttable('mtag',$setarr,1);
			$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
			$r=($db->fetch_array($query));
			if ($r) {
				$tagid = $r['tagid'];
			}else{
				$tagid = -1;
			}
			return $tagid;
		}
	}
	//国外校友群组
	function tagGroupOverseas($uid,$school)	{
		global $_SGLOBAL,$_SCONFIG;

		$tagid = tagGrade4($school,$_SGLOBAL['db']);

		jointag($uid,$tagid,$_SGLOBAL['db']);
	}

	function isAsst($uid) {
		global $_SGLOBAL,$_SCONFIG;
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('asst')." WHERE uid=$uid AND state=1 AND passed=1");
		if ($_SGLOBAL['db']->fetch_array($query)) {
			return TRUE;
		}
		return FALSE;
	}

	function getGroupAsst($tagid) {
		global $_SGLOBAL,$_SCONFIG;
		$arr = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM " .tname('tagspace'). " WHERE tagid='$tagid' AND grade>7");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if (isAsst($value['uid'])) {
				$arr[] = $value['uid'];
			}
		}
		return $arr;
	}

	function tagGroupAsst($uid, $startyear, $academy) {
		global $_SGLOBAL,$_SCONFIG;
		$arr = array(
				'材料科学与工程学院' => '01',
				'电子信息工程学院' => '02',
				'自动化科学与电气工程学院' => '03',
				'能源与动力工程学院' => '04',
				'航空科学与工程学院' => '05',
				'计算机学院' => '06',
				'机械工程及自动化学院' => '07',
				'经济管理学院' => '08', 
				'数学与系统科学学院' => '09',
				'生物医学工程系' => '10',
				'人文社会科学学院' => '11',
				'外国语学院' => '12',
				'交通科学与工程学院' => '13',
				'可靠性与系统工程学院' => '14',
				'宇航学院' => '15',
				'飞行学院' => '16',
				'仪器科学与光电工程学院' => '17',
				'物理科学与核能工程学院' => '19',
				'法学院' => '20',
				'软件学院' => '21',
				'继续教育学院' => '22',
				'高等工程学院' => '23',
				'中法工程师学院' => '24',
				'国际学院' => '25',
				'新媒体艺术与设计学院' => '26',
				'化学与环境学院' => '27'
			);
		$tagname = $startyear.'年'.substr($startyear, 2).$arr[$academy].'大班';
		$tagid=sactag($tagname, $_SGLOBAL['db']);
		if ($tagid != -1) {
			jointag($uid,$tagid,$_SGLOBAL['db']);
			$query = $_SGLOBAL['db']->query("SELECT * FROM " .tname('tagspace'). " WHERE tagid='$tagid' AND uid='$uid'");
			if ($value = $_SGLOBAL['db']->fetch_array($query)) {
				if (!$value['grade']) {
					$_SGLOBAL['db']->query("UPDATE " .tname('tagspace'). " SET grade=8 WHERE tagid='$tagid' AND uid='$uid'");
				}
			}
		}
		return $tagid;
	}
	//已知群组名，查找群组，如果不存在，建立新群组，返回群组号//群组为区域群组,
	function tagArea ($mtagname ,$db) {

		$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
		$r=($db->fetch_array($query));
		if ($r) {
			return $r['tagid'];
		}else{
			$announcement = sprintf("欢迎加入%s，大家常联系哦！",$mtagname);
			$setarr = array(  'tagname' => $mtagname, 'fieldid' => 8, 'announcement' => $announcement ,'joinperm' => 1 , 'viewperm' => 1 ,'threadperm' => 0 , 'postperm' => 0);
			$tagspaceid=inserttable('mtag',$setarr,1);
			$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
			$r=($db->fetch_array($query));
			if ($r) {
				$tagid = $r['tagid'];
			}else{
				$tagid = -1;
			}
			return $tagid;
		}
	}

	//已知群组名，查找群组，如果不存在，建立新群组，返回群组号//群组为同年级群组--应该增加灵活性哎哎哎,但是现在太急了
	function tagGrade ($mtagname ,$db) {

		$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
		$r=($db->fetch_array($query));
		if ($r) {
			return $r['tagid'];
		}else{
			$announcement = sprintf("欢迎加入%s，大家常联系哦！",$mtagname);
			$setarr = array(  'tagname' => $mtagname, 'fieldid' => 7, 'announcement' => $announcement ,'joinperm' => 1 , 'viewperm' => 1 ,'threadperm' => 0 , 'postperm' => 0);
			$tagspaceid=inserttable('mtag',$setarr,1);
			
			$query = $db->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$mtagname'");
			$r=($db->fetch_array($query));
			if ($r) {
				$tagid = $r['tagid'];
			}else{
				$tagid = -1;
			}
			return $tagid;
		}
	}
	function note_no_mtag($uid)	{
		global $_SGLOBAL, $_SCONFIG;
		$data = date('Ymd H:i:s',time());
		$arr = array(
			"uid" => $uid, 
			"apply_date" => $data
		);
		inserttable("no_mtag_register",$arr);

		$note = cplang('note_no_mtag', array("admincp.php?ac=mtaginvite"));;
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." where groupid=1");
		while ($value = $_SGLOBAL['db']->fetch_array($query)){	
			notification_add($value['uid'], 'systemnote', $note);
		}
	}
	
	//检测用户已发送短信数量
	function checksendsms()
	{
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("SELECT COUNT(*) from ".tname("sms")." WHERE uid='".$_SGLOBAL['supe_uid']."' AND dateline>'".$_SGLOBAL['timestamp']."'-86400");
		$end = $_SGLOBAL['db']->result($query, 0);
		return $end;
	}


	/*check the collegeid, if skipmailcheck=0, the process will check whether the defaultemail is existed, else skip the check.*/
	function check_collegeid($collegeid, $skipmailcheck=0){
		global $_SGLOBAL;
		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		$len = dstrlen($collegeid);
		if($len < 3 || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $collegeid))
		{
			return COLLEGEID_CHECK_FAILED;
		}

		$sql = $_SGLOBAL['db']->query("SELECT * FROM ".tname('baseprofile')." WHERE collegeid='$collegeid'");
		$data = $_SGLOBAL['db']->fetch_array($sql);

		if(empty($data)){
			return 	COLLEGEID_NOT_LEGITIMATE;	
		}else{
			//if(empty($data['identifier']) && empty($data['defaultemail'])){
			if ($skipmailcheck == 0){
				if(empty($data['identifier']) && empty($data['defaultemail'])){
					showmessage('identifier_uncertain', '', 3);
				}
			}
			if($data['isactive'] == '1'){
				return COLLEGEID_ACTIVATED;
			}else{
				if ($skipmailcheck == 0){
					if(empty($data['defaultemail'])){
						return EMAIL_NOT_EXIST;
					}else{
						list($number, $domain) = explode("@", $data['defaultemail']);
						$quotaenough = file_get_contents("http://mail.buaa.edu.cn/quota.php?uid=".$number."&domain=".$domain);
						$quotaenough = trim($quotaenough);
						if($quotaenough == 0){
							return MAIL_NOT_ADEQUENT;
						}else{
							return 1;
						}
					}
				}	
			}
		}
	}

	//调用验证码短信接口发送短信，$mobile为手机号，$content为待发送的内容, $sendtime定时时间；

	function verifycodesms($mobile, $content, $sendtime = ''){
		$smsuid = 'TCLKJ0003';
		$smspassword = '215215';
		$content = iconv('UTF-8', 'GBK//IGNORE', $content);
		$content = urlencode($content);
		$sendurl = "http://inolink.com/WS/Send.aspx?CorpID={$smsuid}&Pwd={$smspassword}&Mobile={$mobile}&Content={$content}&Cell=&SendTime={$sendtime}";

		$result = file_get_contents($sendurl);
		return $result;
	}

	function sendsms($mobile, $title, $content) {
		$command = '<?xml version="1.0" encoding="utf-8"?>
			<Root>
			<Function>SendMessage</Function>
			<LoginName>ihome</LoginName>
			<Password>111111</Password>
			<SignedData></SignedData>
			<RequestData>
			<Title>'.$title.'</Title>
			<MessageType>2</MessageType>
			<ReceiveNum>'.$mobile.'</ReceiveNum>
			<Content><![CDATA['.$content.']]></Content>
			</RequestData>
			</Root>';
		$objSoapClient = new SoapClient("http://211.71.14.165/api.asmx?WSDL");
		$param = array("Command"=>$command);
		$result = $objSoapClient->Execute($param);

		if ($result) {
			$xml = simplexml_load_string($result->ExecuteResult);
			$ret_code = $xml->Summary->Code;
			if ($ret_code == '0000') {
				return TRUE;
	}
	}
	return FALSE;
	}

    //中文转拼音 start
    function Pinyin($_String, $_Code='gb2312',$isInitial=false)
    {
        global $_SGLOBAL;
        if(!$_SGLOBAL['pinyinkey'])
        {
            $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
                "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
                "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
                "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
                "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
                "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
                "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
                "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
                "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
                "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
                "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
                "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
                "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
                "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
                "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
                "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
            $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
                "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
                "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
                "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
                "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
                "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
                "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
                "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
                "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
                "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
                "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
                "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
                "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
                "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
                "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
                "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
                "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
                "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
                "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
                "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
                "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
                "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
                "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
                "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
                "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
                "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
                "|-10270|-10262|-10260|-10256|-10254";
            $_TDataKey = explode('|', $_DataKey);
            $_TDataValue = explode('|', $_DataValue);
            $_SGLOBAL['pinyinkey'] = $_TDataKey;
            $_SGLOBAL['pinyinval'] = $_TDataValue;
            $_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
            arsort($_Data);
            reset($_Data);
            $_SGLOBAL['pinyindata'] = $_Data;
        }
        else
        {
            $_TDataKey = $_SGLOBAL['pinyinkey'];
            $_TDataValue = $_SGLOBAL['pinyinval'];
            $_Data = $_SGLOBAL['pinyindata'];
        }

        if($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);
        $_Res = '';
        for($i=0; $i<strlen($_String); $i++)
        {
            $_P = ord(substr($_String, $i, 1));
            if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
            $_Res .= _Pinyin($_P, $_Data,$isInitial);
        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }

	function _Pinyin($_Num, $_Data,$isInitial)
	{
		if ($_Num>0 && $_Num<160 ) return chr($_Num);
		elseif($_Num<-20319 || $_Num>-10247) return '';
		else {
			foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
				if($isInitial)
					$k=substr($k,0,1);//是否只显示首写
				return $k;

		}
	}

	function _U2_Utf8_Gb($_C)
	{
		$_String = '';
		if($_C < 0x80) $_String .= $_C;
		elseif($_C < 0x800)
		{
			$_String .= chr(0xC0 | $_C>>6);
			$_String .= chr(0x80 | $_C & 0x3F);
		}elseif($_C < 0x10000){
			$_String .= chr(0xE0 | $_C>>12);
			$_String .= chr(0x80 | $_C>>6 & 0x3F);
			$_String .= chr(0x80 | $_C & 0x3F);
		} elseif($_C < 0x200000) {
			$_String .= chr(0xF0 | $_C>>18);
			$_String .= chr(0x80 | $_C>>12 & 0x3F);
			$_String .= chr(0x80 | $_C>>6 & 0x3F);
			$_String .= chr(0x80 | $_C & 0x3F);
		}
		return iconv('UTF-8', 'GB2312', $_String);
	}

	function _Array_Combine($_Arr1, $_Arr2)
	{
		for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
		return $_Res;
	} 
	//中文转拼音 end

	//处理邮箱格式，输入邮箱地址，标识是否需要检查邮箱的唯一性，返回-1为格式不正确，-2为已存在该邮箱，0为格式正确
	//这个函数已经有了的
	function getCheckEmail($email, $isunique=true){
		if(!strpos($email,'@') || !strpos($email,'.')){
			return -1;
		}elseif(substr($email,0,1) == '@' || substr($email,0,1) == '.' || substr($email,0,1) == '$' || substr($email,0,1) == '^' || substr($email,-1,1) == '@' || substr($email,-1,1) == '.'){
			return -1;
		}

		if($isunique){
			if(getcount('spacefield', array('email'=>$email))){
				return -2;
			}
		}
		return 0;
	}

    //自动添加好友
    function autobefriends($userlines,$newuid,$username){
runlog("debug", "userlines:".print_r($userlines,true));
runlog("debug", "newuid:".print_r($newuid,true));
runlog("debug", "username".print_r($username,true));

        global $_SGLOBAL;
        $wheresql = "0";
        if($userlines){
            foreach($userlines as $value){
                if($value['usertype'] == '教师'||$value['usertype']=='5'||$value['usertype']=='4'){
                    if($value['academy']){
                        $wheresql .= " or (academy='".$value['academy']."' and (usertype='教师' or usertype in (4,5)) and isactive>0 and uid>0)";
                    }
                }else{
                    if($value['startyear'] && $value['class']){
                        $wheresql .= " or (startyear='".$value['startyear']."' and class='".$value['class']."' and isactive>0 and uid>0)";
                    }
                }
            }
        }
runlog("debug", "where:".$wheresql);
        $value = $fuids = $inserts = $flog = $friendslist = array();
        $query = $_SGLOBAL['db']->query("select a.uid,a.username from ".tname('member')." a, (SELECT distinct uid FROM ".tname('baseprofile')." WHERE $wheresql) b where a.uid=b.uid");
        while($value = $_SGLOBAL['db']->fetch_array($query)) {
            if($value['uid']==$newuid) continue;
            $fuids[] = $value['uid'];
            $inserts[] = "('$newuid','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
            $inserts[] = "('$value[uid]','$newuid','$username','1','$_SGLOBAL[timestamp]')";
            //添加好友变更记录
            $flog[] = "('$value[uid]','$newuid','add','$_SGLOBAL[timestamp]')";
        }

runlog("debug", "fuids:".print_r($fuids,true));
runlog("debug", "inserts:".print_r($inserts,true));
runlog("debug", "log:".print_r($flog, true));
        try
        {
            if($inserts){
                $_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',', $inserts));
                $_SGLOBAL['db']->query("REPLACE INTO ".tname('friendlog')." (uid,fuid,action,dateline) VALUES ".implode(',', $flog));
                friend_cache($newuid);
                foreach ($fuids as $fuid) {
                    friend_cache($fuid);
                }

            }
        }
        catch(Exception $e) 
        {
            runlog("activeerr", $e->getMessage());
        }
    }

	//获取用户基础信息，用于AJAX调用
	function getuserbaseinfo($key, $indextype='uid') {
		global $_SGLOBAL, $_SCONFIG, $_SN;

		$space = array();
		$query = $_SGLOBAL['db']->query("select s.uid,s.username,s.name, sf.marry,sf.birthprovince, sf.birthcity from ".tname('space')." s LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid WHERE s.{$indextype}='$key'");
		$space = $_SGLOBAL['db']->fetch_array($query);
		if($space) {
			$space['marry'] = $space['marry']=='1'?'<a href="cp.php?ac=friend&op=search&marry=1&searchmode=1">'.lang('unmarried').'</a>':($space['marry']=='2'?'<a href="cp.php?ac=friend&op=search&marry=2&searchmode=1">'.lang('married').'</a>':'');		
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type IN ('base', 'edu', 'work') order by startyear asc");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$v_friend = ckfriend($value['uid'], $value['friend']);
				if(!$v_friend) {
					if($space[$value['subtype']]) $space[$value['subtype']] = '';
				}else{
					if($value['type']=='work') $space['work'] = $value['title'].' '.$value['subtitle'];
					if($value['type']=='edu')  {
						$space['edu'] = $value['title'].' '.$value['subtitle'];
						$space['college'] = $value['subtitle'];
					}
				}
			}

		}

		return $space;
	}

	//加解密函数
	//2013-04-22
	function M_encode($content,$key) {
		$iv = '0123456789ABCDEF';
		$AESed =  strtoupper(bin2hex( mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$content,MCRYPT_MODE_ECB,$iv)));
		return $AESed ; 
	}
	function M_decode($AESed,$key) {
		$iv = '0123456789ABCDEF';
		$jiemi =trim( mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,hexToStr($AESed),MCRYPT_MODE_ECB,$iv));
		return $jiemi;
	}
	function hexToStr($hex)
	{
		$bin="";
		for($i=0; $i<strlen($hex)-1; $i+=2)
		{
			$bin.=chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $bin;
	}
	function getRandomChar($Length){
		$Conso=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$String="";
		srand ((double)microtime()*1000000);
		for($i=1; $i<=$Length; $i++)
		{
			$String.=$Conso[rand(0,25)];
		}
		return $String;
	}
	function getAESKey($type){
		switch($type){
			//密钥勿改~!!
		case 'Mobile':return '1E968607E47C5BB91E20CE9A5C4CCD81';break;
		case 'IDcard':return '1E968607E47C5BB91E20CE9A5C4CCD81';break;
		default :return '1E968607E47C5BB91E20CE9A5C4CCD81';break;
		}
	}
	//加解密函数

	function gotologin() {
		global $_SGLOBAL, $_SCONFIG;

		if(empty($_SGLOBAL['supe_uid'])) {
			//这个跳转问题
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			//exit($_SERVER['REQUEST_URI']);
			//把登录这块做好点
			showmessage('to_login', '../../do.php?ac='.$_SCONFIG['login_action'], 0);
		}
	}

	function mobilelogin() {
		global $_SGLOBAL, $_SCONFIG;
		if(empty($_SGLOBAL['supe_uid'])) {
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			//showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action'], 0);
			showmessage('to_login', 'waplogin.php', 0);
		}
	}


	//更新缓存中的powerlevel.php文件
	function updatePowerlevelFile(){
		global $_SGLOBAL;
		$PowerQuery = $_SGLOBAL['db']->query("SELECT * FROM ".tname('powerlevel'));
		while($PowerArray = $_SGLOBAL['db']->fetch_array($PowerQuery)){
			unset($PowerArray['pid']);
			$power[$PowerArray['dept_uid']] = $PowerArray;
			if($PowerArray['isdept']){//去掉多余信息,仅保留uid 和 name ,用于at功能用
				$powerjson = array(
					'department' => $PowerArray['department'],
					'dept_uid' => $PowerArray['dept_uid'],
					'namequery' => $PowerArray['department'].' '.Pinyin($PowerArray['department'],1).' '.$PowerArray['dept_uid'],
					'depduty' => $PowerArray['depduty']
				);
				$powerJsons[] = $powerjson;
			}
		}
		$string_start = "<?php\n";
		$string_name = '$_POWERINFO = ';
		$string_process = var_export($power, TRUE);
		$string_end = "\n?>";
		$string = $string_start.$string_name.$string_process.$string_end;
		//开始写入文件
		echo file_put_contents(S_ROOT.'./data/powerlevel/powerlevel.php', $string);
		$powerJsons = json_encode($powerJsons);
		echo file_put_contents(S_ROOT.'./data/powerlevel/powerlevel.json', $powerJsons);

	}
	//查找是否为部处
	function isDepartment($uid = 0 ,$isDept = 1){
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("select * from ".tname('powerlevel')." WHERE dept_uid='$uid'");
		if ($result = $_SGLOBAL['db']->fetch_array($query)) {
			if($isDept){
				if($result['isdept'] == 1)
					return $result;
				else
					return FALSE;
			}else{
				return $result;
			}
		} else {
			echo FALSE;
		}
	}
	function getBaseDepartmentID($dept_uid = 0){
		include S_ROOT.'./data/powerlevel/powerlevel.php';
		$dept_uids = '';
		if(array_key_exists($dept_uid ,$_POWERINFO)){
			foreach($_POWERINFO as $Department){
				if($Department['isdept'] == 1 && in_array($dept_uid ,explode(',' ,$Department['official'])))
					$dept_uids .= ','.$Department['dept_uid'];
			}
		}
		return $dept_uids;
	}
	function getOfficials($uid ,$officials = ''){
		$UserInfo = isDepartment($uid ,0);
		$officials .= $UserInfo['dept_uid'].',';
		$UpUsers_arr = explode("," ,$UserInfo['up_uid']);
		foreach($UpUsers_arr as $User){
			if($UserInfo['dept_uid'] == $User){
				return $officials;
			}	
			$officials = getOfficials($User ,$officials);
		}
		return $officials;
	}

	function mobile_login()
	{
		global $_SGLOBAL, $_SCONFIG;
		if(empty($_SGLOBAL['supe_uid']))
		{
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			showmessage('to_login', './../../sys/src/login.php', 0);
		}
	}

	//get the url of share. xuxing added 2013-7-1
	function get_shareurl($idtype, $id){
		$url = "cp.php?ac=share";
		switch($idtype){
		case 'doid':
			$url .= "&type=doing&id=$id";
			break;
		case 'blogid':
			$url .= "&type=blog&id=$id";
			break;
		case 'albumid':
			$url .= "&type=album&id=$id";
			break;
		case 'picid':
			$url .= "&type=pic&id=$id";
			break;
		case 'videoid':
			$url .= "&type=video&id=$id";
			break;
		case 'tid':
			$url .= "&type=thread&id=$id";
			break;
		case 'sid':
			$url .= "&type=share&id=$id";
			break;
		case 'pid':
			$url .= "&type=poll&id=$id";
			break;
		case 'eventid':
			$url .= "&type=event&id=$id";
			break;
		default:
			break;
		}
		return $url;
	}

	//get the number of comments. xuxing added 2013-7-5
	function get_commentnum($type,$id){
		global $_SGLOBAL;
		if($type=='doid'){
			$query = $_SGLOBAL['db']->query("select replynum as num from ".tname('doing')." WHERE doid='$id'");
		}else{
			$query = $_SGLOBAL['db']->query("select count(cid) as num from ".tname('comment')." WHERE idtype = '$type' and id='$id'");
		}
		$result = $_SGLOBAL['db']->fetch_array($query);
		if($result){ 
			return $result['num']; 
		}else{
			return 0;
		}
	}


	//取得CAS中的帐号
	function getCASUser(){
		include_once(S_ROOT.'./source/CAS.php');
		phpCAS::setDebug();
		$_cas_server_version=CAS_VERSION_2_0;
		$_hostname='ecampus.buaa.edu.cn';
		$_hostport=443;
		$_uri='cas';
		//initialize phpCAS
		phpCAS::client($_cas_server_version,$_hostname,$_hostport,$_uri);
		//no SSL validation for the CAS server
		phpCAS::setNoCasServerValidation();
		//force CAS authentication
		phpCAS::forceAuthentication();
		//showmessage("cas halt");
		if(isset($_REQUEST['logout']))
		{
			phpCAS::logout();
		}
		//返回学号或者教职工的教工号
		$username=phpCAS::getUser();
		return $username;
	}

	//二维数组指定键值排序
	function array_sort($arr,$keys,$type='desc'){ 
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array; 
	}

	//图片处理成正方形
	function resize_img($src,$des=null)//des默认路径是本身可省缺	{
	{	
		$imgpath=$src;
		if(file_exists($imgpath."_resize.jpg")) {
			return $imgpath."_resize.jpg";
		}

		$cnt=1;
		$img=getimagesize($imgpath);
		try {
			getimagesize($imgpath."_resize.jpg");
			if($img[2]==1)	{
				$image=@imagecreatefromgif($imgpath);
			}
			else if($img[2]==2)	{
				$image=@imagecreatefromjpeg($imgpath);
			}
			else if($img[2]==3)	{
				$image=@imagecreatefrompng($imgpath);
			}
			$width_src=$img[0];
			$height_src=$img[1];
			if ($height_src * 1.0 > 0.75 * $width_src) {
				$width_dst = $width_src;
				$height_dst = $width_dst * 3 / 4;
			} else {
				$height_dst = $height_src;
				$width_dst = $height_dst * 4 / 3;
			}
			$x_src = ($width_src - $width_dst)/2;
			$y_src = ($height_src - $height_dst)/2;
			$newim=imagecreatetruecolor($width_dst, $height_dst); 
			imagecopyresampled($newim, $image, 0, 0, $x_src, $y_src, 
				$width_dst, $height_dst, $width_dst, $height_dst);
			if(!$des)
				imagejpeg($newim, $imgpath."_resize.jpg", 100);
			else	
			{
				$flag=0;
				for($i=strlen($imgpath);$i>=0;$i--)	{
					if($imgpath[$i]=='/')	{
						$flag=$i;
						break;
					}
				}

				$name=substr($imgpath,$flag+1,strlen($imgpath));
				imagejpeg($newim, $des.$name."_resize.jpg",100);
			}
		}
		catch (Exception $e){
			echo $e;        
		}
		if(!$des)
			return $imgpath."_resize.jpg"; 
		else return $des.$name."_resize.jpg";
	}


	//图片处理成正方形
	function square_img($src,$des=null)//des默认路径是本身可省缺	{
	{	
		return $src;
		$imgpath=$src;
		if(file_exists($imgpath."_sqr.jpg")) {
			return $imgpath."_sqr.jpg";
		} 
		$cnt=1;
		$img=getimagesize($imgpath);
/*
		try {
			getimagesize($imgpath."_sqr.jpg");
			if($img[2]==1)	{
				$image=@imagecreatefromgif($imgpath);
			}
			else if($img[2]==2)	{
				$image=@imagecreatefromjpeg($imgpath);
			}
			else if($img[2]==3)	{
				$image=@imagecreatefrompng($imgpath);
			}
			$width_y=$img[0];
			$height_y=$img[1];
			$width=$height=min($width_y,$height_y);
			if($width_y>$height_y){//如果宽大于高
				$width_y_y=$height_y;
				$height_y_y=$height_y;
				$jq_x=($width_y-$height_y)/2;
				$jq_y=0;
			}else if($width_y<$height_y){//如果宽小于高
				$height_y_y=$width_y;
				$width_y_y=$width_y;
				$jq_x=0;
				$jq_y=($height_y-$width_y)/2;
			}else if($width_y=$height_y){//如果宽小于高
				$width_y_y=$width_y;
				$height_y_y=$height_y;
				$jq_x=0;
				$jq_y=0;
			}
			$newim=imagecreatetruecolor($width,$height); 
			imagecopyresampled($newim,$image,0,0,$jq_x,$jq_y,$width,$height,$width_y_y,$height_y_y);
			if(!$des)
				imagejpeg($newim, $imgpath."_sqr.jpg", 100);
			else	
			{
				$flag=0;
				for($i=strlen($imgpath);$i>=0;$i--)	{
					if($imgpath[$i]=='/')	{
						$flag=$i;
						break;
					}
				}

				$name=substr($imgpath,$flag+1,strlen($imgpath));
				imagejpeg($newim, $des.$name."_sqr.jpg",100);
			}
		}
		catch (Exception $e){
echo $e;        
		}
 */
		if(!$des)
			return $imgpath."_sqr.jpg"; 
		else return $des.$name."_sqr.jpg";
	}
	function getUsername($uid,$con) {
		$query = $con -> query("SELECT * FROM ".tname("space")." WHERE uid=".$uid);
		while($res = $con->fetch_array($query))	{
			if($res['name'])	{
				return $res['name'];
			}	
			else {
				return $res['username'];
			}
		}
	}
	function isComplainOrNot($doid,$con)	{
		$query = $con -> query("SELECT * FROM ".tname("complain")." WHERE doid=".$doid);
		if($res = $con->fetch_array($query))	{
			return true;
		}
		return false;
	}
	function complainReplyOrNot($doid,$con)	{
		$query = $con -> query("SELECT * FROM ".tname("complain")." WHERE doid=".$doid);
		if($res = $con->fetch_array($query))	{
			if($res['isreply'])	{
				return true;
			}
			return false;
		}
	}
	function isComplainOrNot_feed($feedid,$con)	{
		$sql = "SELECT * FROM ".tname("feed")." WHERE feedid=".$feedid;

		$query = $con -> query("SELECT * FROM ".tname("feed")." WHERE feedid=".$feedid);
		
		if ($value = $con -> fetch_array($query))	{
			
			if($value['idtype'] == "doid")	{
				if(isComplainOrNot($value['id'],$con))	{
					return true ;
				}
			}
		}
		return false ;
	}
	function poll_style($str) {/*
		$str=preg_replace('/\<br\>/i','',$str);
		$str=preg_replace('/\<input\>/i','<div><input>',$str);
		$str=preg_replace('/\<\/a\>/i','</a></div>',$str);
		$str=preg_replace('/\<a\>/i','<div><a>',$str);*/
		return $str;   
	}
	//获取标签
function getntags($uid, $dotype, $doid = 0){
		global $_SGLOBAL;
		$uids = explode(',',$uid);
		$output = '<ul class="ntags">';
		$whereid = $doid?" AND doid = '$doid'":"";
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ntag_user')." tu LEFT JOIN ".tname('ntags')." t ON tu.tagid = t.tagid WHERE uid IN ('$uid') AND dotype = '$dotype'" .$whereid.' LIMIT 10');
		$delico = '';
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if (in_array($_SGLOBAL['supe_uid'], $uids) || checkperm('admin')) $delico = '<a href="javascript:;" onClick="deltag(\''.$value['tuid'].'\')"><img src="image/tagdel.png" /></a>';
			$output .= '<li><span>'.$value['tagname'].$delico.'</span></li>';
		}
		if (in_array(strval($_SGLOBAL['supe_uid']), $uids) ) $output .= '<li><span><a onclick="ajaxmenu(event, this.id)" id="add_tag" href="cp.php?ac=addtag&op=menu&dt='.$dotype.'&did='.$doid.'&uid='.$uid.'">+添加新标签</a></span></li>';	
		$output .= '</ul>'; 
		return $output;
}
?>
