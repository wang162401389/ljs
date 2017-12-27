<?php
/**
 * 体验标详情
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/8/9
 * Time: 15:59
 */
class ExperienceAction extends HCommonAction
{
    public function detail()
    {
        $borrowinfo = M("borrow_info_experience")->find();//体验标静态数据
        $count = M("investor_detail_experience")->count();//总投资标数
        $hastouzi = 0;//是否投资 0未投资 1已经投资
        $zige = 1; //1 有资格  0无资格 体验标有无资格
        $pre = C('DB_PREFIX');

        if(!$this->uid){
            $zige=3;
            /**
            $this->redirect("M/pub/login");
            exit();
             * **/
        }
        if($zige!=3){
            $sql=" select c.* from {$pre}coupons c left join {$pre}members g on g.user_phone=c.user_phone where g.id={$this->uid} and status=0 and type=2 ";
            $cupos=M("coupons")->query($sql);
            if (!is_array($cupos) || !count($cupos) ) {
                $zige = 0;
            } else {
                if (($cupos[0]["endtime"] - time()) < 0) {
                    $zige = 0;
                }
            }
        }

           if ($zige == 1) {//如果有资格
                $list=M("investor_detail_experience")->where(array("investor_uid"=>"{$this->uid}"))->select();
                $flag = 0;//0 可以立即进行投标  1 已经投了体验标  2 没有实名认证 3 没有设置新浪密码
                if ($list && count($list)) {//是否投注体验标
                    $flag = 1;
                    $hastouzi = 1;
                } else {//没有投资
                    $realnamelist = M('members_status')->where("uid ={$this->uid}")->find();//查询是否实名认证
                    if ($realnamelist["id_status"] == 1) {//已经实名认证
                        //查看是否设置新浪密码
                        $issetpwd = $this->checkissetpaypwd($this->uid);
                        if ($issetpwd['is_set_paypass'] == 'Y') {
                            $flag = 0;
                        } else {
                            $flag = 3;
                        }
                    } else {
                        $flag = 2;//没有实名认证
                    }
                }
            }
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"投标");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("hastouzi",$hastouzi);
            $this->assign("count",$count);
            $this->assign("vo",$borrowinfo);
            $this->assign("flag",$flag);
            $this->assign("zige",$zige);
            $this->display();
    }

    /**
     * 投标成功
     */
    public function save(){
        $list=M("investor_detail_experience")->where(array("investor_uid"=>$this->uid))->find();//查找是否已经存在投注体验标
        if($list){
            $this->error("你已经使用过体验标，不能再次投注");
        }
        $mycoupons=M("coupons c")->field("c.*")->join("lzh_members t on t.user_phone=c.user_phone")->where(array("c.type"=>2 ,"t.id"=>$this->uid))->find();

        if($mycoupons){
            $cha=$mycoupons["endtime"]-time();
            if($cha<=0){
                $this->error("体验金已经过期，不能再次投注");
            }else if($mycoupons["status"]!=0){
                $this->error("体验金已是用无法再次使用");
            }
            else{
                $id=$_POST["id"];
                $borrowinfo = M("borrow_info_experience")->find();
                $data["repayment_time"]=0;
                $data["borrow_id"]=$id;
                $data["investor_uid"]=$this->uid;
                $data["capital"]=C("EXPERIENCE_MONEY");
                $data["interest"]=(($borrowinfo["borrow_interest_rate"]/100)*$borrowinfo["borrow_duration"]/360)*$borrowinfo["borrow_min"];   //此处是利息 ：利息计算方式为： 利率* （天数/360）*金额 此处为（ 12/100） *（5/360） *30000
                $data["status"]=1;
                $data["deadline"]=$borrowinfo["borrow_duration"]*24*3600+time();
                $data["add_time"]=time();
                $flag=M("investor_detail_experience")->add($data);
                $binfo_data['has_borrow'] = $borrowinfo['has_borrow']+$mycoupons['money'];
                $binfo_data['borrow_times'] = $borrowinfo['borrow_times'] + 1;
                M("borrow_info_experience")->where("id = 1")->save($binfo_data);
                if($flag){
                    $content="【链金所】尊敬的链金所用户您好！您投资的新手体验标已成功，您可登录平台账户查询详情，也可与客服中心联系400-6626-985.";
                    sendsms($mycoupons["user_phone"],$content);
                    M("coupons")->where(array("id"=>$mycoupons["id"]))->save(array("status"=>1));
                    $this->success("投标成功",U("Experience/jumpsuccess"));
                }else{
                    $this->error("投标失败");
                }
            }
        }else{
            $this->error("数据错误");
        }

    }

    /**
     * 投标成功页面
     */
    public function jumpsuccess(){
        $simple_header_info=array("url"=>"/M/user/index.html","title"=>"投标成功");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 一块购介绍
     */
    public function  introduce(){
        $simple_header_info=array("url"=>"/M/user/index.html","title"=>"链金豆");
        $this->assign("simple_header_info",$simple_header_info);
       $this->display();
    }

    //重定向到新浪设置支付密码
    public function setpaypassword(){
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
        $data['return_url']			  = "https://".$_SERVER['HTTP_HOST']."/m/user/index"; 	//回调充值页面
        ksort($data);
        $data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
        $setdata 					  = $weibopay->createcurl_data($data);
        $result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
        $rs = $this->checksinaerror($result);
        redirect($rs['redirect_url']);
    }



    /**
     * 体验金规则介绍
     */
    public function rule(){
        $borrowinfo = M("borrow_info_experience")->find();//体验标静态数据
        $this->assign("borrowinfo",$borrowinfo);
        $simple_header_info=array("url"=>"/M/user/mycoupons.html","title"=>"体验金使用规则");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 加息券规则介绍
     */
    public function jxrule(){
        $borrowinfo = M("borrow_info_experience")->find();//体验标静态数据
        $this->assign("borrowinfo",$borrowinfo);
        $simple_header_info=array("url"=>"/M/user/mycoupons.html","title"=>"加息券使用规则");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

     /**
     * 体验金规则介绍
     */
    public function projectcontent(){
        $borrowinfo = M("borrow_info_experience")->find();//体验标静态数据
        $this->assign("borrowinfo",$borrowinfo);
        $simple_header_info=array("url"=>"/M/experience/detail","title"=>"项目信息");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }


    //投资记录
        public function bidhistory(){
            $Page = D('Page');
            import("ORG.Util.Page");
            $count = M("investor_detail_experience")->where('borrow_id='.intval($_GET['borrow_id']))->count('id');
            $Page     = new Page($count,10);
            $Page->setConfig('theme',"%upPage% %downPage% 共%totalPage% 页");
            $show = $Page->show();
            $this->assign('page', $show);
            $this->assign("total_page",$Page->get_total_page());
            $list = M("investor_detail_experience as b")
                    ->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
                    ->join(C(DB_PREFIX)."borrow_info_experience as i on  b.borrow_id = i.id")
                    ->field('b.capital,m.user_phone,b.add_time')
                    ->where('b.borrow_id='.intval($_GET['borrow_id']))->order('b.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
                $string = '';
                 foreach($list as $k=>$v){
                    $relult=$k%2;
                    if(!$relult){
                        $string .= "<tr>
                   <td width='32%'>".hidecard($v['user_phone'],2)."</td>";
                    }else{
                        $string .= "<tr>
                   <td width='32%'>".hidecard($v['user_phone'],2)."</td>";
                    }
                    $string .= "
                      <td width='32%' class='money_orange'>".Fmoney($v['capital'])."元</td>
                      <td width='36%'>".date("Y-m-d H:i",$v['add_time'])."</td>
                     </tr>";
                }
                if($string == null){
                    $string = '<tr><td colspan="3">暂时没有投资记录</td></tr>';
                }
                $borrow = M("borrow_info_experience")->where("id = ".intval($_GET['borrow_id']))->field("borrow_money,has_borrow")->find();
                $this->assign("borrow",$borrow);
                $this->assign("list",$string);
            $simple_header_info=array("url"=>"/M/experience/detail","title"=>"投资记录");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
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

    //新浪找回支付密码
    public function setsinapwd(){
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service'] 			  = "find_pay_password";									//绑定认证信息的接口名称
        $data['version']			  = $payConfig['sinapay']['version'];						//接口版本
        $data['request_time']		  = date('YmdHis');											//请求时间
        $data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
        $data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
        $data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
        $data['identity_id']		  = "20151008".$this->uid;									//用户ID
        $data['identity_type'] 		  = "UID";													//用户标识类型 UID
        $data['return_url']           = "https://".$_SERVER['HTTP_HOST']."/M/user/index";
        ksort($data);
        $data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
        $setdata 					  = $weibopay->createcurl_data($data);
        $result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
        $rs = $this->checksinaerror($result);
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
}