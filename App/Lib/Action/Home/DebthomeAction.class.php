<?php
    /**
    * 普通标债权转让控制器类
    */
class DebthomeAction extends HCommonAction{

	/**
	 * 债权转让列表页面
	 */
	public function debtlists(){
		$searchMap['borrow_status'] = array("0"=>"不限","2"=>"进行中","4"=>"复审中","6"=>"还款中","7"=>"已完成");
		$searchMap['borrow_duration'] = array("0"=>"不限","1"=>"30天内","2"=>"30-90天","3"=>"90-180天","4"=>"180-360天");
		$searchMap['product_type']=array("0"=>"不限","1"=>"信金链","2"=>"融金链","3"=>"优金链","4"=>"质金链","5"=>'保金链');
		$searchMap['borrow_interest_rate']=array("0"=>"不限","1"=>"8%-10%","2"=>"10%-12%","3"=>"12%-15%");

        $map=array();
		$current_borrow_status=isset($_GET["borrow_status"])?text($_GET["borrow_status"],false ,true):"0";
		if($current_borrow_status!="0"){
			$map["d.borrow_status"]=$current_borrow_status;
		}else{
			$map["d.borrow_status"]=array("in",array("2","4","6","7"));
		}

		$current_borrow_duration=isset($_GET["borrow_duration"])?text($_GET["borrow_duration"],false,true):"0";
		if($current_borrow_duration!="0"){
             if($current_borrow_duration=="1"){
				 $map["d.borrow_duration"] = array("between",array(0,30));
			 }if($current_borrow_duration=="2"){
				$map["d.borrow_duration"] = array("between",array(30,90));
			}if($current_borrow_duration=="3"){
				$map["d.borrow_duration"] = array("between",array(90,180));
			}if($current_borrow_duration=="4"){
				$map["d.borrow_duration"] = array("between",array(180,360));
			}
		}
		$current_product_type=isset($_GET["product_type"])?text($_GET["product_type"],false,true):"0";
		if($current_product_type!="0"){
			if($current_product_type=="1"){
				$map["b.product_type"]=array("in",array(5,6));
			}if($current_product_type=="2"){
				$map["b.product_type"]=4;
			}if($current_product_type=="3"){
				$map["b.product_type"]=7;
			}if($current_product_type=="4"){
				$map["b.product_type"]=array("in",array(1,2,3));
			}if($current_product_type=="5"){
				$map["b.product_type"]=8;
			}
		}
		$current_borrow_interest_rate=isset($_GET["borrow_interest_rate"])?text($_GET["borrow_interest_rate"],false ,true):"0";
		if($current_borrow_interest_rate!="0"){
			if($current_borrow_interest_rate=="1"){
				$map["b.borrow_interest_rate"]=array("between",array(8,10));
			}if($current_borrow_interest_rate=="2"){
				$map["b.borrow_interest_rate"]=array("between",array(10,12));
			}if($current_borrow_interest_rate=="3"){
				$map["b.borrow_interest_rate"]=array("between",array(12,15));
			}
		}

		$borrow_status="";
		$borrow_duration="";
		$product_type="";
		$borrow_interest_rate="";
		$current_url=array();
		if($current_borrow_status && $current_borrow_status!="0"){
			$current_url["borrow_status"]=$current_borrow_status;
		}
		if($current_borrow_duration&& $current_borrow_duration!="0"){
			$current_url["borrow_duration"]=$current_borrow_duration;
		}
		if($current_product_type&& $current_product_type!="0"){
			$current_url["product_type"]=$current_product_type;
		}
		if($current_borrow_interest_rate&& $current_borrow_interest_rate!="0"){
			$current_url["borrow_interest_rate"]=$current_borrow_interest_rate;
		}

		foreach ($searchMap['borrow_status'] as $key=>$v){
			$my_url=$current_url;
			$my_url["borrow_status"]=$key;
			$newurl = http_build_query($my_url);
			$url="__URL__/debtlists?type=search&$newurl";
			if($key==$current_borrow_status){
				$borrow_status.="<li class=\"buxz\"><a href=\"$url\">{$v}</a></li>";
			}else{
				$borrow_status.="<li><a href=\"$url\">{$v}</a></li>";
			}

		}
		foreach ($searchMap['borrow_duration'] as $key=>$v){
			$my_url=$current_url;
			$my_url["borrow_duration"]=$key;
			$newurl = http_build_query($my_url);
			$url="__URL__/debtlists?type=search&$newurl";
			if($key==$current_borrow_duration){
				$borrow_duration.="<li class=\"buxz\"><a href=\"$url\">{$v}</a></li>";
			}else{
				$borrow_duration.="<li><a href=\"$url\">{$v}</a></li>";
			}

		}
		foreach ($searchMap['product_type'] as $key=>$v){
			$my_url=$current_url;
			$my_url["product_type"]=$key;
			$newurl = http_build_query($my_url);
			$url="__URL__/debtlists?type=search&$newurl";
			if($key==$current_product_type){
				$product_type.="<li class=\"buxz\"><a href=\"$url\">{$v}</a></li>";
			}else{
				$product_type.="<li><a href=\"$url\">{$v}</a></li>";
			}

		}
		foreach ($searchMap['borrow_interest_rate'] as $key=>$v){
			$my_url=$current_url;
			$my_url["borrow_interest_rate"]=$key;
			$newurl = http_build_query($my_url);
			$url="__URL__/debtlists?type=search&$newurl";
			if($key==$current_borrow_interest_rate){
				$borrow_interest_rate.="<li class=\"buxz\"><a href=\"$url\">{$v}</a></li>";
			}else{
				$borrow_interest_rate.="<li><a href=\"$url\">{$v}</a></li>";
			}

		}
		//$map["d.collect_time"]=array("egt",time());

		$field="d.id,d.borrow_name,d.borrow_money,b.borrow_interest_rate,d.borrow_duration_txt,d.borrow_status,d.has_borrow";
		//分页处理
		import("ORG.Util.Page");
		$count = M('debt_borrow_info d')->join("lzh_borrow_info b on b.id = d.old_borrow_id")->where($map)->count('d.id');
		$p = new Page($count, 10);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		$zhaiquanlist= M('debt_borrow_info d')->join("lzh_borrow_info b on b.id = d.old_borrow_id")->field($field)->where($map)->order("d.borrow_status ASC,d.id DESC")->limit($Lsql)->select();
		foreach ($zhaiquanlist as $k=>$v){
			$zhaiquanlist[$k]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
			$zhaiquanlist[$k]["borrow_money"]=number_format($v["borrow_money"], 2, '.', '');
		}
		$this->assign("zhaiquanlist",$zhaiquanlist);
		$this->assign("zhaiquanlistcount",count($zhaiquanlist));
		$this->assign("page",$page);

		$this->assign("borrow_status",  $borrow_status);
		$this->assign("borrow_duration",$borrow_duration);
		$this->assign("product_type",           $product_type);
		$this->assign("borrow_interest_rate",   $borrow_interest_rate);
		$this->display();
	}

	/**
	 * 债权转让详细页面
	 */
	public function debtdetail(){
		$id = intval($_GET['id']);
		if(!$id){
			$this->error("参数缺失");
		}
		session("lastpcid",$id);
		$field = "d.borrow_name,d.id,d.old_borrow_id,d.debt_rate,d.borrow_status,d.collect_time,d.borrow_times,d.borrow_min,d.borrow_duration_txt,d.add_time,d.borrow_money,d.totalmoney,d.has_borrow,b.borrow_interest_rate,b.repayment_type,b.product_type,b.borrow_use,b.borrow_use_desc";
        $zhaiquanlist=M("debt_borrow_info d")
   		    ->join("lzh_borrow_info b on b.id = d.old_borrow_id")
   		    ->where(array("d.id"=>$id))
   		    ->field($field)
   		    ->find();
   		if($zhaiquanlist){
   			$zhaiquanlist["progress"]=getFloatValue($zhaiquanlist['has_borrow']/$zhaiquanlist['borrow_money']*100,2);
   			$zhaiquanlist['need'] = $zhaiquanlist['borrow_money'] - $zhaiquanlist['has_borrow'];//可投金额
   			$zhaiquanlist['lefttime'] =$zhaiquanlist['collect_time'] - time();//剩余时间$sinasaving = querysaving($this->uid);
			$sinasaving = querysaving($this->uid);
			$sinabalance = querybalance($this->uid);
			$zhaiquanlist['account_money']=$sinasaving+$sinabalance;
   		}else{
   			$this->error("数据有误");
   		}

		//投资记录
		$this->assign('zhaiquanlist',$zhaiquanlist);
		$html=$this->getrecode(1,$id);
		$this->assign("html",$html);

		/**
		 * 是否登录 0未登录  1 已经登录
		 */
		$islogin=0;
		if($this->uid){
			$islogin=1;
		}
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";//确定还款方式和
		$this->assign("bconfig",$Bconfig);
		$this->assign("gloconf",$this->gloconf);
		$this->assign("islogin",$islogin);
		$this->display();
	}

	/**
	 *
	 * 获取投资记录
	 * @param int $type 1 返回结果  2 输出结果
	 * @param $borrow_id 标号
	 */
	private function getrecode($type=1,$borrow_id){
		import("ORG.Util.Page");
		$count = M("debt_borrow_investor")->where('borrow_id=' . $borrow_id)->count('id');
		$Page = new Page($count, 10);
		$Page->rollPage = 10;

		$show = $Page->ajax_show();
		$this->assign("total_page", $Page->get_total_page());
		$this->assign('page', $show);
			$list = M("borrow_investor as b")
				->join("lzh_members as m on  b.investor_uid = m.id")
				->field('b.investor_capital, b.add_time, b.is_auto,m.user_phone')
				->where('b.debt_id=' . $borrow_id)->order('b.id')->limit($Page->firstRow . ',' . $Page->listRows)->select();

			foreach ($list as $k => $v) {
				$list[$k]["user_phone"] = hidecard($v['user_phone'], 2);
				$list[$k]["is_auto"] = $v['is_auto'] ? '自动' : '手动';
				$list[$k]["add_time"] = date("Y-m-d H:i", $v['add_time']);
				$list[$k]["investor_capital"]=Fmoney($v['investor_capital']);
			}
		$this->assign("list", $list);
		$html = $this->fetch("recode");
		if($type==1){
			return $html;
		}else{
			echo $html;
		}

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
	 * 获取投资金额的当前收益
	 */
	public  function get_interest(){
		$money=getFloatValue($_POST["money"], 2);
		$id=intval($_POST["id"]);//标号
		$where["id"]=$id;
		$binfo=M("debt_borrow_info")->where($where)->field("has_borrow,borrow_money,borrow_duration,debt_interest")->find();
		$interest = getFloatValue(($money/$binfo["borrow_money"])*$binfo["debt_interest"],2);
		$leftmoney=$binfo["borrow_money"]-$binfo["has_borrow"]-$money;//此处是判断剩下的金额是否大于100
		$jingdu=0.00001;//精度
		if($leftmoney<100 && $leftmoney>$jingdu){
			echo json_encode(array("status"=>1,"msg"=>"剩余金额不能小于100元"));
		}else{
			echo json_encode(array("status"=>0,"msg"=>"获取成功","data"=>array("shouyi"=>$interest,"leftmoney"=>$leftmoney)));
		}
		exit;
	}

	/**
	 * 投标之后检测并发送数据给新浪
	 */
	public function investmoney(){
		if(!$this->uid) {
			$this->error('请先登录',3);
			exit;
		}

		$money = $_GET['money'];//投资金额
		$money=getFloatValue($money,2);
		$borrow_id = intval($_GET['borrow_id']);//标号
		$sinasaving = querysaving($this->uid);
		$sinabalance = querybalance($this->uid);
		$amoney = $sinabalance+$sinasaving;
		$uname = session('u_user_name');
		if($amoney<$money){//如果金额不足则跳转到充值页面
			$this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.",__APP__."/member/charge#fragment-1");
		}
		
		$binfo = M("debt_borrow_info d")
				->join("lzh_borrow_info b ON b.id = d.old_borrow_id")
			    ->field('d.borrow_uid as debt_uid,b.borrow_uid as old_borrow_uid,d.borrow_money,d.has_borrow,b.borrow_min')->where(array("d.id"=>$borrow_id))->find();
		if($this->uid == $binfo['debt_uid'] || $this->uid == $binfo['old_borrow_uid']) {
			$this->error('不能去投自己的标');
			exit;
		};

		$need = $binfo['borrow_money'] - $binfo['has_borrow'];
		$caninvest = $need - $binfo['borrow_min'];
		if( $money>$caninvest && $need==0){
			$msg = "尊敬的{$uname}，此标已被抢投满了,下次投标手可一定要快呦！";
			$this->error($msg);
		}
		if(($binfo['borrow_min']-$money)>0 ){
			$this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
		}
        $need=number_format($need, 2,'.', '');
		$money=number_format($money, 2,'.', '');
		$d_binfo=M("debt_borrow_info")->where(array("id"=>$borrow_id))->field("has_borrow,borrow_money,borrow_duration")->find();
		$leftmoney=$d_binfo["borrow_money"]-$d_binfo["has_borrow"]-$money;
		$jingdu=0.00001;//精度
		if($leftmoney<100 && $leftmoney>$jingdu){
			$this->error("尊敬的{$uname}，此标剩余金额不足100元");
		}
		if(($need-$money)<0 ){
			$this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
		}else{
			//新浪代收接口
			$sina['money'] = $money;
			$sina['uid'] = $this->uid;
			$sina['content'] = "对第ZQ".$borrow_id."号标投资付款";
			$sina['bid'] = $borrow_id;
			$sina['code'] = "1001";
			$sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/Home/debthome/sinapayrecall?borrow_id=".$borrow_id."&money=".$money;
			$sina['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquanborrownotify";
			$sina['coupons_num'] =null;
			echo sinacollecttrade($sina,2);

		}
	}

	public function sinapayrecall(){
		$borrow_id = $_REQUEST['borrow_id'];
		$count = M("investor_detail")->where("investor_uid = {$this->uid} and status！=-1 ")->count();
		$this->assign("count",$count);
		$this->assign("url","/debthome/debtdetail?id=".$borrow_id);
		$this->display();
	}

	
}
