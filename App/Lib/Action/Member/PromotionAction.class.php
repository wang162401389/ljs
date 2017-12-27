<?php
// 本类由系统自动生成，仅供测试用途
class PromotionAction extends MCommonAction {

    public function index(){
        import("@.conf.friend_invest");
        $friend_invest=new friend_invest();
        $friend_invest_show=$friend_invest->is_show($this->uid);
        $this->assign("friend_invest_show",$friend_invest_show);
        if($_GET['excel'])
        {
            $this->export();
        }
		$this->display();
    }

    public function promotion(){
        $url = C('DISTRIBUTION.url');
		$_P_fee=get_global_setting();
		$this->assign("reward",$_P_fee);
        $this->assign('url',$url);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function promotionlog(){
		$map['uid'] = $this->uid;
		$map['type'] = array("in","1,13");
		$list = getMoneyLog($map,15);

		$totalR = M('member_moneylog')->where("uid={$this->uid} AND type in(1,13)")->sum('affect_money');
		$this->assign("totalR",$totalR);
		$this->assign("CR",M('members')->getFieldById($this->uid,'reward_money'));
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);

		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function promotionfriend(){
		$pre = C('DB_PREFIX');
		$uid=session('u_id');
		$field = " m.id,m.user_name,m.reg_time,sum(ml.affect_money) jiangli ";
		$field1 = " m.user_name,m.reg_time";
		$vm = M("members m")->field($field)->join(" lzh_member_moneylog ml ON m.id = ml.target_uid ")->where(" m.recommend_id ={$uid} AND ml.type =13")->group("ml.target_uid")->select();
		$vm1 = M("members m")->field($field1)->where(" m.recommend_id ={$uid}")->group("m.id")->select();
		$this->assign("vm",$vm);
		$this->assign("vi",$vm1);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	//查询用户是否设置新浪支付密码
	public function checkissetpwd(){

        $ids = M('members m')->join("lzh_members_status s on s.uid = m.id")->where("m.id={$this->uid}")->field('m.user_regtype,s.id_status,s.company_status')->find();
        if($ids['user_regtype'] == 1){
             if($ids['id_status']!=1){
                redirect('/member/verify?id=1#fragment-1'); //实名认证
            }
        }else{
             if($ids['company_status']!=3){
                redirect('/member/verify?id=1#fragment-1'); //企业认证
            }
        }
        $i = $_REQUEST['i'];
        $members_statusModel=M("members_status");
        $info=$members_statusModel->where(array("uid"=>$this->uid))->find();
        if($info["is_pay_passwd"]==1){
             if($i==1){
                 $this->success("您已设置过新浪支付密码","__APP__/member/promotion#fragment-1");
             }else{
                 $this->success("您已设置过新浪支付密码","__APP__/member");
             }
        }else{
            $_SESSION['setpaypwd_url'] = $i;
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
            $rs =  $this->checksinaerror($result);
            if($rs['is_set_paypass'] == 'N'){
                $this->del_mem_cach();
                $this->setpaypwd($i);		//设置支付密码
            }elseif($i ==1){
                $notset = M('members_status')->where(['uid'=>$this->uid,'is_pay_passwd'=>1])->find();
                $result = $members_statusModel->where(array("uid"=>$this->uid))->save(array("is_pay_passwd"=>1));//存储新浪密码为已经设置状态
                if($notset==null&&$result!==false)
                {
                    //设置新浪密码成功
                    setPaypasswd($this->uid);
                }
                $this->success("您已设置过新浪支付密码","__APP__/member/promotion#fragment-1");
            }else{
                $notset = M('members_status')->where(['uid'=>$this->uid,'is_pay_passwd'=>1])->find();
                $members_statusModel->where(array("uid"=>$this->uid))->save(array("is_pay_passwd"=>1));//存储新浪密码为已经设置状态
                if($notset==null&&$result!==false)
                {
                    //设置新浪密码成功
                    setPaypasswd($this->uid);
                }
                $this->success("您已设置过新浪支付密码","__APP__/member");
            }
        }
	}
	//重定向到新浪设置支付密码
	public function setpaypwd($i){
		if(isset($_SESSION['setpaypwd_url']) || !is_null($_SESSION['setpaypwd_url']))$i = $_SESSION['setpaypwd_url'];
		import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "set_pay_password";										//绑定认证信息的接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id']		  = "20151008".$this->uid;						//用户ID
		$data['identity_type'] 		  = "UID";													//用户标识类型 UID
		if($i==1){
			$data['return_url']			  = "http://".$_SERVER['HTTP_HOST']."/member/promotion#fragment-1";
		}elseif($i==3){
            $data['return_url']           = "http://".$_SERVER['HTTP_HOST']."/home/experience/detail";
        }else{
			$data['return_url']			  = "http://".$_SERVER['HTTP_HOST']."/member";
		}
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		$rs = $this->checksinaerror($result);
        $_SESSION['setpaypwd_url'] = null;
		redirect($rs['redirect_url']);
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
    public function promotioninvest(){
        /*
            $map=array();
            if(!empty($_REQUEST['start_time'])){
                $start=strtotime(date("Y-m-d 00:00:00",strtotime($_REQUEST['start_time'])));
                $end=strtotime(date("Y-m-d 23:59:59",strtotime($_REQUEST['end_time'])));
                $search['start_time']=$_REQUEST['start_time'];
                $search['end_time']=$_REQUEST['end_time'];
                $map['i.add_time']=array("between",array($start,$end));
            }

            import("@.conf.friend_invest");
            $friend=new friend_invest();
            $info=$friend->get_friend_invest($this->uid,$map);
            $count=count($info);
            import("ORG.Util.PageFilter");

            $p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $min =$p->firstRow;
            $max=$p->listRows+$min;
            $info1=array();
             $sum=0;
            foreach($info as $key=>$val){
                if(($key>=$min)&&($key<$max)){
                    $val['reg_time']=date("Y-m-d H:i:s",$val['reg_time']);
                    $val['add_time']=date("Y-m-d H:i:s",$val['add_time']);
                    if($val['repayment_type']==1){
                        $val['borrow_duration']=$val['borrow_duration']."天";
                    }else{
                        $val['borrow_duration']=$val['borrow_duration']."月";
                    }
                    $info1[]=$val;
                }
                $sum=getFloatValue(($sum+$val['investor_capital']),2);
            }
            $this->assign("sum",$sum);
            $this->assign("pagebar", $page);
            $this->assign("info",$info1);
            $data['html'] = $this->fetch();
            exit(json_encode($data));
        */
    }
    private function mask_name($str){
        $count=mb_strlen($str,"UTF-8");
        if($count==2){
            $name=mb_substr($str,0,1,"UTF-8")."*";
        }else{
            $num=$count-2;
            $xin="";
            for($i=0;$i<$num;$i++){
                $xin.="*";
            }
            $name=mb_substr($str,0,1,"UTF-8").$xin.mb_substr($str,$count-1,1,"UTF-8");
        }
        return $name;
    }
	public  function promotionresult(){
        if(C("Frind_INFO.enable")!=1){
            return;
        }

        Log::write(var_export($_GET,true));
        if(($_GET['start']!="")&&($_GET['end']!="")){
            $start=strtotime($_REQUEST['start']);
            $end=strtotime($_REQUEST['end']);
            if($start>$end){
                $start=strtotime($_REQUEST['end']."00:00:00");
                $end=strtotime($_REQUEST['start']."23:59:59");
            }else{
                $start=strtotime($_REQUEST['start']."00:00:00");
                $end=strtotime($_REQUEST['end']."23:59:59");
            }
            Log::write("搜索时间".$start."-".$end);
            $where['m.reg_time']=array("between",array($start,$end));
        }


        if(($_GET['start2']!="")&&($_GET['end2']!="")){
            $start2=strtotime($_REQUEST['start2']);
            $end2=strtotime($_REQUEST['end2']);
            if($start2>$end2){
                $start2=strtotime($_REQUEST['end2']."00:00:00");
                $end2=strtotime($_REQUEST['start2']."23:59:59");
            }else{
                $start2=strtotime($_REQUEST['start2']."00:00:00");
                $end2=strtotime($_REQUEST['end2']."23:59:59");
            }
            Log::write("复审搜索时间".$start2."-".$end2);
            $where['bi.second_verify_time']=array("between",array($start2,$end2));
        }

        $where['m.recommend_id']=$this->uid;
        $field="m.user_name,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d'),bi.borrow_name,bi.second_verify_time,b.investor_capital,bi.borrow_duration_txt,op.return_money,cp.money,m.id,m.reg_time";

        $result = M("members m")
                ->join("inner join lzh_borrow_investor as b on b.investor_uid=m.id")
                ->join("inner join lzh_borrow_info as bi on bi.id=b.borrow_id")
                ->join("lzh_member_info mi on m.id=mi.uid")
                ->join("lzh_outside_profit op ON op.investor_id = b.id")
                ->join("lzh_company_profit cp ON cp.investor_id = b.id")
                ->field($field)
                ->where($where)
                ->order("bi.second_verify_time DESC")
                ->select();

        $person_num =  sizeof(array_unique(array_column($result, "id")));

        $where["ms.id_status"] = 1;
        $real_name_num = M("members m")
                        ->join("inner join lzh_borrow_investor as b on b.investor_uid=m.id")
                        ->join("inner join lzh_borrow_info as bi on bi.id=b.borrow_id")
                        ->join("lzh_member_info mi on m.id=mi.uid")
                        ->join("lzh_members_status ms ON ms.uid = m.id")
                        ->join("lzh_outside_profit op ON op.investor_id = b.id")
                        ->join("lzh_company_profit cp ON cp.investor_id = b.id")
                        ->field($field)
                        ->where($where)
                        ->order("bi.second_verify_time DESC")
                        ->select();
        $real_name_num =  sizeof(array_unique(array_column($real_name_num, "id")));
        
        $investor_num=0;
        $list=array();
        foreach($result as $key=>$val){
            if($val['investor_capital']!=""){
                $investor_num+=$val['investor_capital'];
            }
            if($val["money"] != ""){
                $val["profit_money"] = $val["money"];
            }elseif($val["return_money"] != ""){
                $val["profit_money"] = $val["return_money"];
            }
            $val['user_name']=$this->mask_name( $val['user_name']);
            // if($val['real_name']!=""){
            //     $real_name_num++;
            //     $val['real_name']='是';
            // }else{
            //     $val['real_name']='否';
            // }
            // if($val['investor_capital']!=""){
            //     $val['investor_capital']=getFloatValue($val['investor_capital'],2);
            //     $investor_num+=$val['investor_capital'];
            //     $val['investor_capital']="是";
            // }else{
            //     $val['investor_capital']="否";
            // }
            //
            // $val["reg_time"]=date("Y-m-d H:i:s",$val["reg_time"]);
            if($val["second_verify_time"] != 0){
                $list[]=$val;
            }
        }

        if($_GET['excel']!="")
        {
            $header = ['会员名单','会员姓名','注册时间','投资标的','复审时间','投资金额','投资期限','返利'];
            $data = $result;
            exportToCSV($header,$data,"recommend.csv");
            die;

        }

        $this->assign("person_num",$person_num);
        $ab['start_time1'] = strtotime($_GET['start']);
        $ab['end_time1']   = strtotime($_GET['end']);
        $ab['start_time2'] = strtotime($_GET['start2']);
        $ab['end_time2']   = strtotime($_GET['end2']);

        //  $ab['start_time1'] = '0';
        // $ab['end_time1'] = '12';
        // $ab['start_time2'] = $_GET['start2'];
        // $ab['end_time2'] = $_GET['end2'];
        
        $this->assign("search",$ab);
        $this->assign("real_name_num",$real_name_num);
        $this->assign("investor_num",$investor_num);
        $this->assign("list",$list);
        if($_GET['tag']==""){
            $this->assign("tag","all");
        }else{
            $this->assign("tag",$_GET['tag']);
        }

        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }


    private function export(){
        if(C("Frind_INFO.enable")!=1){
            return;
        }

        Log::write(var_export($_GET,true));
        if(($_GET['start']!="")&&($_GET['end']!="")){
            $start=strtotime($_REQUEST['start']);
            $end=strtotime($_REQUEST['end']);
            if($start>$end){
                $start=strtotime($_REQUEST['end']."00:00:00");
                $end=strtotime($_REQUEST['start']."23:59:59");
            }else{
                $start=strtotime($_REQUEST['start']."00:00:00");
                $end=strtotime($_REQUEST['end']."23:59:59");
            }
            Log::write("搜索时间".$start."-".$end);
            $where['m.reg_time']=array("between",array($start,$end));
        }


        if(($_GET['start2']!="")&&($_GET['end2']!="")){
            $start2=strtotime($_REQUEST['start2']);
            $end2=strtotime($_REQUEST['end2']);
            if($start2>$end2){
                $start2=strtotime($_REQUEST['end2']."00:00:00");
                $end2=strtotime($_REQUEST['start2']."23:59:59");
            }else{
                $start2=strtotime($_REQUEST['start2']."00:00:00");
                $end2=strtotime($_REQUEST['end2']."23:59:59");
            }
            Log::write("复审搜索时间".$start2."-".$end2);
            $where['bi.second_verify_time']=array("between",array($start2,$end2));
        }

        $where['m.recommend_id']=$this->uid;
        if(!isset($where['bi.second_verify_time'])){
            $where['bi.second_verify_time']=array('neq',0);    
        }
        
        $field="m.user_name,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d'),bi.borrow_name,date_format(from_unixtime(bi.`second_verify_time`),'%Y-%m-%d'),b.investor_capital,bi.borrow_duration_txt,op.return_money,cp.money,m.id,m.reg_time,bi.second_verify_time";

        $result = M("members m")
                ->join("inner join lzh_borrow_investor as b on b.investor_uid=m.id")
                ->join("inner join lzh_borrow_info as bi on bi.id=b.borrow_id")
                ->join("lzh_member_info mi on m.id=mi.uid")
                ->join("lzh_outside_profit op ON op.investor_id = b.id")
                ->join("lzh_company_profit cp ON cp.investor_id = b.id")
                ->field($field)
                ->where($where)
                ->order("bi.second_verify_time DESC")
                ->select();

        $person_num =  sizeof(array_unique(array_column($result, "id")));

        $where["ms.id_status"] = 1;
        $real_name_num = M("members m")
                        ->join("inner join lzh_borrow_investor as b on b.investor_uid=m.id")
                        ->join("inner join lzh_borrow_info as bi on bi.id=b.borrow_id")
                        ->join("lzh_member_info mi on m.id=mi.uid")
                        ->join("lzh_members_status ms ON ms.uid = m.id")
                        ->join("lzh_outside_profit op ON op.investor_id = b.id")
                        ->join("lzh_company_profit cp ON cp.investor_id = b.id")
                        ->field($field)
                        ->where($where)
                        ->order("bi.second_verify_time DESC")
                        ->select();
        $real_name_num =  sizeof(array_unique(array_column($real_name_num, "id")));
        
        $investor_num=0;
        $list=array();
        foreach($result as $key=>$val){
            if($val['investor_capital']!=""){
                $investor_num+=$val['investor_capital'];
            }
            if($val["money"] != ""){
                $val["profit_money"] = $val["money"];
            }elseif($val["return_money"] != ""){
                $val["profit_money"] = $val["return_money"];
            }
            $val['user_name']=$this->mask_name( $val['user_name']);
            // if($val['real_name']!=""){
            //     $real_name_num++;
            //     $val['real_name']='是';
            // }else{
            //     $val['real_name']='否';
            // }
            // if($val['investor_capital']!=""){
            //     $val['investor_capital']=getFloatValue($val['investor_capital'],2);
            //     $investor_num+=$val['investor_capital'];
            //     $val['investor_capital']="是";
            // }else{
            //     $val['investor_capital']="否";
            // }
            //
            // $val["reg_time"]=date("Y-m-d H:i:s",$val["reg_time"]);
            if($val["second_verify_time"] != 0){
                $list[]=$val;
            }
        }

        if($_GET['excel']!="")
        {
            $header = ['会员名单','会员姓名','注册时间','投资标的','复审时间','投资金额','投资期限','返利'];
            $data = $result;
            $rebate = 0;
            $totalinvest = 0;
            foreach ($data as $key => $value) {
                if($data[$key]["money"]!="")
                {
                    $data[$key]["return_money"] = $value["money"];
                }else{
                    $data[$key]["return_money"] = $value["return_money"];
                }
                if($data[$key]['return_money'] == "")
                {
                    $data[$key]["return_money"] = 0;
                }

                $rebate +=$data[$key]['return_money'];
                $totalinvest += $data[$key]['investor_capital'];
                
                unset($data[$key]['reg_time']);
                unset($data[$key]['second_verify_time']);
                unset($data[$key]['money']);
                unset($data[$key]['id']);
            }
            $data[] = ['合计','\\','\\','\\','\\',$totalinvest,'\\',$rebate];
            exportToCSV($header,$data,"recommend.csv");
            die;

        }
    }
}
