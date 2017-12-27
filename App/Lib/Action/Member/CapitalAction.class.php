<?php
/*资金统计
*/
class CapitalAction extends MCommonAction {

    public function index(){
		$this->display();
    }

    public function summary(){
		$vlist = getMemberMoneySummary($this->uid);
		$this->assign("vo",$vlist);
		

        $this->assign('pcount', get_personal_count($this->uid)); 

		$minfo =getMinfo($this->uid,true);
		//直接调取新浪余额
		$minfo['account_money']=number_format( querysaving($this->uid), 2, ".", "" );
		
        $this->assign("minfo",$minfo); 
        $this->assign('benefit', get_personal_benefit($this->uid));   //收入
        $this->assign('out', get_personal_out($this->uid));      //支出
		////////////////////////////////////////////////////////////////////
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	/**
	 * 资金最近7天记录
	 */
    public function detail(){
	    	
    	$page = empty($_GET['page'])?1:$_GET['page'];
   //  	$start_time = null;
   //  	$end_time= null;
   //  	if(!empty($_GET['start_time'])){
			// $start_time = $_GET['start_time'];
   //  	}
   //  	if(!empty($_GET['end_time'])){
   //  		$end_time= $_GET['end_time'];
   //  	}
    	$result = queryusedetail($this->uid,null,null,$page);
    	if($result){
	    	$detlist = $result['detail_list'];
	    	$totalitem = $result['total_item'];
	    	$page_size = $result['page_size'];
	    	$totalpage = ceil($totalitem/$page_size);
	    	if($totalitem > 0){
		    	$unlist = explode("|", $detlist);
		    	$list=null;
		    	$i=0;
		    	foreach ($unlist as $l) {
		    		$pr_list = explode("^", $l);
		    		$list[$i]['dec'] = $pr_list[0];
		    		$list[$i]['money'] = $pr_list[2].$pr_list[3];
		    		$list[$i]['addtime'] = date("Y-m-d H:i:s",strtotime($pr_list[1]));
		    		$list[$i]['det_yue'] = $pr_list[4];
		    		$i++;
		    	}
	    	}else{
	    		$list = null;
	    	}
	    }else{
	    	$totalitem = 0;
	    }
    	$this->assign("list",$list);		
    	$this->assign("totalitem",$totalitem);		
		$this->assign("totalpage",$totalpage);	
		$this->assign("page",$page);	
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function detaillog(){
    	$p = empty($_GET['p'])?0:$_GET['p'];
    	$uuid = "20151008".$this->uid;
		$where = "(pay_uid='{$uuid}' OR payee_uid='{$uuid}')";
		if(!empty($_GET['start_time']) && !empty($_GET['end_time'])){
			$start_time = trim($_GET['start_time'])." 00:00:00";
			$end_time = trim($_GET['end_time'])." 23:59:59";
			$search['start_time']=trim($_GET['start_time']);
			$search['end_time']=trim($_GET['end_time']);
			$this->assign("search",$search);
			$where .= " AND (create_time BETWEEN '{$start_time}' AND '{$end_time}')";
    	}
    	$list = M("member_detaillog")->where($where)->order("create_time desc")->page("{$p},20")->select();
		import("ORG.Util.Page");
		$count = M('member_detaillog')->where($where)->count();
		$totalpage = ceil($count/20);
		$p = new Page($count, 20);
		$page = $p->show();
    	$this->assign("list",$list);
    	$this->assign("pagebar",$page);
    	$this->assign("totalpage",$totalpage);
    	$data['html'] = $this->fetch();
		exit(json_encode($data));
    }
	
	public function export(){
		import("ORG.Io.Excel");

		$map=array();
		$map['uid'] = $this->uid;
		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			
			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}
		if(!empty($_GET['log_type'])){
				$map['type'] = intval($_GET['log_type']);
				$search['log_type'] = intval($_GET['log_type']);
		}

		$list = getMoneyLog($map,100000);
		
		$logtype = C('MONEY_LOG');
		$row=array();
		$row[0]=array('序号','发生日期','类型','影响金额','可用余额','冻结金额','待收金额','说明');
		$i=1;
		foreach($list['list'] as $v){
				$row[$i]['i'] = $i;
				$row[$i]['uid'] = date("Y-m-d H:i:s",$v['add_time']);
				$row[$i]['card_num'] = $v['type'];
				$row[$i]['card_pass'] = $v['affect_money'];
				$row[$i]['card_mianfei'] = ($v['account_money']+$v['back_money']);
				$row[$i]['card_mianfei0'] = $v['freeze_money'];
				$row[$i]['card_mianfei1'] = $v['collect_money'];
				$row[$i]['card_mianfei2'] = $v['info'];
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'moneyLog');
		$xls->addArray($row);
		$xls->generateXML("moneyLog");
	}

    // 存钱罐收益
    public function pigbanklog(){
        $map['uid']=$this->uid;
        $regtime = M('members')->field('reg_time')->where('id='.$this->uid)->find();
        $start = C('EARNINGS.starting');
        $list = M('member_piggybank')->field('earnings_yesterday,time,total_revenue')->where($map)->order('time DESC')->select();
        foreach($list as $k => $v){
            if($v['time']>strtotime(date('Y-m-d',time())) && $v['time']<strtotime(date('Y-m-d',strtotime('+1 day')))){
                $zrshouyi = $v['earnings_yesterday'];//
            }
            /**
            if($v['time']>strtotime($start)){
                $zshouyi += $v['earnings_yesterday'];
            }
             * **/
            $list[$k]['time']=date("Y-m-d",$v['time']-24*3600);
        }
        $cqglist = piggybankearnings();
        $cqglist1 = explode('|', $cqglist['yield_list']);
        foreach($cqglist1 as $k => $v){
            $cqglist2[$k] = explode('^',$v);
        }
        $this->assign('thousandsincome',$cqglist2[0][2]);
        $this->assign('yields',$cqglist2[0][1]);    
        $this->assign('list',$list);
        $this->assign('start',$start);
        $this->assign('regtime',$regtime['reg_time']);
        $this->assign('zrshouyi',$zrshouyi);
        $this->assign('zonshouyi',$list[0]["total_revenue"]?$list[0]["total_revenue"]:0);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    // 充值记录
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

    //提现记录
    public function withdrawlog(){
        $pagesize = 20;
        $page = 1;
        if($_GET['page']>1){
            $page = $_GET['page'];
        }
        $start = ($page-1)*$pagesize;
        $where["uid"]=$this->uid;
        $where["type"]=array("in","2,14");
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
                $list[$i][2] = "提现成功";
            }elseif($l["status"] == 3){
                $list[$i][2] = "提现失败";
            }elseif($l["status"] == 4){
                $list[$i][2] = "处理中";
            }elseif($l["status"] == 1){
                $list[$i][2] = "未提现";
            }
            $i++;
        }
        $this->assign("list",$list);
        $this->assign("page",$page);
        $this->assign("total_item",$totalpage);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    // 投资记录
    public function investlog(){
        $pagesize = 20;
        $page = 1;
        if($_GET['page']>1){
            $page = $_GET['page'];
        }
        $start = ($page-1)*$pagesize;
        $mywhere=array();
        if($_GET['start_time']){
            $mywhere[]=array("egt",strtotime($_GET['start_time']."000000"));
        }
        if($_GET['end_time']){
            $mywhere[]=array("elt",strtotime($_GET['end_time']."235959"));
        }
        if(count($mywhere)){
            $where['add_time']=$mywhere;
        }
        $limit=$start.",".$pagesize;
        $list = M("borrow_investor")->where('investor_uid='.$this->uid)->order('add_time desc')->limit($limit)->select();
        $count = M("borrow_investor")->where('investor_uid='.$this->uid)->count();
        $totalpage = ceil($count/$pagesize);
        $i = $start;
        $limit=$start.",".$pagesize;
        $this->assign("list",$list);
        $this->assign("page",$page);
        $this->assign("total_item",$totalpage);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

}