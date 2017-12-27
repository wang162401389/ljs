<?php
// 全局设置
class CapitalRepayAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$map=array();
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));
			$query ="start_time=".$_REQUEST['start_time']."&amp;end_time=".$_REQUEST['end_time'];	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
			$query = "start_time=".$_REQUEST['start_time'];	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
			$query = $query."end_time=".$_REQUEST['end_time'];	
		}else{
			if(!empty($_REQUEST['day'])){
			    $day = $_REQUEST['day'];
			}else{
			    $day = 1;
			}
			$query = "day=".$day;
			$start = strtotime("+1 day",strtotime(date("Y-m-d",time())." 00:00:00"));
			$end = strtotime("+{$day} day",strtotime(date("Y-m-d",time())." 23:59:59"));
			$map['b.deadline'] = array(
						"between",
						"{$start},{$end}"
			);
			$search['start_time'] = $start;
			$search['end_time'] = $end;	
		}
		
		$map['b.status'] = 7;
		//$map['i.progress'] = 100;
	
		$list = M("transfer_investor_detail b")->join("{$this->pre}transfer_borrow_info i ON i.id=b.borrow_id")->field('i.borrow_name,b.id,b.borrow_id,sum(b.capital) as bmoney,sum(b.interest) as interest, sum(b.capital+b.interest) as total,b.deadline')->where($map)->group('borrow_id')->select();
		$this->assign("search",$search);
		$this->assign('list',$list);
		$this->assign('query',$query);
		
        $this->display();
    }

	public function export(){
		import("ORG.Io.Excel");

		$map=array();
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("between",$timespan);
			$search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}else{
			if(!empty($_REQUEST['day'])){
			    $day = $_REQUEST['day'];
			}else{
			    $day = 1;
			}
			$start = strtotime("+1 day",strtotime(date("Y-m-d",time())." 00:00:00"));
			$end = strtotime("+{$day} day",strtotime(date("Y-m-d",time())." 23:59:59"));
			$map['b.deadline'] = array(
						"between",
						"{$start},{$end}"
			);
			$search['start_time'] = $start;
			$search['end_time'] = $end;	
		}
		
		$map['b.status'] = 7;
		$map['i.progress'] = 100;
		$pre = $this->pre;
		$list = M("transfer_investor_detail b")->join("{$this->pre}transfer_borrow_info i ON i.id=b.borrow_id")->field('i.borrow_name,b.id,b.borrow_id,sum(b.capital) as bmoney,sum(b.interest) as interest, sum(b.capital+b.interest) as total,b.deadline')->where($map)->group('borrow_id')->select();
		$row=array();
		$row[0]=array('序号','标号ID','明日待还本金','明日待还利息','明日总待还金额','明日待还时间');
		$i=1;
		foreach($list as $v){
				$row[$i]['i'] = $i;
				$row[$i]['borrow_id'] = $v['borrow_id'];
				$row[$i]['borrow_name'] = $v['borrow_name'];
				$row[$i]['bmoney'] = $v['bmoney'];
				$row[$i]['interest'] = $v['interest'];
				$row[$i]['total'] = $v['total'];
				$row[$i]['deadline'] = date("Y-m-d H:i:s",$v['deadline']);
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("datalistcard");
	}

	
}
?>