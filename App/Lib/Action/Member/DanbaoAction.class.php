<?php

/**
 * 担保标
 * Class DanbaoAction
 */
class DanbaoAction extends MCommonAction {

	public function index(){
		$this->display();
	}

	 private  function super_getBorrowList($map,$size,$limit=10){
       //if(empty($map['borrow_uid'])) return;
		
		if($size){
			//分页处理
			import("ORG.Util.Page");
			$count = M('borrow_info')->where($map)->count('id');
			$p = new Page($count, $size);
			$page = $p->show();
			$Lsql = "{$p->firstRow},{$p->listRows}";
			//分页处理
		}else{
			$page="";
			$Lsql="{$parm['limit']}";
		}
		
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$status_arr =$Bconfig['BORROW_STATUS_SHOW'];
		$type_arr =$Bconfig['REPAYMENT_TYPE'];
		$list = M('borrow_info')->where($map)->order('id DESC')->limit($Lsql)->select();
		/////////////使用了视图查询操作 fans 2013-05-22/////////////////////////////////
		// $Model = D("BorrowView");
		// $list=$Model->field(true)->where($map)->order('times ASC')->group('id')->limit($Lsql)->select();

		/////////////使用了视图查询操作 fans 2013-05-22/////////////////////////////////
		foreach($list as $key=>$v){
			$list[$key]['status'] = $status_arr[$v['borrow_status']];
			$list[$key]['repayment_type_num'] = $v['repayment_type'];
			$list[$key]['repayment_type'] = $type_arr[$v['repayment_type']];
			$list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
			if($map['borrow_status']==6){
				$vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['id']} and status=7")->order("deadline ASC")->find();
	            if($v['repayment_type']==1){
	                $list[$key]['repayment_time']=cal_deadline($v['id']);
	            }else
				$list[$key]['repayment_time'] = $vx['deadline'];
			}
			if($map['borrow_status']==5 || $map['borrow_status']==1){
				$vd = M('borrow_verify')->field(true)->where("borrow_id={$v['id']}")->find();
				$list[$key]['dealinfo'] = $vd;
			}
		}
		
		$row=array();
		$row['list'] = $list;
		$row['page'] = $page;
		//$map['status'] = 1;
		//$row['success_money'] = M('member_payonline')->where($map)->sum('money');
		//$map['status'] = array('neq','1');
		//$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
		return $row;
    }

   
   
    public function summary(){
		$pre = C('DB_PREFIX');
		
		$this->assign("mx",getMemberBorrowScan($this->uid));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	/**
	 * 发标中的借款
	 */
	public function danbaoing(){
       $map["danbao"] = $this->uid;
		$map['borrow_status'] = array("in","0,2");
		
		if($_GET['start_time2']&&$_GET['end_time2']){
			$_GET['start_time2'] = strtotime($_GET['start_time2']." 00:00:00");
			$_GET['end_time2'] = strtotime($_GET['end_time2']." 23:59:59");
			
			if($_GET['start_time2']<$_GET['end_time2']){
				$map['add_time']=array("between","{$_GET['start_time2']},{$_GET['end_time2']}");
				$search['start_time2'] = $_GET['start_time2'];
				$search['end_time2'] = $_GET['end_time2'];
			}
		}

        $list = $this->super_getBorrowList($map,10);
        
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
	
	
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}



	/**
	 * 担保中的借款
	 */
	public function danbaopaying(){
       $map["danbao"] = $this->uid;
		$map['borrow_status'] = 6;
		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			
			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}
            if($_GET['bid']){
                $map['id']=intval($_GET['bid']);
            }
//            $map['status'] = 7;

            $list = $this->super_getBorrowList($map,10);
            
        
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
	
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	/**
	 * 已完成借款
	 */
	public function danbaodone(){
       $map["danbao"] = $this->uid;
		$map['borrow_status'] = array(7,9,'OR');
		
		if($_GET['start_time8']&&$_GET['end_time8']){
			$_GET['start_time8'] = strtotime($_GET['start_time8']." 00:00:00");
			$_GET['end_time8'] = strtotime($_GET['end_time8']." 23:59:59");
			
			if($_GET['start_time8']<$_GET['end_time8']){
				$map['add_time']=array("between","{$_GET['start_time8']},{$_GET['end_time8']}");
				$search['start_time8'] = $_GET['start_time8'];
				$search['end_time8'] = $_GET['end_time8'];
			}
		}

        $list = $this->super_getBorrowList($map,10);
        $i=0;
        foreach ($list["list"] as $l) {
        	$is_buid = M("sinalog")->where("type=4 AND borrow_id = {$l["id"]}")->find();
        	if($is_buid["uid"] == $this->uid){
        		$list["list"][$i]["is_buid"]=1;
        	}else{
        		$list["list"][$i]["is_buid"]=0;
        	}
        	$i++;
        }
        	file_put_contents('aaalog.txt', var_export($list["list"],true), FILE_APPEND);
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
	
		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}
	
	public function doexpired(){
		$borrow_id = intval($_POST['bid']);
		$sort_order = intval($_POST['sort_order']);
		$newid = borrowRepayment($borrow_id,$sort_order);
		if($newid===true) ajaxmsg();
		elseif($newid===false) ajaxmsg('还款失败，请重试',0);
		else ajaxmsg($newid,0);
	}

}