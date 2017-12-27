<?php
function getFriendList($map, $size, $xuid=0)
{
    //if(empty($map['f.uid'])) return;
    $pre = C('DB_PREFIX');

    //分页处理
    import("ORG.Util.Page");
    $count = M('member_friend f')->where($map)->count('f.id');
    $p = new Page($count, $size);
    $page = $p->show();
    $Lsql = "{$p->firstRow},{$p->listRows}";
    //分页处理

    $list = M('member_friend f')->field("f.uid,f.friend_id,f.add_time,m.user_name,m.credits,fm.user_name as funame,fm.credits as fcredits")->join("{$pre}members m ON f.uid = m.id")->join("{$pre}members fm ON f.friend_id = fm.id")->where($map)->limit($Lsql)->select();
    foreach ($list as $key=>$v) {
        if ($map['f.apply_status']==0) {
            $list[$key]['user_name'] = $v['user_name'];
            $list[$key]['credits'] = $v['credits'];
        } else {
            $list[$key]['user_name'] = $v['funame'];
            $list[$key]['credits'] = $v['fcredits'];
        }
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}
//获取商品,包括分页数据
function getMsgList($parm=array())
{
    $M = new Model('member_msg');
    $pre = C('DB_PREFIX');
    $field=true;
    $orderby = " id DESC";


    if ($parm['pagesize']) {
        //分页处理
        import("ORG.Util.Page");
        $count = $M->where($parm['map'])->count('id');
        $p = new Page($count, $parm['pagesize']);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $data = M('member_msg')->field(true)->where($parm['map'])->order($orderby)->limit($Lsql)->select();

    $symbol = C('MONEY_SYMBOL');
    $suffix=C("URL_HTML_SUFFIX");
    foreach ($data as $key=>$v) {
    }

    $row=array();
    $row['list'] = $data;
    $row['page'] = $page;
    $row['count'] = $count;
    return $row;
}

function getWithDrawLog($map, $size, $limit=10)
{
    if (empty($map['uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_withdraw')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $status_arr =array('待审核','审核通过,处理中','已提现','审核未通过');
    $list = M('member_withdraw')->where($map)->order('id DESC')->limit($Lsql)->select();
    foreach ($list as $key=>$v) {
        $list[$key]['status'] = $status_arr[$v['withdraw_status']];
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $map['status'] = 1;
    $row['success_money'] = M('member_payonline')->where($map)->sum('money');
    $map['status'] = array('neq','1');
    $row['fail_money'] = M('member_payonline')->where($map)->sum('money');
    return $row;
}

function getChargeLog($map, $size, $limit=10)
{
    if (empty($map['uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_payonline')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $status_arr =array('充值未完成','充值成功','签名不符','充值失败');
    $list = M('member_payonline')->where($map)->order('id DESC')->limit($Lsql)->select();
    foreach ($list as $key=>$v) {
        $list[$key]['status'] = $status_arr[$v['status']];
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $map['status'] = 1;
    $row['success_money'] = M('member_payonline')->where($map)->sum('money');
    $map['status'] = array('neq','1');
    $row['fail_money'] = M('member_payonline')->where($map)->sum('money');
    return $row;
}
//借款逾期但还未还的借款列表(逾期)
function getMBreakRepaymentList($uid=0, $size=10, $Wsql="")
{
    if (empty($uid)) {
        return;
    }
    $pre = C('DB_PREFIX');

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M()->query("select d.id as count from {$pre}investor_detail d where d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_uid={$uid}) AND tb.borrow_status in(6,9) AND d.deadline<".time()." AND d.repayment_time=0 {$Wsql} group by d.sort_order,d.borrow_id");
        $count = count($count);
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $field = "b.borrow_name,d.status,d.total,d.borrow_id,b.product_type,d.sort_order,sum(d.capital) as capital,sum(d.interest) as interest,d.deadline";
    $sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_uid ={$uid} AND b.borrow_status in(6,9) AND d.deadline<".time()." AND d.repayment_time=0 {$Wsql} group by d.sort_order,d.borrow_id order by  d.borrow_id,d.sort_order limit {$Lsql}";

    $list = M()->query($sql);
    $status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
    $glodata = get_global_setting();
    $expired = explode("|", $glodata['fee_expired']);
    $call_fee = explode("|", $glodata['fee_call']);
    foreach ($list as $key=>$v) {
        $list[$key]['status'] = $status_arr[$v['status']];
        $list[$key]['breakday'] = getExpiredDays($v['deadline']);

        if ($list[$key]['breakday']>$expired[0]) {
            $list[$key]['expired_money'] = getExpiredMoney($list[$key]['breakday'], $v['capital'], $v['interest']);
        }

        if ($list[$key]['breakday']>$call_fee[0]) {
            $list[$key]['call_fee'] = getExpiredCallFee($list[$key]['breakday'], $v['capital'], $v['interest']);
        }

        $list[$key]['allneed'] = $list[$key]['call_fee'] + $list[$key]['expired_money'] + $v['capital'] + $v['interest'];
    }
    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $row['count'] = $count;
    return $row;
}



//集合起每笔借款的每期的还款状态(逾期)
function getMBreakInvestList($map, $size=10)
{
    $pre = C('DB_PREFIX');

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('investor_detail d')->join("{$pre}borrow_info b ON b.id=d.borrow_id")->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->count('d.id');
        $p = new Page($count, $size);
        $page = $p->ajax_show();
        $total_page=$p->get_total_page();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
        $total_page=1;
    }

    $field = "m.user_name as borrow_user,b.borrow_interest_rate,d.borrow_id,b.borrow_name,d.status,d.total,d.borrow_id,d.sort_order,d.interest,d.capital,d.deadline,d.sort_order,d.jiaxi_money,d.jiaxi_rate,b.jiaxi_rate as jx_rate";
    $tmp =M('investor_detail d')->field($field)->join("{$pre}borrow_info b ON b.id=d.borrow_id")->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->select();
    //过滤

    $list=array();
    $time=time();
    foreach ($tmp as $key=>$val) {
        $deadtime=cal_deadline($val['borrow_id']);
        if ($time>$deadtime) {
            $list[]=$val;
        }
    }

    $status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
    $glodata = get_global_setting();
    $expired = explode("|", $glodata['fee_expired']);
    $call_fee = explode("|", $glodata['fee_call']);
    foreach ($list as $key=>$v) {
        $list[$key]['status'] = $status_arr[$v['status']];
        $list[$key]['breakday'] = getExpiredDays($v['deadline']);
        $list[$key]['is_auto'] = $v['is_auto'] ? '自动' : '手动';
    }
    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $row['count'] = $count;
    $row["total_page"]=$total_page;
    return $row;
}

function getBorrowList($map, $size, $limit=10)
{
    if (empty($map['borrow_uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_info b')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
    $status_arr =$Bconfig['BORROW_STATUS_SHOW'];
    $type_arr =$Bconfig['REPAYMENT_TYPE'];
    $Model = D("BorrowView");
    $list=$Model->field(true)->where($map)->order('times ASC')->group('id')->limit($Lsql)->select();
    foreach ($list as $key=>$v) {
        $list[$key]['status'] = $status_arr[$v['borrow_status']];
        $list[$key]['repayment_type_num'] = $v['repayment_type'];
        $list[$key]['repayment_type'] = $type_arr[$v['repayment_type']];
        $list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100, 2);
        if ($map['borrow_status']==6) {
            $vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['id']} and status=7 and is_debt = 0")->order("deadline ASC")->find();
            if ($v['repayment_type']==1) {
                $list[$key]['repayment_time']=cal_deadline($v['id']);
            } else {
                $list[$key]['repayment_time'] = $vx['deadline'];
            }
        }
        if ($map['borrow_status']==5 || $map['borrow_status']==1) {
            $vd = M('borrow_verify')->field(true)->where("borrow_id={$v['id']}")->find();
            $list[$key]['dealinfo'] = $vd;
        }
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    //$map['status'] = 1;
    //$row['success_money'] = M('member_payonline')->where($map)->sum('money');
    //$map['status'] = array('neq','1');
    //$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
    return $row;
}

/**
 * @param $map 查询条件
 * @param $size 每页显示条数
 * @param int $type 1 默认其他类型  2回收中的普通标   3 已回收的普通标
 * @param int $limit
 * @return array|void
 */
function getTenderList($map, $size, $type=1, $limit=10)
{
    $pre = C('DB_PREFIX');
    $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
    //if(empty($map['i.investor_uid'])) return;
    if (empty($map['investor_uid'])) {
        return;
    }
    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_investor i')->where($map)->count('i.id');
        $p = new Page($count, $size);
        $page = $p->ajax_show();
        $total_page=$p->get_total_page();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
        $total_page=1;
    }

    $type_arr =$Bconfig['BORROW_TYPE'];
    /////////////////////////视图查询 fan 20130522//////////////////////////////////////////
    if ($map['status'] == 1) {
        $Model = D("TendingListView");
    } else {
        $Model = D("TenderListView");
        $map['InvestorDetail.is_debt'] = 0;
    }
    // $map['InvestorDetail.status']=array('neq' ,-1);
    $list=$Model->field(true)->where($map)->order('times ASC')->group('id')->limit($Lsql)->select();
    // print_r($Model->getLastSql());die;
    ////////////////////////视图查询 fan 20130522//////////////////////////////////////////
    foreach ($list as $key=>$v) {
        if ($type==2) {//如果是回收中的普通标则过滤一下债权的
            $uid=$map['investor_uid'];
            // $milist=M("borrow_debt")->query("SELECT  * FROM lzh_borrow_debt t  WHERE t.`borrow_id`= {$v['borrowid']} AND t.`debt_borrow_uid`={$uid} AND t.`debt_status` IN( 4,6,7)");
            // if($milist && count($milist)){
            // 	unset($list[$key]);
            // }
        }
        if ($map['status']==4) {
            $list[$key]['total'] = ($v['repayment_type']==1)?"1":$v['borrow_duration'];
            $list[$key]['back'] = $v['has_pay'];
            $vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['borrowid']} and status=7")->order("deadline ASC")->find();
            if ($v['repayment_type']==1) {// 天标的计算方式不一样
                $list[$key]['repayment_time']=cal_deadline($v['borrowid']);
            } else {
                $list[$key]['repayment_time'] = $vx['deadline'];
            }
        }
        $list[$key]['is_auto'] = $v['is_auto'] ? '自动' : '手动';
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $row['total_money'] = M('borrow_investor i')->where($map)->sum('investor_capital');
    $row['total_num'] = $count;
    $row["total_page"]=$total_page;
    return $row;
}


function getBackingList($map, $size, $limit=10)
{
    $pre = C('DB_PREFIX');
    if (empty($map['d.investor_uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('investor_detail d')->where($map)->count('d.id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $type_arr =C('BORROW_TYPE');
    $field = true;
    $list = M('investor_detail d')->field($field)->where($map)->order('d.id DESC')->limit($Lsql)->select();
    foreach ($list as $key=>$v) {
        //$list[$key]['status'] = $status_arr[$v['status']];
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    $sx = M('investor_detail d')->field("sum(d.capital + d.interest) as tox")->where("d.status=1 AND d.investor_uid={$map['d.investor_uid']}")->find();
    $sxcount = M('borrow_investor')->where("status=4 AND investor_uid={$map['d.investor_uid']}")->count("id");
    $month = M('investor_detail d')->field("sum(d.capital + d.interest) as tox")->where($map)->find();
    $row['month_total'] = $month['tox'];
    $row['total_money'] = $sx['tox'];
    $row['total_num'] = $sxcount;
    return $row;
}


//在线客服
function get_qq($type)
{
    $list = M('qq')->where("type = $type and is_show = 1")->order("qq_order DESC")->select();
    return $list;
}

//获取借款列表
function getMemberDetail($uid)
{
    $pre = C('DB_PREFIX');
    $map['m.id'] = $uid;
    //$field = "*";
    $list = M('members m')->field(true)->join("{$pre}member_banks mbank ON m.id=mbank.uid")->join("{$pre}member_contact_info mci ON m.id=mci.uid")->join("{$pre}member_house_info mhi ON m.id=mhi.uid")->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")->join("{$pre}member_ensure_info mei ON m.id=mei.uid")->join("{$pre}member_info mi ON m.id=mi.uid")->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")->where($map)->limit($Lsql)->find();
    return $list;
}
//////////////////////////////企业直投 管理模块开始  /////////////////////////////
function getTTenderList($map, $size, $limit = 10)
{
    $pre = C("DB_PREFIX");
    $Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
    if (empty($map['i.investor_uid'])) {
        return;
    }
    if ($size) {
        import("ORG.Util.Page");
        $count = M("transfer_borrow_investor i")->where($map)->count("i.id");
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
    } else {
        $page = "";
        $Lsql = "{$parm['limit']}";
    }
    $type_arr = $Bconfig['BORROW_TYPE'];
    $field = "i.*,i.add_time as invest_time,m.user_name as borrow_user,b.borrow_duration,b.borrow_interest_rate,b.add_time as borrow_time,b.borrow_money,b.borrow_name,m.credits";
    $list = M("transfer_borrow_investor i")->field($field)->where($map)->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")->join("{$pre}members m ON m.id=b.borrow_uid")->order("i.id DESC")->limit($Lsql)->select();
    foreach ($list as $key => $v) {
        if ($map['i.status'] == 4) {
            $list[$key]['total'] = $v['borrow_type'] == 3 ? "1" : $v['borrow_duration'];
            $list[$key]['back'] = $v['has_pay'];
        }
    }
    $row = array();
    $row['list'] = $list;
    $row['page'] = $page;
    $row['total_money'] = M("transfer_borrow_investor i")->where($map)->sum("investor_capital");
    $row['total_num'] = $count;
    return $row;
}

function getTDTenderList($map, $size, $limit = 10)
{
    $pre = C("DB_PREFIX");
    $Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
    if (empty($map['d.investor_uid'])) {
        return;
    }
    if ($size) {
        import("ORG.Util.Page");
        $count = M("transfer_investor_detail d")->where($map)->count("d.id");
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
    } else {
        $page = "";
        $Lsql = "{$parm['limit']}";
    }
    $type_arr = $Bconfig['BORROW_TYPE'];
    $field = "d.*,m.user_name as borrow_user,b.borrow_name,m.credits,i.add_time";
    $list = M("transfer_investor_detail d")->field($field)->where($map)->join("{$pre}transfer_borrow_info b ON b.id=d.borrow_id")->join("{$pre}transfer_borrow_investor i ON i.id=d.invest_id")->join("{$pre}members m ON m.id=b.borrow_uid")->order("d.deadline ASC")->limit($Lsql)->select();
    foreach ($list as $key => $v) {
    }
    $row = array();
    $row['list'] = $list;
    $row['page'] = $page;
    $row['total_money'] = M("transfer_investor_detail d")->where($map)->sum("`capital`+`interest`-`interest_fee`");
    $row['total_num'] = $count;
    return $row;
}


//////////////////////////////企业直投 管理模块结束  /////////////////////////////
