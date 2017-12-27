<?php

/**
 * 投标审核
 */
class BorrowAction extends ACommonAction
{
	/**
	 * 初审列表
	 */
    public function waitverify()
    {
		$map=array();
		$map['b.borrow_status'] = 0;
		$map['b.product_type']= array('neq','5');
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
            }
            $search['protype']= $_REQUEST['protype'];
        }
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.updata,b.borrow_money,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,b.product_type,b.danbao,m.user_phone,m.user_name,m.id mid,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }

	/**
	 * 复审列表
	 */
    public function waitverify2()
    {
		$map=array();
		$map['b.borrow_status'] = 4;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
                $map['b.product_type']=array('eq','');
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
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.full_time,b.product_type,b.danbao,m.user_phone,m.user_name,m.id mid,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$fee = M('allwood_ljs')->where("borrow_id = ");
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
            if($v['product_type'] == 5){
            	$fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
            	$list[$k]['borrow_fee'] = $fee['fee'];
        	}
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function waitmoney()
    {
		$map=array();
		$map['b.borrow_status'] = 2;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,b.product_type,b.danbao,m.user_phone,m.user_name,m.id mid,b.is_tuijian,b.has_borrow,b.money_collect';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
            if($v['product_type'] == 5){
                $fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
                $list[$k]['borrow_fee'] = $fee['fee'];    
            }else{
                $list[$k]['borrow_fee'] = $v['borrow_fee'];    
            }
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function repaymenting()
    {
        $members = M('members');
        
		$map['b.borrow_status'] = 6;//还款中
		
		if(!empty($_REQUEST['uname']) && !$_REQUEST['uid'] || $_REQUEST['uname'] != $_REQUEST['olduname']){
		    $uid = $members->getFieldByUserName(text($_REQUEST['uname']), 'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype'] == 1){
                $check['b.product_type'] = ['in','1,2,3'];
                $check['b.id'] = ['lt', $renumber];
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype'] == 2){
                $map['b.product_type'] = ['eq','4'];
                $map['b.id'] = ['egt', $renumber];
            }else if($_REQUEST['protype'] == 3){
                $map['b.product_type'] = ['eq', '6'];
                $map['b.id']= ['egt', $renumber];
            }else if($_REQUEST['protype'] == 4){
                $map['b.product_type'] = ['eq','7'];
                $map['b.id'] = ['egt', $renumber];
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
        if (!empty($_GET['b_type']) && !empty($_GET['bid'])) {
            $borrow_id = intval($_GET["bid"]);
            
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 7) {
                $map["db.id"] = $borrow_id;
            } elseif ($_GET["b_type"] == 8) {
                $bid = M('borrow_assets')->where("id=".$borrow_id)->field('borrow_id')->find();
            }
            $search['bid'] = intval($_GET['bid']);
            $search['b_type'] = intval($_GET['b_type']);
        }
		if(!empty($_REQUEST['uid']) && !isset($search['uname'])){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['realname'])){
		    $search['realname'] = $_REQUEST['realname'];
		    $map['mi.real_name'] = $_REQUEST['realname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = [$_REQUEST['bj'], $_REQUEST['money']];
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = ["between", $timespan];
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = ["gt", $xtime];
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = ["lt", $xtime];
			$search['end_time'] = $xtime;	
		}
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		$join1 = "{$this->pre}members m ON m.id = b.borrow_uid";
		$join2 = "{$this->pre}member_info mi ON m.id = mi.uid";
		
		$Lsql = 0;
		if($_REQUEST['execl'] != "execl"){
		    //分页处理
		    import("ORG.Util.Page");
		    $count = M('borrow_info b')->join($join1)->join($join2)->where($map)->count('b.id');
		    $p = new Page($count, C('ADMIN_PAGE_SIZE'));
		    $page = $p->show();
		    $Lsql = "{$p->firstRow},{$p->listRows}";
		}

		$field= 'm.id as mid,m.customer_name,b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.borrow_interest,b.repayment_money,b.second_verify_time,b.repayment_interest,b.product_type,b.danbao,b.borrow_fee,b.borrow_interest_rate,b.colligate_fee,b.repayment_type,b.deadline,m.user_name,mi.real_name,m.user_phone,b.is_tuijian,b.money_collect';
		$list = M('borrow_info b')->field($field)->join($join1)->join($join2)->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		
		if (!empty($list)) {
		    $investor_detail = M('investor_detail');
		    $allwood_ljs = M('allwood_ljs');
		    foreach ($list as $k => $v) {
		        $list[$k]['bid'] = borrowidlayout1($v['id']);
		        $vx = $investor_detail->field('deadline,sort_order')->where(" borrow_id={$v['id']} AND status in(4,7)")->order("deadline ASC")->find();
		        $list[$k]['repayment_time'] = $vx['deadline'];
		        $list[$k]['sort_order'] = $vx['sort_order'];
		        $list[$k]['auto'] = "auto";
		        $need = $investor_detail->field('sum(capital + interest) as need')->where(" borrow_id={$v['id']} AND deadline=$vx[deadline]")->find();
		        $list[$k]['need_money'] = $need['need'];
		        if($v['product_type'] == 5){
		            $list[$k]['borrow_fee'] = $allwood_ljs->where("borrow_id = {$v['id']}")->getField('fee');
		        }else{
		            $list[$k]['borrow_fee'] = $v['borrow_fee'];
		        }
		        $list[$k]['repayed_money'] = $v['repayment_money'] + $v['repayment_interest'];
		        $list[$k]['borrow_duration'] = $v['borrow_duration'].($v['repayment_type_num'] == 1 ? '天' : '个月');
		        $list[$k]['second_verify_time'] = date("Y-m-d", $v['second_verify_time']);
		        $list[$k]['repayment_time'] = $list[$k]['repayment_time'] > 0 ? date("Y-m-d", $list[$k]['repayment_time']) : '-';
		    }
		}
        
		if($_REQUEST['execl'] == "execl"){
		    import("ORG.Io.Excel");
		    
		    $row = [];
		    $row[0] = ['标号','用户名','真实姓名','手机号','标题','借款金额','已还金额','借款期限','借款手续费','借款利率','综合服务费利率','还款方式','复审时间','最近还款时间'];
		    
		    if (!empty($list)) {
		        foreach ($list as $k => $v) {
		            $row[$k + 1]['bid'] = $v['bid'];
		            $row[$k + 1]['user_name'] = $v['user_name'];
		            $row[$k + 1]['real_name'] = $v['real_name'];
		            $row[$k + 1]['user_phone'] = $v['user_phone'];
		            $row[$k + 1]['borrow_name'] = $v['borrow_name'];
		            $row[$k + 1]['borrow_money'] = $v['borrow_money'];
		            $row[$k + 1]['repayed_money'] = $v['repayed_money'];
		            $row[$k + 1]['borrow_duration'] = $v['borrow_duration'];
		            $row[$k + 1]['borrow_fee'] = $v['borrow_fee'];
		            $row[$k + 1]['borrow_interest_rate'] = $v['borrow_interest_rate'].'%';
		            $row[$k + 1]['colligate_fee'] = $v['colligate_fee'].'%';
		            $row[$k + 1]['repayment_type'] = $v['repayment_type'];
		            $row[$k + 1]['second_verify_time'] = $v['second_verify_time'];
		            $row[$k + 1]['repayment_time'] = $v['repayment_time'];
		        }
		    }
		    
		    $xls = new Excel_XML('UTF-8', false, 'datalist');
		    $xls->addArray($row);
		    $xls->generateXML(ACTION_NAME."list");
		    exit();
		}
        
        $this->assign("bj", ["gt" => '大于', "eq" => '等于', "lt" => '小于']);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("xaction", ACTION_NAME);
        $search['execl'] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    //计算还款总额
    public function sum(){
		$borrow_id = $_POST['borrow_id'];
		$sort_order = $_POST['sort_order'];
        return cal_repayment_money($borrow_id,$sort_order);
    }
	
    public function borrowbreak()
    {//暂时未处理
		$map['deadline'] = array("exp","<>0 AND deadline<".time()." AND `repayment_money`<`borrow_money`");
		$field= 'id,borrow_name,borrow_uid,borrow_duration,borrow_type,borrow_money,borrow_fee,repayment_money,b.updata,borrow_interest_rate,repayment_type,deadline';
		$this->_list(D('Borrow'),$field,$map,'id','DESC');
        $this->display();
    }
	
	public function unfinish(){
		$map=array();
		$map['b.borrow_status'] = 3;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.deadline,b.product_type,b.danbao,m.user_phone,m.id mid,m.user_name,v.deal_user_2,v.deal_time_2,v.deal_info_2';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
        $this->display();
	}
	
    public function done()
    {
        $map['b.borrow_status'] = ["in", "7,9"];
        $members = M('members');
		if(!empty($_REQUEST['uname']) && !$_REQUEST['uid'] || $_REQUEST['uname'] != $_REQUEST['olduname']){
		    $uid = $members->getFieldByUserName(text($_REQUEST['uname']), 'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		$renumber = C('RENUMBER_BORROW.new_grade');
		if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype'] == 1){
                $check['b.product_type']= ['in', '1,2,3'];
                $check['b.id'] = ['lt', $renumber];
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype'] == 2){
                $map['b.product_type'] = ['eq', '4'];
                $map['b.id'] = ['egt', $renumber];
            }else if($_REQUEST['protype'] == 3){
                $map['b.product_type'] = ['eq', '6'];
                $map['b.id'] = ['egt', $renumber];
            }else if($_REQUEST['protype'] == 4){
                $map['b.product_type'] = ['eq', '7'];
                $map['b.id'] = ['egt', $renumber];
            }else if($_REQUEST['protype']==5){
                $map['b.product_type']=array('eq','8');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $map['b.product_type']=array('eq','10');
                $map['b.id']=array('egt',$renumber);
            }
            $search['protype'] = $_REQUEST['protype'];
        }
        if (!empty($_GET['b_type']) && !empty($_GET['bid'])) {
            $borrow_id = intval($_GET["bid"]);
            
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->field('borrow_id')->find();
                $map["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 7) {
                $map["b.id"] = $borrow_id;
            } elseif ($_GET["b_type"] == 8) {
                $bid = M('borrow_assets')->where("id=".$borrow_id)->field('borrow_id')->find();
            }
            $search['bid'] = intval($_GET['bid']);
            $search['b_type'] = intval($_GET['b_type']);
        }
		if( !empty($_REQUEST['uid']) && !isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['realname'])){
		    $search['realname'] = $_REQUEST['realname'];
		    $map['mi.real_name'] = $_REQUEST['realname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = ["between", $timespan];
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = ["gt", $xtime];
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = ["lt", $xtime];
			$search['end_time'] = $xtime;	
		}
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname, 'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		$join1 = "{$this->pre}members m ON m.id = b.borrow_uid";
		$join2 = "{$this->pre}member_info mi ON m.id = mi.uid";
		
		$Lsql = 0;
		if($_REQUEST['execl'] != "execl"){
		    //分页处理
		    import("ORG.Util.Page");
		    $count = M('borrow_info b')->join($join1)->join($join2)->where($map)->count('b.id');
		    $p = new Page($count, C('ADMIN_PAGE_SIZE'));
		    $page = $p->show();
		    $Lsql = "{$p->firstRow},{$p->listRows}";
		}

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.repayment_money,b.second_verify_time,b.deadline,b.product_type,b.danbao,m.id mid,m.user_name,m.user_phone,mi.real_name';
		$list = M('borrow_info b')->field($field)->join($join1)->join($join2)->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		if (!empty($list)) {
		    $allwood_ljs = M('allwood_ljs');
		    foreach($list as $k => $v){
		        $list[$k]['bid'] = borrowidlayout1($v['id']);
		        if($v['product_type'] == 5){
		            $v['borrow_fee'] = $allwood_ljs->where("borrow_id = {$v['id']}")->getField('fee');
		        }else{
		            $v['borrow_fee'] = $v['borrow_fee'];
		        }
		        $list[$k]['borrow_duration'] = $v['borrow_duration'].($v['repayment_type_num'] == 1 ? '天' : '个月');
		        $list[$k]['second_verify_time'] = date("Y-m-d H:i", $v['second_verify_time']);
		        $list[$k]['deadline'] = date("Y-m-d H:i", $v['deadline']);
		    }
		}
        
        if($_REQUEST['execl'] == "execl"){
            import("ORG.Io.Excel");
            
            $row[0] = ['ID','用户名','真实姓名','手机号','标题','借款金额','已还金额','还款方式','借款期限','借款手续费','复审时间','还款最终限期'];
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $row[$k + 1]['bid'] = $v['bid'];
                    $row[$k + 1]['user_name'] = $v['user_name'];
                    $row[$k + 1]['real_name'] = $v['real_name'];
                    $row[$k + 1]['user_phone'] = $v['user_phone'];
                    $row[$k + 1]['borrow_name'] = $v['borrow_name'];
                    $row[$k + 1]['borrow_money'] = $v['borrow_money'];
                    $row[$k + 1]['repayment_money'] = $v['repayment_money'];
                    $row[$k + 1]['repayment_type'] = $v['repayment_type'];
                    $row[$k + 1]['borrow_duration'] = $v['borrow_duration'];
                    $row[$k + 1]['borrow_fee'] = $v['borrow_fee'];
                    $row[$k + 1]['second_verify_time'] = $v['second_verify_time'];
                    $row[$k + 1]['deadline'] = $v['deadline'];
                }
            }
            
            $xls = new Excel_XML('UTF-8', false, 'datalist');
            $xls->addArray($row);
            $xls->generateXML(ACTION_NAME."list");
            exit();
        }
        
        $this->assign("bj", ["gt" => '大于', "eq" => '等于', "lt" => '小于']);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("xaction", ACTION_NAME);
        $search['execl'] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
	
    public function fail()
    {
		$map=array();
		$map['b.borrow_status'] = 1;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,b.product_type,b.danbao,m.user_phone,m.user_name,v.deal_user,v.deal_time,m.id mid,v.deal_info';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function fail2()
    {
		$map=array();
		$map['b.borrow_status'] = 5;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		//if(session('admin_is_kf')==1){
		//		$map['m.customer_id'] = session('admin_id');
		//}else{
			if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
				$map['m.customer_id'] = $_REQUEST['customer_id'];
				$search['customer_id'] = $map['m.customer_id'];	
				$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
			}
			
			if($_REQUEST['customer_name'] && !$search['customer_id']){
				$cusname = urldecode($_REQUEST['customer_name']);
				$kfid = M('ausers')->getFieldByUserName($cusname,'id');
				$map['m.customer_id'] = $kfid;
				$search['customer_name'] = $cusname;	
				$search['customer_id'] = $kfid;	
			}
		//}
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_status,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.updata,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,b.product_type,b.danbao,m.user_phone,m.user_name,m.id mid,v.deal_user_2,v.deal_time_2,v.deal_info_2';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->join("{$this->pre}borrow_verify v ON b.id=v.borrow_id")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
    public function _addFilter()
    {
		$typelist = get_type_leve_list('0','acategory');//分级栏目
		$this->assign('type_list',$typelist);
		
    }
    private  function  get_additional_info($id){
            $addition=D("borrow_info_additional");
            $info=$addition->get_additional_info($id);
            $map['id']=$id;
            $borrow_info=M("borrow_info")->field("borrow_duration_txt,borrow_uid")->where($map)->select();
            $vm = M("member_money")->where("uid =".$borrow_info[0]['borrow_uid'])->find();
            $this->assign('vm',$vm);
            $duration_list=explode("+",$borrow_info[0]['borrow_duration_txt']);
            if((count($duration_list)==2)||($info["second_rate"]!=0)){
                $this->assign("show_all",1);
                $this->assign("second_rate",$info["second_rate"]);
                $this->assign("second_time",$duration_list[1]);
                $this->assign("frist_time",$duration_list[0]);
                $this->assign("colligate",$info["colligate"]);
            }
    }
    private  function check_additional_info($type=1){
        $id=intval($_POST['id']);
        $where['bid']=$id;
        if($type==1){
            $danwei="天";
        }else{
            $danwei="个月";
        }
        if(isset($_POST["xh_date"])){ //只有提单+现货模式才需要显示以及修改这个部分
            $date['second_time']=text($_POST['xh_date']);
            $date['second_rate']=text($_POST['xh_lx']);
            $date["frist_time"]=text($_POST['td_date']);
            $date["frist_rate"]=text($_POST["borrow_interest_rate"]);

            $data1['borrow_duration_txt']=intval($date["frist_time"]).$danwei."+". intval($date['second_time']).$danwei;
        }else{
            $data1["borrow_duration_txt"]=$_POST['borrow_duration'].$danwei;
        }

        $map['id']=$id;
        M("borrow_info")->where($map)->save($data1);

        $date["colligate"]=getFloatValue($_POST['colligate'],2);
        M("borrow_info_additional")->where($where)->save($date);


    }
	// 招标中借款审核
    public function _editFilter($id)
    {
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$borrow_status = $Bconfig['BORROW_STATUS'];

	 	//$BType = $Bconfig['BORROW_TYPE'];
		switch(strtolower(session('listaction'))){
			case "waitverify":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("1","2")) ) continue;
					unset($borrow_status[$i]);
				}
                $this->get_additional_info($id);
                $danbao_list=D("Members_company")->getDanBaoList();
                $this->assign("danbao",$danbao_list);
			break;
			case "waitverify2":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("5","6")) ) continue;
					unset($borrow_status[$i]);
				}
                $this->get_additional_info($id);
                $danbao_list=D("Members_company")->getDanBaoList();
                $this->assign("danbao",$danbao_list);
			break;
			case "waitmoney":
				for($i=0;$i<=10;$i++){
					if(in_array($i,array("2","3")) ) continue;
					unset($borrow_status[$i]);
				}
                                $this->get_additional_info($id);
			break;
			case "fail":
				unset($borrow_status['3'],$borrow_status['4'],$borrow_status['5']);
			break;
		}
		$token = mt_rand( 100000,999999);
		session("token",$token);
		$this->assign("token",$token);
		///////////////////////////////////////////////////////////////////////////////////
		//$danbao = M('article_category')->field('id,type_name')->where("type_name='合作机构资质展示'")->select();
		
		//$sql = M('article')->field("id,title")->where("type_id =7")->select();//"select id,title from lzh_article where type_id =7";
		$danbao = M('article')->field("id,title")->where("type_id =7")->select();//M()->query($sql);
		$dblist = array();
		if(is_array($danbao)){
			foreach($danbao as $key => $v){
				$dblist[$v['id']]=$v['title'];
			}
		}
		$this->assign("danbao_list",$dblist);//新增担保标A+
		//////////////////////////////////////////////////////////////////////////////
		$this->assign('xact',session('listaction'));
		$btype = $Bconfig['REPAYMENT_TYPE'];
		$this->assign("vv",M("borrow_verify")->find($id));
		$this->assign('borrow_status',$borrow_status);
		$this->assign('type_list',$btype);
		$this->assign('borrow_type',$Bconfig['BORROW_TYPE']);
		$this->assign('product_type',$Bconfig['PRODUCT_TYPE']);
		//setBackUrl(session('listaction'));	
    }
	public function sRepayment(){
		$borrow_id = $_GET['id'];
		$binfo = M("borrow_info")->field("has_pay,total")->find($borrow_id);
		$from = $binfo['has_pay'] + 1;
		for($i=$from;$i<=$binfo['total'];$i++){
			$res = borrowRepayment($borrow_id,$i,2);
		}
		if($res===true){
			alogs("Repay",0,1,'网站代还成功！');//管理员操作日志
			$this->success("代还成功");
		}elseif(!empty($res)){
			$this->error($res);
		}else{
			alogs("Repay",0,0,'网站代还出错！');//管理员操作日志
			$this->error("代还出错，请重试");
		}
	}

	public function _doAddFilter($m){
		if(!empty($_FILES['imgfile']['name'])){
			$this->saveRule = date("YmdHis",time()).rand(0,1000);
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Article/' ;
			$this->thumbMaxWidth = C('ARTICLE_UPLOAD_W');
			$this->thumbMaxHeight = C('ARTICLE_UPLOAD_H');
			$info = $this->CUpload();
			$data['art_img'] = $info[0]['savepath'].$info[0]['savename'];
		}
		if($data['art_img']) $m->art_img=$data['art_img'];
		$m->art_time=time();
		if($_POST['is_remote']==1) $m->art_content = get_remote_img($m->art_content);
		return $m;
	}

	/**
	 * 初审提交
	 */
	public function doEditWaitverify(){
		//Token值判断
		if($_POST["token"] == "" || $_POST["token"] != $_SESSION["token"]){
			$this->error("提交出错，请重试",0);exit;
		}else{
			session("token",null);
		}
        $m = D(ucfirst($this->getActionName()));
        if((!isset($_POST['danbao']))||(intval($_POST['danbao'])==0)){//没有担保公司
            if(getFloatValue($_POST['vouch_money'],2)>0){
                $this->error("没有担保公司，设置了担保金额",0);exit;
            }
        }else{
            if(getFloatValue($_POST['vouch_money'],2)==0){
                $this->error("担保金额不能为0",0);exit;
            }else{
                $m->danbao=intval($_POST['danbao']);
                $m->vouch_money=getFloatValue($_POST['vouch_money'],2);
            }
        }
        //提单转现货
        if(isset($_POST['td_date'])){
            $td_date=intval($_POST['td_date']);
            $xh_date=intval($_POST['xh_date']);
            $borrow_duration=intval($_POST['borrow_duration']);
            if(($td_date+$xh_date)!=$borrow_duration){
                $this->error("提单+现货时间与总时间不符合");exit;
            }
        }

        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('borrow_uid,borrow_status,borrow_type,product_type,first_verify_time,password,updata,danbao,vouch_money,money_collect,can_auto,borrow_money')->find($m->id);

		$rate_lixt = explode("|",$this->glo['rate_lixi']);
		$borrow_duration = explode("|",$this->glo['borrow_duration']);
		$borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
		if(floatval($_POST['borrow_interest_rate'])>$rate_lixt[1] || floatval($_POST['borrow_interest_rate'])<$rate_lixt[0]){
			$this->error("提交的借款利率超出允许范围，请重试",0);exit;
		}
		if($m->repayment_type=='1'&&($m->borrow_duration>$borrow_duration_day[1] || $m->borrow_duration<$borrow_duration_day[0])){
			$this->error("提交的借款期限超出允许范围，请去网站设置处重新设置系统参数",0);exit;
		}
		if($m->repayment_type!='1'&&($m->borrow_duration>$borrow_duration[1] || $m->borrow_duration<$borrow_duration[0])){
			$this->error("提交的借款期限超出允许范围，请去网站设置处重新设置系统参数",0);exit;
		}
		
		////////////////////图片编辑///////////////////////
		if(!empty($_POST['swfimglist'])){
			foreach($_POST['swfimglist'] as $key=>$v){
				$row[$key]['img'] = substr($v,1);
				$row[$key]['info'] = $_POST['picinfo'][$key];
			}
			$m->updata=serialize($row);
		}
		////////////////////图片编辑///////////////////////

		if($vm['borrow_status']<>2 && $m->borrow_status==2){
		  //新标提醒
			//newTip($m->id);
			MTip('chk8',$vm['borrow_uid'],$m->id);
		  //自动投标
			if($m->borrow_type==1){
				memberLimitLog($vm['borrow_uid'],1,-($m->borrow_money),$info="{$m->id}号标初审通过");
			}elseif($m->borrow_type==2){
				memberLimitLog($vm['borrow_uid'],2,-($m->borrow_money),$info="{$m->id}号标初审通过");
			}
			$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
			$newbid=borrowidlayout1($m->id);
			SMStip("firstV",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$newbid));
		}
		//if($m->borrow_status==2) $m->collect_time = strtotime("+ {$m->collect_day} days");
		if($m->borrow_status==2){ 
			$m->collect_time = strtotime("+ {$m->collect_day} days");
			//$m->is_tuijian = 1;
		}
		$m->borrow_interest = getBorrowInterest($m->repayment_type,$m->borrow_money,$m->borrow_duration,$m->borrow_interest_rate);
        //保存当前数据对象
		if($m->borrow_status==2 || $m->borrow_status==1) $m->first_verify_time = time();
		else unset($m->first_verify_time);
		unset($m->borrow_uid);
		$bs = intval($_POST['borrow_status']);

        $repayment_type=$m->repayment_type;
        if ($result = $m->save()) { //保存成功
			if($bs==2 || $bs==1){
				$verify_info['borrow_id'] = intval($_POST['id']);
				$verify_info['deal_info'] = text($_POST['deal_info']);
				$verify_info['deal_user'] = $this->admin_id;
				$verify_info['deal_time'] = time();
				$verify_info['deal_status'] = $bs;
				if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
				else  M('borrow_verify')->add($verify_info);
			}
			if($vm['borrow_status']<>2 && $_POST['borrow_status']==2 && $vm['can_auto']==1 && empty($vm['password'])==true) {
				autoInvest(intval($_POST['id']));
			}
			//if($vm['borrow_status']<>2 && $_POST['borrow_status']==2)) autoInvest(intval($_POST['id']));
			alogs("doEditWait",$result,1,'初审操作成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            if($bs==2)
                 $this->check_additional_info($repayment_type);//只有发标成功的才需要修改扩展参数
            if($bs==1&&$vm['product_type']==6){
				$credit['uid'] = $vm['borrow_uid'];
        		M('member_money')->where($credit)->setInc('credit_limit',$vm['borrow_money']);
        	}
            $this->success(L('修改成功'));
        } else {
			alogs("doEditWait",$result,0,'初审操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}
	}

	/**
	 * 复审处理
	 */
	public function doEditWaitverify2(){
		//Token值判断
		if($_POST["token"] == "" || $_POST["token"] != $_SESSION["token"]){
			$this->error("提交出错，请退出重试",0);exit;
		}else{
			session("token",null);
		}
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('borrow_uid,borrow_money,borrow_type,product_type,borrow_status,first_verify_time,updata,danbao,vouch_money,borrow_fee,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time,money_collect')->find($m->id);
	    $info=D("borrow_info_additional")->get_additional_info($m->id);
            
         if($vm['borrow_type']==1){
             if($vm['borrow_money']<>$m->borrow_money ||
			 $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
			 $vm['borrow_duration']<>$m->borrow_duration ||
			 $vm['repayment_type']<>$m->repayment_type 
			  ){
				$this->error('复审中的借款不能再更改‘还款方式’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’,‘担保机构’');
				exit;
			}
        }else {
	        if($vm['borrow_money']<>$m->borrow_money ||
				 $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
				 $vm['borrow_duration']<>$m->borrow_duration ||
				 $vm['repayment_type']<>$m->repayment_type ||
				 $vm['danbao'] <> $m->danbao||
	             $vm['vouch_money']<>$m->vouch_money
			  ){
				$this->error('复审中的借款不能再更改‘还款方式’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’,‘担保机构’');
				exit;
			}
        }
        if((isset($_POST['colligate']))&&( $info['colligate']<>$_POST['colligate']) && $vm['product_type'] != 5){
            $this->error('复审中的借款不能再更改,担保服务费’');
            exit;
        }
		if($m->borrow_status<>5 && $m->borrow_status<>6){
			$this->error('已经满标的的借款只能改为复审通过或者复审未通过');
			exit;
		}

		////////////////////图片编辑///////////////////////
		if(!empty($_POST['swfimglist'])){
			foreach($_POST['swfimglist'] as $key=>$v){
				$row[$key]['img'] = substr($v,1);
				$row[$key]['info'] = $_POST['picinfo'][$key];
			}
			$m->updata=serialize($row);
		}
		////////////////////图片编辑///////////////////////
		//复审投标检测
		//$capital_sum1=M('investor_detail')->where("borrow_id={$m->id}")->sum('capital');
		$capital_sum2=M('borrow_investor')->where("borrow_id={$m->id}")->sum('investor_capital');
		if(($vm['borrow_money']!=$capital_sum2)){
			$this->error('投标金额不统一，请确认！');
			exit;
		}
		if($m->borrow_status==6){//复审通过
			
			$appid = borrowApproved($m->id);
			if(!$appid){ $this->error("复审失败");exit;}
			/****************************************复审start*************************************************************/
			$list = M("sinalog")->where("type=3 AND borrow_id = {$m->id} AND status = 2")->select();
			$a=0;
			$b=0;
			$c=0;
			$trade_list = null;
			$newbid=borrowidlayout1($m->id);
			foreach ($list as $i) {
				if($i['coupons'] != null && $i['coupons'] != ""){
					$coupons_money = M('coupons c')->join("lzh_members m on m.user_phone = c.user_phone")->where("c.serial_number='".$i["coupons"]."' AND m.id = ".$i['uid'])->find();
					$i["money"] = $i["money"]-$coupons_money['money'];
				}
				if($a<100){
					if($b === 0){
						$trade_list[$c] = date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第".$newbid."号标投资成功";
						$b++;
					}else{
						$trade_list[$c] .= '$'.date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第".$newbid."号标投资成功";
					}
					$a++;
					if($a===100){$a = 0;$b=0;$c++;}
				}
			}
			foreach ($trade_list as $list) {
				sinafinishpretrade($list);
			}

			//更新时间,复审通过后执行
        	D('borrow_info_additional')->update_review(intval($_POST['id']));
        	//复审通过后，判断是否需要提前支付综合服务费
	        $need=D("borrow_info_additional")->is_pay_frist(intval($_POST['id']));
	        if($need && $vm['product_type'] != 5){
	            $map['bid']=intval($_POST['id']);
	            $map['uid']=$vm['borrow_uid'];
	            $map['danbao_id']= $vm['danbao'];
	            $map["danbao"] = $vm['vouch_money'];
	            $map["fee"] = D("borrow_info_additional")->pay_first_money(intval($_POST['id']));
	            D("Confirm")->addConfirmList($map);
	        }

			if($vm['product_type'] != 5){
				$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
				SMStip("approve",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$newbid));
			}
			
		}elseif($m->borrow_status==5){//复审未通过
			$appid = borrowRefuse($m->id,3);
			if(!$appid) $this->error("复审失败");
		}

        //保存当前数据对象
		$m->second_verify_time = time();
		unset($m->borrow_uid);
		$bs = intval($_POST['borrow_status']);
        if ($result = $m->save()) { //保存成功
				$verify_info['borrow_id'] = intval($_POST['id']);
				$verify_info['deal_info_2'] = text($_POST['deal_info_2']);
				$verify_info['deal_user_2'] = $this->admin_id;
				$verify_info['deal_time_2'] = time();
				$verify_info['deal_status_2'] = $bs;
				if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
				else  M('borrow_verify')->add($verify_info);


				//全木行冻结资金
		        $allwood_config = C('ALLWOOD_ORDER');
				$order = M("allwood_ljs")->where("borrow_id = ".intval($_POST['id']))->find();
				if($order){
					$order_no = $order["allwood_orderno"];
					$datas["order_sn"] = $order_no;
					$datas["collect_money"] =  $vm['borrow_money'];
					$all_result = curl_post($allwood_config['DONG_URL'],$datas);
					file_put_contents('javalog.txt', var_export($all_result,true), FILE_APPEND);
				}

			alogs("borrowApproved",$result,1,'复审操作成功！');//管理员操作日志
			if($bs==5&&$vm['product_type']==6){
				$credit['uid'] = $vm['borrow_uid'];
        		M('member_money')->where($credit)->setInc('credit_limit',$vm['borrow_money']);
        	}
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("borrowApproved",$result,0,'复审操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}	
	}

	public function doEditWaitmoney(){
		//Token值判断
		if($_POST["token"] == "" || $_POST["token"] != $_SESSION["token"]){
			$this->error("提交出错，请重试",0);exit;
		}else{
			session("token",null);
		}
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		
		$vm = M('borrow_info')->field('borrow_uid,borrow_type,borrow_money,first_verify_time,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time,borrow_fee,money_collect')->find($m->id);
		
		if($vm['borrow_money']<>$m->borrow_money ||
			 $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
			 $vm['borrow_duration']<>$m->borrow_duration ||
			 //$vm['borrow_type']<>$m->borrow_type ||
			 $vm['repayment_type']<>$m->repayment_type ||
			 $vm['borrow_fee'] <> $m->borrow_fee
		  ){
			$this->error('招标中的借款不能再更改‘还款方式’，‘借款种类’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’');
			exit;
		}

		//招标中的借款流标
		if($m->borrow_status==3){
			alogs("borrowRefuse",0,1,'流标操作成功！');//管理员操作日志
			//流标返回
			$appid = borrowRefuse($m->id,2);
			if(!$appid) {
				alogs("borrowRefuse",0,0,'流标操作失败！');//管理员操作日志
				$this->error("流标失败");
			}
			MTip('chk11',$vm['borrow_uid'],$m->id);
			$m->second_verify_time = time();
			//流标操作相当于复审
			$verify_info['borrow_id'] = $m->id;
			$verify_info['deal_info_2'] = text($_POST['deal_info_2']);
			$verify_info['deal_user_2'] = $this->admin_id;
			$verify_info['deal_time_2'] = time();
			$verify_info['deal_status_2'] = $m->borrow_status;
			if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
			else  M('borrow_verify')->add($verify_info);
			
			$vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
			SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$m->id));

		}else{
			if($vm['collect_day'] < $m->collect_day){
				$spanday = $m->collect_day-$vm['collect_day'];
				$m->collect_time = strtotime("+ {$spanday} day",$vm['collect_time']);
			}
			unset($m->second_verify_time);
		}
		
        //保存当前数据对象
 		unset($m->borrow_uid);
		////////////////////图片编辑///////////////////////
		foreach($_POST['swfimglist'] as $key=>$v){
			$row[$key]['img'] = substr($v,1);
			$row[$key]['info'] = $_POST['picinfo'][$key];
		}
		$m->updata=serialize($row);

		////////////////////图片编辑///////////////////////
       if ($result = $m->save()) { //保存成功
	   		//$this->assign("waitSecond",10000);
			alogs("borrowing",0,1,'招标中的借款操作修改成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("borrowing",0,0,'招标中的借款操作修改失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
		}
	}
	

	public function doEditFail(){
		//Token值判断
		if($_POST["token"] == "" || $_POST["token"] != $_SESSION["token"]){
			$this->error("提交出错，请重试",0);exit;
		}else{
			session("token",null);
		}
        $m = D(ucfirst($this->getActionName()));
        if (false === $m->create()) {
            $this->error($m->getError());
        }
		$vm = M('borrow_info')->field('borrow_uid,borrow_status')->find($m->id);
		if($vm['borrow_status']==2 && $m->borrow_status<>2){
			$this->error('已通过审核的借款不能改为别的状态');
			exit;
		}
		
		foreach($_POST['updata_name'] as $key=>$v){
			$updata[$key]['name'] = $v;
			$updata[$key]['time'] = $_POST['updata_time'][$key];
		}
		$m->borrow_interest = getBorrowInterest($m->repayment_type,$m->borrow_money,$m->borrow_duration,$m->borrow_interest_rate);
		$m->updata = serialize($updata);
		$m->collect_time = strtotime($m->collect_time);
        //保存当前数据对象
        if ($result = $m->save()) { //保存成功
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
            //失败提示
            $this->error(L('修改失败'));
		}	
	}
	
	
	public function _AfterDoEdit(){
		switch(strtolower(session('listaction'))){
			case "waitverify":
				$v = M('borrow_info')->field('borrow_uid,borrow_status,deal_time')->find(intval($_POST['id']));
				if(empty($v['deal_time'])){
					$newid = M('members')->where("id={$v['borrow_uid']}")->setInc('credit_use',floatval($_POST['borrow_money']));
					if($newid) M('borrow_info')->where("id={$v['borrow_uid']}")->setField('deal_time',time());
				}
				//$vss = M("members")->field("user_phone,user_name")->where("id = {$v['borrow_uid']}")->find();
				//SMStip("firstV",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],intval($_POST['id'])));
				//$this->assign("waitSecond",1000);
				//Notice();
			break;
		}
	}
	
	public function _listFilter($list){
		session('listaction', ACTION_NAME);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
	 	$listType = $Bconfig['REPAYMENT_TYPE'];
	 	$BType = $Bconfig['BORROW_TYPE'];
		$row=array();
		$aUser = get_admin_name();
		foreach($list as $key => $v){
			$v['repayment_type_num'] = $v['repayment_type'];
			$v['repayment_type'] = $listType[$v['repayment_type']];
			$v['borrow_type'] = $BType[$v['borrow_type']];
			if($v['deadline']) $v['overdue'] = getLeftTime($v['deadline']) * (-1);
			if($v['borrow_status']==1 || $v['borrow_status']==3 || $v['borrow_status']==5){
				$v['deal_uname_2'] = $aUser[$v['deal_user_2']];
				$v['deal_uname'] = $aUser[$v['deal_user']];
			}

			$v['last_money'] = $v['borrow_money'] - $v['has_borrow'];//新增剩余金额
			if($v['is_auto'] == 1){
				$v['is_auto'] = "自动投标";
			}else{
				$v['is_auto'] = "手动投标";
			}
			
			$row[$key] = $v;
		}
		return $row;
	}
	
	 public function doweek()
    {
		$map=array();
		$map['b.borrow_status'] = 6;
		$week_1 = array(strtotime(date("Y-m-d")." 00:00:00"),strtotime("+6 day",strtotime(date("Y-m-d",time())." 23:59:59")));//一周内
		$map['d.deadline'] = array("between", $week_1);
		
		if (!empty($_GET['b_type']) && !empty($_GET['bid'])) {
		    $borrow_id = intval($_GET["bid"]);
		    
		    if ($_GET["b_type"] == 1) {
		        $bid = M('borrow_pledge')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 2) {
		        $bid = M('borrow_optimal')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 3) {
		        $bid = M('borrow_finance')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 4) {
		        $bid = M('borrow_credit')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 5) {
		        $bid = M('borrow_guarantee')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 6) {
		        $bid = M('borrow_installment')->where("id=".$borrow_id)->field('borrow_id')->find();
		        $map["b.id"] = $bid['borrow_id'];
		    } elseif ($_GET["b_type"] == 7) {
		        $map["db.id"] = $borrow_id;
		    }
		    $search['bid'] = intval($_GET['bid']);
		    $search['b_type'] = intval($_GET['b_type']);
		}
		if(!empty($_REQUEST['realname'])){
		    $search['realname'] = $_REQUEST['realname'];
		    $map['mi.real_name'] = $_REQUEST['realname'];
		}
		
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}

		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		$map["d.repayment_time"] = $map["d.substitute_time"] = 0;
		
		$join1 = "{$this->pre}investor_detail as d on d.borrow_id = b.id";
		$join2 = "{$this->pre}members m ON m.id = b.borrow_uid";
		$join3 = "{$this->pre}member_info mi ON m.id = mi.uid";
		
		$field = 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,
                d.deadline,b.product_type,b.danbao,m.user_name,m.id as mid,mi.real_name,m.user_phone';
		
		$Lsql = 0;
		if($_REQUEST['execl'] != "execl"){
		    //分页处理
		    import("ORG.Util.Page");
		    $data = M('borrow_info b')->field($field)->join($join1)->join($join2)->join($join3)->where($map)->group("d.borrow_id")->select();
		    $p = new Page(count($data), C('ADMIN_PAGE_SIZE'));
		    $page = $p->show();
		    $Lsql = "{$p->firstRow},{$p->listRows}";
		}
		
		$list = M('borrow_info b')->field($field)->join($join1)->join($join2)->join($join3)->where($map)->limit($Lsql)->group("d.borrow_id")->order("b.id DESC")->select();
		$list = $this->_listFilter($list);

		if (!empty($list)) {
		    foreach ($list as $k => $v) {
		        $list[$k]['bid'] = borrowidlayout1($v['id']);
		        $list[$k]['borrow_duration'] = $v['borrow_duration'].($v['repayment_type_num'] == 1 ? '天' : '个月');
		        $list[$k]['deadline'] = date('Y-m-d H:i', $v['deadline']);
		    }
		}
		
		if($_REQUEST['execl'] == "execl"){
		    import("ORG.Io.Excel");
		    
		    $row = [];
		    $row[0] = ['标号','用户名','真实姓名','手机号','标题','借款金额','已还金额','还款方式','借款期限','借款手续费','还款最终限期'];
		    $i = 1;
		    if (!empty($list)) {
		        foreach ($list as $v) {
		            $row[$i]['bid'] = $v['bid'];
		            $row[$i]['user_name'] = $v['user_name'];
		            $row[$i]['real_name'] = $v['real_name'];
		            $row[$i]['user_phone'] = $v['user_phone'];
		            $row[$i]['borrow_name'] = $v['borrow_name'];
		            $row[$i]['borrow_money'] = $v['borrow_money'];
		            $row[$i]['repayment_money'] = $v['repayment_money'];
		            $row[$i]['repayment_type'] = $v['repayment_type'];
		            $row[$i]['borrow_duration'] = $v['borrow_duration'];
		            $row[$i]['borrow_fee'] = $v['borrow_fee'];
		            $row[$i]['deadline'] = $v['deadline'];
		            $i++;
		        }
		    }
		    
		    $xls = new Excel_XML('UTF-8', false, 'datalist');
		    $xls->addArray($row);
		    $xls->generateXML(ACTION_NAME."list");
		    exit();
		}
        
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("xaction",ACTION_NAME);
        $search['execl'] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
	
	//swf上传图片
	public function swfUpload(){
		if($_POST['picpath']){
			$imgpath = substr($_POST['picpath'],1);
			if(in_array($imgpath,$_SESSION['imgfiles'])){
					 unlink(C("WEB_ROOT").$imgpath);
					 $thumb = get_thumb_pic($imgpath);
				$res = unlink(C("WEB_ROOT").$thumb);
				if($res) $this->success("删除成功","",$_POST['oid']);
				else $this->error("删除失败","",$_POST['oid']);
			}else{
				$this->error("图片不存在","",$_POST['oid']);
			}
		}else{
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Product/' ;
			$this->thumbMaxWidth = C('PRODUCT_UPLOAD_W');
			$this->thumbMaxHeight = C('PRODUCT_UPLOAD_H');
			$this->saveRule = date("YmdHis",time()).rand(0,1000);
			$info = $this->CUpload();
			$data['product_thumb'] = $info[0]['savepath'].$info[0]['savename'];
			if(!isset($_SESSION['count_file'])) $_SESSION['count_file']=1;
			else $_SESSION['count_file']++;
			$_SESSION['imgfiles'][$_SESSION['count_file']] = $data['product_thumb'];
			echo "{$_SESSION['count_file']}:".__ROOT__."/".$data['product_thumb'];//返回给前台显示缩略图
		}
	}
	
	//人工处理满标但未进入复审列表的数据
	public function dowaitMoneyComplete(){
		$pre = C('DB_PREFIX');
		$borrow_id = $_REQUEST['id'];
		$upborrowsql = "update `{$pre}borrow_info` set ";
		$upborrowsql .= "`borrow_status`= 4,`full_time`=".time();
		$upborrowsql .= " WHERE `id`={$borrow_id}";
		
		$result = M()->execute($upborrowsql);
		if($result) {
			alogs("dowaitMoneyComplete",0,1,'人工处理满标但未进入复审列表的数据操作成功！');//管理员操作日志
			$this->success("处理成功");
			$this->assign('jumpUrl', __URL__."/".session('listaction'));
		}else{
			alogs("dowaitMoneyComplete",0,0,'人工处理满标但未进入复审列表的数据操作失败！');//管理员操作日志
			$this->error("处理失败");
			$this->assign('jumpUrl', __URL__."/".session('listaction'));
		}
	}
	
	//邮件提醒
	  public function tip() {
	  	$id = intval($_REQUEST['id']);
		$vm = M('borrow_info')->field('borrow_uid,borrow_name,borrow_money,repayment_type,deadline')->find($id);
		$borrowName = $vm['borrow_name'];
		$borrowMoney = $vm['borrow_money'];
		if($id){
			Notice(9,$vm['borrow_uid'],array('id'=>$id,'borrowName'=>$borrowName,'borrowMoney'=>$borrowMoney));
			ajaxmsg();
		}
		else ajaxmsg('',0);
	}
	
	//每个借款标的投资人记录
	 public function doinvest()
    {
		$borrow_id = intval($_REQUEST['borrow_id']);
		$map=array();
		
		$map['bi.borrow_id'] = $borrow_id;
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_investor bi')->join("{$this->pre}members m ON m.id=bi.investor_uid")->where($map)->count('bi.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'bi.id bid,b.id,bi.investor_capital,bi.investor_interest,bi.invest_fee,bi.add_time,bi.is_auto,m.user_name,m.id mid,m.user_phone,b.borrow_duration,b.repayment_type,m.customer_name,b.borrow_type,b.borrow_name';
		$list = M('borrow_investor bi')->field($field)->join("{$this->pre}members m ON m.id=bi.investor_uid")->join("{$this->pre}borrow_info b ON b.id=bi.borrow_id")->where($map)->limit($Lsql)->order("bi.id DESC")->select();
		$list = $this->_listFilter($list);
		
		//dump($list);exit;
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->display();
    }
	
	/////////////////////////////////////新增未满标的人工满标应急处理  2014-06-13 fan 开始//////////////////////////////////
	 public function borrowfull(){
		$map=array();
		$map['b.borrow_status'] = 2;
		if(!empty($_REQUEST['uname'])&&!$_REQUEST['uid'] || $_REQUEST['uname']!=$_REQUEST['olduname']){
			$uid = M("members")->getFieldByUserName(text($_REQUEST['uname']),'id');
			$map['b.borrow_uid'] = $uid;
			$search['uid'] = $map['b.borrow_uid'];
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
            }
            $search['protype']= $_REQUEST['protype'];
        }
		if( !empty($_REQUEST['uid'])&&!isset($search['uname']) ){
			$map['b.borrow_uid'] = intval($_REQUEST['uid']);
			$search['uid'] = $map['b.borrow_uid'];
			$search['uname'] = $_REQUEST['uname'];
		}
		if(!empty($_REQUEST['uphone'])){
			$search['uphone'] = $_REQUEST['uphone'];
			$map['m.user_phone'] = $_REQUEST['uphone'];
		}
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['money'])){
			$map['b.borrow_money'] = array($_REQUEST['bj'],$_REQUEST['money']);
			$search['bj'] = $_REQUEST['bj'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['b.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['b.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		
		if($_REQUEST['customer_id'] && $_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name'] && !$search['customer_id']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
		
		$map['b.borrow_min'] = array("exp","> (b.borrow_money-b.has_borrow)");
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理

		$field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.borrow_money,b.product_type,b.danbao,m.user_phone,m.user_name,m.id mid,b.is_tuijian,b.has_borrow,b.money_collect,b.borrow_min';
		$list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
		
		$list = $this->_listFilter($list);
		foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
        }
		$vo = M('members')->field("id,user_name")->select();//查询出所有会员
		$userlist = array();
		if(is_array($vo)){
			foreach($vo as $key => $v){
				foreach($list as $key1 => $v1){
					if($v['id']!=$v1['borrow_uid']){
						$userlist[$v['id']]=$v['user_name'];
					}
				}
			}
		}
		$this->assign("userlist",$userlist);//流转会员
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
		$this->assign("xaction",ACTION_NAME);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
	//人工处理低于最小投标金额无法正常满标的情况
	
	public function doMoneyComplete(){
		$pre = C('DB_PREFIX');
		$borrow_id = $_REQUEST['id'];
		$money = intval($_POST['lastmoney']);
		$uid = $_REQUEST['uid'];
		if(empty($uid)){
			$this->error("请选择投资人");
		}
		$vm = M('borrow_info')->field('borrow_status')->find($borrow_id);
		if(($vm['borrow_status']!=2)){
			$this->error('该标借款状态不在借款中，无法执行满标处理！');
			exit;
		}else{
			$done = investMoney($uid,$borrow_id,$money);
			if($done===true) {
				alogs("doMoneyComplete",0,1,'人工处理低于最小投标金额无法正常满标的数据操作成功！');//管理员操作日志
				$this->success("恭喜成功投标{$money}元");
			}else if($done){
				$this->error($done);
			}else{
				alogs("doMoneyComplete",0,1,'人工处理低于最小投标金额无法正常满标的数据操作成功！');//管理员操作日志
				$this->error("对不起，投标失败，请重试!");
			}
		}
	}
 	/////////////////////////////////////新增未满标的人工满标应急处理  2014-06-13 fan 结束//////////////////////////////////

 	// 转现货EDIT
 	function editxianhuo(){
 		$id = intval($_REQUEST['id']);
 		$data = M('borrow_info')->where("id={$id}")->find(); 
 		
 		if($data['product_type']==2){
 			$timediff = (strtotime(date('Y-m-d',$data['deadline']))-strtotime(date('Y-m-d',$data['add_time'])))/(3600*24);
 			$data['borrow_duration'] = $timediff+1;//包含转现货当天
 		}
 		$data['add_time']=date('Y-m-d H:i:s',$data['add_time']);
 		$extra_info = M('borrow_info_additional')->where("bid={$id}")->find();
 		$data['extra_info']= $extra_info['extra_info'];
		$this->assign('vo',$data);
		$this->display();
 	}

 	function computationtime(){
 		$id = intval($_REQUEST['id']);
 		$data = M('borrow_info')->field('deadline')->where("id={$id}")->find(); 
 		$addtime = strtotime($_REQUEST['addtime']);
 		
 		$timediff = (strtotime(date('Y-m-d',$data['deadline']))-strtotime(date('Y-m-d',$addtime)))/(3600*24);
 		$res['timediff'] = $timediff+1;
 		echo json_encode($res);
 	}

 	function editdoxianhuo(){
 		$pre = C('DB_PREFIX');
 		// 转现货利息计算公式
 		// 最终利息 = 原利率/360 * 借款总额 * 原借款期限 + 现货利率/360 * 借款总额 * 现货还款天数
 		$v = M('borrow_info')->where("id={$_POST['id']}")->find();
 		$vd = M('investor_detail')->where("borrow_id={$_POST['id']}")->find();

 		if ($v['product_type']==2) {
 			$duration = $_POST['borrow_duration'];
 			$timediff = (strtotime(date('Y-m-d',$v['deadline']))-strtotime(date('Y-m-d',$v['add_time'])))/(3600*24);
 			if($duration > $timediff){
 				$this->error("借款期限不能大于当前期限");
 			}
 			if($duration<=0){
 				$this->error("借款期限不能小于0");
 			} 
 			if($vd['deadline']<time()&&$vd['status']==7){
 				$this->error('逾期不能修改借款期限');
 			}
 			$pars['borrow_id'] = $_POST['id'];
 			$par['deadline'] = $v['add_time'] + $duration*24*3600;
 			$res = M('borrow_investor')->where($pars)->save($par);
 			$res1 = M('investor_detail')->where($pars)->save($par);
 			$res2 = M('borrow_info')->where('id='.$_POST['id'])->save($par);

        	$param['extra_info']=$_POST['art_content'];
            $where['bid']=$_POST['id'];
            $res3 = M('borrow_info_additional')->where($where)->save($param);
            if(($res&&$res1&&$res2)||$res3){
	 			$this->success("操作成功！");
	 		}else{
	 			$this->error('操作失败！');
	 		}
	 		exit;
 		}

 		$data['id'] 					= text($_POST['id']);
 		$data['product_type'] 			= text($_POST['product_type']);
 		$data['borrow_interest_rate'] 	= text($_POST['borrow_interest_rate']);
 		$data['borrow_duration'] 	    = text($_POST['borrow_duration']);
 		$data['colligate_fee'] 	    	= text($_POST['colligate_fee']);

        if($_POST['add_time']==""){
            $this->error("请填写提单转现货的时间");
        }
        else{
            $data['add_time'] 				= strtotime($_POST['add_time']); //更新起始标的日期
            $cur=time();
            if($data['add_time']>$cur)
                $this->error("设置时间不能大于当前时间");
        }


 		// 转现货更新deadline时间 最后还款时间
 		$deadline 					= date('Y-m-d',$data['add_time']);
                $after=$data['borrow_duration']-1;
 		$deadline_new				= strtotime(date('Y-m-d 23:59:59',strtotime($deadline.'+ '.$after.' day')));
 		$data['deadline']           = $deadline_new; // 更新最后还款期限

 		if ($v['borrow_money'] != text($_POST['borrow_money'])) {
 			$this->error("借款金不能修改!");
 		}
 		$remark = M('member_genzong')->field('remark_type')->where("remark_type=5 and borrow_id=".$v['id'])->find();
 		if (empty($remark)) {
 			$this->error("请先走完质押跟综流程，才能转现货质押！");
 		}

        if($_POST['art_content']!=''){
            D('borrow_info_additional')->save_extra_info($_POST['id'],$_POST['art_content']);
        }
         
 		// 提单利息
 		// 利息计算天数 = 当前时间 - 发标时间
 		$current_time = strtotime(date('Y-m-d', $data['add_time'] ));
 		$send_time    = strtotime(date('Y-m-d',$v['second_verify_time']));
 		$end_time = ceil(($current_time - $send_time)/3600/24);
        /*
 		if ($end_time == 0) {
 			$end_time += 1;
 		}*/
 		$blo_borrow_interest = getFloatValue($v['borrow_interest_rate']/36000*$v['borrow_money']*$end_time, 2);
 		// print_r($blo_borrow_interest);die;
 		$data['n_interest'] = $blo_borrow_interest;
 		// 现货利息
 		$new_borrow_interest = ($data['borrow_interest_rate']/36000)*$v['borrow_money']*$data['borrow_duration'];
 		$data['borrow_interest'] = getFloatValue($blo_borrow_interest + $new_borrow_interest,2);

 		// 综合服务费 利率/36000 * 借款金额 * 天数  提单、现货的综合服务费
		$data['n_colligate_fee'] = getFloatValue($v['colligate_fee']/36000*$v['borrow_money']*$end_time, 2);

 		// 更新详情表的利息
 		$vdborrow_id = $vd['borrow_id'];
 		$vdcapital   = $v['borrow_money'];
 		$Detail = M("investor_detail");
 		$investor_uid = $Detail->where('borrow_id='.$vdborrow_id)->select();
 		foreach ($investor_uid as $items) {
 			$vdinterest = $items['capital']/$v['borrow_money']*$data['borrow_interest'];
 			$Detail->execute("update `{$pre}investor_detail` set `interest`={$vdinterest},`deadline`={$deadline_new} WHERE `capital`={$items['capital']} and `borrow_id`={$vdborrow_id}");
 		}

        //提单转现货，更新补充资料
       D('borrow_info_additional')->second_xianhuo(intval($_POST['id']));

 		$neid = M('borrow_info')->save($data);


 		if($neid){
 			$this->success("操作成功！");
 		}else{
 			$this->error('操作失败！');
 		}
 	}

	/**
	 * 债权转让复审列表
	 */
	public function debtcheckindex(){
		$where["d.borrow_status"] = 4;
		$field = "d.id,d.old_borrow_id,mi.real_name,d.borrow_name,bi.investor_capital,d.totalmoney,d.borrow_money,d.debt_rate,d.full_time,d.invest_id";
		$list = M("debt_borrow_info d")
				->join("lzh_member_info mi ON mi.uid = d.borrow_uid")
				->join("lzh_borrow_investor bi ON bi.id = d.invest_id")
				->where($where)
				->field($field)
				->select();
		$this->assign("list",$list);
		$this->display();
	}

	/**
	 * 债权转让审核
	 */
	public function debtcheck(){
		$borrowid=$_POST["borrow_id"];
		$result=$_POST["result"];
		file_put_contents('debtlog.txt', "债权复审：标号：".$borrowid."\n", FILE_APPEND);
		
		if($result){
			/****************************************复审start*************************************************************/
			$list = M("sinalog")->where("type=16 AND borrow_id ={$borrowid} AND status = 2")->select();
			$a=0;
			$b=0;
			$c=0;
			$trade_list = null;
			$newbid=$borrowid;
			foreach ($list as $i) {
				if($a<100){
					if($b === 0){
						$trade_list[$c] = date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第ZQ".$newbid."号标投资成功";
						$b++;
					}else{
						$trade_list[$c] .= '$'.date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第ZQ".$newbid."号标投资成功";
					}
					$a++;
					if($a===100){$a = 0;$b=0;$c++;}
				}
			}
			foreach ($trade_list as $list) {
				sinafinishpretrade($list);
			}
			$this->outmessage(0,"审核通过成功");
			/************************************************end ****************************************************/
		}else{
			$this->outmessage(1,"审核不通过成功");
		}


	}


	/**
	 * ajax 输出json格式
	 * @param $status
	 * @param $message
	 * @param null $data
	 */
	private function outmessage($status,$message,$data=null){
		$outdata=array();
		$outdata["status"]=$status;
		$outdata["msg"] =$message;
		if($data) {
			$outdata["data"]=$data;
		}
		echo json_encode($outdata);
		exit();
	}

	/**
	 * 原债权比对
	 */
	public function compare(){
		  $borrowid=$_GET["id"];
		  $invest_id=$_GET['investid'];
		  $where["d.id"]=$borrowid;
		  $debt_info  = M("debt_borrow_info d")
		  				->join("lzh_member_info mi ON mi.uid = d.borrow_uid")
		 				->where($where)
		 				->field("d.id,d.totalmoney,d.debt_captial,d.debt_interest,mi.real_name,d.invest_id,d.old_borrow_id")
		 				->find();
		  $invest_info = M("investor_detail")->where(array("invest_id"=>$debt_info["invest_id"]))->field("sum(capital) as capital,sum(interest) as interest")->find();

		  $this->assign("debt_info",$debt_info);
		  $this->assign("invest_info",$invest_info);

          $this->display();
	}

	/**
	 * 投标明细
	 */
	public function debtdetail(){
		 $borrow_id=$_GET["id"];
		 $borrow_debt=M("borrow_debt")->where(array("debt_borrow_id"=>$borrow_id))->find();
		 $ot=array();
		 $old_borrow_id=$borrow_debt['borrow_id'];
		 $last_borrow_id=$borrow_debt['debt_parent_borrow_id'];
         $qishulist=M("borrow_info")->where(array("id"=>$old_borrow_id))->find();
		 $qishu=$qishulist['total'];
		 $tongji=[];
		 $mylist=M("borrow_investor")->query("select DISTINCT investor_uid from (
select t.investor_uid,t.borrow_id from lzh_borrow_investor t where  t.investor_uid not in(select investor_uid from lzh_investor_detail g where  g.invest_id=t.id and g.status=-1 and g.investor_uid=t.investor_uid)
union 
select t.investor_uid,t.borrow_id from lzh_debt_borrow_investor t where  t.investor_uid not in(select investor_uid from lzh_investor_detail g where  g.invest_id=t.id and g.status=-1 and g.investor_uid=t.investor_uid)
) g where  g.borrow_id={$old_borrow_id}");
		 foreach ($mylist as $key=>$value){
			 $total=M("investor_detail t")->where(array("investor_uid"=>$value['investor_uid'], 'borrow_id'=>$old_borrow_id,'status'=>array('neq',-1)))->sum("capital+interest");
             for($i=1;$i<=$qishu;$i++){
				 $oplist=M("investor_detail t")->join("lzh_members m on m.id=t.investor_uid")->where(array("investor_uid"=>$value['investor_uid'], 'sort_order'=>$i, 'borrow_id'=>$old_borrow_id,'status'=>array('neq',-1)))->select();
				  foreach ($oplist as $v){
					 $op['user_name']=$v['user_name'];
					 $op['money']=$v['capital']+$v['interest'];
					 $ot[$key][$i-1]=$op;
				 }
			 }
			 $tongji[$key]["total"]=$total;
		 }
		 for ($i=0;$i<$qishu;$i++){
			$total_qishu[]=$i+1;
		 }

		 $this->assign("total_qishu",$total_qishu);
		 $this->assign("ot",$ot);
		$this->assign("tongji",$tongji);
		$this->assign("borrow_id",zhaiquan_borrowidlayout1($borrow_id));
         if($last_borrow_id==$old_borrow_id){
			 $last_borrow_id=borrowidlayout1($last_borrow_id);
		 }else{
			 $last_borrow_id=zhaiquan_borrowidlayout1($last_borrow_id);
		 }
		$this->assign("last_borrow_id",$last_borrow_id);
		 $this->display();
	}


 	/**
 	 已逾期借款
 	 */
 	public function overdue(){
 	    $this->display();
 	}
}
?>