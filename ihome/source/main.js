(function() {
	var jq = jQuery.noConflict();
	//左右边栏漂浮效果
	jq(window).scroll(function() {
		var scroll = jq(window).scrollTop();
		var leftSide, rightSide;
		try {
			leftSide = jq("#app_sidebar").outerHeight(true) + jq("#app_sidebar").position().top;
			rightSide = jq("#sidebar").outerHeight(true) + jq("#sidebar").position().top;
		}
		catch(e) {}
		function showOut(id) {
			jq(id).css({
				"display": "block"
			});
			jq(id).stop().animate({
				"margin-top": "0px"
			},
			300);
			jq(id).css({
				"position": "absolute",
				"width": "175px"
			});
			jq(id).css({
				"top": scroll
			});
		}
		function unshowOut(id) {
			jq(id).css({
				"display": "none",
				"position": "relative",
				"margin-top": "-261px"
			});
		}
		if (scroll > leftSide) showOut("#scrollToShow");
		if (scroll < leftSide) unshowOut("#scrollToShow");
		if (scroll > rightSide) showOut("#toFlyright");
		if (scroll < rightSide) unshowOut("#toFlyright");
	});
	//左边栏我的活动隐藏与显示
	jq(function() {
		jq(".left_line_my_act").click(function() {
			if (jq("#my_defaultapp").css("display") == "none") {
				jq("#my_defaultapp").css({
					"display": "block"
				});
				jq("#my_defaultapp").stop().animate({
					"height": "322px"
				},
				300);
			} else {
				jq("#my_defaultapp").stop().animate({
					"height": "0px"
				},
				400);
				jq("#my_defaultapp").css({
					"display": "none"
				});
			}
		});
	});
	//回到顶部
	jq(window).scroll(function() {
		if (jq(window).scrollTop() > 0) {
			jq("#back").css({
				"display": "block"
			});
		} else {
			jq("#back").css({
				"display": "none"
			});
		}
	});
	jq(function() {
		jq("#back").click(function() {
			if (navigator.appName != "Microsoft Internet Explorer") {
				jq("#back").stop().animate({
					"top": "67%"
				},
				1000, function() {
					jq("#back").stop().animate({
						"top": "0%"
					},
					500, function() {
						jq("#back").css({
							"top": "70%",
						});
					});
				});

				jq("html,body").stop().animate({
					scrollTop: "0px",
				},
				1500);
			}
			else {
				jq("html,body").stop().animate({
					scrollTop: "0px"
				},
				600);
			}
		});
	});
	jq(document).ready(function() {
		var path = window.location.search;
		if (path.indexOf("do=home") > 0 || path.indexOf("do=feed") > 0) {
			jq("#index").addClass("active");
		} else if (path.indexOf("do=friend") > 0 || path.indexOf("ac=invite") > 0) {
			jq("#friend").addClass("active");
		} else if (path == "") {
			jq("#home").addClass("active");
		} else if (path.indexOf("pluginid=apps") > 0) {
			jq("#app").addClass("active");
		}
	});
	jq(function() {
		jq(".left_line_my_act a").click(function() {
			if (jq(".icon-pull").attr("src") == "image/icons/icon-pull-down.png") {
				jq(".icon-pull").attr({
					"src": "image/icons/icon-pull-up.png"
				});
			}
			else {
				jq(".icon-pull").attr({
					"src": "image/icons/icon-pull-down.png"
				});
			}
		});
	});
	jq(function() {
		var str = jq(".Left_mainname").data("name");
		str = jq.trim(str);
		var size = (16 - str.length) / 3 * 2;
		size += 14;
		size += "px";
		jq(".Left_mainname").css({
			"font-size": size,
			"color": "#01b6f9"
		});
	});
})();
