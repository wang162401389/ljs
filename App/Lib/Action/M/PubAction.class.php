<?php
    class PubAction extends Action
    {

        private function do_register_ext()
        {
            /*****************************
             * 1:3月份开始的常规推广
             * 2:线下商铺推广
             */
            $from=array("default"=>0,"weixin"=>1,"shop"=>2,"apr"=>3);
            $type=$_GET['type'];
            if ($type=="") {
                $type=session("regiser_type");
                if ($type=="") {
                    return;
                }
            }

            $uid=intval($_GET['uid']);
            if ($uid=="") {
                $uid=session("regiser_uid");
                if ($uid=="") {
                    return;
                }
            }
            switch ($type) {
                case 'weixin':
                    if ($uid!=0) {
                        $where['id']=$uid;
                        $info=M('members')->field("user_name")->where($where)->select();
                        if ($info[0]['user_name']) {
                            $this->assign("tuijian", $info[0]['user_name']);
                        }
                        session("regiser_type", $type);
                        session("regiser_uid", $uid);
                    }
                    session("register_from", $from['weixin']);
                    break;
                case 'shop':
                    session("register_from", $from['shop']);
                    session("arg1", $_GET["uid"]);
                    break;
                case 'apr':
                    $where['id']=intval($uid);
                    $info=M('members')->field("user_name")->where($where)->select();
                    if ($info[0]['user_name']) {
                        $this->assign("tuijian", $info[0]['user_name']);
                    }
                    session("register_from", $from['shop']);
                    session("arg1", $_GET["uid"]);
                    break;
                default:
                    session("register_from", $from['default']);
            }
        }

        /**
         * 用户登陆
         */
         public function login()
         {
             $hetong = M('hetong')->field('name,dizhi,tel')->find();
             $this->assign("web", $hetong);
             if ($this->isPost()) {
                 //[username] => dsfsaf [password] => asdf [verify] => mebr
                 // if($_SESSION['verify'] != md5($_POST['verify'])) {
                 //   $this->error('验证码错误！');
                 // }
                 $token = $this->_post('token');
                 if (!check_token("login", $token)) {
                     $this->redirect('M/pub/login');
                 }
                 $interface = C('UNIFY_INTERFACE.enable');
                 if ($interface == 0) {
                     $user_name = $this->_post('username');
                     $pass = $this->_post('password');
                     $vo = M('members')->field('id,user_name,user_email,user_pass,is_ban,user_regtype')->where("user_name='{$user_name}' OR user_phone='{$user_name}'")->find();
                     if (!$vo) {
                         echo("<script language='javascript'>window.onload=function(){alert('没有此用户！');location.reload()}</script>");
                         exit();
                     }
                     if ($vo['is_ban'] == 1) {
                         echo("<script language='javascript'>window.onload=function(){alert('您的帐户已被冻结，请联系客服处理！');location.reload()}</script>");
                         exit();
                     }
                     if ($vo['user_pass'] != md5($pass)) {
                         echo("<script language='javascript'>window.onload=function(){alert('密码错误，请重新输入！');location.reload()}</script>");
                         exit();
                     }
                     if ($vo['user_regtype'] == 2) {
                         echo("<script language='javascript'>window.onload=function(){alert('企业用户暂不支持手机端登录');location.reload()}</script>");
                         exit();
                     }

                     session('u_id', $vo['id']);
                     session('u_user_name', $vo['user_name']);
                     $url = session("login_next");
                     if (empty($url)) {
                         $this->redirect($url);
                     }
                 } else {
                     $data['user_name'] = $this->_post('username');
                     $data['user_pass'] = md5($this->_post('password'));
                     $data['platform_source'] = 1;  //平台来源
                    $data['recommend_id'] = $_SESSION["salesman_usrid"];//分销员ID
                    $data['recommend_2_id'] = $_SESSION['salesman_2_usrid'];//2级分销员ID
                     import("@.Phpconectjava.usersapi");
                     $users = new usersapi();
                     $vo1 = $users->logindo($data);
                     Log::write(var_export($vo1, true));
                     $vo2 = json_decode($vo1, true);
                     $vo3 = json_decode($vo2['resultText'], true);
                     $vo['id'] = $vo2['code'];
                     $vo['is_ban'] = $vo3['is_ban'];
                     if (is_null($vo['id'])) {
                         Log::write("服务器失败");
                         echo("<script language='javascript'>window.onload=function(){alert('登录失败！');location.reload()}</script>");
                         exit();
                     }
                     if ($vo['id'] !== -1) {
                         $map['id'] = $vo2['code'];
                         $info = M('members')->where($map)->select();
                         if (!$info) {
                             $usrid['usr_id'] = $vo['id'];
                             $result = $users->getUsrinf($usrid);
                             $result1 = json_decode($result, true);
                             $result2 = json_decode($result1['resultText'], true);
                             if ($result1['code']==-1) {
                                 echo("<script language='javascript'>window.onload=function(){alert('{$result1['resultText']}');location.reload()}</script>");
                                 exit();
                             }
                             $recomuid =  json_decode($users->getRecommend($usrid), true);
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
                             M('members') -> add($memberinfo);
                             Log::write(var_export($result, true));
                             $map['uid']=$result2['usr_id'];
                             $map['phone_status']=1;
                             if (M("members_status")->add($map)) {
                                 $mem['uid']=$result2['usr_id'];
                                 $mem['cell_phone']=$result2['user_phone'];
                                 if (M("member_info")->add($mem)) {
                                     /********************一块购或者全木行新用户登录过来相当于注册新用户，注册送体验金**************************** */
                                     $coup['user_phone'] = $result2['user_phone'];
                                     $coup['money'] = C("EXPERIENCE_MONEY");//体验金
                                     $coup['endtime'] =(C("EXPERIENCE_DURATION")-1)*24*3600+time();//体验金的期限,减去当前天数
                                     $coup['status'] = '0';
                                     $coup['serial_number'] = date('YmdHis', time()).mt_rand(100000, 999999);
                                     $coup['type'] = '2';
                                     $coup['name'] = '新用户注册送体验金';
                                     $coup['addtime'] = date("Y-m-d H:i:s", time());
                                     $coup['isexperience'] = '1';
                                     M('coupons')->add($coup);
                                     sendsms($result2['user_phone'], "【链金所】尊敬的用户,感谢您选择链金所！".C("EXPERIENCE_MONEY")."元体验金已送达您的账户，实名认证后即可参与新手体验，开启您的财富之旅。详询客服中心：400-6626-985");
                                     /************************************* *************/
                                    
                                    // 20170727 注册68元红包
                                    //redPacketActivity($result2['uid']);

                                    //regSuccess($result2['uid']);

                                    

                                     // 新注册用户奖励
                                     $reg['account_money'] = 0;//"10.00";
                                     $reg['uid'] = $result2['usr_id'];
                                     M("member_money")->add($reg);
                                     //Notice(1,$result2['usr_id'],array('email',$data['user_email']));
                                     session('u_id', $result2['usr_id']);
                                     session('u_user_name', $result2['user_phone']);
                                 } else {
                                     $this->del_register_info($result2['usr_id'], 1);
                                     echo("<script language='javascript'>window.onload=function(){alert('登录失败！');location.reload()}</script>");
                                     exit();
                                 }
                             } else {
                                 $this->del_register_info($result2['usr_id'], 0);
                                 echo("<script language='javascript'>window.onload=function(){alert('登录失败！');location.reload()}</script>");
                                 exit();
                             }
                             if ($data["from"] == 2) {
                                 $content ="好汉请留步！平台要实名认证后才可以把赚到的钱存入账户哦，请您在手机端页面依次点击：我的账户-请输入用户名及密码-点击登录-账户设置-输入您的姓名及身份证号码即可完成实名认证操作，如有疑问请联系客服电话：400-6626-985。";
                                 sendsms($result2["user_phone"], $content);
                             }
                         } else {
                             if ($info[0]['user_email']!=$data['user_name']&&$info[0]['user_phone']!=$data['user_name']&&$info[0]['user_name']!=$data['user_name']) {
                                 Log::write("java返回ID：".$vo2['code']);
                                 echo("<script language='javascript'>window.onload=function(){alert('登录失败！');location.reload()}</script>");
                                 exit();
                             }
                         }
                     }
                     $minfo = M('members')->field('user_name,user_email,user_pass,user_regtype')->where($map)->find();
                     if ($vo['id'] == -1) {
                         echo("<script language='javascript'>window.onload=function(){alert('用户名或密码错误！');location.reload()}</script>");
                         exit();
                     }
                     if ($vo['is_ban'] == 1) {
                         echo("<script language='javascript'>window.onload=function(){alert('您的帐户已被冻结，请联系客服处理！');location.reload()}</script>");
                         exit();
                     }
                     if ($minfo['user_regtype'] == 2) {
                         echo("<script language='javascript'>window.onload=function(){alert('企业用户暂不支持手机端登录');location.reload()}</script>");
                         exit();
                     }

                     import("@.conf.single_login");
                     $single= single_login::getInstance();
                     $single->login($vo["id"]);

                     session('u_id', $vo['id']);
                     session('u_user_name', $minfo['user_name']);
                     $lup['id'] = $vo['id'];
                     $lup['last_log_time'] = time();
                     $lup['last_log_ip'] = get_client_ip();
                     M('members')->save($lup);
                     $url = session("login_next");

                     $fxpg_popup_status = M('members_status')->where(array("uid" => $vo['id']))->getField('fxpg_popup_status');
                     $risk = M("risk_result")->where(array("uid" => $vo['id']))->limit(1)->find();
                     $fxpg_popup_status = empty($risk) ? $fxpg_popup_status : 1;
                     session('fxpg_popup_status'.$vo['id'], $fxpg_popup_status);

                     if ($fxpg_popup_status == 0) {
                         $url = U('M/fengxian/index', array('source' => 3), '');
                         $this->redirect($url);
                     } elseif (empty($url)) {
                         $this->redirect($url);
                     }
                 }

                 $logintype=$_POST["logintype"];
                 $og=session("lastphone");
                 if ($og&&$logintype==1) {
                     $myurl="/M/invest/detail/id/".$og;
                     session('lastphone', null);
                     $this->redirect($myurl);
                 } else {
                     $this->redirect('M/user/index');
                 }
                 /**
                 $JumpUrl = session('JumpUrl')?session('JumpUrl'):'/M/user/index';
                 session('JumpUrl','');
                 redirect($JumpUrl);
                ***/
             } else {
                 $logintype=$_GET["type"]?1:0;
                 if (session('u_id')) {
                     $og=session("lastphone");
                     if ($og) {
                         $myurl="/M/invest/detail/id/".$og;
                         session('lastphone', null);
                         $this->redirect($myurl);
                     } else {
                         $this->redirect('M/user/index');
                     }
                 }
                 $this->assign("token", create_token("login"));
                 session('JumpUrl', $_SERVER['HTTP_REFERER']);
                 $this->assign("tab", "user");
                 $this->assign("logintype", $logintype);
                 $this->display();
             }
         }
         /**
         * 注销用户
         */
         public function logout()
         {
             session(null);
             $this->redirect('M/pub/login');
         }

         /**
         * 用户注册
         *
         */
        private function del_register_info($id, $level=0)
        {
            $del['id']=$id;
            M("members")->where($del)->delete();
            if ($level==1) {
                $del1['uid']=$id;
                M("members_status")->where($del1)->delete();
            }
        }

        /**
         * 注册
         * @return [type] [description]
         */
        public function regist()
        {
            //如果是从融普惠島流而来，需要标记用户

            $is_fromrph = $_REQUEST['s'];
            if ($is_fromrph == "rph") {
                session('is_from_rph', 1);
            }
            
            checkSource();

             if($_REQUEST['equipment'] == "pt"){
                session('source_pt',trim($_REQUEST['equipment']));
             }else{
                session('source_pt',trim($_REQUEST['equipment']));
             }
            $hetong = M('hetong')->field('name,dizhi,tel')->find();
            $this->assign("web", $hetong);
            if (session('u_id')) {
                $this->redirect('M/user/index');
            }
            if ($this->isAjax()) {
                $token = $this->_post('token');
                if (!check_token("register", $token)) {
                    die("请刷新");
                }
                $phone = $this->_post('phone');
                $username = $this->_post('username');
                $password = $this->_post('password');
                $verify = $this->_post('verify');
                $other_name=$this->_post("other_name");
                $fubabaid = 0;
                if (session('is_from_rph') == 1) {
                    $equipment="rph";
                } elseif (session('utmsource')=='fubaba') {
                    $equipment = 'fubaba';
                    $fubabaid = session('utmid');
                }elseif(isset($_SESSION['utmsource'])&&isset($_SESSION['utmid'])){
                    $equipment = session('utmsource');
                    $fubabaid  = session('utmid');
                }elseif(session("equipment") == null){
                    $equipment="APP";
                } else {
                    $equipment="WeChat";
                }

                if (strlen($username)<4) {
                    die("用户名不能小于4位数");
                }
                if (!$phone || !$username || !$password || !$verify) {
                    die("数据不完整");
                }
                if (strlen($password)<6) {
                    die("密码不能小于6位数");
                }
                if ((($_SESSION['code_temp'] != $verify)||($_SESSION['temp_phone']!=$phone)) && APP_DEBUG == 0) {
                    die('验证码错误！');
                }
                if (M("members")->where("user_phone='{$phone}'")->count('id')) {
                    die("您的手机被占用，请更换手机号码");
                }
                if (M("members")->where("user_name='{$username}'")->count('id')) {
                    die("用户名已被占用，请更换");
                }
                if ($other_name!="") {
                    $userinfo=M("members")->where("user_name='{$other_name}'")->field("id")->limit(1)->select();
                    if ($userinfo[0]['id']=='') {
                        die("推荐人不存在");
                    } else {
                        $uid=$userinfo[0]['id'];
                    }
                }


                $from=intval(session("register_from"));

                if ($from != 0) {
                    $argument1 = session("arg1");
                } else {
                    $argument1 = 0;
                }
                if (!$this->checkrecommeduid()) {
                    $data = array(
                         'user_name'    =>$username,
                         'user_pass'    =>md5($password),
                         'user_phone'   =>$phone,
                         'reg_ip'       => get_client_ip(),
                         'reward_money' => 0,//"10.00",
                         'is_reward'    => '1',
                         'equipment'    => $equipment,
                         'fubabaid'     => $fubabaid,
                         'from'         =>$from,
                         'argument1'    => $argument1,
                     );
                } else {
                    $data = array(
                         'user_name'    =>$username,
                         'user_pass'    =>md5($password),
                         'user_phone'   =>$phone,
                         'reg_ip'       => get_client_ip(),
                         'recommend_id' => session("regiser_uid"),
                         'reward_money' => 0,//"10.00",
                         'is_reward'    => '1',
                         'equipment'    => $equipment,
                         'fubabaid'     =>$fubabaid,
                         'from'         =>$from,
                         'argument1'    => $argument1,
                     );
                }
                $interface = C('UNIFY_INTERFACE.enable');
                if ($interface == 0) {
                    $data['reg_time'] = time();
                    $data['last_log_time'] = time();
                    $newid = M("members")->add($data);
                } else {
                    import("@.Phpconectjava.usersapi");
                    $users = new usersapi();
                    $data["platform_source"] = 1;
                    $data["equipment_source"] = 1;
                    if ($_SESSION["salesman_usrid"] != null) {
                        $data['recommend_id'] = $_SESSION["salesman_usrid"];
                    }
                    if ($_SESSION["salesman_2_usrid"] != null) {
                        $data["recommend_2_id"] = $_SESSION["salesman_2_usrid"];
                    }
                    $res = $users->regdo($data);
                    $res1 = json_decode($res, true);
                    if (is_null($res1['code'])) {
                        Log::write("服务器失败");
                        die('注册失败！');
                    }
                    if ($res1['code']==-1) {
                        die($res1['resultText']);
                    }
                    if ($res1['code'] !== -1) {
                        $newid = $res1['code'];
                    }
                    $data['id'] = $newid;
                    $data['reg_time'] = time();
                    $data['last_log_time'] = time();
                    M("members")->add($data);
                }
                if ($newid) {
                    $map['uid']=$newid;
                    $map['phone_status']=1;
                    if (M("members_status")->add($map)) {
                        $mem['uid']=$newid;
                        $mem['cell_phone']=$phone;
                        if (M("member_info")->add($mem)) {
                          
                            // 注册成功
                            regSuccess($newid);
                             

                            // 新注册用户奖励
                            $reg['account_money'] = 0;//"10.00";
                            $reg['uid'] = $newid;
                            M("member_money")->add($reg);
                            //Notice(1,$newid,array('email',$data['user_email']));
                            session('u_id', $newid);
                            session('u_user_name', $username);
                            if (!empty(session("source_pt"))) {
                                $source_data["uid"] = $newid;
                                $source_data["source_pt"] = session("source_pt");
                                M("member_source")->add($source_data);
                            }
                            echo '1';
                        } else {
                            $this->del_register_info($newid, 1);
                            die('注册失败');
                        }
                    } else {
                        $this->del_register_info($newid, 0);
                        die('注册失败');
                    }
                    if ($data["from"] == 2) {
                        $content ="好汉请留步！平台要实名认证后才可以把赚到的钱存入账户哦，请您在手机端页面依次点击：我的账户-请输入用户名及密码-点击登录-账户设置-输入您的姓名及身份证号码即可完成实名认证操作，如有疑问请联系客服电话：400-6626-985。";
                        sendsms($data["user_phone"], $content);
                    }

                    if (strtotime(C("THE_MAY_ACTIVE.start_time"))<=time() && strtotime(C("THE_MAY_ACTIVE.end_time"))>=time()) {
                        if ($this->checkrecommeduid()) {
                            $rec_act_data["recommend_uid"] = session("regiser_uid");
                            $rec_act_data["invest_uid"] = $newid;
                            M("recommend_invest")->add($rec_act_data);
                            $is_recommed_list = M("recommend_first")->where(array("recommend_uid"=>session("regiser_uid")))->count();
                            if (!$is_recommed_list) {
                                $first_data["recommend_uid"] = session("regiser_uid");
                                M("recommend_first")->add($first_data);
                            }
                        }
                    }
                } else {
                    die('注册失败');
                }
            } else {
                $this->do_register_ext();
                $this->weixin_token(session("regiser_uid"));
                $this->assign("token", create_token("register"));
                session("equipment", $this->_get("equipment"));
                $this->assign("tab", "user");
                $this->display();
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

         //新浪创建激活会员并绑定认证信息
        public function sinamember($sina)
        {
            //创建激活会员
            $payConfig = FS("Webconfig/payconfig");
            $sinafile = C('SINA_FILE');
            import("@.Oauth.sina.Weibopay");
            $weibopay = new Weibopay();
            $memberdata['service'] = "create_activate_member";                                      //接口名称
            $memberdata['version'] = $payConfig['sinapay']['version'];                              //接口版本
            $memberdata['request_time'] = date('YmdHis');                                           //请求时间
            $memberdata['partner_id'] = $payConfig['sinapay']['partner_id'];                        //合作者身份ID
            $memberdata['_input_charset'] = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $memberdata['sign_type'] = $payConfig['sinapay']['sign_type'];                          //签名方式 MD5
            $memberdata['identity_id'] = "20151008".$sina['identity_id'];                           //用户ID
            $memberdata['identity_type'] = "UID";                                                   //用户标识类型 UID
            $memberdata['member_type'] = 1;                                                         //用户类型：1：个人用户 2：企业用户
            $memberdata["client_ip"]=get_client_ip();                                                       //获取ip
            ksort($memberdata);                                                                     //对签名参数数据数组排序
            $memberdata['sign'] = $weibopay->getSignMsg($memberdata, $memberdata['sign_type']);      //计算签名
            $createdata = $weibopay -> createcurl_data($memberdata);
            $result = $weibopay -> curlPost($payConfig['sinapay']['mgs'], $createdata);              //模拟表单提交
            //$this->checksinaerror($result);
            //绑定认证信息
            $verifydata['service'] = "binding_verify";                                              //接口名称
            $verifydata['version'] = $payConfig['sinapay']['version'];                              //接口版本
            $verifydata['request_time'] = date('YmdHis');                                           //请求时间
            $verifydata['partner_id'] = $payConfig['sinapay']['partner_id'];                        //合作者身份ID
            $verifydata['_input_charset'] = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $verifydata['sign_type'] = $payConfig['sinapay']['sign_type'];                          //签名方式 MD5
            $verifydata['identity_id'] = "20151008".$sina['identity_id'];                           //用户ID
            $verifydata['identity_type'] = "UID";                                                   //用户标识类型 UID
            $verifydata['verify_type'] = "MOBILE";                                                  //认证类型
            $verifydata['client_ip']=get_client_ip();
            $verify_entity_rsa=$weibopay->Rsa_encrypt($sina['verify_entity'], dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对认证信息进行加密
            $verifydata['verify_entity'] = $verify_entity_rsa;                                      //认证内容
            ksort($verifydata);                                                                     //对签名参数数据排序
            $verifydata['sign'] = $weibopay->getSignMsg($verifydata, $verifydata['sign_type']);      //计算签名
            $bindingdata = $weibopay->createcurl_data($verifydata);
            $result = $weibopay->curlPost($payConfig['sinapay']['mgs'], $bindingdata);               //模拟表单提交
        }

        /********************************************
         * 忘记密码
         */

        public function forget()
        {
            if ($this->isAjax()) {
                $phone = $this->_post('phone');
                $password = $this->_post('password');
                $verify = $this->_post('verify');
                if (!$phone || !$password || !$verify) {
                    die("数据不完整");
                }
                if (($_SESSION['code_temp'] != $verify)) {
                    die('验证码错误！');
                }

                $where['user_phone']=$phone;
                $data['user_pass']=md5($password);

                $item=M("members")->where($where)->limit(1)->select();
                if (empty($item)) {
                    die('设置密码失败,当前用户没有对应的手机号');
                }
                if ($phone!=$item['0']['user_phone']) {
                    Log::write("用户输入：".$phone."数据库查询：".$item['0']['user_phone']);
                    die('设置密码失败，当前的手机号和存储的手机号不一致');
                }
                $params['usr_id']=$item['0']['id'];
                $params['user_name'] = $phone;
                $params['user_pass'] = $item['0']['password'];
                $params['user_pass_new'] = md5($password);
                $params['is_checkoldpass'] = 0;
                import("@.Phpconectjava.usersapi");
                $users = new usersapi();
                $vo = $users->setUsrpwd($params);
                $vo1 = json_decode($vo, true);
                $vo2 = json_decode($vo1['resultText'], true);
                if (is_null($vo1['code'])) {
                    Log::write("服务器失败");
                    die('设置密码失败！');
                }
                if ($vo1['code']==-1) {
                    die($vo1['resultText']);
                }
                if (M("members")->where($where)->save($data)) {
                    $where['user_phone']=$phone;
                    $item=M("members")->where($where)->limit(1)->select();
                    $newid=$item['0']['id'];
                    session('u_id', $newid);
                    session('u_user_name', $phone);
                    echo '1';
                } else {
                    die('设置密码失败');
                }
            } else {
                $simple_header_info=array("url"=>"/M/pub/login.html","title"=>"找回密码");
                $this->assign("simple_header_info", $simple_header_info);
                $this->display();
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
                    $left=160-($cur-$before);
                    echo 4;
                    exit();
                }
            }
        }
        public function ajax_forget_pass()
        {
            $user_name=htmlspecialchars($_POST['user_name']);
            $map['user_phone']=$user_name;
            $result=M("members")->where($map)->limit(1)->select();
            if ($user_name!=$result['0']['user_phone']) {
                Log::write("用户输入：".$user_name."数据库查询：".$result[0]['user_phone']);
                echo 2;
                exit();
            }
            if (!isset($result[0]['user_phone'])) {
                echo 2;
                exit();
            } else {
                $this->check_sendphone();
                if ($_SESSION['verify'] != md5($_POST['sVerCode'])) {
                    echo 3;
                    exit();
                }
                $smsTxt = FS("Webconfig/smstxt");
                $smsTxt = de_xie($smsTxt);
                $code = rand_string_reg(6, 1, 2);
                $phone=$result[0]['user_phone'];
                $res = sendsms($phone, str_replace(array("#USERANEM#", "#CODE#"), array($result[0]['user_name'], $code), $smsTxt['forget_password']));
                if ($res) {
                    session("code_temp", $code);
                    session("user_name", $user_name);
                    echo 1;
                    exit();
                } else {
                    echo 4;
                    exit();
                }
            }
        }

        public function protocol()
        {
            $content = M("article_category")->where("id = 6")->select();//file_get_contents("Style/Phone/doc/protocol.htm");
            $this->assign("content", $content[0]["type_content"]);
            $simple_header_info=array("url"=>"/M/pub/regist.html","title"=>"注册服务协议");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display();
        }

        public function checkrecommeduid()
        {
            if (session("regiser_uid")) {
                $is_recommed=M("members")->where(array("id"=>session("regiser_uid")))->count();
                if ($is_recommed > 0) {
                    return true;
                }
            }
            return false;
        }

        public function weixin_token($uid)
        {
            $token = M("weixin_token")->where(array("type"=>1,"expires_time"=>array("gt",time())))->find();
            $access_token = null;
            if ($token) {
                $access_token = $token["content"];
            } else {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C("WEIXIN.appid")."&secret=".C("WEIXIN.secret");
                $rs = curl_get($url);
                log::write("微信access_token:".$rs);
                $get_token = json_decode($rs, true);
                $data['content'] = $get_token["access_token"];
                $data['type'] = 1;
                $data['expires_time'] = time()+7200;
                M("weixin_token")->add($data);
                $access_token = $get_token["access_token"];
            }
            $ticket_info = M("weixin_token")->where(array("type"=>2,"expires_time"=>array("gt",time())))->find();
            $ticket = null;
            if ($ticket_info) {
                $ticket = $ticket_info["content"];
            } else {
                $ticket_url  = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
                $rs = curl_get($ticket_url);
                log::write("微信ticket:".$rs);
                $get_ticket = json_decode($rs, true);
                $data['content'] = $get_ticket["ticket"];
                $data['type'] = 2;
                $data['expires_time'] = time()+7200;
                M("weixin_token")->add($data);
                $ticket = $get_ticket["ticket"];
            }

            $noncestr ='wwwccfaxcn123321lianjinsuoshenzhen';
            $timestamp = time();
            $protocol = (!empty($_SERVER[HTTPS]) && $_SERVER[HTTPS] !== off || $_SERVER[SERVER_PORT] == 443) ? "https://" : "http://";
            $reg_url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
            // if($uid){
            //     $reg_url = "https://".$_SERVER['HTTP_HOST']."/M/pub/regist.html?type=weixin&uid=".$uid;
            // }else{
            //     $reg_url = "https://".$_SERVER['HTTP_HOST']."/M/pub/regist.html";
            // }
            $string="jsapi_ticket=".$ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$reg_url;
            log::write("微信签名字符串:".$string);
            $signature = sha1($string);
            $this->assign("noncestr", $noncestr);
            $this->assign("timestamp", $timestamp);
            $this->assign("signature", $signature);
            $this->assign("img_url", $protocol.$_SERVER[HTTP_HOST]."/Style/H/images/recomactive/themayrecommend.jpg");
            // $this->assign("noncestr",$noncestr);
        }
    }
