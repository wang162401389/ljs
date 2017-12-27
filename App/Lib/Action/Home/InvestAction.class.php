<?php
/*投标
*/
class InvestAction extends HCommonAction {

	public function _initialize()
	{
		parent::_initialize();
		$title="个人理财投资产品,网络理财产品,P2P理财产品,互联网理财产品首选链金所";
		$keyword="个人理财投资产品,网络理财产品,P2P理财产品,互联网理财产品";
		$description="个人理财投资产品,网络理财产品,P2P理财产品,互联网理财产品首选链金所,链金所依托高效的互联网信息发布渠道,提供专业,个性化的网络理财金融服务.";
		$this->assign("title",$title);
		$this->assign("keyword",$keyword);
		$this->assign("description",$description);
	}

	/**
    * 普通标列表
    * 
    */
    public function index()
    {
    	//分销员信息接收
    	session("salesman_usrid", $_REQUEST["salesman_usrid"]);
    	session("salesman_2_usrid", $_REQUEST["salesman_2_usrid"]);
    	session("way", $_REQUEST["way"]);
    	$disdata["usrid"] = $_SESSION["salesman_usrid"];
    	$disdata["source"] = $_SESSION["way"];
    	setDistribut($disdata);
        static $newpars;
		//预发标的借款
        $parm = $searchMap = array();
		$searchMap['b.borrow_status'] = 0;
		$curl = $_SERVER['REQUEST_URI'];
		$urlarr = parse_url($curl);
		parse_str($urlarr['query'], $surl);//array获取当前链接参数，2.

        $urlArr = array('borrow_status','borrow_duration','product_type','borrow_interest_rate');

		$searchMap['borrow_status'] = array("all"=>"不限","2"=>"进行中","8"=>"待发布","4"=>"复审中","6"=>"还款中","7"=>"已完成");
		$searchMap['borrow_duration'] = array("all"=>"不限","0-30"=>"30天内","30-90"=>"30-90天","90-180"=>"90-180天","180-360"=>"180-360天");
        $searchMap['product_type'] = array("all"=>"不限","5-6"=>"信金链","4"=>"融金链","7"=>"优金链","1-3"=>"质金链","8"=>"保金链",'10'=>'质金链（保）');
        $searchMap['borrow_interest_rate'] = array("all"=>"不限","8-10"=>"8%-10%","10-12"=>"10%-12%","12-15"=>"12%-15%");
		
		$mysearch=" where ( 1=1 ";
		$initstatus = 0;

		// 满标以后新手标需要显示在列表中，同时支持搜索
		// 在此增加临时变量，记录搜索产生的sql，供后面拼接使用
		$searchsql = "";
		
		//搜索条件
		foreach($urlArr as $v){
		    $newpars = $surl;//用新变量避免后面的连接受影响
		    unset($newpars[$v], $newpars['type'], $newpars['order_sort'], $newpars['orderby']);//去掉公共参数，对掉当前参数
		    
		    foreach($newpars as $skey => $sv){
		        if($sv == "all") {
		            unset($newpars[$skey]);//去掉"全部"状态的参数,避免地址栏全满
		        }
		    }
		    
		    $newurl = http_build_query($newpars);//生成此值的链接,生成必须是即时生成
		    $_GET[$v] = text($_GET[$v], false, true);
		    $searchUrl[$v]['url'] = $newurl;
		    $searchUrl[$v]['cur'] = empty($_GET[$v]) ? "all" : $_GET[$v];
		    
			if($_GET[$v] && $_GET[$v] <> 'all'){
				switch($v){
					case 'borrow_status':
						$mysearch.=" and b.borrow_status=".intval($_GET[$v]);
						$searchsql.=" and b.borrow_status=".intval($_GET[$v]);
						$initstatus = $_GET[$v];
					   break;
                    case 'borrow_duration':
						$barr = explode("-", text($_GET[$v]));
						$mysearch.=" and case when b.repayment_type=1 then
                     b.borrow_duration between $barr[0] and $barr[1]
                     else   b.borrow_duration between $barr[0]/30 and $barr[1]/30 end ";
                     	$searchsql.=" and case when b.repayment_type=1 then
                     b.borrow_duration between $barr[0] and $barr[1]
                     else   b.borrow_duration between $barr[0]/30 and $barr[1]/30 end ";
					   break;
                    case 'product_type':
                        $ag = $_GET[$v];
                        if(strpos($ag,"-") === false){
							 $mysearch .= " and product_type=".intval($_GET[$v]);
							 $searchsql.= " and product_type=".intval($_GET[$v]);
                        }else{
                        	$tg = explode("-", $ag);
							$mysearch .= " and product_type between ".intval(trim($tg[0]))." and " .intval(trim($tg[1]));
							$searchsql.= " and product_type between ".intval(trim($tg[0]))." and " .intval(trim($tg[1]));
                        }
                        break;
                    default:
                        $barr = explode("-",text($_GET[$v]));
						$mysearch .= " and $v between ".intval(trim($barr[0]))." and " .intval(trim($barr[1])) ;
						$searchsql.= " and $v between ".intval(trim($barr[0]))." and " .intval(trim($barr[1])) ;
				}
			}
		}
	
		if($initstatus == 0){
			$mysearch .= " and b.borrow_status in(2,4,6,7,8)";
		}

		$str = "%".urldecode($_REQUEST['searchkeywords'])."%";
		if($_GET['is_keyword'] == '1'){
			$mysearch .= " and m.user_name like '".$str."' ";
		}elseif($_GET['is_keyword'] == '2'){
			$mysearch .= " and b.borrow_name like '".$str."' ";
		}
		// 不包含新手标
		$mysearch .= " and b.is_beginnercontract = 0 and b.test=0 ) or (b.is_beginnercontract=1 and b.test=0 and b.borrow_status in ('4','6','7')  {$searchsql} ) ";
		$parm['map'] = $mysearch;
		$parm['pagesize'] = 10;
		//排序
		$sort = strtolower($_GET['sort']) == "asc" ? "desc" : "asc";
		$parm['orderby'] = "INSTR('2,8,4,6,7',borrow_status) ASC,b.id DESC";
		if($_GET['orderby']){
			if(strtolower($_GET['orderby']) == "rate"){
			    $parm['orderby'] = "b.borrow_interest_rate ".text($_GET['sort']);
			}
			elseif(strtolower($_GET['orderby']) == "borrow_money") {
			    $parm['orderby'] = "b.borrow_money ".text($_GET['sort']);
			}
			else{
			    $parm['orderby'] = "b.id DESC";
			}
		}
		
		//排序
		$this->assign("searchUrl", $searchUrl);
        $this->assign("searchMap", $searchMap);
        $this->assign("list", getBorrowList($parm, 3));
        $this->display();
    }
	
    public function detail(){
		$id = intval($_GET['id']);
		session("lastpcid", $id);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		//合同ID
		$invsx = 'login';
		if($this->uid){
			$invs = M('borrow_investor')->field('id')->where("borrow_id={$id} AND (investor_uid={$this->uid})")->find();
			if($invs['id'] > 0) {
			    $invsx = $invs['id'];
			}elseif(!is_array($invs)){
			    $invsx = 'no';
			}
		}
		$this->assign("invid", $invsx);

		//合同ID
		$borrowinfo = M("borrow_info bi")->field('bi.*,mc.company_name')->join('lzh_members_company mc on mc.uid= bi.danbao')->where('bi.id='.$id)->find();
		if(!is_array($borrowinfo) || ($borrowinfo['borrow_status'] == 0 && $this->uid != $borrowinfo['borrow_uid'])){
		    $this->error("数据有误");
		}
		$borrowinfo['biao'] = $borrowinfo['borrow_times'];
		$borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
        if($borrowinfo['borrow_status'] == 8){
            //待发标
            $borrowinfo['lefttime'] = $borrowinfo['add_time'] - time();
        }else{
            $borrowinfo['lefttime'] = $borrowinfo['collect_time'] - time();
        }		
		$borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow'] / $borrowinfo['borrow_money'] * 100, 2);

		// 图片资料
		$updata = unserialize($borrowinfo['updata']);
		$upphoto = array();
		foreach ($updata as $item) {
			$upphoto[] = $item['img'];
		}
		
		$this->assign("vplist", $upphoto);
		$this->assign("vo", $borrowinfo);
		
		$is_white_investor = in_array($this->uid, ['73661']) ? 1 : 0;
		$this->assign('is_white_investor', $is_white_investor);

		$pre = C('DB_PREFIX');
		$memberinfo = M("members m")->field("m.id,m.customer_name,m.customer_id,m.user_name,m.reg_time,m.credits,fi.*,mi.*,mm.*")->join("{$pre}member_financial_info fi ON fi.uid = m.id")->join("{$pre}member_info mi ON mi.uid = m.id")->join("{$pre}member_money mm ON mm.uid = m.id")->where("m.id={$borrowinfo['borrow_uid']}")->find();
		$areaList = getArea();
		$memberinfo['location'] = $areaList[$memberinfo['province']].$areaList[$memberinfo['city']];
		$memberinfo['location_now'] = $areaList[$memberinfo['province_now']].$areaList[$memberinfo['city_now']];
		$memberinfo['zcze']=$memberinfo['account_money']+$memberinfo['back_money']+$memberinfo['money_collect']+$memberinfo['money_freeze'];
		$this->assign("minfo",$memberinfo);

		//data_list
		$data_list = M("member_data_info")->field('type,add_time,count(status) as num,sum(deal_credits) as credits')->where("uid={$borrowinfo['borrow_uid']} AND status=1")->group('type')->select();
		$this->assign("data_list",$data_list);
		//data_list
		
        // 投资记录
		$html=$this->getrecode(1,$id);
		$this->assign("html",$html);
        $this->assign('borrow_id', $id);

		//近期还款的投标
		$history = getDurationCount($borrowinfo['borrow_uid']);
		$this->assign("history",$history);

		//investinfo
		$fieldx = "bi.investor_capital,bi.add_time,m.user_name,bi.is_auto";
		$investinfo = M("borrow_investor bi")->field($fieldx)->join("{$pre}members m ON bi.investor_uid = m.id")->limit(10)->where("bi.borrow_id={$id}")->order("bi.id DESC")->select();

		//直接调取新浪余额
		$sinasaving = querysaving($this->uid);
		$sinabalance = querybalance($this->uid);
		$investinfo['account_money']=$sinasaving+$sinabalance;//number_format($sinasaving+$sinabalance, 2, ".", "" );

		$this->assign("investinfo",$investinfo);
		//investinfo
		
		//帐户资金情况
		$this->assign("investInfo", getMinfo($this->uid,true));
		$this->assign("mainfo", getMinfo($borrowinfo['borrow_uid'],true));
		$this->assign("capitalinfo", getMemberBorrowScan($borrowinfo['borrow_uid']));
		//帐户资金情况
		//展示资料
		$show_list = M("member_borrow_show")->where("uid={$borrowinfo['borrow_uid']}")->order('sort DESC')->select();
		$this->assign("show_list",$show_list);
		//展示资料
		
		//上传资料类型
		$upload_type = FilterUploadType(FS("Webconfig/integration"));
		$this->assign("upload_type", $upload_type); // 上传资料所有类型

		// 质押跟踪
		$list = M('member_genzong')->where('borrow_id='.$id)->order('id asc')->select();
		$this->assign("listcount",count($list));
		$this->assign("productty",$borrowinfo['product_type']);
        $this->assign("list",$list);
        $this->assign("p_type",$borrowinfo['product_type']);
		
		//评论
		$cmap['tid'] = $id;
		$clist = getCommentList($cmap,5);
		$this->assign("Bconfig",$Bconfig);
		$this->assign("gloconf",$this->gloconf);
		$this->assign("commentlist",$clist['list']);
		$this->assign("commentpagebar",$clist['page']);
		$this->assign("commentcount",$clist['count']);

		//投资卷
		$coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")->where("m.id = {$this->uid} AND c.status = 0")->count();
		$this->assign("coupons", $coupons);
        //补充说明
        if($borrowinfo['product_type'] == 2){
            $extra_info=D('borrow_info_additional')->get_extra_info($id);
            $this->assign("extra_info", $extra_info);
        }
		$count = M("borrow_investor")->where('borrow_id='.$id.' AND debt_id = 0')->count('id');//投资记录数
		$this->assign("touzicount", $count);
		
        //touzitype  投资问卷调查状态：0未登录    1 已经登录未填写   2 已经登录但是忽略 3已经填写
		$touzitype = 0;
		$fxpg_popup_status = 1;
		if($this->uid){
		    $touzitype = 2;
            if(!isset($_COOKIE["touzitype"])){//是否跳过
                $touzitype = 1;
                if(M("risk_result")->where(array("uid"=>$this->uid))->find()){
                    $touzitype = 3;
                }
            }
            
            $fxpg_popup_status = M("members_status")->where(array("uid" => $this->uid))->getField('fxpg_popup_status');
            $risk = M("risk_result")->where(array("uid" => $this->uid))->limit(1)->find();
            $fxpg_popup_status = empty($risk) ? $fxpg_popup_status : 0;
        }
        
		$this->assign("touzitype", $touzitype);
		$this->assign("fxpg_popup_status", $fxpg_popup_status);

		// new beginner contract 
		$model = new MembersStatusModel();
        $userstatus = $model->getUserStatus();
        $this->assign('is_newhand',intval(intval($userstatus&8)!=8));
		$this->display();
    }
	
	public function investcheck(){
		$pre = C('DB_PREFIX');
		if(!$this->uid) {
			ajaxmsg('',3);
			exit;
		}
		// $pin = md5($_POST['pin']);
		$borrow_id = intval($_POST['borrow_id']);//标号
		$money = intval($_POST['money']);//投资金客
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		//$vm['account_money'] 账户余额
		$sinasaving = querysaving($this->uid);
		$sinabalance = querybalance($this->uid);
		if($sinabalance > "0.00"){
			$amoney = $sinabalance;
		}else{
			$amoney = $sinasaving;
		}
		//$amoney = $sinasaving+$sinabalance;//$vm['account_money']+$vm['back_money'];
		$uname = session('u_user_name'); // 当前登录用户名
		$pin_pass = $vm['pin_pass'];
		$amoney = floatval($amoney);

		$binfo = M("borrow_info")->field('borrow_money,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect')->find($borrow_id);
		if(!empty($binfo['password'])){
			if(empty($_POST['borrow_pass'])) ajaxmsg("此标是定向标，必须验证投标密码",3);
			else if($binfo['password']<>md5($_POST['borrow_pass'])) ajaxmsg("投标密码不正确",3);
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			ajaxmsg("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}",3);
		}
		
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money>$caninvest && ($need-$money)<>0 ){
			$msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>或者投标金额必须<font color='#FF0000'>小于等于{$caninvest}元</font>";
			if($caninvest<$binfo['borrow_min']) $msg = "尊敬的{$uname}，此标还差{$need}元满标,如果您投标{$money}元，将导致最后一次投标最多只能投".($need-$money)."元，小于最小投标金额{$binfo['borrow_min']}元，所以您本次可以选择<font color='#FF0000'>满标</font>即投标金额必须<font color='#FF0000'>等于{$need}元</font>";

			ajaxmsg($msg,3);
		}
		if(($binfo['borrow_min']-$money)>0 ){
			$this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
		}
		if(($need-$money)<0 ){
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
		}
		
		// if($pin<>$pin_pass) ajaxmsg("支付密码错误，请重试!",0);
		if($money>$amoney){
			$msg = "尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，您要先去充值吗？";
			ajaxmsg($msg,2);
		}else{
			$msg = "尊敬的{$uname}，您的账户可用余额为{$amoney}元，您确认投标{$money}元吗？";
			ajaxmsg($msg,1);
		}
		ajaxmsg($msg,1);
	}
		
	public function investmoney(){
		//if(!$this->uid) exit;
		if(!$this->uid) {
			$this->error('请先登录',3);
			exit;
		}
        if(C("Cach.member_info")){
            //删除cach
            $path="html/member_info/".date("Ymd")."/";
            $filename=$this->uid.".html";
            unlink($path.$filename);
        }
        $jx = $_GET['jx'];//加息券序列号
        $coupons = $_GET['coupons'];
        // 记录加息券金额
        $coupon_amount = 0;
        if($coupons != null || $coupons != 0){
        	$coupon_info = explode("|", $coupons);
        	$money = intval($_GET['money'])-$coupon_info[0];
        	//加息券减免
        	$coupon_amount = $coupon_info[0];
        }else{
        	$money = intval($_GET['money']);
        }

		$borrow_id = intval($_GET['borrow_id']);
		$m = M("member_money")->field('account_money,back_money,money_collect')->find($this->uid);
		$sinasaving = querysaving($this->uid);
		$sinabalance = querybalance($this->uid);
		//if($sinabalance > "0.00"){
			$amoney = $sinabalance+$sinasaving;
		// }else{
		// 	$amoney = $sinasaving;
		// }
		//$amoney = $sinasaving+$sinabalance;//$vm['account_money']+$vm['back_money'];
		$uname = session('u_user_name');
		if($amoney<$money)
		{
			$totalInvest = $money + $coupon_amount;
			$this->error("尊敬的{$uname}，您准备投标{$totalInvest}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.",__APP__."/member/charge#fragment-1");
		}
		
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		$pin_pass = $vm['pin_pass'];
		$pin = md5($_POST['pin']);
		// if($pin<>$pin_pass) $this->error("支付密码错误，请重试");

		$binfo = M("borrow_info")->field('borrow_uid,borrow_money,borrow_max,has_borrow,has_vouch,borrow_type,borrow_min,money_collect')->find($borrow_id);
		if($this->uid == $binfo['borrow_uid']) {
			$this->error('不能去投自己的标');
			exit;
		};
		
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($m['money_collect']<$binfo['money_collect']) {
				$this->error("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		//投标总数检测
		$capital = M('borrow_investor')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->sum('investor_capital');
		if(($capital+$money+$coupon_amount)>$binfo['borrow_max']&&$binfo['borrow_max']>0){
			$xtee = $binfo['borrow_max'] - $capital;
			$this->error("您已投标{$capital}元，此投上限为{$binfo['borrow_max']}元，你最多只能再投{$xtee}");
		}
		//if($binfo['has_vouch']<$binfo['borrow_money'] && $binfo['borrow_type'] == 2) $this->error("此标担保还未完成，您可以担保此标或者等担保完成再投标");
		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money+$coupon_amount>$caninvest && $need==0){
			$msg = "尊敬的{$uname}，此标已被抢投满了,下次投标手可一定要快呦！";
			$this->error($msg);
		}
		if(($binfo['borrow_min']-$money-$coupon_amount)>0 ){
			$this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
		}
		if(($need-$money-$coupon_amount)<0 ){
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
		}else{
			moneyactlog($this->uid,3,$money,0,"投资人发起对".$borrow_id."号标付款",1);
			//新浪代收接口
			$sina['money'] = $money;
			$sina['uid'] = $this->uid;
			$newbid = borrowidlayout1($borrow_id);
			$sina['content'] = "对第".$newbid."号标投资付款";
			$sina['bid'] = $borrow_id;
			$sina['code'] = "1001";
			$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/Home/invest/sinapayrecall?borrow_id=".$borrow_id."&money=".$money;
			$sina['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Sinanotify/borrownotify";
			$sina['coupons_num'] = $coupon_info;
			$sina['jx_num'] = $jx;
			echo sinacollecttrade($sina);
			
		}
	} 
	
	public function sinapayrecall(){
		$borrow_id = $_REQUEST['borrow_id'];
		$money = $_REQUEST['money'];
		$count = M("investor_detail")->where("investor_uid = {$this->uid}")->count();
		$this->assign("count",$count);
		$this->assign("url","/invest/".$borrow_id.".html");
		$this->display();
		//$this->success("恭喜成功投标{$money}元","/invest/".$borrow_id.".html");
	}

	// //新浪代收接口
	// public function sinacollecttrade($sina){
	// 	import("@.Oauth.sina.Weibopay");
	// 	$payConfig = FS("Webconfig/payconfig");
	// 	$weibopay = new Weibopay();
	// 	$data['service'] 			  = "create_hosting_collect_trade";							//接口名称
	// 	$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
	// 	$data['request_time']		  = date('YmdHis');											//请求时间
	// 	$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
	// 	$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
	// 	$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
	// 	$data['out_trade_no']         = date('YmdHis').mt_rand( 100000,999999); 				//交易订单号
	// 	$data['out_trade_code']		  = '1001';													//交易码 1001代收投资金，1002代收还款金
	// 	$data['summary']			  = '对'.$sina['borrow_id'].'号标进行投标';					//摘要
	// 	$data['payer_id']			  = '20151008'.$sina['uid'];								//用户ID
	// 	$data['payer_identity_type']  = 'UID';													//ID类型
	// 	$data['pay_method']			  = "online_bank^".$sina['money']."^SINAPAY,DEBIT,C";		//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
	// 	$data['extend_param']		  = "channel_black_list^online_bank^binding_pay^quick_pay";
	// 	$data['return_url']			  = "http://".$_SERVER['HTTP_HOST']."/home/invest/sinapayrecall?state=success&borrow_id=".$sina['borrow_id']."&money=".$sina['money'];
	// 	ksort($data);
	// 	$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
	// 	$setdata 					  = $weibopay->createcurl_data($data);
	// 	$result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata);//模拟表单提交
	// 	echo $result;
	// }
	

	public function addcomment(){
	
		$data['comment'] = text($_POST['comment']);
		if(!$this->uid)  ajaxmsg("请先登陆",0);
		if(empty($data['comment']))  ajaxmsg("留言内容不能为空",0);
		$data['type'] = 1;
		$data['add_time'] = time();
		$data['uid'] = $this->uid;
		$data['uname'] = session("u_user_name");
		$data['tid'] = intval($_POST['tid']);
		$data['name'] = M('borrow_info')->getFieldById($data['tid'],'borrow_name');

		$newid = M('comment')->add($data);
		//$this->display("Public:_footer");
		if($newid) ajaxmsg();
		else ajaxmsg("留言失败，请重试",0);
	}
	
	public function jubao(){
		if($_POST['checkedvalue']){
			$data['reason'] = text($_POST['checkedvalue']);
			$data['text'] = text($_POST['thecontent']);
			$data['uid'] = $this->uid;
			$data['uemail'] = text($_POST['uemail']);
			$data['b_uid'] = text($_POST['b_uid']);
			$data['b_uname'] = text($_POST['theuser']);
			$data['add_time'] = time();
			$data['add_ip'] = get_client_ip();
			$newid = M('jubao')->add($data);
			if($newid) exit("1");
			else exit("0");
		}else{
			$id=intval($_GET['id']);
			$u['id'] = $id;
			$u['uname']=M('members')->getFieldById($id,"user_name");
			$u['uemail']=M('members')->getFieldById($this->uid,"user_email");
			$this->assign("u",$u);
			$data['content'] = $this->fetch("Public:jubao");
			exit(json_encode($data));
		}
	}
	
	public function ajax_invest(){
		if(!$this->uid) ajaxmsg("请先登陆", 0);
		$id = intval($_GET['id']);
		if($id < 1) ajaxmsg('借款标号不正确', 0);
		
		$field = "id,borrow_uid,borrow_money,borrow_status,borrow_type,has_borrow,has_vouch,borrow_interest_rate,borrow_duration,repayment_type,collect_time,borrow_min,borrow_max,password,borrow_use,money_collect";
		$vo = M('borrow_info')->field($field)->find($id);
		if(empty($vo)) ajaxmsg('没有此标', 0); // 防止用户修改界面抢投
		if($this->uid == $vo['borrow_uid']) ajaxmsg("不能去投自己的标",0);
		if($vo['borrow_status'] != 2) ajaxmsg("只能投正在借款中的标",0);
		
		$binfo = M("borrow_info")->field('borrow_money,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect')->find($id);
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		
		// $this->assign("has_pin", (empty($vm['pin_pass']))?'no':'yes');
		$this->assign("vo",$vo);
		$this->assign("investMoney", intval($_GET['num']));
		$data['content'] = $this->fetch();
		ajaxmsg($data);
	}
	
	
	public function ajax_vouch(){
		if(!$this->uid) {
			ajaxmsg("请先登陆",0);
		}
		$pre = C('DB_PREFIX');
		$id=intval($_GET['id']);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$field = "id,borrow_uid,borrow_money,has_borrow,borrow_interest_rate,borrow_duration,repayment_type,collect_time,has_vouch,reward_vouch_rate,borrow_min,borrow_max,money_collect";
		$vo = M('borrow_info')->field($field)->find($id);
		
		$vo['need'] = $vo['borrow_money'] - $vo['has_borrow'];
		$vo['lefttime'] =$vo['collect_time'] - time();
		$vo['progress'] = getFloatValue($vo['has_borrow']/$vo['borrow_money']*100,4);//ceil($vo['has_borrow']/$vo['borrow_money']*100);
		$vo['vouch_progress'] = getFloatValue($vo['has_vouch']/$vo['borrow_money']*100,4);//ceil($vo['has_vouch']/$vo['borrow_money']*100);
		$vo['vouch_need'] = $vo['borrow_money'] - $vo['has_vouch'];
		$vo['uname'] = M("members")->getFieldById($vo['borrow_uid'],'user_name');
		$time1 = microtime(true)*1000;
		$vm = getMinfo($this->uid,"m.pin_pass,mm.invest_vouch_cuse,mm.money_collect");
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		////////////////////投标时自动填写可投标金额在投标文本框 2013-07-03 fan////////////////////////
		if($vo['vouch_progress']==100){
			$amoney = $vm['account_money']+$vm['back_money'];
			if($amoney<floatval($vo['borrow_min'])){
				ajaxmsg("您的账户可用余额小于本标的最小投标金额限制，不能投标！",0);
			}elseif($amoney>=floatval($vo['borrow_max']) && floatval($vo['borrow_max'])>0){
				$toubiao = intval($vo['borrow_max']);
			}else if($amoney>=floatval($vo['need'])){
				$toubiao = intval($vo['need']);
			}else{
				$toubiao = floor($amoney);
			}
			$vo['toubiao'] =$toubiao;
		}
		////////////////////投标时自动填写可投标金额在投标文本框 2013-07-03 fan////////////////////////
		
		$pin_pass = $vm['pin_pass'];
		$has_pin = (empty($pin_pass))?"no":"yes";
		$this->assign("has_pin",$has_pin);
		$this->assign("vo",$vo);
		$this->assign("invest_vouch_cuse",$vm['invest_vouch_cuse']);
		$this->assign("Bconfig",$Bconfig);
		$data['content'] = $this->fetch();
		ajaxmsg($data,1);
	}
	
	public function vouchcheck(){
		$pre = C('DB_PREFIX');
		if(!$this->uid) ajaxmsg('',3);
		$pin = md5($_POST['pin']);
		$money = intval($_POST['vouch_money']);
		$vm = getMinfo($this->uid,"m.pin_pass,mm.invest_vouch_cuse");
		$amoney = $vm['invest_vouch_cuse'];
		$uname = session('user_name');
		$pin_pass = $vm['pin_pass'];
		$amoney = floatval($amoney);

		if($pin<>$pin_pass) ajaxmsg("支付密码错误，请重试",0);
		if($money>$amoney){
			$msg = "尊敬的{$uname}，您准备担保{$money}元，但您可用担保投资额度为{$amoney}元，要去申请更高额度吗？";
			ajaxmsg($msg,2);
		}else{
			$msg = "尊敬的{$uname}，您可用担保投资额度为{$amoney}元，您确认担保{$money}元吗？";
			ajaxmsg($msg,1);
		}
	}
		
	public function vouchmoney(){
		if(!$this->uid) exit;
			/****** 防止模拟表单提交 *********/
		$cookieKeyS = cookie(strtolower(MODULE_NAME)."-vouch");
		if($cookieKeyS!=$_REQUEST['cookieKey']){
			$this->error("数据校验有误");
		}
		/****** 防止模拟表单提交 *********/
		$money = intval($_POST['vouch_money']);
		$borrow_id = intval($_POST['borrow_id']);
		$rate = M('borrow_info')->getFieldById($borrow_id,'reward_vouch_rate');
		$amoney = M("member_money")->getFieldByUid($this->uid,'invest_vouch_cuse');
		$uname = session('u_user_name');
		if($amoney<$money) $this->error("尊敬的{$uname}，您准备担保{$money}元，但您可用担保投资额度为{$amoney}元，请先去申请更高额度.");
		
		$saveVouch['borrow_id'] = $borrow_id;
		$saveVouch['uid'] = $this->uid;
		$saveVouch['uname'] = $uname;
		$saveVouch['vouch_money'] = $money;
		$saveVouch['vouch_reward_rate'] = $rate;
		$saveVouch['vouch_reward_money'] = getFloatValue($money*$rate/100,2);
		$saveVouch['add_ip'] = get_client_ip();
		$saveVouch['vouch_time'] = time();
		$newid = M('borrow_vouch')->add($saveVouch);
		
		if($newid) $done = M("member_money")->where("uid={$this->uid}")->setDec('invest_vouch_cuse',$money);
		//$this->assign("waitSecond",1000);
		if($done==true){
			M("borrow_info")->where("id={$borrow_id}")->setInc('has_vouch',$money);
			$this->success("恭喜成功担保{$money}元");
		}
		else $this->error("对不起，担保失败，请重试!");
	}
	
	public function getarea(){
		$rid = intval($_GET['rid']);
		if(empty($rid)){
			$data['NoCity'] = 1;
			exit(json_encode($data));
		}
		$map['reid'] = $rid;
		$alist = M('area')->field('id,name')->order('sort_order DESC')->where($map)->select();

		if(count($alist)===0){
			$str="<option value=''>--该地区下无下级地区--</option>\r\n";
		}else{
			if($rid==1) $str.="<option value='0'>请选择省份</option>\r\n";
			foreach($alist as $v){
				$str.="<option value='{$v['id']}'>{$v['name']}</option>\r\n";
			}
		}
		$data['option'] = $str;
		$res = json_encode($data);
		echo $res;
	}	
	
	public function addfriend(){
		if(!$this->uid) ajaxmsg("请先登陆",0);
		$fuid = intval($_POST['fuid']);
		$type = intval($_POST['type']);
		if(!$fuid||!$type) ajaxmsg("提交的数据有误",0);
		
		$save['uid'] = $this->uid;
		$save['friend_id'] = $fuid;
		$vo = M('member_friend')->where($save)->find();	
		
		if($type==1){//加好友
		if($this->uid == $fuid) ajaxmsg("您不能对自己进行好友相关的操作",0);
			if(is_array($vo)){
				if($vo['apply_status']==3){
					$msg="已经从黑名单移至好友列表";
					$newid = M('member_friend')->where($save)->setField("apply_status",1);
				}elseif($vo['apply_status']==1){
					$msg="已经在你的好友名单里，不用再次添加";
				}elseif($vo['apply_status']==0){
					$msg="已经提交加好友申请，不用再次添加";
				}elseif($vo['apply_status']==2){
					$msg="好友申请提交成功";
					$newid = M('member_friend')->where($save)->setField("apply_status",0);
				}
			}else{
				$save['uid'] = $this->uid;
				$save['friend_id'] = $fuid;
				$save['apply_status'] = 0;
				$save['add_time'] = time();
				$newid = M('member_friend')->add($save);	
				$msg="好友申请成功";
			}
		}elseif($type==2){//加黑名单
		if($this->uid == $fuid) ajaxmsg("您不能对自己进行黑名单相关的操作",0);
			if(is_array($vo)){
				if($vo['apply_status']==3) $msg="已经在黑名单里了，不用再次添加";
				else{
					$msg="成功移至黑名单";
					$newid = M('member_friend')->where($save)->setField("apply_status",3);	
				}
			}else{
				$save['uid'] = $this->uid;
				$save['friend_id'] = $fuid;
				$save['apply_status'] = 3;
				$save['add_time'] = time();
				$newid = M('member_friend')->add($save);	
				$msg="成功加入黑名单";
			}
		}
		if($newid) ajaxmsg($msg);
		else ajaxmsg($msg,0);
	}
	
	
	public function innermsg(){
		if(!$this->uid) ajaxmsg("请先登陆",0);
		$fuid = intval($_GET['uid']);
		if($this->uid == $fuid) ajaxmsg("您不能对自己进行发送站内信的操作",0);
		$this->assign("touid",$fuid);
		$data['content'] = $this->fetch("Public:innermsg");
		ajaxmsg($data);
	}
	public function doinnermsg(){
		$touid = intval($_POST['to']);
		$msg = text($_POST['msg']);	
		$title = text($_POST['title']);	
		$newid = addMsg($this->uid,$touid,$title,$msg);
		if($newid) ajaxmsg();
		else ajaxmsg("发送失败",0);
		
	}
     /**
    * ajax 获取投资记录
    * 
    */
    public function investRecord($borrow_id=0)
    {
        isset($_GET['borrow_id']) && $borrow_id = intval($_GET['borrow_id']);
        $this->getrecode(2,$borrow_id);
    }

	/**
	 *
	 * 获取投资记录
	 * @param int $type 1 返回结果  2 输出结果
	 * @param $borrow_id 标号
	 */
	private function getrecode($type=1,$borrow_id){
		import("ORG.Util.Page");
		$count = M("borrow_investor")->where('borrow_id=' . $borrow_id .' AND debt_id = 0')->count('id');
		$Page = new Page($count, 10);
		$Page->rollPage = 10;

		$show = $Page->ajax_show();
		$this->assign("total_page", $Page->get_total_page());
		$this->assign('page', $show);
		if ($_GET['borrow_id']) {
			$list = M("borrow_investor as b")
				->join(C(DB_PREFIX) . "members as m on  b.investor_uid = m.id")
				->join(C(DB_PREFIX) . "borrow_info as i on  b.borrow_id = i.id")
				->field('i.borrow_interest_rate, i.repayment_type, b.investor_capital, b.add_time, b.is_auto, m.user_name,m.user_phone')
				->where('b.borrow_id=' . $borrow_id .' AND b.debt_id = 0')->order('b.id')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			foreach ($list as $k => $v) {
				$list[$k]["user_phone"] = hidecard($v['user_phone'], 2);
				$list[$k]["is_auto"] = $v['is_auto'] ? '自动' : '手动';
				$list[$k]["add_time"] = date("Y-m-d H:i", $v['add_time']);
				$list[$k]["investor_capital"]=Fmoney($v['investor_capital']);
			}
			$this->assign("list", $list);
		}
		$html = $this->fetch("investRecord");
		if($type==1){
			return $html;
		}else{
			echo $html;
		}

    }
    public  function get_interest(){
        $money=getFloatValue($_POST["money"],2);
        $id=intval($_POST["id"]);
        $where["id"]=$id;
        $binfo=M("borrow_info")->where($where)->field("repayment_type,borrow_interest_rate,borrow_duration,jiaxi_rate")->find();
        $interest=getBorrowInterest($binfo["repayment_type"],$money,$binfo["borrow_duration"],$binfo["borrow_interest_rate"]);
        //标加息的部分
        if($binfo["jiaxi_rate"]>0){
            $jx=getBorrowInterest($binfo["repayment_type"],$money,$binfo["borrow_duration"],$binfo["jiaxi_rate"]);
            $interest += $jx;
        }
        echo getFloatValue($interest,2);
        exit;
    }

    public function use_coupons(){
    	if(!$this->uid) ajaxmsg("请先登陆", 0);
		$id = intval($_GET['borrow_id']);
		if($id < 1) ajaxmsg('借款标号不正确', 0);
		
		$field = "id,borrow_uid,borrow_money,borrow_status,borrow_type,has_borrow,has_vouch,borrow_interest_rate,borrow_duration,repayment_type,collect_time,borrow_min,borrow_max,password,borrow_use,money_collect";
		$vo = M('borrow_info')->field($field)->where(array("id"=>$id))->find();
		if(empty($vo)) ajaxmsg('没有此标', 0); // 防止用户修改界面抢投
		if($this->uid == $vo['borrow_uid']) ajaxmsg("不能去投自己的标",0);
		if($vo['borrow_status'] != 2) ajaxmsg("只能投正在借款中的标",0);
		
		$binfo = M("borrow_info")->field('borrow_money,has_borrow,has_vouch,borrow_max,borrow_min,borrow_type,password,money_collect,repayment_type,borrow_duration')
		                         ->where(array("id"=>$id))
		                         ->find();
		$vm = getMinfo($this->uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
		if($binfo['money_collect']>0){
			if($vm['money_collect']<$binfo['money_collect']) {
				ajaxmsg("此标设置有投标待收金额限制，您账户里必须有足够的待收才能投此标",3);
			}
		}
		////////////////////////////////////待收金额限制 2013-08-26  fan///////////////////
        $money = intval($_GET['money']);
        $days = $binfo['repayment_type'] == 1?$binfo['borrow_duration']:$binfo['borrow_duration']*30;
        log::write('days = '.$days.' borrowinfo = '.json_encode($binfo));
		$coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")
		                         ->where("m.id = {$this->uid} AND c.status = 0 AND c.type = 1 AND c.use_money<={$money} AND c.min_investrange <= {$days}")
		                         ->order("c.use_money desc")
		                         ->group("c.use_money")
		                         ->select();

		$this->assign("coupons",$coupons);
		$i = 0;
		$j = 0;
		foreach ($coupons as $key => $value) {
			if(intval($_GET['money']) < $value["use_money"]){
				$i ++;
			}
			$j++;
		}
		if($i == $j){
			$this->assign("is_use","yes");
		}else{
			$this->assign("is_use","no");
		}
		$this->assign("vo",$vo);
		$this->assign("investMoney", intval($_GET['money']));
      
        //加息劵
        $jiaxi = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")->where("m.id = {$this->uid} AND c.status = 0 AND c.type = 3")->order("c.money desc")->group("c.money")->select();        
        $is_jiaxi = empty($jiaxi)?0:1;
        $this->assign('jiaxi',$jiaxi);
        $this->assign('is_jiaxi',$is_jiaxi);
        //
		$data['content'] = $this->fetch();
		ajaxmsg($data);
    }
}
