<?php
// 全局设置
class CommonAction extends ACommonAction
{
    private function  create_pay_list($id){
        $map['uid']=$id;
        $count = M('member_moneylog')->where($map)->count('uid');
        import("ORG.Util.Page");
        $p = new Page($count,20);
        $page = $p->ajax_show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $list=M("member_moneylog")->field("type,affect_money,account_money,back_money,collect_money,freeze_money,info,add_time")->limit($limit)->order('id asc')->where($map)->select();
        $tmp=array();
        foreach($list as $key=>$val){
            $list[$key]['time']=date("Y-m-d H:i:s",$val['add_time']);
            if($val['affect_money']>0){
                $list[$key]['affect_money']="<font color='blue'>".$val['affect_money']."</font>";
            }else{
                $list[$key]['affect_money']="<font color='red'>".$val['affect_money']."</font>";
            }
            if($val['type']==3){
               $list[$key]['info']="客户充值";
            }

            //设置颜色
            if($key==0){
                $tmp['account_money']=$list[$key]['account_money'];
                $tmp['back_money']=$list[$key]['back_money'];
                $tmp['collect_money']=$list[$key]['collect_money'];
                $tmp['freeze_money']=$list[$key]['freeze_money'];
                if($list[$key]['account_money']!=0)  $list[$key]['account_money']="<font color='blue'>".$val['account_money']."</font>";
                if($list[$key]['back_money']!=0)      $list[$key]['back_money']="<font color='blue'>".$val['back_money']."</font>";
                if($list[$key]['collect_money']!=0)  $list[$key]['collect_money']="<font color='blue'>".$val['collect_money']."</font>";
                if($list[$key]['freeze_money']!=0)   $list[$key]['freeze_money']="<font color='blue'>".$val['freeze_money']."</font>";

            }else{

                if($list[$key]['account_money']!=$tmp['account_money']){
                    $tmp['account_money']= $list[$key]['account_money'];
                    $list[$key]['account_money']="<font color='blue'>".$val['account_money']."</font>";
                }else{
                    $tmp['account_money']= $list[$key]['account_money'];
                }
                if($list[$key]['back_money']!=$tmp['back_money']){
                    $tmp['back_money']= $list[$key]['back_money'];
                    $list[$key]['back_money']="<font color='blue'>".$val['back_money']."</font>";
                }else{
                    $tmp['back_money']= $list[$key]['back_money'];
                }

                if($list[$key]['collect_money']!=$tmp['collect_money']) {
                    $tmp['collect_money']= $list[$key]['collect_money'];
                    $list[$key]['collect_money']="<font color='blue'>".$val['collect_money']."</font>";
                }else{
                    $tmp['collect_money']= $list[$key]['collect_money'];
                }


                if($list[$key]['freeze_money']!=$tmp['freeze_money']){
                    $tmp['freeze_money']= $list[$key]['freeze_money'];
                    $list[$key]['freeze_money']="<font color='blue'>".$val['freeze_money']."</font>";
                } else{
                    $tmp['freeze_money']= $list[$key]['freeze_money'];
                }

            }
        }
        $info=array();
        $info["page"]=$page;
        $info["list"]=$list;
        return $info;
    }
    private function  create_page_html($info){
        $uid=intval($_GET['id']);
        $html='   <table id="area_list" width="100%" border="0" cellspacing="0" cellpadding="0" height="3rem">';
        $html.= ' <tr style="background-color: #CCC">';
        $html.='    <th style="width:20%;text-align: center">时间</th>';
        $html.='     <th style="width:10%;text-align: center">流动资金</th>';
        $html.='       <th style="width:10%;text-align: center">充值资金</th>';
        $html.='       <th style="width:10%;text-align: center">回款资金</th>';
        $html.='       <th style="width:10%;text-align: center">冻结资金</th>';
        $html.='       <th style="width:10%;text-align: center">代收资金</th>';
        $html.='       <th style="width:30%;text-align: center">备注</th>';
        $html.='</tr>';
        foreach($info['list'] as $key=>$val){
            if($key%2==1)
                 $html.='<tr class="list_info" style="background-color: #CCC"  >';
            else
                $html.='<tr class="list_info" >';
            $html.='<td style="width:20%;text-align: center">'.$val['time'].'</td>';
            $html.='<td style="width:10%;text-align: center">'.$val['affect_money'].'</td>';
            $html.='<td style="width:10%;text-align: center">'.$val['account_money'].'</td>';
            $html.='<td style="width:10%;text-align: center">'.$val['back_money'].'</td>';
            $html.='<td style="width:10%;text-align: center">'.$val['freeze_money'].'</td>';
            $html.='<td style="width:10%;text-align: center">'.$val['collect_money'].'</td>';
            $html.='<td style="width:30%;text-align: center">'.$val['info'].'</td>';
            $html.="</tr>";
        }
        $html.=" </table>";
        $html.="<div style='text-align: center;margin-top: 1rem;margin-bottom: 1rem'>";
        $html.='<a class="btn_a"  style="float: right" href="/Admin/common/member?id='.$uid.'&execl=execl"><span>将当前条件下数据导出为Excel</span></a>';
        $html.=$info['page'];
        $html.="</div>";
        return $html;


    }
    public  function save_member_info(){
        $uid=intval($_GET['id']);
        $map['uid']=$uid;
        $list=M("member_moneylog")->field("type,affect_money,account_money,back_money,collect_money,freeze_money,info,add_time")->order('id asc')->where($map)->select();
        import("ORG.Io.Excel");
        $row=array();
        $row[0]=array('时间','流动资金','充值资金','回款资金','冻结资金','代收资金','备注');
        $i=1;
        foreach($list as $key=>$val){
            $row[$i]['time']=date("Y-m-d H:i:s",$val['add_time']);
            $row[$i]['affect_money']=$val['affect_money'];
            $row[$i]['account_money']=$val['account_money'];
            $row[$i]['back_money']=$val['back_money'];
            $row[$i]['freeze_money']=$val['freeze_money'];
            $row[$i]['collect_money']=$val['collect_money'];
            $row[$i]['info']=$val['info'];
            $i++;
        }
        $xls = new Excel_XML('UTF-8', false, 'manbiao');
        $xls->addArray($row);
        $name=$uid."mem_info";
        $xls->generateXML($name);
        exit;
    }
    public function member(){
		$utype = C('XMEMBER_TYPE');
        if(isset($_GET['p'])){
            $uid=intval($_GET['id']);
            $info=$this->create_pay_list($uid);
           echo $this->create_page_html($info);exit;
        }
        else if($_REQUEST['execl']=="execl"){
            $this->save_member_info();exit;
        }
		$area=get_Area_list();
		$uid=intval($_GET['id']);
        $this->assign("id",$uid);
        $info=$this->create_pay_list($uid);
        $this->assign("list",$info['list']);
        $this->assign("page",$info['page']);
        $field = 'm.user_email,m.customer_name,m.user_phone,m.id,m.credits,m.is_ban,m.is_vip,m.user_type,m.user_regtype,m.user_name,m.integral,m.active_integral,ms.user_name as recommendname,mi.*,mm.*,mb.*';
		$vo = M('members m')->field($field)
		->join("{$this->pre}members ms ON ms.id=m.recommend_id")
		->join("{$this->pre}member_info mi ON mi.uid=m.id")
		->join("{$this->pre}member_money mm ON mm.uid=m.id")
		->join("{$this->pre}member_banks mb ON mb.uid=m.id")
		->where("m.id={$uid}")->find();
		$vo['province'] = $area[$vo['province']];
		$vo['city'] = $area[$vo['city']];
		$vo['area'] = $area[$vo['area']];
		$vo['province_now'] = $area[$vo['province_now']];
		$vo['city_now'] = $area[$vo['city_now']];
		$vo['area_now'] = $area[$vo['area_now']];
		$vo['is_ban'] = ($vo['is_ban']==0)?"未冻结":"<span style='color:red'>{$vo['bank_num']}</span>";
		$vo['user_type'] = $utype[$vo['user_type']];
        if($vo['is_vip']==1){
            $vo['is_vip'] = "<span style='color:red'>投资人/借款人</span>";
         }else{
            $vo['is_vip'] ="投资人";
         }
        if($vo['user_regtype'] == 2){
            $company = M('members_company')->where('uid = '.$uid)->find();
            $vo['agent_name'] = $company['agent_name'];
            $vo['alicense_no'] = $company['alicense_no'];
            $vo['agent_mobile'] = $company['agent_mobile'];
            $vo['cert_no'] = $company['cert_no'];
            $vo['legal_person_phone'] = $company['legal_person_phone'];
            $vo['legal_person'] = $company['legal_person'];
            $vo['company_name'] = $company['company_name'];
            $vo['address'] = $company['address'];            
            $vo['email'] = $company['email'];            
        }
        //$vo['money_collect'] = M('investor_detail')->where(" investor_uid={$uid} AND status =7 ")->sum("capital+interest-interest_fee");
        //$vo['money_need'] = M('investor_detail')->where(" borrow_uid={$uid} AND status in(4,7) ")->sum("capital+interest");
		//$vo['money_all'] = $vo['account_money'] + $vo['money_freeze'] + $vo['money_collect'] - $vo['money_need'];
		
		$this->assign("capitalinfo",getMemberBorrowScan($uid));
		$this->assign("wc",getUserWC($uid));
        $this->assign("credit", getCredit($uid));
        $this->assign("vo",$vo);
		$this->assign("user",$vo['user_name']);

		//*******2013-11-23*************
		$minfo =getMinfo($uid,true);
        $this->assign("minfo",$minfo); 

		$this->assign('benefit', get_personal_benefit($uid)); //收益相关
		$this->assign('out', get_personal_out($uid)); //支出相关
		$this->assign('pcount', get_personal_count($uid));
		
		import("@.Oauth.sina.Sina");
		$sina = new Sina();
		//是否设置新浪支付密码
		$this->assign('issetpaypwd', $sina->issetpaypwd($uid));
		//是否绑卡
		$this->assign('bind_card', queryusercard($uid));
		//新浪审核状态
		$company_status = M('members_status')->where(array('uid' => $uid))->getField('company_status');
		$this->assign('company_status', $company_status);
		
        $this->display();
    }
	
	public function sms(){
		$utype = C('XMEMBER_TYPE');
        if(in_array(intval($_GET['tab']), array(1,2,3,4)))  $tab = intval($_GET['tab']);
        else    $tab = 1;

        $this->assign("tab", $tab);
        $this->assign("user_name", text($_GET['user_name']));
        $this->assign("admin_id", $this->admin_id);
        $this->display();
    }

    public function sendsms(){
    	$info = cnsubstr(text($_POST['info']), 500);
    	$title = cnsubstr($info, 20);
    	if ($info == "") 	exit("发送内容不可为空");

        $smsLog['admin_id'] = $_SESSION['admin_id'];
        $smsLog['admin_real_name'] = $_SESSION['admin_user_name'];

        $smsLog['title'] = $title;
        $smsLog['content'] = $info;
        $smsLog['add_time'] = time();

    	if(intval($_POST['sms'])==1){//账户通讯
    		$user_name = text($_POST['user_name']);
    		$type = text($_POST['type']);

    		$user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where(" m.user_name = '".$user_name."' ")->find();
    		if (!$user)		exit("找不到用户$user_name");

            if (stripos( $type,"1") && $user['email_status']==1){//邮件
                $sm = sendemail($user['user_email'],$title,$info);
                if($sm) $smsLog['user_email'] = $user['user_email'];
            }

            if (stripos( $type,"2") && $user['phone_status']==1){//短信
                $ss = sendsms($user['user_phone'],$info);
                if($ss) $smsLog['user_phone'] = $user['user_phone'];
            }

            if (stripos( $type,"4")){//站内信
                $si = true;
                addInnerMsg($user['id'],$title,$info);
                $smsLog['user_name'] = $user_name;
            }

            if($sm || $ss || $si){
                M('smslog')->add($smsLog);
				alogs("Smslog",0,1,'成功执行了会员账户通讯通知操作！');//管理员操作日志
                exit("发送成功");
            }else{
				alogs("Smslog",0,0,'执行会员账户通讯通知操作失败！');//管理员操作日志
                exit("发送失败");
            }
    	}elseif(intval($_POST['sms'])==2){//具体通讯
    		$email = text($_POST['email']);
    		$phone = text($_POST['phone']);

    		if ($phone){
                $ss = sendsms($phone,$info);
                if($ss) $smsLog['user_phone'] = $phone;
            }

    		if ($email){
                $sm = sendemail($email,$title,$info);
                if($sm) $smsLog['user_email'] = $email;
            }

            if($sm || $ss ){
                M('smslog')->add($smsLog);
				alogs("Smslog",0,1,'成功执行了单个会员通讯通知操作！');//管理员操作日志
                exit("发送成功");
            }else{
				alogs("Smslog",0,0,'执行单个会员通讯通知操作失败！');//管理员操作日志
                exit("发送失败");
            }
    	}
    }

    public function sendgala(){
        set_time_limit(0);//设置脚本最大执行时间

        $info = cnsubstr(text($_POST['info']),500);
        $title = cnsubstr($info,12);
        if ($info == "")    exit("发送内容不可为空");

        $smsLog['admin_id'] = $_SESSION['admin_id'];
        $smsLog['admin_real_name'] = $_SESSION['admin_user_name'];

        $smsLog['title'] = $title;
        $smsLog['content'] = $info;
        $smsLog['add_time'] = time();

        $type = text($_POST['type']);
        $user_name = intval($_POST['user_name']);

        if ($user_name==2){//VIP会员
            $map = " user_leve=1 AND time_limit>".time();
            $user = "VIP会员";
        }elseif ($user_name==3){//非VIP会员
            $map = " user_leve=0 OR time_limit<".time();
            $user = "非VIP会员";
        }else{//所有会员
            $map = ""; 
            $user = "所有会员";
        }

        if(stripos( $type,"1")) $smsLog['user_email'] = $user;
        if(stripos( $type,"2")) $smsLog['user_phone'] = $user;
        if(stripos( $type,"4")) $smsLog['user_name'] = $user;
        M('smslog')->add($smsLog);
       
        $user = M('members m')->field(" m.id,m.user_email,m.user_phone,ms.email_status,ms.phone_status ")->join(" lzh_members_status ms ON m.id=ms.uid ")->where($map)->select();
        
        if (stripos( $type,"4")) {//站内信
            foreach ($user as $k => $v) {
                addInnerMsg($v['id'],$title,$info);
            }
        }

        /*if (stripos( $type,"1")) {//邮件
            $i= 1;
            foreach ($user as $k => $v) {
                if($v['email_status']==1){
                    $to[floor($i/160)] .=$v['user_email'].",";
                    $i++;
                }
            }

            foreach ($to as $key => $val) {
                $val = substr($val, 0, strlen($val)-1 );

                if($key<6)     sendemail2($val,$title,$info);
                else           sendemail($val,$title,$info);
            }
        }*/

        if (stripos( $type,"2")) {//短信
            $i= 1;
            foreach ($user as $k => $v) {
                if($v['phone_status']==1){
                    $phone[floor($i/150)] .=$v['user_phone'].",";
                    $i++;
                }
            }
            //var_dump($phone);

            foreach ($phone as $key2 => $val2) {
                $val2 = substr($val2, 0, strlen($val2)-1 );
                sendsms($val2,$info);
                // var_dump("$val2,$info");
            }
        }
		alogs("Smslog",0,1,'对'.$user.'执行通讯通知操作成功！');//管理员操作日志
        exit("发送成功");
    }

    public function smslog(){
        $data = M('ausers')->field("id,real_name")->select();
        foreach ($data as $k => $v) {
            $admin_data[$v['id']] = $v['real_name'];
        }

        if(!empty($_GET['admin_id']))       $map['admin_id'] = intval($_GET['admin_id']);
        if(!empty($_GET['user_name']))      $map['user_name'] = text($_GET['user_name']);
        if(!empty($_GET['user_email']))     $map['user_email'] = text($_GET['user_email']);
        if(!empty($_GET['user_phone']))     $map['user_phone'] = text($_GET['user_phone']);

        //分页处理
        import("ORG.Util.Page");
        $count = M('smslog')->where($map)->count();
        $p = new Page($count, 10);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        
        $list = M('smslog')->where($map)->order("id desc")->limit($Lsql)->select();
            // ->join("{$per}members m ON m.id=d.borrow_uid")

        $this->assign("page", $page);
        $this->assign("list", $list);
        $this->assign('admin_data',$admin_data);
        $this->assign('map',$map);

        $data['html'] = $this->fetch('smslog_res');
        exit(json_encode($data));
    }



}
?>