<?php
/*用户中心
*/
class IndexAction extends MCommonAction {
    private $customer_id;
    private function person_info(){
        //个人信息
        $minfo =getMinfo($this->uid,true);
        $check_use_counpons = M("sinalog s")->join("lzh_coupons c ON s.coupons = c.serial_number")->where(array("s.type"=>3,"s.status"=>2,"s.uid"=>$this->uid))->sum('c.money');
        $minfo["money_freeze"] = getFloatValue(querysaving($this->uid,1) + querybalance($this->uid,1) + $check_use_counpons,2);

        $this->customer_id=$minfo["customer_id"];
        if($minfo["user_name"]==$minfo["user_phone"]){
            $this->assign("modify_name",1);
        }
        $level=25;
        //是否实名认证
        $members_statusModel=M("members_status");
        $id_status=$members_statusModel->where("uid={$this->uid}")->find();
       if($id_status["id_status"]==1){
           $this->assign("ID_SET",1);
           $level+=25;
       }else if($id_status["company_status"]!=0){
           $this->assign("ID_SET",1);
           $level+=25;
       }
       else{
           $go_id= "/member/verify?id=1#fragment-1";
           $this->assign("go_id",$go_id);
           $improve=$go_id;
       }
        //查询支付密码
        if($id_status["is_pay_passwd"]){
            $sina_password["is_set_paypass"]="Y";
        }else{
            $sina_password=checkissetpaypwd($this->uid);
            if($sina_password["is_set_paypass"]=="Y"){
                $notset = M('members_status')->where(['uid'=>$this->uid,'is_pay_passwd'=>1])->find();
                $result = $members_statusModel->where(array("uid"=>$this->uid))->save(array("is_pay_passwd"=>1));//存储新浪密码为已经设置状态
                if($notset==null&&$result!==false)
                {
                    //设置新浪密码成功
                    setPaypasswd($this->uid);
                }
            }
        }
        if($sina_password["is_set_paypass"]=="Y"){
           $this->assign("SINA_SET",1);
           $level+=25;
       }else{
            $go_sina="/member/promotion/checkissetpwd?i=2";
           $this->assign("go_sina",$go_sina);
           if(empty($improve)){
               $improve=$go_sina;
           }
       }
        //是否邮箱验证
        if($id_status["email_status"]==1){
            $this->assign("EMAIL_SET",1);
            $level+=25;
        }else{
            $go_email= "/member/verify?id=1#fragment-2";
            $this->assign("go_email",$go_email);
            if(empty($improve)){
                $improve=$go_email;
            }
        }
        switch($level){
            case 25: $this->assign("level_text","低");break;
            case 50: $this->assign("level_text","中");break;
            case 75: $this->assign("level_text","高");break;
            case 100: $this->assign("level_text","优");break;
        }
        $this->assign("improve",$improve);
        //$this->assign("level",$level."%");
        $this->assign("minfo",$minfo);
        //代收本
        $total = M("borrow_investor")->where('investor_uid= '.$this->uid.' AND status in (1,4) AND debt_status = 0')->sum('investor_capital - receive_capital');
        $daishoubenjin=$total-$minfo["money_freeze"];
        if($daishoubenjin<0){//代收本金
            $daishoubenjin=0;
        }
        $this->assign("daishoubenjin",$daishoubenjin);
        $this->assign("total",$total);
        //查询投资收益总额
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $info = $Model->query("SELECT SUM(interest) AS totalmoney,SUM(jiaxi_money) AS totaljxmoney FROM lzh_investor_detail  WHERE investor_uid = {$this->uid} AND (repayment_time != 0 OR substitute_time!=0);");
        $totalmoney = '0.00';
        if($info[0]['totalmoney'] != null){
            $totalmoney=$info[0]['totalmoney']+$info[0]['totaljxmoney'];
        }
        $this->assign('totalmoney',$totalmoney);
        //直接调取新浪余额
        //$minfo['account_money']=number_format( querysaving($this->uid), 2, ".", "" );

        //代收收益
        //$benefit=get_personal_benefit($this->uid);
        $this->assign('benefit', get_personal_benefit($this->uid));
        //体验券
        $this->assign('tsykgurl',U("home/security/tiyanjin"));
        $expinfo = M('borrow_info_experience')->find();
        $this->assign('expinfo',$expinfo);
        $explist = M('coupons')->where('user_phone = '.$minfo['user_phone'].' and type = 2')->find();
        //$investcount = M('investor_detail')->where('investor_uid ='.$minfo['id'])->count();//判断是否投资
        $investcount = M('investor_detail_experience')->where('investor_uid ='.$minfo['id'])->count();//判断是否投资
        $this->assign('investnum',$investcount);
        $this->assign('expmoney',C('EXPERIENCE_MONEY'));
        $this->assign('explist',$explist);
        //存钱罐收益
        if($minfo['user_regtype']!=2){
            $cqglist = piggybankearnings();
            $cqglist1 = explode('|', $cqglist['yield_list']);
            foreach($cqglist1 as $k => $v){
                $cqglist2[$k] = explode('^',$v);
            }
            $cmap['uid']=$this->uid;
            $start = C('EARNINGS.starting');//存钱罐收益起始日

            $list = M('member_piggybank')->field('earnings_yesterday,time,total_revenue')->where($cmap)->order('time DESC')->select();
            $zshouyi=0;
            foreach($list as $k => $v){
                /**
                if($v['time']>strtotime(date('Y-m-d',time())) && $v['time']<strtotime(date('Y-m-d',strtotime('+1 day')))){
                    $zrshouyi = $v['earnings_yesterday'];
                }
                ***/
                if($v['time']>strtotime(date('Y-m-d',time())) && $v['time']<strtotime(date('Y-m-d',time()+3600*24))){
                    $zrshouyi = $v['earnings_yesterday'];
                }
                if($v['time']>strtotime($start)){
                    $zshouyi += $v['earnings_yesterday'];
                }
                $list[$k]['time']=date("Y-m-d",$v['time']-24*3600);
            }
            $this->assign('zrshouyi',$zrshouyi);//昨日收益
            $this->assign('zonshouyi',$list[0]["total_revenue"]?$list[0]["total_revenue"]:0);//总收益
            $this->assign('start',$start);
            $this->assign('thousandsincome',$cqglist2[0][2]);//万分收益
            $this->assign('yields',$cqglist2[0][1]); //七日年化
            $this->assign('cqgsy',1);
        }

        //moneylog
        $map=array();
        $map['uid'] = $this->uid;
        $Log_list = getMoneyLog($map,4);
        $this->assign("Log_list",$Log_list['list']);
        $this->assign("list",get_personal_count($this->uid));

        //判断是否为借款人
        //判断投资人还是借款人，投资人不显示借款管理
        if($this->supper_login == 1){
            $touzi = M('borrow_info')->count();
        }else{
            $touzi = M('borrow_info')->where("borrow_uid=".$this->uid)->count();
        }
        cookie('borrowing', $touzi, 3600);
        if($touzi){
            $where["b.borrow_uid"] = $this->uid;
            $where["b.borrow_status"] = array("egt",6);//只有还款中的标的才算借款了
            $minfo=M("borrow_info b")->where($where)->field("sum(borrow_money) as total")->group("borrow_uid")->find();
            $total=$minfo["total"];

            $field="i.repayment_time,i.capital,i.interest,i.deadline,i.sort_order,b.id,b.repayment_type";
            $detail_info=M("investor_detail i")->join("lzh_borrow_info b on b.id=borrow_id")->where($where)->field($field)->order("repayment_time,deadline")->select();
            $last_time = "借款已经还完";
            if($detail_info[0]["repayment_time"] == 0){
                $last_time = date("Y-m-d 23:59:59",$detail_info[0]['deadline']);
            }
            $left_capital=0;//未还本
            $left_interest=0;//未还息
            $left_total=0;//总共未还
            $return_total=0;//已还本
            foreach($detail_info as $key => $val){
                if($val["repayment_time"] == 0){ //未还
                    $left_capital+=$val["capital"];
                    $left_interest+=$val["interest"];
                    $left_total+=$val["interest"]+$val["capital"];//总共未还
                }else{
                    $return_total+=$val["interest"]+$val["capital"];//已还本息
                }
            }
            $this->assign("touzi",1);
            $this->assign("borrow_total",$total);
            $this->assign("return_total",$return_total);
            $this->assign("last_time",$last_time);
            $this->assign("left_total",$left_total);
            $this->assign("left_capital",$left_capital);
            $this->assign("left_interest",$left_interest);
            $return_url="/member/borrowdetail?id=".$detail_info[0]["id"]."#fragment-1";
            $this->assign("return_url",$return_url);
        }

        if (empty(session('fxpg_popup_status'.$this->uid))) {
            $risk = M("risk_result")->where(array("uid" => $this->uid))->limit(1)->find();
            $fxpg_popup_status = empty($risk) ? $id_status['fxpg_popup_status'] : 1;
            session('fxpg_popup_status'.$this->uid, $fxpg_popup_status);
        }
        $this->assign("fxpg_popup_status", session('fxpg_popup_status'.$this->uid));

        /**
         * 获取目前虚拟标的的信息,
        if(C("V_INVEST.enable")){
            $where["borrow_status"]=array("in","2,4,6");
            $result=M("borrow_info")->db(1,C("V_INVEST.db"))->order("borrow_status")->field("id")->find();
            if ($_SERVER["HTTPS"] <> "on") {
                $url="http://".C("V_INVEST.v_url")."/invest/".$result['id'].".html";
            }
            else{
                $url="https://".C("V_INVEST.v_url")."/invest/".$result['id'].".html";
            }
            $this->assign("v_url",$url);
            //查询虚拟账户
            $where["uid"]=$this->uid;
            $sql="select * from lzh_members where uid='".$this->uid."'";
            $res=M("members")->db(1,C("V_INVEST.db"))->query($sql);
            if(count($res)==1){ //新用户
                $this->assign("v_money",getFloatValue($res[0]["v_money"],2));
                $left=$res[0]["deadtime"]-time();
                if($left<=0){ //体验金过期，
                    $this->assign("v_income",0);//逾期收益为0
                }else{
                    $this->assign("v_income",5);
                }
            }else{ //老用户
                $this->assign("v_money",getFloatValue(0,2)); //老用户虚拟资金为0
                $this->assign("v_income",0);
            }


            //
            $token = mt_rand( 100000,999999);
            session("token",$token);
            $this->assign("token",$token);
		}
        ******/
	}

    /**
     * 推荐列表
     */
    public function investor(){
        //推荐列表
        $sql="select id,add_time, product_type,has_borrow,borrow_money,borrow_name,borrow_interest_rate,borrow_duration_txt,borrow_duration,repayment_type,borrow_status from (select b.id,b.add_time,product_type,has_borrow,borrow_money,borrow_name,borrow_interest_rate,borrow_duration_txt,borrow_duration,repayment_type,borrow_status,
       has_borrow*100/borrow_money as progress from lzh_borrow_info b inner join lzh_members m on m.id=b.borrow_uid where b.borrow_status in ('2,4,6,7') and b.test=0 and b.is_beginnercontract=0 ) t where progress<100 order by  progress desc, add_time asc limit 3";
        $blist = M('borrow_info b')->query($sql);
        $count=M("coupons")->query("select count(*) as mycount from lzh_coupons c inner join lzh_members t on t.user_phone=c.user_phone where t.id='{$this->uid}' and c.status=0 and c.type=2");

        /**
         * 是否显示体验标:0 不显示 1显示
         */
        $isshow=0;

        if($count[0]["mycount"]!=0){//如果存在体验金则显示
            $expinfo = M('borrow_info_experience')->find();
            $this->assign('expmoney',C('EXPERIENCE_MONEY'));
            $this->assign('expinfo',$expinfo);
            $isshow=1;
        }
        $this->assign("isshow",$isshow);

        foreach($blist as $key=>$v){
            $blist[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
        }
        $this->assign("blist",$blist);
        $beanlist=M("apr_bean")->where(array("uid"=>$this->uid))->find();
        $doucount=$beanlist?$beanlist["beancount"]:0;
        $data['html'] = $this->fetch();
        $data["bean"]=$doucount;//剩余链金豆的数量
        $data["total"]=count($blist);
        exit(json_encode($data));
    }

    public function index(){
        if(C("Cach.member_info")){
            $path="html/member_info/".date("Ymd")."/";
            $filename=$this->uid;
            $time=filemtime($path.$filename.".html");
            if(empty($time)){ //缓存时间失效，清楚time
                unlink($path . $filename . ".html");
            }else{
                $where["add_time"]=array("gt",$time);
                $where["uid"]=$this->uid;
                $result=M("member_moneylog")->where($where)->select();
                if(file_exists($path.$filename.".html")){
                    if(!is_array($result)) {
                        echo file_get_contents($path . $filename . ".html");
                        exit;
                    }else{
                        unlink($path . $filename . ".html");
                    }
                }
            }
        }

        $this->person_info();
		$ucLoing = de_xie($_COOKIE['LoginCookie']);
		setcookie('LoginCookie','',time()-10*60,"/");
		$this->assign("uclogin",$ucLoing);

		$this->assign("unread",$read=M("inner_msg")->where("uid={$this->uid} AND status=0")->count('id'));
		$this->assign("mstatus", M('members_status')->field(true)->find($this->uid));
		$isdanbao = M("members_company")->where("uid={$this->uid} AND is_danbao = 1")->count();
		cookie('danbao',$isdanbao,3600);

		$this->assign("memberdetail", M('member_info')->find($this->uid));
        $this->assign('out', get_personal_out($this->uid));
        $this->assign('sinasaving',querysaving($this->uid));
		$this->assign('sinabalance',querybalance($this->uid));
		$this->assign("bank",M('member_banks')->field('bank_num')->find($this->uid));
		$info = getMemberDetail($this->uid);
		$this->assign("info",$info);

		$this->assign("kflist",get_admin_name());
		$list=array();
		$pre = C('DB_PREFIX');
		$rule = M('ausers u')->field('u.id,u.qq,u.phone')->join("{$pre}members m ON m.customer_id=u.id")->where("u.is_kf =1 and m.customer_id={$this->customer_id}")->select();
		foreach($rule as $key=>$v){
			$list[$key]['qq']=$v['qq'];
			$list[$key]['phone']=$v['phone'];
		}
		$this->assign("kfs",$list);
		$this->display();
    }

	//验证新浪接口响应信息
	public function checksinaerror($data){

		import("@.Oauth.sina.Weibopay");
		$weibopay = new Weibopay();
		$deresult = urldecode($data);
		$splitdata = array ();
		$splitdata = json_decode( $deresult, true );
		ksort ($splitdata); // 对签名参数据排序

		if ($weibopay->checkSignMsg ($splitdata,$splitdata["sign_type"]))
		{
			return $splitdata;
		}else{
			return "sing error!" ;
			exit();
		}

	}
    //查询用户是否设置新浪支付密码
	public function checkissetpaypwd(){

		import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "query_is_set_pay_password";							//绑定认证信息的接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id']		  = "20151008".$this->uid;						//用户ID
		$data['identity_type'] 		  = "UID";													//用户标识类型 UID
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		return $this->checksinaerror($result);
	}
//新浪托管代收代付接口（注册奖励）
	public function sinareward(){

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
        // $data['user_ip']              = getIp();                                                //用户IP地址
		// $data1['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		// $data1['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		// $data1['sign_type'] 		  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		// $data1['out_trade_no']        = date('YmdHis').mt_rand( 100000,999999); 				// 交易订单号
		// $data1['out_trade_code']	  = '2001';													//交易码
		// $data1['amount']			  = '10';													//金额
		// $data1['summary']			  = '用户注册奖励';											//摘要
		// $data1['payee_identity_id']	  = '20151008'.$this->uid;									//用户ID
		// $data1['payee_identity_type'] = 'UID';													//ID类型
		// $data1['account_type']		  = "SAVING_POT";											//账户类型
		// ksort($data1);
		// $data1['sign'] 				  = $weibopay->getSignMsg($data1,$data1['sign_type']);		//计算签名
		// $setdata1 					  = $weibopay->createcurl_data($data1);
		// $result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata1);//模拟表单提交
		// moneyactlog($this->uid,4,10,0,"平台完成注册奖励",2);

	}

	/**************新增找回支付密码  2013-10-02  fan*********************************/
		public function getpaypassword(){
		$d['content'] = $this->fetch();
		echo json_encode($d);
	}

	//找回支付密码
	public function dogetpaypass(){
		(false!==strpos($_POST['u'],"@"))?$data['user_email'] = text($_POST['u']):$data['user_name'] = text($_POST['u']);
		$vo = M('members')->field('id')->where($data)->find();
		if(is_array($vo)){
			$res = Notice(10,$vo['id']);
			if($res) ajaxmsg();
			else ajaxmsg('',0);
		}else{
			ajaxmsg('',0);
		}
	}

	//验证码验证
	public function getpaypasswordverify(){
		$code = text($_GET['vcode']);
		$uk = is_verify(0,$code,7,60*1000);
		if(false===$uk){
			$this->error("验证失败");
		}else{
			session("temp_get_paypass_uid",$uk);
			$this->display('getpaypass');
		}
	}

	//设置新支付密码
	public function setnewpaypass(){
		$d['content'] = $this->fetch();
		echo json_encode($d);
	}

	//处理支付密码
	public function dosetnewpaypass(){
		$per = C('DB_PREFIX');
		$uid = session("temp_get_paypass_uid");
		$oldpass = M("members")->getFieldById($uid,'pin_pass');
		if($oldpass == md5($_POST['paypass'])){
			$newid = true;
		}else{
			$newid = M()->execute("update {$per}members set `pin_pass`='".md5($_POST['paypass'])."' where id={$uid}");
		}

		if($newid){
			session("temp_get_paypass_uid",NULL);
			ajaxmsg();
		}else{
			ajaxmsg('',0);
		}
	}

    /**
     * 修改用户名
     */
    public  function  modify(){
        $modify =   $_POST["modify_name"];
        $user_name  =   $_POST["user_name"];

        if(preg_match("/^[0-9]*$/",$modify)){
            $this->ajaxReturn("用户名不能为纯数字");exit;
        }
        if((strlen($modify)<4)||(strlen($modify)>20)){
            $this->ajaxReturn("用户名长度必须大于4，小于20");exit;
        }
        if($this->uid){
            $where["id"]=$this->uid;
            $where["user_name"]=$user_name;
            $list=M("members")->where($where)->find();
            if(!is_array($list)){
                $this->ajaxReturn("您没有权限修改用户名");
                exit;
            }else{
                $where1["user_name"]=$modify;
                $list1=M("members")->where($where1)->find();
                $where3["user_phone"]=$modify;
                $list3=M("members")->where($where3)->find();
                if(is_array($list1)){
                    $this->ajaxReturn("用户名已经存在");
                }elseif(is_array($list3)){
                    $this->ajaxReturn("不能使用手机号");
                }else{
                    //Java修改用户名
                    $list4=M('members')->where("id=".$this->uid)->find();
                    import("@.Phpconectjava.usersapi");
                    $users = new usersapi();
                    $option['usr_id']=$this->uid;
                    $option['user_name']=$list4['user_name'];
                    $option['user_pass']=$list4['user_pass'];
                    $option['user_name_new']=$modify;
                    $molist = $users->setUsrname($option);
                    $molist1 = json_decode($molist,true);
                    if($molist1['code']==-1){
                        $this->ajaxReturn("修改失败");
                    }
                    Log::write(var_export($molist,true));
                    $where2["id"]=$this->uid;
                    $data["user_name"]=$modify;
                    $result=M("members")->where($where2)->save($data);
                    if($result){
                        $this->del_mem_cach();
                        $this->ajaxReturn("OK");
                    }else{
                        $this->ajaxReturn("修改失败");
                    }
                }
            }
        }else{
            echo "请登录";
            exit;
        }
    }

    public function cancelPopupStatus(){
        if($this->uid){
            $status = $_GET['status'];
            $ori_status = M('members_status')->where(array('uid' => $this->uid))->getField('fxpg_popup_status');
            if ($ori_status != 2) {
                M('members_status')->where(array('uid' => $this->uid))->setField('fxpg_popup_status', $status);
            }
            $this->ajaxReturn("OK");
        }else{
            echo "请登录";
            exit;
        }
    }

    public function  get_vmoney_left(){
        $sql="select * from lzh_members where uid='".$this->uid."'";
        $res=M("members")->db(1,C("V_INVEST.db"))->query($sql);
         $left=$res[0]["deadtime"]-time();
        if($left<=0){
            $left=0;
        }
        $beanlist=M("apr_bean")->where(array("uid"=>$this->uid))->find();
        $doucount=$beanlist?$beanlist["beancount"]:0;
        echo json_encode(array("bean"=>$doucount,"left"=>$left));
        exit();
    }

    /**
     * 风险评估的题目
     */
    public function fengxian(){
        $list=M("risk_problem g")->field("g.*, answer_id")->join("lzh_risk_result s on g.id=s.problem_id and s.uid='{$this->uid}'")->order("g.id")->select();
        $data=[];
        foreach ($list as $k=>$v){
            $data[$k]["question"]=$v["problem"];
            $data[$k]["id"]=$v["id"];
            $data[$k]["answer_id"]=$v["answer_id"];
            $answerlist=M("risk_answer")->where(array("problem_id"=>$v["id"]))->order("id")->select();
            foreach ($answerlist as $ke=>$va){
                $data[$k]["answer"][$ke]=$va["answer"];
                $data[$k]["score"][$ke]=$va["score"];
                $data[$k]["answerid"][$ke]=$va["id"];
            }
        }
        unset($list);
        $this->assign("data",$data);
        $html=$this->fetch();
        echo json_encode(array("ret"=>0,"message"=>"获取数据成功","html"=>$html));
        exit();
    }

    /**
     * 答题保存结果
     */
    public function answer(){
        $risk_resultModel=M("risk_result");
        $answer=$_POST["data"];
        $risk_resultModel->where(array("uid"=>$this->uid))->delete();
        foreach ($answer as $value){
            $risk_resultModel->add(array(
                "uid"=>$this->uid,
                "problem_id"=>$value["problem_id"],
                "answer_id"=>$value["answer"],
                "time"=>time()
            ));
        }
        $result=$this->getresult($this->uid);
        echo json_encode(array("ret"=>0,"message"=>"恭喜您测评成功,您属于".$result));
        exit();
    }
}
