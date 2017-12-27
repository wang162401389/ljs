<?php
// 本类由系统自动生成，仅供测试用途
class AgreementAction extends MCommonAction {

    private function mask_name($str){
        $count=mb_strlen($str,"UTF-8");
        if($count==2){
            $name=mb_substr($str,0,1,"UTF-8")."*";
        }else{
            $num=$count-2;
            $xin="";
            for($i=0;$i<$num;$i++){
                    $xin.="*";
            }
            $name=mb_substr($str,0,1,"UTF-8").$xin.mb_substr($str,$count-1,1,"UTF-8");
        }
        return $name;
    }
    private function mask_carid($str){
        return substr_replace($str,'************',4,12);
    }
    
    public function downfile(){
        $per = C('DB_PREFIX');
        $borrow_id=intval($_GET['id']);
        //$invest_id=intval($_GET['id']);

        //判断是否为担保机构
        $is_danbao = M("members_company")->where("is_danbao = 1 AND uid = {$this->uid}")->count();
        if($is_danbao>0){
            $iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id} ")->select();
        }else{
        //所以投标记录
            $iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("(investor_uid={$this->uid} OR borrow_uid={$this->uid}) AND borrow_id={$borrow_id} ")->select();
        }

        //标详情
        if($iinfos==""){
            echo '<script>alert("您不是投资人与借款人，您无权查看该合同");history.go(-1)</script>';
        }
        $iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where(" borrow_id={$borrow_id} ")->select();

        $binfo = M('borrow_info')->field('id,borrow_use,repayment_type,borrow_duration,borrow_duration_txt,borrow_uid,borrow_type,borrow_use,borrow_money,full_time,add_time,borrow_interest_rate,deadline,second_verify_time,warehousing,borrow_duration_txt,product_type')->find($borrow_id);
        //借款人信息
        $uid=$binfo['borrow_uid'];
        $mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_regtype,m.user_name,mi.idcard')->where("m.id=$uid")->find();
        if($this->uid != $uid){
            if(empty($mBorrow["real_name"])){
        		 $co_info=M('members_company mc')->field('mc.company_name,mc.license_no')->where("mc.uid={$uid}")->find();
        		 $mBorrow["real_name"]=substr_replace($co_info["company_name"],'************',9,50);
                 $mBorrow["idcard"]=substr_replace($co_info["license_no"],'*********',4,20);
                 $mBorrow["is_com"]=1;
        	}else{
                $mBorrow["real_name"]=substr_replace($mBorrow["real_name"],'**',3,20);
            }
            $mBorrow["idcard"]=$this->mask_carid($mBorrow['idcard']);
        }else{
            if(empty($mBorrow["real_name"])){
                 $co_info=M('members_company mc')->field('mc.company_name,mc.license_no')->where("mc.uid={$uid}")->find();
                 $mBorrow["real_name"]=$co_info["company_name"];
                 $mBorrow["idcard"]=$co_info["license_no"];
                 $mBorrow["is_com"]=1;
            }
        }
        $mInvests=array();
        foreach ($iinfos as  $key =>$val){
            $mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name,m.user_phone,m.user_regtype,mi.idcard')->where("m.id={$val['investor_uid']}")->find();
            $detail = M('investor_detail d')->field('sum(d.capital+d.interest-d.interest_fee) benxi')->where("d.borrow_id={$borrow_id} AND d.invest_id={$val['id']}")->group('d.invest_id')->find();
            if($val['investor_uid']==$this->uid){
                $mInvests[$key]['user_regtype']=$mInvest['user_regtype'];
                if ($mInvests[$key]['user_regtype'] == 1) {
                 $mInvests[$key]['real_name']=$mInvest['real_name'];
                } elseif($mInvests[$key]['user_regtype'] == 2) {
                 $mCompany = M('members_company mc')->field('mc.company_name')->where("mc.uid={$val['investor_uid']}")->find();
                 //Log::write(var_export($mCompany,true));
                 $mInvests[$key]['real_name']=$mCompany['company_name'];
                }
             
                $mInvests[$key]['user_phone']=$mInvest['user_phone'];
                $mInvests[$key]['user_name']=$mInvest['user_name'];
                $mInvests[$key]['idcard']=$mInvest['idcard'];
                $mInvests[$key]['benxi']=$detail['benxi'];
                $mInvests[$key]['investor_capital']=$val['investor_capital'];
            }else{
                $mInvests[$key]['user_regtype']=$mInvest['user_regtype'];
                if ($mInvests[$key]['user_regtype'] == 1) {
                    $mInvests[$key]['real_name']=$this->mask_name($mInvest['real_name']);
                } elseif($mInvests[$key]['user_regtype'] == 2) {
                    $mCompany = M('members_company mc')->field('mc.company_name')->where("mc.uid={$val['investor_uid']}")->find();
                    $mInvests[$key]['real_name']=$this->mask_name($mCompany['company_name']);
                }
                $mInvests[$key]['user_phone']=$this->mask_name($mInvest['user_phone']);
                $mInvests[$key]['user_name']=$this->mask_name($mInvest['user_name']);
                $mInvests[$key]['idcard']=$this->mask_carid($mInvest['idcard']);
                $mInvests[$key]['benxi']=$detail['benxi'];
                $mInvests[$key]['investor_capital']=$val['investor_capital'];
            }
        }

        //$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$iinfo['investor_uid']}")->find();
        $jgcode = M("members m")->join("{$per}member_department_info jg ON jg.uid=m.id")->field('jg.institution_code')->where("m.id={$borrow_uid}")->find();
        
        //if(!is_array($iinfo)||!is_array($binfo)||!is_array($mBorrow)||!is_array($mInvest)) exit;
        
        //$detail = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();
        
        $detailinfo = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,(d.capital+d.interest-d.interest_fee) benxi,d.capital,d.interest,d.interest_fee,d.sort_order,d.deadline')->where("d.borrow_id={$borrow_id}")->select();
        $repay=array();
        foreach ($detailinfo as $key =>$val){
            $repay['sort_order']=$val['sort_order'];
            $repay['benxi']+=round($val['benxi'],2);
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
        $binfo['borrow_use_no'] =$binfo['borrow_use'];
        $binfo['borrow_use'] = $type1[$binfo['borrow_use']];
        
        $ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
        
        $this->assign("ht",$ht);
        $type = $borrow_config['REPAYMENT_TYPE'];
        $binfo['repayment_name'] = $type[$binfo['repayment_type']];
        
        $iinfo = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->find();
        $iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
        $memberinfo = M('members')->find($uid);
        $this->assign("bid","CCFAX");
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
                $this->assign("a_date", intval($duration_list[0]) + intval($duration_list[1]));
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
            }else if(count($duration_list)==1){
                if($binfo['product_type']==1){
                    $show_type='A';
                    $this->assign("a_date",intval($duration_list[0]));
                    $this->assign("tidan_rate",$add_info['frist_rate']);
                }else if($binfo['product_type']==3||$binfo['product_type']==7 || $binfo['product_type']==8){
                    $show_type='B';
                    $this->assign("b_date",intval($duration_list[0]));
                    $this->assign("xianhuo_rate",$add_info['frist_rate']);
                }else if($binfo['product_type']==2){ //本来打算提单标，后面转现货
                    $show_type='A';
                    $this->assign("a_date",intval($duration_list[0]));
                    $this->assign("tidan_rate",$add_info['frist_rate']);
                }
            }
            $this->assign("borrow_interest_rate",$add_info["borrow_interest_rate"]);
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
        //信用标收益列表
        $investlist = M("investor_detail")->field('(capital+interest-interest_fee) benxi,capital,interest,interest_fee,sort_order,deadline')->where("borrow_id={$borrow_id} AND investor_uid={$this->uid}")->select();
        $this->assign('investlist',$investlist);
        $flag=C('START_FLAG');

        if($binfo['product_type']==5){//分期购
            $where1['uid']=$uid;
            $info=M("members_company")->where($where1)->field("license_no")->find();
            $this->assign("license_no",$info["license_no"]);
            $r_info=getBorrowInvest($borrow_id,$uid);
            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
            $this->assign("r_info",$r_info["list"]);
            if($binfo["borrow_use_no"] == 9){
                $this->display("newfqgagreement");
            }else{
                $this->display("fqghetong");
            }
        
        }else if($binfo['product_type']==8){//保金链
            // $this->buildHtml("contract_".$borrow_id, "html/contract/", "bjlhetong");
            $this->assign("repayment_type",$binfo['repayment_type']);
            $this->display("bjlhetong");
        }else if($binfo['product_type']==10){//质金链(保)
            // $this->buildHtml("contract_".$borrow_id, "html/contract/", "bjlhetong");
            $this->assign("repayment_type",$binfo['repayment_type']);
            $this->display("cjlhetong");
        }else if($binfo['product_type']==7){//优金链
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
            $this->display("credithetong");
        }else if($binfo['product_type']==4){//融金链
            $where1['uid']=$uid;
            $info=M("members_company")->where($where1)->field("license_no")->find();
            $this->assign("license_no",$info["license_no"]);
            $r_info=getBorrowInvest($borrow_id,$uid);
            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
            $this->assign("r_info",$r_info["list"]);
            if($borrow_id > $flag['break_point_3']){
                $this->display("rjlhetong");
            }else{
                 $this->display("monthhetong");
            }
        }else if($binfo['product_type']<4&&$borrow_id > $flag['break_point_3']){//质金链
            $this->display("zjlhetong");
        }else if(isset($newhetong)){
            if ($borrow_id <= $flag['break_point_1']) {
                $this->display("newhetong");
            }
            if ($flag['break_point_1'] < $borrow_id && $borrow_id <= $flag['break_point_2']) {
                $this->display("newhetong1");
            }
            if ($borrow_id > $flag['break_point_2'] && $borrow_id <= $flag['break_point_3']) {
                $this->display("agreement20160414");
            }
        } else{
            $this->display("index");
        }
    }
	
	 public function downliuzhuanfile(){
		$per = C('DB_PREFIX');
		$borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";
		$type = $borrow_config['REPAYMENT_TYPE'];

		$invest_id=intval($_GET['id']);
		
		$iinfo = M("transfer_borrow_investor")->field(true)->where("investor_uid={$this->uid} AND id={$invest_id}")->find();

		$binfo = M('transfer_borrow_info')->field(true)->find($iinfo['borrow_id']);
		$tou =  M('transfer_investor_detail')->where(" borrow_id={$iinfo['borrow_id']} AND investor_uid={$this->uid} ")->find();
		
		$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$binfo['borrow_uid']}")->find();
		$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$iinfo['investor_uid']}")->find();
		
		if(!is_array($tou)) $mBorrow['real_name'] = hidecard($mBorrow['real_name'],5);

		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$this->assign("bid","LZBHT-".str_repeat("0",5-strlen($binfo['id'])).$binfo['id']);
		
		$detailinfo = M('transfer_investor_detail d')->join("{$per}transfer_borrow_investor bi ON bi.id=d.invest_id")->join("{$per}members m ON m.id=d.investor_uid")->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total,m.user_name,bi.investor_capital,bi.add_time')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();
		
		$time = M('transfer_borrow_investor')->field('id,add_time')->where("borrow_id={$iinfo['borrow_id']} order by add_time asc")->limit(1)->find();
		
		$deadline_last = strtotime("+{$binfo['borrow_duration']} month",$time['add_time']);
		
		$this->assign('deadline_last',$deadline_last);
		$this->assign('detailinfo',$detailinfo);

		$type1 = $this->gloconf['BORROW_USE'];
		$binfo['borrow_use'] = $type1[$binfo['borrow_use']];



		$type = $borrow_config['REPAYMENT_TYPE'];
		//echo $binfo['repayment_type'];
		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
		
		
		
		$this->assign('iinfo',$iinfo);
		$this->assign('binfo',$binfo);
		$this->assign('mBorrow',$mBorrow);
		$this->assign('mInvest',$mInvest);

		$detail_list = M('transfer_investor_detail')->field(true)->where("invest_id={$invest_id}")->select();
		$this->assign("detail_list",$detail_list);

		$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
		$this->assign("ht",$ht);
		$this->display("transfer");
    }

 public function downdingtoubao(){
		$per = C('DB_PREFIX');
		$borrow_config = require C("APP_ROOT")."Conf/borrow_config.php";
		$type = $borrow_config['REPAYMENT_TYPE'];

		$invest_id=intval($_GET['id']);
		if(empty($invest_id)) $this->display("dingtoubao");
		$datag = get_global_setting();
		$fee_rate = $datag['fee_invest_manage'];//投资者成交管理费费率
		$iinfo = M("transfer_borrow_investor")->field(true)->where("investor_uid={$this->uid} AND id={$invest_id}")->find();

		$binfo = M('transfer_borrow_info')->field(true)->find($iinfo['borrow_id']);
		$tou =  M('transfer_investor_detail')->where("invest_id={$iinfo['id']} AND investor_uid={$this->uid} ")->find();
		$detail = M("transfer_detail")->field(true)->find($iinfo['borrow_id']);
		$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,mi.address,mi.cell_phone,m.user_name')->where("m.id={$binfo['borrow_uid']}")->find();
		$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,mi.address,mi.cell_phone,mi.idcard,m.user_name,m.user_email')->where("m.id={$iinfo['investor_uid']}")->find();
		$danbao = M('article')->field('id,title,art_img')->where("id={$binfo['danbao']}")->find();
		if(!is_array($tou)) $mBorrow['real_name'] = hidecard($mBorrow['real_name'],4);

		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$this->assign("bid","LZBHT-".str_repeat("0",5-strlen($iinfo['id'])).$iinfo['id']);
		
		$detailinfo = M('transfer_investor_detail d')->join("{$per}transfer_borrow_investor bi ON bi.id=d.invest_id")->join("{$per}members m ON m.id=d.investor_uid")->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total,m.user_name,bi.investor_capital,bi.add_time')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();
		
		$time = M('transfer_borrow_investor')->field('id,add_time,deadline')->where("borrow_id={$iinfo['borrow_id']} order by add_time asc")->limit(1)->find();
		
		$deadline_last = $time['deadline'];
		
		$this->assign('deadline_last',$deadline_last);
		$this->assign('detailinfo',$detailinfo);

		$type1 = $this->gloconf['BORROW_USE'];
		$binfo['borrow_use'] = $type1[$binfo['borrow_use']];



		$type = $borrow_config['REPAYMENT_TYPE'];
		//echo $binfo['repayment_type'];
		$binfo['repayment_name'] = $type[$binfo['repayment_type']];

		$iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
		$this->assign("ht",$ht);
		$this->assign("fee_rate",$fee_rate);
		$this->assign("Bconfig",$Bconfig);
		$this->assign('iinfo',$iinfo);
		$this->assign('binfo',$binfo);
		$this->assign('tou',$tou);
		$this->assign('mBorrow',$mBorrow);
		$this->assign('mInvest',$mInvest);
		$this->assign('danbao',$danbao);
		$this->assign('detail',$detail);

		$detail_list = M('transfer_investor_detail')->field(true)->where("invest_id={$invest_id}")->select();
		$this->assign("detail_list",$detail_list);

		$ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();
		$this->assign("ht",$ht);
		$this->display("dingtoubao");
    }

}