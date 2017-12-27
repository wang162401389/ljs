<?php
    /**
    * 手机版 用户中心
    */
    class UserAction extends MobileAction
    {
        public function index()
        {
            $notset = M('members_status')->where(['uid'=>$this->uid,'is_pay_passwd'=>1])->find();
            $check = checkissetpaypwd($this->uid);//查询用户是否设置新浪支付密码
            if($notset==null&&$check['is_set_paypass'] == 'Y')
            {
                $result = M('members_status')->where(array("uid"=>$this->uid))->save(array("is_pay_passwd"=>1));//存储新浪密码为已经设置状态
                //设置新浪密码成功
                setPaypasswd($this->uid);
            }

            $ids = M('members_status')->getFieldByUid($this->uid, 'id_status');

            if ($ids ==1) {
                $issetpwd = checkissetpaypwd($this->uid);//查询用户是否设置新浪支付密码
                if ($issetpwd['is_set_paypass'] == 'Y') {
                    $isget = M('members')->where('id='.$this->uid)->find();
                    if ($isget['is_reward'] == '0' && $isget['reward_money'] != '0.00') {
                        moneyactlog($this->uid, 4, 10, 0, "平台发起注册奖励", 1);
                        $Model = new Model();
                        $rs = $Model->execute("update lzh_members set is_reward=1 where id=".$this->uid);
                        //sinareward($this->uid,"注册奖励");			//注册奖励
                    }
                }
            } else {
                $usertype = M('members')->where('id='.$this->uid)->find();
                if ($usertype['user_regtype'] == 2) {
                    $status = M('members_status')->where('id='.$this->uid)->find();
                    if ($status['company_status'] == 1) {
                        $issetpwd = checkissetpaypwd($this->uid);//查询用户是否设置新浪支付密码
                        if ($issetpwd['is_set_paypass'] == 'Y') {
                            $isget = M('members')->where('id='.$this->uid)->find();
                            if ($isget['is_reward'] == '0' && $isget['reward_money'] != '0.00') {
                                moneyactlog($this->uid, 4, 10, 0, "平台发起注册奖励", 1);
                                $Model = new Model();
                                $rs = $Model->execute("update lzh_members set is_reward=1 where id=".$this->uid);
                                //sinareward($this->uid,"注册奖励");			//注册奖励
                            }
                        }
                    }
                }
            }
            $capital = M("borrow_investor")->where('investor_uid= '.$this->uid.' AND status in (1,4)  AND debt_status = 0')->sum('investor_capital - receive_capital');
            $interest = M("investor_detail")->where('investor_uid= '.$this->uid.' AND repayment_time = 0 AND substitute_time=0')->sum('interest');
            //查询投资收益总额
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $info = $Model->query("SELECT SUM(interest) AS totalmoney,SUM(jiaxi_money) AS totaljxmoney FROM lzh_investor_detail  WHERE investor_uid = {$this->uid} AND (repayment_time != 0 OR substitute_time!=0);");
            $totalmoney = '0.00';
            if ($info[0]['totalmoney'] != null) {
                $totalmoney=$info[0]['totalmoney']+$info[0]['totaljxmoney'];
            }
            $this->assign('totalmoney', $totalmoney);
            $total=$capital+$interest;
            $this->assign('saving', querysaving($this->uid));
            $this->assign('balance', querybalance($this->uid));
            $this->assign('benefit', get_personal_benefit($this->uid));
            $this->assign("total", $total);
            $this->assign("capital", $capital);
            $minfo =getMinfo($this->uid, true);
            $check_use_counpons = M("sinalog s")->join("lzh_coupons c ON s.coupons = c.serial_number")->where(array("s.type"=>3,"s.status"=>2,"s.uid"=>$this->uid))->sum('c.money');
            $minfo["money_freeze"] = getFloatValue(querysaving($this->uid, 1) + querybalance($this->uid, 1) + $check_use_counpons, 2);
            $daishoubenjin = $capital-$minfo["money_freeze"];

            //避免代收本金未负数的情况
            if ($daishoubenjin < 0) {
                $daishoubenjin = 0;
            }
            $this->assign('daishoubenjin', $daishoubenjin);
            $this->assign("minfo", $minfo);
            $this->assign("tab", 'user');
            if (partake_filter($this->uid)) {
                $this->assign("involved", "1");
                $project_url= "/M/project/info?uid=".$this->uid;
                $this->assign("project_url", $project_url);
            }

            if (C("EVENT_INFO.enable")) {
                $this->assign("event_enable", C("EVENT_INFO.enable"));
                $this->assign("event_url", C("EVENT_INFO.mobile_url"));
                $this->assign("event_prom", C("EVENT_INFO.mobile_prom"));
            }
             /******************添加用户是否领取体验金**************************************/
             $experiflag=0;//没有领取体验金   0没有领 1 领取未使用  2领取已经投标 3 已经过期
             $model=M("investor_detail_experience");//已经投标需要关联体验标静态数据

             //查询是否已经使用体验券投标
             $list=$model->query("select * from lzh_investor_detail_experience  t  where investor_uid={$this->uid}");
            $zige=0;// 0无
             $mycoupons=M("coupons c")->field("c.*")->join("lzh_members t on t.user_phone=c.user_phone")->where(array("c.type"=>2 ,"t.id"=>$this->uid))->find();
            if ($mycoupons) {
                $zige=1;
            }
            if ($list && count($list)) {
                $experiflag=2;
            } else {
                $model1=M("coupons c");//查看是否有拥有体验券
                     $experilt=$model1->query("select * from lzh_coupons c inner join lzh_member t on t.user_phone=c.user_phone where c.type=2 and uid={$this->uid}");
                if (is_array($experilt) && count($experilt)) {
                    if ($experilt[0]["endtime"]-time()<=0) {
                        $experiflag=3;
                    } else {
                        $experiflag=1;//已经领取体验金
                    }
                }
            }
            $mount=M("coupons c")->join("lzh_members t on t.user_phone=c.user_phone")->where(array("t.id"=>$this->uid,"status"=>0))->count();
            $this->assign("count", $mount);//统计券的数量
             $this->assign("experiflag", $experiflag) ;
             /********************************end****************************************** **/
             //添加链金豆

             $beanlist=M("apr_bean")->where(array("uid"=>$this->uid))->find();
            $doucount=$beanlist?$beanlist["beancount"]:0;
            $this->assign("doucount", $doucount);
            $this->assign("tiyanjing", C("EXPERIENCE_MONEY"));
            $this->assign("zige", $zige);
            $this->assign('tsykgurl', U("M/security/tiyanjin"));
             //判断是否进行风险评估
               $info = M('risk_result')->where("uid = '{$this->uid}'")->select();
            $res = 0;
            if ($info) {
                $res=1;
            }
            $this->assign('res', $res);

            $this->display();
        }

         /**
         * 个人资料
         */
         public function info()
         {
             /**
             $this->assign("kflist",get_admin_name());
             $pre = C('DB_PREFIX');
             $rule = M('ausers u')->field('u.id,u.qq,u.phone')->join("{$pre}members m ON m.customer_id=u.id")->where("u.is_kf =1 and m.customer_id={$minfo['customer_id']}")->select();
             foreach($rule as $key=>$v){
                $list[$key]['qq']=$v['qq'];
                $list[$key]['phone']=$v['phone'];
             }
             $this->assign("kfs",$list);
             **/
             $minfo =getMinfo($this->uid, true);
             $simple_header_info=array("url"=>"/M/user/index.html","title"=>"个人资料");
             $this->assign("simple_header_info", $simple_header_info);
             $this->assign("tab", 'user');
             $this->assign("minfo", $minfo);
             $this->display();
         }

         /**
         * 资金信息
         */
         public function fund()
         {
             $this->assign('saving', querysaving($this->uid));
             $this->assign('balance', querybalance($this->uid));
             $this->assign('pcount', get_personal_count($this->uid));
             $this->assign('benefit', get_personal_benefit($this->uid));   //收入
             $minfo =getMinfo($this->uid, true);
             $this->assign("minfo", $minfo);
             $this->display();
         }

         /**
         * 我要提现
         */
         public function cash()
         {
             if ($this->isAjax()) {
                 $money = $this->_post('money');
                 die(checkCash($this->uid, $money));
             } else {
                 $pre = C('DB_PREFIX');
                 $field = "m.user_name,m.user_phone,m.user_regtype,m.is_vip,i.real_name";
                 $vo = M('members m')->field($field)->join("{$pre}member_info i on i.uid = m.id")->where("m.id={$this->uid}")->find();
                 if ($vo['user_regtype'] == 2) {
                     $company = M('members_company')->where("uid={$this->uid}")->field('company_name')->find();
                     $vo['real_name']=$company['company_name'];
                 }
                 $txfee = explode("|", $this->glo['tx_fee']);
                 $fee[0]= $txfee[0];    //提现手续费
                $txmoney = explode("-", $txfee[1]); //提现金额范围
                $fee[1] = $txmoney[0];    //最小提现金额
                $fee[2] = $txmoney[1];    //最大提现金额(万元)
               //防止表单重复提交
                $_SESSION['token1']=time().mt_rand(1, 9);
                 $vo['token1']=$_SESSION['token1'];
                 $_SESSION['token2']=time().mt_rand(1, 9);
                 $vo['token2']=$_SESSION['token2'];

                 $this->assign("fee", $fee);

                 $this->assign("vo", $vo);

                 $this->assign("tab", 'user');
                 $simple_header_info=array("url"=>"/M/user/index.html","title"=>"我要提现");
                 $this->assign("simple_header_info", $simple_header_info);
                 $this->display();
             }
         }

        public function checkpayfee()
        {
            $vo = M('members')->field('is_vip,user_regtype')->where("id={$this->uid}")->find();
            M('withdrawlog')->where("uid={$this->uid} AND fee_status=0")->delete();
            if ($vo['is_vip'] == 0) {
                $fee_list = M('withdrawlog')->where("uid={$this->uid} AND fee_status = 1")->field('money_orderno')->select();
                $j=0;
                $flist = null;
                foreach ($fee_list as $f) {
                    $flist[$j] = $f['money_orderno'];
                    $j++;
                }
                $withdrawlist[0] = checkwithdraw($this->uid, $vo['user_regtype']);
                $itemtotal = ceil($withdrawlist['total_item']/20);
                $a = 1;
                if ($itemtotal>1) {
                    for ($b=1; $b <= $itemtotal; $b++) {
                        $withdrawlist[$a] = checkwithdraw($this->uid, $vo['user_regtype'], ($b+1));
                        $a++;
                    }
                }
                $i = 0;
                foreach ($withdrawlist as $w) {
                    if ($w['withdraw_list'] != null) {
                        $withdrawinfo = explode('|', $w['withdraw_list']);
                        $i = 0;
                        $list = null;
                        $orderno_list=null;
                        foreach ($withdrawinfo as $ww) {
                            $list[$i]= explode('^', $ww);
                            if ($list[$i][2] == 'SUCCESS' || $list[$i][2] == 'PROCESSING') {
                                $orderno_list[$i] = $list[$i][0];
                            }
                            $i++;
                        }
                    }
                }

                $different_list = array_diff($flist, $orderno_list);
                if (count($different_list)>0) {
                    foreach ($different_list as $d) {
                        if ($d==null || $d == '') {
                            $fee_no = M('withdrawlog')->where("uid={$this->uid} AND money_orderno is null")->field("fee_orderno")->find();
                        } else {
                            $fee_no = M('withdrawlog')->where('uid='.$this->uid .' AND money_orderno = "'.$d.'"')->field("fee_orderno")->find();
                        }
                        //代收撤销
                        $data['uid'] = $this->uid;
                        $data['money'] = $money;
                        $data["orderno"] = $fee_no['fee_orderno'];
                        $rs = sinafeecancel($data);
                        if ($rs["response_code"] == "APPLY_SUCCESS") {
                            M('withdrawlog')->where('uid='.$this->uid .' AND fee_orderno ="'.$fee_no['fee_orderno'].'"')->delete();
                        }
                    }
                }
            }
        }

         //手续费收取完成跳转提现页
        public function sinawithdrawfee()
        {
            if ($_REQUEST['fee_orderno'] != null) {
                $sina['fee_orderno'] = $_REQUEST['fee_orderno'];
            }
            $sina['account_type'] = 'SAVING_POT';
            $sina['uid'] = $this->uid;
            $sina['withdraw'] = $_REQUEST['withdraw'];
            $sina['phone'] = "yes";
            echo sinawithdraw($sina);
        }

        public function sinareturn()
        {
            $this->success('您的提现申请正在处理中', "__APP__/M/user");
        }
         /**
         * 投资总表
         */
         public function invest()
         {
             $uid = $this->uid;
             $pre = C('DB_PREFIX');

             $this->assign("dc", M('investor_detail')->where("investor_uid = {$this->uid}")->sum('substitute_money'));
             $this->assign("mx", getMemberBorrowScan($this->uid));
             $this->display();
         }
        public function loan()
        {
            $this->assign("mx", getMemberBorrowScan($this->uid));
            $this->display();
        }
         /**
         * 安全中心
         */
         // public function safe()
         // {
             // $this->assign("memberinfo", M('members')->find($this->uid));
             // $this->assign("mstatus", M('members_status')->field(true)->find($this->uid));
             // $this->assign("memberdetail", M('member_info')->find($this->uid));
             // $paypass = M("members")->field('pin_pass')->where('id='.$this->uid)->find();
             // $this->assign('paypass', $paypass['pin_pass']);
             // $this->display();
         // }
         // /**
         // * 设置支付密码
         // */
         // public function setPayPass()
         // {
            // if($this->isAjax()){
                // $password = $this->_post('password');
                // $paypass = $this->_post('paypass');
                // $paypass2 = $this->_post('paypass2');
                // if(!$password || !$paypass || !$paypass2){
                    // die('数据不完整，请检查后再试');
                // }
                // $paypass == $password && die('不能和登陆密码相同，请重新输入');
                // $paypass != $paypass2 && die('两次支付密码不一致，请重新输入');
                // $user = M('members')->field('user_pass, pin_pass')->where('id='.$this->uid)->find();
                // !$user  && die('数据有误');
                // if($user['user_pass']!=md5($password)){
                    // die('登陆密码不正确');
                // }
                // if(M("members")->where('id='.$this->uid)->save(array('pin_pass'=>md5($paypass)))){
                    // die('TRUE');
                // }else{
                    // echo '设置出错，刷新页面重试';
                // }

            // }else{
                // $this->display();
            // }
         // }
         /**
         * 修改支付密码
         *
         */
         // public function editpaypass()
         // {
             // if($this->isAjax()){
                // $oldpass = $this->_post('oldpass');
                // $paypass = $this->_post('paypass');
                // $paypass2 = $this->_post('paypass2');
                // if(!$oldpass || !$paypass || !$paypass2){
                    // die('数据不完整，请检查后再试');
                // }
                // $paypass == $oldpass && die('新密码不能和旧密码相同，请重新输入');
                // $paypass != $paypass2 && die('两次支付密码不一致，请重新输入');
                // $user = M('members')->field('pin_pass')->where('id='.$this->uid)->find();
                // !$user  && die('数据有误');
                // if($user['pin_pass']!=md5($oldpass)){
                    // die('支付密码不正确');
                // }
                // if(M("members")->where('id='.$this->uid)->save(array('pin_pass'=>md5($paypass)))){
                    // die('TRUE');
                // }else{
                    // echo '设置出错，刷新页面重试';
                // }

             // }else{
                // $this->display();
             // }
         // }

         /**
         * 修改登录密码
         *
         */
         public function editpass()
         {
             if ($this->isAjax()) {
                 $oldpass = $this->_post('oldpass');
                 $password = $this->_post('password');
                 $password2 = $this->_post('password2');
                 if (!$oldpass || !$password || !$password2) {
                     die('数据不完整，请检查后再试');
                 }
                 $password == $oldpass && die('新密码不能和旧密码相同，请重新输入');
                 $password != $password2 && die('两次密码不一致，请重新输入');
                 $user = M('members')->field('user_pass')->where('id='.$this->uid)->find();
                 !$user  && die('数据有误');
                 if ($user['user_pass']!=md5($oldpass)) {
                     die('旧密码不正确');
                 }
                 if (M("members")->where('id='.$this->uid)->save(array('user_pass'=>md5($password)))) {
                     die('TRUE');
                 } else {
                     echo '设置出错，刷新页面重试';
                 }
             } else {
                 $this->display();
             }
         }

         /**
         * 资金记录
         */
         // public function  records()
         // {
            // $logtype = C('MONEY_LOG');
            // $this->assign('log_type',$logtype);

            // $map['uid'] = $this->uid;
            // $list = getMoneyLog($map,15);
            // $this->assign("list",$list['list']);
            // $this->assign("pagebar",$list['page']);
            // $this->assign("query", http_build_query($search));
            // $this->assign("tab",'user');

   //           $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"资金记录");
   //           $this->assign("simple_header_info",$simple_header_info);
            // $this->display();
         // }
         public function records()
         {
             $page = empty($_GET['page'])?1:$_GET['page'];
             $start_time = null;
             $end_time= null;
             $result = queryusedetail($this->uid, $start_time, $end_time, $page);
             if ($result) {
                 $detlist = $result['detail_list'];
                 $totalitem = $result['total_item'];
                 $page_size = $result['page_size'];
                 $totalpage = ceil($totalitem/$page_size);
                 if ($totalitem > 0) {
                     $unlist = explode("|", $detlist);
                     $list=null;
                     $i=0;
                     foreach ($unlist as $l) {
                         $pr_list = explode("^", $l);
                         $list[$i]['dec'] = $pr_list[0];
                         $list[$i]['money'] = $pr_list[2].$pr_list[3];
                         $list[$i]['addtime'] = date("Y-m-d H:i:s", strtotime($pr_list[1]));
                         $list[$i]['det_yue'] = $pr_list[4];
                         $i++;
                     }
                 } else {
                     $list = null;
                 }
             } else {
                 $totalitem = 0;
             }
             $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"最近7天交易明细");
             $this->assign("simple_header_info", $simple_header_info);
             $this->assign("list", $list);
             $this->assign("totalpage", $totalpage);
             $this->assign("totalitem", $totalitem);
             $this->assign("page", $page);
             $this->display();
         }

        public function detaillog()
        {
            $uuid = "20151008".$this->uid;
            $where = "(pay_uid='{$uuid}' OR payee_uid='{$uuid}')";
            $list = M("member_detaillog")->where($where)->order("create_time desc")->select();
            $this->assign("list", $list);
            $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"历史资金明细");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("list", $list);
            $this->display();
        }


        public function msg()
        {
            if ($this->isAjax()) {
                $id = $this->_get('id');
                $msg = M('inner_msg')->field('msg')->where('id='.$id.' and uid='.$this->uid)->find();
                if (count($msg)) {
                    M('inner_msg')->where('id='.$id)->save(array('status'=>1));
                    echo $msg['msg'];
                } else {
                    echo '<font color=\'red\'>读取错误</font>';
                }
            } else {
                $map['uid'] = $this->uid;
                //分页处理
                import("ORG.Util.Page");
                $count = M('inner_msg')->where($map)->count('id');
                $p = new Page($count, 15);
                $page = $p->show();
                $Lsql = "{$p->firstRow},{$p->listRows}";
                //分页处理
                $list = M('inner_msg')->where($map)->order('status asc,id DESC')->limit($Lsql)->select();

                $simple_header_info=array("url"=>"/M/user/index.html","title"=>"站内信息");
                $this->assign("simple_header_info", $simple_header_info);
                $this->assign("tab", "user");

                $this->assign("list", $list);
                $this->assign("pagebar", $page);
                $this->assign("count", $count);
                $this->display();
            }
        }

         //实名认证
         public function verify()
         {
             if ($this->isAjax()) {
                 $ids = M('members_status')->where(array("uid"=>$this->uid))->find();
                 $data['real_name'] = $this->_post('realname');
                 $data['idcard']    = $this->_post('idcardno');
                 $data['up_time']   = time();
                 $data1['idcard']   = $this->_post('idcardno');
                 $data1['up_time']  = time();
                 $data1['uid']      = $this->uid;
                 $data1['status']   = 0;

                 if (empty($data['real_name'])||empty($data['idcard'])) {
                     die("请填写真实姓名和身份证号码");
                 }

                 $b = M('name_apply')->where("uid = {$this->uid}")->count('uid');
                 if ($b==1) {
                     M('name_apply')->where("uid ={$this->uid}")->save($data1);
                 } else {
                     M('name_apply')->add($data1);
                 }

                 $xuid = M('member_info')->getFieldByIdcard($data['idcard'], 'uid');
                 $c = M('member_info')->where("uid = {$this->uid}")->count('uid');

                
                $uinfo = M("members")->where("id={$this->uid}")->field("user_phone")->find();
                $sina['identity_id'] = $this->uid;//用户ID
                $sina['member_type'] = 1;    //用户类型
                $sina['phone'] = $uinfo['user_phone'];    //用户手机
                $sina['real_name'] = $data['real_name'];//用户真实姓名
                $sina['cert_no'] = $data['idcard'];//用户身份证号
                $rs = sinamember($sina);

                if ($rs == 1|| $rs===true) {
                    $rs1['status'] = 1;
                    M('name_apply')->where("uid ={$this->uid}")->save($rs1);

                    //实名之后 线下店铺推广记录并发送信息
                    $this->shop();
                } else {
                    die($rs);
                }

                 if ($c==1) {
                     $newid = M('member_info')->where("uid = {$this->uid}")->save($data);
                     ancunUser($this->uid);
                 } else {
                     $data['uid'] = $this->uid;
                     $newid = M('member_info')->add($data);
                 }

                 if ($newid && $rs == 1) {
                     $ms=M('members_status')->where("uid={$this->uid}")->setField('id_status', 1);
                     if ($ms==1) {
                         die('TRUE');
                     } else {
                         $dt['uid'] = $this->uid;
                         $dt['id_status'] = 1;
                         M('members_status')->add($dt);
                     }

                     $issetpwd = $this->checkissetpaypwd();//查询用户是否设置新浪支付密码
                    if ($issetpwd['is_set_paypass'] == 'N') {
                        die('TRUE');    //设置支付密码
                    } else {
                        die('TRUE');
                    }
                 } else {
                     die("保存失败，请重试");
                 }
             } else {
                 $ids = M('members_status')->getFieldByUid($this->uid, 'id_status');
                 $list=M("members")->where(array("id"))->find();
                 $minfo =getMinfo($this->uid, true);
                 $this->assign("id_status", $ids);
                 $this->assign("tab", 'user');
                 $this->assign("minfo", $minfo);
                 $simple_header_info=array("url"=>"/M/user/setting","title"=>"实名认证");
                 $this->assign("simple_header_info", $simple_header_info);
                 $this->display();
             }
         }

         //线下推广
         public function shop()
         {
             $userinfo = M("members")->where("id={$this->uid}")->find();
             if ($userinfo["from"] == 2) {
                 $data["token"] = date("His").mt_rand(100000, 999999);
                 $data["uid"] = $this->uid;
                 $data["name"] = $userinfo["user_name"];
                 $data["tel"] = $userinfo["user_phone"];
                 $data["shop_id"] = $userinfo['argument1'];
                 $data["create_time"] = time();
                 $rs = M("offlinep2p.register_info", "lzh_")->add($data);
                 $content = "亲，您来晚了，他们都在平台赚了好多了。不过现在您就可以凭验证码想您对面的收银员索取优惠了！验证码：{$data["token"]}，请勿转发TA人，此码只可使用一次哦，亲，赶紧来兑换吧！";
                 sendsms($userinfo["user_phone"], $content);
             }
         }

         //新浪设置实名信息
        public function setrealname($sina)
        {
            $payConfig = FS("Webconfig/payconfig");
            $sinafile = C('SINA_FILE');
            import("@.Oauth.sina.Weibopay");
            $weibopay = new Weibopay();
            //设置实名信息
            $data['service'] = "set_real_name";                //绑定认证信息的接口名称
            $data['version'] = $payConfig['sinapay']['version'];                        //接口版本
            $data['request_time'] = date('YmdHis');            //请求时间
            $data['partner_id'] = $payConfig['sinapay']['partner_id'];            //合作者身份ID
            $data['_input_charset'] = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $data['sign_type'] = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
            $data['identity_id'] = '20151008'.$sina['identity_id'];        //用户ID
            $data['identity_type'] = "UID";                    //用户标识类型 UID
            $realname = $weibopay -> Rsa_encrypt($sina['real_name'], dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对用户姓名进行rsa公钥加密
            $data['real_name'] = $realname;                    //真是姓名
            $data['cert_type'] = "IC";                        //用户标识类型 UID
            $cret_no = $weibopay->Rsa_encrypt($sina['cert_no'], dirname(dirname(dirname(__FILE__)))."/Key/".$sinafile);//对身份证号进行rsa公钥加密
            $data['cert_no'] = $cret_no;                //身份证号
            $data["client_ip"]=get_client_ip();
            ksort($data);                                    //对签名参数数据排序
            $data['sign'] = $weibopay->getSignMsg($data, $data['sign_type']);//计算签名
            $setdata = $weibopay->createcurl_data($data);
            $result = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
            return $this->checksinaerror($result);//验证
        }


        //绑定银行卡
         public function bind()
         {
             if ($this->isAjax()) {
                 $bank_info = M('member_banks')->field("uid, bank_num")->where("uid=".$this->uid)->find();

                 !$bank_info['uid'] && $data['uid'] = $this->uid;
                 $data['bank_num']       = $this->_post('banknum');
                 $bankname = explode("_", $this->_post('bank_name'));
                 $data['bank_name']      = $bankname[0];
                 $data['bank_address']   = $this->_post('bank_address');
                 $data['bank_province']  = $this->_post('province');
                 $data['bank_city']      = $this->_post('city');
                 $data['add_ip']         = get_client_ip();
                 $data['add_time']       = time();
                 if ($bank_info['uid']) {
                     /////////////////////新增银行卡修改锁定开关 开始 20130510 fans///////////////////////////
                    if (intval($this->glo['edit_bank'])!= 1 && $bank_info['bank_num']) {
                        ajaxmsg("为了您的帐户资金安全，银行卡已锁定，如需修改，请联系客服", 0);
                    }
                    /////////////////////新增银行卡修改锁定开关 结束 20130510 fans///////////////////////////
                    $old = $this->_post('oldaccount');
                     if ($bank_info['bank_num'] && $old <> $bank_info['bank_num']) {
                         die('原银卡号不对');
                     }
                     $newid = M('member_banks')->where("uid=".$this->uid)->save($data);
                     $cardid = M('member_banks')->where("uid=".$this->uid)->field('card_id')->find();
                     $unbinddata['cardid'] = $cardid['card_id'];
                     $unbinddata['identity_id'] = $this->uid;
                     $this->unbindingcard($unbinddata);
                 } else {
                     $newid = M('member_banks')->add($data);
                 }
                 if ($newid) {
                     //生成订单请求号
                    $time = explode(" ", microtime());
                     $time = $time [1] . ($time [0] * 1000);
                     $time2 = explode(".", $time);
                     $time = $time2 [0];
                     $sina['request_no'] = $time;//订单请求号
                    $sina['bank_code'] = $bankname[1];//银行编号
                    $sina['phone'] = $this->_post('phone');//银行卡号
                    $sina['bank_account_no'] = $data['bank_num'];//银行卡号
                    $sina['card_attribute'] = "C";    //卡属性 C 对私 B 对公
                    $sina['province'] = $data['bank_province'];//省份
                    $sina['city'] = $data['bank_city'];//城市
                    $sina['bank_branch'] = $data['bank_address'];//开户行
                    $result = $this->bindingBankCard($sina);
                     if ($result["response_code"] != 'APPLY_SUCCESS') {
                         die($result["response_code"].":".$result["response_message"]);
                     } else {
                         die("/M/user/bindsafecard?ticket=".$result['ticket']);
                     }

                     MTip('chk2', $this->uid);
                     die("TRUE");
                 } else {
                     die('操作失败，请重试');
                 }
             } else {
                 $bconf = get_bconf_setting();
                 $vobank = M("member_banks")->field(true)->where("uid = {$this->uid} and bank_num !=''")->find();
                 $vobank['bank_province'] = M('area')->getFieldByName("{$vobank['bank_province']}", 'id');
                 $vobank['bank_city'] = M('area')->getFieldByName("{$vobank['bank_city']}", 'id');
                 $this->assign("vobank", $vobank);
                 $this->assign("bank_list", $bconf['BANK_NAME']);
                 $this->display();
             }
         }

        public function card()
        {
            redirect(bankcard($this->uid, "https://".$_SERVER['HTTP_HOST']."/M/user/index"));
        }
        //解绑银行卡
        public function unbindingcard()
        {
            $rs = unbindcard($this->uid, $_REQUEST['cardid']);
            if ($rs['response_code'] == "APPLY_SUCCESS") {
                $this->redirect('/M/user/card');
            } elseif ($rs['response_code'] =='UNBINDING_SECURITY_CARD_FORBIDDING') {
                $this->error($rs['response_message']);
            } else {
                $this->error('解绑失败');
            }
        }

         //我要充值
         public function pay()
         {
             if ($_POST) {
                 $money = $_POST['money'];
                 $this->sinapay($money);
             } else {
                 $this->assign('saving', querysaving($this->uid));
                 $this->assign('balance', querybalance($this->uid));
                 $this->assign("tab", 'user');
                 $simple_header_info=array("url"=>"/M/user/index.html","title"=>"我要充值");
                 $this->assign("simple_header_info", $simple_header_info);
                 $this->display();
             }
         }

         //新浪支付接口
        public function sinapay($money)
        {
            import("@.Oauth.sina.Weibopay");
            $payConfig = FS("Webconfig/payconfig");
            $weibopay = new Weibopay();
            if ($payConfig['sinapay']['enable'] == 0) {
                exit("对不起，该支付方式被关闭，暂时不能使用!");
            }

            $ids = M('members_status')->getFieldByUid($this->uid, 'id_status');
            if ($ids!=1) {
                redirect('/M/user/verify.html'); //实名认证
            }

            $issetpwd = $this->checkissetpaypwd();//查询用户是否设置新浪支付密码
            if ($issetpwd['is_set_paypass'] == 'N') {
                $this->setpaypassword();        //设置支付密码
            }

            $isget = M('members')->where('id='.$this->uid)->find();
            if ($isget['is_reward'] == '0' && $isget['reward_money'] != '0.00') {
                moneyactlog($this->uid, 4, 10, 0, "平台发起注册奖励", 1);
                $Model = new Model();
                $rs = $Model->execute("update lzh_members set is_reward=1 where id=".$this->uid);
                $this->sinareward();            //注册奖励
            }

            moneyactlog($this->uid, 1, $money, 0, "用户在手机端发起充值", 1);

            //参数
            $data['service']              = "create_hosting_deposit";                                //绑定认证信息的接口名称
            $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
            $data['request_time']          = date('YmdHis');                                            //请求时间
            $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
            $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
            $data['return_url']           = "https://".$_SERVER['HTTP_HOST']."/M/user/index";                                        // *支付成功后跳转回的页面链接
            $data['notify_url']           = "https://".$_SERVER['HTTP_HOST']."/Home/sinanotify/depositnotify";        // 异步后台通知地址,如果不传此参数，则不会后台通知
            $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
            $data['summary']              = "账户充值";                                                //摘要
            $data['identity_id']          = "20151008".$this->uid;                                        //用户ID
            $data['identity_type']          = "UID";                                                    //用户标识类型 UID
            $data['amount']              = getFloatValue($money, 2); // *元 充值金额（精确到分，不可为负）
            $data['user_fee']              = 0;                                                        //充值手续费
            $data['payer_ip']              = get_client_ip();
            $data['account_type']          = "SAVING_POT";
            $data['pay_method']              = "online_bank^".$data['amount']."^SINAPAY,DEBIT,C";        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
            ksort($data);

            $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
            $setdata                      = $weibopay->createcurl_data($data);
            $paydetail['money'] = getFloatValue($money, 2);
            $paydetail['fee'] = 0;
            $paydetail['add_time'] = time();
            $pydetail['add_ip'] = get_client_ip();
            $paydetail['status'] = 0;
            $paydetail['uid'] = $this->uid;
            $paydetail['bank'] = "sinapay";
            $paydetail['nid'] = $this->createnid("sinapay", $data['out_trade_no']);

            $paydetail['way'] = "sinapay";
            $paydetail['requestId'] = $data['out_trade_no'];
            $paydetail['fee'] = 0;

            $payuid = M("member_payonline")->add($paydetail);

            if (!empty($payuid)) {
                session('payuid', $payuid);
            }

            $result = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
            $rs = $this->checksinaerror($result);
            sinalog($this->uid, null, 1, $data['out_trade_no'], $data['amount'], time(), null);
            file_put_contents('/web/App/Runtime/Cache/log.txt', var_export($rs, true), FILE_APPEND);
            //echo $result;
             echo $result;
        }

        //查询用户是否设置新浪支付密码
        public function checkissetpaypwd()
        {
            import("@.Oauth.sina.Weibopay");
            $payConfig = FS("Webconfig/payconfig");
            $weibopay = new Weibopay();
            $data['service']              = "query_is_set_pay_password";                            //绑定认证信息的接口名称
            $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
            $data['request_time']          = date('YmdHis');                                            //请求时间
            $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
            $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
            $data['identity_id']          = "20151008".$this->uid;                        //用户ID
            $data['identity_type']          = "UID";                                                    //用户标识类型 UID
            ksort($data);
            $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
            $setdata                      = $weibopay->createcurl_data($data);
            $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
            return $this->checksinaerror($result);
        }
        //重定向到新浪设置支付密码
        public function setpaypassword()
        {
            import("@.Oauth.sina.Weibopay");
            $payConfig = FS("Webconfig/payconfig");
            $weibopay = new Weibopay();
            $data['service']              = "set_pay_password";                                        //绑定认证信息的接口名称
            $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
            $data['request_time']          = date('YmdHis');                                            //请求时间
            $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
            $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
            $data['identity_id']          = "20151008".$this->uid;                        //用户ID
            $data['identity_type']          = "UID";                                                    //用户标识类型 UID
            $data['return_url']              = "https://".$_SERVER['HTTP_HOST']."/m/user/index";    //回调充值页面
            ksort($data);
            $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
            $setdata                      = $weibopay->createcurl_data($data);
            $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
            $rs = $this->checksinaerror($result);
            redirect($rs['redirect_url']);
        }

        //新浪托管代收代付接口（注册奖励）
        public function sinareward()
        {
            // import("@.Oauth.sina.Weibopay");
            // $payConfig = FS("Webconfig/payconfig");
            // $weibopay = new Weibopay();
            // $data['service'] 			  = "create_hosting_collect_trade";							//接口名称
            // $data['version']			  = $payConfig['sinapay']['version'];						//接口版本
            // $data['request_time']		  = date('YmdHis');											//请求时间
            // $data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
            // $data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
            // $data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
            // $data['out_trade_no']         = date('YmdHis').mt_rand( 100000,999999); 				// 交易订单号
            // $data['out_trade_code']		  = '1001';													//交易码
            // $data['summary']			  = '用户注册奖励';											//摘要
            // $data['payer_id']	  		  = $payConfig['sinapay']['email'];							//付款人邮箱
            // $data['payer_identity_type']  = 'EMAIL';												//ID类型
            // $data['pay_method']			  = "balance^10^BASIC";										//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
            // ksort($data);
            // $data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
            // $setdata 					  = $weibopay->createcurl_data($data);
            // $result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata);//模拟表单提交
            // $data1['service'] 			  = "create_single_hosting_pay_trade";						//接口名称
            // $data1['version']			  = $payConfig['sinapay']['version'];						//接口版本
            // $data1['request_time']		  = date('YmdHis');											//请求时间
            // $data['user_ip']			  = get_client_ip();												//用户IP地址
            // $data1['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
            // $data1['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
            // $data1['sign_type'] 		  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
            // $data1['out_trade_no']        = date('YmdHis').mt_rand( 100000,999999); 				// 交易订单号
            // $data1['out_trade_code']	  = '2001';													//交易码
            // $data1['amount']			  = '10';													//金额
            // $data1['summary']			  = '用户注册奖励';											//摘要
            // $data1['payee_identity_id']	  = '20151008'.$this->uid;										//用户ID
            // $data1['payee_identity_type'] = 'UID';													//ID类型
            // ksort($data1);
            // $data1['sign'] 				  = $weibopay->getSignMsg($data1,$data1['sign_type']);		//计算签名
            // $setdata1 					  = $weibopay->createcurl_data($data1);
            // $result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata1);//模拟表单提交
            // moneyactlog($this->uid,4,10,0,"平台完成注册奖励",2);
        }

        private function createnid($type, $static)
        {
            return md5("XXXXX@@#$%".$type.$static);
        }
        //验证新浪接口响应信息
        public function checksinaerror($data)
        {
            import("@.Oauth.sina.Weibopay");
            $weibopay = new Weibopay();
            $deresult = urldecode($data);
            $splitdata = array();
            $splitdata = json_decode($deresult, true);
            ksort($splitdata); // 对签名参数据排序

            if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
                return $splitdata;
            } else {
                return "sing error!" ;
                exit();
            }
        }

        public function setting()
        {
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"账户设置");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("tab", "user");
            $this->display();
        }
        private function tending()
        {
            //$map['i.investor_uid'] = $this->uid;
//		$map['i.status'] = 1;
            $map['investor_uid'] = $this->uid;
            $map['status'] = 1;

            $list = getTenderList($map, 15);
            foreach ($list['list'] as $k => $v) {
                //月标 加息money计算
                //if ($v['repayment_type']!=1) {
                    $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']}")->sum('jiaxi_money');
                //}
            }
            $this->assign("list", $list['list']);
            $this->assign("pagebar", $list['page']);
            $this->assign("total", $list['total_money']);
            $this->assign("num", $list['total_num']);
        }

        private function tendbacking()
        {
            $map['investor_uid'] = $this->uid;
            $map['status'] = 4;
            // $map['is_debt'] = 0;
            $map['debt_status'] = array("neq",3);

            $list = getTenderList($map, 15, 2);
            foreach ($list['list'] as $k => $v) {
                // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
                $list['list'][$k]["user_phone"]= hidecard($v['userphone'], 2);
                $list['list'][$k]['second_verify_time']=date("Y-m-d", $v['second_verify_time']);
                if ($v['repayment_type']==1) {
                    $list['list'][$k]['borrow_duration']=$v['borrow_duration']."天";
                    if ($v['debt_id']>0) {
                        $list['list'][$k]['second_verify_time'] =  date("Y-m-d", $v['debt_time']);
                    }
                } else {
                    $list['list'][$k]['borrow_duration']=$v['borrow_duration']."个月";
                    //月标 加息money计算
                    $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']} and repayment_time>0")->sum('jiaxi_money');
                    if ($v['debt_id']>0) {
                        $list['list'][$k]['second_verify_time'] =  date("Y-m-d", strtotime(date("Y-m-d", $v['second_verify_time']).'+ '.intval($v['borrow_duration']-$v['debt_duration']/30).' months'));
                    }
                }
            }
            $tiyan = M("investor_detail_experience")->where("investor_uid = {$this->uid} and status = 1")->count();
            if ($tiyan) {
                $ty_info = M("investor_detail_experience ie")->join("lzh_borrow_info_experience be on be.id = ie.borrow_id")->where("ie.investor_uid = {$this->uid}  and status = 1")->field("be.id,be.borrow_name,ie.add_time,ie.capital,be.borrow_interest_rate,be.borrow_duration_txt,ie.deadline")->select();
                foreach ($ty_info as $key => $value) {
                    $list_t["list"][$key]['bid'] = "TY".$value['id'];
                    $list_t["list"][$key]["borrow_user"] = "体验标";
                    $list_t["list"][$key]["is_auto"] = "手动";
                    $list_t["list"][$key]["borrow_name"] = $value['borrow_name'];
                    $list_t["list"][$key]["investor_capital"] = $value["capital"];
                    $list_t["list"][$key]["second_verify_time"] = date("Y-m-d", $value["add_time"]);
                    $list_t["list"][$key]["receive_capital"] = 0;
                    $list_t["list"][$key]["receive_interest"] = 0;
                    $list_t["list"][$key]["borrow_interest_rate"] = $value["borrow_interest_rate"];
                    $list_t["list"][$key]["borrow_duration"] = $value["borrow_duration_txt"];
                    $list_t["list"][$key]["repayment_time"] = $value["deadline"];
                }
                if (is_array($list["list"]) && count($list["list"])) {
                    $list['list'] = array_merge($list_t["list"], $list["list"]);
                } else {
                    $list['list'] = $list_t["list"];
                }
            }
            //$list = $this->getTendBacking();
            $this->assign("list", $list['list']);
            $this->assign("pagebar", $list['page']);
            $this->assign("total", $list['total_money']);
            $this->assign("num", $list['total_num']);
            //$this->display("Public:_footer");
            $this->assign('uid', $this->uid);
        }

        private function tenddone()
        {
            //$map['i.investor_uid'] = $this->uid;
//		$map['i.status'] = array("in","5,6");
            $map['investor_uid'] = $this->uid;
            $map['status'] = array("in","5,6");

            $list = getTenderList($map, 15);
            foreach ($list['list'] as $k => $v) {
                //月标 加息money计算
                if ($v['repayment_type']!=1) {
                    $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']} and repayment_time>0")->sum('jiaxi_money');
                }
            }
            $map2["borrow_id"] = 2;
            $map2["status"] = 2;
            $map2["investor_uid"] = $this->uid;
            $tiyan = M("investor_detail_experience")->where($map2)->count();
            if ($tiyan) {
                $ty_info = M("investor_detail_experience ie")->join("lzh_borrow_info_experience be on be.id = ie.borrow_id")->where("ie.investor_uid = {$this->uid} and ie.status = 2 and ie.borrow_id=2")->field("be.id,be.borrow_name,ie.add_time,ie.capital,ie.interest,be.borrow_interest_rate,be.borrow_duration_txt,ie.deadline")->select();
                foreach ($ty_info as $key => $value) {
                    $list_t["list"][$key]['bid'] = "TY".$value['id'];
                    $list_t["list"][$key]["borrow_user"] = "体验标";
                    $list_t["list"][$key]["is_auto"] = "手动";
                    $list_t["list"][$key]["borrow_name"] = $value['borrow_name'];
                    $list_t["list"][$key]["investor_capital"] = $value["capital"];
                    $list_t["list"][$key]["second_verify_time"] = date("Y-m-d", $value["add_time"]);
                    $list_t["list"][$key]["receive_capital"] = 0;
                    $list_t["list"][$key]["receive_interest"] = $value['interest'];
                    $list_t["list"][$key]["jiaxi_money"] = 0;
                    $list_t["list"][$key]["myexpired_money"] = 0;
                    $list_t["list"][$key]["borrow_interest_rate"] = $value["borrow_interest_rate"];
                    $list_t["list"][$key]["borrow_duration"] = $value["borrow_duration_txt"];
                    $list_t["list"][$key]["repaytime"] = $value["deadline"];
                    $list['total_money'] = $list['total_money']+$value['interest'];
                }
                if (is_array($list_t["list"]) && count($list_t["list"])) {
                    $list['list'] = array_merge($list_t["list"], $list["list"]);
                } else {
                    $list['list'] = $list_t["list"];
                }
            }
            $this->assign("list", $list['list']);
            $this->assign("pagebar", $list['page']);
            $this->assign("total", $list['total_money']);
            $this->assign("num", $list['total_num']);
            //$this->display("Public:_footer");
        }

        public function invest_list()
        {
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"投资记录");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("tab", "user");
            $this->display();
        }
        public function invest_list_detail()
        {
            $id=$_GET['id'];
            switch ($id) {
                case 1: $this->tending();
                        $simple_header_info=array("url"=>"/M/user/invest_list.html","title"=>"竞标中普通标");
                        $this->assign("simple_header_info", $simple_header_info);
                        $this->assign("tab", "user");
                        $this->display("tending");
                        break;
                case 2: $this->tendbacking();//回收中的普通标添加体验标
                         $simple_header_info=array("url"=>"/M/user/invest_list.html","title"=>"回收中普通标");
                         $this->assign("simple_header_info", $simple_header_info);
                         $this->assign("tab", "user");
                         $this->display("tendbacking");
                         break;
                case 3: $this->tenddone();
                         $simple_header_info=array("url"=>"/M/user/invest_list.html","title"=>"已回收普通标");
                         $this->assign("simple_header_info", $simple_header_info);
                        $this->assign("tab", "user");
                        $this->display("tenddone");break;
            }
        }

        public function loginpassword()
        {
            $simple_header_info=array("url"=>"/M/user/setting.html","title"=>"设置登录密码");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("tab", "user");
            $this->display();
        }

        // public function paypassword(){
            // $status=check_set_pinpass($this->uid);
            // $this->assign("status",$status);
            // $simple_header_info=array("url"=>"/M/user/setting.html","title"=>"设置平台支付密码");
            // $this->assign("simple_header_info",$simple_header_info);
            // $this->assign("tab","user");
            // $this->display();
        // }

        public function sinapass()
        {
            $ids = M('members_status')->getFieldByUid($this->uid, 'id_status');
            if ($ids!=1) {
                $this->success('请先实名认证！', '/M/user/verify'); //实名认证
                exit;
            }

            $issetpwd = $this->checkissetpaypwd();//查询用户是否设置新浪支付密码
            if ($issetpwd['is_set_paypass'] == 'N') {
                $this->setpaypassword();        //设置支付密码
            } else {
                $this->setsinapwd();
            }
        }

        //新浪找回支付密码
        public function setsinapwd()
        {
            import("@.Oauth.sina.Weibopay");
            $payConfig = FS("Webconfig/payconfig");
            $weibopay = new Weibopay();
            $data['service']              = "find_pay_password";                                    //绑定认证信息的接口名称
            $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
            $data['request_time']          = date('YmdHis');                                            //请求时间
            $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
            $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
            $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
            $data['identity_id']          = "20151008".$this->uid;                                    //用户ID
            $data['identity_type']          = "UID";                                                    //用户标识类型 UID
            $data['return_url']           = "https://".$_SERVER['HTTP_HOST']."/M/user/index";
            ksort($data);
            $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
            $setdata                      = $weibopay->createcurl_data($data);
            $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
            $rs = $this->checksinaerror($result);
            redirect($rs['redirect_url']);
        }

        public function regisuccess()
        {
            $this->display();
        }
        public function certsuccess()
        {
            $this->display();
        }
        public function records_list()
        {
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"资金管理");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("tab", "user");
            $this->display();
        }

        public function get_money()
        {
            $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"提现记录");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("tab", "user");
            $this->display();
        }
        private function create_html($list)
        {
            $html="";
            foreach ($list as $key=>$val) {
                $html.='<li style="background-color: #ffffff;height: 2.6rem;line-height: 2.6rem;margin-top:0.2rem;margin-bottom: 0.2rem;text-align: center">';
                $html.=$val[3]."&nbsp;&nbsp;&nbsp;&nbsp;提现".$val[1]."元&nbsp;&nbsp;&nbsp;&nbsp".$val[2];
                $html.='</li>';
            }
            return $html;
        }
        public function ajax_get_money()
        {
            $pagesize =20;
            $page = 1;
            if ($_POST['page']>1) {
                $page = $_POST['page'];
            }
            $start = ($page-1)*$pagesize;
            $where["uid"]=$this->uid;
            $where["type"]=array("in","2,14");
            $limit=$start.",".$pagesize;
            $withdrawlist = M("sinalog")->where($where)->order("addtime desc")->limit($limit)->select();
            $count = M("sinalog")->where($where)->count();
            $totalpage = ceil($count/$pagesize);
            if ($totalpage>=$page) {
                $i = $start;
                $list = null;
                foreach ($withdrawlist as $l) {
                    $list[$i][1] = $l["money"];
                    $list[$i][5] = $i+1;
                    $list[$i][3] = date("Y-m-d H:i:s", $l["addtime"]);
                    if ($l["status"] == 2) {
                        $list[$i][2] = "提现成功";
                    } elseif ($l["status"] == 3) {
                        $list[$i][2] = "提现失败";
                    } elseif ($l["status"] == 4) {
                        $list[$i][2] = "处理中";
                    } elseif ($l["status"] == 1) {
                        $list[$i][2] = "未提现";
                    }
                    $i++;
                }
                echo $this->create_html($list);
                exit;
            } else {
                echo "end";
                exit;
            }
        }

        // public function recharge_detail()
        // {
        //     $map['uid'] = $this->uid;
        //     $status_arr = array('充值未完成', '充值成功', '签名不符', '充值失败');
        //     $list = M('member_payonline')->where($map)->order('id DESC')->select();
        //     foreach ($list as $key => $v) {
        //         $list[$key]['status'] = $status_arr[$v['status']];
        //     }
        //     $row = array();
        //     $row['list'] = $list;
        //     $map['status'] = 1;
        //     $row['success_money'] = M('member_payonline')->where($map)->sum('money');
        //     $map['status'] = array('neq', '1');
        //     $row['fail_money'] = M('member_payonline')->where($map)->sum('money');
        //     foreach($list as $key=>$val){
        //         $list[$key]['add_time']=date("Y-m-d H:i:s",$val['add_time']);
        //     }

        //     $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"充值记录");
        //     $this->assign("simple_header_info",$simple_header_info);
        //     $this->assign("list",$list);
        //     $this->assign("success_money",$row['success_money']);
        //     $this->assign("fail_money",$row['fail_money']);
        //     $this->display();
        // }

        //充值记录
    public function recharge_detail()
    {
        $where["uid"]=$this->uid;
        $where["type"]=1;
        $mywhere=array();
        $rechargelist = M("sinalog")->where($where)->order("addtime desc")->select();
        $rechargesuccsum = M("sinalog")->where("uid={$this->uid} AND type = 1 AND status = 2")->sum('money');
        $rechargefailsum = M("sinalog")->where("uid={$this->uid} AND type = 1 AND status IN (1,3)")->sum('money');
        if ($rechargefailsum == null) {
            $rechargefailsum = 0.00;
        }
        if ($rechargesuccsum == null) {
            $rechargesuccsum = 0.00;
        }
        $i = 0;
        foreach ($rechargelist as $l) {
            // $list[$i]['money'] = $l["money"];
            // $list[$i]['addtime'] = date("Y-m-d H:i:s",$l["addtime"]);
            if ($l["status"] == 2) {
                $rechargelist[$i]['status'] = "充值成功";
            } elseif ($l["status"] == 1) {
                $rechargelist[$i]['status'] = "处理中";
            } elseif ($l["status"] == 3) {
                $rechargelist[$i]['status'] = "充值失败";
            }
            $i++;
        }
        $simple_header_info=array("url"=>"/M/user/records_list.html","title"=>"充值记录");
        $this->assign("simple_header_info", $simple_header_info);
        $this->assign("list", $rechargelist);
        $this->assign("success_money", $rechargesuccsum);
        $this->assign("fail_money", $rechargefailsum);
        $this->display();
        ;
    }

        public function piggybank()
        {
            $map['uid']=$this->uid;
            $regtime = M('members')->field('reg_time')->where('id='.$this->uid)->find();
            $start = C('EARNINGS.starting');
            $list = M('member_piggybank')->field('earnings_yesterday,time,total_revenue')->where($map)->order('time DESC')->select();
            foreach ($list as $k => $v) {
                if ($v['time']>strtotime(date('Y-m-d', time())) && $v['time']<strtotime(date('Y-m-d', strtotime('+1 day')))) {
                    $zrshouyi = $v['earnings_yesterday'];//
                }
                /**
                if($v['time']>strtotime($start)){
                    $zshouyi += $v['earnings_yesterday'];
                }
                 * **/
                $list[$k]['time']=date("Y-m-d", $v['time']-24*3600);
            }
            $cqglist = piggybankearnings();
            $cqglist1 = explode('|', $cqglist['yield_list']);
            foreach ($cqglist1 as $k => $v) {
                $cqglist2[$k] = explode('^', $v);
            }
            $this->assign('thousandsincome', $cqglist2[0][2]);
            $this->assign('yields', $cqglist2[0][1]);
            $this->assign('list', $list);
            $this->assign('start', $start);
            $this->assign('regtime', $regtime['reg_time']);
            $this->assign('zrshouyi', $zrshouyi);
            $this->assign('zonshouyi', $list[0]["total_revenue"]?$list[0]["total_revenue"]:0);
            $this->display();
        }

        //投资券
        public function mycoupons()
        {
            $coupons= M("coupons c")->field("c.*")->join("lzh_members m ON m.user_phone = c.user_phone")->where("m.id ={$this->uid}")->select();
            $co1=[];//未使用
            $co2=[];//已使用
            $co3=[];//已过期
            foreach ($coupons as $va) {
                if ($va["type"]==1) {
                    $va["type_name"]="投资券";
                } elseif ($va["type"]==2) {
                    $va["type_name"]="体验券";
                } else {
                    $va["type_name"]="加息券";
                }
                if ($va["status"]==2) {
                    $co3[]=$va;
                } elseif ($va["status"]==0) {
                    $co1[]=$va;
                } else {
                    $co2[]=$va;
                }
            }
            unset($coupons);

            $this->assign("couponsunused", $co1);//未使用
            $this->assign("coupons", $co2);//已使用
            $this->assign("couponsexpired", $co3);//已过期

            $this->assign("tab", 'user');
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"我的赠券");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display();
        }

        /*
         * 自动投标
         */
        public function autoborrow()
        {
            //实名认证的状态
            $is_pass = M('members_status')->where('uid ='.$this->uid)->getField('id_status');
            if ($is_pass != 1) {
                redirect(U('user/verify'));
            }
            //单笔最低投标额最大限制
            $min_money = M('system_setting')->where('number =1002')->getField('value');

            $auto_info = M('borrow_auto')->where('uid='.$this->uid)->find();
            $time = '';
            if ($auto_info) {
                if ($auto_info['is_borrow_day']==1&&$auto_info['is_borrow_month'] == 1) {
                    $time = $auto_info['month_start'].'月-'.$auto_info['month_end'].'月或'.$auto_info['day_start'].'天-'.$auto_info['day_end'].'天';
                } elseif ($auto_info['is_borrow_day']==1) {
                    $time = $auto_info['day_start'].'天-'.$auto_info['day_end'].'天';
                } elseif ($auto_info['is_borrow_month']==1) {
                    $time = $auto_info['month_start'].'月-'.$auto_info['month_end'].'月';
                }
            }
            //是否开通委托代扣
            $is_open = $this->getUserBuckle();
            $this->assign(array('min_money'=>$min_money,'auto_info'=>$auto_info,'time'=>$time,'is_open'=>$is_open));
            $simple_header_info=array("url"=>"/M/user/setting.html","title"=>"自动投标");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display();
        }

        /*
         * 保存自动投标参数
         */
        public function autoBorrowSet()
        {
            //校验数据
            $new_params =  $this->verifyBorrowDate();
            $auto_info = M('borrow_auto')->where('uid='.$this->uid)->find();
    //        需要判断是否有委托代扣
            $is_open = $this->getUserBuckle();
            $new_params['open_type'] = $is_open=='Y'?1:2;
            if ($auto_info) {
                //更新数据
                $new_params['update_time'] = time();
                $result = M('borrow_auto')->where('uid='.$this->uid)->save($new_params);
            } else {
                //插入数据
                $new_params['create_time'] = time();
                $new_params['uid'] = $this->uid;
                $result = M('borrow_auto')->add($new_params);
            }
            if ($result) {
                ajaxmsg('保存成功！');
            } else {
                $msg['message'] ='保存失败或未做修改！';
                ajaxmsg('保存失败或未做修改！', 0);
            }
        }

        /*
         * 校验数据
         */
        private function verifyBorrowDate()
        {
            $params = array();
            if (empty($_POST)) {
                ajaxmsg('数据错误！', 0);
            }
            //单笔最低投标额最大限制
            $min_money = M('system_setting')->where('number =1002')->getField('value');
            //填写最低限制
            $params['money'] = intval($_POST['lowmoney']);
            if (empty($params['money'])|| $params['money']<100 || $params['money']>$min_money) {
                ajaxmsg('单笔最低投标额不能为空，范围100-'.$min_money.'！', 0);
            }

            $params['borrow_type'] = intval($_POST['borrow']);
            $params['repayment_type'] = intval($_POST['hk']);
            //天标
            $params['is_borrow_day'] = intval($_POST['db']);
            if ($params['is_borrow_day'] == 1) {
                $params['day_start'] = intval($_POST['startday']);
                $params['day_end'] = intval($_POST['endday']);
                if ($params['day_start'] > $params['day_end']||$params['day_start']>180||$params['day_end']>180||$params['day_start']<1||$params['day_end']<1) {
                    ajaxmsg('天标范围1-180天,且开始天数不能大于结束天数！', 0);
                }
            }
            //月标
            $params['is_borrow_month'] = intval($_POST['mb']);
            if ($params['is_borrow_month'] == 1) {
                $params['month_start'] = intval($_POST['startmonth']);
                $params['month_end'] = intval($_POST['endmonth']);
                if ($params['month_start'] > $params['month_end']||$params['month_start']>12||$params['month_end']>12||$params['month_start']<1||$params['month_end']<1) {
                    ajaxmsg('月标范围1-12月,且开始月数不能大于结束月数！', 0);
                }
            }
            //预期年化率
            $params['rate_start'] = intval($_POST['startnhl']);
            $params['rate_end'] = intval($_POST['endnhl']);
            if ($params['rate_start'] > $params['rate_end']||$params['rate_start']>15||$params['rate_end']>15||$params['rate_start']<1||$params['rate_end']<1) {
                ajaxmsg('年化收益率范围1%-15%，且开始年化收益率不能大于结束年化收益率！', 0);
            }
            //使用投资卷
            $params['ticket_type'] = intval($_POST['usejuan']);
            return $params;
        }
        /*
         * 禁用自动投标
         */
        public function stopAutoBorrow()
        {
            $auto_m = M('borrow_auto');
            $auto_info = $auto_m->where('uid='.$this->uid)->find();
            if ($auto_info) {
                //是否开通委托代扣
                $is_open = $this->getUserBuckle();
                if ($is_open == 'Y') {
                    $updata['open_type'] = $auto_info['open_type'] == 1?0:1;
                    $result = $auto_m->where('uid='.$this->uid)->save($updata);
                    if ($result) {
                        ajaxmsg('设置成功！');
                    }
                } else {
                    ajaxmsg('未开通代扣！', 0);
                }
            } else {
                ajaxmsg('填写数据！', 0);
            }
        }

        /*
         * 查询用户是否有委托代扣
         */
        private function getUserBuckle()
        {
            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $is_open = $sina->queryauthority($this->uid);
            return $is_open;
        }
        /*
         * 新浪开通代扣页面
         */
        private function openBuckle()
        {
            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $url = U('M/User/changeState');
            return $sina->handleauthority($this->uid, $url);
        }
        /*
         * 开通委托代扣后更新状态
         */
        public function changeState()
        {
            $is_open = $this->getUserBuckle();
            if ($is_open == 'Y') {
                $data['open_type'] =1;
                M('borrow_auto')->where('uid='.$this->uid)->save($data);
            }
            $this->redirect('M/User/autoborrow');
        }



        /*
         * 自动投标须知
         */
        public function borrownotice()
        {
            //实名认证的状态
            $is_pass = M('members_status')->where('uid ='.$this->uid)->getField('id_status');
            if ($is_pass != 1) {
                redirect(U('User/verify'));
            }
            //是否开通委托代扣
            $is_open = $this->getUserBuckle();
            $url = $this->openBuckle();//新浪开通委托代扣地址
            $this->assign(array('url'=>$url,'is_open'=>$is_open));
            $simple_header_info=array("url"=>"/M/user/autoborrow.html","title"=>"服务须知");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display('autoborrow_notice');
        }
    }
