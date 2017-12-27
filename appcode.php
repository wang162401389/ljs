<!-- <<!DOCTYPE html>
<html>
<head>
    <title></title>
<script type="text/javascript">
    function isWeiXin(){
        var ua = window.navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){
            return true;
        }else{
            return false;
        }
    }
     
    function transferTo(){
        var flag = isWeiXin();
        if(flag){
            window.location.href="http://a.app.qq.com/o/simple.jsp?pkgname=com.shaoshaohuo.app";
        }else{
            var u = navigator.userAgent;
            if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {//安卓手机
                //安卓手机
                window.location.href = "http://www.ccfax.cn/UF/app/ccfax_2015.apk";
             
            } else if (u.indexOf('iPhone') > -1) {//苹果手机
                //苹果手机
                window.location.href = "Items://itunes.apple.com/cn/app/lian-jin-suo/id1061851134?mt=8";
             
            } else if (u.indexOf('Windows Phone') > -1) {//winphone手机
                //alert("winphone手机");
                // window.location.href = "mobile/index.html";
            }
        }
    }
    transferTo();
</script>
</head>
<body>

</body>
</html> -->



<?php
	function is_weixin(){ 
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
				return true;
		}	
		return false;
	}

    //判断手机终端
    header("Content-type:text/html; charset=utf-8");
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
        //var_dump($user_agent);
	if(is_weixin()){
		if(stristr($_SERVER['HTTP_USER_AGENT'],'Android')) {
			//echo "<script>alert('Android系统');</script>";
			header('Location: http://a.app.qq.com/o/simple.jsp?pkgname=com.wta.NewCloudApp.jiuwei29455'); exit();
		}else if(stristr($_SERVER['HTTP_USER_AGENT'],'iPhone')){
			//echo '你的手机是：ISO系统';
			header('Location: http://a.app.qq.com/o/simple.jsp?pkgname=com.wta.NewCloudApp.jiuwei29455&g_f=991653');exit();
		}	
	}
	else{
		 if(stristr($_SERVER['HTTP_USER_AGENT'],'Android')) {
			//echo "<script>alert('Android系统');</script>";
			header('Location: http://www.ccfax.cn/UF/app/jiuwei29455.apk');
		}else if(stristr($_SERVER['HTTP_USER_AGENT'],'iPhone')){
			//echo '你的手机是：ISO系统';
			header('Location: itms://itunes.apple.com/cn/app/lian-jin-suo/id1061851134?mt=8');
		}else{
			//echo '你使用的是其他系统';
			header('Location: http://www.ccfax.cn/Home/bangzhu/phone.html');
		}
	}
    

?>
