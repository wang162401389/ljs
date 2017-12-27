<?php
// 本类由系统自动生成，仅供测试用途
class CommonAction extends MCommonAction
{
    public $notneedlogin=true;
    public function index()
    {
        $this->display();
    }

    public function login()
    {
        $loginconfig = FS("Webconfig/loginconfig");//判断快捷登录是否开启
        $title="链金所_账户登录";
        $keyword="链金所账户登录,链金所账户地址";
        $description="链金所_会员账户登录网址.";
        $mylogintype=$_GET["type"]?$_GET["type"]:0;
        $this->assign("logintype", $mylogintype);
        $this->assign("title", $title);
        $this->assign("keyword", $keyword);
        $this->assign("description", $description);
        $this->assign("loginconfig", $loginconfig);
        $this->display();
    }

    public function register(){
        $remid = $_REQUEST['invite'];

        //查询是否从融汇普网站島流  https://ccfax.cn/member/common/register?s=rph
        //如果是，写入数据库渠道来源
        $is_fromrph = $_REQUEST['s'];
        if ($is_fromrph == "rph") {
            session('is_from_rph', 1);
        }

        //检查注册是否利用ＣＰＳ
        checkSource();
        $start_time = strtotime(date("Y-m-d H:i:00",time()));
        $end_time = strtotime(date("Y-m-d H:i:59",time()));
        if ($_SESSION['recommend_id'] != null) {
            $remid = $_SESSION['recommend_id'];
        } elseif ($_SESSION["salesman_usrid"] != null) {
            $remid = $_SESSION["salesman_usrid"];
        } else {
            session("recommend_id", $remid);
        }
        if($remid){
           $reg_count =  M("members")->where(array("recommend_id"=>$remid,"reg_time"=>array("between",$start_time.",".$end_time)))->count();
            if($reg_count > 2){
                exit;
            }
        }
        if ($this->is_mobile()) {
            if (!empty($remid)) {
                redirect("/M/pub/regist.html?type=weixin&uid=".$remid);
            } else {
                redirect("/M/pub/regist.html?type=weixin");
            }
        }
        $this->assign('rename', $remid);
        // if($_GET['invite']){
        // 	//$uidx = M('members')->getFieldByUserName(text($_GET['invite']),'id');
        // 	if($uidx>0) session("recommend_id",$uidx);
        // 	session("tmp_invite_user",$_GET['invite']);
        // }
        $this->display();
    }

    //推广着陆
    public function tuiguangreg()
    {
        /********已合规平稳运营 累计注册人数 累计成交额 累计成交利息总额 ***************/
        // 注册人数统计
        import("@.Phpconectjava.usersapi");
        $users = new usersapi();
        $java_data["platform_source"] = 0;
        $java_result = $users->getmembercount($java_data);
        $count_list = json_decode($java_result, true);
        $resultText = json_decode($count_list["resultText"], true);
        $regnumber = 0;
        foreach ($resultText["Logincntlist"] as $key => $value) {
            $regnumber += $value["logincnt"];
        }
        $onlinetime  = "2015-10-7";
        // 当前时间
        $compliance  = (time() - strtotime($onlinetime)) / (24 *3600);

        $this->assign("compliance", intval($compliance));
        $this->assign("regnumber", $regnumber);
        $list = M("member_moneylog")->field('type,sum(affect_money) as money')->group('type')->select();
        $row=array();
        foreach ($list as $v) {
            $row[$v['type']]['money']= ($v['money']>0)?$v['money']:$v['money']*(-1);
        }
        $this->assign('list', $row);
        /********************************** **/
        $this->display();
    }

    /**
     * 　推广页面（注册送３０００体验金，实名送１００投资券)
     * @return [type] [description]
     */
    public function joinus(){
        import("@.Phpconectjava.usersapi");
        $users = new usersapi();
        $java_data["platform_source"] = 0;
        $java_result = $users->getmembercount($java_data);
        $count_list = json_decode($java_result, true);
        $resultText = json_decode($count_list["resultText"], true);
        $regnumber = 0;
        foreach ($resultText["Logincntlist"] as $key => $value) {
            $regnumber += $value["logincnt"];
        }
        $onlinetime  = "2015-10-7";
        // 当前时间
        $compliance  = (time() - strtotime($onlinetime)) / (24 *3600);

        $this->assign("compliance", intval($compliance));
        $this->assign("regnumber", $regnumber);
        $list = M("member_moneylog")->field('type,sum(affect_money) as money')->group('type')->select();
        $row=array();
        foreach ($list as $v) {
            $row[$v['type']]['money']= ($v['money']>0)?$v['money']:$v['money']*(-1);
        }
        $this->assign('list', $row);
        $this->display();
    }

    private function actlogin_bak()
    {
        (false!==strpos($_POST['sUserName'], "@"))?$data['user_email'] = text($_POST['sUserName']):$data['user_name'] = text($_POST['sUserName']);
        $vo = M('members')->field('id,user_name,user_email,user_pass')->where($data)->find();
        if ($vo) {
            $this->_memberlogin($vo['id']);
            ajaxmsg();
        } else {
            ajaxmsg("用户名不存在", 0);
        }
    }

    public function actlogin()
    {
        setcookie('LoginCookie', '', time()-10*60, "/");
        //uc登陆

        $loginconfig = FS("Webconfig/loginconfig");
        $uc_mcfg  = $loginconfig['uc'];
        if ($uc_mcfg['enable']==1) {
            require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
            require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
        }

        //uc登陆
//         if ($_SESSION['verify'] != md5(strtolower($_POST['sVerCode']))) {
//             ajaxmsg("验证码错误!", 0);
//         }

        // java统一接口登录
        $interface = C('UNIFY_INTERFACE.enable');

        // 非java统一接口登录
        if ($interface == 0) {
            $input = text($_POST['sUserName']);
            $data['user_name'] = $input;
            $data['user_email'] = $input;
            $data['user_phone'] = $input;
            $data['_logic'] = 'OR';
            $vo = M('members')->field('id,user_name,user_email,user_pass,is_ban')->where($data)->find();
            if ($vo['is_ban']==1) {
                ajaxmsg("您的帐户已被冻结，请联系客服处理！", 0);
            }

            if (!is_array($vo)) {
                //本站登陆不成功，偿试uc登陆及注册本站
                if ($uc_mcfg['enable'] == 1) {
                    list($uid, $username, $password, $email) = uc_user_login(text($_POST['sUserName']), text($_POST['sPassword']));
                    if ($uid > 0) {
                        $regdata['txtUser'] = text($_POST['sUserName']);
                        $regdata['txtPwd'] = text($_POST['sPassword']);
                        $regdata['txtEmail'] = $email;
                        $newuid = $this->ucreguser($regdata);

                        if (is_numeric($newuid) && $newuid > 0) {
                            $logincookie = uc_user_synlogin($uid);//UC同步登陆
                            setcookie('LoginCookie', $logincookie, time() + 10 * 60, "/");
                            $this->_memberlogin($newuid);
                            ajaxmsg();//登陆成功
                        } else {
                            ajaxmsg($newuid, 0);
                        }
                    }
                }
                //本站登陆不成功，偿试uc登陆及注册本站
                ajaxmsg("用户名或者密码错误！", 0);
            } else {
                if ($vo['user_pass'] == md5($_POST['sPassword'])) {//本站登陆成功，uc登陆及注册UC
                    //uc登陆及注册UC
                    if ($uc_mcfg['enable'] == 1) {
                        $dataUC = uc_get_user($vo['user_name']);
                        if ($dataUC[0] > 0) {
                            $logincookie = uc_user_synlogin($dataUC[0]);//UC同步登陆
                            setcookie('LoginCookie', $logincookie, time() + 10 * 60, "/");
                        } else {
                            $uid = uc_user_register($vo['user_name'], $_POST['sPassword'], $vo['user_email']);
                            if ($uid > 0) {
                                $logincookie = uc_user_synlogin($dataUC[0]);//UC同步登陆
                                setcookie('LoginCookie', $logincookie, time() + 10 * 60, "/");
                            }
                        }
                    }
                    //uc登陆及注册UC
                    $this->_memberlogin($vo['id']);
                    $url = session("login_next");
                    if (empty($url)) {
                        $url = "/member/";
                    }
                    Log::write($url);
                    ajaxmsg($url, 1);
                } else {//本站登陆不成功
                    ajaxmsg("用户名或者密码错误！", 0);
                }
            }
        } else {
            // java 统一接口登录
            $data['user_name']       = text($_POST['sUserName']);
            $data['user_pass']       = md5($_POST['sPassword']);
            // 平台来源
            $data['platform_source'] = 1;
            // 分销员ID
            $data['recommend_id']    = $_SESSION["salesman_usrid"];
            // 2级分销员ID
            $data['recommend_2_id']  = $_SESSION['salesman_2_usrid'];
            
            import("@.Phpconectjava.usersapi");
            $users = new usersapi();
            $vo1 = $users->logindo($data);
            $vo2 = json_decode($vo1, true);
            $vo3 = json_decode($vo2['resultText'], true);
            $vo['id'] = $vo2['code'];
            $vo['is_ban'] = $vo3['is_ban'];
            
            // 没有此用户
            if (is_null($vo['id'])) {
                Log::write("服务器失败");
                ajaxmsg('登录失败！', 0);
            }

            // 账户已冻结
            if ($vo['is_ban']==1) {
                ajaxmsg("您的帐户已被冻结，请联系客服处理！", 0);
            }

            // 账户正常
            if ($vo['id'] !== -1) {
                $map['id'] = $vo2['code'];
                $info = M('members')->where($map)->select();
                // java数据库正常, 但是本地数据库没有数据
                // 在本地数据库创建用户数据(相当于重新注册)
                if (!$info) {
                    $usrid['usr_id'] = $vo['id'];

                    // 使用java接口获取用户数据
                    $result = $users->getUsrinf($usrid);
                    $result1 = json_decode($result, true);

                    // 接口报错
                    if ($result1['code']==-1) {
                        ajaxmsg($result1['resultText'], 0);
                    }

                    $result2 = json_decode($result1['resultText'], true);

                    // 使用java接口返回推荐人信息
                    $recomuid =  json_decode($users->getRecommend($usrid), true);

                    // 组装用户信息
                    $memberinfo['id'] = $result2['usr_id'];
                    $memberinfo['user_name'] = $result2['user_name'];
                    $memberinfo['user_pass'] = $result2['user_pass'];
                    $memberinfo['user_regtype'] = null;
                    $memberinfo['user_email'] = $result2['user_email']?$result2['user_email']:'';
                    $memberinfo['user_phone'] = $result2['user_phone'];
                    $memberinfo['reg_ip'] = $result2['reg_ip']?$result2['reg_ip']:'';
                    if (empty($result2['equipment'])) {
                        $memberinfo['equipment'] = '';
                    } else {
                        $memberinfo['equipment'] = $result2['equipment'];
                    }
                    $memberinfo['reg_time'] =time();// strtotime($result2['reg_time']);
                    $memberinfo['last_log_ip'] = get_client_ip();
                    $memberinfo['last_log_time'] = time();
                    $memberinfo['is_ban']=$vo3['is_ban']?$vo3['is_ban']:0;
                    $memberinfo['is_borrow']=$vo3['is_borrow']?$vo3['is_borrow']:0;
                    $memberinfo['is_transfer']= $vo3['is_transfer']?$vo3['is_transfer']:0;
                    $memberinfo['is_vip'] = $vo3['is_vip']?$vo3['is_vip']:0;
                    if (empty($recomuid['recommend_id'])) {
                        $memberinfo['recommend_id'] = 0;
                    } else {
                        $memberinfo['recommend_id'] = $recomuid['recommend_id'];
                    }

                    // 创建用户
                    M('members') -> add($memberinfo);

                    // 新注册用户奖励
                    $reg['account_money'] = 0;//$global['reg_reward'];
                    $reg['uid'] = $result2['usr_id'];
                    M("member_money")->add($reg);

                    $updata['cell_phone'] = $result2['user_phone'];
                    $b = M('member_info')->where("uid = {$result2['usr_id']}")->count('uid');
                    if ($b == 1) {
                        M("member_info")->where("uid={$result2['usr_id']}")->save($updata);
                    } else {
                        $updata['uid'] = $result2['usr_id'];
                        $updata['cell_phone'] = $result2['user_phone'];
                        M('member_info')->add($updata);
                    }
                    $map['uid']=$result2['usr_id'];
                    $map['phone_status']=1;
                    M("members_status")->add($map);
                    
                    // 注册成功
                    regSuccess($result2['usr_id']);
                    
                } else {
                    // 错误检查
                    if ($info[0]['user_email']!=$data['user_name']&&$info[0]['user_phone']!=$data['user_name']&&$info[0]['user_name']!=$data['user_name']) {
                        Log::write("java返回ID：".$vo2['code']);
                        ajaxmsg('登录失败！', 0);
                    } else {
                        if (($info[0]['recommend_id'] == null || $info[0]['recommend_id'] == 0) && $_SESSION["salesman_usrid"] != null) {
                            $recdata["recommend_id"] = $_SESSION["salesman_usrid"];
                            M('members')->where("id={$map['id']}")->save($recdata);
                            $disdata['usrid'] = $recdata["recommend_id"];
                            $disdata['is_active'] = 1;
                            setDistribut($disdata);
                        }
                    }
                }
            } else {
                ajaxmsg($vo2['resultText'], 0);
            }

            if ($vo['id'] == -1) {
                //本站登陆不成功，偿试uc登陆及注册本站
                if ($uc_mcfg['enable']==1) {
                    echo1: exit;
                    list($uid, $username, $password, $email) = uc_user_login(text($_POST['sUserName']), text($_POST['sPassword']));
                    if ($uid > 0) {
                        $regdata['txtUser'] = text($_POST['sUserName']);
                        $regdata['txtPwd'] = text($_POST['sPassword']);
                        $regdata['txtEmail'] = $email;
                        $newuid = $this->ucreguser($regdata);

                        if (is_numeric($newuid)&&$newuid>0) {
                            $logincookie = uc_user_synlogin($uid);//UC同步登陆
                            setcookie('LoginCookie', $logincookie, time()+10*60, "/");
                            $this->_memberlogin($newuid);
                            ajaxmsg();//登陆成功
                        } else {
                            ajaxmsg($newuid, 0);
                        }
                    }
                }
                //本站登陆不成功，偿试uc登陆及注册本站
                ajaxmsg($vo2['resultText'], 0);
            } else {
                //本站登陆成功，uc登陆及注册UC
                if ($uc_mcfg['enable']==1) {
                    $dataUC = uc_get_user($vo['user_name']);
                    if ($dataUC[0] > 0) {
                        $logincookie = uc_user_synlogin($dataUC[0]);//UC同步登陆
                        setcookie('LoginCookie', $logincookie, time()+10*60, "/");
                    } else {
                        $uid = uc_user_register($vo['user_name'], $_POST['sPassword'], $vo['user_email']);
                        if ($uid>0) {
                            $logincookie = uc_user_synlogin($dataUC[0]);//UC同步登陆
                            setcookie('LoginCookie', $logincookie, time()+10*60, "/");
                        }
                    }
                }
                //uc登陆及注册UC
                $this->_memberlogin($vo['id']);
                $url = session("login_next");
                if (empty($url)) {
                    $url = "/member/";
                }
                $logintype=$_POST["logintype"];

                if (session("lastpcid")&& $logintype==1) {//普通标的
                    $url = "/invest/".session("lastpcid").".html";
                    session("lastpcid", null);
                } elseif (session("lastpcid")&& $logintype==2) {//债权转让
                    $url = "/debthome/debtdetail?id=".session("lastpcid");
                    session("lastpcid", null);
                }
                
                //single_login
                import("@.conf.single_login");
                $single= single_login::getInstance();
                $single->login($vo["id"]);
                Log::write($url);
                ajaxmsg($url, 1);
            }
        }
    }

    public function actlogout()
    {
        $this->_memberloginout();
        //uc登陆
        $loginconfig = FS("Webconfig/loginconfig");
        $uc_mcfg  = $loginconfig['uc'];
        if ($uc_mcfg['enable']==1) {
            require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
            require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
            $logout = uc_user_synlogout();
        }
        //uc登陆
        $this->assign("uclogout", de_xie($logout));
        $this->assign('waitSecond', '1');
        $this->success("注销成功", __APP__."/");
    }

    //UC注册
    private function ucreguser($reg)
    {
        $data['user_name'] = text($reg['txtUser']);
        $data['user_pass'] = md5($reg['txtPwd']);
        // $data['user_email'] = text($reg['txtEmail']);
        // $count = M('members')->where("user_email = '{$data['user_email']}' OR user_name='{$data['user_name']}'")->count('id');
        // if($count>0) return "注册失败,UC用户名冲突,用户名或者邮件已经有人使用";
        $data['reg_time'] = time();
        $data['reg_ip'] = get_client_ip();
        $data['last_log_time'] = time();
        $data['last_log_ip'] = get_client_ip();
        $newid = M('members')->add($data);

        if ($newid) {
            session('u_id', $newid);
            session('u_user_name', $data['user_name']);
            return $newid;
        }
        return "登陆失败,UC用户名冲突";
    }

    //reg.js传值过来保存session
    public function regtemp()
    {
        session('txt_phone', text($_POST['txt_phone']));
        // session('user_regtype',text($_POST['user_regtype']));
        // session('user_regtype',text($_POST['user_regtype']));
        // session('email_temp',text($_POST['txtEmail']));
        //if(session("tmp_invite_user") == null){
            session('rec_temp', text($_POST['txtRec']));
        //}
        session('name_temp', text($_POST['txt_phone']));
        session('pwd_temp', md5($_POST['txtPwd']));
        session('code_temp', $_POST['sVerCode']);
        // 注册
        $updata['phone_status'] = 1;
        $mid = $this->regaction();
        setMemberStatus($mid, 'phone', 1, 10, '手机');
        ajaxmsg();
    }

    //开始注册
    public function regaction()
    {   
        if(session("recommend_id")){
           $reg_count =  M("members")->where(array("recommend_id"=>session("recommend_id"),"reg_time"=>array("between",$start_time.",".$end_time)))->count();
            if($reg_count > 2){
                exit;
            }
        }
        $data['txt_phone'] = session('txt_phone');
        $data['user_regtype'] = session('user_regtype');
        $data['user_email'] = session('email_temp');
        $data['user_name'] = session('name_temp');
        $data['user_pass'] = session('pwd_temp');
        $data['code'] = session('code_temp');
        if (session('temp_phone')) {
            $data['user_phone'] = session('temp_phone');
        }

        $check_res = $this->regProtection(session('txt_phone'),$data['user_pass'],get_client_ip());
        if(!$check_res){
            ajaxmsg('帐号异常', 0);
        }
        //通过uc注册开始
        $loginconfig = FS("Webconfig/loginconfig");
        $uc_mcfg  = $loginconfig['uc'];
        if ($uc_mcfg['enable']==1) {
            require_once C('APP_ROOT')."Lib/Uc/config.inc.php";
            require C('APP_ROOT')."Lib/Uc/uc_client/client.php";
            $uid = uc_user_register($data['user_name'], $_POST['txtPwd'], $data['user_email']);
            if ($uid <= 0) {
                if ($uid == -1) {
                    ajaxmsg('用户名不合法', 0);
                } elseif ($uid == -2) {
                    ajaxmsg('包含要允许注册的词语', 0);
                } elseif ($uid == -3) {
                    ajaxmsg('用户名已经存在', 0);
                } elseif ($uid == -4) {
                    ajaxmsg('Email 格式有误', 0);
                } elseif ($uid == -5) {
                    ajaxmsg('Email 不允许注册', 0);
                } elseif ($uid == -6) {
                    ajaxmsg('该 Email 已经被注册', 0);
                } else {
                    ajaxmsg('未定义', 0);
                }
            }
        }
       // 新注册用户奖励
        $global = get_global_setting();
        $data['reward_money'] = 0;// $global['reg_reward'];
        $data['is_reward'] = '1';
        if (session("tmp_invite_user") != null) {
            $data['recommend_id'] = session("tmp_invite_user");
        } elseif ($_SESSION["salesman_usrid"] != null) {
            $data['recommend_id'] = $_SESSION["salesman_usrid"];
        } elseif (session('rec_temp')) {
            $Rectemp = session('rec_temp');
            $Retemp1 = M('members')->field("id")->where("id = {$Rectemp} OR user_phone = ".$Rectemp)->find();
            if ($Retemp1['id'] > 0) {
                $data['recommend_id'] = $Retemp1['id'];//推荐人为投资人
            }
        }

        // 通过uc注册结束
        $interface = C('UNIFY_INTERFACE.enable');
        if ($interface == 0) {
            $data['reg_time'] = time();
            $data['reg_ip'] = get_client_ip();
            $data['last_log_time'] = time();
            $data['last_log_ip'] = get_client_ip();
            $newid = M('members')->add($data);
        } else {
            //调用接口
            import("@.Phpconectjava.usersapi");
            $users = new usersapi();
            $data["platform_source"] = 1;
            $data["equipment_source"] = 1;
            $data["recommend_2_id"] = $_SESSION["salesman_2_usrid"];
            $res = $users->regdo($data);
            Log::write(var_export($res, true));
            $res1 = json_decode($res, true);
            if (is_null($res1['code'])) {
                Log::write("服务器失败");
                ajaxmsg('注册失败！', 0);
            }
            if ($res1['code']==-1) {
                ajaxmsg($res1['resultText'], 0);
            }
            if ($res1['code']!==-1) {
                $newid = $res1['code'];
                Log::write("java注册用户ID为{$newid}");
            }
            $data['id'] = $newid;
            $data['reg_time'] = time();
            $data['last_log_time'] = time();

            //保存本地数据库，区分融普惠
            logw(' -------------------------check from rpu ='.session('is_from_rph'));
            if (session('is_from_rph')==1) {
                logw(' --------------------xxxxx');
                $data['equipment'] = 'rph';
            }

            $data = cpsData($data);
            logw(' data = '.json_encode($data));
            M('members')->add($data);
        }

        //分销效果更新
          $disdata['usrid'] = $_SESSION["salesman_usrid"];
        $disdata['is_active'] = 1;
        setDistribut($disdata);

        // 新注册用户奖励
        if (session('jk_flag_'.$data['user_phone']) != 1) {
            $reg['account_money'] = 0;//$global['reg_reward'];
            $reg['uid'] = $newid;
            M("member_money")->add($reg);
        }

        if ($newid) {
            $updata['cell_phone'] = session("temp_phone");

            $b = M('member_info')->where("uid = {$newid}")->count('uid');
            if ($b == 1) {
                M("member_info")->where("uid={$newid}")->save($updata);
            } else {
                $updata['uid'] = $newid;
                $updata['cell_phone'] = session("temp_phone");
                if (session("idcard".session('temp_phone'))) {
                    $updata['idcard'] = session("idcard_".session('temp_phone'));
                }
                M('member_info')->add($updata);
            }

            if (session('jk_flag_'.$data['user_phone']) != 1) {
                // 非借款通道用户,注册送体验金,已废弃
            } else {
                if (session('temp_phone')) {
                    if (session('clear_pwd_temp')) {
                        $phone = session('temp_phone');
                        sendsms($phone, "你好，您的手机号".$phone."的默认密码为".session('clear_pwd_temp').",请及时登录炼金所进行密码修改。");
                    }
                }
            }


            // 注册成功
            regSuccess($newid);

            //借款通道自动注册完成后不登录
            if (session('jk_flag_'.$data['user_phone']) != 1) {
                session('u_id', $newid);
                session('u_user_name', $data['user_name']);
            }

            if (strtotime(C("THE_MAY_ACTIVE.start_time"))<=time() && strtotime(C("THE_MAY_ACTIVE.end_time"))>=time()) {
                if ($Retemp1['id'] > 0) {
                    $rec_act_data["recommend_uid"] = $Retemp1['id'];
                    $rec_act_data["invest_uid"] = $newid;
                    M("recommend_invest")->add($rec_act_data);
                    $is_recommed_list = M("recommend_first")->where(array("recommend_uid"=>session("regiser_uid")))->count();
                    if (!$is_recommed_list) {
                        $first_data["recommend_uid"] = session("regiser_uid");
                        M("recommend_first")->add($first_data);
                    }
                }
            }

            //注册成功后邮件，可点邮件连接认证
            // Notice(1,$newid);
            return $newid;
        }
    }

    /**
     * chase your dream activity
     * @param  [type] $money [description]
     * @param  [type] $uid   [description]
     * @return [type]        [description]
     */
    private function chaseYourDream($uid)
    {
        $glo = get_global_setting();
        $start = $glo['dream_start_time'];
        $end = $glo['dream_end_time'];
        $status = $glo['dream_status'];

        if ($status == 0) {
            //closed
            return ;
        }

        if ($start > time()) {
            //not ready
            return ;
        }

        if ($end < time()) {
            //is over
            return;
        }

        $savedata['dream_feeds'] = "3";
        $result = M('members')->where(array('id'=>$uid))->save($savedata);
        if ($result) {
            $logdata['create_time'] = time();
            $logdata['desc'] ="{$uid} register , 3 dream feeds ";
            $logdata['type'] = 3;
            M('dream_log')->add($logdata);
        }
    }

    /**
     * A 轮融资推荐人活动
     * @param  [type] $uid       [description]
     * @param  [type] $parent_id [description]
     * @return [type]            [description]
     */
    private function vcRecommend($uid,$parent_id,$user_phone)
    {
        //检查时间,如果没到时间,直接返回
        if((time()<=C('VC_FROM'))||(time()>=C('VC_TO')))
        {
            return false;
        }
        
        $query['uid'] = $uid;
        $query['parent_id'] = $parent_id==null?0:$parent_id;
        $query['user_phone'] = $user_phone;
        $isExist = M('vc_recom')->where($query)->find();
        if ($isExist == null)
        {
            $query['create_time']  = time();
            $res = M('vc_recom')->add($query);
        }else{
        }
    }

    private function check_sendphone()
    {
        if (!$_SERVER['HTTP_USER_AGENT']) {
            die("请稍后再试");
        }
        $cur = strtotime("now");
        if (!isset($_SESSION['phone_time'])) {
            session("phone_time", $cur);
        } else {
            $before=session("phone_time");
            if (($cur-$before)>30) {
                session("phone_time", null);
            } else {
                $left=30-($cur-$before);
                ajaxmsg("请稍后再试", 3);
            }
        }
    }

    public function sendphone1()
    {
        $this->check_sendphone();
        if ($_SESSION['verify'] != md5($_POST['sVerCode'])) {
            ajaxmsg("验证码错误", 3);
        }
        $smsTxt = FS("Webconfig/smstxt");
        $smsTxt = de_xie($smsTxt);
        $phone = text($_POST['cellphone']);
        $xuid = M('members') -> getFieldByUserPhone($phone, 'id');

        if ($xuid > 0 && $xuid <> $this -> uid) {
            ajaxmsg("", 2);
        }

        $code = rand_string_reg(6, 1, 2);
        $datag = get_global_setting();
        $is_manual = $datag['is_manual'];
        if ($is_manual == 0) { // 如果未开启后台人工手机验证，则由系统向会员自动发送手机验证码到会员手机，
            $res = sendsms($phone, str_replace(array("#UserName#", "#CODE#"), array(session('u_user_name'), $code), $smsTxt['verify_phone']));
            // dump($smsTxt['verify_phone']);die;
        } else { // 否则，则由后台管理员来手动审核手机验证
            $res = true;
            $phonestatus = M('members_status') -> getFieldByUid($this -> uid, 'phone_status');
            if ($phonestatus == 1) {
                ajaxmsg("手机已经通过验证", 1);
            }
            $updata['phone_status'] = 3; //待审核
            $updata1['user_phone'] = $phone;
            $a = M('members') -> where("id = {$this->uid}") -> count('id');
            if ($a == 1) {
                $newid = M("members") -> where("id={$this->uid}") -> save($updata1);
            } else {
                M('members') -> where("id={$this->uid}") -> setField('user_phone', $phone);
            }

            $updata2['cell_phone'] = $phone;
            $b = M('member_info') -> where("uid = {$this->uid}") -> count('uid');
            if ($b == 1) {
                $newid = M("member_info") -> where("uid={$this->uid}") -> save($updata2);
            } else {
                $updata2['uid'] = $this -> uid;
                $updata2['cell_phone'] = $phone;
                M('member_info') -> add($updata2);
            }
            $c = M('members_status') -> where("uid = {$this->uid}") -> count('uid');
            if ($c == 1) {
                $newid = M("members_status") -> where("uid={$this->uid}") -> save($updata);
            } else {
                $updata['uid'] = $this -> uid;
                $newid = M('members_status') -> add($updata);
            }
            if ($newid) {
                ajaxmsg();
            } else {
                ajaxmsg("验证失败", 0);
            }
            // ////////////////////////////////////////////////////////////
        }

        if ($res) {
            session("temp_phone", $phone);
            ajaxmsg("", 1);
        } else {
            ajaxmsg("", 0);
        }
    }

    public function validatephone()
    {
        if (session('code_temp')==text($_POST['code'])) {
            if (!session("temp_phone")) {
                ajaxmsg("验证失败", 0);
            }
            ajaxmsg("验证成功", 1);
        } else {
            ajaxmsg("验证校验码不对，请重新输入！", 2);
        }
    }

    public function emailverify()
    {
        $code = text($_GET['vcode']);
        $uk = is_verify(0, $code, 1, 60*1000);
        if (false===$uk) {
            $this->error("验证失败");
        } else {
            $this->assign("waitSecond", 3);
            setMemberStatus($uk, 'email', 1, 9, '邮箱');
            $this->success("验证成功", __APP__."/member");
        }
    }

    public function getpasswordverify()
    {
        if ($_POST["type"]=1) {
            if (session("code_temp") != $_POST["code"]) {
                $this->error("验证失败");
            } else {
                $code = session('vcode');
            }
        } else {
            $code = text($_GET['vcode']);
        }

        $uk = is_verify(0, $code, 7, 60*1000);
        if (false===$uk) {
            $this->error("验证失败");
        } else {
            session("temp_get_pass_uid", $uk);
            $this->display('getpass');
        }
    }

    public function setnewpass()
    {
        $d['content'] = $this->fetch();
        echo json_encode($d);
    }

    public function dosetnewpass()
    {
        $per = C('DB_PREFIX');
        $uid = session("temp_get_pass_uid");
        $oldpass = M("members")->getFieldById($uid, 'user_pass');
        $username = M("members")->getFieldById($uid, 'user_name');

        $params['usr_id']=$uid;
        $params['user_name'] = $username;
        $params['user_pass'] = $oldpass;
        $params['user_pass_new'] = md5($_POST['pass']);
        $params['is_checkoldpass'] = 0;
        import("@.Phpconectjava.usersapi");
        $users = new usersapi();
        $vo = $users->setUsrpwd($params);
        $vo1 = json_decode($vo, true);
        $vo2 = json_decode($vo1['resultText'], true);
        if (is_null($vo1['code'])) {
            Log::write("服务器失败");
            ajaxmsg('修改失败！', 0);
        }
        if ($vo1['code']==-1) {
            ajaxmsg($vo1['resultText'], 0);
        }

        if ($oldpass == md5($_POST['pass'])) {
            $newid = true;
        } else {
            $newid = M()->execute("update {$per}members set `user_pass`='".md5($_POST['pass'])."' where id={$uid}");
        }

        if ($newid) {
            session("temp_get_pass_uid", null);
            ajaxmsg();
        } else {
            ajaxmsg('', 0);
        }
    }

    public function ckuser()
    {
        $map['user_name'] = text($_POST['UserName']);
        $count = M('members')->where($map)->count('id');

        if ($count>0) {
            $json['status'] = 0;
            exit(json_encode($json));
        } else {
            $json['status'] = 1;
            exit(json_encode($json));
        }
    }

    public function ckemail()
    {
        $map['user_email'] = text($_POST['Email']);
        $count = M('members')->where($map)->count('id');

        if ($count>0) {
            $json['status'] = 0;
            exit(json_encode($json));
        } else {
            $json['status'] = 1;
            exit(json_encode($json));
        }
    }

    public function emailvsend()
    {
        session('email_temp', text($_POST['email']));
        $mid = $this->regaction();

        $status=Notice(8, $mid);
        if ($status) {
            ajaxmsg('邮件已发送，请注意查收！', 1);
        } else {
            ajaxmsg('邮件发送失败,请重试！', 0);
        }
    }

    public function ckcode()
    {
        if ($_SESSION['verify'] != md5($_POST['sVerCode'])) {
            echo(0);
        } else {
            echo(1);
        }
    }

    public function verify()
    {
        import("ORG.Util.Image");
        Image::GBCal();
    }

    public function verify2()
    {
        import("ORG.Util.Image");
        Image::GBCal();
    }

    public function regsuccess()
    {
        // $this->assign('userEmail',M('members')->getFieldById($this->uid,'user_email'));
        // $d['content'] = $this->fetch();
        // echo json_encode($d);
        $this->display();
    }

    public function getpassword()
    {
        $d['content'] = $this->fetch();
        echo json_encode($d);
    }

    public function dogetpass()
    {
        (false!==strpos($_POST['u'], "@"))?$data['user_email'] = text($_POST['u']):$data['user_name'] = text($_POST['u']);
        $vo = M('members')->field('id,user_name')->where($data)->find();
        if ($data['user_name']) {
            if ($data['user_name']!=$vo['user_name']) {
                Log::write("用户输入：".$data['user_name']."数据库用户名：".$vo['user_name']);
                ajaxmsg('', 0);
            }
        }
        if (is_array($vo)) {
            $res = Notice(7, $vo['id']);
            if ($res) {
                ajaxmsg();
            } else {
                ajaxmsg('', 0);
            }
        } else {
            ajaxmsg('', 0);
        }
    }

    public function register2()
    {
        $this->display();
    }

    public function phone()
    {
        $this->assign("phone", $_GET['phone']);
        $data['content'] = $this->fetch();
        exit(json_encode($data));
    }

    //跳过手机验证
    public function skipphone()
    {
        $this->regaction();
        ajaxmsg();
    }

    //推荐人检测
    public function ckInviteUser()
    {
        //$map['user_name'] = text($_POST['InviteUserName']);
        //$map2['user_name'] = text($_POST['InviteUserName']);
        //$map2['u_group_id'] = 26;
        $where = text($_POST['InviteUserName']);
        $count = M('members')->where("id = {$where} OR user_phone = ".$where)->count('id');
        //$count2 = M('ausers')->where($map2)->count('id');

        if ($count==1) {
            $json['status'] = 1;
            exit(json_encode($json));
        } else {
            $json['status'] = 0;
            exit(json_encode($json));
        }
    }

    public function forget_pass()
    {
        $user_name=htmlspecialchars($_POST['user_name']);
        $map['user_phone']=$user_name;
        $result=M("members")->where($map)->limit(1)->select();
        if (!isset($result[0]['user_phone'])) {
            echo 2;
        } else {
            $smsTxt = FS("Webconfig/smstxt");
            $smsTxt = de_xie($smsTxt);
            $code = rand_string_reg(6, 1, 2);
            $phone=$result[0]['user_phone'];
            $res = sendsms($phone, str_replace(array("#USERANEM#", "#CODE#"), array($result[0]['user_name'], $code), $smsTxt['forget_password']));
            if ($res) {
                $vcode = rand_string($result[0]["id"], 32, 0, 7);
                session("vcode", $vcode);
                session("code_temp", $code);
                session("user_name", $user_name);
                echo 1;
            } else {
                echo 4;
            }
        }
        exit();
    }

    private function is_mobile()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    private function regProtection($phone,$pwd,$ip){
        if(!C("TIANYU.enable")){
            return true;
        }
        import("@.Oauth.tianyu.TencentProtection");
        $TencentProtection = new TencentProtection();
        $result =  $TencentProtection->RegisterProtection($phone,$ip,$pwd);
        $flag = true;
        if($result["level"] > 2){
            // 帐号异常
            $flag = false;
        }else{
            // if(in_array(1,$result["riskType"])){
            //     // 帐号信用低
            //     $flag = true;
            // }elseif (in_array(2,$result["riskType"])) {
            //     // 垃圾帐号
            //     $flag = true;
            // }else
            if (in_array(3,$result["riskType"])) {
                // 无效帐号
                $flag = false;
            }elseif (in_array(4,$result["riskType"])) {
                // 黑名单
                $flag = false;
            }elseif (in_array(101,$result["riskType"])) {
                // 批量操作
                $flag = false;
            }elseif (in_array(102,$result["riskType"])) {
                // 自动机
                $flag = false;
            }elseif (in_array(201,$result["riskType"])) {
                // 环境异常
                $flag = false;
            }elseif (in_array(202,$result["riskType"])) {
                // js上报异常
                $flag = false;
            }elseif (in_array(203,$result["riskType"])) {
                // 撞库
                $flag = false;
            }
        }

        return $flag;
    }
}
