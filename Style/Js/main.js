/**
*网站共用的基础方法
*@author zhouquan
*/
(function($){
	Date.prototype.format = function (fmt) { //author: meizz 
		fmt = fmt || "";
		fmt = fmt.replace(/Y/ig,"y").replace(/H/ig,"h").replace(/D/ig,"d").replace(/S/ig,"s");
	    var o = {
	        "M+": this.getMonth() + 1, //月份 
	        "d+": this.getDate(), //日 
	        "h+": this.getHours(), //小时 
	        "m+": this.getMinutes(), //分 
	        "s+": this.getSeconds(), //秒 
	        "q+": Math.floor((this.getMonth() + 3)/3), //季度 
	        "S": this.getMilliseconds() //毫秒 
	    };
	    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	    for (var k in o)
	    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	    return fmt;
	};
	var timeer_interval = null
	var Module = {
		/**
		* 封装ajax请求
		*/
		ajax: function(param) {
			var funError = param.error ;
			param.dataType = param.dataType || "json";
			param.type = param.type || 'POST';
			param.timeout = param.timeout || 60 * 1000;
			param.error = function(e){
				parent.layer.closeAll('loading');
				//console.error('[ERROR]['+param.url+']:', e.stack, e);
				setTimeout(function(){
					parent.layer.msg("网络异常", {
						icon: 5
					});
				}, 500);
				if(funError){
					funError(e);
				}else{
					//param.success && param.success({code:1, msg:"请求信息异常",error: e});
				}
			};
			$.ajax(param);
		},
		/**
		 * 常用api接口
		 */
		getApi: function(api, data, callback){
			var pid = parent.layer.load(360);
			Module.ajax({
				url: api,
				data: data,
				success: function(result){
					parent.layer.close(pid);
					var resultText = result.resultText || {};
					//code = 1 是正常
					//code = 0 是失败
					//code = -404 java接口没有响应
					//code = -500 JAVA接口异常
					if(result.code == 1){
						$.wrongMsg(resultText.message || "操作成功", 1);
						callback && callback(resultText);
					}else if(result.code == 0){
						//失败
						$.wrongMsg(resultText.message || "操作失败");
					}else if(result.code == -404){
						//java接口没有响应
						$.wrongMsg(resultText.message || resultText);
					}else if(result.code == -500){
						//java接口异常
						$.wrongMsg(resultText.message || resultText);
					}else{
						$.wrongMsg(resultText.message || "操作失败");						
					}
				}
			});
		},
		/**
		*	把url序列化为对象
		*	如: name=zhouquan&age=22&sex=1 => {name:"zhouquan", age: 22, sex: 1};
		*/
		serializeJson: function(search) {
			search = (search || "").replace(/^\s*\?/, "");
			var arrs = search.split("&");
			var result = {};
			for (var i in arrs) {
				var vals = arrs[i].split("=");
				result[decodeURIComponent(vals[0])] = decodeURIComponent(vals[1]);
			}
			return result;
		},
		getParam: function(){
			var search = location.search.replace(/^\s*\?/, "");
			return Module.serializeJson(search);
		},
		/**
		* 格式化日期
		*/
		formatDate: function(value, format){
			var date = value;
			if(!(value instanceof Date)){
				date = new Date(date);
			}
			return date.format("yyyy-MM-dd hh:mm:ss");
		},
		/** 
		* 实现setTimeout的功能
		*/
		cycle: function(fun, cycleTime){
			(function handle(){
				setTimeout(function(){
					fun();
					handle();
				}, cycleTime)
			})();
		},
		/** 
		* 验证码倒计时
		*/
		countdown: function(url, data, obj, count){
			if(me.hasClass("btn-disable")) return;
			var countdown = count,
				pid = parent.layer.load(360);
			$.post(url, data, function (result){
				parent.layer.close(pid);
				clearInterval(timeer_interval);
				timeer_interval = setInterval(function () {
					if(countdown == 0) {
						obj.text("重新发送").removeClass("btn-disable");
						countdown = 60;
						clearInterval(timeer_interval);
					}else{
						obj.text("重新发送(" + countdown + "s)").addClass("btn-disable");
						countdown--;
					}
				}, 1000);
			});
		}
	};
	/** 
	* 错误提示
	*/
	$.wrongMsg = function(str, icon, time){
		var str = str || "操作失败",
			icon = icon || 2,
			time = time || 1500;
		parent.layer.msg(str, {
			icon: icon,
			time: time
		});
	};
	$.wrongTip = function(obj, str, icon, time){
		var str = str || "操作失败",
			icon = icon || [1, "#ff9900"],
			time = time || 2000;
		layer.tips(str, obj, {
			tips: icon,
			time: time,
			maxWidth: 250
		});
	};
	/** 
	* 调用css
	*/
	$.getCss = function(url){
		var node = document.createElement("link");
		node.href = url;
		node.async = "async";
		node.rel = "stylesheet";
		node.text = "text/css";
		document.body.appendChild(node);
		return node;
	};
	/** 
	* 点击关闭浮动层
	*/
	$.clickHide = function(obj){
		$(document).on("click.clickHide",function(e){
			if($(e.target).closest(obj).length > 0) return;
			$(obj).fadeOut("fast");
			$(document).off(".clickHide");
		});
	};
	/** 
	*价格 只能输入数字和点
	*/
	$("body").on("keyup", "input.js-money", function(){
		var val = $(this).val(),
			maxNum = parseFloat($(this).data("max")) || "";
		if(maxNum && parseFloat(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		if(val.indexOf(".")>=0) val = val.substring(0,val.indexOf(".") + 3);
		$(this).val(val.replace(/[^\d.]/g,""));
		if(maxNum && parseFloat(val) > maxNum) $(this).val(val.substr(0,val.length-1));
	});
	/**
	 *联系电话
	 */
	$("body").on("keyup", "input.js-homephone", function(){
		var val = $(this).val(),
			maxNum = parseFloat($(this).data("max")) || "";
		if(maxNum && parseFloat(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		$(this).val(val.replace(/[^\d-]/g,""));
		if(maxNum && parseFloat(val) > maxNum) $(this).val(val.substr(0,val.length-1));
	});
	/** 
	*只能数字整数
	*/
	$("body").on("keyup", "input.js-number, textarea.js-number", function(){
		var val = $(this).val(),
			maxNum = parseInt($(this).data("max")) || "";
		if(maxNum && parseInt(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		$(this).val(val.replace(/[^\d]/g,""));
	});
	/**
	 *只能数字或字母
	 */
	$("body").on("keyup", "input.js-numletter", function(){
		var val = $(this).val(),
			reg = /^[A-Za-z0-9]*$/;
		if(!reg.test(val)) {
			$(this).val(val.substr(0,val.length-1).replace(/[\u4e00-\u9fa5]/g,""));
			return;
		}
	});
	/**
	 * 不能输入特殊符号
	 */
	$("body").on("keyup","input.js-teshu",function(){
		var val = $(this).val();
		    reg = new RegExp("[`~!@#$^&%*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]")
		if(reg.test(val)){
			$(this).val(val.replace(reg,""));
			return;
		}
	})
	/** 
	*手机号码
	*/
	$.isTelephone = function(str){
		var reg = /^0?1[0-9]\d{9}$/;
		if(reg.test(str)){
			return true;
		}
		return false;	
	}
	/**
	/*智能浮动定位*
	用法：$("#float").smartFloat();
	**/
	$.fn.smint = function( options ) {
		// adding a class to users div
		$(this).addClass('smint');
		var settings = $.extend({
            'scrollSpeed '  : 500
            }, options);
		return $('.smint a').each( function() {
			if ( settings.scrollSpeed ) {
				var scrollSpeed = settings.scrollSpeed
			}
			// get initial top offset for the menu 
			var stickyTop = $('.smint').offset().top;	
			// check position and make sticky if needed
			var stickyMenu = function(){
				// current distance top
				var scrollTop = $(window).scrollTop(); 		
				if (scrollTop > stickyTop) { 
					//$('.smint').css({ 'position': 'fixed', 'top':0 });
					$('.smint').addClass('fxd');
				} else {
					$('.smint').removeClass('fxd');
					//$('.smint').css({ 'position': 'absolute', 'top':stickyTop }); 
				}   
			};	
			// run function
			//stickyMenu();
			$(window).scroll(function() {
				 stickyMenu();
			});
        	$(this).on('click', function(e){
				var selectorHeight = $('.smint').height();   
        		// stops empty hrefs making the page jump when clicked
				e.preventDefault();
		 		var id = $(this).attr('id');
				var goTo =  $('div.'+ id).offset().top -selectorHeight;
				$("html, body").animate({ scrollTop: goTo }, scrollSpeed);
			});	
		});
    }
	/**
	/*删除数组里的某个元素
	调用：var emp = ['abs','dsf','sdf','fd'];$.removeArr(emp, "fd");
	**/
	$.removeArr = function(arrs, val) {
		var index = arrs.indexOf(val);
		if(index > -1){
			arrs.splice(index, 1);
		}
	};
	/**
	/*时间戳*
	调用：getTimeFormat("2015-06-28 23:59:59"), 显示1435507199000
	**/
    getTimeFormat = function(day){
        var re = /(\d{4})(?:-(\d{1,2})(?:-(\d{1,2}))?)?(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?/.exec(day);
        return new Date(re[1],(re[2]||1)-1,re[3]||1,re[4]||0,re[5]||0,re[6]||0).getTime();
    };
	define("main", function(require, exports, module) {
		module.exports = Module;
	});
})(jQuery);