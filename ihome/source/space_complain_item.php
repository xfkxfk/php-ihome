<?php

if (!defined('iBUAA')) {
    exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$query=$_SGLOBAL['db']->query("select * from ".tname('complain')." where doid = $doid");
$complain=$_SGLOBAL['db']->fetch_array($query);
$dv=null;
$doclist= array();
if ($complain) {
    $theurl = "space.php?do=complain_item&doid=$doid";
    $query=$_SGLOBAL['db']->query("select * from ".tname('doing')." where doid = $doid");
    $dv= $_SGLOBAL['db']->fetch_array($query);
    if ($dv) {
        if ($_SGLOBAL['supe_uid'] != $complain['uid']) {
            $space = getspace($complain['uid']);
        }
    }
    include_once(S_ROOT.'./source/class_tree.php');
    $tree = new tree();

    $query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('docomment')." USE INDEX(dateline) WHERE doid = $doid ORDER BY dateline");
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        realname_set($value['uid'], $value['username']);
        if (empty($value['upid'])) {
            $value['upid'] = "do$doid";
        }
        $tree->setNode($value['id'], $value['upid'], $value);
    }
    $values = $tree->getChilds("do$doid");
    foreach ($values as $key=>$id) {
        $one = $tree->getValue($id);
        $one['layer'] = $tree->getLayer($id) * 2 - 2;
        $one['style'] = "padding-left:{$one['layer']}em;";
        $doclist[$doid][] = $one;
    }
    $query = $_SGLOBAL['db']->query("select uid,name,username from ".tname('space')." where groupid=3");
    $deps = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        if (empty($value['name'])) $value['name'] = $value['username'];
        $deps[] = $value;
    }
    $query = $_SGLOBAL['db']->query("select * from ".tname('complain_op')." where doid=$doid");
    $complain_ops = array();
    while ($value = $_SGLOBAL['db']->fetch_array($query)) {
        $complain_ops[] = $value;
        realname_set($value['uid'], $value['username']);
    }
}

realname_get();
$_TPL['css'] = 'complain_item';
include_once template("space_complain_item");

?>



