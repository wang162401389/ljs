<?php 
class GetinfoAction extends JCommonAction{
	public function getborrowsum(){
		$user_phone = $_REQUEST["user_phone"];
		$map["m.user_phone"]=$user_phone;
		$map["b.borrow_status"] = array('in','4,6');
		$borrowsum = M("borrow_info b")->join("lzh_members m ON m.id = b.borrow_uid")->where($map)->sum('b.borrow_money');
		if($borrowsum == null){
			$data = array("code"=>-1,"message"=>"无数据");
			echo json_encode($data);
		}else{
			$data = array("code"=>0,"message"=>"提交成功","total"=>$borrowsum);
			echo json_encode($data);
		}
	}

	public function getborrowsumlist(){
		$user_phone = $_REQUEST["user_phone"];
		$map["m.user_phone"]=array('in', $user_phone );
		$map["b.borrow_status"] = array('in','4,6');
		$borrowsum = M("borrow_info b")->join("lzh_members m ON m.id = b.borrow_uid")->where($map)->group("m.user_phone")->field("m.user_phone user_phone,sum(b.borrow_money) total")->select();
		if($borrowsum == null){
			$data = array("code"=>-1,"message"=>"无数据");
			echo json_encode($data);
		}else{
			$data = array("code"=>0,"message"=>"提交成功","borrow_list"=>$borrowsum);
			echo json_encode($data);
		}
	}
}
?>