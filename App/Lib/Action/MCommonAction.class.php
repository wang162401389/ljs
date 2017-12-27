<?php
// 全局设置
class MCommonAction extends Action
{
    public $glo=null;
    public $uid=0;
    //上传参数
    public $savePathNew=null;
    public $thumbMaxWidthNew="10,50";
    public $thumbMaxHeightNew="10,50";
    public $thumbNew=null;
    public $allowExtsNew=null;
    public $saveRule=null;
    //验证身份
    protected function _initialize()
    {
        $loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
        $this->assign("loginconfig", $loginconfig);
        $datag = get_global_setting();
        $this->glo = $datag;//供PHP里面使用
        $this->assign("glo", $datag);//公共参数
        $hetong = M('hetong')->field('name,dizhi,tel')->find();
        $this->assign("web", $hetong);
        $bconf = get_bconf_setting();
        $this->gloconf = $bconf;//供PHP里面使用
        $this->assign("gloconf", $bconf);
        $borrow_count = M("borrow_info")->where("borrow_status = 2 and test = 0 and is_beginnercontract=0")->count();
        $debt_count = M("debt_borrow_info")->where("borrow_status = 2")->count();
        $this->assign("borrow_count", $borrow_count);
        $this->assign("debt_count", $debt_count);
        if ($this->notneedlogin === true) {
            if (session("u_id")) {
                $this->uid = session("u_id");
                $this->assign('UID', $this->uid);
                $unread=M("inner_msg")->where("uid={$this->uid} AND status=0")->count('id');
                $this->assign('unread', $unread);
                if (!in_array(strtolower(ACTION_NAME), array("actlogout",'regsuccess','emailverify','verify'))) {
                    redirect(__APP__."/member/");
                }
            } else {
                $loginconfig = FS("Webconfig/loginconfig");
                $de_val = $this->_authcode(cookie('UKey'), 'DECODE', $loginconfig['cookie']['key']);
                if (substr(md5($loginconfig['cookie']['key'].$de_val), 14, 10) == cookie('Ukey2')) {
                    $vo = M('members')->field("id,user_name")->find($de_val);
                    if (is_array($vo)) {
                        foreach ($vo as $key=>$v) {
                            session("u_{$key}", $v);
                        }
                        $this->uid = session("u_id");
                        $this->assign('UID', $this->uid);
                        $unread=M("inner_msg")->where("uid={$this->uid} AND status=0")->count('id');
                        $this->assign('unread', $unread);
                        if (!in_array(strtolower(ACTION_NAME), array("actlogout",'regsuccess','emailverify','verify'))) {
                            redirect(__APP__."/member/");
                        }
                    } else {
                        cookie("Ukey", null);
                        cookie("Ukey2", null);
                    }
                }
            }
        } elseif (session("u_user_name")) {
            $this->uid = session("u_id");
            $unread=M("inner_msg")->where("uid={$this->uid} AND status=0")->count('id');
            $this->assign('unread', $unread);
            $this->assign('UID', $this->uid);
            //如果开启代还款模式
            $add_function=C("ADD_FUNCTION");
            if ($add_function['repayment']['enable']) {
                $super_name=$add_function['repayment']['account'];
                $super_name1=$add_function['repayment']['account1'];
                $minfo =getMinfo($this->uid, true);
                if ($super_name==$minfo['user_name']) {
                    $this->supper_login=1;
                    $this->assign("supper_login", $this->supper_login);
                } elseif ($super_name1==$minfo['user_name']) {
                    $this->supper_login=2;
                    $this->assign("supper_login", $this->supper_login);
                }
            }
        } else {
            $loginconfig = FS("Webconfig/loginconfig");
            $de_val = $this->_authcode(cookie('UKey'), 'DECODE', $loginconfig['cookie']['key']);
            if (substr(md5($loginconfig['cookie']['key'].$de_val), 14, 10) == cookie('Ukey2')) {
                $vo = M('members')->field("id,user_name")->find($de_val);
                if (is_array($vo)) {
                    foreach ($vo as $key=>$v) {
                        session("u_{$key}", $v);
                    }
                    $this->uid = session("u_id");
                    $this->assign('UID', $this->uid);
                    $unread=M("inner_msg")->where("uid={$this->uid} AND status=0")->count('id');
                    $this->assign('unread', $unread);
                } else {
                    cookie("Ukey", null);
                    cookie("Ukey2", null);
                }
            } else {
                redirect(__APP__."/member/common/login/");
                exit;
            }
        }

        if (method_exists($this, '_MyInit')) {
            $this->_MyInit();
        }
        if (ACTION_NAME != "actlogout" && ACTION_NAME!="xieyi") {
            $this->checkconfirm();
        }

        if (C("EVENT_INFO.enable")) {
            $this->assign("event_enable", C("EVENT_INFO.enable"));
            $this->assign("event_prom", C("EVENT_INFO.mobile_prom"));
        }
        if (C("Frind_INFO.enable")) {
            $this->assign("friend_enable", C("Frind_INFO.enable"));
        }
        //协议判断
        if ($_SERVER["SERVER_PORT"] == 443) {
            session('xieyi', 'https');
        } else {
            session('xieyi', 'http');
        }
        //$this->assign("sinabalance",querybalance($this->uid));
        //$this->assign("sinasaving",querysaving($this->uid));
        $this->assign("touzitype", $this->getresult($this->uid));

        // import("@.conf.single_login");
        // $single= single_login::getInstance();
        // $single->check_login($this->uid);
        ccfaxapibalace($this->uid);
        //查询是否投了体验标 没投普通标的
        if ($this->uid) {
            $is_borrow = M("members")->where(array("id"=>$this->uid,"is_vip"=>0))->find();
            $borrow_invest = M("borrow_investor")->where(array("investor_uid"=>$this->uid))->count();
            $borrow_experience = M("investor_detail_experience")->where(array("investor_uid"=>$this->uid,"borrow_id"=>2))->count();
            if ($is_borrow && $borrow_invest == 0 && $borrow_experience > 0) {
                $this->assign("tips_withdarw", 1);
            }
        }
        /************* 用户是否设置新浪密码，用户中心下拉列表是否显示设置新浪密码***************/
        $setpasswd=0;//是否设置新浪密码 0 未设置  1设置
        $ids = M('members m')->join("lzh_members_status s on s.uid = m.id")->where("m.id={$this->uid}")->field('m.user_regtype,s.is_pay_passwd,s.company_status')->find();
        if ($ids['is_pay_passwd']==1) {
            $setpasswd=1;
        }
        $this->assign("setpasswd", $setpasswd);
        //用户类型
        $this->assign('user_type', $ids['user_regtype']);
        /*****************end*************/
        //友情链接
        $linklist = M('friend')->where('link_type = 1 and is_show = 1')->order('link_order DESC')->select();
        $this->assign('linklist', $linklist);

        $jiekuan_info = M('jiekuan')->where(array('uid' => $this->uid, 'status' => array('in', array('1', '2', '4'))))->field('id')->find();
        $this->assign("jiekuan_status", !empty($jiekuan_info) ? 1 : 0);
    }

    protected function getresult($uid)
    {
        $sql="SELECT SUM(g.score) AS tp_sum FROM `lzh_risk_result` h inner join lzh_risk_answer g on g.id=h.answer_id WHERE ( `uid` = '{$uid}') ";
        $scorelist=M("risk_result h")->query($sql);
        $score=$scorelist[0]["tp_sum"];
        if ($score>=7 && $score<=12) {
            return "保守型";
        } elseif ($score>=13 && $score<=17) {
            return "谨慎型";
        } elseif ($score>=18 && $score<=23) {
            return "稳健型";
        } elseif ($score>=24 && $score<=28) {
            return "积极型";
        } else {
            return "无";
        }
    }

    //检查满标确认
    protected function checkconfirm()
    {
        if ($this->uid) {
            //综合服务费和咨询服务费
            $vo = M("borrow_confirm")->where("uid={$this->uid}")->select();
            if ($vo != null) {
                foreach ($vo as $i) {
                    if ($i["fee_status"] == 0 || ($i["danbao_status"] == 0 && $i["danbao_id"] != 0)) {
                        redirect(__APP__."/confirm/index/confirm"); //支付列表页
                        exit;
                    }
                }
            }

            /**
             * 债权费用
             */



         $zhaiquan_fee=M("debt_borrow_info")->where("borrow_uid={$this->uid} AND pay_fee = 0 AND borrow_status in (6,7)")->count();
            if ($zhaiquan_fee > 0) {//如果存在
             redirect(__APP__."/confirm/index/zhaiquanfee");
                exit;
            }
        }
    }

    public function memberheaderuplad()
    {
        if ($this->uid <> $_GET['uid'] || !$this->uid) {
            exit;
        } else {
            redirect(__ROOT__."/Style/header/upload.php?uid={$this->uid}");
        }
        exit;
    }
    //上传图片
    protected function CUpload()
    {
        if (!empty($_FILES)) {
            return $this->_Upload();
        }
    }

    protected function _Upload()
    {
        import("ORG.Net.UploadFile");
        $upload = new UploadFile();

        $upload->thumb = true;
        $upload->saveRule = $this->saveRule;//图片命名规则
        $upload->thumbMaxWidth = $this->thumbMaxWidth;
        $upload->thumbMaxHeight = $this->thumbMaxHeight;
        $upload->maxSize  = C('MEMBER_MAX_UPLOAD') ;// 设置附件上传大小
        $upload->allowExts  = C('MEMBER_ALLOW_EXTS');// 设置附件上传类型
        $upload->savePath =  $this->savePathNew?$this->savePathNew:C('MEMBER_MAX_UPLOAD');// 设置附件上传目录
        if (!$upload->upload()) {// 上传错误提示错误信息
            //$this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
        }

        return $info;
    }
    //上传图片END
    //会员登陆
    protected function _memberlogin($uid)
    {
        $member_model = M('members');
        $vo = $member_model->field("id,user_name")->find($uid);
        if (is_array($vo)) {
            foreach ($vo as $key=>$v) {
                session("u_{$key}", $v);
            }
            $up['uid'] = $vo['id'];
            $up['add_time'] = time();
            $up['ip'] = get_client_ip();
            M('member_login')->add($up);

            //修改最后登录时间
            $member_model->where(array('id' => $uid))->setField('last_log_time', time());
            $lup['id'] = $vo['id'];
            $lup['last_log_time'] = time();
            $lup['last_log_ip'] = get_client_ip();
            M('members')->save($lup);
            if (intval($_POST['Keep'])>0) {
                $time = intval($_POST['Keep'])*3600*24;
                $loginconfig = FS("Webconfig/loginconfig");
                $cookie_key = substr(md5($loginconfig['cookie']['key'].$uid), 14, 10);
                $cookie_val = $this->_authcode($uid, 'ENCODE', $loginconfig['cookie']['key']);
                cookie("UKey", $cookie_val, $time);
                cookie("Ukey2", $cookie_key, $time);
            }
        }
    }

    protected function _memberloginout()
    {
        $vo = array("id","user_name");
        foreach ($vo as $v) {
            session("u_{$v}", null);
        }
        session(null);
        cookie("Ukey", null);
        cookie("Ukey2", null);
        $this->assign("waitSecond", 3);
    }

    protected function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
            $ckey_length = 4;
            // 密匙
            $key = md5($key ? $key : "lzh_jiedai");
            // 密匙a会参与加解密
            $keya = md5(substr($key, 0, 16));
            // 密匙b会用来做数据完整性验证
            $keyb = md5(substr($key, 16, 16));
            // 密匙c用于变化生成的密文
            $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
            // 参与运算的密匙
            $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
            // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
            // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
            $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();

            // 产生密匙簿
            for ($i = 0; $i <= 255; $i++) {
                $rndkey[$i] = ord($cryptkey[$i % $key_length]);
            }

            // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
            for ($j = $i = 0; $i < 256; $i++) {
                $j = ($j + $box[$i] + $rndkey[$i]) % 256;
                $tmp = $box[$i];
                $box[$i] = $box[$j];
                $box[$j] = $tmp;
            }

            // 核心加解密部分
            for ($a = $j = $i = 0; $i < $string_length; $i++) {
                $a = ($a + 1) % 256;
                $j = ($j + $box[$a]) % 256;
                $tmp = $box[$a];
                $box[$a] = $box[$j];
                $box[$j] = $tmp;
                // 从密匙簿得出密匙进行异或，再转成字符
                $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
            }


        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 验证数据有效性
                // substr($result, 0, 10) - time() > 0 验证数据有效性
                // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
                // 验证数据有效性，请看未加密明文的格式
                if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                    return substr($result, 26);
                } else {
                    return '';
                }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
                // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
                return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    protected function del_mem_cach()
    {
        if (C("Cach.member_info")) {
            $path="html/member_info/".date("Ymd")."/";
            $filename=$this->uid.".html";
            unlink($path.$filename);
        }
    }

    // public function borrowidlayout1($borrowid,$protype){
    //     $newgrade = C('RENUMBER_BORROW.new_grade');
    //     if($borrowid<$newgrade){
    //         $bid="ZJ".$borrowid;
    //     }else{
    //         if($protype==1||$protype==2||$protype==3){
    //             $bid = M('borrow_pledge')->where("borrow_id=".$borrowid)->find();
    //             $bid="ZJ".$bid['id'];
    //         }else if($protype==4){
    //             $bid = M('borrow_finance')->where("borrow_id=".$borrowid)->find();
    //             $bid="RJ".$bid['id'];
    //         }else if($protype==6){
    //             $bid = M('borrow_credit')->where("borrow_id=".$borrowid)->find();
    //             $bid="XJ".$bid['id'];
    //         }
    //     }
    //     return $bid;
    // }
}
