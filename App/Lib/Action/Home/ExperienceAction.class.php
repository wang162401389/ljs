<?php
/*投标
*/
class ExperienceAction extends HCommonAction {
	
    public function detail(){
		//$pre = C('DB_PREFIX');
		//$id = intval($_GET['id']);
		$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		
		
		//合同ID
		$borrowinfo = M("borrow_info_experience")->where('id=1')->find();
		// 图片资料
		$updata = unserialize($borrowinfo['updata']);
		$upphoto =array();
		foreach ($updata as $item) {
			$upphoto[] = $item['img'];
		}
		$this->assign("vplist",$upphoto);
		$this->assign("vo",$borrowinfo);

        // 投资记录
        $this->investRecord(1);
        $this->assign('borrow_id', 1);

		$this->assign("Bconfig",$Bconfig);
		$this->assign("gloconf",$this->gloconf);

		//投资卷
		$coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")->where("m.id = {$this->uid} AND c.status = 0 AND  c.type = 2")->find();
		$this->assign("coupons",$coupons);

		$realnamelist = M('members_status')->where("uid ={$this->uid}")->find();//查询是否实名认证
		$status=0;//没有实名认证 1 已经实名没有设置支付密码 2 都已经完成
		if ($realnamelist["id_status"] == 1) {//已经实名认证
			$is_setpassword = checkissetpaypwd($this->uid);//查看新浪密码是否设置
			if($is_setpassword['is_set_paypass']=="Y"){
				$status=2;
			}else{
				$status=1;
			}
		}
		$this->assign("status",$status);
		$this->display();
    }
	
	public function investmoney(){

        if(C("Cach.member_info")){
            //删除cach
            $path="html/member_info/".date("Ymd")."/";
            $filename=$this->uid.".html";
            unlink($path.$filename);
        }

        $bid =  intval($_GET['borrow_id']);
        $binfo = M("borrow_info_experience")->find($bid);

        $coupons = M("coupons c")->join("lzh_members m on m.user_phone = c.user_phone")->where("m.id = {$this->uid} AND c.type = 2 AND c.status = 0")->find();
        if($coupons){
        	$data['status'] = 1;
        	M("coupons")->where("serial_number={$coupons['serial_number']}")->save($data);
        	$b_data['borrow_id'] = 1;
        	$b_data['investor_uid'] = $this->uid;
        	$b_data['capital'] = $coupons['money'];
        	$interest = $binfo['borrow_interest_rate']/100/360*$binfo['borrow_duration']*$b_data['capital'];
        	$b_data['interest'] = round($interest,2);
        	$b_data['status'] = 1;
        	$b_data['deadline'] = strtotime(date("Y-m-d 23:59:59",strtotime("+4 days")));
        	$b_data['add_time'] = time();
        	M("investor_detail_experience")->add($b_data);
        	$binfo_data['has_borrow'] = $binfo['has_borrow']+$b_data['capital'];
        	$binfo_data['borrow_times'] = $binfo['borrow_times'] + 1;
        	M("borrow_info_experience")->where("id = 1")->save($binfo_data);
            $phone = M("member")->where("id = {$this->uid}")->field("user_phone")->find();
            $content = "尊敬的链金所用户您好！您投资的新手体验标已成功，您可登录平台账户查询详情，也可与客服中心联系400-6626-985。";
            sendsms($phone["user_phone"],$content);
	    	$this->success("恭喜您投标成功","__APP__/member");
        }else{
        	$this->error("您没有体验标的资格");
        }
	}

     /**
    * ajax 获取投资记录
    *
    */
    public function investRecord($borrow_id=0)
    {
        import("ORG.Util.Page");
        $count = M("investor_detail_experience")->where('borrow_id=1')->count();
        $Page     = new Page($count,10);
        //$Page->setConfig('theme',"%upPage% %downPage% 共%totalPage% 页");
        $Page->rollPage=10;
        $show = $Page->show();
        $this->assign("total_page",$Page->get_total_page());
        $this->assign('page', $show);
        	$list = M("investor_detail_experience as b")
                        ->join("lzh_members as m on  b.investor_uid = m.id")
                        ->join("lzh_borrow_info_experience as i on  b.borrow_id = i.id")
                        ->field('b.capital, b.add_time,m.user_phone')
                        ->where('b.borrow_id= 1')->order('b.id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
            if(count($list)){
				$ad=[];
				foreach ($list as $va){
                       $va["add_time"]=date("Y-m-d H:i",$va['add_time']);
					   $va["user_phone"]=hidecard($va['user_phone'],2);
					   $va["capital"]=Fmoney($va['capital']);
					   $ad[]=$va;
				}
				$this->assign("list",$ad);
				unset($list);
			}
    }


    public function set_paypass(){
    	$data['content'] = $this->fetch();
		ajaxmsg($data);
    }

}
