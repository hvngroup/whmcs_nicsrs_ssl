/***
 * 漫画原创弹出内容提示Jquery插件
 * 编写时间：2012年10月29号
 * 关注JquerySchool，支持原创
 * http://www.jq-school.com
 * version:manhua_msgTips.js
***/
$(function() {
	$.fn.manhua_msgTips = function(options) {
		var defaults = {
			Event : "click",			//响应的事件
			timeOut : 3000,				//提示层显示的时间
			msg : ['thank you'],			//显示的消息
			speed : 300,				//滑动速度
			type : "success"			//提示类型（1、success 2、error 3、warning）
		};
		var options = $.extend(defaults,options);
		var bid = parseInt(Math.random()*100000);
		var errorHtml = '<div id="tip_container'+bid+'" class="container tip_container">';
		$.each(options.msg,function (index,value) {
			errorHtml += '<div name="tip'+bid+'" class="mtip"><i class="micon"></i><span name="tsc'+bid+'">'+value+'</span><i name="mclose'+bid+'" class="mclose"></i></div>'
		})
		errorHtml += '</div>';
		$("body").prepend(errorHtml);
		var $this = $(this);
		var $tip_container = $("#tip_container"+bid)
		var $tip = $("[name=tip"+bid+"]");
		var $tipSpan = $("[name=tsc"+bid+"]");
		var $colse = $("[name=mclose"+bid+"]");
		//先清楚定时器
		clearTimeout(window.timer);
		
		//主体元素绑定事件

		$tip.attr("class", options.type).addClass("mtip");
		$tip_container.slideDown(options.speed);
			//提示层隐藏定时器
		window.timer = setTimeout(function (){
			$tip_container.slideUp(options.speed);
			}, options.timeOut);
		

		
		
		//鼠标移到提示层时清除定时器
		$tip_container.on("mouseover",function() {
			clearTimeout(window.timer);
		});
		
		//鼠标移出提示层时启动定时器
		$tip_container.on("mouseout",function() {
			window.timer = setTimeout(function (){
				$tip_container.slideUp(options.speed);
			}, options.timeOut);
		});
	
		//关闭按钮绑定事件
		$colse.on("click",function() {
			if($("[name=mclose"+bid+"]").length == 1){
				$tip_container.slideUp(options.speed);
			}else {
				$(this).parents("[name=tip"+bid+"]").remove();
			}

		});
	}
});