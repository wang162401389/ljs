<?php 
class ConfirmModel extends Model{
	protected $tableName = 'borrow_confirm'; 

	//复审添加列表
	public function addConfirmList($data){
		$rs = $this->add($data);
		if($rs>0){
			return ture;
		}else{
			return false;
		}
	}

	//获取未支付的综合服务费总和
	public function getFeeSum($uid){
		$sum = $this->where("uid={$uid} AND fee_status = 0")->sum('fee');
		return $sum;
	}

	//获取未支付的咨询服务费总和
	public function getDanbaoSum($uid){
		$sum = $this->where("uid={$uid} AND danbao_status = 0")->sum('danbao');
		return $sum;
	}

	//获取未支付的综合服务费列表
	public function getFeeList($uid){
		$list = $this->join("lzh_borrow_info bi ON bi.id = lzh_borrow_confirm.bid")->where("lzh_borrow_confirm.uid={$uid} AND lzh_borrow_confirm.fee_status = 0")->field("bi.id,bi.borrow_name,bi.borrow_duration_txt,borrow_money,lzh_borrow_confirm.fee")->select();
		return $list;
	}

	//获取未支付的咨询服务费列表
	public function getDanbaoList($uid){
		$list = $this->join("lzh_borrow_info bi ON bi.id = lzh_borrow_confirm.bid")->where("lzh_borrow_confirm.uid={$uid} AND lzh_borrow_confirm.danbao_status = 0 AND lzh_borrow_confirm.danbao_id > 0")->field("bi.id,bi.borrow_name,bi.borrow_duration_txt,borrow_money,lzh_borrow_confirm.danbao,lzh_borrow_confirm.danbao_id")->select();
		return $list;
	}

	//获取未支付的综合服务费数量
	public function getFeeCount($uid){
		$count = $this->where("uid={$uid} AND fee_status = 0")->count();
		return $count;	
	}

	//获取未支付的咨询服务费数量
	public function getDanbaoCount($uid){
		$count = $this->where("uid={$uid} AND danbao_status = 0 AND danbao_id > 0")->count();
		return $count;
	}

	//PC获取未支付的综合服务费和咨询服务费列表
	public function getAllList($uid){
		$list = $this->join("lzh_borrow_info bi ON bi.id = lzh_borrow_confirm.bid")
					->join("lzh_borrow_info_additional bia ON bia.bid = bi.id")
					->where("lzh_borrow_confirm.uid={$uid} AND (lzh_borrow_confirm.fee_status = 0 OR (lzh_borrow_confirm.danbao_status=0 AND lzh_borrow_confirm.danbao_id > 0))")->field("bi.id,bi.borrow_name,bi.borrow_duration_txt,borrow_money,lzh_borrow_confirm.fee,lzh_borrow_confirm.danbao,lzh_borrow_confirm.fee_status,lzh_borrow_confirm.danbao_status,bia.is_tocard")->select();
		return $list;
	}

	//PC获取未支付的综合服务费和咨询服务费数量
	public function getAllCount($uid){
		$count = $this->where("uid={$uid} AND (fee_status = 0 OR (danbao_status=0 AND danbao_id > 0))")->count();
		return $count;
	}


	//支付完成更新综合服务费支付状态
	public function UpdateFee($bid){
		$data["fee_status"] = 1;
		$rs = $this->where("bid={$bid}")->save($data);
		if($rs>0){
			return ture;
		}else{
			return false;
		}
	}

	//支付完成更新咨询服务费支付状态
	public function UpdateDanbao($bid){
		$data["danbao_status"] = 1;
		$rs = $this->where("bid={$bid}")->save($data);
		if($rs>0){
			return ture;
		}else{
			return false;
		}
	}
	
}
?>
