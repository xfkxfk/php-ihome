<?php
include_once('./common.php');

$nowtime = time() - 3 * 24 * 3600;

$log = Logger::getLogger("closecomplain");
$log->debug("close complain");

$complains = $_SGLOBAL['db']->query("select * from ".tname("complain")." use index(dateline) where status=1 and dateline < $nowtime");
$num = 0;
while ($result = $_SGLOBAL['db']->fetch_array($complains)) {
    updatetable("complain", array("status"=>2), array("id"=>$result['id']));
    $oparr = array();
    $oparr['doid'] = $result['doid'];
    $oparr['uid'] = 0;
    $oparr['username'] = 'system';
    $oparr['message'] = '';
    $oparr['optype'] = 6;
    $oparr['dateline'] = $_SGLOBAL['timestamp'];
    $oparr['opvalue'] = 2;
    inserttable("complain_op", $oparr);
    $num += 1;
    $log->debug("close complain $result[doid]");
}
echo "一共处理了{$num}个诉求";
$log->debug("close $num complain");
?>
