<?php

if(!defined('iBUAA')) {
	exit('Access Denied');
}

$_SGLOBAL['cplang'] = array(

	'by' => '通过',
	'tab_space' => ' ',
	'feed_comment_space' => '{actor} 在 {touser} 的留言板留了言',
	'feed_comment_image' => '{actor} 评论了 {touser} 的图片',
	'feed_comment_video' => '{actor} 评论了 {touser} 上传的视频',
	'feed_comment_blog' => '{actor} 评论了 {touser} 的日志 {blog}',
	'feed_comment_arrangement' => '{actor} 评论了 {touser} 的校园日历 {arrangement}',
	'feed_comment_poll' => '{actor} 评论了 {touser} 的投票 {poll}',
	'feed_comment_event' => '{actor} 在 {touser} 组织的活动 {event} 中留言了',
	'feed_comment_share' => '{actor} 对 {touser} 分享的 {share} 发表了评论',
	'share' => '分享',
	'share_action' => '分享了',
	'note_wall' => '在留言板上给你<a href="\\1" target="_blank">留言</a>',
	'note_wall_reply' => '回复了你的<a href="\\1" target="_blank">留言</a>',
	'note_pic_comment' => '评论了你的<a href="\\1" target="_blank">图片</a>',
	'note_video_comment' => '评论了你的<a href="\\1" target="_blank">视频</a>',
	'note_pic_comment_reply' => '回复了你的<a href="\\1" target="_blank">图片评论</a>',
	'note_video_comment_reply' => '回复了你的<a href="\\1" target="_blank">视频评论</a>',
	'note_blog_comment' => '评论了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'note_blog_comment_reply' => '回复了你的<a href="\\1" target="_blank">日志评论</a>',
	'note_arrangement_comment' => '评论了你的校园日历 <a href="\\1" target="_blank">\\2</a>',
	'note_arrangement_comment_reply' => '回复了你的<a href="\\1" target="_blank">校园日历评论</a>',
	'note_poll_comment' => '评论了你的投票 <a href="\\1" target="_blank">\\2</a>',
	'note_poll_comment_reply' => '回复了你的<a href="\\1" target="_blank">投票评论</a>',
	'note_share_comment' => '评论了你的 <a href="\\1" target="_blank">分享</a>',
	'note_share_comment_reply' => '回复了你的<a href="\\1" target="_blank">分享评论</a>',
	'note_event_comment' => '在你组织的活动里<a href="\\1" target="_blank">留言</a>了',
	'note_event_comment_reply' => '回复了你在活动中的<a href="\\1" target="_blank">留言</a>',
	'note_show_out' => '访问了你的主页后，你在竞价排名榜中最后一个积分也被消费掉了',
	'note_space_bar' => '把你设置为站点推荐用户了',
	'note_public_apply' => '<a href="\\1" target="_blank">有新的公共主页需要审批</a>',
	
	'note_click_blog' => '对你的日志 <a href="\\1" target="_blank">\\2</a> 做了表态',
	'note_click_thread' => '对你的话题 <a href="\\1" target="_blank">\\2</a> 做了表态',
	'note_click_pic' => '对你的 <a href="\\1" target="_blank">图片</a> 做了表态',

	'wall_pm_subject' => '您好，我给您留言了',
	'wall_pm_message' => '我在您的留言板给你留言了，[url=\\1]点击这里去留言板看看吧[/url]',
	'note_showcredit' => '赠送给您 \\1 个竞价积分，帮助提升在<a href="space.php?do=top" target="_blank">竞价排行榜</a>中的名次',
	'feed_showcredit' => '{actor} 赠送给 {fusername} 竞价积分 {credit} 个，帮助好友提升在<a href="space.php?do=top" target="_blank">竞价排行榜</a>中的名次',
	'feed_showcredit_self' => '{actor} 增加竞价积分 {credit} 个，提升自己在<a href="space.php?do=top" target="_blank">竞价排行榜</a>中的名次',
	'feed_doing_title' => '{actor}：{message}',
	'note_doing_reply' => '在<a href="\\1" target="_blank">一条记录</a>中对你进行了回复',
	'note_doing_at' => '在<a href="\\1" target="_blank">一条记录</a>中@了你',
	'note_share_at' => '在<a href="\\1" target="_blank">一条分享</a>中@了你',
	'note_doingcomment_at' => '在<a href="\\1" target="_blank">一条回复</a>中@了你',
	
	'note_complain_user_success' => '【温馨提示】您的<a href="\\1" target="_blank">诉求</a>发起成功!',
	'note_complain_user_failed' => '【温馨提示】您的诉求发起失败,已转成普通<a href="\\1" target="_blank">记录</a>!',
	'note_complain_credit_failed' => '【温馨提示】您的积分不够,诉求发起失败,已转成普通<a href="\\1" target="_blank">记录</a>!',
	
	'note_complain_buchu' => '【温馨提示】您有<a href="\\1" target="_blank">一条诉求</a>待处理,如不及时处理,此诉求将于\\2上报给负责人处',
	'note_complain_buchu1' => '【温馨提示】您有<a href="\\1" target="_blank">一条诉求</a>待处理,如不及时处理,此诉求将于\\2上报给处长处',
	'note_complain_buchu2' => '【温馨提示】您有<a href="\\1" target="_blank">一条诉求</a>在规定时间内未处理,已上报给主管副校长处,如不及时处理,此诉求将于\\2上报给校长处',
	'note_complain_chuzhang' => '【温馨提示】您单位有<a href="\\1" target="_blank">一条诉求</a>待处理，如不及时处理，此诉求将于\\2上报给主管副校长处',
	'note_complain_chuzhang2' => '【温馨提示】您单位有<a href="\\1" target="_blank">一条诉求</a>在规定时间内未处理,已上报给主管副校长处,如不及时处理,此诉求将于\\2上报给校长处',
	'note_complain_chuzhang3' => '【温馨提示】您单位有<a href="\\1" target="_blank">一条诉求</a>在规定时间内未处理,已上报给校长处',
	'note_complain_fuxiaozhang' => '【温馨提示】\\3有<a href="\\1" target="_blank">一条诉求</a>未处理,如不及时处理,此诉求将于\\2上报给校长处',
	'note_complain_xiaozhang' => '【温馨提示】\\2有<a href="\\1" target="_blank">一条诉求</a>未处理',
	
	
	'note_complain_user' => '【温馨提示】您发布的<a href="\\1" target="_blank">一条诉求</a>由于\\2未及时回复.已上报给\\3处',
	
	
	'note_doingcomplain_at' => '【温馨提示】您有<a href="\\1" target="_blank">一条诉求</a>待处理，此诉求将于\\2上报给负责人处',
	'note_doingcomplain_upto' => '【温馨提示】\\1有<a href="\\2" target="_blank">一条诉求</a>未处理,已转至您处',
	'note_doingcomplain_uptousers' => '【温馨提示】<a href="\\1" target="_blank">\\2</a>未在相应的时间内处理您的<a href="\\3" target="_blank">诉求信息</a>,该诉求已转至<a href="\\4" target="_blank">\\5</a>处',
	'note_doingcomplain_up' => '【温馨提示】在<a href="\\1" target="_blank">一条诉求</a>中提到了你,由于你没有及时处理,已上报到<a href="\\2" target="_blank">\\3</a>',
	'note_doingcomplain_reply' => '回复了你的<a href="\\1" target="_blank">诉求</a>',
	
	
	
	'note_comment_at' => '在<a href="\\1" target="_blank">一条评论</a>中@了你',
	'feed_friend_title' => '{actor} 和 {touser} 成为了好友',
	'note_friend_add' => '和你成为了好友',
	'note_poll_invite' => '邀请你一起参与 <a href="\\1" target="_blank">《\\2》</a>的\\3投票',
	'reward' => '悬赏',
	'reward_info' => '参与投票可获得  \\1 积分',
	'poll_separator' => '"、"',

	'feed_upload_pic' => '{actor} 上传了新图片',

	'feed_click_blog' => '{actor} 送了一个“{click}”给 {touser} 的日志 {subject}',
	'feed_click_thread' => '{actor} 送了一个“{click}”给 {touser} 的话题 {subject}',
	'feed_click_pic' => '{actor} 送了一个“{click}”给 {touser} 的图片',

	'friend_subject' => '<a href="\\2" target="_blank">\\1 请求加你为好友</a>',
	'comment_friend' =>'<a href="\\2" target="_blank">\\1 给你留言了</a>',
	'photo_comment' => '<a href="\\2" target="_blank">\\1 评论了你的照片</a>',
	'video_comment' => '<a href="\\2" target="_blank">\\1 评论了你的视频</a>',
	'blog_comment' => '<a href="\\2" target="_blank">\\1 评论了你的日志</a>',
	'poll_comment' => '<a href="\\2" target="_blank">\\1 评论了你的投票</a>',
	'share_comment' => '<a href="\\2" target="_blank">\\1 评论了你的分享</a>',
	'friend_pm' => '<a href="\\2" target="_blank">\\1 给你发私信了</a>',
	'poke_subject' => '<a href="\\2" target="_blank">\\1 向你打招呼</a>',
	'mtag_reply' => '<a href="\\2" target="_blank">\\1 回复了你的话题</a>',
	'event_comment' => '<a href="\\2" target="_blank">\\1 评论了你的活动</a>',

	'friend_pm_reply' => '\\1 回复了你的私信',
	'comment_friend_reply' => '\\1 回复了你的留言',
	'blog_comment_reply' => '\\1 回复了你的日志评论',
	'photo_comment_reply' => '\\1 回复了你的照片评论',
	'video_comment_reply' => '\\1 回复了你的视频评论',
	'poll_comment_reply' => '\\1 回复了你的投票评论',
	'share_comment_reply' => '\\1 回复了你的分享评论',
	'event_comment_reply' => '\\1 回复了你的活动评论',
    //公共主页
	'note_public_apply' => '有新的公共主页申请，<a href="\\1" target="_blank">请点击处理</a>',
	'invite_subject' => '\\1邀请您加入\\2，并成为TA的好友',
	'invite_massage' => '<table border="0">
		<tr>
		<td valign="top">\\1</td>
		<td valign="top">
		<h3>Hi，我是\\2，在\\3上建立了公共主页，邀请您也加入并成为我的好友</h3><br>
		您可以通过我的公共主页了解到最新的信息，分享我的照片，<br>
		随时和我保持联系<br>
		<br><br>
		<strong>请你点击以下链接，接受好友邀请：</strong><br>
		<a href="\\5">\\5</a><br>
		<br>
		<strong>如果你拥有\\3上面的账号，请点击以下链接查看我的个人主页：</strong><br>
		<a href="\\6">\\6</a><br>
		</td></tr></table>',

	'app_invite_subject' => '\\1邀请您加入\\2，一起来玩\\3',
	'app_invite_massage' => '<table border="0">
		<tr>
		<td valign="top">\\1</td>
		<td valign="top">
		<h3>Hi，我是\\2，在\\3上玩 \\7，邀请您也加入一起玩</h3><br>
		<br>
		邀请附言：<br>
		\\4
		<br><br>
		<strong>请你点击以下链接，接受好友邀请一起玩\\7：</strong><br>
		<a href="\\5">\\5</a><br>
		<br>
		<strong>如果你拥有\\3上面的账号，请点击以下链接查看我的个人主页：</strong><br>
		<a href="\\6">\\6</a><br>
		</td></tr></table>',

	'feed_mtag_add' => '{actor} 创建了新小组 {mtags}',
	'note_members_grade_-9' => '将你从小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 请出',
	'note_members_grade_-2' => '将你在小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的成员身份修改为 待审核',
	'note_members_grade_-1' => '将你在小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 中禁言',
	'note_members_grade_0' => '将你在小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的成员身份修改为 普通成员',
	'note_members_grade_1' => '将你设为了小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的明星成员',
	'note_members_grade_8' => '将你设为了小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的副群主',
	'note_members_grade_9' => '将你设为了小组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的群主',
	'feed_mtag_join' => '{actor} 加入了小组 {mtag} ({field})',
	'mtag_joinperm_2' => '需邀请才可加入',
	'feed_mtag_join_invite' => '{actor} 接受 {fromusername} 的邀请，加入了小组 {mtag} ({field})',
	'person' => '人',
	'delete' => '删除',

	'space_update' => '{actor} 被SHOW了一下',

	'active_email_subject' => '您的邮箱激活邮件',
	'active_email_msg' => '请复制下面的激活链接到浏览器进行访问，以便激活你的邮箱。<br>邮箱激活链接:<br><a href="\\1" target="_blank">\\1</a>',
	'protect_email_subject' => '您的密保邮箱验证邮件',
	'protect_email_msg' => '请复制下面的验证链接到浏览器进行访问，以便验证你的邮箱。<br>邮箱验证链接:<br><a href="\\1" target="_blank">\\1</a>',
	'active_different_uids_title' => '[ihome]用户激活时出现一人多个账号的情况',
	'active_different_uids_content' => '已激活了多个ihome账号，请告知技术开人员进行检查。',
	'share_space' => '分享了一个用户',
	'note_share_space' => '分享了你的空间',
	'share_doing' => '分享了一条记录',
	'note_share_doing' => '分享了你的<a href="\\1" target="_blank">一条记录</a>',
	'share_blog' => '分享了一篇日志',
	'note_share_blog' => '分享了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'share_arrangement' => '分享了一篇校园日历',
	'note_share_arrangement' => '分享了你的校园日历 <a href="\\1" target="_blank">\\2</a>',
	'share_album' => '分享了一个相册',
	'note_share_album' => '分享了你的相册 <a href="\\1" target="_blank">\\2</a>',
	'default_albumname' => '默认相册',
	'share_image' => '分享了一张图片',
	'share_video' => '分享了一个视频',
	'share_video_info' => '@{author}:{desc}',
	'album' => '相册',
	'note_share_pic' => '分享了你的相册 \\2 中的<a href="\\1" target="_blank">图片</a>',
	'note_share_video' => '转发了你上传的<a href="\\1" target="_blank">视频</a>',
	'share_thread' => '分享了一个话题',
	'mtag' => '小组',
	'note_share_thread' => '分享了你的话题 <a href="\\1" target="_blank">\\2</a>',
	'share_mtag' => '分享了一个小组',
	'share_mtag_membernum' => '现有 {membernum} 名成员',
	'share_tag' => '分享了一个标签',
	'share_tag_blognum' => '现有 {blognum} 篇日志',
	'share_link' => '分享了一个网址',
	'share_video' => '分享了一个视频',
	'share_music' => '分享了一个音乐',
	'share_flash' => '分享了一个 Flash',
	'share_event' => '分享了一个活动',
	'share_poll' => '分享了一个\\1投票',
	'note_share_poll' => '分享了你的投票 <a href="\\1" target="_blank">\\2</a>',
	'event_time' => '活动时间',
	'event_location' => '活动地点',
	'event_creator' => '发起人',
	'feed_task' => '{actor} 完成了有奖任务 {task}',
	'feed_task_credit' => '{actor} 完成了有奖任务 {task}，领取了 {credit} 个奖励积分',
	'the_default_style' => '默认风格',
	'the_diy_style' => '自定义风格',
	'feed_thread' => '{actor} 发起了新话题',
	'feed_eventthread' => '{actor} 发起了新活动话题',

	'feed_thread_reply' => '{actor} 回复了 {touser} 的话题 {thread}',
	'note_thread_reply' => '回复了你的话题',
	'note_post_reply' => '在话题 <a href=\\"\\1\\" target="_blank">\\2</a> 中回复了你的<a href=\\"\\3\\" target="_blank">回帖</a>',
	'thread_edit_trail' => '<ins class="modify">[本话题由 \\1 于 \\2 编辑]</ins>',
	'create_a_new_album' => '创建了新相册',
	'not_allow_upload' => '您现在没有权限上传图片',
	'get_passwd_subject' => '取回密码邮件',
	'get_passwd_message' => '您只需在提交请求后的三天之内，通过点击下面的链接重置您的密码：<br />\\1<br />(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)<br />上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录了。',
	'file_is_too_big' => '文件过大',
	'feed_blog_password' => '{actor} 发表了新加密日志 {subject}',
	'feed_blog' => '{actor} 发表了新日志',
	'feed_arrangement' => '{actor} 发布了一篇新的校园日历',
	'feed_poll' => '{actor} 发起了新投票',
	'note_poll_finish' => '您发起的<a href="\\1" target="_blank">《\\2》</a>的投票已结束,<a href="\\1" target="_blank">去写写投票总结</a>',
	'take_part_in_the_voting' => '{actor} 参与了 {touser} 的{reward}投票 <a href="{url}" target="_blank">{subject}</a>',
	'lack_of_access_to_upload_file_size' => '无法获取上传文件大小',
	'only_allows_upload_file_types' => '只允许上传jpg、jpeg、gif、png标准格式的图片',
	'only_allows_upload_video_types' => '目前仅能上传flv格式',
	'unable_to_create_upload_directory_server' => '服务器无法创建上传目录',
	'inadequate_capacity_space' => '空间容量不足，不能上传新附件',
	'mobile_picture_temporary_failure' => '无法转移临时图片到服务器指定目录',
	'ftp_upload_file_size' => '远程上传图片失败',
	'comment' => '评论',
	'upload_a_new_picture' => '上传了新图片',
	'upload_a_pic' => '上传图片',
	'upload_a_video' => '{actor} 上传了视频 {title}',
	'upload_album' => '更新了相册',
	'the_total_picture' => '共 \\1 张图片',
	'feed_invite' => '{actor} 发起邀请，和 {username} 成为了好友',
	'note_invite' => '接受了您的好友邀请',
	'space_open_subject' => '快来打理一下您的个人主页吧',
	'space_open_message' => 'hi，我今天去拜访了一下您的个人主页，发现您自己还没有打理过呢。赶快来看看吧。地址是：\\1space.php',
	'feed_space_open' => '{actor} 开通了自己的个人主页',
	
	'feed_profile_update_base' => '{actor} 更新了自己的基本资料',
	'feed_profile_update_contact' => '{actor} 更新了自己的联系方式',
	'feed_profile_update_edu' => '{actor} 更新了自己的教育情况',
	'feed_profile_update_work' => '{actor} 更新了自己的工作信息',
	'feed_profile_update_info' => '{actor} 更新了自己的兴趣爱好等个人信息',
	
	'apply_mtag_manager' => '想申请成为 <a href="\\1" target="_blank">\\2</a> 的群主，理由如下:\\3。<a href="\\1" target="_blank">(点击这里进入管理)</a>',
	'feed_add_attachsize' => '{actor} 用 {credit} 个积分兑换了 {size} 附件空间，可以上传更多的图片啦(<a href="cp.php?ac=credit&op=addsize">我也来兑换</a>)',

	'event'=>'活动',
	'event_set_delete' => '管理员取消了您组织的活动 \\1',
	'event_set_verify' => '管理员审核通过了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_set_unverify' => '管理员审核没有通过您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_set_recommend' => '管理员推荐了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_set_unrecommend' => '管理员取消推荐了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_set_open' => '管理员开启了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_set_close' => '管理员关闭了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_add' => '{actor} 发起了新活动',
	'event_feed_info' => '<strong>{title}</strong><br/>地点：{province} {city} {location} <br/>时间：{starttime} - {endtime}',
	'event_join' => '{actor} 参加了 <a href="space.php?uid={uid}" target="_blank">{username}</a> 的活动 <a href="space.php?do=event&id={eventid}" target="_blank">{title}</a>',
	'event_join_member' => '参加了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_quit_member' => '退出了您组织的活动 <a href="\\1" target="_blank">\\2</a>',
	'event_join_verify' => '申请参加您组织的活动 <a href="\\1" target="_blank">\\2</a>，赶紧去<a href="\\3" target="_blank">审核</a>吧',
	'eventmember_set_verify' => '通过了您参加活动 <a href="\\1" target="_blank">\\2</a> 的申请',
	'eventmember_unset_verify' => '把您在活动 <a href="\\1" target="_blank">\\2</a> 中的身份设为了待审核',
	'eventmember_set_admin' => '把您设为了活动 <a href="\\1" target="_blank">\\2</a> 的组织者',
	'eventmember_unset_admin' => '取消了您作为活动 <a href="\\1" target="_blank">\\2</a> 的组织者身份',
	'eventmember_set_delete' => '把您请出了活动 <a href="\\1" target="_blank">\\2</a>',
	'event_feed_share_pic_title'=>'{actor} 共享了新照片到活动相册',
	'event_feed_share_pic_info'=>'<b><a href="space.php?do=event&id={eventid}&view=pic" target="_blank">{title}</a></b><br/>共 {picnum} 张照片',
	'event_accept_invite' => '接受您的邀请参加了活动 <a href="\\1" target="_blank">\\2</a> ',
	'event_accept_success' => '成功参加该活动，您可以：<a href="\\1" target="_blank">立即访问该活动</a>',

	//道具：source/magic/*
	'magicunit' => '个',
	'magic_note_wall' => '在留言板上给你<a href="\\1" target="_blank">留言</a>',
	'magic_call' => '在\\1中点了你的名，<a href="\\2" target="_blank">快去看看吧</a>',
	'magicuse_thunder_announce_title' => '<strong>{username} 发出了“雷鸣之声”</strong>',
	'magicuse_thunder_announce_body' => '大家好，我上线啦<br><a href="space.php?uid={uid}" target="_blank">欢迎来我家串个门</a>',
	'magic_present_note' => '送给你一个道具 \\1, <a href="\\2">赶快去看看吧</a>',
	//用户组升级获赠道具
	'upgrade_magic_award' => '恭喜你等级提升为 \\1，并获赠以下道具：\\2',
	//管理员向用户赠送道具
	'present_user_magics' => '您收到了管理员赠送的道具：\\1',
	'has_not_more_doodle' => '您没有涂鸦板了',

	'do_stat_login' => '来访用户',
	'do_stat_register' => '新注册用户',
	'do_stat_invite' => '好友邀请',
	'do_stat_appinvite' => '应用邀请',
	'do_stat_add' => '信息发布',
	'do_stat_comment' => '信息互动',
	'do_stat_space' => '用户互动',
	'do_stat_login' => '来访用户',
	'do_stat_doing' => '记录',
	'do_stat_blog' => '日志',
	'do_stat_pic' => '图片',
	'do_stat_poll' => '投票',
	'do_stat_event' => '活动',
	'do_stat_share' => '分享',
	'do_stat_thread' => '话题',
	'do_stat_docomment' => '记录回复',
	'do_stat_blogcomment' => '日志评论',
	'do_stat_piccomment' => '图片评论',
	'do_stat_pollcomment' => '投票评论',
	'do_stat_pollvote' => '参与投票',
	'do_stat_eventcomment' => '活动评论',
	'do_stat_eventjoin' => '参加活动',
	'do_stat_sharecomment' => '分享评论',
	'do_stat_post' => '话题回帖',
	'do_stat_click' => '表态',
	'do_stat_wall' => '留言',
	'do_stat_poke' => '打招呼',
    'reporter_message_success' => '通过了你对<a href="space.php?uid=\\1">\\2</a>的\\3的举报，并删除了该\\3，<br/>被举报的\\3是：\\4，<br/>举报的类型是：\\5',
    'reporter_message_delete' => '删除了你对<a href="space.php?uid=\\1">\\2</a>的\\3举报，<br/>被举报的\\3是：\\4，<br/>举报的类型是：\\5',
    'reporter_message_ignore' => '禁止了你对<a href="space.php?uid=\\1">\\2</a>的\\3举报，<br/>被举报的\\3是：\\4，<br/>举报的类型是：\\5',
    'reportee_message' => '通过了其他用户对你的\\3的举报，并对其进行了删除<br/><br/>删除的\\3是：\\4<br/>举报的理由是：\\5',
    'report_type_ad' => '虚假广告',
    'report_type_sex' => '淫秽色情',
    'report_type_fake_reward' => '虚假中奖',
    'report_type_sensitive' => '敏感信息',
    'report_type_fake_info' => '虚假信息',
    'report_type_private' => '泄漏他人隐私',
    'report_type_attack' => '人身攻击',
    'report_type_copy' => '内容抄袭',
    'report_type_fake_people' => '冒充他人',
    'report_type_bother' => '骚扰他人',
    'report_type_' => '未填',
    'report_blogid' => '日志',
    'report_picid' => '照片',
    'report_albumid' => '相册',
    'report_tid' => '话题',
    'report_tagid' => '群组',
    'report_sid' => '分享',
    'report_uid' => '空间',
    'report_eventid' => '活动',
    'report_comment' => '评论',
    'report_pid' => '投票',
    'report_post' => '回帖',
    'report_doid' => '足迹',

	//plugin
	'mobile_addtrack' => '{actor}：{message}',
    'use_new_app' => '授权使用了新应用',

    //search
    'common_friends' => '\\1等\\2位共同好友。',
    'common_class' => '你们都是\\1的',
    'common_hometown' => '你们都是来自\\1',
    'mtag_add_thread' => '发起了新话题<br/><a href="space.php?do=thread&id=\\1">\\2</a><br/>群组:<a href="space.php?do=mtag&tagid=\\3">\\4</a>',
    'mtag_invite_note' => '邀请您加入群组<a href="space.php?do=mtag&tagid=\\1">\\2</a>',
    'event_invite_note' => '邀请您参加活动<a href="space.php?do=event&id=\\1">\\2</a>'
);

?>
