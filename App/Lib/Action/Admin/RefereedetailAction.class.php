<?php
// 全局设置
class RefereeDetailAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$this->pre = C('DB_PREFIX');
		$map=array();
		if(!empty($_REQUEST['runame'])){
			$ruid = M("members")->getFieldByUserName(text($_REQUEST['runame']),'id');
			$map['m.recommend_id'] = $ruid;
		}else{
			$map['m.recommend_id'] =array('neq','0');
		}
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['bi.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		if(session('admin_is_kf')==1 && m.customer_id!=0)	$map['m.customer_id'] = session('admin_id');
		//分页处理
		import("ORG.Util.Page");
		$xcount =M('borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->where($map)->group('bi.investor_uid')->buildSql();
		$newxsql = M()->query("select count(*) as tc from {$xcount} as t");
		$count = $newxsql[0]['tc'];
		
		$p = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		
		$field= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$list = M('borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($field)->where($map)->group('bi.investor_uid')->limit($Lsql)->select();
		
		$tfield= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$tlist = M('transfer_borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($tfield)->where($map)->group('bi.investor_uid')->limit($Lsql)->find();
		
		foreach($list as $key => $v)
		{
			$list[$key]['investor_capital'] = $v['investor_capital']+$tlist['investor_capital'];
			$list[$key]['total'] = $v['total']+$tlist['total'];		
		}
	
		$list=$this->_listFilter($list);
		
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
		
        $this->display();
    }
	
	
	public function _listFilter($list){
		$row=array();
		foreach($list as $key=>$v){
			 if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"user_name");
			 }else{
				$v['recommend_name'] ="<span style='color:red'>无推荐人</span>";
			 }
			 $row[$key]=$v;
		 }
		return $row;
	}
	
	public function export(){
		import("ORG.Io.Excel");

		$this->pre = C('DB_PREFIX');
		$map=array();
		if(!empty($_REQUEST['runame'])){
			$ruid = M("members")->getFieldByUserName(text($_REQUEST['runame']),'id');
			$map['m.recommend_id'] = $ruid;
		}else{
			$map['m.recommend_id'] =array('neq','0');
		}
		if($_REQUEST['uname']){
			$map['m.user_name'] = array("like",urldecode($_REQUEST['uname'])."%");
			$search['uname'] = urldecode($_REQUEST['uname']);	
		}
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("between",$timespan);
			$search['start_time'] = urldecode($_REQUEST['start_time']);	
			$search['end_time'] = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['bi.add_time'] = array("gt",$xtime);
			$search['start_time'] = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['bi.add_time'] = array("lt",$xtime);
			$search['end_time'] = $xtime;	
		}
		if(session('admin_is_kf')==1 && m.customer_id!=0)	$map['m.customer_id'] = session('admin_id');
		
		$field= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$list = M('borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($field)->where($map)->group('bi.investor_uid')->limit($Lsql)->select();
		
		$tfield= ' sum(bi.investor_capital) investor_capital,count(bi.id) total,bi.investor_uid,m.recommend_id,m.id,m.user_name';
		$tlist = M('transfer_borrow_investor bi')->join("{$this->pre}members m ON m.id = bi.investor_uid")->field($tfield)->where($map)->group('bi.investor_uid')->limit($Lsql)->find();
		
		foreach($list as $key => $v)
		{
			$list[$key]['investor_capital'] = $v['investor_capital']+$tlist['investor_capital'];
			$list[$key]['total'] = $v['total']+$tlist['total'];		
		}
		
		$list=$this->_listFilter($list);
		
		
		$row=array();
		$row[0]=array('序号','推广人','投资人','投资总金额','投资总笔数');
		$i=1;
		foreach($list as $v){
				$row[$i]['i'] = $i;
				$row[$i]['recommend_name'] = $v['recommend_name'];
				$row[$i]['user_name'] = $v['user_name'];
				$row[$i]['capital'] = $v['investor_capital'];
				$row[$i]['bishu'] = $v['total'];
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("datalistcard");
	}

	public function referees(){
		$admin_id = session('admin');
		$permissions = permissions($admin_id);
		if(!empty($_REQUEST['id'])){
			$map['m.id'] = $_REQUEST['id'];
			$search['id'] = $_REQUEST['id'];
		}
		if(!empty($_REQUEST['runame'])){
			$map['m.user_name']=$_REQUEST['runame'];
			$search['runame']=$_REQUEST['runame'];
		}
		if(!empty($_REQUEST['userphone'])){
			$map['m.user_phone']=$_REQUEST['userphone'];
			$search['userphone']=$_REQUEST['userphone'];
		}
		$this->pre = C('DB_PREFIX');
		import("ORG.Util.PageFilter");
		$count = M("members m")->field($field)->join("{$this->pre}member_info mi on mi.uid = m.id")->join("{$this->pre}members ms on ms.id = m.recommend_id")->where($map)->count('m.id');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";

		$field = "m.id,m.user_name,m.user_phone,m.reg_time,ms.id as recomid,ms.user_name as recommendname,mi.real_name";
		$list = M("members m")->field($field)->join("{$this->pre}member_info mi on mi.uid = m.id")->join("{$this->pre}members ms on ms.id = m.recommend_id")->limit($limit)->where($map)->order('m.id DESC')->select();
		foreach ($list as $key => $value) {
			$list[$key]['reg_time'] = date('Y-m-d',$value['reg_time']);
			$result = M('recommend_verify')->where("uid=".$value['id']." and status in(0,2)")->count('id');
			if($result>0){
				$list[$key]['num'] = 1;
			}
		}
		$this->assign('permissions',$permissions);
		$this->assign('pagebar',$page);
		$this->assign('search',$search);
		$this->assign('list',$list);
		$this->display();
	}

	public function refereemodify(){
		$this->pre = C('DB_PREFIX');
		setBackUrl();
		$id= intval($_GET['id']);
		$recom = M('members m')->field('ms.id,ms.user_name')->join("{$this->pre}members ms on ms.id = m.recommend_id")->where('m.id='.$id)->find();
		$this->assign("id",$id);
		$this->assign('recom',$recom);
		$this->display();
    }

   	public function refereephone(){
   		$map['user_phone']=$_POST['cellphone'];
   		$info = M('members')->where($map)->find();
   		if(empty($info)){
   			$res['status']=1;
   		}else{
   			$res['status']=2;
   			$res['user_name']=$info['user_name'];
   		}
   		echo json_encode($res);
   	}

    public function dorefereemodify(){
    	$admin_id = session('admin');
    	$id = intval($_POST['id']);
    	$rid = intval($_POST['rid']);
		$userphone = $_POST['user_phone'];
		$content = $_POST['textarea'];
		$info = M('members')->where("user_phone=".$userphone)->find();
		if($info['id']==$rid){
			$this->error('新推荐人不能为原推荐人');
		}
		if($info['id']==$id){
			$this->error('新推荐人不能为自己');
		}
		$data['uid']=$id;
		$data['new_recommend_id']=$info['id'];
		$data['applicant_id']=$admin_id;
		$data['status']=0;
		$data['application_time']=time();
		$data['old_recommend_id']=$rid;
		$newid = M('recommend_verify')->add($data);

		$data1['verify_id']=$newid;
		$data1['operation_id']=$admin_id;
		$data1['status']=0;
		$data1['content']=$content;
		$data1['time']=time();
		$newid1 = M('recommend_log')->add($data1);
        $this->assign('jumpUrl', __URL__."/referees".session('listaction'));
		if($newid&&$newid1){
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}
    }

    public function refereeslog()
    {
		setBackUrl();
        $uid = intval($_GET['id']);
        $map['m.id']=$uid;
        $map['r.status']=4;
        $field = "m.id,m.user_name,ms.user_name as recommendname,me.user_name as applicant,mem.user_name as newrecommend,mem.user_phone,r.review_time";
		$list = M("members m")->field($field)->join("{$this->pre}recommend_verify r on r.uid = m.id")->join("{$this->pre}members ms on ms.id = r.old_recommend_id")->join("{$this->pre}ausers me on me.id = r.applicant_id")->join("{$this->pre}members mem on mem.id = r.new_recommend_id")->where($map)->select();
		foreach ($list as $key => $value) {
			$list[$key]['review_time'] = date('Y-m-d,H:i:s',$value['review_time']);
		}
		$this->assign('list',$list);
		$this->assign("id",$uid);
		$this->display();
    }
    
	public function audit(){
		$admin_id = session('admin');
		$permissions = permissions($admin_id);
		$map['r.status']=array('neq','5');
		if(!empty($_REQUEST['id'])){
			$map['r.uid'] = $_REQUEST['id'];
			$search['id'] = $_REQUEST['id'];
		}
		if(!empty($_REQUEST['runame'])){
			$map['m.user_name']=$_REQUEST['runame'];
			$search['runame'] = $_REQUEST['runame'];
		}
		if(!empty($_REQUEST['userphone'])){
			$map['m.user_phone']=$_REQUEST['userphone'];
			$search['userphone'] = $_REQUEST['userphone'];
		}
		if(!empty($_REQUEST['status'])){
			$search['status']=intval($_REQUEST['status']);
			$status = intval($_REQUEST['status']);
			if($status==1){
				$map['r.status']=0;
			}else if($status==2){
				$map['r.status']=2;
			}else if($status==3){
				$map['r.status']=4;
			}else if($status==4){
				$map['r.status']=array('in','6,8');
			}
		}
		import("ORG.Util.PageFilter");
		$count = M('recommend_verify r')->where($map)->count('id');
		$p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
		$page = $p->show();
		$limit = "{$p->firstRow},{$p->listRows}";

        $field = "r.id,r.uid,m.user_name,m.user_phone,mem.user_name as recomname,me.user_name as newrecomname,a.user_name as appname,r.application_time,r.status";
        $list=M('recommend_verify r')->field($field)->join('lzh_members m on m.id = r.uid')->join('lzh_members me on me.id=r.new_recommend_id')->join('lzh_members mem on mem.id = r.old_recommend_id')->join('lzh_ausers a on a.id = r.applicant_id')->limit($limit)->where($map)->order('r.status ASC,r.application_time ASC')->select();
        foreach($list as $k=>$v){
        	$list[$k]['application_time']=date('Y-m-d',$v['application_time']);
        }
        $info=M('recommend_permissions r')->field('a.user_name,r.permissions')->join('lzh_ausers a on a.id = r.uid')->select();
		foreach($info as $k => $v){
			if($v['permissions']==2){
				$first_trial[] = $v['user_name'];
			}else if($v['permissions']==3){
				$second_trial[] = $v['user_name'];
			}
		}	
		$first_trial1 = implode(',', $first_trial);
		$second_trial1 = implode(',', $second_trial);
		$this->assign('first_trial',$first_trial1);
		$this->assign('second_trial',$second_trial1);
		$this->assign('permissions',$permissions);
		$this->assign('list',$list);
		$this->assign('search',$search);
		$this->assign("pagebar", $page);
		$this->display();
	}


	public function editaudit(){
		$admin_id = session('admin');
		$permissions = permissions($admin_id);
		$map['rv.id']=intval($_REQUEST['id']);
		if($permissions==2){
			$map['rl.status']==0;
		}else if($permissions==3){
			$map['rl.status']==2;
		}
		$field="rv.id,m.id as uid,m.user_name,me.id as recomid,mem.user_name as recomname,me.user_name as newrecomname,me.user_phone,rl.content";
		$list =M('recommend_verify rv')->field($field)->join('lzh_recommend_log rl on rv.id = rl.verify_id')->join('lzh_members m on m.id = rv.uid')->join('lzh_members me on me.id=rv.new_recommend_id')->join('lzh_members mem on mem.id = rv.old_recommend_id')->where($map)->find();

		$param['r.verify_id'] = intval($_REQUEST['id']);
		$res = M('recommend_log r')->join('lzh_ausers a on a.id = r.operation_id')->where($param)->select();
		foreach ($res as $k => $v) {
			if($v['status']==0){
				$res[$k]['content']="修改原因：".$v['content'];
			}else{
				$res[$k]['content']="审核意见：".$v['content'];
			}
			$res[$k]['time']=date('Y-m-d H:i:s',$v['time']);
		}
		$this->assign('res',$res);
		$this->assign('list',$list);
		$this->display();
	}

	public function delaudit(){
		$admin_id = session('admin');
		$permissions = permissions($admin_id);
		$id = intval($_REQUEST['id']);
		if($id){
			$map['status']=5;
			$res = M('recommend_verify')->where('id='.$id)->save($map);
			$para['operation_id']=$admin_id;
			$para['status']=5;
			$para['time']=time();
			$res1 = M('recommend_log')->where('verify_id='.$id)->save($para);
			if($res&&$res1){
				$this->success('撤销成功');
			}else{
				$this->error('撤销失败');
			}
		}else{
			$this->error('撤销失败');
		}
	}
	
	public function doeditaudit(){
		$admin_id = session('admin');
		$permissions = permissions($admin_id);
		$id = intval($_POST['id']);
		$status = intval($_POST['status']);
		$content = $_POST['content'];
		if(empty($content)){
			echo 'no';exit;
		}
		$uid = intval($_POST['uid']);
		$recomid = intval($_POST['recomid']);
		if($status==1){
			if($permissions==2){
				$data['status']=2;
				$data1['status']=2;
			}else if($permissions==3){
				$data['status']=4;
				$data1['status']=4;
			}
			$data['review_time']=time();
			$data1['verify_id']=$id;
			$data1['operation_id']=$admin_id;
			$data1['content']=$content;
			$data1['time']=time();
			if($permissions==3){
				$map['recommend_id']=$recomid;
				import('@.Phpconectjava.usersapi');
				$user = new usersapi();
				$option['usr_id'] = $uid;
				$option['recommend_id'] = $recomid;
				$option['platform_source'] = 1;
				$option['is_override'] = 1;
				$vo = $user->setrecommend($option);
				$vo1 = json_decode($vo,true);
				if(is_null($vo1['code'])){
					Log::write("服务器失败");
					echo "not";exit;
				}
				if($vo1['code']==0){
					$res = M('members')->where('id='.$uid)->save($map);
				}else{
					echo "not";exit;
				}
			}
			if($permissions==2||!empty($res)){
				M('recommend_verify')->where('id='.$id)->save($data);
				M('recommend_log')->add($data1);
			}else{
				echo "not";exit;
			}
			echo "ok";
		}else if($status==2){
			if($permissions==2){
				$data['status']=6;
				$data1['status']=6;
			}else if($permissions==3){
				$data['status']=8;
				$data1['status']=8;
			}
			M('recommend_verify')->where('id='.$id)->save($data);
			$data1['verify_id']=$id;
			$data1['operation_id']=$admin_id;
			$data1['content']=$content;
			$data1['time']=time();
			M('recommend_log')->add($data1);
			echo "ok";
		}
	}

	public function auditlog(){
		setBackUrl();
		$map['r.verify_id'] = intval($_REQUEST['id']);
		$list = M('recommend_log r')->join('lzh_ausers a on a.id = r.operation_id')->where($map)->select();
		foreach ($list as $k => $v) {
			if($v['status']==0){
				$list[$k]['content']="修改原因：".$v['content'];
			}else{
				$list[$k]['content']="审核意见：".$v['content'];
			}
			$list[$k]['time']=date('Y-m-d H:i:s',$v['time']);
		}
		$this->assign('list',$list);
		$this->display();
	}

	public function auditconf(){
		$map['permissions']=array('in','1,2,3');
		$list = M('recommend_permissions r')->field('m.user_name,r.permissions')->join('lzh_ausers m on m.id = r.uid')->where($map)->select();
		
		foreach ($list as $key => $value) {
			if($value['permissions']==1){
				$applicant[] = $value['user_name'];
			}else if($value['permissions']==2){
				$first_trial[] = $value['user_name'];
			}else if($value['permissions']==3){
				$second_trial[] = $value['user_name'];
			}
		}
		$applicant1 = implode(';', $applicant);
		$first_trial1 = implode(';', $first_trial);
		$second_trial1 = implode(';', $second_trial);
		$this->assign('applicant',$applicant1);
		$this->assign('first_trial',$first_trial1);
		$this->assign('second_trial',$second_trial1);
		$this->display();
	}

	public function auditconf1(){
		$map['permissions']=array('in','1,2,3');
		$list = M('recommend_permissions r')->field('m.user_name,r.permissions')->join('lzh_ausers m on m.id = r.uid')->where($map)->select();
		foreach ($list as $key => $value) {
			if($value['permissions']==1){
				$applicant[] = $value['user_name'];
			}else if($value['permissions']==2){
				$first_trial[] = $value['user_name'];
			}else if($value['permissions']==3){
				$second_trial[] = $value['user_name'];
			}
		}
		$applicant1 = implode(';', $applicant);
		$first_trial1 = implode(';', $first_trial);
		$second_trial1 = implode(';', $second_trial);
		
		$this->assign('applicant',$applicant1);
		$this->assign('first_trial',$first_trial1);
		$this->assign('second_trial',$second_trial1);
		$this->display();
	}

	public function ajaxaudit(){
		$applicant = convert($_POST['applicant']);
		$first_trial = convert($_POST['first_trial']);
		$second_trial = convert($_POST['second_trial']);
		foreach ($applicant as $key => $value) {
			$map['user_name']=$value;
			$uid1 = M('ausers')->where($map)->find();
			 if(empty($uid1)){
			 	$res['status']=1;
			 	$res['name']=$value;
			 }else{
			 	$res['status']=0;
			 }
		}
		foreach ($first_trial as $key => $value) {
			$map['user_name']=$value;
			$uid2 = M('ausers')->where($map)->find();
			 if(empty($uid2)){
			 	$res['status1']=1;
			 	$res['name1']=$value;
			 }else{
			 	$res['status1']=0;
			 }
		}
		foreach ($second_trial as $key => $value) {
			$map['user_name']=$value;
			$uid3 = M('ausers')->where($map)->find();
			 if(empty($uid3)){
			 	$res['status2']=1;
			 	$res['name2']=$value;
			 }else{
			 	$res['status2']=0;
			 }
		}
		echo json_encode($res);
	}

	public function doauditconf(){
		$admin_id = session('admin');
		$applicant = convert($_POST['applicant']);
		$first_trial = convert($_POST['first_trial']);
		$second_trial = convert($_POST['second_trial']);
		$info = M('recommend_permissions r')->field('r.id,r.permissions,a.user_name')->join('lzh_ausers a on a.id = r.uid')->select();
		foreach($info as $k=>$v){
			if(!in_array($v['user_name'], $applicant)&&$v['permissions']==1){
				$par=M('recommend_permissions')->where('id='.$v['id'])->delete();
			}
			if(!in_array($v['user_name'], $first_trial)&&$v['permissions']==2){
				$par1=M('recommend_permissions')->where('id='.$v['id'])->delete();
			}
			if(!in_array($v['user_name'], $second_trial)&&$v['permissions']==3){
				$par2=M('recommend_permissions')->where('id='.$v['id'])->delete();
			}
		}
		if(empty($applicant[0])||empty($first_trial[0])||empty($second_trial[0])){
			$this->error("操作员不能为空");
		}	
		if(count($applicant)>5){
			$this->error("申请人最多为5人");
		}
		if(count($first_trial)>3){
			$this->error("一审人员最多为3人");
		}
		if(count($second_trial)>3){
			$this->error("二审人员最多为3人");
		}
		foreach ($applicant as $key => $value) {
			$map['user_name']=$value;
			$uid1 = M('ausers')->where($map)->find();
			 if(empty($uid1)){
			 	$this->error("申请人员".$value."用户不存在");
			 }
			$appuid = M('recommend_permissions')->where('uid='.$uid1['id'])->find();
			$data['uid']=$uid1['id'];
			$data['permissions']=1;
			$data['modify_time']=time();
			$data['modify_uid']=$admin_id;
			if(!$appuid){
				$res = M('recommend_permissions')->add($data);
			}
		}
		foreach ($first_trial as $key => $value) {
			$map['user_name']=$value;
			$uid2 = M('ausers')->where($map)->find();
			 if(empty($uid2)){
			 	$this->error("一审人员".$value."用户不存在");
			 }
			$firuid = M('recommend_permissions')->where('uid='.$uid2['id'])->find();
			$data1['uid']=$uid2['id'];
			$data1['permissions']=2;
			$data1['modify_time']=time();
			$data1['modify_uid']=$admin_id;
			if(!$firuid){
				$res1 = M('recommend_permissions')->add($data1);
			}
		}
		foreach ($second_trial as $key => $value) {
			$map['user_name']=$value;
			$uid3 = M('ausers')->where($map)->find();
			 if(empty($uid3)){
			 	$this->error("二审人员".$value."用户不存在");
			 }
			$secuid = M('recommend_permissions')->where('uid='.$uid3['id'])->find();
			$data2['uid']=$uid3['id'];
			$data2['permissions']=3;
			$data2['modify_time']=time();
			$data2['modify_uid']=$admin_id;
			if(!$secuid){
				$res2 = M('recommend_permissions')->add($data2);
			}
		}
		if(empty($res)&&empty($res1)&&empty($res2)&&empty($par)&&empty($par1)&&empty($par2)){
			$this->error("保存失败",__URL__."/auditconf");
		}else{
			$this->success("保存成功",__URL__."/auditconf");
		}
		
	}

	
}
?>