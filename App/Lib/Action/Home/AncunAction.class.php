<?php
class AncunAction extends HCommonAction {

    public function sendtoancun(){
        import("@.redis.AncunProject");
        $project = new AncunProject();
        while(1){
            $bid = $project->get_ancun();
               log::write(var_export($bid,true));

            if($bid){
               $return=$this->createhetong($bid);
                if($return!=true){
                    $fail['bid'] = $bid;
                    $fail_list[]=$fail;
                    log::write($bid."发送数据失败");
                }
            }else{
                log::write("目前没有任务");
                break;
            }
        }
        //处理失败任务
        if(count($fail_list)>0){
            foreach($fail_list as $l){
                $project->release_ancun($l["bid"]);
            }
        }
	}
    public function test(){
        $this->createhetong($_GET["id"]);
    }

	public function createhetong($borrow_id){
        $buid = M('borrow_info')->where("id = {$borrow_id}")->field('borrow_uid')->find();
		$per = C('DB_PREFIX');
		$uid=$buid['borrow_uid'];
		//$invest_id=intval($_GET['id']);
       // show_contract($borrow_id);
	    //所以投标记录
		$iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->select();
		//标详情
		$binfo = M('borrow_info')->field('id,borrow_use,repayment_type,borrow_duration,borrow_duration_txt,borrow_uid,borrow_type,borrow_use,borrow_money,full_time,add_time,borrow_interest_rate,deadline,second_verify_time,warehousing,borrow_duration_txt,product_type')->find($borrow_id);
		//借款人信息
		$mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_regtype,m.user_name,mi.idcard,m.user_phone,mi.cell_phone')->where("m.id=$uid")->find();
		if(empty($mBorrow["real_name"])){
			 $co_info=M('members_company mc')->field('mc.company_name,mc.license_no')->where("mc.uid={$uid}")->find();
             $mBorrow["sub_real_name"]=$co_info["company_name"];
             $mBorrow["real_name"]=$co_info["company_name"];
             $mBorrow["idcard"]=$co_info["license_no"];
			 $mBorrow["is_com"]=1;
		}else{
             $mBorrow["sub_real_name"]=$mBorrow["real_name"];
        }
        $mInvests=array();      
		$mShang=array();
        $mShang[0]['usertype']=$mBorrow['user_regtype'];
        if(STAGING || TESTING || DEVELOPMENT){
            $mShang[0]['mobile']=$mBorrow['cell_phone'];
        }else{
            $mShang[0]['mobile']=$mBorrow['user_phone'];
        }
        
        $mShang[0]['name']=$mBorrow["real_name"];
        $mShang[0]['email']=$uid;  
        $p = 3;
        if($binfo['product_type']==5){//分期购
            $shang[0]["pagenum"] = 3;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.69;
            }else{
                $shang[0]["signy"] = 0.76;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.83;
            $com_xy['p']=3;
            $x=0.002;        
            $y=0.16;   
            $p = 4;
        }else if($binfo['product_type']==7){//优金链
            $shang[0]["pagenum"] = 3;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.19;
            }else{
                $shang[0]["signy"] = 0.262;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.33;
            $com_xy['p']=3;
            $x=0.002;        
            $y=0.522;
            $p = 3;
        }else if($binfo['product_type']==6){//信用标
            $shang[0]["pagenum"] = 2;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==0){
                $shang[0]["signy"] = 0.645;
            }else{
                $shang[0]["signy"] = 0.715;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.775;
            $com_xy['p']=2;
            $x=0.002;        
            $y=0.058;
            $p = 3;
        }else if($binfo['product_type']==4){//融金链
            $shang[0]["pagenum"] = 2;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.65;
            }else{
                $shang[0]["signy"] = 0.715;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.78;
            $com_xy['p']=2;
            $x=0.002;        
            $y=0.0815;
            $p = 3;
        }else if($binfo['product_type']<4){//质金链
            $shang[0]["pagenum"] = 3;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.21;
            }else{
                $shang[0]["signy"] = 0.262;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.35;
            $com_xy['p']=3;
            $x=0;        
            $y=0.523;
            $p = 3;
        }else if($binfo['product_type']==8){//保金链
            $shang[0]["pagenum"] = 2;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.63;
            }else{
                $shang[0]["signy"] = 0.69;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.76;
            $com_xy['p']=2;
            $x=0.002;        
            $y=0.058;
            $p = 3;
        }else if($binfo['product_type']==10){//质金链（保）
            $shang[0]["pagenum"] = 2;
            $shang[0]["signx"] = 0.02; 
            if($mBorrow["is_com"]==1){
                $shang[0]["signy"] = 0.75;
            }else{
                $shang[0]["signy"] = 0.802;
            }
            $mShang[0]['coordinatelist'] = $shang;
            $com_xy['x']=0.02;
            $com_xy['y']=0.01;
            $com_xy['p']=3;
            $x=0;        
            $y=0.189;
            $p = 3;
        }
       	
        $k = 1;
        $ll = array();
        $intval_uid=array();
        // for ($i=0; $i < 100 ; $i++) { 
        //     $iinfoss[$i] = $iinfos[0];
        // }
		foreach ($iinfos as  $key =>$val){
			$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('m.id,mi.real_name,m.user_name,mi.cell_phone,m.user_phone,m.user_regtype')->where("m.id={$val['investor_uid']}")->find();
            $mInvests[$key]['user_regtype']=$mInvest['user_regtype'];
			$mInvests[$key]['uid']=$mInvest['id'];
           
			if ($mInvests[$key]['user_regtype'] == 1) {
                $mInvests[$key]['real_name']=$mInvest['real_name'];
			} elseif($mInvests[$key]['user_regtype'] == 2) {
				$mCompany = M('members_company mc')->field('mc.company_name')->where("mc.uid={$val['investor_uid']}")->find();
                $mInvests[$key]['real_name']=$mCompany['company_name'];
			}
			
            $mInvests[$key]['user_phone']=$mInvest['user_phone'];
            // $mInvests[$key]['cell_phone']=$mInvest['cell_phone'];
            if(!in_array($mInvest['user_phone'], $ll)){
                $mShang[$k]['usertype']=$mInvest['user_regtype'];
                $mShang[$k]['mobile']=$mInvest['user_phone'];
                $mShang[$k]['name']=$mInvests[$key]['real_name'];
                $mShang[$k]['Signimagetype']=1;
                $mShang[$k]['coordinatelist']=null;
                $mShang[$k]['email']=$mInvests[$key]['uid'];
                $k ++;
                array_push($ll, $mInvest['user_phone']);
                array_push($intval_uid, $val['investor_uid']);
            }
               
			$mInvests[$key]['user_name']=$mInvest['user_name'];		
			$detail = M('investor_detail d')->field('d.invest_id,sum(d.capital+d.interest-d.interest_fee) benxi,capital')->where("d.borrow_id={$borrow_id} and d.invest_id ={$val['id']}")->group('d.invest_id')->find();
			$mInvests[$key]['capital']=$detail['capital'];
			$mInvests[$key]['benxi']=$detail['benxi'];
			$mInvests[$key]['total']=$detail['total'];
			$mInvests[$key]['investor_capital']=$val['investor_capital'];
			
			
		}
	
        $shanglist=array();
        $j=1;
       // $where["m.id"]=array("IN",$intval_uid);
       // $mIn = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.cell_phone,m.user_phone')->where($where)->select();
        foreach ($mShang as $key => $value) {
            if($key > 0){
                foreach ($mInvests as $k => $v) {
                    if($value["mobile"] == $v["user_phone"] && $value["coordinatelist"]==null){
                        $shangu["pagenum"] = $p;
                        $shangu["signx"] = $x;
                        $shangu["signy"] = $y;
                        if($binfo['product_type']==5){//分期购
                            if($j+1 == 42 || $j+1 == 94){
                                $p +=1;
                                $y = 0.03;
                            }else{
                                $y += 0.0179;
                            }
                        }else if($binfo['product_type']==7){//优金链
                             if($j+1 == 22 || $j+1 == 71){
                                $p +=1;
                                $y = 0.017;
                            }else{
                                $y += 0.0179;
                            }
                        }else if($binfo['product_type']==6){//信用标
                            if($j+1 == 48 || $j+1 == 97){
                                $p +=1;
                                $y = 0.018;
                            }else{
                                $y += 0.0178;
                            }
                        }else if($binfo['product_type']==4){//融金链
                            if($j+1 == 47 || $j+1 == 96){
                                $p +=1;
                                $y = 0.017;
                            }else{
                                $y += 0.0179;
                            }
                        }else if($binfo['product_type']<4){//质金链
                            if($j+1 == 22 || $j+1 == 71){
                                $p +=1;
                                $y = 0.017;
                            }else{
                                $y += 0.0178;
                            }
                        }else if($binfo['product_type']==8){//融金链
                            if($j+1 == 48 || $j+1 == 97){
                                $p +=1;
                                $y = 0.017;
                            }else{
                                $y += 0.0179;
                            }
                        }else if($binfo['product_type']==10){//质金链（保）
                            if($j+1 == 41 || $j+1 == 90){
                                $p +=1;
                                $y = 0.0178;
                            }else{
                                $y += 0.0178;
                            }
                        }
                        $j++;
                        array_push($shanglist, $shangu);
                        // log::write("对比手机：".$v["cell_phone"]);
                        // log::write("内循环手机：".$value["mobile"]." 位置：".var_export($shanglist,true));

                    }
                }
               
                $mShang[$key]["coordinatelist"] = $shanglist;
                unset($shanglist);$shanglist=array();
            }
        }

		$jgcode = M("members m")->join("{$per}member_department_info jg ON jg.uid=m.id")->field('jg.institution_code')->where("m.id={$borrow_uid}")->find();
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

		$type1 = $this->gloconf['BORROW_USE'];
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

        //判断类型
        if($binfo['borrow_duration_txt']!=""){ //新版判断方式
            $newhetong=1;
            $add_info= D("borrow_info_additional")->get_additional_info($borrow_id);
            $duration_list=explode("+",$binfo['borrow_duration_txt']);
            if(count($duration_list)==2){
                $show_type='A';
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
            if($binfo["borrow_use"] == 9){
                $this->buildHtml("contract_".$borrow_id,"html/contract/","newfqgagreement");

            }else{
                $this->buildHtml("contract_".$borrow_id,"html/contract/","fqghetong");

            }
        }else if($binfo['product_type']==7){//优金链
        	$this->buildHtml("contract_".$borrow_id, "html/contract/", "yjlhetong");
        }else if($binfo['product_type']==6){//信用标
        	if($binfo['repayment_type']==2){
        		$where1['uid']=$uid;
            $info=M("members_company")->where($where1)->field("license_no")->find();
            $this->assign("license_no",$info["license_no"]);
            $r_info=getBorrowInvest($borrow_id,$uid);
            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
            $this->assign("r_info",$r_info["list"]);
        	}
        	$this->buildHtml("contract_".$borrow_id,"html/contract/","credithetong");
        }else if($binfo['product_type']==4){//融金链
            $where1['uid']=$uid;
            $info=M("members_company")->where($where1)->field("license_no")->find();
            $this->assign("license_no",$info["license_no"]);
            $r_info=getBorrowInvest($borrow_id,$uid);
            foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
            $this->assign("r_info",$r_info["list"]);
            if($borrow_id > $flag['break_point_3']){
            	$this->buildHtml("contract_".$borrow_id,"html/contract/","rjlhetong");
            }else{
            	 $this->buildHtml("contract_".$borrow_id,"html/contract/","monthhetong");
            }
        }else if($binfo['product_type']<4&&$borrow_id > $flag['break_point_3']){//质金链
        	$this->buildHtml("contract_".$borrow_id, "html/contract/", "zjlhetong");
        }else if($binfo['product_type']==8){
            $this->buildHtml("contract_".$borrow_id, "html/contract/", "bjagreement");
        }else if($binfo['product_type']==10){
            $this->buildHtml("contract_".$borrow_id, "html/contract/", "cjlhetong");
        }else if(isset($newhetong)){
            if ($borrow_id <= $flag['break_point_1']) {
            	$this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong");
            }
            if ($flag['break_point_1'] < $borrow_id && $borrow_id <= $flag['break_point_2']) {
            	$this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong1");
            }
            if ($borrow_id > $flag['break_point_2'] && $borrow_id <= $flag['break_point_3']) {
            	$this->buildHtml("contract_".$borrow_id, "html/contract/", "agreement20160414");
            }
        }else{
            $this->buildHtml("contract_".$borrow_id,"html/contract/","index");
        }
        $this->createpdf($borrow_id,$mShang,$com_xy);
        return true;
	}

	public function createpdf($bid,$mShang,$com_xy){
		$hetong = file_get_contents('html/contract/contract_'.$bid.'.html');
		import('@.Oauth.tcpdf.tcpdf');
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);  
        $pdf ->setHeaderData('pdflogo.png','50','','',array(0,0,0),array(255,255,255)); 
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(5,15,5); 
		// $pdf->SetAutoPageBreak(TRUE, 25);
		// $pdf->setFontSubsetting(true);
		// $pdf->SetFont('stsongstdlight', '', 10);
		$pdf->AddPage();
		$html = $hetong;
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		$pdf->Output('pdf/contract_'.$bid.'.pdf', 'F');
        ancunProjectSafe($bid,dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/pdf/contract_'.$bid.'.pdf');
        import("@.Oauth.ancun.Shang");
        $shang = new Shang();
        $rs = $shang->hetong(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/pdf/contract_'.$bid.'.pdf','contract_'.$bid.'.pdf',json_encode($mShang),$com_xy);
        if($rs["code"] === 0){
            $shang_data["borrow_id"]=$bid;
            $shang_data["sign_id"]=$rs["resultText"]["sign_id"];
            $shang_data["doc_id"]=$rs["resultText"]["doc_id"];
            M("shangshang")->add($shang_data);
        }
    }

    public function javatouser(){
        $uid = $_REQUSET['uid'];
        ancunUser($uid);
        echo 'success';
    }


    //全木行取标号
    public function allwoodGetbid(){
        $order_no = $_REQUEST['order_no'];
        $borrow_id = M('allwood_ljs')->where("allwood_orderno = {$order_no}")->find();
        echo borrowidlayout1($borrow_id['borrow_id']);
    }
}