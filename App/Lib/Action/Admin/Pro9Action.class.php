<?php
/**
 * 2017 9月活动
 * 
 */
class Pro9Action extends ACommonAction
{
    /**
     * 送现金
     */
    public function nineone()
    {
        $where = 'w.type = 0';
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND mi2.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            $where .= " AND w.create_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND w.create_time < $e";
        }
        
        $field = "w.uid,w.user_phone,c.invest_money,mi1.real_name,date_format(from_unixtime(w.`create_time`),'%Y-%m-%d') as reg_date,mi2.cell_phone,c.invest_money,sum(value) as total_red_packet";
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->group('w.uid')
                ->order("total_red_packet desc")
                ->select();
        
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
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->limit($limit)
                ->group('w.uid')
                ->order("total_red_packet desc")
                ->select();
        
        if ($ispage == 1) {
            $header = array('ID','手机号码','活动期间累计投资额','真实姓名','注册时间','推荐人手机号','获得红包金额');
            exportToCSV($header, $list, "nineone.csv");
            die;
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "nineone");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 砸冰块
     */
    public function ninetwo()
    {
        $where = 'w.type = 1';
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND mi2.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            $where .= " AND w.create_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND w.create_time < $e";
        }
        
        $field = "w.uid,w.user_phone,c.count_1,mi1.real_name,date_format(from_unixtime(w.`create_time`),'%Y-%m-%d') as reg_date,mi2.cell_phone,group_concat(w.name) as prizes,count(w.id) as total_wid";
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->group('w.uid')
                ->order("m.reg_time desc")
                ->select();
        
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
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->limit($limit)
                ->group('w.uid')
                ->order("w.create_time desc")
                ->select();
        
        if (!empty($list)) {
            $model = M('sinalog');
            $pmodel = M('p9_count');
            $smodel = M('members_status');
            $glo = get_global_setting();
            foreach ($list as &$v) {
                $con = [];
                $con = [
                    'type' => 3,
                    'money' => ['egt', 2000],
                    'status' => ['in', [2, 4]],
                    'uid' => $v['uid'],
                    'addtime' => ['between', [$glo['p9_start'], $glo['p9_end']]]
                ];
                $v['invest_count'] = $model->where($con)->count();
                
                $uids = $pmodel->where(['parent_id' => $v['uid']])->field('group_concat(uid) as uids')->find();
                $v['sina_pay_num'] = $smodel->where(['is_pay_passwd' => 1, 'uid' => ['in', implode(',', $uids)]])->count();
            };
        }
        
        if ($ispage == 1) {
            $header = array('ID','手机号码','剩余砸冰次数','真实姓名','注册时间','推荐人手机号','获得的奖品','累计砸冰次数','单笔投资满2000元的次数','累计邀请开通新浪支付人数');
            exportToCSV($header, $list, "ninetwo.csv");
            die;
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "ninetwo");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 抢
     */
    public function ninethree()
    {
        $where = 'w.type = 2';
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            $where .= " AND w.create_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND w.create_time < $e";
        }
        
        $field = "w.uid,w.user_phone,mi1.real_name,date_format(from_unixtime(w.`create_time`),'%Y-%m-%d') as reg_date,mi2.cell_phone,c.count_2,group_concat(w.value) as prizes";
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->group('w.uid')
                ->order("w.create_time desc")
                ->select();
        
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
        
        $list = M('p9_win w')
                ->join('lzh_members m on w.uid = m.id')
                ->join('lzh_member_info mi1 on w.uid = mi1.uid')
                ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
                ->join('lzh_p9_count c on w.uid = c.uid')
                ->where($where)
                ->field($field)
                ->limit($limit)
                ->group('w.uid')
                ->order("w.create_time desc")
                ->select();
        
        if (!empty($list)) {
            $model = M('sinalog');
            $glo = get_global_setting();
            foreach ($list as &$v) {
                $con = [];
                $con = [
                    'status' => 2,
                    'type' => 1,
                    'uid' => $v['uid'],
                    'addtime' => ['between', [$glo['p9_start'], $glo['p9_end']]]
                ];
                $v['charge_count'] = $model->where($con)->count();
            };
        }
        
        if ($ispage == 1) {
            $header = array('ID','手机号码','真实姓名','注册时间','推荐人手机号','剩余抢加息券次数','获得的加息券','累计充值次数');
            exportToCSV($header, $list, "ninethree.csv");
            die;
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "ninethree");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 活动设置
     * @return [type] [description]
     */
    public function config()
    {
        $num = $_GET['_URL_'][3];
        if ($num == 1) 
        {
            $con = ['type' => 3, 'active_type' => 1];
        }
        elseif ($num == 2)
        {
            $con = ['active_type' => 2];
        }
        elseif ($num == 3)
        {
            $con = ['active_type' => 3];
        }
        $model = M("p9_prize");
        $list = $model->field('id,info,type,value,num_total,num_left,odds_0')->where($con)->order('id asc')->select();
        
        $this->prizeset();
        
        $this->assign('list', $list);
        $this->assign("num_total", $model->where($con)->sum("num_total"));
        $this->assign("num_left", $model->where($con)->sum("num_left"));
        $this->assign('num', $num);
        $this->display();
    }
    
    public function prizeset()
    {
        if ($this->isPost()) {
            $ids = $_POST["id"];
            $num_left = $_POST["num_left"];
            $odds_0 = $_POST["odds_0"];
            foreach ($num_left as $key => $val){
                if (empty($val)) {
                    $this->error('剩余数量填写错误');
                    exit;
                }
            }
            
            foreach ($odds_0 as $key => $value) {
                if ($value < 0 || $value > 100) {
                    $this->error('概率填写错误');
                    exit;
                };
            }
            if (array_sum($odds_0) != 100) {
                $this->error('概率总和必须等于100');
                exit;
            }
            
            $tmp_arr = [];
            $tmp_maxnum = 0;
            foreach ($ids as $k => $v) {
                $tmp_arr[$k]['id'] = $v;
                $tmp_arr[$k]['num_left'] = $num_left[$k];
                $tmp_arr[$k]['odds_0'] = $odds_0[$k];
                $num = $odds_0[$k] * 100;
                $tmp_arr[$k]['minnum_0'] = $tmp_arr[$k]['maxnum_0'] = 0;
                if ($num != 0) {
                    $tmp_arr[$k]['minnum_0'] = 0;
                    $tmp_arr[$k]['maxnum_0'] = $num - 1;
                    if ($k != 0)
                    {
                        $tmp_arr[$k]['minnum_0'] = $tmp_maxnum + 1;
                        $max_num = $tmp_arr[$k]['minnum_0'] + $num;
                        $tmp_arr[$k]['maxnum_0'] = $k == count($ids) - 1 ? $max_num : $max_num - 1;
                    }
                    $tmp_maxnum = $tmp_arr[$k]['maxnum_0'];
                }
                
                $res = M('p9_prize')->save($tmp_arr[$k]);
                if ($res === false) {
                    $this->error('修改失败');
                    exit;
                }
            }
            
            $this->success('操作成功');
        }
    }

    /**
     * 荣耀之争1
     * @return [type] [description]
     */
    public function ryone()
    {
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        //手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND mi2.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            $where .= " AND m.reg_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND m.reg_time < $e ";
        }
        
        if (!empty($_REQUEST['invest_max'])) {
            $search['invest_max'] = $_REQUEST['invest_max'];
            $where .= " AND bi.investor_capital <= ".$_REQUEST['invest_max']."  ";
        }
        
        if (!empty($_REQUEST['invest_min'])) {
            $search['invest_min'] = $_REQUEST['invest_min'];
            $where .= " AND bi.investor_capital >= ".$_REQUEST['invest_min']." ";
        }
        
        $uids = M('p9_count')->getField('id,uid');
        $field = "m.id,m.user_phone,mi1.real_name,m.reg_time,mi2.cell_phone,bi.add_time,bi.investor_capital";
        
        $sql = "SELECT
                obj.id,obj.user_phone,obj.real_name,obj.reg_time,obj.cell_phone,obj.add_time,obj.investor_capital,
                CASE
                WHEN @rowtotal = obj.investor_capital THEN
                    @rownum
                WHEN @rowtotal := obj.investor_capital THEN
                    @rownum :=@rownum + 1
                WHEN @rowtotal = 0 THEN
                    @rownum :=@rownum + 1
                END AS rownum
                FROM
                    (
                        SELECT ".$field."
    					FROM lzh_borrow_investor bi 
    					LEFT JOIN lzh_members m on bi.investor_uid = m.id 
    					LEFT JOIN lzh_member_info mi1 on m.id = mi1.uid 
    					LEFT JOIN lzh_member_info mi2 on m.recommend_id = mi2.uid 
    					WHERE m.id in (".implode(',', $uids).") ".$where."
    					ORDER BY investor_capital desc 
                    ) AS obj,
                (SELECT @rownum := 0 ,@rowtotal := NULL) r ";
        
        if (!empty($uids)) { 
            $list = M()->query($sql);
//             $where1 = "m.id in (".implode(',', $uids).")";
//             $where1 .= $where;
            
//             $count = M('borrow_investor bi')
//                     ->join('lzh_members m on bi.investor_uid = m.id')
//                     ->join('lzh_member_info mi1 on m.id = mi1.uid')
//                     ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
//                     ->where($where1)
//                     ->field($field)
//                     ->order("investor_capital desc")
//                     ->count();
            
//             //分页
            if ($_REQUEST['execl'] == "execl") {
                $ispage = 1;
//                 $limit = "0,1000000";
            } 
            else 
            {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
//                 $limit = "{$p->firstRow},{$p->listRows}";
            }
            
//             $list = M('borrow_investor bi')
//                     ->join('lzh_members m on bi.investor_uid = m.id')
//                     ->join('lzh_member_info mi1 on m.id = mi1.uid')
//                     ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
//                     ->where($where1)
//                     ->field($field)
//                     ->limit($limit)
//                     ->order("investor_capital desc")
//                     ->select();
            
            $pg = $_GET['p'];
            $pg = empty($pg) ? 1 : $pg;
            $show_list = array_slice($list, ($pg - 1) * C('ADMIN_PAGE_SIZE'), C('ADMIN_PAGE_SIZE'));

            if ($ispage == 1) {
                $header = array('用户id','手机号码','真实姓名','注册时间','推荐人号码','投资时间','单笔投资金额','实时排名');
                foreach ($list as $key => &$v) {
                    $v['reg_time'] = date("Y-m-d", $v['reg_time']);
                    $v['add_time'] = date("Y-m-d", $v['add_time']);
                }
                
                exportToCSV($header, $list, "ryone.csv");
                die;
            }
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $show_list);
        // 方法
        $this->assign("xaction", "ryone");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 荣耀之争2
     * @return [type] [description]
     */
    public function rytwo()
    {
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        //手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND mi2.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        if (!empty($_REQUEST['startTime'])) {
            $startTime = $_REQUEST['startTime'];
            $s = strtotime($startTime." 00:00:00");
            $search['startTime'] = $startTime;
            $where .= " AND m.reg_time > $s";
        }
        
        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND m.reg_time < $e ";
        }
        
        $min = 0;
        if (!empty($_REQUEST['invest_max'])) {
            $search['invest_max'] = $_REQUEST['invest_max'];
            $max = $_REQUEST['invest_max'];
        }
        
        if (!empty($_REQUEST['invest_min'])) {
            $search['invest_min'] = $_REQUEST['invest_min'];
            $min = $_REQUEST['invest_min'];
        }
        $having = 'sum_investor_capital >= '.$min;
        if (isset($max)) {
            $having .= ' and sum_investor_capital <= '.$max;
        }
        
        $uids = M('p9_count')->getField('id,uid');
        
        //实名人数
        $field = "m.id,m.user_phone,mi1.real_name,m.reg_time,mi2.cell_phone,sum(bi.investor_capital) as sum_investor_capital";
        $sql = "SELECT
                obj.id,obj.user_phone,obj.real_name,obj.reg_time,obj.cell_phone,obj.sum_investor_capital,
                CASE
                WHEN @rowtotal = obj.sum_investor_capital THEN
                    @rownum
                WHEN @rowtotal := obj.sum_investor_capital THEN
                    @rownum :=@rownum + 1
                WHEN @rowtotal = 0 THEN
                    @rownum :=@rownum + 1
                END AS rownum
                FROM
                    (
                        SELECT ".$field."
    					FROM lzh_borrow_investor bi
    					LEFT JOIN lzh_members m on bi.investor_uid = m.id
    					LEFT JOIN lzh_member_info mi1 on m.id = mi1.uid
    					LEFT JOIN lzh_member_info mi2 on m.recommend_id = mi2.uid
                        WHERE m.id in (".implode(',', $uids).") ".$where."
                        GROUP BY bi.investor_uid
    					HAVING ".$having."
    					ORDER BY sum_investor_capital desc
                    ) AS obj,
                    (SELECT @rownum := 0 ,@rowtotal := NULL) r";
        if (!empty($uids)) {
            $list = M()->query($sql);
            
//             $where1 = "m.id in (".implode(',', $uids).")";
//             $where1 .= $where;
            
//             $list = M('borrow_investor bi')
//                     ->join('lzh_members m on bi.investor_uid = m.id')
//                     ->join('lzh_member_info mi1 on m.id = mi1.uid')
//                     ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
//                     ->where($where1)
//                     ->field($field)
//                     ->group('bi.investor_uid')
//                     ->order('sum_investor_capital desc')
//                     ->HAVING($having)
//                     ->select();
            
            //分页
            if ($_REQUEST['execl'] == "execl") {
                $ispage = 1;
//                 $limit = "0,1000000";
            } else {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
//                 $limit = "{$p->firstRow},{$p->listRows}";
            }
            
//             $list = M('borrow_investor bi')
//                     ->join('lzh_members m on bi.investor_uid = m.id')
//                     ->join('lzh_member_info mi1 on m.id = mi1.uid')
//                     ->join('lzh_member_info mi2 on m.recommend_id = mi2.uid')
//                     ->where($where1)
//                     ->field($field)
//                     ->limit($limit)
//                     ->group('bi.investor_uid')
//                     ->order('sum_investor_capital desc')
//                     ->HAVING($having)
//                     ->select();

            $pg = $_GET['p'];
            $pg = empty($pg) ? 1 : $pg;
            $show_list = array_slice($list, ($pg - 1) * C('ADMIN_PAGE_SIZE'), C('ADMIN_PAGE_SIZE'));
            
            if ($ispage == 1) {
                $header = array('用户id','手机号码','真实姓名','注册时间','推荐人号码','活动期间累计投资金额','实时排名');
                foreach ($list as $key => &$v) {
                    $v['reg_time'] = date("Y-m-d", $v['reg_time']);
                }
                
                exportToCSV($header, $list, "rytwo.csv");
                die;
            }
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $show_list);
        // 方法
        $this->assign("xaction", "rytwo");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
}