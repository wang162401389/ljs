<?php
// 全局设置
class YwaggAction extends ACommonAction
{
    /**
     * 注册统计
     * @return [type] page
     */
    public function regAggregate(){
        $where = "equipment in ('PC', 'APP', 'WeChat')";
        if ($_REQUEST["reg_time"]) {
            $where .= " and FROM_UNIXTIME(reg_time,'%Y-%m-%d') = '$_REQUEST[reg_time]'";
            $reg_time = $_REQUEST['reg_time'];
            
            $field = "sum(case when equipment='PC'      then 1 else 0 end) as pc,
                        sum(case when equipment='APP'     then 1 else 0 end) as app,
                        sum(case when equipment='WeChat'  then 1 else 0 end) as wechat,
                        FROM_UNIXTIME(reg_time,'%Y-%m-%d')
                    ";
            
            $result = M()->query("SELECT $field FROM lzh_members where ".$where);
            
            //注册人数
            $total_count = $result[0]['pc'] + $result[0]['app'] + $result[0]['wechat'];
            $this->assign('total_count', !empty($total_count) ? $total_count : 0);
            //PC注册人数
            $this->assign('pc_reg_count', !empty($result[0]['pc']) ? $result[0]['pc'] : 0);
            //APP注册人数
            $this->assign('app_reg_count', !empty($result[0]['app']) ? $result[0]['app'] : 0);
            //微信注册人数
            $this->assign('wechat_reg_count', !empty($result[0]['wechat']) ? $result[0]['wechat'] : 0);
            
            //实名人数
            $realname_count = M('members m')->join('lzh_members_status ms on m.id = ms.uid')->where("ms.id_status = 1 and $where")->count();
            $this->assign('realname_count', $realname_count);
            
            $b_where = " and equipment in ('PC', 'APP', 'WeChat')";
            
            //投资人数
            $investor_count = M('borrow_investor bi')->join('lzh_members m on m.id = bi.investor_uid')
            ->where("FROM_UNIXTIME(bi.add_time,'%Y-%m-%d') = '$reg_time' $b_where and FROM_UNIXTIME(m.reg_time,'%Y-%m-%d') = '$reg_time'")->count('distinct(bi.investor_uid)');
            $this->assign('investor_count', $investor_count);
            
            //发标总额
            $borrow_sum = M('borrow_info bi')->join('lzh_members m on m.id = bi.borrow_uid')
            ->where("FROM_UNIXTIME(add_time,'%Y-%m-%d') = '$reg_time' $b_where")->sum('borrow_money');
            $this->assign('borrow_sum', $borrow_sum);
            
            //充值金额
            $charge_sum = M('sinalog s')->join('lzh_members m on m.id = s.uid')
            ->where("FROM_UNIXTIME(addtime,'%Y-%m-%d') = '$reg_time' and type = 1 $b_where")->sum('money');
            $this->assign('charge_sum', $charge_sum);
            
            $field = "sum(case when m.is_vip = 1 then money end ) as jie_sum, sum(case when m.is_vip = 0 then money end ) as invest_sum";
            $result = M('sinalog s')->join('lzh_members m on m.id = s.uid')
            ->where("FROM_UNIXTIME(s.addtime,'%Y-%m-%d') = '$reg_time' and s.type = 1 $b_where")->field($field)->select();
            
            //投资人充值额
            $this->assign('jie_sum', !empty($result[0]['jie_sum']) ? $result[0]['jie_sum'] : 0);
            //借款人充值额
            $this->assign('invest_sum', !empty($result[0]['invest_sum']) ? $result[0]['invest_sum'] : 0);
            
            $this->assign('reg_time', $reg_time);
        }
        
        $this->assign("xaction", "regAggregate");
        $this->display();
    }

    /**
     * 投资统计
     * @return [type] page
     */
    public function investAggregate(){
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $start_time = $_REQUEST['start_time'];
            $end_time = $_REQUEST['end_time'];
            
            $time_period = ["between", strtotime($start_time." 00:00:00").",".strtotime($end_time." 23:59:59")];
            
            $investor_model = M('borrow_investor');
            
            $field = "count(distinct(investor_uid)) as investor_p_count,count(*) as investor_n_count,sum(investor_capital) as total_invest";
            $investor = $investor_model->where(['add_time' => $time_period])->field($field)->find();
            
            //投资人数
            $this->assign('investor_p_count', $investor['investor_p_count']);
            //投资笔数
            $this->assign('investor_n_count', $investor['investor_n_count']);
            //总投资金额
            $this->assign('total_invest', $investor['total_invest']);
            
            //发标总额
            $total_borrow_money = M('borrow_info')->where(['add_time' => $time_period])->sum('borrow_money');
            $this->assign('total_borrow_money', $total_borrow_money > 0 ? $total_borrow_money : 0);
            
            //投资券使用总额
            $invest_money_num = M('sinalog s')->join('lzh_coupons c on s.coupons = c.serial_number')
            ->where(['s.addtime' => $time_period, 's.type' => 3, 's.status' => ['in', [2, 4]]])->sum('c.money');
            $this->assign('invest_money_num', $invest_money_num > 0 ? $invest_money_num : 0);
            
            //体验店投资总额
            $recommend_id_arr = M('members')->field('GROUP_CONCAT(id) as idstr')->where(['recommend_id' => ['in', C('OFFLINE_UID')]])->find();
            
            $experience_uid_arr = array_merge(C('OFFLINE_UID'), explode(',', $recommend_id_arr['idstr']));
            
            $experience_shop_invest_sum = $investor_model->where(['add_time' => $time_period, 'investor_uid' => ['in', $experience_uid_arr]])->sum('investor_capital');
            $this->assign('experience_shop_invest_sum', $experience_shop_invest_sum);
            
            //内部员工投资总额
            $inner_invest_sum = $investor_model->where(['add_time' => $time_period, 'investor_uid' => ['in', C('CCFAX_USER')]])->sum('investor_capital');
            $this->assign('inner_invest_sum', $inner_invest_sum);
            
            //其他投资总额
            $other_uid_arr = ['not in', array_merge(C('CCFAX_USER'), $experience_uid_arr)];
            $other_invest_sum = $investor_model->where(['add_time' => $time_period, 'investor_uid' => $other_uid_arr])->sum('investor_capital');
            $this->assign('other_invest_sum', $other_invest_sum);
            
            $this->assign('m_total', $experience_shop_invest_sum + $inner_invest_sum + $other_invest_sum);
            
            $this->assign('start_time', $start_time);
            $this->assign('end_time', $end_time);
        }
        
        $this->assign("xaction", "investAggregate");
        $this->display();
    }

    /**
     * cps　统计
     * @return [type] page
     */
    public function cpsAggregate(){
        if (!empty($_REQUEST['startTime']) && !empty($_REQUEST['endTime'])) {
            $startTime = $_REQUEST['startTime'];
            $endTime = $_REQUEST['endTime'];
        
            $timeSpan = ["between", strtotime($startTime." 00:00:00").",".strtotime($endTime." 23:59:59")];
            $equipment_name=["in","PC,APP,fengche,WeChat"];
            $members = M('members');
            $groupres = $members->field('equipment as src,count(*) as no')->group('equipment')->where(['reg_time' => $timeSpan,"equipment" => $equipment_name])->select();
            
            $aggre = [];
            if (!empty($groupres)) {
                $borrow_investor = M('borrow_investor');
                $investor_detail_experience = M('investor_detail_experience');
                foreach ($groupres as $key => $value) {
                    $equip = $value['src'];
                    
                    //实名人数
                    $idstatusNum = M('members m')->field('m.id,s.id_status,.m.reg_time')
                    ->join('lzh_members_status s on m.id = s.uid')
                    ->where(['m.equipment' => $equip, 's.id_status' => 1, 'm.reg_time' => $timeSpan])
                    ->count();
                    
                    //不同来源对应的id列表
                    $idsRes = $members->field('id,equipment,reg_time')->where(['equipment' => $equip, 'reg_time' => $timeSpan])->select();
                    $ids = array_column($idsRes, 'id');
                    
                    $in_ids = ['in', $ids];
                    
                    //投资笔数
                    $investTransRes = $borrow_investor->where(['investor_uid' => $in_ids])->count();
                    
                    //投资总额
                    $investTotalRes = $borrow_investor->where(['investor_uid' => $in_ids])->sum('investor_capital');
                    
                    //投资人数
                    $investHeadcountRes = $borrow_investor->field('id')->where(['investor_uid' => $in_ids])->group('investor_uid')->select();
                    $investHeadcountRes = sizeof($investHeadcountRes);
                    
                    //体验金使用人数
                    $trailNumRes = $investor_detail_experience->where(['investor_uid' => $in_ids])->count();
                    
                    $tmp = [];
                    $tmp['name']            = $equip;
                    $tmp['regNum']          = $value['no'];
                    $tmp['realnameNum']     = $idstatusNum;
                    $tmp['trailNum']        = is_null($trailNumRes) ? 0 : $trailNumRes;
                    $tmp['investHeadCount'] = is_null($investHeadcountRes) ? 0 : $investHeadcountRes;
                    $tmp['investAmount']    = is_null($investTotalRes) ? 0 : $investTotalRes;
                    $tmp['investNum']       = is_null($investTransRes) ? 0 : $investTransRes;
                    $aggre[] = $tmp;
                }
            }
        }
        
        $this->assign('list', $aggre);
        $this->assign("xaction", "cpsAggregate");
        $this->display();
    }

    /**
     * 拉新统计
     * @return [type] page
     */
    public function recommendAggregate(){
        if($this->isPost()){
            if (!empty($_POST['start_time']) && empty($_POST['end_time'])) {
                $start_time = strtotime($_POST['start_time']."  00:00:00");
                $search['start_time'] = $_POST['start_time'];
            }
            if (!empty($_POST['end_time']) && empty($_POST['start_time'])) {
                $end_time = strtotime($_POST['end_time']."  23:59:59");
                $search['end_time'] = $_POST['end_time'];
            }

            if (!empty($_POST['end_time']) && !empty($_POST['start_time'])) {
                $start_time = strtotime($_POST['start_time']."  00:00:00");
                $end_time = strtotime($_POST['end_time']." 23:59:59");
                $search['start_time'] = $_POST['start_time'];
                $search['end_time'] = $_POST['end_time'];
            }

            $offline_uid = C('OFFLINE_UID');
            $company_uid = array_diff(C('CCFAX_USER'),$offline_uid);

            $all_list = M("borrow_investor b")
                        ->join("lzh_members m ON m.id = b.investor_uid")
                        ->where("b.investor_uid >= 73")->group("b.investor_uid")->field("MIN(b.add_time) as first_time,b.investor_uid,b.investor_capital,m.recommend_id")->select();
            $total_money = 0;
            $offline_total = 0;
            $company_total = 0;
            $other_total = 0;
            foreach ($all_list as $key => $value) {
                if($value["first_time"] >= $start_time && $value["first_time"] <= $end_time){
                    if(in_array($value["investor_uid"], $offline_uid) || in_array($value["recommend_id"], $offline_uid)){
                        $offline_total += $value["investor_capital"];
                    }
                    if(in_array($value["recommend_id"], $company_uid)){
                        $company_total += $value["investor_capital"];
                    }
                    $total_money += $value["investor_capital"];
                }
            }
            $other_total =  $total_money - $offline_total - $company_total;

            $this->assign("offline",$offline_total);
            $this->assign("offline_percent",round(($offline_total/$total_money*100),2));
            $this->assign("company",$company_total);
            $this->assign("company_percent",round(($company_total/$total_money*100),2));
            $this->assign("other",$other_total);
            $this->assign("other_percent",round(($other_total/$total_money*100),2));
            $this->assign("total",$total_money);
            $this->assign('search', $search);
        }
        $this->display();
    }

    /**
     * 充值统计
     * @return [type] page
     */
    public function rechargeAggregate(){
        if($this->isPost()){
            if(!empty($_POST["uid"])){
                $where["s.uid"] = intval($_POST["uid"]);
                $where1["i.investor_uid"] = intval($_POST["uid"]);
                $search["uid"] = intval($_POST["uid"]);
            }

            if(!empty($_POST["real_name"])){
                $where["mi.real_name"] = array('like','%'.trim($_POST["real_name"]).'%');
                $where1["mi.real_name"] = array('like','%'.trim($_POST["real_name"]).'%');
                $search["real_name"] = trim($_POST["real_name"]);
            }

            if (!empty($_POST['start_time']) && empty($_POST['end_time'])) {
                $where["s.addtime"] = array("egt",strtotime($_POST['start_time']."  00:00:00"));
                $where1["i.completetime"] = array("egt",strtotime($_POST['start_time']."  00:00:00"));
                $search['start_time'] = $_POST['start_time'];
            }
            if (!empty($_POST['end_time']) && empty($_POST['start_time'])) {
                $where["s.addtime"] = array("elt",strtotime($_POST['end_time']."  23:59:59"));
                $where1["i.completetime"] = array("elt",strtotime($_POST['end_time']."  23:59:59"));
                $search['end_time'] = $_POST['end_time'];
            }

            if (!empty($_POST['end_time']) && !empty($_POST['start_time'])) {
                $where["s.addtime"] = array("between",strtotime($_POST['start_time']."  00:00:00").",".strtotime($_POST['end_time']." 23:59:59"));
                $where1["s.completetime"] = array("between",strtotime($_POST['start_time']."  00:00:00").",".strtotime($_POST['end_time']." 23:59:59"));
                $search['start_time'] = $_POST['start_time'];
                $search['end_time'] = $_POST['end_time'];
            }

            $where['s.type'] = 1;
            $where["s.status"] = 2;
            //充值金额
            $charge_money = M("member_info mi")
                            ->join("lzh_sinalog s ON mi.uid = s.uid")
                            ->where($where)->group("s.uid")->field("s.uid,mi.real_name,SUM(money) as charge_money")->select();
            $charge_list = array();
            foreach ($charge_money as $key => $value) {
                $charge_list[$value["uid"]] = $value;
            }
           
            //回款金额
            // $where1["repayment_time"] = array("gt",0);
            $repayment_money = M("sinalog s")
                                ->join("lzh_investor_detail i ON i.borrow_id = s.borrow_id AND s.sort_order = i.sort_order")
                                ->join("lzh_member_info mi ON mi.uid = i.investor_uid")
                                ->where($where1)->group("i.investor_uid")->field("i.investor_uid as uid,mi.real_name,SUM(i.capital+i.interest+i.expired_money) as receive_money")->select();
                                // echo M("sinalog s")->getLastsql();die;
            $repayment_list = array();
            foreach ($repayment_money as $key => $value) {
                $repayment_list[$value["uid"]] = $value;
            }
            // echo M("investor_detail i")->getLastsql();

            $list = array_merge($charge_list,$repayment_list);
            $temp_arr = array();
            foreach($list as $k => $v){
                        if(!isset($temp_arr[$v['uid']])){
                            
                            $temp_arr[$v['uid']] = $v;
                            
                            continue;
                        }
                        
                        $field = isset($v['charge__money']) ? 'charge__money' : 'receive_money';
                        
                        $temp_arr[$v['uid']] = array_merge($temp_arr[$v['uid']], $v);
                        
                    }

            // print_r($charge_list);
            // echo "<br>";
            // print_r($repayment_list);
            // echo "<br>";
            // print_r($temp_arr);
            // print_r($this->more_array_unique($list));
            // $list = array();
            // foreach ($charge_money as $key => $value) {
            //     $where1["investor_uid"] = $value["uid"];
                
            //     $value["receive_money"] = $repayment_money;
            //     $list[$key] = $value;
            // }
            $this->assign('list',$temp_arr);
            $this->assign('search', $search);
        }
        
        $this->display();
    }
    
    /**
     * 标的统计数据
     * @return [type] page
     */
    public function contractAggregate(){
        $where = "status in (1,4,5,6,7)";
        if (!empty($_POST['search_time'])) {
            $dates[$i]["days"] = date("Y-m-d",strtotime(trim($_POST['search_time'])));
            // $where .= " AND FROM_UNIXTIME(add_time,'%Y-%m-%d') = '".date("Y-m-d",strtotime(trim($_POST['search_time'])))."'";
            $search['search_time'] = $_POST['search_time'];
        }else{
            $dates = array();
            $start_date = "2015-10-7";
            $today = date("Y-m-d",time());
            $i = 0;
            while ( $today >= $start_date) {
                $dates[$i]["days"] = $today;
                $i++;
                $today = date("Y-m-d",strtotime($today."-1day"));
            }
        }
        import("ORG.Util.PageFilter");
        $p = new PageFilter(count($dates), $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $start_key = $p->firstRow;
        $end_key = $p->firstRow + $p->listRows;
        // echo $limit;die;
        // $borrow_money = M("borrow_investor")->where($where)->field("FROM_UNIXTIME(add_time,'%Y年%m月%d日') as days")->order("days desc")->group("days")->select();
        
        $list = array();
        foreach ($dates as $key => $value) {
            if($key>= $start_key &&  $key < $end_key){
                $day = $value["days"];
                $invest_money = M("borrow_investor")->where("status in (1,4,5,6,7) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->sum("investor_capital");
                $invest_count = M("borrow_investor")->where("status in (1,4,5,6,7) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->group("investor_uid")->select();
                $borrow_man = M("borrow_info")->where("borrow_status in (2,3,4,6,7,8,9) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->group("borrow_uid")->select();
                $borrow_count = M("borrow_info")->where("borrow_status in (2,3,4,6,7,8,9) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->count();
                $borrow_rate = M("borrow_info")->where("borrow_status in (2,3,4,6,7,8,9) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->sum("borrow_interest_rate");
                $sum_borrow = M("borrow_info")->where("borrow_status in (2,3,4,6,7,8,9) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->sum("borrow_money");
                $borrow = M("borrow_info")->where("borrow_status in (2,3,4,6,7,8,9) and FROM_UNIXTIME(add_time,'%Y-%m-%d') = '{$day}'")->field("repayment_type,borrow_duration")->select();
                $total_day = 0;
                foreach ($borrow as $k => $v) {
                    if($v["repayment_type"] == 1){
                        $total_day += $v["borrow_duration"];
                    }else{
                        $total_day += $v["borrow_duration"]*30;
                    }
                }
                $value["invest_money"] = $invest_money;
                $value["borrow_money"] = $sum_borrow;
                $value["invest_count"] = count($invest_count);
                $value["borrow_man"] = count($borrow_man);
                $value["borrow_rate"] = round($borrow_rate/$borrow_count,2);
                $value["borrow_month"] = round(($total_day/30)/$borrow_count,2);
                $list[$key] = $value;
            }elseif($key >= $end_key){
                break;
            }
        }
        $this->assign('pagebar', $page);
        $this->assign('list',$list);
        $this->assign('search', $search);
        $this->display();
    }
}