<?php
/**
 * 2017 10月活动
 * 
 */
class OctactAction extends ACommonAction
{
    /**
     * 周年庆抢标奖励
     */
    public function zhounian17()
    {
        //手机号
        if ($_REQUEST['user_phone']) {
            // $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            // $where .= " AND w.create_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            // $where .= " AND w.create_time < $e";
        }
        //周年庆幸运儿
        //查询周年有奖标的
        $searchMap = array();
        $searchMap['b.borrow_status'] = array("in", '6,7,9');
        $searchMap['b.test'] = 0;//不是测试标
        $searchMap['b.is_zhounianbiao'] = 1;//不是测试标

        $borrow_list = M("borrow_info b")->where($searchMap)->field("id,borrow_name")->order("b.id desc")->select();
        $arr_list = array();
        foreach ($borrow_list as $k => $b) {
                //土豪财主
                $rich_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON m.id = mi.uid")
                            ->where("bi.borrow_id = ".$b['id'])
                            ->order("money desc,bi.add_time desc")
                            ->field("m.id,m.user_name,m.user_phone,mi.real_name,m.reg_time,sum(bi.investor_capital) as money")
                            ->group("m.id")
                            ->find();
                if($rich_man){
                    $ll["real_name"] = $rich_man["real_name"];
                    $ll["uid"] = $rich_man["id"];
                    $ll["user_phone"] = $rich_man["user_phone"];
                    $ll["price_name"] = "土豪财主奖";
                    $ll["price_money"] = "1800.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = date("Y-m-d H:i:s",$rich_man["reg_time"]);
                    array_push($arr_list, $ll);
                }else{
                    $ll["real_name"] = "-";
                    $ll["uid"] = "-";
                    $ll["user_phone"] = "-";
                    $ll["price_name"] = "土豪财主奖";
                    $ll["price_money"] = "1800.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = "-";
                    array_push($arr_list, $ll);
                }
                //抢标先锋
                    $first_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON m.id = mi.uid")
                            ->where("bi.investor_capital >= 50000 AND bi.borrow_id = ".$b['id'])
                            ->order("bi.add_time asc")
                            ->field("m.id,m.user_name,m.user_phone,mi.real_name,m.reg_time")
                            ->find();
                
                if($first_man && $rich_man["id"] != $first_man["id"]){
                    $ll["real_name"] = $first_man["real_name"];
                    $ll["uid"] = $first_man["id"];
                    $ll["user_phone"] = $first_man["user_phone"];
                    $ll["price_name"] = "抢标先锋奖";
                    $ll["price_money"] = "800.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = date("Y-m-d H:i:s",$first_man["reg_time"]);
                    array_push($arr_list, $ll);
                }else{
                    $ll["real_name"] = "-";
                    $ll["uid"] = "-";
                    $ll["user_phone"] = "-";
                    $ll["price_name"] = "抢标先锋奖";
                    $ll["price_money"] = "800.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = "-";
                    array_push($arr_list, $ll);
                }
                //完美收官
                    $last_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON m.id = mi.uid")
                            ->where("bi.borrow_id = ".$b['id'])
                            ->order("bi.add_time desc")
                            ->field("m.id,m.user_name,m.user_phone,mi.real_name,m.reg_time")
                            ->find();
                
                if($last_man && $rich_man["id"] != $last_man["id"] && $first_man["id"] != $last_man["id"]){
                    $ll["real_name"] = $last_man["real_name"];
                    $ll["uid"] = $last_man["id"];
                    $ll["user_phone"] = $last_man["user_phone"];
                    $ll["price_name"] = "完美收官奖";
                    $ll["price_money"] = "200.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = date("Y-m-d H:i:s",$last_man["reg_time"]);
                    array_push($arr_list, $ll);
                }else{
                    $ll["real_name"] = "-";
                    $ll["uid"] = "-";
                    $ll["user_phone"] = "-";
                    $ll["price_name"] = "完美收官奖";
                    $ll["price_money"] = "200.00";
                    $ll["borrow_name"] = $b["borrow_name"];
                    $ll["reg_time"] = "-";
                    array_push($arr_list, $ll);
                }
        }

        $temp_arr = array();
         if ($_REQUEST['user_phone'] || !empty($_REQUEST['startTime']) || !empty($_REQUEST['endTime'])){
            foreach ($arr_list as $key => $value) {
                if($value["user_phone"] == $search['user_phone'] && $value["user_phone"] != "-" && $_REQUEST['user_phone'])
                {
                    array_push($temp_arr, $arr_list[$key]);
                } elseif (strtotime($value["reg_time"]) >= $s && $value["reg_time"] != "-" && !empty($_REQUEST['startTime']) && empty($_REQUEST['endTime'])) {
                    array_push($temp_arr, $arr_list[$key]);
                }elseif (strtotime($value["reg_time"]) <= $e && $value["reg_time"] != "-" && !empty($_REQUEST['endTime']) && empty($_REQUEST['startTime'])) {
                    array_push($temp_arr, $arr_list[$key]);
                }elseif (strtotime($value["reg_time"]) <= $e && strtotime($value["reg_time"]) >= $s && $value["reg_time"] != "-" && !empty($_REQUEST['endTime']) && !empty($_REQUEST['startTime'])) {
                    array_push($temp_arr, $arr_list[$key]);
                }
            }
            unset($arr_list);
         }
        
        if(count($temp_arr) > 0){
            $list = $temp_arr;
        }else{
            $list = $arr_list;
        }
        $this->assign("list",$list);
        
        //分页
        if ($_REQUEST['execl'] == "execl") {
            import("ORG.Io.Excel");
            $row=array();
            $row[0]=array('用户id','手机号码','真实姓名','注册时间',' 奖项','奖励金额','标的');
            $i=1;
            foreach($list as $v){
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['real_name'] = $v['real_name'];
                $row[$i]['reg_time'] = $v['reg_time'];
                $row[$i]['price'] = $v['price_name'];
                $row[$i]['money'] = $v['price_money'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, 'zhounian17');
            $xls->addArray($row);
            $xls->generateXML("zhounian17");
            exit;
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }

        
        // // 记录查询条件
        $this->assign("search", $search);
        // 方法
        $this->assign("xaction", "zhounian17");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 月底家具大闯关
     */
    public function furniturewin()
    {
        $where['bi.add_time'] = ['between', [strtotime(C("THE_OCTOBER_ACTIVE.start_time")), strtotime(C("THE_OCTOBER_ACTIVE.end_time"))]];
        //手机号
        if ($_REQUEST['user_phone']) {
            $where['m.user_phone'] = $_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        //推荐人手机号码
        if ($_REQUEST['cell_phone']) {
            $where['mi2.cell_phone'] = $_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        if (!empty($_REQUEST['startTime']) || !empty($_REQUEST['endTime'])) {
            $startTime = $_REQUEST['startTime'];
            $endTime = $_REQUEST['endTime'];
            $search['startTime'] = $startTime;
            $search['endTime'] = $endTime;
            $startTime = !empty($startTime) ? $startTime : '1970-01-01';
            $endTime = !empty($endTime) ? $endTime : date('Y-m-d');
            $where['m.reg_time'] = ['between', [strtotime($startTime." 00:00:00"), strtotime($endTime." 23:59:59")]];
        }
        
        if (!empty($_REQUEST['invest_max'])) {
            $search['invest_max'] = $_REQUEST['invest_max'];
            $having[] = 'total_invest <= '.$_REQUEST['invest_max'];
        }
        
        if (!empty($_REQUEST['invest_min'])) {
            $search['invest_min'] = $_REQUEST['invest_min'];
            $having[] = 'total_invest >= '.$_REQUEST['invest_min'];
        }
        
        //实名人数
        $field = "m.id,m.user_phone,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d %H:%i:%s') as reg_date,mi2.cell_phone,
                sum(bi.`investor_capital`) as total_invest";
        
        $list = M('members m')
                ->join('lzh_member_info mi on m.id = mi.uid')
                ->join("lzh_member_info mi2 on m.recommend_id = mi2.uid")
                ->join('lzh_borrow_investor bi on m.id = bi.investor_uid')
                ->where($where)
                ->field($field)
                ->having(implode(' and ', $having))
                ->group('m.id')
                ->select();
        
        if (!empty($list)) 
        { 
            //分页
            if ($_REQUEST['execl'] == "execl") {
                $ispage = 1;
                $limit = "0,1000000";
            } else {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
                $limit = "{$p->firstRow},{$p->listRows}";
            }
            
            $list = M('members m')
                    ->join('lzh_member_info mi on m.id = mi.uid')
                    ->join("lzh_member_info mi2 on m.recommend_id = mi2.uid")
                    ->join('lzh_borrow_investor bi on m.id = bi.investor_uid')
                    ->where($where)
                    ->field($field)
                    ->group('m.id')
                    ->having(implode(' and ', $having))
                    ->limit($limit)
                    ->select();
            
            foreach ($list as $k => &$v) {
                $v['prize_val'] = $this->getPrizeValue($v['total_invest']);
            }
            
            if ($ispage == 1) {
                $header = array('用户id','手机号码','真实姓名','注册时间','推荐人号码','累计投资额','当前兑换家具奖金');
                exportToCSV($header, $list, "furniturewin.csv");
                die;
            }
            
            $this->assign('list', $list);
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "furniturewin");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();  
    }
    
    private function getPrizeValue($money)
    {
        $prize_arr = [
            8 => ['min' => 800001, 'max' => 0, 'value' => 6688],
            7 => ['min' => 500001, 'max' => 800000, 'value' => 3588],
            6 => ['min' => 300001, 'max' => 500000, 'value' => 1288],
            5 => ['min' => 100001, 'max' => 300000, 'value' => 618],
            4 => ['min' => 50001, 'max' => 100000, 'value' => 308],
            3 => ['min' => 30001, 'max' => 50000, 'value' => 168],
            2 => ['min' => 10001, 'max' => 30000, 'value' => 68],
            1 => ['min' => 5000, 'max' => 10000, 'value' => 38]
        ];
        
        $prize_val = 0;
        foreach ($prize_arr as $value) {
            if ($money >= $value['min'] && $money <= $value['max']) 
            {
                $prize_val = $value['value'];
                break;
            }
        }
        
        return $prize_val;
    }
}