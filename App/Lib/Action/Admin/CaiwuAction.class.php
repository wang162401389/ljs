<?php
// 全局设置
class CaiwuAction extends ACommonAction
{
	/**满标记录**/
    private function  find_name($name){
        $map['user_name']=array("like","%".$name."%");
        $uid=M("members")->where($map)->field('id')->select();
        $list=array();
        foreach($uid as $key=>$val){
            $list[]=$val['id'];
        }
        $mine="综合服务费";
        if(strstr($mine,$name)){
            $list[]=0;
        }
        return array("in",$list);
    }
	public function manbiao(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['s.status'] = intval($_REQUEST['status']);
			$search['status'] = $map['s.status'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $map['b.product_type']=array('eq','7');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
		//充值状态
		$map['s.type']=$search['type']=3;

		if(!empty($_REQUEST['uname'])){
            $map['s.uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['borrow_id'])){
			$search['borrow_id'] = $_REQUEST['borrow_id'];
			$borrowid = $this->bidsousuo($_REQUEST['borrow_id']);
			$map['s.borrow_id'] = intval($borrowid);
		}

		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){

			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['s.start_time'] = $_REQUEST['start_time'];
			$search['s.end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,b.id,b.product_type,b.danbao";
		import("ORG.Util.PageFilter");

		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->limit($limit)->order(" s.addtime DESC")->select();
        $total=0;
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
            $total+=$val['money'];
		}
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','标号','金额','时间','交易状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['borrow_id'] = $v['bid'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ? date("Y-m-d H:i:s",$v['addtime']) : '-' ;
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if(($v['status']==2) ||($v['status'] == 4)){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ? $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'manbiao');
			$xls->addArray($row);
			$xls->generateXML("manbiaolistcard");
			exit;
		}
		$this->assign("total",$total);
		$this->assign("list",$list);
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"manbiao");
		$this->assign("pagebar", $page);
		$this->display();
	}

	/**充值***/
	public  function chongzhi(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['status'] = intval($_REQUEST['status']);
			$search['status'] = $map['status'];
		}
		//充值状态
		$map['type'] = $search['type'] = 1;

		if(!empty($_REQUEST['uname'])){
            $map['uid'] = $this->find_name($_REQUEST['uname']);
            $search['uname'] = $_REQUEST['uname'];
		}

		if($_REQUEST['is_vip']=='yes'){
			$map['m.is_vip'] = 1;
			$search['is_vip'] = 'yes';
		}elseif($_REQUEST['is_vip']=='no'){
			$map['m.is_vip'] = 0;
			$search['is_vip'] = 'no';
		}

		if(!empty($_REQUEST['uid'])){
			$map['uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['uid'];
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['addtime'] = ["between", "{$start_time}, {$end_time}"];
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
		}
		$field = "uid,money,addtime,status,order_no";
		$limit = 0;
		if($_REQUEST['execl'] != "execl"){
		    import("ORG.Util.PageFilter");
		    $count = M('sinalog')->where($map)->count('uid');
		    $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
		    $page = $p->show();
		    
		    $limit = "{$p->firstRow},{$p->listRows}";
		}
		$list = M('sinalog s')->field($field)->join('lzh_members m on m.id = s.uid')->where($map)->limit($limit)->order(" addtime DESC")->select();
		
		$members = M('members');
		foreach ($list as $key => &$val){
			$uid = $val['uid'];
			$m_info = $members->field("user_name,is_vip")->find($uid);
			$val['uname'] = $m_info['user_name'];
			$val['is_vip'] = $m_info['is_vip'] == 1 ? '投资人/借款人' : '投资人';
			if($val['status'] == 1){
			    $val['status'] = "处理中";
			}else if($val['status'] == 2){
			    $val['status'] ="已完成";
			}else{
			    $val['status'] = "交易失败";
			}
			$val['addtime'] = isset($val['addtime']) ? date("Y-m-d H:i:s",$val['addtime']) : '-';
			$val['order_no'] = isset($val['order_no']) ? $val['order_no'] : '-';
		}
		
		//导出exel
		if($_REQUEST['execl'] == "execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row = [];
			$row[0] = ['UID','会员名','身份','金额','充值时间','充值状态','订单号'];
			$i = 1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['is_vip'] = $v['is_vip'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] = $v['addtime'];
			    $row[$i]['status'] = $v['status'];
			    $row[$i]['order_no'] = $v['order_no'];
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'chongzhi');
			$xls->addArray($row);
			$xls->generateXML("chongzhicard");
			exit;
		}

		$this->assign("list", $list);
		$this->assign('search', $search);
		$search['execl'] = "execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"chongzhi");
		$this->assign("pagebar", $page);
		$this->display();
	}

	/**用户提现***/
	public  function tixian(){
	  if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['s.status'] = intval($_REQUEST['status']);
			$search['status'] = $map['s.status'];
		}
		if($_REQUEST['is_vip']=='yes'){
			$map['m.is_vip'] = 1;
			$search['is_vip'] = 'yes';
		}elseif($_REQUEST['is_vip']=='no'){
			$map['m.is_vip'] = 0;
			$search['is_vip'] = 'no';
		}

		//充值状态
		$map['s.type']=$search['type']=array("in","2,14");
		if(!empty($_REQUEST['uname'])){
            $map['s.uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}

		if(!empty($_REQUEST['start_time'])&&empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			//$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$end_time = time();
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = '2099-01-01';
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = '2099-01-01';
		}elseif(!empty($_REQUEST['start_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,m.is_vip";
		//分页处理
		import("ORG.Util.PageFilter");
		$count = M('sinalog s')->join('lzh_members m on m.id = s.uid')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_members m on m.id = s.uid')->where($map)->limit($limit)->order("s.addtime DESC")->select();
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name,is_vip")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
			$list[$key]['is_vip'] ="投资人";
			if($username['is_vip']==1){
                $list[$key]['is_vip'] = "<span style='color:red'>投资人/借款人</span>";
             }
		}

		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','提现类型','金额','提现时间','完成时间','状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];

				//1.充值2.提现3.投标4.还款5.退款6.红包7.付款8.付提现手续费9.收提现手续费
				if($v['type']==2){
					$row[$i]['type'] = "余额提现";
				}else if($v['status']==14){
					$row[$i]['type'] ="代付提现卡";
				}else{
					$row[$i]['type'] ="代付提现卡";
				}
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ?date("Y-m-d H:i:s",$v['addtime']):"无";
				$row[$i]['completetime'] =isset($v['completetime']) ?  date("Y-m-d H:i:s",$v['completetime']) : '-';
				if($v['status']==4){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else if($v['status']==3){
					$row[$i]['status'] = "交易失败";
				}else{
					$row[$i]['status'] = "未生成订单";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ?  $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'tixian');
			$xls->addArray($row);
			$xls->generateXML("tixiancordlist");
			exit;
		}
		$this->assign("pagebar", $page);
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"tixian");
		$this->assign('list',$list);
		$this->display();
	}


	/**退款对**/
	public function tuikuang(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['status'] = intval($_REQUEST['status']);
			$search['status'] = $map['status'];
		}
		//充值状态
		$map['type']=$search['type']=5;
		if(!empty($_REQUEST['uname'])){
            $map['uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['uid'];
		}
		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,b.id,b.product_type,b.danbao";
		import("ORG.Util.PageFilter");
		$count = M('sinalog')->where($map)->count('uid');

		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog')->field($field)->where($map)->limit($limit)->order(" addtime DESC")->select();
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
		}
		foreach($list as $k => $v){
			$list[$k]['bid']=borrowidlayout1($v['id']);
		}
		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','金额','时间','状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ?  $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'tuikuang');
			$xls->addArray($row);
			$xls->generateXML("tuikuangrecord");
			exit;
		}


		$this->assign("list",$list);
		$this->assign("pagebar", $page);
		$this->assign("xaction",'tuikuang');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->display();
	}


	/***用户资金**/
	public  function zhiji(){
		$where="1=1";
		if(!empty($_REQUEST['uname'])){
		//	$uid = M("members")->getFieldByUserName(text(urldecode($_REQUEST['uname'])),'id');
            $map['user_name']=array("like","%".$_REQUEST['uname']."%");
            $uid=M("members")->where($map)->field('id')->select();
            $list="uid in (";
            $index=0;
            foreach($uid as $key=>$val){
                if($index==0){
                    $list.=" ".$val['id'];
                    $index=1;
                }else{
                    $list.=",".$val['id'];
                }
            }
            $list.=")";
			$where.=" AND  $list";
			$map['uid']=$uid;
            $search['uname']=$_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uid'])){
			$uid=intval($_REQUEST['uid']);
			$where.=" AND uid=$uid";
			$map['uid']=$uid;
            $search['uid']=$map['uid'];
        }
		//分页处理
		import("ORG.Util.PageFilter");
		$count = M('member_money')->where($map)->count('uid');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
        if($_REQUEST['execl']=="execl"){
            $sql=" select mm.uid, SUM(mm.account_money+mm.back_money) AS account_money,mm.money_collect,mm.money_freeze from lzh_member_money as mm left join  lzh_members as m  on mm.uid=m.id where $where and m.user_name!=\"\" group by uid ";
        }else{
            $sql=" select mm.uid, SUM(mm.account_money+mm.back_money) AS account_money,mm.money_collect,mm.money_freeze from lzh_member_money as mm left join  lzh_members as m  on mm.uid=m.id where $where and m.user_name!=\"\" group by uid limit $limit";

        }
		$list_money=M('member_money')->query("$sql");
		$list=array();
		foreach($list_money as $key =>$val){
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			if(empty($username['user_name'])){
				continue;
			}
			$investcapital = M("investor_detail")->where("investor_uid = {$val[uid]} AND repayment_time = 0")->SUM("capital"); //待收本金
			$investinterest = M("investor_detail")->where("investor_uid = {$val[uid]} AND repayment_time = 0")->SUM("interest"); //待收利息
			$hkcapital = M("investor_detail")->where("investor_uid = {$val[uid]} AND repayment_time != 0")->SUM("capital"); //收益本金
			$hkinterest = M("investor_detail")->where("investor_uid = {$val[uid]} AND repayment_time != 0")->SUM("interest"); //收益利息
			$borrowmoney = M("borrow_info")->where("borrow_uid = {$val[uid]}")->SUM("borrow_money"); //借款总额
			$borrowinterest = M("borrow_info")->where("borrow_uid = {$val[uid]}")->SUM("borrow_interest"); //借款总额
			$okcapital = M("investor_detail")->where("borrow_uid = {$val[uid]} AND repayment_time != 0")->SUM("capital"); //已还本金
			$okinterest = M("investor_detail")->where("borrow_uid = {$val[uid]} AND repayment_time != 0")->SUM("interest"); //已还利息
			$hongbao = M("hongbao")->where("uid = {$val[uid]} AND status=2")->sum("money");
			$list[$key]=$val;
			if($investcapital == null)$investcapital="0.00";
			if($investinterest == null)$investinterest="0.00";
			if($hkcapital == null)$hkcapital="0.00";
			if($hkinterest == null)$hkinterest="0.00";
			if($borrowmoney == null)$borrowmoney="0.00";
			if($borrowinterest == null)$borrowinterest="0.00";
			if($okcapital == null)$okcapital="0.00";
			if($okinterest == null)$okinterest="0.00";
			if($hongbao == null)$hongbao="0.00";
			$list[$key]['hongbao'] = $hongbao;
			$list[$key]['okcapital'] = $okcapital;
			$list[$key]['okinterest'] = $okinterest;
			$list[$key]['borrowmoney'] = $borrowmoney;
			$list[$key]['borrowinterest'] = $borrowinterest;
			$list[$key]['investcapital'] = $investcapital;
			$list[$key]['investinterest'] = $investinterest;
			$list[$key]['hkcapital'] = $hkcapital;
			$list[$key]['hkinterest'] = $hkinterest;
			$sql1="select SUM(money) AS chongzh from lzh_member_payonline where status=1 AND uid={$val[uid]}";
			$one=D('member_money')->query($sql1);
			if($one[0]['chongzh'] == null)$one[0]['chongzh']="0.00";
			$list[$key]['chongzhi']=$one[0]['chongzh'];
			$sql2="select SUM(withdraw_money) AS tixian from lzh_member_withdraw where withdraw_status=2 AND uid={$val[uid]}";
			$one2=D('member_money')->query($sql2);
			if($one2[0]['tixian'] == null)$one2[0]['tixian']="0.00";
			$list[$key]['tixian']=$one2[0]['tixian'];
			$list[$key]['uname']=$username['user_name'];

		}

        //导出exel
        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Caiwut",0,1,'打印用户资金！');//管理员操作日志
            $row=array();
            $row[0]=array('UID','会员名','冻结金额','红包总额','待收本金','待收利息','获得还款本金','获得还款利息','借款资金','借款待付利息','借款已还本金','借款已还利息','充值金额','提现金额');
            $i=1;
            foreach($list as $v){
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['uname'] = $v['uname'];
                $row[$i]['money_freeze']=$v['money_freeze'];
                $row[$i]['hongbao']=$v['hongbao'];
                $row[$i]['investcapital']=$v['investcapital'];
                $row[$i]['investinterest']=$v['investinterest'];
                $row[$i]['hkcapital']=$v['hkcapital'];
                $row[$i]['hkinterest']=$v['hkinterest'];
                $row[$i]['borrowmoney']=$v['borrowmoney'];
                $row[$i]['borrowinterest']=$v['borrowinterest'];
                $row[$i]['okcapital']=$v['okcapital'];
                $row[$i]['okinterest']=$v['okinterest'];
                $row[$i]['chongzhi']=$v['chongzhi'];
                $row[$i]['tixian']=$v['tixian'];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, 'zijin');
            $xls->addArray($row);
            $xls->generateXML("zijin");
            exit;
        }


		/* $list=M('member_money mm')->query("select mm.uid,SUM(mm.account_money+mm.back_money) AS account_money,mm.money_collect,SUM(mp.money) AS chongzhi,SUM(mw.withdraw_money) AS tixian from lzh_member_money mm left join lzh_member_payonline mp on mm.uid=mp.uid left join lzh_member_withdraw mw on mw.uid=mm.uid group by mm.uid");
		$field="mm.uid,sum(mm.account_money+mm.back_money) as account_money,mm.money_collect,sum(mp.money) as chongzhi,sum(mw.withdraw_money) as tixian";
		$map['mw.withdraw_status']=2;
		$map['mp.status']=1;
		$list = M('member_money mm')->field($field)->join("{$this->pre}member_payonline mp ON mm.uid=mp.uid")->join("{$this->pre}member_withdraw mw ON mm.uid=mw.uid")->where($map)->limit($Lsql)->order('mm.uid DESC')->group('mm.uid')->select();
		$field= 'm.id,m.user_phone,m.reg_time,m.user_name,m.customer_name,m.user_leve,m.time_limit,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,m.user_email,m.recommend_id,m.is_borrow,m.is_vip';
		$list = M('members m')->field($field)->join("{$this->pre}member_money mm ON mm.uid=m.id")->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
		 */
		$this->assign("pagebar", $page);
		$this->assign('list',$list);
		$this->assign("xaction",'zhiji');
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
		$this->display();

	}

	/****查看新浪余额**/
	public function loadsina(){
		import("@.Oauth.sina.Weibopay");
		$payConfig = FS("Webconfig/payconfig");
		$weibopay = new Weibopay();
		$data['service'] 			  = "show_member_infos_sina";							//接口名称
		$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
		$data['request_time']		  = date('YmdHis');											//请求时间
		$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
		$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
		$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
		$data['identity_id'] 		  = "20151008".$_REQUEST['id'];													//ID类型
		$data['identity_type']		  = "UID";		//支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
		ksort($data);
		$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
		$setdata 					  = $weibopay->createcurl_data($data);
		$result						  = $weibopay->curlPost($payConfig['sinapay']['mgs'],$setdata);//模拟表单提交
		echo $result;
		//$this->display();
	}

	/***所以标的列表***/
	public function borrowlist(){
		$map['b.borrow_status'] = array("in","6,7,9");//还款中
		if(!empty($_REQUEST['uname'])){
			$map['b.borrow_uid'] =$this->find_name($_REQUEST['uname']);
			//$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $map['b.product_type']=array('eq','7');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
		if(!empty($_REQUEST['uid'])){
			$search['uid'] =$_REQUEST['uid'];
			$pre = substr($_REQUEST['uid'],0,2);
	        $id = substr($_REQUEST['uid'],2);
	        if($pre == ZJ){//质金链
	            if($id<$renumber){
	                $borrowid=$id;
	            }else{
	                $bid = M('borrow_pledge')->where("id=".$id)->find();
	                $borrowid=$bid['borrow_id'];
	            }
	        }else if($pre == RJ){//融金链
	            $bid = M('borrow_finance')->where("id=".$id)->find();
	            $borrowid=$bid['borrow_id'];
	        }else if($pre == XJ){//信金链
	            $bid = M('borrow_credit')->where("id=".$id)->find();
	            $borrowid=$bid['borrow_id'];
	        }else if($pre == YJ){//优金链
	            $bid = M('borrow_optimal')->where("id=".$id)->find();
	            $borrowid=$bid['borrow_id'];
	        }
			$map['b.id'] = intval($borrowid);
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);
			$search['end_time'] = urldecode($_REQUEST['end_time']);
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.deadline'] = array("gt",$xtime);
			$search['start_time'] = $xtime;
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.deadline'] = array("lt",$xtime);
			$search['end_time'] = $xtime;
		}

		//分页处理
		import("ORG.Util.PageFilter");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$Lsql =0;
		}
		//分页处理
		$field= 'm.id as mid,m.customer_name,b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.product_type,b.borrow_money,b.borrow_interest,b.repayment_money,b.product_type,b.danbao,b.repayment_interest,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.deadline,m.user_name,m.user_phone,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();

		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$listType = $Bconfig['REPAYMENT_TYPE'];

		foreach ($list as $k => $v) {
			$vx = M('investor_detail')->field('deadline,sort_order,status')->where(" borrow_id={$v['id']} AND status in(4,7) ")->order("deadline ASC")->find();
            if($list[$k]['repayment_type']==1){
                $list[$k]['borrow_duration'].="天";
            }else{
                $list[$k]['borrow_duration'].="月";
            }
            $list[$k]['repayment_time'] = $vx['deadline'];
			$list[$k]['sort_order'] = $vx['sort_order'];
			$list[$k]['auto'] = "auto";
			$list[$k]['repayment_type'] = $listType[$v['repayment_type']];
			$need = M('investor_detail')->field(' sum(capital + interest) as need')->where(" borrow_id={$v['id']} AND deadline=$vx[deadline] ")->find();
			$list[$k]['need_money'] = $need['need'];
		}
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
            if($v['product_type'] == 5){
                $fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
                $list[$k]['borrow_fee'] = $fee['fee'];
            }else{
                $list[$k]['borrow_fee'] = $v['borrow_fee'];
            }
        }
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了导出借款合同列表！');//管理员操作日志
			$row=array();
			$row[0]=array('标号','用户名','手机号','客服','标题','借款金额','已还金额','借款期限','借款手续费','还款方式','最近还款时间');
			$i=1;
			foreach($list as $v){
				$row[$i]['bid'] = $v['bid'];
				$row[$i]['uname'] = $v['user_name'];
				$row[$i]['user_phone'] = $v['user_phone'];
				$row[$i]['customer_name'] = $v['customer_name'];
				$row[$i]['borrow_name'] = $v['borrow_name'];
				$row[$i]['borrow_money'] = $v['borrow_money'];
				$row[$i]['repaymented'] = $v['repayment_money']+$v['repayment_interest'];
				$row[$i]['borrow_duration'] = $v['borrow_duration'];
				$row[$i]['repayment_type'] = $v['repayment_type'];
				$row[$i]['deadline'] = isset($v['deadline']) ? date("Y-m-d",$v['deadline']) : '-';
				if($v['product_type'] == 5){
                    $fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
                    $row[$i]['borrow_fee'] = $fee['fee'];
                }else{
                    $row[$i]['borrow_fee'] = $v['borrow_fee'];
                }
				$i++;
			}
			$xls = new Excel_XML('UTF-8', false, 'borrowlist');
			$xls->addArray($row);
			$xls->generateXML("borrowlist");
			exit;
		}
		$this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
		$this->assign("list", $list);
		$this->assign("pagebar", $page);
		$this->assign("search", $search);
		$search['execl']="execl";
		$this->assign("xaction","borrowlist");
		$this->assign("query", http_build_query($search));
		$this->display();
	}

	/***差看合同***/
	public function  showhetong(){
		$per = C('DB_PREFIX');
		$uid=intval($_GET['uid']);
		$borrow_id=intval($_GET['id']);
		if($borrow_id > C("SHANG_HETONG"))
	     {
	        $shanglist = M("shangshang")->where("borrow_id = ".$borrow_id)->find();
	        if($shanglist){
	            import("@.Oauth.ancun.Shang");
	            $shang = new Shang();
	            $rs = $shang->gethetong($shanglist["sign_id"],$shanglist["doc_id"]);
	            redirect($rs["resultText"]["url"]);
	        }else{
	            $this->error("合同生成中，请稍候再试");
	        }
	     }else{
			//$invest_id=intval($_GET['id']);
	        // show_contract($borrow_id);
		    //所以投标记录
			$iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->select();
			//标详情
			$binfo = M('borrow_info')->field('id,borrow_use,repayment_type,borrow_duration,borrow_uid,borrow_type,borrow_use,borrow_money,full_time,add_time,borrow_interest_rate,deadline,second_verify_time,warehousing,borrow_duration_txt,product_type')->find($borrow_id);
			//借款人信息
			$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_regtype,m.user_name,mi.idcard')->where("m.id=$uid")->find();
			if(empty($mBorrow["real_name"])){
				 $co_info=M('members_company mc')->field('mc.company_name')->where("mc.uid={$uid}")->find();
				 $mBorrow["real_name"]=$co_info["company_name"];
				 $mBorrow["idcard"]=substr_replace($co_info["license_no"],'*********',4,20);;
			 	 $mBorrow["is_com"]=1;
			}
			$mInvests=array();
			foreach ($iinfos as  $key =>$val){
				$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name,m.user_phone,m.user_regtype')->where("m.id={$val['investor_uid']}")->find();
				$mInvests[$key]['user_regtype']=$mInvest['user_regtype'];
				if ($mInvests[$key]['user_regtype'] == 1) {
					$mInvests[$key]['real_name']=$mInvest['real_name'];
				} elseif($mInvests[$key]['user_regtype'] == 2) {
					$mCompany = M('members_company mc')->field('mc.company_name')->where("mc.uid={$val['investor_uid']}")->find();
					$mInvests[$key]['real_name']=$mCompany['company_name'];
				}

				$mInvests[$key]['user_phone']=$mInvest['user_phone'];
				$mInvests[$key]['user_name']=$mInvest['user_name'];
				$detail = M('investor_detail d')->field('d.invest_id,sum(d.capital+d.interest-d.interest_fee) benxi,capital')->where("d.borrow_id={$borrow_id} and d.invest_id ={$val['id']}")->group('d.invest_id')->find();
				$mInvests[$key]['capital']=$detail['capital'];
				$mInvests[$key]['benxi']=$detail['benxi'];
				$mInvests[$key]['total']=$detail['total'];
				$mInvests[$key]['investor_capital']=$val['investor_capital'];


			}

			//$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$iinfo['investor_uid']}")->find();
			$jgcode = M("members m")->join("{$per}member_department_info jg ON jg.uid=m.id")->field('jg.institution_code')->where("m.id={$borrow_uid}")->find();

			//if(!is_array($iinfo)||!is_array($binfo)||!is_array($mBorrow)||!is_array($mInvest)) exit;

			//$detail = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();


			$detailinfo = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,(d.capital+d.interest-d.interest_fee) benxi,d.capital,d.interest,d.interest_fee,d.sort_order,d.deadline')->where("d.borrow_id={$borrow_id}")->select();
			$repay=array();
			foreach ($detailinfo as $key =>$val){
				 $repay['sort_order']=$val['sort_order'];
				 $repay['benxi']+=$val['benxi'];
				 $repay['capital']+=$val['capital'];
				 $repay['interest']+=$val['interest'];
				 $repay['interest_fee']+=$val['interest_fee'];
				 $repay['deadline']=$val['deadline'];
			}

			$time = M('borrow_investor')->field('id,add_time')->where("borrow_id={$borrow_id} order by add_time asc")->limit(1)->find();

			if($binfo['repayment_type']==1){
					$deadline_last = strtotime("+{$binfo['borrow_duration']} day",$time['add_time']);
				}else{
					$deadline_last = strtotime("+{$binfo['borrow_duration']} month",$time['add_time']);
				}
			$this->assign('deadline_last',$deadline_last);
			//$this->assign('detail',$detail);

			$type1 = $this->gloconf['BORROW_USE'];
			$binfo['borrow_use_no'] = $binfo['borrow_use'];
			$binfo['borrow_use'] = $type1[$binfo['borrow_use']];
			$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();

			$this->assign("ht",$ht);
			$type = $borrow_config['REPAYMENT_TYPE'];
			//echo $binfo['repayment_type'];
			$binfo['repayment_name'] = $type[$binfo['repayment_type']];

			$iinfo = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->find();
			$iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
			$memberinfo = M('members')->find($uid);
			$this->assign("bid","CCFAX");
			//print_r($type);
			$this->assign('iinfo',$iinfo);
			$this->assign('memberinfo',$memberinfo);
			$this->assign('jgcode',$jgcode);

			//$detail_list = M('investor_detail')->field(true)->where("invest_id={$invest_id}")->select();
			//$this->assign("detail_list",$detail_list);
	        //判断类型
	        if($binfo['borrow_duration_txt']!=""){ //新版判断方式
	            $newhetong=1;
	            $add_info= D("borrow_info_additional")->get_additional_info($borrow_id);
	            $duration_list=explode("+",$binfo['borrow_duration_txt']);
	            if(count($duration_list)==2){
	                $show_type='A';
	                //$this->assign("c_date1",intval($duration_list[0]));
	                //$this->assign("c_date2",intval($duration_list[1]));
	                $this->assign("a_date", intval($duration_list[0])+intval($duration_list[1]));
	                $this->assign("tidan_rate",$add_info['frist_rate']);
	                $this->assign("xianhuo_rate",$add_info['second_rate']);


	                $day_array=explode("+",$binfo['borrow_duration_txt']);
	                $total_time=intval(mb_strcut($day_array[0],0,mb_strlen($day_array[0])-1));
	                if(count($day_array)==2){
	                    $day2=intval(mb_strcut($day_array[1],0,mb_strlen($day_array[0])-1));
	                    $total_time+=$day2;
	                }
	                //提单转现货模式， 需要修正时间
	                if($binfo['borrow_duration']!=$total_time){
	                    $binfo['borrow_duration']=$total_time;
	                    if($binfo['repayment_type']==1){
	                        $repay['deadline']=$binfo['deadline'] = strtotime("+{$total_time} day",$binfo['second_verify_time']);
	                    }else{
	                        $repay['deadline']=$binfo['deadline']= strtotime("+{$total_time} month",$binfo['second_verify_time']);
	                    }
	                    //修正利息
	                    foreach($mInvests as $key=>$mInvest){
	                        $seconde_interest=getFloatValue($day2*$mInvests[$key]['capital']*$add_info['second_rate']/36000,2);
	                        $mInvests[$key]['benxi']+=$seconde_interest;
	                        $repay['interest']+=$seconde_interest;
	                        $repay['benxi']+=$seconde_interest;
	                    }

	                }
	            }
	            else if(count($duration_list)==1){
	                if($binfo['product_type']==1){
	                    $show_type='A';
	                    $this->assign("a_date",intval($duration_list[0]));
	                    $this->assign("tidan_rate",$add_info['frist_rate']);
	                }
	                else if($binfo['product_type']==3||$binfo['product_type']==7){
	                    $show_type='B';
	                    $this->assign("b_date",intval($duration_list[0]));
	                    $this->assign("xianhuo_rate",$add_info['frist_rate']);
	                }
	                else if($binfo['product_type']==2){ //本来打算提单标，后面转现货
	                    $show_type='A';
	                    $this->assign("a_date",intval($duration_list[0]));
	                    $this->assign("tidan_rate",$add_info['frist_rate']);
	                }
	            }
	            $this->assign("show_type",$show_type);
	            //parser 利率


	        }
	        $renumber = C('RENUMBER_BORROW.new_grade');
	        if($binfo['id']>=$renumber){
	        	$binfo['id'] = borrowidlayout1($binfo['id']);
	        }
	        $this->assign('binfo',$binfo);
	        $this->assign('mBorrow',$mBorrow);
	        $this->assign('mInvests',$mInvests);
	        $this->assign('repay',$repay);
	        $flag=C('START_FLAG');
	        if($binfo['product_type']==5){//分期购
	        	$where1['uid']=$uid;
	            $info=M("members_company")->where($where1)->field("license_no")->find();
	            $this->assign("license_no",$info["license_no"]);
	            $r_info=getBorrowInvest($borrow_id,$uid);
	            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
	            $this->assign("r_info",$r_info["list"]);
	            // $this->buildHtml("contract_".$borrow_id,"html/contract/","fqghetong");
	           	if($binfo["borrow_use_no"] == 9){
	                $this->display("newfqgagreement");
	            }else{
	                $this->display("fqghetong");
	            }
	        }else
	        if($binfo['product_type']==7){//优金链
	        	// $this->buildHtml("contract_".$borrow_id, "html/contract/", "yjlhetong");
	            $this->display("yjlhetong");
	        }else if($binfo['product_type']==6){//信用标
	        	if($binfo['repayment_type']==2){
	        		$where1['uid']=$uid;
	            $info=M("members_company")->where($where1)->field("license_no")->find();
	            $this->assign("license_no",$info["license_no"]);
	            $r_info=getBorrowInvest($borrow_id,$uid);
	            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
	            $this->assign("r_info",$r_info["list"]);
	        	}
	        	// $this->buildHtml("contract_".$borrow_id,"html/contract/","credithetong");
	        	$this->display("credithetong");
	        }else if($binfo['product_type']==4){//融金链
	            $where1['uid']=$uid;
	            $info=M("members_company")->where($where1)->field("license_no")->find();
	            $this->assign("license_no",$info["license_no"]);
	            $r_info=getBorrowInvest($borrow_id,$uid);
	            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
	            $this->assign("r_info",$r_info["list"]);
	            if($borrow_id > $flag['break_point_3']){
	            	// $this->buildHtml("contract_".$borrow_id,"html/contract/","rjlhetong");
		            $this->display("rjlhetong");
	            }else{
	            	 // $this->buildHtml("contract_".$borrow_id,"html/contract/","monthhetong");
	           		 $this->display("monthhetong");
	            }
	        }else if($binfo['product_type']<4&&$borrow_id > $flag['break_point_3']){//质金链
	        	// $this->buildHtml("contract_".$borrow_id, "html/contract/", "zjlhetong");
	            $this->display("zjlhetong");
	        }else if(isset($newhetong)){
	            if ($borrow_id <= $flag['break_point_1']) {
	            	// $this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong");
	            	$this->display("newhetong");
	            }
	            if ($flag['break_point_1'] < $borrow_id && $borrow_id <= $flag['break_point_2']) {
	            	$this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong1");
	            	$this->display("newhetong1");
	            }
	            if ($borrow_id > $flag['break_point_2'] && $borrow_id <= $flag['break_point_3']) {
	            	// $this->buildHtml("contract_".$borrow_id, "html/contract/", "agreement20160414");
	            	$this->display("agreement20160414");
	            }
	        }else{
	            // $this->buildHtml("contract_".$borrow_id,"html/contract/","index");
	            $this->display("index");
	        }
	    }
	}

	/**红包奖励对账***/
	public function redbag(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['status'] = intval($_REQUEST['status']);
			$search['status'] = $map['status'];
		}
		//红包状态
		$map['type']=$search['type']=6;
		if(!empty($_REQUEST['uname'])){
            $map['uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['uid'];
		}

		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="*";
		import("ORG.Util.PageFilter");
		$count = M('sinalog')->where($map)->count('uid');

		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog')->field($field)->where($map)->limit($limit)->order(" addtime DESC")->select();
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
		}

		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','金额','时间','状态','单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no']=$v['order_no'];
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'redbag');
			$xls->addArray($row);
			$xls->generateXML("redbagrecord");
			exit;
		}


		$this->assign("list",$list);
		$this->assign("pagebar", $page);
		$this->assign("xaction",'redbag');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->display();
	}

	/***还款对账**/
	public  function huankuang(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['s.status'] = intval($_REQUEST['status']);
			$search['status'] = $map['s.status'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');//标号重新编排起始ID
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $map['b.product_type']=array('eq','7');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
		//充值状态
		$map['type']=$search['type']=4;
		if(!empty($_REQUEST['uname'])){
            $map['s.uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,b.id,b.product_type,b.danbao ";
		import("ORG.Util.PageFilter");
		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->count('s.uid');

		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->limit($limit)->order(" s.addtime DESC")->select();
		$mylist=$list;

		foreach ($mylist as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
			$list[$key]['bid']=borrowidlayout1($val['borrow_id']);//标号
            if($uid>1){//批量还款和综合服务费的不计算在内
                $freelist=M("investor_detail")->where(array("borrow_uid"=>$val["uid"],"borrow_id"=>$val["borrow_id"],"sort_order"=>$val["sort_order"],'is_debt'=>0))->select();//把所有投标人的金额累加
				$list[$key]["capital"]=0;
				$list[$key]["interest"]=0;
				$list[$key]["expired_money"];
				foreach ($freelist as $v){
					$list[$key]["capital"]+=$v["capital"];//一个标的每一期的本金
					$list[$key]["interest"]+=$v["interest"];//一个标的每一期的利息
					$list[$key]["expired_money"]+=$v["expired_money"];//一个标的每一期的逾期罚息
				}
				if($val["product_type"]==5){//分期购标
					$myfreelist=M("allwood_ljs")->where(array("borrow_id"=>$val["borrow_id"]))->find();
					if($myfreelist){
						$list[$key]["fee"]=$myfreelist["fee"];
					}else{
						$list[$key]["fee"]=0;
					}
				}
			}
		}
		unset($mylist);
		//导出exel
		if($_REQUEST['execl']=="execl"){
			set_time_limit(0);
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有满标对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','标号','本金','利息','服务费','罚息','金额','期号','时间','状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['borrow_id']=$v['bid'];
				$row[$i]["capital"]=$v["capital"];
				$row[$i]["interest"]=$v["interest"];
				$row[$i]["fee"]=$v["fee"];
				$row[$i]["expired_money"]=$v["expired_money"];
				$row[$i]['money'] = $v['money'];
				$row[$i]['sort_order'] =$v['sort_order'];
				$row[$i]['addtime'] =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ?  $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'huankuang');
			$xls->addArray($row);
			$xls->generateXML("huankuangrecord");
			exit;
		}


		$this->assign("list",$list);
		$this->assign("pagebar", $page);
		$this->assign("xaction",'huankuang');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->display();
	}

	/***投资券对账***/
	public function touziquan(){
		if(!empty($_REQUEST['status'])){
			$search['status'] = $_REQUEST['status'];
			if($_REQUEST['status']==1){
				$map['s.status'] = array('in','2,4');
			}else{
				$map['s.status'] = array('not in','2,4');
			}
		}
        if(!empty($_REQUEST['uname'])){
            $map['c.user_phone'] =$_REQUEST['uname'];
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$map['s.coupons'] = array('exp','is not null');
		$map['s.type']=3;//投标
        $map['c.type'] = array('in','1,2');//1:投资券，2:体验券
		import("ORG.Util.PageFilter");
		$field = "s.uid,s.borrow_id,s.order_no,s.money as invetmoney,s.addtime,s.status,c.user_phone,c.money,c.admin_name as issuer,c.name,c.addtime as issue_time,CAST((c.endtime-unix_timestamp(c.addtime))/86400 as UNSIGNED) as issue_timerange,b.id,b.product_type,b.collect_day,b.borrow_duration_txt  ";
		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->join('lzh_members m on m.id = s.uid')->join('lzh_coupons c on c.serial_number = s.coupons and c.user_phone=m.user_phone ')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->join('lzh_members m on m.id = s.uid')->join('lzh_coupons c on c.serial_number = s.coupons and c.user_phone=m.user_phone ')->where($map)->limit($limit)->order('s.completetime DESC')->select();
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['borrow_id']);
            $list[$k]['timelimit']=C('EXPERIENCE_DURATION')."天";
        }
        //导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有投资券对账！');//管理员操作日志
			$row=array();
			$row[0]=array('用户ID','手机号','标号','投资金额','投资券使用金额','投资券来源','投资券发放人','投资券发放时间','投资券有效期','投资期限','投标日期','状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid']                 = $v['uid'];
				$row[$i]['user_phone']          = $v['user_phone'];
				$row[$i]['bid']                 = $v['bid'];
				$row[$i]['invetmoney']          = $v['invetmoney'];
				$row[$i]['money']               = $v['money'];
				$row[$i]['name']                = $v['name'];
				$row[$i]['issuer']              = $v['issuer'];
				$row[$i]['issue_time']          = $v['issue_time'];
				$row[$i]['issue_timerage']      = $v['issue_timerange']."天";
				$row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
				$row[$i]['addtime']             =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
				if($v['status']==2||$v['status']==4){
					$row[$i]['status'] ="投资成功";
				}else{
					$row[$i]['status'] = "投资失败";
				}
				$row[$i]['order_no'] = $v['order_no'];
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'touziquan');
			$xls->addArray($row);
			$xls->generateXML("touziquanrecord");
			exit;
		}
        $this->assign("pagebar", $page);
		$this->assign("xaction",'touziquan');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('list',$list);
		$this->display();
	}
	/***加息券对账***/
	public function jxquan(){
        //只找 成功发放加息金额的标的
        $map['s.status'] = array('in','2,4');
        if(!empty($_REQUEST['uname'])){
            $map['c.user_phone'] =$_REQUEST['uname'];
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$map['s.jx_coupons'] = array('exp','is not null');
		$map['s.type']=3;//投标
		$map['c.type']=3;//加息券
//        $map['b.borrow_status']=7;//已还款的表
        $map['b.has_pay']=array('gt',0);//已还款的表
		import("ORG.Util.PageFilter");
		$field = "s.uid,s.borrow_id,s.order_no,s.money as invetmoney,s.addtime,s.status,c.user_phone,c.money,b.id,b.product_type,b.repayment_type,b.collect_day,b.borrow_duration_txt,b.jiaxi_rate";
		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->join('lzh_members m on m.id = s.uid')->join('lzh_coupons c on c.serial_number = s.jx_coupons and c.user_phone=m.user_phone ')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->join('lzh_members m on m.id = s.uid')->join('lzh_coupons c on c.serial_number = s.jx_coupons and c.user_phone=m.user_phone ')->where($map)->limit($limit)->order('s.completetime DESC')->select();
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['borrow_id']);
            $list[$k]['timelimit']=C('EXPERIENCE_DURATION')."天";
            //加息金额

            $where['i.borrow_id'] = $v['borrow_id'];
            $where['i.investor_uid'] = $v['uid'];
            $where['i.investor_capital'] = $v['invetmoney'];
            $where['d.repayment_time'] = array('gt',0);
            $where['d.jiaxi_rate'] =$v['money'];
            if($v['repayment_type'] == 1){
                //天标
                $jx =  M('borrow_investor i')->field('d.jiaxi_money')->join('lzh_investor_detail d on d.invest_id = i.id')->where($where)->find();
                $list[$k]['jiaxi_money'] = $jx['jiaxi_money'];
            }else{
                $jx =  M('borrow_investor i')->field('i.id')->join('lzh_investor_detail d on d.invest_id = i.id')->where($where)->select();
                $list[$k]['jiaxi_money'] = M('investor_detail')->where(array('invest_id'=>$jx[0]['id'],'repayment_time'=>array('gt',0)))->sum('jiaxi_money');
            }
        }
        //导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有加息券对账！');//管理员操作日志
			$row=array();
			$row[0]=array('用户ID','用户名','标号','投资金额','使用加息券','投资期限','投标日期','加息金额','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['user_phone'] = $v['user_phone'];
				$row[$i]['bid'] = $v['bid'];
				$row[$i]['invetmoney'] = $v['invetmoney'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
				$row[$i]['addtime'] =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
//				if($v['status']==2||$v['status']==4){
//					$row[$i]['status'] ="投资成功";
//				}else{
//					$row[$i]['status'] = "投资失败";
//				}
                $row[$i]['jx_money'] = $v['jiaxi_money'];
				$row[$i]['order_no'] = $v['order_no'];
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'jxquan');
			$xls->addArray($row);
			$xls->generateXML("jxquanrecord");
			exit;
		}
        $this->assign("pagebar", $page);
		$this->assign("xaction",'jxquan');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('list',$list);
		$this->display();
	}

	/***提现手续费表格***/
	public function shouxufei(){

		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['status'] = intval($_REQUEST['status']);
			$search['status'] = $map['status'];
		}

		//手续费状态
		$map['type']=$search['type']=8;
		if(!empty($_REQUEST['uname'])){
            $map['uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}

		if(!empty($_REQUEST['uid'])){
			$map['uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['uid'];
		}
		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){
			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="*";
		import("ORG.Util.PageFilter");
		$count = M('sinalog')->where($map)->count('uid');


		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog')->field($field)->where($map)->limit($limit)->order(" addtime DESC")->select();
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
		}

		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了对账手续费！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','金额','时间','状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ?  date("Y-m-d H:i:s",$v['addtime']) : '-';
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ?  $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'shouxufei');
			$xls->addArray($row);
			$xls->generateXML("shouxufeirecord");
			exit;
		}


		$this->assign("list",$list);
		$this->assign("pagebar", $page);
		$this->assign("xaction",'shouxufei');
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->display();
	}

	//综合服务费对账
	public function feemoney(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['s.status'] = intval($_REQUEST['status']);
			$search['status'] = $map['s.status'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $map['b.product_type']=array('eq','7');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
		//充值状态
		$map['s.type']=$search['type']=10;

		if(!empty($_REQUEST['uname'])){
            $map['s.uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['borrow_id'])){
			$map['s.borrow_id'] = intval($_REQUEST['borrow_id']);
			$search['borrow_id'] = $map['s.borrow_id'];
		}

		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){

			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,b.id,b.product_type,b.danbao";
		import("ORG.Util.PageFilter");

		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->limit($limit)->order(" s.addtime DESC")->select();
        $total=0;
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
            $total+=$val['money'];
		}
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有综合服务费对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','标号','金额','时间','交易状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['borrow_id'] = $v['bid'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ? date("Y-m-d H:i:s",$v['addtime']) : '-' ;
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ? $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'fuwufei');
			$xls->addArray($row);
			$xls->generateXML("feelistcard");
			exit;
		}
		$this->assign("total",$total);
		$this->assign("list",$list);
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"feemoney");
		$this->assign("pagebar", $page);
		$this->display();
	}

  	//担保金对账
	public function danbao(){
		if(!empty($_REQUEST['status']) && $_REQUEST['status']>-1){
			$map['s.status'] = intval($_REQUEST['status']);
			$search['status'] = $map['s.status'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $map['b.product_type']=array('eq','7');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
		//充值状态
		$map['s.type']=$search['type']=13;

		if(!empty($_REQUEST['uname'])){
            $map['s.uid'] =$this->find_name($_REQUEST['uname']);
            $search['uname'] =$_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uid'])){
			$map['s.uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['s.uid'];
		}
		if(!empty($_REQUEST['borrow_id'])){
			$map['s.borrow_id'] = intval($_REQUEST['borrow_id']);
			$search['borrow_id'] = $map['s.borrow_id'];
		}

		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){

			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['s.addtime'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
			$xtime['start_time'] = $_REQUEST['start_time'];
			$xtime['end_time'] = $_REQUEST['end_time'];
		}
		$field="s.*,b.id,b.product_type,b.danbao";
		import("ORG.Util.PageFilter");

		$count = M('sinalog s')->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->count('s.uid');
		$p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M('sinalog s')->field($field)->join('lzh_borrow_info b on b.id = s.borrow_id')->where($map)->limit($limit)->order(" s.addtime DESC")->select();
        $total=0;
		foreach ($list as $key =>$val){
			$list[$key]=$val;
			$uid=$val['uid'];
			$username=M('members')->field("user_name")->where("id=$uid")->find();
			$list[$key]['uname']=$username['user_name'];
            $total+=$val['money'];
		}
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
		//导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有咨询服务费对账！');//管理员操作日志
			$row=array();
			$row[0]=array('UID','会员名','标号','金额','时间','交易状态','订单号');
			$i=1;
			foreach($list as $v){
				$row[$i]['uid'] = $v['uid'];
				$row[$i]['uname'] = $v['uname'];
				$row[$i]['borrow_id'] = $v['bid'];
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] =isset($v['addtime']) ? date("Y-m-d H:i:s",$v['addtime']) : '-' ;
				if($v['status']==1){
					$row[$i]['status'] = "处理中";
				}else if($v['status']==2){
					$row[$i]['status'] ="已完成";
				}else{
					$row[$i]['status'] = "交易失败";
				}
				$row[$i]['order_no'] = isset($v['order_no']) ? $v['order_no'] : '-';
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'fuwufei');
			$xls->addArray($row);
			$xls->generateXML("feelistcard");
			exit;
		}
		$this->assign("total",$total);
		$this->assign("list",$list);
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"danbao");
		$this->assign("pagebar", $page);
		$this->display();
	}
    public function  company(){
        if(!empty($_REQUEST['tuname'])){
            $search['m.user_name'] =array('like',"%".$_REQUEST['tuname']."%");
            $map['tuname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['uname'])){
            $search['mm.user_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['tuid'])){
            $search['m.id'] =intval($_REQUEST['tuid']);
            $map['tuid']=intval($_REQUEST['tuid']);
        }
        if(!empty($_REQUEST['uid'])){
            $search['mm.id'] =intval($_REQUEST['uid']);
            $map['uid']=intval($_REQUEST['uid']);
        }

        $search["co.end_time"]=array("neq",0);
        $search["co.money"]=array("neq",0);
        $field="co.money as return_money,i.investor_uid,i.investor_capital,b.borrow_name,b.id,b.borrow_duration,b.second_verify_time,m.recommend_id,mm.user_name as tuijian,m.user_name,mm.user_phone,mm.id as tuijian_id,b.borrow_duration_txt,b.product_type";
        $result=M("company_profit co")->join("lzh_borrow_info b on co.borrow_id=b.id")->join("lzh_borrow_investor i on i.id=co.investor_id")->join("lzh_members m on m.id=co.buid")->join("lzh_members mm on mm.id=co.uid")->field($field)->where($search)->select();
        $info=array();
        $money=0;
        $return_money=0;
        foreach($result as $key=>$val){
            $val['borrow_duration']=$val['borrow_duration_txt'];
            $money+=intval($val['investor_capital']);
            $val['second_verify_time']=date("Y-m-d", $val['second_verify_time']);
            $info[]=$val;
            $return_money+=$val['return_money'];
        }
        foreach($info as $k => $v){
            $info[$k]['bid']=borrowidlayout1($v['id']);
        }
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        //过滤
        $info1=array();
        foreach($info as $key=>$val){
            if(($key>=$min)&&($key<$max)){
                $info1[]=$val;
            }
        }
        //导出exel
        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Caiwut",0,1,'执行导出公司内部推荐用户投资分红！');//管理员操作日志
            $row=array();
            $row[0]=array('投资人id','投资人','投资标号','标号名称','标复审时间','标期限','投资金额','推荐人','推荐人id','推荐人手机号码','获取返利');
            $i=1;
            foreach($info as $v){
                $row[$i]['investor_uid'] = $v['investor_uid'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['borrow_id'] = $v['bid'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration'] = $v['borrow_duration'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['tuijian'] = $v['tuijian'];
                $row[$i]['tuijian_id'] = $v['tuijian_id'];
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, date("Ymd").'company');
            $xls->addArray($row);
            $xls->generateXML(date("Ymd").'company');
            exit;
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"company");
        $this->assign("info",$info1);
        $this->assign("money",$money);
        $this->assign("return_money",$return_money);
        $map['execl']="execl";
        $this->assign("query", http_build_query($map));
        $this->display();
    }

	public function outside(){
        if(!empty($_REQUEST['tuname'])){
            $search['mi.real_name'] =array('like',"%".$_REQUEST['tuname']."%");
            $map['tuname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['uname'])){
            $search['mmi.real_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['tuid'])){
            $search['m.id'] =intval($_REQUEST['tuid']);
            $map['tuid']=intval($_REQUEST['tuid']);
        }
        if(!empty($_REQUEST['uid'])){
            $search['mm.id'] =intval($_REQUEST['uid']);
            $map['uid']=intval($_REQUEST['uid']);
        }

        $search["op.end_time"]=array("neq",0);
        $search["op.return_money"]=array("neq",0);
        $field="op.invest_uid,m.user_name,op.borrow_id,b.borrow_name,b.second_verify_time,op.invest_money,b.borrow_duration_txt,mmi.real_name as recommend_real_name,op.uid as recommend_id,mm.user_phone as recommend_phone,op.return_money";
        $result=M("outside_profit op")
				->join("lzh_borrow_info b on op.borrow_id=b.id")
				->join("lzh_borrow_investor i on i.id=op.investor_id")
				->join("lzh_members m on m.id=op.invest_uid")
				->join("lzh_member_info mi on mi.uid=op.invest_uid")
				->join("lzh_members mm on mm.id=op.uid")
				->join("lzh_member_info mmi on mmi.uid=op.uid")
				->field($field)
				->where($search)
				->select();
				// echo M("outside_profit op")->getLastSql();die;
        $info=array();
        $money=0;
        $return_money=0;
        foreach($result as $key=>$val){
            $val['borrow_id']=borrowidlayout1($val['borrow_id']);
            $money+=intval($val['invest_money']);
            $val['second_verify_time']=date("Y-m-d", $val['second_verify_time']);
            $info[]=$val;
            $return_money+=$val['return_money'];
        }
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        //过滤
        $info1=array();
        foreach($info as $key=>$val){
            if(($key>=$min)&&($key<$max)){
                $info1[]=$val;
            }
        }
        //导出exel
        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Caiwut",0,1,'执行导出外部推荐用户投资分红！');//管理员操作日志
            $row=array();
            $row[0]=array('投资人id','投资人','投资标号','标号名称','标复审时间','标期限','投资金额','推荐人','推荐人id','推荐人手机号码','获取返利');
            $i=1;
            foreach($info as $v){
                $row[$i]['investor_uid'] = $v['invest_uid'];
                $row[$i]['user_name'] = $v['real_name'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration'] = $v['borrow_duration_txt'];
                $row[$i]['investor_capital'] = $v['invest_money'];
                $row[$i]['tuijian'] = $v['recommend_real_name'];
                $row[$i]['tuijian_id'] = $v['recommend_id'];
                $row[$i]['user_phone'] = $v['recommend_phone'];
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, date("Ymd").'outside');
            $xls->addArray($row);
            $xls->generateXML(date("Ymd").'outside');
            exit;
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"outside");
        $this->assign("info",$info1);
        $this->assign("money",$money);
        $this->assign("return_money",$return_money);
        $map['execl']="execl";
        $this->assign("query", http_build_query($map));
        $this->display();
    }

    public function invest_info(){
        $search=array();
        if(!empty($_REQUEST['uid'])){
            $where["bi.investor_uid"]=intval($_REQUEST['uid']);
            $search["uid"]=$_REQUEST['uid'];
        }
        if(!empty($_REQUEST['u_name'])){
            $where["m.user_name"]=array('like',"%".$_REQUEST['u_name']."%");
            $search["u_name"]=$_REQUEST['u_name'];
        }
        if(!empty($_REQUEST['real_name'])){
            $where["mi.real_name"]=array('like',"%".$_REQUEST['real_name']."%");
            $search["real_name"]=$_REQUEST['real_name'];
        }

        if(!empty($_REQUEST['start_reg_time'])){
            $search["start_reg_time"]=$_REQUEST['start_reg_time'];
            $r_start_time=strtotime(date("Y-m-d 00:00:00",strtotime($search["start_reg_time"])));
        }
        if(!empty($_REQUEST['end_reg_time'])){
            $search["end_reg_time"]=$_REQUEST['end_reg_time'];
            $r_end_time=strtotime(date("Y-m-d 23:59:59",strtotime($search["end_reg_time"])));
        }
        if(isset($r_end_time)&&isset($r_start_time)){
            $where["m.reg_time"]=array("between",array($r_start_time,$r_end_time));
        }else if(isset($r_start_time)){
            $where["m.reg_time"]=array("egt",$r_start_time);
        }else if(isset($r_end_time)){
            $where["m.reg_time"]=array("elt",$r_end_time);
        }

        if(!empty($_REQUEST['t_name'])){
            $where["mm.user_name"]=array('like',"%".$_REQUEST['t_name']."%");
            $search["t_name"]=$_REQUEST['t_name'];
        }

        if(!empty($_REQUEST['product_type_num'])){
            $type=intval($_REQUEST['product_type_num']) ;
            if($type==3){
                $where["b.product_type"]=array("elt",3);
            }else if($type==4){
                $where["b.product_type"]=4;
            }else if($type==6){
                $where["b.product_type"]=6;
            }else if($type==7){
                $where["b.product_type"]=7;
            }else if($type==5){
                $where["b.product_type"]=5;
            }else if($type==8){
                $where["b.product_type"]=8;
            }else if($type==10){
                $where["b.product_type"]=10;
            }
            $search["product_type_num"]=$_REQUEST['product_type_num'];
        }

        if(!empty($_REQUEST['bid'])){
            $search["bid"]=$_REQUEST['bid'];
            $bid=text($_REQUEST['bid']);

            if(strpos($bid,"ZJ")===0){
                $tmp=explode("ZJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_pledge')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];

            }
            else if(strpos($bid,"RJ")===0){
                $tmp=explode("RJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_finance')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }
            else if(strpos($bid,"XJ")===0){
                $tmp=explode("XJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_credit')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }
            else if(strpos($bid,"YJ")===0){
                $tmp=explode("YJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_optimal')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"FQG")===0){
                $tmp=explode("FQG",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_installment')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"BJ")===8){
                $tmp=explode("BJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_guarantee')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"ZJB")===0){
                $tmp=explode("ZJB",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_assets')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else{
                $where["b.id"]=$bid;
            }
        }
        //投资时间
        if(!empty($_REQUEST['start_add_time'])){
            $search["start_add_time"]=$_REQUEST['start_add_time'];
            $s_start_time=strtotime(date("Y-m-d 00:00:00",strtotime($search["start_add_time"])));
        }
        if(!empty($_REQUEST['end_add_time'])){
            $search["end_add_time"]=$_REQUEST['end_add_time'];
            $s_end_time=strtotime(date("Y-m-d 23:59:59",strtotime($search["end_add_time"])));
        }
        if(isset($s_end_time)&&isset($s_start_time)){
            $where["bi.add_time"]=array("between",array($s_start_time,$s_end_time));
        }else if(isset($s_start_time)){
            $where["bi.add_time"]=array("egt",$s_start_time);
        }else if(isset($s_end_time)){
            $where["bi.add_time"]=array("elt",$s_end_time);
        }
        //



        $where["b.borrow_status"]=array("egt",6);
        $count=M("borrow_investor bi")->join("lzh_borrow_info b on b.id=bi.borrow_id")
            ->join("lzh_members m on m.id=bi.investor_uid")->join("lzh_members mm on mm.id=m.recommend_id")->join("lzh_member_info mi on mi.uid=m.id")->where($where)->count();

        import("ORG.Util.PageFilter");
        $p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        $limit = "{$p->firstRow},{$p->listRows}";
        if($_REQUEST['execl']=="execl"){
            $limit =0;
        }
       $field="m.id as uid,m.user_name,mi.real_name,from_unixtime(m.reg_time) as reg_time,mm.user_name as t_user_name,
             m.from,m.argument1,b.product_type,b.id,bi.investor_capital,bi.investor_interest,from_unixtime(bi.add_time) as add_time,b.borrow_duration_txt,b.borrow_duration,b.borrow_status
            ,b.repayment_type,b.product_type";
        $list=M("borrow_investor bi ")->join("lzh_borrow_info b on b.id=bi.borrow_id")
            ->join("lzh_members m on m.id=bi.investor_uid")->join("lzh_members mm on mm.id=m.recommend_id")->join("lzh_member_info mi on mi.uid=m.id")
            ->where($where)->field($field)->limit($limit)->select();

       foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
        foreach($list as $key=>$val){
            if($val["product_type"]<=3)
                $list[$key]["product_type"]="质金链";
            else if($val["product_type"]==4){
                $list[$key]["product_type"]="融金链";
            }else if($val["product_type"]==6){
                $list[$key]["product_type"]="信金链";
            }else if($val["product_type"]==7){
                $list[$key]["product_type"]="优金链";
            }else if($val["product_type"]==5){
                $list[$key]["product_type"]="分期购";
            }else if($val["product_type"]==8){
                $list[$key]["product_type"]="保金链";
            }else if($val["product_type"]==10){
                $list[$key]["product_type"]="质金链(保)";
            }
        }

        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Caiwut",0,1,'导出投资人数据！');//管理员操作日志
            $row=array();
            $row[0]=array('用户ID','用户类型','用户名','真实姓名','注册时间','推荐人','来源渠道','投资类型','投资标号','投资金额','所得利息','投资时间','投资周期');
            $i=1;
            foreach($list as $v){
                $row[$i]['uid'] = $v['uid'];
                $row[$i]["type"]="普通用户";
                $row[$i]["user_name"]=$v["user_name"];
                $row[$i]["real_name"]=$v["real_name"];
                $row[$i]["reg_time"]=$v["reg_time"];
                $row[$i]["t_user_name"]=$v["t_user_name"];
                $row[$i]["from"]="";
                $row[$i]["product_type"]=$v["product_type"];
                $row[$i]["bid"]=$v["bid"];
                $row[$i]["investor_capital"]=$v["investor_capital"]."元";
                $row[$i]["investor_interest"]=$v["investor_interest"]."元";
                $row[$i]["add_time"]=$v["add_time"];
                $row[$i]["borrow_duration_txt"]=$v["borrow_duration_txt"];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, date("Ymd").'invest_info');
            $xls->addArray($row);
            $xls->generateXML(date("Ymd").'invest_info');
            exit;
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"invest_info");
        $this->assign("info",$list);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    public function  invest_member_info(){
        $search=array();
        if(!empty($_REQUEST['uid'])){
            $where["b.borrow_uid"]=intval($_REQUEST['uid']);
            $search["uid"]=$_REQUEST['uid'];
        }
        if(!empty($_REQUEST['u_name'])){
            $where["m.user_name"]=array('like',"%".$_REQUEST['u_name']."%");
            $search["u_name"]=$_REQUEST['u_name'];
        }
        if(!empty($_REQUEST['real_name'])){
            $where["mi.real_name"]=array('like',"%".$_REQUEST['real_name']."%");
            $search["real_name"]=$_REQUEST['real_name'];
        }

        if(!empty($_REQUEST['start_reg_time'])){
            $search["start_reg_time"]=$_REQUEST['start_reg_time'];
            $r_start_time=strtotime(date("Y-m-d 00:00:00",strtotime($search["start_reg_time"])));
        }
        if(!empty($_REQUEST['end_reg_time'])){
            $search["end_reg_time"]=$_REQUEST['end_reg_time'];
            $r_end_time=strtotime(date("Y-m-d 23:59:59",strtotime($search["end_reg_time"])));
        }
        if(isset($r_end_time)&&isset($r_start_time)){
            $where["m.reg_time"]=array("between",array($r_start_time,$r_end_time));
        }else if(isset($r_start_time)){
            $where["m.reg_time"]=array("egt",$r_start_time);
        }else if(isset($r_end_time)){
            $where["m.reg_time"]=array("elt",$r_end_time);
        }

        if(!empty($_REQUEST['t_name'])){
            $where["mm.user_name"]=array('like',"%".$_REQUEST['t_name']."%");
            $search["t_name"]=$_REQUEST['t_name'];
        }

        if(!empty($_REQUEST['product_type_num'])){
            $type=intval($_REQUEST['product_type_num']) ;
            if($type==3){
                $where["b.product_type"]=array("elt",3);
            }else if($type==4){
                $where["b.product_type"]=4;
            }else if($type==6){
                $where["b.product_type"]=6;
            }else if($type==7){
                $where["b.product_type"]=7;
            }else if($type==5){
                $where["b.product_type"]=5;
            }else if($type==8){
                $where["b.product_type"]=8;
            }else if($type==10){
                $where["b.product_type"]=10;
            }
            $search["product_type_num"]=$_REQUEST['product_type_num'];
        }

        if(!empty($_REQUEST['bid'])){
            $search["bid"]=$_REQUEST['bid'];
            $bid=text($_REQUEST['bid']);
            if(strpos($bid,"ZJ")===0){
                $tmp=explode("ZJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $newgrade = C('RENUMBER_BORROW.new_grade');
                if($where1["id"]<=$newgrade){
                    $where["b.id"]= $where1["id"];
                }else{
                    $bid_info = M('borrow_pledge')->where($where1)->find();
                    $where["b.id"]=$bid_info['borrow_id'];
                }

            }
            else if(strpos($bid,"RJ")===0){
                $tmp=explode("RJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_finance')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }
            else if(strpos($bid,"XJ")===0){
                $tmp=explode("XJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_credit')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }
            else if(strpos($bid,"YJ")===0){
                $tmp=explode("YJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_optimal')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"FQG")===0){
                $tmp=explode("FQG",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_installment')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"BJ")===8){
                $tmp=explode("BJ",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_guarantee')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else if(strpos($bid,"ZJB")===0){
                $tmp=explode("ZJB",$bid);
                $where1["id"]=intval($tmp[1]);
                $bid_info = M('borrow_assets')->where($where1)->find();
                $where["b.id"]=$bid_info['borrow_id'];
            }else{
                $where["b.id"]=$bid;
            }
        }
        //投资时间
        if(!empty($_REQUEST['start_add_time'])){
            $search["start_add_time"]=$_REQUEST['start_add_time'];
            $s_start_time=strtotime(date("Y-m-d 00:00:00",strtotime($search["start_add_time"])));
        }
        if(!empty($_REQUEST['end_add_time'])){
            $search["end_add_time"]=$_REQUEST['end_add_time'];
            $s_end_time=strtotime(date("Y-m-d 23:59:59",strtotime($search["end_add_time"])));
        }
        if(isset($s_end_time)&&isset($s_start_time)){
            $where["b.second_verify_time"]=array("between",array($s_start_time,$s_end_time));
        }else if(isset($s_start_time)){
            $where["b.second_verify_time"]=array("egt",$s_start_time);
        }else if(isset($s_end_time)){
            $where["b.second_verify_time"]=array("elt",$s_end_time);
        }
        //



        $where["b.borrow_status"]=array("egt",6);
        $count=M("borrow_info b")->join("lzh_borrow_info_additional ad on ad.bid=b.id")
            ->join("lzh_members m on m.id=b.borrow_uid")->join("lzh_members mm on mm.id=m.recommend_id")->join("lzh_member_info mi on mi.uid=m.id")
            ->where($where)->count();

        import("ORG.Util.PageFilter");
        $p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        $limit = "{$p->firstRow},{$p->listRows}";
        if($_REQUEST['execl']=="execl"){
            $limit =0;
        }
        $field="m.id as uid,m.user_name,mi.real_name,from_unixtime(m.reg_time) as reg_time,mm.user_name as t_user_name,
             m.from,m.argument1,b.product_type,b.id,b.borrow_duration_txt,b.borrow_duration,b.borrow_status,from_unixtime(b.deadline) as return_day
             ,b.repayment_type,from_unixtime(b.second_verify_time) as second_verify_time ,b.borrow_money,ad.colligate";

        $list=M("borrow_info b ")->join("lzh_borrow_info_additional ad on ad.bid=b.id")
            ->join("lzh_members m on m.id=b.borrow_uid")->join("lzh_members mm on mm.id=m.recommend_id")->join("lzh_member_info mi on mi.uid=m.id")
            ->where($where)->field($field)->limit($limit)->select();


        foreach($list as $key=>$val){
            unset($where);
            if($val["product_type"]<=3)
                $list[$key]["product_type"]="质金链";
            else if($val["product_type"]==4){
                $list[$key]["product_type"]="融金链";
            }else if($val["product_type"]==6){
                $list[$key]["product_type"]="信金链";
            }else if($val["product_type"]==7){
                $list[$key]["product_type"]="优金链";
            }else if($val["product_type"]==5){
                $list[$key]["product_type"]="分期购";
            }else if($val["product_type"]==8){
                $list[$key]["product_type"]="保金链";
            }else if($val["product_type"]==10){
                $list[$key]["product_type"]="质金链(保)";
            }
            $where["borrow_id"]=$val["id"];
            $field="repayment_time,substitute_time,(capital+interest) as money,expired_money";
            $info=M("investor_detail")->where($where)->field($field)->select();
            $list[$key]["none_money"]=0;
            $list[$key]["has_money"]=0;
            $list[$key]["expired_money"]=0;
            if(is_array($info)){
                foreach($info as $key1=>$val){
                    if($val["repayment_time"]==0&&$val["substitute_time"]==0){
                        $list[$key]["none_money"]+=$val["money"];
                    }else{
                        $list[$key]["has_money"]+=$val["money"];
                    }
                    $list[$key]["expired_money"]+=$val["expired_money"];
                }
            }
        }
        foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }

        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Caiwut",0,1,'导出投资人数据！');//管理员操作日志
            $row=array();
            $row[0]=array('用户ID','用户类型','用户名','真实姓名','注册时间','推荐人','来源渠道','标号','借款类型','借款金额','借款时间','到期日期','借款周期','已还金额','待还金额','罚息','垫付服务费','综合服务费');
            $i=1;
            foreach($list as $v){
                $row[$i]['uid'] = $v['uid'];
                $row[$i]["type"]="普通用户";
                $row[$i]["user_name"]=$v["user_name"];
                $row[$i]["real_name"]=$v["real_name"];
                $row[$i]["reg_time"]=$v["reg_time"];
                $row[$i]["t_user_name"]=$v["t_user_name"];
                $row[$i]["from"]="";
                $row[$i]["bid"]=$v["bid"];
                $row[$i]["product_type"]=$v["product_type"];
                $row[$i]["borrow_money"]=$v["borrow_money"];
                $row[$i]["second_verify_time"]=$v["second_verify_time"];
                $row[$i]["return_day"]=$v["return_day"];
                $row[$i]["borrow_duration_txt"]=$v["borrow_duration_txt"];
                $row[$i]["has_money"]=$v["has_money"];
                $row[$i]["none_money"]=$v["none_money"];
                $row[$i]["expired_money"]=$v["expired_money"];
                $row[$i]["other"]=0;
                $row[$i]["colligate"]=$v["colligate"];

                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, date("Ymd").'invest_member_info');
            $xls->addArray($row);
            $xls->generateXML(date("Ymd").'invest_member_info');
            exit;
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"invest_member_info");
        $this->assign("info",$list);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    public function showusermoney(){
	$userinfo = M("members")->where("id = {$_POST['uid']}")->field('user_regtype,id')->find();
	if($userinfo['user_regtype'] == 1){
		$this->ajaxReturn(querysaving($userinfo['id']));
	}elseif($userinfo['user_regtype'] == 2){
		$this->ajaxReturn(querybalance($userinfo['id']));
	}else{
		$this->ajaxReturn('0.00');
	}

}

	public function zhaiquanfee(){
		if($_REQUEST['pay_status']>-1){
			$map['d.pay_fee'] = intval($_REQUEST['pay_status']);
			$search['pay_status'] = $map['d.pay_fee'];
		}else{
			$search['pay_status'] = $_REQUEST['pay_status'];
		}
		if(!empty($_REQUEST['uname'])){
			$map['m.real_name'] = trim($_REQUEST['uname']);
			$search['uname'] = $map['m.real_name'];
		}
		if(!empty($_REQUEST['borrow_id'])){
			$map['d.id'] = trim($_REQUEST['borrow_id']);
			$search['borrow_id'] = $map['d.id'];
		}

		$map["d.borrow_status"] = array("in",'6,7');

		if(!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])){

			$start_time = strtotime($_REQUEST['start_time']." 00:00:00");
			$end_time = strtotime($_REQUEST['end_time']." 23:59:59");
			$map['d.second_verify_time'] = array("between","{$start_time},{$end_time}");
			$search['start_time'] = $_REQUEST['start_time'];
			$search['end_time'] = $_REQUEST['end_time'];
		}

		$field="d.id,m.real_name,d.borrow_name,d.totalmoney,d.borrow_money,d.second_verify_time,d.colligate_fee,d.pay_fee";
		import("ORG.Util.PageFilter");

		$count = M("debt_borrow_info d")->join("lzh_member_info m on m.uid = d.borrow_uid")->where($map)->count("d.id");
		$p = new PageFilter($count,$search,C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";
		if($_REQUEST['execl']=="execl"){
			$limit =0;
		}
		$list = M("debt_borrow_info d")->field($field)->join("lzh_member_info m on m.uid = d.borrow_uid")->where($map)->limit($limit)->order(" d.second_verify_time DESC")->select();
        //导出exel
		if($_REQUEST['execl']=="execl"){
			import("ORG.Io.Excel");
			alogs("Caiwut",0,1,'执行了所有债权手续费对账！');//管理员操作日志
			$row=array();
			$row[0]=array('标号','转让人','项目名称','债权价值','转让价格','转让成功时间','手续费（元）','支付状态');
			$i=1;
			foreach($list as $v){
				$row[$i]['borrow_id'] = 'ZQ'.$v['id'];
				$row[$i]['uname'] = $v['real_name'];
				$row[$i]['borrow_name'] = $v['borrow_name'];
				$row[$i]['totalmoney'] = $v['totalmoney'];
				$row[$i]['borrow_money'] = $v['borrow_money'];
				$row[$i]['colligate_fee'] = $v['colligate_fee'];
				$row[$i]['second_verify_time'] =isset($v['second_verify_time']) ? date("Y-m-d H:i:s",$v['second_verify_time']) : '-' ;
				if($v['pay_fee']==0){
					$row[$i]['pay_fee'] = "未支付";
				}else if($v['pay_fee']==1){
					$row[$i]['pay_fee'] ="已支付";
				}
				$i++;
			}

			$xls = new Excel_XML('UTF-8', false, 'zhaiquanfee');
			$xls->addArray($row);
			$xls->generateXML("feelistcard");
			exit;
		}
		$this->assign("total",$total);
		$this->assign("list",$list);
		$this->assign('search',$search);
		$search['execl']="execl";
		$this->assign("query", http_build_query($search));
		$this->assign('xaction',"feemoney");
		$this->assign("pagebar", $page);
		$this->display();
	}
}


?>
