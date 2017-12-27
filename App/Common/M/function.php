<?php
/**
* wap版公共函数库
*/


//获取借款列表
function getBorrowList($parm=array())
{
    $map= $parm['map'];
    //$map['test']=0;
    $orderby= $parm['orderby'];

    if ($parm['pagesize']) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_info b')->where($map)->count('b.id');
        $p = new Page($count, $parm['pagesize']);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";

        $row['page']['total'] = ceil($count/$parm['pagesize']);
        $row['page']['nowPage'] =  isset($_REQUEST['p'])?$_REQUEST['p']:1;
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }
    $pre = C('DB_PREFIX');
    $suffix=C("URL_HTML_SUFFIX");
    $field = "b.id,b.product_type,b.password,b.borrow_min,b.borrow_name,b.borrow_type,b.updata,b.borrow_times,b.borrow_status,b.borrow_money,b.borrow_use,b.repayment_type,b.borrow_interest_rate,b.borrow_duration,b.collect_time,b.add_time,b.province,b.has_borrow,b.has_vouch,b.city,b.area,b.reward_type,b.reward_num,m.user_name,m.id as uid,m.credits,m.customer_name,b.is_tuijian,b.deadline,b.danbao,b.borrow_info,b.risk_control,b.borrow_duration_txt,b.jiaxi_rate,b.is_beginnercontract";
    if ($parm['type']==0) {//首页
        $co=M('borrow_info')->query("select count(b.id) as  mycount from lzh_borrow_info b  where b.is_beginnercontract=0 and b.test=0 and b.borrow_status in('2,4,6,7') and has_borrow!=borrow_money ");//未满的标的
        if ($co[0]["mycount"]>0) {//如果未满的标的存在,取不大于5条标
           // $list = M('borrow_info')->query("select ".$field."  from lzh_borrow_info b inner join lzh_members m on  m.id=b.borrow_uid where b.test=0 and b.borrow_status in('2,4,6,7') and (has_borrow*100/borrow_money)<100  order by b.borrow_status ASC,b.id DESC limit 5 ");//未满的标的
            $list = M('borrow_info')->query("select * from (select ".$field.",(has_borrow*100/borrow_money) as progress  from lzh_borrow_info b inner join lzh_members m on  m.id=b.borrow_uid where b.is_beginnercontract=0 and b.test=0 and b.borrow_status in('2,4,6,7') and (has_borrow*100/borrow_money)<100  limit 5 ) g order by progress desc ,add_time ");//未满的标的
        } else {//否则显示3条还款中的标的
            $list = M('borrow_info b')->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit("3")->select();
        }
    } else {//内页列表页面
        $list = M('borrow_info b')->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
    }

    foreach ($list as $key=>$v) {
        $list[$key]['biao'] = $v['borrow_times'];
        $list[$key]['need'] = $v['borrow_money'] - $v['has_borrow'];
        $list[$key]['leftdays'] = getLeftTime($v['collect_time']);
        $list[$key]['progress'] = intval($v['has_borrow']/$v['borrow_money']*100);
        $list[$key]['vouch_progress'] = getFloatValue($v['has_vouch']/$v['borrow_money']*100, 2);
        $list[$key]['burl'] = MU("M/invest", "invest", array("id"=>$v['id'],"suffix"=>$suffix));
        $img = unserialize($v['updata']);
        $list[$key]['image'] = $img['0']['img'];
    }
    $row['list'] = $list;
    return $row;
}

/**
* 格式化资金数据保持两位小数
* @desc intval $num  // 接受资金数据
*/
function MFormt($num)
{
    return number_format($num, 2);
}

/**
* 根据接收到的状态输出状态按钮
* @desc intval $status  // 借款状态
* @return string
* @author zhangjili 2014-03-25
*/
function borrow_status($borrow_info)
{
    switch ($borrow_info["borrow_status"]) {
        case 0:
            $href =  '<a  type="button" class="btn btn-info"  style="background-color:#CCC;border-color:#CCC">等待初审</a> ';
            break;
        case 1:
            $href =  '<a  type="button" class="btn btn-info"  style="background-color:#CCC;border-color:#CCC">初审失败</a> ';
            break;
        case 2:
            $href =  '<a type="button" class="btn btn-info1" style="color:#fa4622" href="'.U('M/Invest/detail', array('id'=>$borrow_info["id"])).'" >我要投资</a> ';
            break;
        case 4:
            $href =  '<a  type="button" class="btn btn-info "  style="background-color:#CCC;border-color:#CCC" >复审中</a> ';
            break;
        case 5:
            $href =  '<a  type="button" class="btn btn-info"  style="background-color:#CCC;border-color:#CCC">复审失败</a> ';
            break;
        case 6:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC" >还款中</a> ';
            break;
        case 6:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC" >还款完成</a> ';
            break;
        case 8:
            //$href =  '<a href="/m'.getInvestUrl($borrow_id).'"  class="tz_bt">我要投资</a> ';
            $href =  '<a type="button" class="btn btn-info1" href="'.U('M/Invest/detail', array('id'=>$borrow_info["id"])).'">'.date("H:i", $borrow_info["add_time"]).'开始</a> ';
            break;
        default:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC"  >已结束</a> ';
    }

    return $href;
}

function zhaiquan_borrow_status($borrow_info)
{
    switch ($borrow_info["borrow_status"]) {
        case 0:
            $href =  '<a  type="button" class="btn btn-info"  style="background-color:#CCC;border-color:#CCC">等待初审</a> ';
            break;
        case 2:
            $href =  '<a type="button" class="btn btn-info1" style="color:#fa4622" href="'.U('M/debthome/detail', array('id'=>$borrow_info["id"])).'" >我要投资</a> ';
            break;
        case 4:
            $href =  '<a  type="button" class="btn btn-info "  style="background-color:#CCC;border-color:#CCC" >等待复审</a> ';
            break;
        case 6:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC" >还款中</a> ';
            break;
        case 8:
            //$href =  '<a href="/m'.getInvestUrl($borrow_id).'"  class="tz_bt">我要投资</a> ';
            $href =  '<a type="button" class="btn btn-info1" style="color:#fa4622">'.date("H:i", $borrow_info["add_time"]).'开始</a> ';
            break;
        default:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC"  >已结束</a> ';
    }

    return $href;
}


/**
* @param intval $invest_uid // 投资人id
* @param intval $borrow_id // 借款id
* @param intval $invest_money // 投资金额必须为整数
* @param string $paypass // 支付密码
* @param string $invest_pass='' //投资密码
*/
function checkInvest($invest_uid, $borrow_id, $investmoney, $coupons, $jx, $invest_pass='')
{
    file_put_contents('sinatestlog.txt', "点击投标：时间：".date("Y-m-d H:i:s")."\n", FILE_APPEND);
    $borrow_id = intval($borrow_id);
    $invest_uid = intval($invest_uid);
    $invest_money = $investmoney;
    //if(!$paypass) return(L('please_enter').L('paypass'));
    if (!$invest_money) {
        return(L('please_enter').L('invest_money'));
    }
    if (!is_numeric($invest_money)) {
        return(L('invest_money').L('only_intval'));
    }
    $vm = getMinfo($invest_uid, 'm.pin_pass,mm.account_money,mm.back_money,mm.money_collect');

    // $pin_pass = $vm['pin_pass'];
    // if(md5($paypass) != $pin_pass) return L('paypass').L('error');  // 支付密码错误



    $borrow = M('borrow_info')
                ->field('id, borrow_uid, borrow_money, has_borrow, has_vouch, borrow_max,borrow_min,
                            borrow_type, password, money_collect')
                ->where("id='{$borrow_id}'")
                ->find();
    if (!$borrow) { // 没有读取到借款数据
        return L('error_parameter');
    }
    $need = $borrow['borrow_money'] - $borrow['has_borrow'];
    if ($borrow['borrow_uid'] == $invest_uid) {// 不能投自己的标
        return L('not_cast_their_borrow');
    }
    if (!empty($borrow['password']) && $borrow['password']!= md5($invest_pass)) { // 定向密码
        return L('error_invest_password');
    }

    if ($borrow['money_collect'] > 0 && $vm['money_collect'] < $borrow['money_collect']) {  // 待收限制
        return L('amount_to_be_received');
    }

    if ($borrow['borrow_min'] > $invest_money) { // 最小投资
        return L('not_less_than_min').$borrow['borrow_min'].L('yuan');
    }
    if ($invest_money%$borrow['borrow_min']) {
        return "投标金额必须为最小投资的整数倍！";
    }
    if (($need - $invest_money) < 0) { // 超出了借款资金
        return L('error_max_invest_money').$need.L('yuan');
    }

    // 避免最后一笔投资剩余金额小于最小资金导致无法投递，再次最后一笔投资可以大于最大投资
    if ($invest_money != $need && ($need-$invest_money) < $borrow['borrow_min']) {
        return L('full_scale_investment').$need.L('yuan');
    }
    if ($borrow['borrow_max'] && $need > ($borrow['borrow_min']*2) && $invest_money > $borrow['borrow_max']) {
        return L('beyond_invest_max');
    }
    if (!empty($coupons)) {
        $couponinfo = explode('|', $coupons);
        $invest_money = $investmoney - intval($couponinfo[0]);
    } else {
        $invest_money = $investmoney;
    }
    $saving=querysaving($invest_uid);
    $balance=querybalance($invest_uid);
    // if(($vm['account_money']+$vm['back_money'])< $invest_money)
     if (($saving+$balance)< $invest_money) {
         return L('lack_of_balance');
     }
    $sina['uid'] = $invest_uid;
    $sina['money'] = $invest_money;
    //新浪代收接口
    $newbid=borrowidlayout1($borrow_id);
    $sina['content'] = "对第".$newbid."号标投资付款";
    $sina['bid'] = $borrow_id;
    $sina['code'] = "1001";
    $sina['return_url'] = "https://".$_SERVER['HTTP_HOST']."/M/invest/jumpsuccess";
    $sina['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/borrownotify";
    $sina['coupons_num'] = $couponinfo;
    $sina['jx_num'] = $jx;
    file_put_contents('sinatestlog.txt', "准备进去新浪：时间：".date("Y-m-d H:i:s")."\n", FILE_APPEND);
    return sinacollecttrade($sina);

   //return sinainvest($sina);
    //return 'TRUE';
}
/**
* @param intval $uid  用户id
* @param flaot $money 提现金额
* @param string $paypass 支付密码
*/
function checkCash($uid, $money)
{
    $pre = C('DB_PREFIX');
    $withdraw_money = floatval($money);
    $vo = M('members')->field('user_regtype,is_vip')->where("id={$uid}")->find();
    if (!is_array($vo)) {
        return false;
    }
    $datag = get_global_setting();
    $txfee = explode("|", $datag['tx_fee']);
    $fee[0]= $txfee[0]; //提现手续费
        $txmoney = explode("-", $txfee[1]); //提现金额范围
        $fee[1] = $txmoney[0];  //最小提现金额

        if ($vo['user_regtype'] == 1) {
            $fee[2] = $txmoney[1]*10000; //个人单笔最大提现金额
            $sinamoney = querysaving($uid);
        } else {
            $fee[2] = $txfee[2]*10000;  //企业单笔最大提现金额
            $sinamoney = querybalance($uid);
        }
    if ($sinamoney<$withdraw_money) {
        return "提现金额大于帐户余额";
    }
    if ($withdraw_money<$fee[1] ||$withdraw_money>$fee[2]) {
        return "单笔提现金额限制为{$fee[1]}-{$fee[2]}元";
    }
    $totalnum = session("num");

        //计算提现手续费
        if ($vo['user_regtype'] == 1 && $vo['is_vip'] == 0) {
            //个人提现手续费
            if ($totalnum>=3) {
                $fee1 = $withdraw_money*$fee[0];
                if ($fee1<2) {
                    $fee1="0.00";
                } else {
                    $fee1 = number_format($fee1, 2, '.', '');
                }
            } else {
                //免费提现额度
                $starttime=strtotime(date('Ymd', time()).'-14 day');
                $endtime=strtotime(date('Ymd', time()).'+1 day -1s');
                $wmap['i.investor_uid'] = $uid;
                $wmap['i.add_time'] = array("between","{$starttime},{$endtime}");
                $wmap['d.repayment_time'] = 0;
                $notbackmoney = M('borrow_investor i')->join('lzh_investor_detail d on d.invest_id = i.id')->where($wmap)->sum('d.capital');
                $map['uid'] = $uid;
                $map['completetime'] = array("between","{$starttime},{$endtime}");
                $map['type'] = 1;
                $map['status'] = 2;
                $czmoney = M('sinalog')->where($map)->sum('money');
                $fee_txmoney = number_format($sinamoney+$notbackmoney-$czmoney, 2, '.', '');
                if ($fee_txmoney<0) {
                    $fee_txmoney=0;
                }
                if ($withdraw_money<$fee_txmoney) {
                    $fee1 = 0;
                } else {
                    $fee1 = ($withdraw_money-$fee_txmoney) * $fee[0];
                    if ($fee1<2) {
                        $fee1="0.00";
                    } else {
                        $fee1 = number_format($fee1, 2, '.', '');
                    }
                }
            }
        } elseif ($vo['is_vip'] == 1) {
            //借款端不收取手续费
            $fee1 = 0;
        } else {
            //企业提现手续费
            $fee1 = $withdraw_money* 0.00005;
            $fee1 = number_format($fee1, 2, '.', '');
            if ($fee1 > 200) {
                $fee1 = number_format(200, 2, '.', '');
            }
        }

    if ($fee1 > 0) {
        //收取手续费
            $money = $sinamoney - $withdraw_money - $fee1;
        if ($money < 0) {
            //提现金额+手续费 大于 余额 在提现金额扣取
                $result = takesina($fee1, $withdraw_money-$fee1, $vo['user_regtype'], $uid);
        } else {
            //提现金额+手续费 小于 余额 在余额扣取
                $result = takesina($fee1, $withdraw_money, $vo['user_regtype'], $uid);
        }
    } else {
        //不收手续费
            $result = takesina($fee1, $withdraw_money, $vo['user_regtype'], $uid);
    }
    return $result;
}

//操作新浪提现
    function takesina($fee, $withdraw, $utype, $uid)
    {
        $sina['uid'] = $uid;
        $sina['withdraw'] = $withdraw;
        $sina['phone'] = "yes";
        if ($utype==1) {
            $sina['fee'] = getfloatvalue($fee, 2);
            $sina['account_type'] = 'SAVING_POT';
        } else {
            $sina['user_fee'] = $fee;
            $sina['account_type'] = 'BASIC';
        }
        if ($fee > "0.00" && $utype==1) {
            return sinafreecollecttrade($sina);
        } else {
            return sinawithdraw($sina);
        }
    }

//获取企业直投借款列表
function getTBorrowList($parm =array())
{
    if (empty($parm['map'])) {
        return;
    }
    $map = $parm['map'];
    $orderby = $parm['orderby'];
    if ($parm['pagesize']) {
        import("ORG.Util.Page");
        $count = M("transfer_borrow_info b")->where($map)->count("b.id");
        $p = new Page($count, $parm['pagesize']);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
    } else {
        $page = "";
        $Lsql = "{$parm['limit']}";
    }
    $pre = C("DB_PREFIX");
    $suffix =C("URL_HTML_SUFFIX");
    $field = "b.id,b.borrow_name,b.borrow_status,b.borrow_money,b.repayment_type,b.min_month,b.transfer_out,b.transfer_back,b.transfer_total,b.per_transfer,b.borrow_interest_rate,b.borrow_duration,b.increase_rate,b.reward_rate,b.deadline,b.is_show,m.province,m.city,m.area,m.user_name,m.id as uid,m.credits,m.customer_name,b.borrow_type,b.b_img,b.add_time,b.collect_day,b.danbao";
    $list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
    //$areaList = getarea();
    foreach ($list as $key => $v) {
        // $list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
        $list[$key]['progress'] = getfloatvalue($v['transfer_out'] / $v['transfer_total'] * 100, 2);
        $list[$key]['need'] = getfloatvalue(($v['transfer_total'] - $v['transfer_out'])*$v['per_transfer'], 2);
        $list[$key]['burl'] = MU("Home/invest_transfer", "invest_transfer", array("id" => $v['id'],"suffix" => $suffix));
        $temp=floor(("{$v['collect_day']}"*3600*24-time()+"{$v['add_time']}")/3600/24);
        $list[$key]['leftdays'] = "{$temp}".'天以上';
        $list[$key]['now'] = time();
        if ($v['danbao']!=0) {
            $list[$key]['danbaoid'] = intval($v['danbao']);
            $danbao = M('article')->field('id,title')->where("type_id=7 and id={$v['danbao']}")->find();
            $list[$key]['danbao']=$danbao['title'];//担保机构
        } else {
            $list[$key]['danbao']='暂无担保机构';//担保机构
        }
    }
    $row = array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

/********************************************
 * @param $find
 * @param $info
 * @return mixed
 * 用于pareser借款信息
 */
function borrow_info_pares($find, $info)
{
    import("@.simple_html_dom.simple_html_dom");
    $html = new simple_html_dom();
    $html->load($info);
    foreach ($html->find('tr') as $val) {
        foreach ($val->children as $key=>$tmp) {
            if ($flag==1) {
                return $tmp;
            }
            if (strstr($tmp, $find)) {
                $flag=1;
            }
        }
    }
    return "<td>无</td>";
}
//获取特定栏目下文章列表
function getArticleList($parm)
{
    if (empty($parm['type_id'])) {
        return;
    }
    //$map['type_id'] = $parm['type_id'];
    $type_id= intval($parm['type_id']);
    $Allid = M("article_category")->field("id")->where("parent_id = {$type_id}")->select();
    $newlist = array();
    array_push($newlist, $parm['type_id']);

    foreach ($Allid as $ka => $v) {
        array_push($newlist, $v["id"]);
    }
    $map['type_id']= array("in",$newlist);

    $Osql="sort_order desc,id DESC";//id DESC,
    $field="id,title,art_set,art_time,art_url,art_img,art_info";
    //查询条件
    if ($parm['pagesize']) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('article')->where($map)->count('id');
        $p = new Page($count, $parm['pagesize']);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    } else {
        $page="";
        $Lsql="{$parm['limit']}";
    }

    $data = M('article')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();

    $suffix=C("URL_HTML_SUFFIX");
    $typefix = get_type_leve_nid($map['type_id']);
    $typeu = implode("/", $typefix);
    foreach ($data as $key=>$v) {
        if ($v['art_set']==1) {
            $data[$key]['arturl'] = (stripos($v['art_url'], "http://")===false)?"http://".$v['art_url']:$v['art_url'];
        }
        //elseif(count($typefix)==1) $data[$key]['arturl'] =
        else {
            $data[$key]['arturl'] = MU("Home/{$typeu}", "article", array("id"=>$v['id'],"suffix"=>$suffix));
        }
    }
    $row=array();
    $row['list'] = $data;
    $row['page'] = $page;

    return $row;
}


/************************************
 * 文章页面链接转换
 */
function setUrl($url)
{
    return "/M/more/notice".$url;
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

/**
 * @param $map 查询条件
 * @param $size 每页显示条数
 * @param int $type 1 默认其他类型  2回收中的普通标
 * @param int $limit
 * @return array|void
 */
function getTenderList($map, $size, $type=1, $limit=10)
{
    $pre = C('DB_PREFIX');
    $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
    $count = M('borrow_investor i')->where($map)->count('i.id');
    //if(empty($map['i.investor_uid'])) return;
    if (empty($map['investor_uid'])) {
        return;
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
    $list=$Model->field(true)->where($map)->order('times ASC')->group('id')->select();
    ////////////////////////视图查询 fan 20130522//////////////////////////////////////////
    foreach ($list as $key=>$v) {
        if ($type==2) {//如果是回收中的普通标则过滤一下债权的
            $uid=$map['investor_uid'];
            // $milist=M("borrow_debt")->query("SELECT  * FROM lzh_borrow_debt t  WHERE t.`borrow_id`= {$v['borrowid']} AND t.`debt_borrow_uid`={$uid} AND t.`debt_status` IN( 4,6,7)");
            // if($milist && count($milist)){
            //     unset($list[$key]);
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
    }

    $row=array();
    $row['list'] = $list;
    $row['total_money'] = M('borrow_investor i')->where($map)->sum('investor_capital');
    $row['total_num'] = $count;
    return $row;
}

function check_set_pinpass($uid)
{
    $uid=intval($uid);
    $result=M("members")->field('pin_pass')->where("id=$uid")->limit(1)->select();
    if ($result[0]["pin_pass"]=="") {
        return false;
    } else {
        return true;
    }
}

function create_token($id)
{
    $token=md5(strtotime("now"));
    session($id, $token);
    return $token;
}

function check_token($id, $token)
{
    $token_session=session($id);
    if ($token==$token_session) {
        session($id, null);
        return true;
    } else {
        return false;
    }
}
/*
function get_day($day_string){
    $day_array=explode("+",$day_string);
    $day=intval(mb_strcut($day_array[0],0,mb_strlen($day_array[0])-1));
    if(count($day_array)==2){
        $day2=intval(mb_strcut($day_array[1],0,mb_strlen($day_array[0])-1));
        $day+=$day2;
    }
    return $day;
}
*/
