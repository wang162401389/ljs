<?php
// 本类由系统自动生成，仅供测试用途
class BorrowdetailAction extends MCommonAction {

    public function index(){
		$this->assign("bid",intval($_GET['id']));
		$this->display();
    }

    private  function need_request($id){
        $where['id']=$id;
        $info = M("borrow_info")->field('id,deadline,apply_status')->where($where)->select();
        $current=strtotime(date("Y-m-d",strtotime("now")));
        $target=strtotime(date("Y-m-d",cal_deadline($id)));
        if($current>=$target){
            return 2;
        }else{
            return $info[0]['apply_status'];
        }

    }

    /**
     * @param int $borrowid
     * @return array|void
     */
    function super_getBorrowInvest($borrowid=0,$is_wood=false){
        if(empty($borrowid)) return;
        $vx = M("borrow_info")->field('id')->where("id={$borrowid}")->find();
        if(!is_array($vx)) return;

        $binfo = M("borrow_info")->field('borrow_name,borrow_uid,borrow_type,borrow_duration,repayment_type,has_pay,total,deadline')->find($borrowid);
        $list = array();
        switch($binfo['repayment_type']){
            case 1://一次性还款
            case 5://一次性还款
                $field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline";
                $vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=1 and status!=-1")->group('sort_order')->find();
                //$status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
                $status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
                ///////////////////
                if($vo['deadline']<time() && $vo['status']==7){
                    $vo['status'] ='逾期未还';
                    import("@.conf.borrow_expired");
                    $expired=new borrow_expired($borrowid);
                    $vo['expired__money']=$expired->get_expired__money();
                }else{
                    if($vo['status']==5){
                        import("@.conf.borrow_expired");
                        $vo['expired__money']=borrow_expired::get_over_expired__money($borrowid);
                    }else{
                        $vo['expired__money']=0;
                    }
                    $vo['status'] = $status_arr[$vo['status']];
                }
                $return_info=cal_repayment_money($borrowid,1,1);
                $r_info=explode("=",$return_info);
                $vo["money"]=$r_info[1];
                ///////////////////
                //$vo['status'] = $status_arr[$vo['status']];
                //$vo['needpay'] = getFloatValue(sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid'])),2);
                $vo['needpay'] = sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid']));
                $list[] = $vo;
                break;
            default://每月还款
                for($i=1;$i<=$binfo['borrow_duration'];$i++){
                    $field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline";
                    $vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=$i and status!=-1")->group('sort_order')->find();
                    $status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
                    ///////////////////
                    if($vo['deadline']<time() && $vo['status']==7){
                        $vo['status'] ='逾期未还';
                        import("@.conf.borrow_expired");
                        $expired=new borrow_expired($borrowid,$i);
                        $vo['expired__money']=$expired->get_expired__money();
                    }else{
                        if($vo['status']==5){
                            import("@.conf.borrow_expired");
                            $vo['expired__money']=borrow_expired::get_over_expired__money($borrowid,$i);
                        }else{
                            $vo['expired__money']=0;
                        }
                        $vo['status'] = $status_arr[$vo['status']];
                    }
                    $return_info=cal_repayment_money($borrowid,$i,1);
                    $r_info=explode("=",$return_info);
                    $vo["money"]=$r_info[1];
                    ///////////////////
                    //$vo['status'] = $status_arr[$vo['status']];
                    $vo['needpay'] = sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid']));
                    $list[] = $vo;
                }
                break;
        }
        $row=array();
        $row['list'] = $list;
        $row['name'] = $binfo['borrow_name'];
        $row['repayment_type'] = $binfo['repayment_type'];
        return $row;

    }
    public function borrowdetail(){
		$pre = C('DB_PREFIX');
		$borrow_id = intval($_GET['id']);
		$is_danbao = M("members_company")->where("is_danbao = 1 AND uid = {$this->uid}")->count();
        $is_danbao=0;
        if($this->supper_login == 1 || $is_danbao>0){
            $list=$this->super_getBorrowInvest($borrow_id);
        }
        // elseif($this->supper_login == 2){
        //     $list=$this->super_getBorrowInvest($borrow_id,true);
        // }
        else{
            $list = getBorrowInvest($borrow_id,$this->uid);
        }

		$this->assign("list",$list);
        $need=$this->need_request($borrow_id);
        $this->assign("need",$need);
        if($this->supper_login == 1 ){
            //生产动态码，
            import("ORG.Util.String");
            $token = String::randString(6);
            session("super_replay_token",$token);
            $add_function=C("ADD_FUNCTION");
            if($this->supper_login == 1){
                $tel=$add_function['repayment']['tel'];
            }
            // elseif($this->supper_login == 2){
            //     $tel=$add_function['repayment']['tel1'];
            // }

            import("@.sms.Notice");
            $notice=new Notice();
            $notice->super_replay_code($tel,$token,$borrow_id);
            $data['html'] = $this->fetch("super_borrow");
        }
        else
		    $data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function sinacollecttrade(){
        if($this->supper_login == 1){ //如果是超级用户，需要使用动态
            $token=$_POST['token'];
            $super_replay_token=session("super_replay_token");
            if(($token!=$super_replay_token)||($token=='')){
                ajaxmsg('动态验证码不正确',3);
                die;
            }
        }
		$borrow_id = intval($_POST['bid']);
		$sort_order = intval($_POST['sort_order']);
		$money = $this->sum($borrow_id,$sort_order);
		$utype = M("members")->where("id={$this->uid}")->field("user_regtype")->find();
        if($utype["user_regtype"]==1){
            //直接调取新浪余额
           $saving = querysaving($this->uid);
        }else{
           $saving = querybalance($this->uid);
        }

		if(!is_numeric($money)){
			ajaxmsg($money,3);
			die;
		}
		if($saving<$money){
			ajaxmsg('余额不足，请充值！',3);
			die;
		}else{
			moneyactlog($this->uid,5,$money,0,"借款人发起对".$borrow_id."号标还款",1);
            // $borrow_id=$info['borrow_id'];
            $newbid = borrowidlayout1($borrow_id);
            $sina['uid'] = $this->uid;
            $sina['content'] = "对第".$newbid."号标还款";
            $sina['money'] = $money;
            $sina['code'] = "1002";
            $sina['bid'] = $borrow_id;
            $sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/Borrowdetail/repayment?borrow_id=".$borrow_id;
            $sina['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/collecttradenotify";
            $sina['sort_order'] = $sort_order;
            $this->ajaxreturn(sinacollecttrade($sina),"还款",2);
		}
	}

    /**
     * @param $borrow_id
     * @param $sort_order
     * @return int|string
     */
	public function sum($borrow_id,$sort_order){
		$pre = C('DB_PREFIX');
		$done = false;
		$money = 0;
		$borrowDetail = D('investor_detail');
		$binfo = M("borrow_info")->field("id,borrow_uid,n_interest,n_colligate_fee,colligate_fee,product_type,add_time,second_verify_time,borrow_interest_rate,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline")->find($borrow_id);
		$b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);

		if( $binfo['has_pay']>=$sort_order) return "本期已还过，不用再还";
		if( $binfo['has_pay'] == $binfo['total'])  return "此标已经还完，不用再还";
		if( ($binfo['has_pay']+1)<$sort_order) return "对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期";
		if( $binfo['deadline']>time() && $type==2)  return "此标还没逾期，不用代还";

		$voxe = $borrowDetail->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')->where("borrow_id={$borrow_id} and status!=-1 and is_debt = 0 ")->group('sort_order')->select();
		foreach($voxe as $ee=>$ss){
			if($ss['sort_order']==$sort_order) $vo = $ss;
		}

		// 复审通过后开始计算借款人利息 获取复审时间
		//$atime = M('borrow_investor')->field("add_time")->where("borrow_id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
		$atime = $binfo['second_verify_time'];
		//企业直投与普通标,判断还款期数不一样
		//借款天数、还款时间
		//利息计算公式 借款总金额*(借款利率/36000)*借款天数
		$borrow_money           = intval($binfo['borrow_money']); //借款总额
		$borrow_interest_rate   = $binfo['borrow_interest_rate']; //借款利率 此处因为利率转成了整数 20% 转成 2
		$day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率


		$colligate_fee =0;//综合服务费
		// 提前还款 当前还时间小于最后还款时间23:59:59
        import("@.conf.borrow_expired");
        $expired=new borrow_expired($borrow_id,$sort_order);
        $expired__money=$expired->get_expired__money();
		if($binfo['repayment_type'] == 1){
			// 更新利息 M('investor_detail')
			$investor_uid = M('investor_detail')->where('borrow_id='.$borrow_id." and status!=-1 and is_debt = 0")->select();


			$vo['interest'] = 0;
			$Detail = M("investor_detail");
			// 提单质押标
			if($binfo['product_type'] == 1 || $binfo['product_type'] == 3||$binfo['product_type'] == 6||$binfo['product_type'] == 7||$binfo['product_type'] == 8||$binfo['product_type'] == 10){

                //计算还款天数，如果不足70%天，需要按70%算利息
                /***********************************************/
                /*
				$duration=$binfo['borrow_duration'];
                $limit_borrow_day=ceil($duration*0.7);
                if($BorrowingDays<$limit_borrow_day)
                    $BorrowingDays=$limit_borrow_day;*/
                $currentTime            = strtotime(date('Y-m-d')); //当前需还款时间
                $issueTime              = strtotime(date('Y-m-d',$atime));//复审后的时间

                $binfo['deadline']=cal_deadline($borrow_id);//修正bug.

                if(strtotime(date('Y-m-d',$binfo['deadline'])) == $currentTime && $borrow_id <= 325){
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
                }else if(strtotime(date('Y-m-d',$binfo['deadline']))>$currentTime){
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
                }else{
                    $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
                }

                if($BorrowingDays == 0){
                    $BorrowingDays = $BorrowingDays +1;
                }




				// 综合服务费 利率/36000 * 借款金额 * 天数  提单、现货的综合服务费
				$colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
				foreach ($investor_uid as $iteme) {
					$tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
					$vo['interest'] += $tou_interest;
					unset($iteme['id']);
					//$Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id}");
				}
			}
			// 转现货质押标
			if ($binfo['product_type'] == 2) {

				// 投资人额度/标的总额*旧利息
				$vo['interest'] = 0;
				$xhtime = M('borrow_info')->field("add_time")->where("id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
				$currentTime            = strtotime(date('Y-m-d')); //当前时间
				$issueTime              = strtotime(date('Y-m-d',$xhtime['add_time']));//转现货时间
                $binfo['deadline']=cal_deadline($borrow_id);//修正bug.
				if(strtotime(date('Y-m-d',$binfo['deadline'])) == $currentTime && $borrow_id <= 325){
					$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
				}else  if(strtotime(date('Y-m-d',$binfo['deadline']))>$currentTime){
					$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
				}else{
                    $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
                }
				if($BorrowingDays == 0){
					$BorrowingDays = $BorrowingDays +1;
				}
                //计算还款天数，如果不足70%天，需要按70%算利息
                /***********************************************/
				/*
                $duration=$binfo['borrow_duration'];
                $limit_borrow_day=ceil($duration*0.7);
                if($BorrowingDays<$limit_borrow_day)
                    $BorrowingDays=$limit_borrow_day;*/


				$totalinterest = 0;
				// 综合服务费 利率/36000 * 借款金额 * 天数  提单转现货的综合服务费
				$colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
				foreach ($investor_uid as $iteme) {
					$tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
					$vo['interest'] += $tou_interest;
				}
				foreach ($investor_uid as $n) {
					$d_interest = getFloatValue($n['capital']/$binfo['borrow_money']*($vo['interest']+$binfo['n_interest']),2);
					$totalinterest += $d_interest;
					unset($iteme['id']);
					// print_r($binfo['n_interest']."<br>");
					//$Detail->execute("update `{$pre}investor_detail` set `interest`={$d_interest} WHERE `capital`={$n['capital']} and `borrow_id`={$borrow_id}");
				}
				$vo['interest'] = $totalinterest;
				$colligate_fee +=$binfo['n_colligate_fee'];
			}
		}else{
			$field = "sum(capital) as capital,sum(interest) as interest";
			$vo = M("investor_detail")->field($field)->where("borrow_id={$borrow_id} AND `sort_order`={$sort_order} and status!=-1 and is_debt = 0")->find();
			$money = $vo['capital']+$vo['interest'];
		}
            $pay_frist=D("borrow_info_additional")->is_pay_frist($borrow_id);//判断此标是否提前收取了综合服务费。 1表示已经收取。
            if($pay_frist)
                $money = $vo['capital']+$vo['interest']; //已经收取了综合服务费，这里不在计算
            else
                $money = $vo['capital']+$vo['interest']+$colligate_fee;
        $money+=$expired__money;//罚息
		return $money;
	}

	public function sum1(){
		$borrow_id = $_POST['borrow_id'];
		$sort_order = $_POST['sort_order'];
        return cal_repayment_money($borrow_id,$sort_order);
	}
    private function  cal_custom_info($borrow_id,$sort_order){
        return cal_repayment_money($borrow_id,$sort_order,1);
    }

    /**
     * 还款列表
     */
    public function  do_apply(){
        $borrow_id = $_POST['borrow_id'];
        $sort_order = $_POST['sort_order'];
        $search['id']=$borrow_id;
        $result=M("borrow_info")->where($search)->field("apply_status,borrow_status")->select();
        if($result[0]['apply_status']!=0){
            ajaxmsg("您已经审核过了",1);
        }
        if($result[0]['borrow_status']!=6){
            ajaxmsg("这个标不在还款状态",1);
        }
        $result=$this->cal_custom_info($borrow_id,$sort_order);
        $info="[".date("Y-m-d",strtotime("now"))."]".$result;
        $data['apply_status']=1;
        $where['id']=$borrow_id;
        M("borrow_info")->where($where)->save($data);
        D("borrow_info_additional")->apply_repayment($borrow_id,$info);
	   import("@.sms.Notice");
	   $notice= new Notice();
	   $notice->replay($borrow_id,1);
        ajaxmsg("我们正在审核，请稍后",0);
    }

    public function repayment(){
        $bid = borrowidlayout1($_REQUEST['borrow_id']);
		$this->success('对第'.$bid.'号标还款成功！','/member');
    }

    public function  zhaiquan_repayment(){
        $bid = zhaiquan_borrowidlayout1($_REQUEST['borrow_id']);
        $this->success('对第'.$bid.'号债权标还款成功！','/member');
    }
    // 自由分期还款
    public function stagerepayment(){
        $pre = C('DB_PREFIX');
        // 参数说明 $borrow_id标号   $sort_order 还款期数
        $bid        = $_POST['bid'];
        $sort_order = $_POST['sort_order'];
        $money      = $_POST['money'];
        $res        = stageborrowRepayment($bid,$sort_order,$money);

        if(true===$res) ajaxmsg();
        elseif(!empty($res)) ajaxmsg($res,0);
        else ajaxmsg("还款失败，请重试或联系客服",0);
    }

}
