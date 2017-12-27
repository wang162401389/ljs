<?php
// 全局设置
class MembersAction extends ACommonAction
{
    
    // 有过投资行为的用户
    protected $investor_ids = array();

    // 身份通过验证的用户
    protected $approved_ids = array();

    private function _getInvestorIds(){
    	$investors = M('borrow_investor')->Distinct(true)->field('investor_uid')->select();
    	foreach ($investors as $key => $value) {
    		$this->investor_ids[] = $value['investor_uid'];
    	}
    }

    private function _getApprovedMembers(){
    	$members = M('members_status')->field('uid')->where('id_status=1 or company_status=3')->select();
    	foreach ($members as $key => $value) {
    		$this->approved_ids[] = $value['uid'];
    	}
    }

    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$this->_getApprovedMembers();
		$this->_getInvestorIds();

		$map = [];
		if($_REQUEST['uname']){
			$map['m.user_name'] = ["like", "%".urldecode($_REQUEST['uname'])."%"];
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if($_REQUEST['uphone']){
			$map['m.user_phone'] = $_REQUEST['uphone'];
			$search['uphone'] = $_REQUEST['uphone'];
		}
		if($_REQUEST['realname']){
			$map1['mi.real_name'] = ["like", "%".urldecode($_REQUEST['realname'])."%"];
            $map1["mc.company_name"] = ["like", "%".urldecode($_REQUEST['realname'])."%"];
            $map1['_logic'] = 'or';
            $map['_complex'] = $map1;
            $search['realname'] = $_REQUEST['realname'];
		}
		if($_REQUEST['is_vip'] == 'yes'){
			$map['is_vip'] = 1;
			$search['is_vip'] = 'yes';	
		}elseif($_REQUEST['is_vip'] == 'no'){
			$map['is_vip'] = 0;
			$search['is_vip'] = 'no';	
		}
		if($_REQUEST['user_regtype'] == 1){
			$map['m.user_regtype'] = 1;
			$search['user_regtype'] = 1;	
		}elseif($_REQUEST['user_regtype'] == 2){
			$map['m.user_regtype'] = 2;
			$search['user_regtype'] = 2;	
		}
		if($_REQUEST['is_approved'] == 'yes'){
			$map['m.id'] = ['in', $this->approved_ids];
			$search['is_approved'] = 'yes';	
		}elseif($_REQUEST['is_approved'] == 'no'){
			$map['m.id'] = ['not in', $this->approved_ids];
			$search['is_approved'] = 'no';	
		}
		if($_REQUEST['is_investor'] == 'yes'){
			$is_investor['m.id'] = ['in', $this->investor_ids];
			$search['is_investor'] = 'yes';	
			$map["_complex"] = $is_investor;
		}elseif($_REQUEST['is_investor'] == 'no'){
			$is_investor['m.id'] = ['not in', $this->investor_ids];
			$search['is_investor'] = 'no';	
			$map["_complex"] = $is_investor;
		}

		if($_REQUEST['is_transfer'] == 'yes'){
			$map['m.is_transfer'] = 1;
		}elseif($_REQUEST['is_transfer'] == 'no'){
			$map['m.is_transfer'] = 0;
		}
		
		if($_REQUEST['customer_name']){
			$map['m.customer_id'] = $_REQUEST['customer_id'];
			$search['customer_id'] = $map['m.customer_id'];	
			$search['customer_name'] = urldecode($_REQUEST['customer_name']);	
		}
		
		if($_REQUEST['customer_name']){
			$cusname = urldecode($_REQUEST['customer_name']);
			$kfid = M('ausers')->getFieldByUserName($cusname,'id');
			$map['m.customer_id'] = $kfid;
			$search['customer_name'] = $cusname;	
			$search['customer_id'] = $kfid;	
		}
			
		if(!empty($_REQUEST['bj']) && !empty($_REQUEST['lx']) && !empty($_REQUEST['money'])){
			if($_REQUEST['lx'] == 'allmoney'){
				if($_REQUEST['bj'] == 'gt'){
					$bj = '>';
				}else if($_REQUEST['bj'] == 'lt'){
					$bj = '<';
				}else if($_REQUEST['bj'] == 'eq'){
					$bj = '=';
				}
				$map['_string'] = "(mm.account_money+mm.back_money) ".$bj.$_REQUEST['money'];
			}else{
				$map[$_REQUEST['lx']] = [$_REQUEST['bj'], $_REQUEST['money']];
			}
			$search['bj'] = $_REQUEST['bj'];	
			$search['lx'] = $_REQUEST['lx'];	
			$search['money'] = $_REQUEST['money'];	
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = ["between", $timespan];
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime($_REQUEST['start_time']);
			$map['m.reg_time'] = ["gt", $xtime];
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time'] = ["lt", $xtime];
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}
		
		$borrow_investor = M('borrow_investor');
		$members_status = M('members_status');
		$members = M('members');
		
		$field = 'm.id,m.user_phone,m.reg_time,m.user_name,m.customer_name,m.user_leve,m.user_regtype,m.time_limit,m.equipment,mi.real_name,mm.money_freeze,mm.money_collect,(mm.account_money+mm.back_money) account_money,m.user_email,m.recommend_id,m.is_borrow,m.is_vip,mc.company_name,m.is_rebate';
		$join1 = "{$this->pre}member_money mm ON mm.uid=m.id";
		$join2 = "{$this->pre}member_info mi ON mi.uid=m.id";
		$join3 = "{$this->pre}members_company mc ON mc.uid=m.id";
		
		$Lsql = 0;
		if($_REQUEST['execl'] != "execl"){
		    import("ORG.Util.Page");
		    $count = M('members m')->field($field)->join($join1)->join($join2)->join($join3)->where($map)->count('m.id');
		    $p = new Page($count, C('ADMIN_PAGE_SIZE'));
		    $page = $p->show();
		    $Lsql = "{$p->firstRow},{$p->listRows}";
		}
		
		$list = M('members m')->field($field)->join($join1)->join($join2)->join($join3)->where($map)->limit($Lsql)->order('m.id DESC')->select();

		if (!empty($list)) {
		    foreach ($list as $k => &$v) {
		        $condition1['id_status'] = 1;
		        $condition1['company_status'] = 3;
		        $condition1["_logic"] = "or";
		        $where1["_complex"] = $condition1;
		        $where1['uid'] = $v['id'];
		        $v['is_approved'] = $members_status->where($where1)->count() > 0 ? '是' : '否';
		        $condition2['investor_uid'] = $v['id'];
		        $v['is_investor'] = $borrow_investor->where($condition2)->count() > 0 ? '是' : '否';
		        $v['is_rebate'] = $v['is_rebate'] == 1 ? '是' : '否';
		    }
		}
        
		$list = $this->_listFilter($list);

		// 屏蔽cps
		foreach ($list as $key => $value) {
			//exit($list[$key]['equipment']);
			if(in_array($list[$key]['equipment'],array_column(C("BLOCK_CPS"),"code")))
			{
				$list[$key]['equipment'] = "PC端";
			}
		}
        
		//导出exel
		if($_REQUEST['execl'] == "execl"){
		    import("ORG.Io.Excel");
		    
		    $row = [];
		    $row[0] = ['ID','身份','用户名','手机号','真实姓名','推荐人','注册渠道','会员类型','注册时间','是否已身份验证', '是否已进行过投资', '是否投标返利'];
		    
		    if (!empty($list)) {
		        $members_company = M('members_company');
		        foreach ($list as $k => $v) {
		            $row[$k + 1]['id'] = $v['id'];
		            $row[$k + 1]['is_vip'] = strip_tags($v['is_vip']);
		            $row[$k + 1]['user_name'] = $v['user_name'];
		            $row[$k + 1]['user_phone'] = $v['user_phone'];
		            $row[$k + 1]['real_name'] = $v['real_name'];
		            $row[$k + 1]['recommend_name'] = strip_tags($v['recommend_name']);
		            $equipment = strtoupper($v['equipment']);
		            if ($equipment == 'PC') {
		                $row[$k + 1]['equipment'] = 'PC端';
		            }elseif ($equipment == 'WECHAT'){
		                $row[$k + 1]['equipment'] = '微信';
		            }else {
		                $row[$k + 1]['equipment'] = $v['equipment'];
		            }

		           
		            if ($v['user_regtype'] == 2) {
		                $row[$k + 1]['real_name'] = $members_company->where(['uid' => $v['id']])->getField('company_name');
		            }
		            $row[$k + 1]['user_type'] = $v['user_type'];
		            $row[$k + 1]['reg_time'] = date('Y-m-d H:i', $v['reg_time']);
		            $row[$k + 1]['is_approved'] = $v['is_approved'];
		            $row[$k + 1]['is_investor'] = $v['is_investor'];
		            $row[$k + 1]['is_rebate'] = $v['is_rebate'];
		        }
		    }
		    $xls = new Excel_XML('UTF-8', false, 'datalist');
		    $xls->addArray($row);
		    $xls->generateXML("memberlist");
		    exit();
		}
		
        $this->assign("bj", ["gt" => '大于', "eq" => '等于', "lt" => '小于']);
        $this->assign("lx", ["allmoney" => '可用余额', "mm.money_freeze" => '冻结金额', "mm.money_collect" => '待收金额']);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $search['execl'] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    public function edit() {
        $model = D(ucfirst($this->getActionName()));
		setBackUrl();
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
		$vx = M('member_info')->where("uid={$id}")->find();
		if(!is_array($vx)){
			M('member_info')->add(array("uid"=>$id));
		}else{
			foreach($vx as $key=>$vxe){
				$vo[$key]=$vxe;
			}
		}
		
		///////////////////////
		$vb = M('member_banks')->where("uid={$id}")->find();
		if(!is_array($vb)){
			M('member_banks')->add(array("uid"=>$id));
		}else{
			foreach($vb as $key=>$vbe){
				$vo[$key]=$vbe;
			}
		}
		
		//////////////////////
        $this->assign('vo', $vo);
		$this->assign("utype", C('XMEMBER_TYPE'));
		$this->assign("bank_list",$this->gloconf['BANK_NAME']);
        $this->assign("memberinfo", M('members')->find($id)); // 判断企业还是个人
        $this->display();
    }
	
	//添加数据
    public function doEdit() {
        $model = D(ucfirst($this->getActionName()));
        $model2 = M("member_info");
		$model3 = M("member_banks");

        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false === $model2->create()) {
            $this->error($model2->getError());
        }
		if (false === $model3->create()) {
            $this->error($model3->getError());
        }
		
		$model->startTrans();
        if(!empty($model->user_pass)){
			$model->user_pass=md5($model->user_pass);
		}else{
			unset($model->user_pass);
		}
        if(!empty($model->pin_pass)){
			$model->pin_pass=md5($model->pin_pass);
		}else{
			unset($model->pin_pass);
		}

		$model->user_phone = $model2->cell_phone;
		$model3->add_ip = get_client_ip();
		$model3->add_time = time();
        $model3->companyname = $_POST['bank_companyname'];
		
		$aUser = get_admin_name();
		$kfid = $model->customer_id;
		$model->customer_name = $aUser[$kfid];

		$result = $model->save();
		$result2 = $model2->save();
		$result3 = $model3->save();
		
        //保存当前数据对象
        if ($result || $result2 || $result3) { //保存成功
			$model->commit();
			alogs("Members",0,1,'成功执行了会员信息资料的修改操作！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			alogs("Members",0,0,'执行会员信息资料的修改操作失败！');//管理员操作日志
			$model->rollback();
            //失败提示
            $this->error(L('修改失败'));
        }
    }
	
    public function info()
    {	
		if($_GET['user_name']) $search['m.user_name'] = text($_GET['user_name']);
		else if($_GET['user_phone']) $search['m.user_phone'] = text($_GET['user_phone']);
		else $search=array();
		$list = getMemberInfoList($search,10);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
        $this->assign("search", $search);
        $this->display();
    }
	
    public function infowait()
    {	
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		if($_GET['user_name']) $search['m.user_name'] = text($_GET['user_name']);
		else $search=array();
		$list = getMemberApplyList($search,10);
		$this->assign("aType",$Bconfig['APPLY_TYPE']);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);
        $this->display();
    }
	
    public function viewinfo()
    {	
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$this->assign("aType",$Bconfig['APPLY_TYPE']);
		setBackUrl();
		$id = intval($_GET['id']);
		$vx = M('member_apply')->field(true)->find($id);
		$uid = $vx['uid'];
		$vo = getMemberInfoDetail($uid);
		$this->assign("vx",$vx);
		$this->assign("vo",$vo);
		$this->assign("id",$id);
        $this->display();
    }
	
    public function viewinfom()
    {	
		$id = intval($_GET['id']);
		$vo = getMemberInfoDetail($id);
		$this->assign("vo",$vo);
        $this->display();
    }

	public function doEditCredit(){
		$id = intval($_POST['id']);
		$uid = intval($_POST['uid']);
		$data['id'] = $id;
		$data['deal_info'] = text($_POST['deal_info']);
		$data['apply_status'] = intval($_POST['apply_status']);
		$data['credit_money'] = floatval($_POST['credit_money']);
		$newid = M('member_apply')->save($data);
		
		if($newid){
			//审核通过后资金授信改动
			if($data['apply_status']==1){
				$vx = M('member_apply')->field(true)->find($id);
				$umoney = M('member_money')->field(true)->find($vx['uid']);
				
				$moneyLog['uid'] = $vx['uid'];
				if($vx['apply_type']==1){
					$moneyLog['credit_limit'] = floatval($umoney['credit_limit']) + $data['credit_money'];
					$moneyLog['credit_cuse'] = floatval($umoney['credit_cuse']) + $data['credit_money'];
				}elseif($vx['apply_type']==2){
					$moneyLog['borrow_vouch_limit'] = floatval($umoney['borrow_vouch_limit']) + $data['credit_money'];
					$moneyLog['borrow_vouch_cuse'] = floatval($umoney['borrow_vouch_cuse']) + $data['credit_money'];
				}elseif($vx['apply_type']==3){
					$moneyLog['invest_vouch_limit'] = floatval($umoney['invest_vouch_limit']) + $data['credit_money'];
					$moneyLog['invest_vouch_cuse'] = floatval($umoney['invest_vouch_cuse']) + $data['credit_money'];
				}
				
				if(!is_array($umoney))	M('member_money')->add($moneyLog);
				else M('member_money')->where("uid={$vx['uid']}")->save($moneyLog);
			}//审核通过后资金授信改动
			alogs("Members",0,1,'成功执行了会员资料通过后资金授信改动的审核操作！');//管理员操作日志
			$this->success("审核成功",__URL__."/infowait".session('listaction'));
		}else{
			alogs("Members",0,0,'执行会员资料通过后资金授信改动的审核操作失败！');//管理员操作日志
			$this->error("审核失败");
		}
	}
	
    public function moneyedit()
    {
		setBackUrl();
		$this->assign("id",intval($_GET['id']));
		$this->display();
    }
	
    public function doMoneyEdit()
    {
		$id = intval($_POST['id']);
		$uid = $id;
		$info = text($_POST['info']);
		$done=false;
		if(floatval($_POST['account_money'])!=0){
			$done=memberMoneyLog($uid,71,floatval($_POST['account_money']),$info);
		}
		if(floatval($_POST['money_freeze'])!=0){
			$done=false;
			$done=memberMoneyLog($uid,72,floatval($_POST['money_freeze']),$info);
		}
		if(floatval($_POST['money_collect'])!=0){
			$done=false;
			$done=memberMoneyLog($uid,73,floatval($_POST['money_collect']),$info);
		}
		//记录
		
        $this->assign('jumpUrl', __URL__."/index".session('listaction'));
		if($done){
			alogs("Members",0,1,'成功执行了会员余额调整的操作！');//管理员操作日志
			$this->success("操作成功");
		}else{
			alogs("Members",0,0,'执行会员余额调整的操作失败！');//管理员操作日志
			$this->error("操作失败");
		}
    }
	
    public function creditedit()
    {
		setBackUrl();
                $uid = intval($_GET['id']);
                $credit = M('member_money')->find($uid);
                $credit['credit_cuse']=$credit['credit_cuse']?$credit['credit_cuse']:'0.00';
                $credit['credit_limit']=$credit['credit_limit']?$credit['credit_limit']:'0.00';
                $credit['credit_used']=getFloatValue($credit['credit_cuse']-$credit['credit_limit'],2);
                $this->assign('credit',$credit);
		$this->assign("id",$uid);
		$this->display();
    }
	
    public function doCreditEdit()
    {
		$id = intval($_POST['id']);
		
		$umoney = M('member_money')->field(true)->find($id);
                $umoney['credit_used'] = $umoney['credit_cuse']-$umoney['credit_limit'];
		if(intval($_POST['new_credit'])!=0){
                    if(intval($_POST['new_credit'])<=intval($umoney['credit_used'])){
                        $this->error('新额度要大于已使用额度');
                    }
			$moneyLog['uid'] = $id;
			$moneyLog['credit_limit'] = floatval($_POST['new_credit']) - floatval($umoney['credit_used']);
			$moneyLog['credit_cuse'] = floatval($_POST['new_credit']);
			if(!is_array($umoney))	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		if(intval($_POST['borrow_vouch_limit'])!=0){
			$moneyLog=array();
			$moneyLog['uid'] = $id;
			$moneyLog['borrow_vouch_limit'] = floatval($umoney['borrow_vouch_limit']) + floatval($_POST['borrow_vouch_limit']);
			$moneyLog['borrow_vouch_cuse'] = floatval($umoney['borrow_vouch_cuse']) + floatval($_POST['borrow_vouch_limit']);
			if(!is_array($umoney) && !$newid)	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		if(intval($_POST['invest_vouch_limit'])!=0){
			$moneyLog=array();
			$moneyLog['uid'] = $id;
			$moneyLog['invest_vouch_limit'] = floatval($umoney['invest_vouch_limit']) + floatval($_POST['invest_vouch_limit']);
			$moneyLog['invest_vouch_cuse'] = floatval($umoney['invest_vouch_cuse']) + floatval($_POST['invest_vouch_limit']);
			if(!is_array($umoney) && !$newid)	$newid = M('member_money')->add($moneyLog);
			else $newid = M('member_money')->where("uid={$id}")->save($moneyLog);
		}
		
		//修改会员信用等级积分（E级->AAA级）
		$userCredits = M('members')->field(true)->find($id);
		if(intval($_POST['credits'])!=0){
			$moneyLog=array();
			$moneyLog['id'] = $id;
			$moneyLog['credits'] = intval($userCredits['credits'])+intval($_POST['credits']);
			if(!is_array($userCredits) && !$newid)	$newid = M('members')->add($moneyLog);
			else $newid = M('members')->where("id={$id}")->save($moneyLog);
		}
		
        $this->assign('jumpUrl', __URL__."/index".session('listaction'));
		if($newid){
			alogs("Members",0,1,'成功执行了会员授信调整的操作！');//管理员操作日志
			$this->success("操作成功");
		}else{
			alogs("Members",0,0,'执行会员授信调整的操作失败！');//管理员操作日志
			$this->error("操作失败");
		}
    }
	
	public function _listFilter($list){
		$row = [];
		if (!empty($list)) {
		    $members = M("members");
		    $members_company = M('members_company');
		    foreach($list as $key => $v){
		        if($v['recommend_id'] <> 0){
		            $v['recommend_name'] = $members->getFieldById($v['recommend_id'], "user_name");
		        }else{
		            $v['recommend_name'] = "<span style='color:#000'>无推荐人</span>";
		        }
		        if($v['is_vip'] == 1){
		            $v['is_vip'] = "<span style='color:red'>投资人/借款人</span>";
		        }else{
		            $v['is_vip'] ="投资人";
		        }
		        if($v['user_regtype'] == 2){
		            $company = $members_company->where('uid = '.$v['id'])->field('company_name')->find();
		            $v['user_type'] = "企业会员";
		            $v['real_name'] = $company['company_name'];
		        }elseif($v['user_regtype'] == 1){
		            $v['user_type'] = "个人会员";
		        }else{
		            $v['user_type'] = "普通会员";
		        }
		        $row[$key] = $v;
		    }
		}
		
		return $row;
	}
	
	public function getusername(){
		$uname = M("members")->getFieldById(intval($_POST['uid']),"user_name");
		if($uname) exit(json_encode(array("uname"=>"<span style='color:green'>".$uname."</span>")));
		else exit(json_encode(array("uname"=>"<span style='color:orange'>不存在此会员</span>")));
	}
	
	 public function idcardedit() {
        $model = D(ucfirst($this->getActionName()));
		setBackUrl();
        $id = intval($_REQUEST['id']);
        $vo = $model->find($id);
		$vx = M('member_info')->where("uid={$id}")->find();
		if(!is_array($vx)){
			M('member_info')->add(array("uid"=>$id));
		}else{
			foreach($vx as $key=>$vxe){
				$vo[$key]=$vxe;
			}
		}
        $this->assign('vo', $vo);
		$this->assign("utype", C('XMEMBER_TYPE'));
        $this->display();
    }
	
	//添加身份证信息
    public function doIdcardEdit() {
        $model = D(ucfirst($this->getActionName()));
        $model2 = M("member_info");
		
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        if (false === $model2->create()) {
            $this->error($model->getError());
        }
		
		$model->startTrans();
		/////////////////////////////
		if(!empty($_FILES['imgfile']['name'])){
			$this->fix = false;
			//设置上传文件规则
			$this->saveRule = 'uniqid';
			//$this->saveRule = date("YmdHis",time()).rand(0,1000)."_".$model->id;
			$this->savePathNew = C('ADMIN_UPLOAD_DIR').'Idcard/';
			$this->thumbMaxWidth = C('IDCARD_UPLOAD_H');
			$this->thumbMaxHeight = C('IDCARD_UPLOAD_W');
			$info = $this->CUpload();
			$data['card_img'] = $info[0]['savepath'].$info[0]['savename'];
			$data['card_back_img'] = $info[1]['savepath'].$info[1]['savename'];
			
			if($data['card_img']&&$data['card_back_img']){ 
				$model2->card_img=$data['card_img'];
				$model2->card_back_img=$data['card_back_img'];
			}
		}
		///////////////////////////
		$result = $model->save();
		$result2 = $model2->save();

        //保存当前数据对象
        if ($result || $result2) { //保存成功
			$model->commit();
			alogs("Members",0,1,'成功执行了会员身份证代传的操作！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
			$model->rollback();
			alogs("Members",0,0,'执行会员身份证代传的操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
        }
    }
    //发布借款
    public function issuesign() {
        $per = C('DB_PREFIX');
        if($this->uid){
            $this->assign("mstatus", M('members_status')->field(true)->find($this->uid));
            $this->assign("mdata", getMemberInfoDone($this->uid));
            $minfo = getMinfo($this->uid,true);
            $this->assign("minfo", $minfo);
            $this->assign("netMoney", getNet($this->uid));//可用净值额度
            $_allnetMoney = getFloatValue(0.9*$minfo['money_collect'],2);
            $this->assign("allnetMoney",$_allnetMoney);//总净值额度
            $this->assign("capitalinfo", getMemberBorrowScan($this->uid));
        }
        //查询该借款人是否设置支付密码或实名认证
        $id_status = M("members_status")->where("uid={$_GET['id']}")->field("id_status,company_status")->find();
        $utype = M("members")->where("id={$_GET['id']}")->field("user_regtype")->find();
		$issetpwd = checkissetpaypwd($_GET['id']);
		if($utype['user_regtype'] == 1){
			if($issetpwd['is_set_paypass'] == 'N' || $id_status["id_status"] == 0){
				$this->error("借款人在新浪未设置支付密码或未实名认证");
				exit;
			}
		}else{
			if($issetpwd['is_set_paypass'] == 'N' || $id_status["company_status"] == 0){
			$this->error("借款人在新浪未设置支付密码或未实名认证");
			exit;
		}
		}
        $vm = M("member_money")->where("uid={$_GET['id']}")->find();
        $this->assign('vm',$vm);
        $this->assign("issueid",$_GET['id']);
        $this->assign("pagebar", $donate_list['page']);
        $this->display();
    }
    //发布
    public function post(){
        file_put_contents("/mnt/share/p2p/UF/logs/uploadswf.log","666444",FILE_APPEND);
        file_put_contents("/mnt/share/p2p/UF/logs/uploadswf.log", "66660000",FILE_APPEND);
        //if(!$this->uid) $this->error("请先登陆",__APP__."/member/common/login");
        $vminfo = M('members')->field("user_leve,time_limit,is_borrow,is_vip")->find($_GET['id']);
        $membermoney = M('member_money')->field('credit_limit,credit_cuse')->find($_GET['id']);
        //是否内部发标人员，0：不开启；1：开启 后台设置
        if($vminfo['is_vip']==0){
            $_xoc = M('borrow_info')->where("borrow_uid={$_GET['id']} AND borrow_status in(0,2,4)")->count('id');
            if($_xoc>0)  $this->error("您有一个借款中的标，请等待审核");
            //通过VIP才能发标
            // if(!($vminfo['user_leve']>0 && $vminfo['time_limit']>time())) $this->error("请先通过VIP审核再发标",__APP__."/member/vip");
            
            if($vminfo['is_borrow']==0){
                $this->error("您目前不允许发布借款，如需帮助，请与客服人员联系！");
                $this->assign("waitSecond",3);
            }
            $vo = getMemberInfoDetail($_GET['id']);
            if($vo['province']==0 && $vo['province_now ']==0 && $vo['province_now ']==0 && $vo['city']==0 && $vo['city_now']==0 ){
                $this->error("请用户先登录填写个人详细资料后再发标");
            }
        }
        //发标类型 信用标  担保标...
        $gtype = $_GET[_URL_][3];
        $vkey = md5(time().$gtype);
        switch($gtype){
            case "normal"://普通标
                $borrow_type=1;
            break;
            case "vouch"://新担保标
                $borrow_type=2;
            break;
            case "second"://秒还标
                $this->assign("miao",'yes');
                $borrow_type=3;
            break;
            case "net"://净值标
                $borrow_type=4;
            break;
            case "mortgage"://抵押标
                $borrow_type=5;
            break;
        }

        cookie($vkey,$borrow_type,3600);
        $borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
        $day = range($borrow_duration_day[0],$borrow_duration_day[1]);

        $day_time=array();
        foreach($day as $v){
            $day_time[$v] = $v."天";
        }

        $borrow_duration = explode("|",$this->glo['borrow_duration']);
        $month = range($borrow_duration[0],$borrow_duration[1]);
        $month_time=array();
        foreach($month as $v){
            $month_time[$v] = $v."个月";
        }
        $rate_lixt = explode("|",$this->glo['rate_lixi']);
        $borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";
        $danbao=D("Members_company")->getDanBaoList();
        $this->assign('membermoney',$membermoney);
        $this->assign("danbao",$danbao);
        $this->assign("borrow_use",$this->gloconf['BORROW_USE']);
        $this->assign("borrow_min",$this->gloconf['BORROW_MIN']);
        $this->assign("borrow_max",$this->gloconf['BORROW_MAX']);
        $this->assign("borrow_time",$this->gloconf['BORROW_TIME']);
        $this->assign("BORROW_TYPE",$borrow_config['BORROW_TYPE']);
        $this->assign("borrow_type",$borrow_type);
        if($borrow_type!==1){
            foreach ($borrow_config['PRODUCT_TYPE'] as $K => $v){
                unset($borrow_config['PRODUCT_TYPE'][6]);
            }
        }
        $a = array('protype1'=>1,'protype'=>6);
        $this->assign('a',$a);
        $this->assign("borrow_day_time",$day_time);
        $this->assign("borrow_month_time",$month_time);
        $this->assign("repayment_type",$borrow_config['REPAYMENT_TYPE']);
        $this->assign("product_type",$borrow_config['PRODUCT_TYPE']);
        $this->assign("vkey",$vkey);
        $this->assign("rate_lixt",$rate_lixt);
        $this->assign("issueid",$_GET['id']);
        $this->assign("user_name", M('members')->where('id='.$_GET['id'])->getField('user_name'));
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

    //保存标
    public function save(){
        $pre = C('DB_PREFIX');
        //发标用户ID
        $userid = $_POST['issueid'];
        //相关的判断参数
        $rate_lixt = explode("|",$this->glo['rate_lixi']);
        $borrow_duration = explode("|",$this->glo['borrow_duration']);
        $borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
        $fee_borrow_manage = explode("|",$this->glo['fee_borrow_manage']);
        $vminfo = M('members m')->join("{$pre}member_info mf ON m.id=mf.uid")->field("m.user_leve,m.time_limit,mf.province_now,mf.city_now,mf.area_now")->where("m.id={$userid}")->find();
        //相关的判断参数
        $borrow['borrow_type'] = intval(cookie(text($_POST['vkey'])));
		if(trim($_POST['borrow_time'])==""|| $_POST['borrow_time']=="--请选择--"){
			$this->error("有效时间不能为空！");
			exit();
		}
		if(trim($_POST["borrow_use"])==""|| $_POST["borrow_use"]=="--请选择--"){
			$this->error("借款用途不能为空！");
			exit();
		}
		if(trim($_POST["borrow_duration"])=="" || $_POST["borrow_duration"]=="--请选择--"){
			$this->error("借款期限不能为空！");
			exit();
		}
		if(trim($_POST["borrow_min"])=="" || $_POST["borrow_min"]=="--请选择--"){
			$this->error("最小投资金额不能为空！");
			exit();
		}
		if((trim($_POST["repayment_type"])=="" || $_POST["repayment_type"]=="--请选择--") && strtolower($_POST['is_day'])!='yes'){
			$this->error("还款方式不能为空！");
			exit();
		}
		if(trim($_POST["product_type"])=="" || $_POST["product_type"]=="--请选择--"){
			$this->error("产品类型不能为空！");
			exit();
		}
		if($_POST["colligate"]==""){
			$this->error("平台综合服务费不能为空！");
			exit();
		}

        if($borrow['borrow_type']==0) $this->error("校验数据有误，请重新发布");
        //判断是否有担保机构
        $danbao=intval($_POST['danbao']);
        if($danbao!=0){
            if($_POST['vouch_money']==""){
                $this->error("担保机构没有设置服务费");
            }else{
                $max_money=D("Members_company")->get_left_credit_money($danbao);
                if($max_money<getFloatValue($_POST['borrow_money'],2)){
                    $this->error("担保公司额度小于借款额度");
                }else{
                    $borrow['danbao']=$danbao;
                    $borrow['vouch_money']=getFloatValue($_POST['vouch_money'],2);
                }

            }
        }

        if(floatval($_POST['borrow_interest_rate'])>$rate_lixt[1] || floatval($_POST['borrow_interest_rate'])<$rate_lixt[0]) $this->error("提交的借款利率超出允许范围，请重试",0);
        $borrow['borrow_money'] = intval($_POST['borrow_money']);


        $_minfo = getMinfo($userid,"m.pin_pass,mm.account_money,mm.back_money,mm.credit_cuse,mm.credit_limit,mm.money_collect");
        $_capitalinfo = getMemberBorrowScan($userid);
        ///////////////////////////////////////////////////////
        $borrowNum=M('borrow_info')->field("borrow_type,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money")->where("borrow_uid = {$userid} AND borrow_status=6 ")->group("borrow_type")->select();
        $borrowDe = array();
        foreach ($borrowNum as $k => $v) {
            $borrowDe[$v['borrow_type']] = $v['money'] - $v['repayment_money'];
        }
        ///////////////////////////////////////////////////
        switch($borrow['borrow_type']){
            case 1://普通标
                if($_minfo['credit_limit']<$borrow['borrow_money']) $this->error("您的可用信用额度为{$_minfo['credit_limit']}元，小于您准备借款的金额，不能发标");
                if(intval($_POST['product_type'])!==6) {$this->error ('产品类型应为“信金链”');}
            break;
            case 2://新担保标
            case 3://秒还标
            break;
            case 4://净值标
                $_netMoney = getFloatValue(0.9*$_minfo['money_collect']-$borrowDe[4],2);
                if($_netMoney<$borrow['borrow_money']) $this->error("您的净值额度{$_netMoney}元，小于您准备借款的金额，不能发标");
            break;
            case 5://抵押标
                //$borrow_type=5;
            break;
        }

        ////////////////////////新功能， 发标的时候，记入转现货的时间///////////////////////
        if(strtolower($_POST['is_day'])=='yes')
            $unit="天";
        else
            $unit="个月";
        $duration=intval($_POST['borrow_duration']);
        $text=$duration.$unit;
        if( ( $_POST['product_type']==1)&&(isset($_POST['second_duration']))&&($_POST['second_duration']>0)){
            $next=intval($_POST['second_duration']);
            $text.=" + ".$next.$unit;
            $duration+=$next;
        }
        $borrow['borrow_duration_txt']=$text;

        $borrow['borrow_uid'] = $userid;
        $borrow['borrow_name'] = text($_POST['borrow_name']);
        $borrow['borrow_duration'] = ($borrow['borrow_type']==3)?1:$duration;//秒标固定为一月
        $borrow['borrow_interest_rate'] = floatval($_POST['borrow_interest_rate']);
        if(strtolower($_POST['is_day'])=='yes') $borrow['repayment_type'] = 1;
        elseif($borrow['borrow_type']==3) $borrow['repayment_type'] = 2;//秒标按月还
        else $borrow['repayment_type'] = intval($_POST['repayment_type']);
        if($borrow['repayment_type']=='1' || $borrow['repayment_type']=='5'){
            $borrow['total'] = 1;
        }else{
            $borrow['total'] = $duration;//分几期还款(M+N)
        }
        $borrow['borrow_status'] = 0;
        $borrow['borrow_use'] = intval($_POST['borrow_use']);
        $borrow['add_time'] = strtotime($_POST['add_time']);
        $borrow['collect_day'] = intval($_POST['borrow_time']);
        $borrow['add_ip'] = get_client_ip();
        $borrow['borrow_info'] = $_POST['borrow_info'];
        $borrow['product_type'] = $_POST['product_type'];
       // $borrow['colligate_fee'] = $_POST['colligate_fee'];
       // $borrow['colligate_fee'] = $_POST['colligate_fee'];//提前收取手续费,不再设置服务费利率
        $borrow['reward_type'] = intval($_POST['reward_type']);
        $borrow['reward_num'] = floatval($_POST["reward_type_{$borrow['reward_type']}_value"]);
        $borrow['borrow_min'] = intval($_POST['borrow_min']);
        $borrow['borrow_max'] = intval($_POST['borrow_max']);
        if(isset($_POST['warehousing'])){
        	$borrow['warehousing'] = $_POST['warehousing'];
        }else{
        	$borrow['warehousing'] = '';
        }
        
        /*$borrow['province'] = $vminfo['province_now'];
        $borrow['city'] = $vminfo['city_now'];
        $borrow['area'] = $vminfo['area_now'];*/
        if($_POST['is_pass']&&intval($_POST['is_pass'])==1) $borrow['password'] = md5($_POST['password']);
        $borrow['money_collect'] = floatval($_POST['moneycollect']);//代收金额限制设置
        
        
        //借款费和利息
        //利息计算公式 借款总金额*(借款利率/36500)*借款天数
        $borrow['borrow_interest'] = getBorrowInterest(
             $borrow['repayment_type'],         // 还款类型
             $borrow['borrow_money'],           // 借款金额
             $borrow['borrow_duration'],        // 借款时间
             $borrow['borrow_interest_rate']    // 标的有效时间(天)
        );

        if($borrow['repayment_type'] == 1){//按天还
            $fee_rate = (is_numeric($fee_borrow_manage[0]))?($fee_borrow_manage[0]/100):0.001;
            $borrow['borrow_fee'] = getFloatValue($fee_rate*$borrow['borrow_money']*$borrow['borrow_duration'],2);
        }else{
            $fee_rate_1=(is_numeric($fee_borrow_manage[1]))?($fee_borrow_manage[1]/100):0.02;
            $fee_rate_2=(is_numeric($fee_borrow_manage[2]))?($fee_borrow_manage[2]/100):0.002;
            if($borrow['borrow_duration']>$fee_borrow_manage[3]&&is_numeric($fee_borrow_manage[3])){
                $borrow['borrow_fee'] = getFloatValue($fee_rate_1*$borrow['borrow_money'],2);
                $borrow['borrow_fee'] += getFloatValue($fee_rate_2*$borrow['borrow_money']*($borrow['borrow_duration']-$fee_borrow_manage[3]),2);
            }else{
                $borrow['borrow_fee'] = getFloatValue($fee_rate_1*$borrow['borrow_money'],2);
            }
        }
        
        if($borrow['borrow_type']==3){//秒还标
            if($borrow['reward_type']>0){
                $_reward_money = getFloatValue($borrow['borrow_money']*$borrow['reward_num']/100,2);
            }
            $_reward_money =floatval($_reward_money);
            if(($_minfo['account_money']+$_minfo['back_money'])<($borrow['borrow_fee']+$_reward_money)) $this->error("发布此标您最少需保证您的帐户余额大于等于".($borrow['borrow_fee']+$_reward_money)."元，以确保可以支付借款管理费和投标奖励费用");
        }

        // 上传图片
        ////////////////////图片编辑///////////////////////
        foreach($_POST['swfimglist'] as $key=>$v){
            $row[$key]['img'] = substr($v,1);
            $row[$key]['info'] = $_POST['picinfo'][$key];
        }
        $borrow['updata'] = serialize($row);

        //新增标记录
        $newid = M("borrow_info")->add($borrow);
        $product['borrow_id']=$newid;
        
        	if ($borrow['product_type']==1 || $borrow['product_type']==2 || $borrow['product_type']==3) {//质押标
	        	M("borrow_pledge")->add($product);
	        }else if($borrow['product_type']==4){//生产金融
	        	M("borrow_finance")->add($product);
	        }else if($borrow['product_type']==6){//信用标
	        	M("borrow_credit")->add($product);
	        }else if($borrow['product_type']==7){//优金链
	        	M("borrow_optimal")->add($product);
	        }
	        //担保标	
        	//M("borrow_guarantee")->add($product);
        $suo=array();
        $suo['id']=$newid; 
        $suo['suo']=0;
        $suoid = M("borrow_info_lock")->add($suo);

        // 发标处理提单质押跟完时间
        $td['add_time']                 = strtotime($_POST['td_add_time']);
        $td['user_id']                  = $userid; // 会员id
        $td['user_name']                = "linying001"; // 管理员用户名
        $td['remark']                   = "提单已签收"; // 备注
        $td['borrow_id']                = $newid; // 标id
        $td['admin_id']                = 127; // 管理id
        $td['remark_type']              = 1; // 类型
        M('member_genzong')->add($td);//质押物债权转让标不要跟踪
        // 如果是提到转现货，补充资料
        D("borrow_info_additional")->add_item($newid);

        if($newid){
        	if($borrow['product_type']==6){
        		$credit['credit_limit'] = $_minfo['credit_limit'] - $borrow['borrow_money'];
        		M('member_money')->where("uid={$userid}")->save($credit);
        	}
            $this->success("借款发布成功，网站会尽快初审",__URL__."/index");
        }else 
        $this->error(L('发布失败，请先检查是否完成了个人详细资料然后重试'));
    }
	///////////////////////////////////
    public function ajax_company_credit(){
        $uid=intval($_POST['uid']);
        $left=D("Members_company")->get_left_credit_money($uid);
        echo $left;
    }
}
?>