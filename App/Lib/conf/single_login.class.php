<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/7/28
 * Time: 17:03
 */
class single_login{
    private $enable=0;
    private $redis;
    private static $_instantce;

    private function __construct(){
        $this->enable=C("SINGLE_LOGIN.enable");
        if($this->enable){
            $this->redis = new Redis();
            $redis_info=C("REDIS_INFO");
            $this->redis->connect($redis_info['host'], 6379);
            $this->redis->auth($redis_info['auth']);
        }
    }

    static function getInstance(){

        if(!(self::$_instantce instanceof self)){
            self::$_instantce= new single_login();
        }
        return self::$_instantce;
    }

    private function is_mobile() {
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
    /**************************
     * login
     */

    public function  login($uid){
        if($this->enable){
            if($this->is_mobile()){
                $method="m";
            }else{
                $method="pc";
            }
            $string=$method."_".$uid;
            $where["id"]=$uid;
            $info=M("members")->where($where)->field("user_name")->find();
            Log::write("用户".$info["user_name"],"现在一次登录,对应的session_id为：".$session_id);
            $this->redis->set($string,session_id());

        }
    }

    public function check_login($uid=0){
        if($uid==0){
            return;
        }
        if($this->enable){
            if(!is_ajax()){
                if($this->is_mobile()){
                    $method="m";
                }else{
                    $method="pc";
                }

                $string=$method."_".$uid;
                $session_id=$this->redis->get($string);
                Log::write("session_id : ".$session_id);
                if($session_id!=session_id()&&(!empty($session_id))){
                    session(null);
                    echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
                    echo '<script type="text/javascript">alert("您的账号已在其他地方登陆，如不是本人操作，请注意账号安全！");top.location.href="/member/common/login/";</script>';exit;
                }
            }
        }
    }


}
