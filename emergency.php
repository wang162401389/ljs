<?php
function is_mobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }
if(is_mobile()){
echo '<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<link href="https://www.ccfax.cn/404/error.css" rel="stylesheet"  type="text/css" />
		<title>升级维护</title>
	</head>
	<style>
		
	</style>
	<body>
		 <section class="mobileError404">
		 	<div>
		 		<img width="auto" src="https://www.ccfax.cn/404/error404.png" alt="维护中" />
		 	</div>
		 	<h3>19:30-21:30 系统维护中，请稍候再访问！</h3>
		</section>
		<footer class="mobileFooter">
			<img width="100%" src="https://www.ccfax.cn/404/mobileFooter.png" />
		</footer>
	</body>
</html>';
}else{
echo '<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta charset="{CHARSET}">
		<title>升级维护</title>
		<link href="https://www.ccfax.cn/404/error.css" rel="stylesheet"  type="text/css"  />
	</head>
	<body>
		<header>
			<img width="100%" src="https://www.ccfax.cn/404/PCheader.png" />
		</header>
		 <section class="PCerror404">
		 	<div>
		 		<img width="auto" src="https://www.ccfax.cn/404/error404.png" alt="维护中" />
		 	</div>
		 	<h3>19:30-21:30 系统维护中，请稍候再访问！</h3>
		</section>
		<footer>
			<img width="100%" src="https://www.ccfax.cn/404/PCfooter.png" />
		</footer>
	</body>
</html>';
}
?>
