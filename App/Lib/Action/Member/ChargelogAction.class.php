<?php
/**
 * 充值记录
 * Class WithdlogAction
 */
class ChargelogAction extends MCommonAction {
	public function index(){
		$this->display();
    }

    //充值记录
    public function chargelog(){
		$pagesize = 20;
		$page = 1;
		if($_GET['page']>1){
			$page = $_GET['page'];
		}
		$start = ($page-1)*$pagesize;
		$where["uid"]=$this->uid;
		$where["type"]=1;
		$mywhere=array();
		if($_GET['start_time']){
			$mywhere[]=array("egt",strtotime($_GET['start_time']."000000"));
		}
		if($_GET['end_time']){
			$mywhere[]=array("elt",strtotime($_GET['end_time']."235959"));
		}
		if(count($mywhere)){
			$where['addtime']=$mywhere;
		}
		$limit=$start.",".$pagesize;
		$withdrawlist = M("sinalog")->where($where)->order("addtime desc")->limit($limit)->select();
		$count = M("sinalog")->where($where)->count();
		$totalpage = ceil($count/$pagesize);
		$i = $start;
		$list = null;
		foreach ($withdrawlist as $l) {
			$list[$i][1] = $l["money"];
			$list[$i][5] = $i+1;
			$list[$i][3] = date("Y-m-d H:i:s",$l["addtime"]);
			if($l["status"] == 2){
				$list[$i][2] = "充值成功";
			}elseif($l["status"] == 1){
				$list[$i][2] = "处理中";
			}elseif($l["status"] == 3){
				$list[$i][2] = "充值失败";
			}
			$i++;
		}
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->assign("total_item",$totalpage);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
}
?>