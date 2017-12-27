<?php
//-------------------------------------------维护页面-------------------------------------------------------------------------
//每次上线可以开启下面的ｄｅｂｕｇ方法进入调试页面，调试提供白名单，任何在白名单内的ＩＰ可以无视调试页面，正常反问站点
function getClientIP(){

     if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
            //如果客户端同时使用代理，HTTP_X_FORWARD_FOR会返回一个由逗号分隔的字符串，最后一个ｉｐ是用户的真实ｉｐ
            $IParray=array_values(array_filter(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])));
            return end($IParray); 
        }else if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
            return $_SERVER["REMOTE_ADDR"]; 
        }else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER["HTTP_CLIENT_IP"]; 
        } 

        return '';
}

function debug($isdebug=false){
    if($isdebug){
        $allow_ip=Array("127.0.0.1",
                        "180.153.89.85",
                        "115.159.65.162",
                        "218.17.34.102",
                        "101.200.185.166",
                        "101.200.185.189",
                        "121.40.169.1",
                        "120.26.109.71",
                        "120.55.198.29",
                        "101.201.177.121",
                        "172.16.20.70");

        if(!in_array(getClientIP(), $allow_ip)){
            Header("Location: http://".$_SERVER['SERVER_NAME'].'/emergency.php');
        }

        // //https重定向
        if ($_SERVER["HTTPS"] <> "on")
        {
            $xredir="https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            header("Location: ".$xredir);
        }
    }
}

//开启进入维护页面，需要配置维护页面的时间
//debug(true);

//-------------------------------------------版本检查-------------------------------------------------------------------------
//四种版本 STAGING TESTING DEVELOPMENT PRODUCT ,配置于ＮＧＩＮＸ配置文件中，fastcgi APPENV **（如果没有定义,默认未PRODUCT)
//根据该参数定义同名的四个常量用户配置
(@include './env.php') or die('env.php not find !'.__FILE__);


//-------------------------------------------共享session-------------------------------------------------------------------------
ini_set('session.cookie_path', '/');
//ini_set('session.cookie_domain','ccfax.cn');

//-------------------------------------------路由重写-------------------------------------------------------------------------
if(isset($_SERVER['HTTP_X_REWRITE_URL'])){
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
    $___s = explode(".",$_SERVER['REQUEST_URI']);
    $____s = explode("?",$_SERVER['REQUEST_URI']);
    $_SERVER['PATH_INFO'] = $____s[0];
    $GLOBALS['is_iis'] = true;
}  

//-------------------------------------------移动端重定向-------------------------------------------------------------------------
//链金所的移动端重定向需要手动合成ｕｒｌ，导向用户想要看到的页面
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

function is_ajax(){
    if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
        return 1;
    }
    else
        return 0;
}

    
$url = explode('/', $_SERVER['REQUEST_URI']);
if(is_mobile() && !(strtolower($url[1])=='m' or 
                    strtolower($url[1])=='adminm' or 
                    strtolower($url[1])=='confirm' or 
                    strtolower($url[1])=='m.html'  or 
                    strtolower($url[2])=='active' or 
                    strtolower($url[2])=='report' or 
                    strtolower($url[2])=='huaxin' or 
                    strtolower(substr($url[2],0,3))=="pro" or 
                    strtolower(substr($url[2],0,7))=="huodong" or
                    strtolower($url[2])=='hongmu'  or 
                    strtolower($url[3])=='register' or  
                    strstr($_SERVER['REQUEST_URI'],"verify2"))&&(!is_ajax())){
    Header("HTTP/1.1 301 Moved Permanently");
    if(strtolower($url[1])=='invest'){
        if(strstr($url[2],"index")){
             Header("Location: http://".$_SERVER['SERVER_NAME'].'/m/invest/lists'.str_replace('index',"",strtolower($url[2])));
        }else{
           Header("Location: http://".$_SERVER['SERVER_NAME'].'/m/invest/detail/id/'.strtolower($url[2]));
        }
    }else{
        Header("Location: http://".$_SERVER['SERVER_NAME'].'/m/index');
    }
}


//-------------------------------------------系统定义-------------------------------------------------------------------------

if(STAGING){
    define('APP_DEBUG',0);
}elseif(TESTING){
    define('APP_DEBUG',0);
}elseif(DEVELOPMENT){
    define('APP_DEBUG',1);
}else{
    define('APP_DEBUG',0);
}


define('THINK_PATH',dirname(__FILE__).'/CORE/');
define('APP_NAME',dirname(__FILE__).'App');
define('APP_PATH',dirname(__FILE__).'/App/');
define('APP_PUBLIC_PATH',dirname(__FILE__).'/Public');
define('BUILD_DIR_SECURE',true); 
define('DIR_SECURE_FILENAME', 'default.html'); 
define('DIR_SECURE_CONTENT', 'deney Access!'); 

require(THINK_PATH.'Core.php');

?>