<?php

/**
 * 我的赠券
 * Class CouponsAction
 */
class CouponsAction extends MCommonAction{
	public function index(){
		$this->display();
	}

	/**
	 * 投资券 type 1 投资券  2 体验金
	 */
	public function mycoupons(){
	    $type=$_GET["type"];//0 未使用 1使用 2过期
		$coupons = M("coupons c")->join("lzh_members m on m.user_phone = c.user_phone")->where("m.id = {$this->uid} and status={$type}" )->select();
		$list=[];
		foreach ($coupons as $va){
			if($va["type"]==1){
				$va["type_name"]="投资券";
			}elseif($va["type"]==2){
				$va["type_name"]="体验券";
			}else{
				$va["type_name"]="加息券";
			}
		    $va["endtime"]=date("Y-m-d",$va["endtime"]);
		    $list[]=$va;
		}
		if(count($list)==0){
		    $list=null;
		}
		$this->assign("coupons",$list);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
}