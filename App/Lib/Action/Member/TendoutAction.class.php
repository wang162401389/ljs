<?php
// 本类由系统自动生成，仅供测试用途
class TendoutAction extends MCommonAction
{
    public function index()
    {
        $this->display();
    }
    public function tindex()
    {
        $this->display();
    }
    public function summary()
    {
        $uid = $this->uid;
        $pre = C('DB_PREFIX');

        $this->assign("dc", M('investor_detail')->where("investor_uid = {$this->uid}")->sum('substitute_money'));
        $this->assign("mx", getMemberBorrowScan($this->uid));
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    /**
     * 竞标中的普通标
     */
    public function tending()
    {
        //$map['i.investor_uid'] = $this->uid;
//		$map['i.status'] = 1;
        $map['investor_uid'] = $this->uid;
        $map['status'] = 1;

        $list = getTenderList($map, 15);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
            //月标 加息money计算
            //if ($v['repayment_type']!=1) {
                $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']}")->sum('jiaxi_money');
            //}
        }

        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    public function tendinginfo()
    {
        $map['investor_uid'] = $this->uid;
        $map['status'] = 1;
        $list = getTenderList($map, 15);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
            //月标 加息money计算
            if ($v['repayment_type']!=1) {
                $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']}")->sum('jiaxi_money');
            }
        }

        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $html= $this->fetch();
        echo $html;
    }

     /**
      * 回收中的普通标
      */
    public function tendbacking()
    {
        $map['investor_uid'] = $this->uid;
        $map['status'] = 4;
        // $map['is_debt'] = 0;
        $map['debt_status'] = array("neq",3);

        $tiyan = M("investor_detail_experience")->where("investor_uid = {$this->uid} and status = 1")->count();
        $list = getTenderList($map, 15, 2);
        $total_money=M('borrow_investor i')->where($map)->sum('investor_capital');
        foreach ($list['list'] as $k => $v) {
            $list['list'][$k]["user_phone"]= hidecard($v['userphone'], 2);
            $list['list'][$k]['second_verify_time']=date("Y-m-d", $v['second_verify_time']);
            if ($v['repayment_type']==1) {
                $list['list'][$k]['borrow_duration']=$v['borrow_duration']."天";
                if ($v['debt_id']>0) {
                    $list['list'][$k]['second_verify_time'] =  date("Y-m-d", $v['debt_time']);
                }
            } else {
                $list['list'][$k]['borrow_duration']=$v['borrow_duration']."个月";
                    //月标 加息money计算
                    $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']} and repayment_time>0")->sum('jiaxi_money');
                if ($v['debt_id']>0) {
                    $list['list'][$k]['second_verify_time'] =  date("Y-m-d", strtotime(date("Y-m-d", $v['second_verify_time']).'+ '.intval($v['borrow_duration']-$v['debt_duration']/30).' months'));
                }
            }
        }
        if ($tiyan) {
            $ty_info = M("investor_detail_experience ie")->join("lzh_borrow_info_experience be on be.id = ie.borrow_id")->where("ie.investor_uid = {$this->uid} and ie.status = 1")->field("be.id,be.borrow_name,ie.add_time,ie.capital,be.borrow_interest_rate,be.borrow_duration_txt,ie.deadline")->select();
            foreach ($ty_info as $key => $value) {
                $list_t["list"][$key]['bid'] = "TY".$value['id'];
                $list_t["list"][$key]["borrow_user"] = "体验标";
                $list_t["list"][$key]["is_auto"] = "手动";
                $list_t["list"][$key]["borrow_name"] = $value['borrow_name'];
                $list_t["list"][$key]["investor_capital"] = $value["capital"];
                $list_t["list"][$key]["second_verify_time"] = date("Y-m-d", $value["add_time"]);
                $list_t["list"][$key]["receive_capital"] = 0;
                $list_t["list"][$key]["receive_interest"] = 0;
                $list_t["list"][$key]["borrow_interest_rate"] = $value["borrow_interest_rate"];
                $list_t["list"][$key]["borrow_duration"] = $value["borrow_duration_txt"];
                $list_t["list"][$key]["repayment_time"] = $value["deadline"];
            }

            if (is_array($list["list"]) && count($list["list"])) {
                $list['list'] = array_merge($list_t["list"], $list["list"]);
            } else {
                $list['list'] = $list_t["list"];
            }
        }

        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $total_money);
        $this->assign("total_page", $list["total_page"]);
        $this->assign('uid', $this->uid);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    public function tendbackinginfo()
    {
        $map['investor_uid'] = $this->uid;
        $map['status'] = 4;
        // $map['is_debt'] = 0;
        $map['debt_status'] = array("neq",3);

        $tiyan = M("investor_detail_experience")->where("investor_uid = {$this->uid} and status = 1")->count();
        $list = getTenderList($map, 15, 2);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
            $list['list'][$k]['second_verify_time']=date("Y-m-d", $v['second_verify_time']);
            if ($v['repayment_type']==1) {
                $list['list'][$k]['borrow_duration']=$v['borrow_duration']."天";
                if ($v['debt_id']>0) {
                    $list['list'][$k]['second_verify_time'] =  date("Y-m-d", $v['debt_time']);
                }
            } else {
                $list['list'][$k]['borrow_duration']=$v['borrow_duration']."个月";
                //月标 加息money计算
                $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']} and repayment_time>0")->sum('jiaxi_money');
                if ($v['debt_id']>0) {
                    $list['list'][$k]['second_verify_time'] =  date("Y-m-d", strtotime(date("Y-m-d", $v['second_verify_time']).'+ '.intval($v['borrow_duration']-$v['debt_duration']/30).' months'));
                }
            }
        }
        if ($tiyan) {
            $ty_info = M("investor_detail_experience ie")->join("lzh_borrow_info_experience be on be.id = ie.borrow_id")->where("ie.investor_uid = {$this->uid} and ie.status = 1")->field("be.id,be.borrow_name,ie.add_time,ie.capital,be.borrow_interest_rate,be.borrow_duration_txt,ie.deadline")->select();
            foreach ($ty_info as $key => $value) {
                $list_t["list"][$key]['bid'] = "TY".$value['id'];
                $list_t["list"][$key]["borrow_user"] = "体验标";
                $list_t["list"][$key]["is_auto"] = "手动";
                $list_t["list"][$key]["borrow_name"] = $value['borrow_name'];
                $list_t["list"][$key]["investor_capital"] = $value["capital"];
                $list_t["list"][$key]["second_verify_time"] = date("Y-m-d", $value["add_time"]);
                $list_t["list"][$key]["receive_capital"] = 0;
                $list_t["list"][$key]["receive_interest"] = 0;
                $list_t["list"][$key]["borrow_interest_rate"] = $value["borrow_interest_rate"];
                $list_t["list"][$key]["borrow_duration"] = $value["borrow_duration_txt"];
                $list_t["list"][$key]["repayment_time"] = $value["deadline"];
            }
            if (is_array($list["list"]) && count($list["list"])) {
                $list['list'] = array_merge($list_t["list"], $list["list"]);
            } else {
                $list['list'] = $list_t["list"];
            }
        }

        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $this->assign('uid', $this->uid);
        $html = $this->fetch();
        echo $html;
    }



    public function getTendBacking()
    {
        import("ORG.Util.Page");
        $map = "(investor_uid={$this->uid} or debt_uid={$this->uid}) and status=4";
        $count = M("borrow_investor")->where($map)->count("id");
        $Page = new Page($count, 14);
        $list['list'] = M("borrow_investor i")
            ->join(C('DB_PREFIX')."borrow_info b ON i.borrow_id=b.id")
            ->join(C('DB_PREFIX')."members m ON i.investor_uid=m.id")
            ->join(C('DB_PREFIX')."invest_detb d ON i.id=d.invest_id")
            ->field("i.borrow_id, b.borrow_name, m.user_name as borrow_user,
                     i.investor_capital, b.borrow_interest_rate, i.receive_interest, i.receive_capital,
                     b.total, b.has_pay, i.id, d.period, d.status, i.debt_uid")
            ->where("(i.investor_uid={$this->uid} or i.debt_uid={$this->uid}) and i.status=4")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $list['page']=$Page->ajax_show();
        return $list;
    }

    /**
     * 已回收的普通标
     */
    public function tenddone()
    {
        //$map['i.investor_uid'] = $this->uid;
//		$map['i.status'] = array("in","5,6");
        $map['investor_uid'] = $this->uid;
        $map1['investor_uid'] = $this->uid;
        $map2['investor_uid'] = $this->uid;
        $map['status'] = array("in","5,6");
        $map1['repayment_time'] = array("gt",0);
        $map2['status'] = 2;
        $timetype = 'all';
        if ($_GET['timetype'] == 'tomonth') {
            $start_time = strtotime(date('Y-m-01')." 00:00:00");
            $end_time=time();
            $timetype = 'tomonth';
        } elseif ($_GET['timetype'] == 'threemonth') {
            $start_time = strtotime(date('Y-m-01', strtotime("-3 month"))." 00:00:00");
            $end_time=time();
            $timetype = 'threemonth';
        } elseif ($_GET['timetype'] == 'toyear') {
            $start_time = strtotime(date('Y-01-01')." 00:00:00");
            $end_time=time();
            $timetype = 'toyear';
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $start_time = strtotime($_GET['start_time']." 00:00:00");
            $end_time = strtotime($_GET['end_time']." 23:59:59");
            if ($_GET['start_time']<$_GET['end_time']) {
                $map1['repayment_time']=array("between","{$start_time},{$end_time}");
                $map2['repayment_time']=array("between","{$start_time},{$end_time}");
                $search['start_time'] = $start_time;
                $search['end_time'] = $end_time;
            }
            $timetype = null;
        }
        $map2["borrow_id"] = 2;
        if ($timetype != 'all') {
            $map1['repayment_time']=array("between","{$start_time},{$end_time}");
            $map2['repayment_time']=array("between","{$start_time},{$end_time}");
            $iid = M("investor_detail")->where($map1)->field('invest_id')->select();
            $j = 0;
            foreach ($iid as $i) {
                $iid[$j] = $i['invest_id'];
                $j++;
            }
            $map['id'] = array('in',$iid);
            $tiyan = M("investor_detail_experience")->where($map2)->count();
        } else {
            $tiyan = M("investor_detail_experience")->where($map2)->count();
        }

        $list = getTenderList($map, 10);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
            //月标 加息money计算
            if ($v['repayment_type']!=1) {
                $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']} and repayment_time>0")->sum('jiaxi_money');
            }
        }
        $shouyi = M("investor_detail")->where($map1)->sum("interest");
        if ($tiyan) {
            $ty_info = M("investor_detail_experience ie")->join("lzh_borrow_info_experience be on be.id = ie.borrow_id")->where("ie.investor_uid = {$this->uid} and ie.status = 2 and ie.borrow_id=2")->field("be.id,be.borrow_name,ie.add_time,ie.capital,ie.interest,be.borrow_interest_rate,be.borrow_duration_txt,ie.deadline")->select();
            foreach ($ty_info as $key => $value) {
                $list_t["list"][$key]['bid'] = "TY".$value['id'];
                $list_t["list"][$key]["borrow_user"] = "体验标";
                $list_t["list"][$key]["is_auto"] = "手动";
                $list_t["list"][$key]["borrow_name"] = $value['borrow_name'];
                $list_t["list"][$key]["investor_capital"] = $value["capital"];
                $list_t["list"][$key]["second_verify_time"] = date("Y-m-d", $value["add_time"]);
                $list_t["list"][$key]["receive_capital"] = 0;
                $list_t["list"][$key]["receive_interest"] = $value['interest'];
                $list_t["list"][$key]["jiaxi_money"] = 0;
                $list_t["list"][$key]["myexpired_money"] = 0;
                $list_t["list"][$key]["borrow_interest_rate"] = $value["borrow_interest_rate"];
                $list_t["list"][$key]["borrow_duration"] = $value["borrow_duration_txt"];
                $list_t["list"][$key]["repaytime"] = $value["deadline"];
                $shouyi = $shouyi+$value['interest'];
            }

            if (is_array($list["list"]) && count($list["list"])) {
                $list['list'] = array_merge($list_t["list"], $list["list"]);
            } else {
                $list['list'] = $list_t["list"];
            }
        }



        // $shouyi = M("investor_detail")->where($map1)->sum("interest");
        $this->assign("shouyi", $shouyi);

        $benjin = M("investor_detail")->where($map1)->sum("capital");
        $this->assign('timetype', $timetype);
        $this->assign('search', $search);
        $this->assign("benjin", $benjin);
        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    public function tenddoneinfo()
    {
        $map['investor_uid'] = $this->uid;
        $map1['investor_uid'] = $this->uid;
        $map2['investor_uid'] = $this->uid;
        $map['status'] = array("in","5,6");
        $map1['repayment_time'] = array("gt",0);
        $map2['status'] = 2;
        $timetype = 'all';
        if ($_GET['timetype'] == 'tomonth') {
            $start_time = strtotime(date('Y-m-01')." 00:00:00");
            $end_time=time();
            $timetype = 'tomonth';
        } elseif ($_GET['timetype'] == 'threemonth') {
            $start_time = strtotime(date('Y-m-01', strtotime("-3 month"))." 00:00:00");
            $end_time=time();
            $timetype = 'threemonth';
        } elseif ($_GET['timetype'] == 'toyear') {
            $start_time = strtotime(date('Y-01-01')." 00:00:00");
            $end_time=time();
            $timetype = 'toyear';
        }
        if ($_GET['start_time'] && $_GET['end_time']) {
            $start_time = strtotime($_GET['start_time']." 00:00:00");
            $end_time = strtotime($_GET['end_time']." 23:59:59");
            if ($_GET['start_time']<$_GET['end_time']) {
                $map1['repayment_time']=array("between","{$start_time},{$end_time}");
                $map2['repayment_time']=array("between","{$start_time},{$end_time}");
                $search['start_time'] = $start_time;
                $search['end_time'] = $end_time;
            }
            $timetype = null;
        }
        if ($timetype != 'all') {
            $map1['repayment_time']=array("between","{$start_time},{$end_time}");
            $map2['repayment_time']=array("between","{$start_time},{$end_time}");
            $iid = M("investor_detail")->where($map1)->field('invest_id')->select();
            $j = 0;
            foreach ($iid as $i) {
                $iid[$j] = $i['invest_id'];
                $j++;
            }
            $map['id'] = array('in',$iid);
            //$tiyan = M("investor_detail_experience")->where($map2)->count();
        } else {
            //$tiyan = M("investor_detail_experience")->where($map2)->count();
        }


        $list = getTenderList($map, 10);
        foreach ($list['list'] as $k => $v) {
            $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
            //月标 加息money计算
            if ($v['repayment_type']!=1) {
                $list['list'][$k]['jiaxi_money'] =  M('investor_detail')->where("invest_id={$v['id']}")->sum('jiaxi_money');
            }
        }

        $shouyi = M("investor_detail")->where($map1)->sum("interest");
        $this->assign("shouyi", $shouyi);
        $benjin = M("investor_detail")->where($map1)->sum("capital");
        $this->assign('timetype', $timetype);
        $this->assign('search', $search);
        $this->assign("benjin", $benjin);
        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $html = $this->fetch();
        echo $html;
    }

    public function tendbreak()
    {
        $map['d.status'] = array('neq',0);
        $map['d.repayment_time'] = array('eq',"0");
        $map['d.substitute_time'] = array('eq',"0");
        $map['d.deadline'] = array('lt',time());
        $map['d.investor_uid'] = $this->uid;

        $list = getMBreakInvestList($map, 15);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
        }
        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    /**
     * break ajax翻页
     */
    public function tendbreakinfo()
    {
        $map['d.status'] = array('neq',0);
        $map['d.repayment_time'] = array('eq',"0");
        $map['d.substitute_time'] = array('eq',"0");
        $map['d.deadline'] = array('lt',time());
        $map['d.investor_uid'] = $this->uid;

        $list = getMBreakInvestList($map, 15);
        foreach ($list['list'] as $k => $v) {
            // $list['list'][$k]['bid']=borrowidlayout1($v['borrow_id']);
        }
        $this->assign("list", $list['list']);
        $this->assign("pagebar", $list['page']);
        $this->assign("total", $list['total_money']);
        $this->assign("num", $list['total_num']);
        $this->assign("total_page", $list["total_page"]);
        $html= $this->fetch();
        echo $html;
    }

    public function tendoutdetail()
    {
        $pre = C('DB_PREFIX');
        $status_arr =array('还未还','已还完','已提前还款','迟还','网站代还本金','逾期还款','','等待还款');
        $investor_id = intval($_GET['id']);
        if ($_GET['id'] == "TY1") {
            $ty_info = M("investor_detail_experience")->where("investor_uid = {$this->uid}")->field("borrow_id,capital,interest,deadline")->find();
            $list[0]["bid"] = "TY".$ty_info["borrow_id"];
            $list[0]["deadline"] = $ty_info["deadline"];
            $list[0]["capital"] = "0.00";
            $list[0]["interest"] = $ty_info["interest"];
            $list[0]["interest_fee"] = "0.00";
            $list[0]["receive_capital"] = 0;
            $list[0]["receive_interest"] = 0;
            $this->assign("name", "新手体验标");
        } else {
            $vo = M("borrow_investor i")->field("b.borrow_name,b.product_type")->join("{$pre}borrow_info b ON b.id=i.borrow_id")->where("i.investor_uid={$this->uid} AND i.id={$investor_id}")->find();
            if (!is_array($vo)) {
                $this->error("数据有误");
            }
            $map['invest_id'] = $investor_id;
            $list = M('investor_detail')->field(true)->where($map)->select();
            foreach ($list as $key=>$val) {
                if ($vo["product_type"]<=3) { //只有天标才使用这种模式
                    $list[$key]['deadline']=cal_deadline($val['borrow_id']);
                }
            }
            $this->assign("name", $vo['borrow_name'].$investor_id);
        }
        $this->assign("status_arr", $status_arr);
        $this->assign("list", $list);

        $this->display();
    }

    public function detail()
    {
        $pre = C('DB_PREFIX');
        $status_arr =array('还未还','已还完','已提前还款','迟还','网站代还本金','逾期还款','','等待还款');
        $investor_id = intval($_GET['id']);
        if ($_GET['id'] == "TY1") {
            $ty_info = M("investor_detail_experience")->where("investor_uid = {$this->uid}")->field("borrow_id,capital,interest,deadline")->find();
            $list[0]["bid"] = "TY".$ty_info["borrow_id"];
            $list[0]["deadline"] = $ty_info["deadline"];
            $list[0]["capital"] = "0.00";
            $list[0]["interest"] = $ty_info["interest"];
            $list[0]["interest_fee"] = "0.00";
            $list[0]["receive_capital"] = 0;
            $list[0]["receive_interest"] = 0;
            $this->assign("name", "新手体验标");
        } else {
            $vo = M("borrow_investor i")->field("b.borrow_name,b.product_type")->join("{$pre}borrow_info b ON b.id=i.borrow_id")->where("i.investor_uid={$this->uid} AND i.id={$investor_id}")->find();
            if (!is_array($vo)) {
                $this->error("数据有误");
            }
            $map['invest_id'] = $investor_id;
            $list = M('investor_detail')->field(true)->where($map)->select();
            foreach ($list as $key=>$val) {
                if ($vo["product_type"]<=3) { //只有天标才使用这种模式
                    $list[$key]['deadline']=cal_deadline($val['borrow_id']);
                }
            }
            $this->assign("name", $vo['borrow_name']);
        }
        $this->assign("status_arr", $status_arr);
        $this->assign("list", $list);
        $html=$this->fetch();
        echo $html;
        exit();
    }
}
