<?php
// 本类由系统自动生成，仅供测试用途
class BorrowinAction extends MCommonAction {

    public function index(){
		$this->display();
    }
    private  function super_getBorrowList($map,$size,$limit=10){
        foreach($map as $key=>$val){
            if($key=='borrow_uid'){
                unset($map[$key]);
            }
        }
        if($size){
            //分页处理
            import("ORG.Util.Page");
            $count = M('borrow_info')->where($map)->count('id');
            $p = new Page($count, $size);
            $page = $p->show();
            $Lsql = "{$p->firstRow},{$p->listRows}";
            //分页处理
        }else{
            $page="";
            $Lsql="{$parm['limit']}";
        }

        $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
        $status_arr =$Bconfig['BORROW_STATUS_SHOW'];
        $type_arr =$Bconfig['REPAYMENT_TYPE'];

        $Model = D("BorrowView");
        $list=$Model->field(true)->where($map)->order('times ASC')->group('id')->limit($Lsql)->select();
        foreach($list as $key=>$v){
            $list[$key]['status'] = $status_arr[$v['borrow_status']];
            $list[$key]['repayment_type_num'] = $v['repayment_type'];
            $list[$key]['repayment_type'] = $type_arr[$v['repayment_type']];
            $list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
            if($map['borrow_status']==6){
                $vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['id']} and status=7")->order("deadline ASC")->find();
                $list[$key]['repayment_time'] = $vx['deadline'];
            }
            if($map['borrow_status']==5 || $map['borrow_status']==1){
                $vd = M('borrow_verify')->field(true)->where("borrow_id={$v['id']}")->find();
                $list[$key]['dealinfo'] = $vd;
            }
        }

        $row=array();
        $row['list'] = $list;
        $row['page'] = $page;
        return $row;
    }
    public function summary(){
		$pre = C('DB_PREFIX');

		$this->assign("mx",getMemberBorrowScan($this->uid));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public function borrowing(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = array("in","0,2");

		if($_GET['start_time2']&&$_GET['end_time2']){
			$_GET['start_time2'] = strtotime($_GET['start_time2']." 00:00:00");
			$_GET['end_time2'] = strtotime($_GET['end_time2']." 23:59:59");

			if($_GET['start_time2']<$_GET['end_time2']){
				$map['add_time']=array("between","{$_GET['start_time2']},{$_GET['end_time2']}");
				$search['start_time2'] = $_GET['start_time2'];
				$search['end_time2'] = $_GET['end_time2'];
			}
		}
        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        // elseif($this->supper_login == 2){
        // 	$map["borrow_use"] = 9;
        //     $list=$this->super_getBorrowList($map,10);
        // }
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
			$is_debtlist=M("borrow_debt")->query("select id from  lzh_borrow_debt g where g.borrow_id={$v['id']}");
			if($is_debtlist && count($is_debtlist[0])){//判断是否是债权标
				$list['list'][$k]['is_debt']=2;
			}else{
				$list['list'][$k]['is_debt']=1;
			}
		}
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);


		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	/**
	 * 偿还中的借款
	 */
	public function borrowpaying(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = 6;

		if($_GET['start_time']&&$_GET['end_time']){
			$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
			$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");

			if($_GET['start_time']<$_GET['end_time']){
				$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
				$search['start_time'] = $_GET['start_time'];
				$search['end_time'] = $_GET['end_time'];
			}
		}
        if($_GET['bid']){
            $map['id']=intval($_GET['bid']);
        }
        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        //elseif($this->supper_login == 2){
        	  // $map["borrow_use"] = 9;
            // $list=$this->super_getBorrowList($map,10);
        //}
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
			// $is_debtlist=M("borrow_debt")->query("select id from  lzh_borrow_debt g where g.borrow_id={$v['id']}");
			// if($is_debtlist && count($is_debtlist[0])){//判断是否是债权标
			// 	$list['list'][$k]['is_debt']=2;
			// }else{
			// 	$list['list'][$k]['is_debt']=1;
			// }
		}
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);


		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}


	public function borrowbreak(){
		$Wsql="";
		if($_GET['start_time1']&&$_GET['end_time1']){
			$_GET['start_time1'] = strtotime($_GET['start_time1']." 00:00:00");
			$_GET['end_time1'] = strtotime($_GET['end_time1']." 23:59:59");

			if($_GET['start_time1']<$_GET['end_time1']){
				$Wsql = " AND ( d.deadline between {$_GET['start_time1']} AND {$_GET['end_time1']} ) ";
				$search['start_time1'] = $_GET['start_time1'];
				$search['end_time1'] = $_GET['end_time1'];
			}
		}

		$list = getMBreakRepaymentList($this->uid,10,$Wsql);
			import("@.conf.borrow_expired");

		foreach($list['list'] as $k => $v){
        	$expired=new borrow_expired($v['borrow_id'], $v['sort_order']);
        	$expired__money=$expired->get_expired__money();
			$list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
			$list['list'][$k]['expired_money']=$expired__money;
			$list['list'][$k]['allneed']=$expired__money+$v['capital'] + $v['interest'];
		}
		//print_r($list['list']);
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);

		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	public function borrowfail(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = array("in","1,3,5");

		if($_GET['start_time4']&&$_GET['end_time4']){
			$_GET['start_time4'] = strtotime($_GET['start_time4']." 00:00:00");
			$_GET['end_time4'] = strtotime($_GET['end_time4']." 23:59:59");

			if($_GET['start_time4']<$_GET['end_time4']){
				$map['add_time']=array("between","{$_GET['start_time4']},{$_GET['end_time4']}");
				$search['start_time4'] = $_GET['start_time4'];
				$search['end_time4'] = $_GET['end_time4'];
			}
		}

        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        // elseif($this->supper_login == 2){
        // 	$map["borrow_use"] = 9;
        //     $list=$this->super_getBorrowList($map,10);
        // }
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
		}
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);


		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}


	public function borrowfail2(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = 5;

		if($_GET['start_time5']&&$_GET['end_time5']){
			$_GET['start_time5'] = strtotime($_GET['start_time5']." 00:00:00");
			$_GET['end_time5'] = strtotime($_GET['end_time5']." 23:59:59");

			if($_GET['start_time5']<$_GET['end_time5']){
				$map['add_time']=array("between","{$_GET['start_time5']},{$_GET['end_time5']}");
				$search['start_time5'] = $_GET['start_time5'];
				$search['end_time5'] = $_GET['end_time5'];
			}
		}

        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        // elseif($this->supper_login == 2){
        // 	$map["borrow_use"] = 9;
        //     $list=$this->super_getBorrowList($map,10);
        // }
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
		}
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);


		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}


	public function borrowfail1(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = 1;

		if($_GET['start_time6']&&$_GET['end_time6']){
			$_GET['start_time6'] = strtotime($_GET['start_time6']." 00:00:00");
			$_GET['end_time6'] = strtotime($_GET['end_time6']." 23:59:59");

			if($_GET['start_time6']<$_GET['end_time6']){
				$map['add_time']=array("between","{$_GET['start_time6']},{$_GET['end_time6']}");
				$search['start_time6'] = $_GET['start_time6'];
				$search['end_time6'] = $_GET['end_time6'];
			}
		}

        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        // elseif($this->supper_login == 2){
        // 	$map["borrow_use"] = 9;
        //     $list=$this->super_getBorrowList($map,10);
        // }
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
		}
		$this->assign('search',$search);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);


		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}


	public function borrowdone(){
		$map['borrow_uid'] = $this->uid;
		$map['borrow_status'] = 7;

		if($_GET['start_time8']&&$_GET['end_time8']){
			$_GET['start_time8'] = strtotime($_GET['start_time8']." 00:00:00");
			$_GET['end_time8'] = strtotime($_GET['end_time8']." 23:59:59");

			if($_GET['start_time8']<$_GET['end_time8']){
				$map['add_time']=array("between","{$_GET['start_time8']},{$_GET['end_time8']}");
				$search['start_time8'] = $_GET['start_time8'];
				$search['end_time8'] = $_GET['end_time8'];
			}
		}

        if($this->supper_login == 1){
            $list=$this->super_getBorrowList($map,10);
        }
        // elseif($this->supper_login == 2){
        // 	$map["borrow_use"] = 9;
        //     $list=$this->super_getBorrowList($map,10);
        // }
        else{
            $list = getBorrowList($map,10);
        }
        foreach($list['list'] as $k => $v){
			$list['list'][$k]['bid']=borrowidlayout1($v['id']);
		}

        $user_type = M("members")->where(array("id"=>$this->uid))->field("user_regtype")->find();
        $this->assign('search',$search);
		$this->assign('user_type',$user_type['user_regtype']);
		$this->assign("list",$list['list']);
		$this->assign("pagebar",$list['page']);

		$data['html'] = $this->fetch();
		exit(json_encode($data));
	}

	public function cancel(){
		$id = intval($_POST['id']);
		$newid = M('borrow_info')->where("borrow_uid={$this->uid} AND id={$id} AND borrow_status=0")->delete();
		if($newid) ajaxmsg("撤消成功");
		else ajaxmsg("出错，如果您正在撤回的是还未初审的标，请重试，如已经初审，则不能撤回",0);

	}

	public function doexpired(){
		$borrow_id = intval($_POST['bid']);
		$sort_order = intval($_POST['sort_order']);
		$newid = borrowRepayment($borrow_id,$sort_order);
		if($newid===true) ajaxmsg();
		elseif($newid===false) ajaxmsg('还款失败，请重试',0);
		else ajaxmsg($newid,0);
	}

	/**
	 * 将数组转化为sql语句中的where 条件语句目前只是支持一层循环
	 * @param array $a
	 * @return  string
	 */
	private function arraytocondition(array $a){
        if(count($a)>0){
           $condition=" where 1=1 ";
			foreach ($a as $key=>$value){
				if(is_string($value) && $value!=""){
					$condition.=" and {$key}={$value} ";
				}else if(is_array($value)){//如果是数组
                     if(trim($value[0])=="in"){
						 if(is_string($value[1])){
							 $condition.=" and ";
						 }

					 }else if(trim($value[0])=="like"){
						   $condition.="  and {$key} like {$value[1]}";
					 }else if(trim($value[0])=="between"){
                           $og=explode(',',$value[1]);
						   $condition.=" and {$key} between {$og[0]} and {$og[1]}";
					 }
				}
			}
		}else{
			return "";
		}
	}

    /**
     * 回单列表
     * @return [type] [description]
     */
    public function receiptlist(){
        $bid = $_POST["bid"];
        $borrow_info = M("borrow_info")->where(array("id"=>$bid))->field("id,has_pay,borrow_name")->find();
        $borrow_info["borrow_id"] = borrowidlayout1($borrow_info['id']);
        $this->assign("borrow_info",$borrow_info);
        $html=$this->fetch();
        echo json_encode(array("ret"=>0,"message"=>"获取数据成功","html"=>$html));
    }

    public function receipt(){
        $bid = intval($_GET['bid']);
        $sort_order = intval($_GET['sort_order']);
        if($sort_order == 0){
            $list = M("sinalog s")
                    ->join("lzh_borrow_info bi ON bi.id = s.borrow_id")
                    ->join("lzh_members_company mc ON mc.uid = bi.borrow_uid")
                    ->join("lzh_borrow_confirm bc ON bc.bid = bi.id")
                    ->field("s.completetime,s.borrow_id,mc.company_name,bi.borrow_uid,bc.fee,bi.borrow_name,bi.borrow_duration_txt")
                    ->where(array("s.borrow_id"=>$bid,"s.type"=>10))
                    ->find();
        }else{
            $list = M("investor_detail d")
                    ->join("lzh_borrow_info bi ON bi.id = d.borrow_id")
                    ->join("lzh_members_company mc ON mc.uid = bi.borrow_uid")
                    ->where(array('d.borrow_id' => $bid, "sort_order"=>$sort_order))
                    ->field("d.repayment_time,d.borrow_id,bi.borrow_name,mc.company_name,bi.borrow_uid,SUM(d.capital) as capital,SUM(d.interest) as interest,SUM(d.expired_money) as expired_money,bi.total,bi.borrow_interest_rate,bi.second_verify_time,d.deadline,bi.repayment_type,bi.borrow_duration_txt")
                    ->find();
        }
        $this->assign("list",$list);
        $this->assign("sort_order",$sort_order);
        import('@.Oauth.tcpdf.tcpdf');
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', true);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(5,2,5);
		$pdf->AddPage();
		$html = $this->fetch();
        $pdf->Image(__ROOT__.'/Style/M/images/receipt/caiwuzhang.png', 158, 90, 40, 40, 'PNG', '', '', false, 150, '', false, false, false, false, false, false);
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        ob_end_clean();
		$pdf->Output(date("YmdHis").'.pdf', 'D');
        // $this->display();
    }

}
