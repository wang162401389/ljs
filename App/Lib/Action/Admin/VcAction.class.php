<?php
/**
 * 链金所A轮融资活动后台
 */
class VcAction extends ACommonAction
{
    /**
     * 新人返利
     * 100  - 999    返利5元
     * 1000 - 4999   返利15元
     * 5000 - 19999  返利25元
     * 20000 -       返利35元
     * @return [type] page
     */
    public function openaccountoffer()
    {
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND mi2.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
            $conditional = true;
        }

        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND v.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
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

        $field = "v.uid,v.user_phone,mi.real_name,date_format(from_unixtime(`reg_time`),'%Y-%m-%d') as reg_date,mi2.cell_phone,v.invest_money,case  when v.invest_money >=99 and v.invest_money<=999 then 5 when v.invest_money>=1000 and v.invest_money<=4999 then 15 when v.invest_money>=5000 and v.invest_money<=19999 then 25 when v.invest_money>=20000 then 35 else 0 end as gift,v.parent_id,m.reg_time";

        $where1 = "v.invest_money<>0";
        $where1 .=$where;
        //exit(json_encode($where1));
        $count = M('vc_recom v')->join("lzh_member_info mi on mi.uid=v.uid")
                               ->join("lzh_member_info mi2 on mi2.uid=v.parent_id")
                               ->join("lzh_members m on m.id=v.uid")
                               ->field($field)
                               ->order("v.id desc")
                               ->where($where1)
                               ->count();

        //分页
        if ($_REQUEST['execl'] == "execl") {
            $ispage = 1;
            $limit = "0,1000000";
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }


        $list = M('vc_recom v')->join("lzh_member_info mi on mi.uid=v.uid")
                               ->join("lzh_member_info mi2 on mi2.uid=v.parent_id")
                               ->join("lzh_members m on m.id=v.uid")
                               ->field($field)
                               ->order("v.id desc")
                               ->where($where1)
                               ->limit($limit)
                               ->select();


        if ($ispage == 1) {
          
            $header = array('ID','手机号码','真实姓名','注册时间','推荐人手机号','活动期间累计投资额','应得返现');
            $data = $list;
            foreach ($data as $key => $v) {
              unset($data[$key]['reg_time']);
              unset($data[$key]['parent_id']);
            }
            
            exportToCSV($header,$data,"xinshoufanli.csv");
            die;
        }

        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "openaccountoffer");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();  
    }

    /**
     * 大转盘活动
     * @return [type] [description]
     */
    public function prizewheel()
    {
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND recm.user_phone=".$_REQUEST['cell_phone'];
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
            $where .= " AND m.reg_time > $s";
        }

        if (!empty($_REQUEST['endTime'])) {
            $endTime = $_REQUEST['endTime'];
            $e = strtotime($endTime." 23:59:59");
            $search['endTime'] = $endTime;
            $where .= " AND m.reg_time < $e ";
        }

        //所有参与大转盘抽奖的用户
        $idList = M('vc_count')->field('uid')->select();
        $idList = array_column($idList, "uid");

        $field = "bi.investor_uid as xx,m.user_phone,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d') as aaa,recm.user_phone as rec_user_phone,bi.investor_capital,cn.consume_no+cn.count_left as total,cn.count_left,cn.prize,bi.investor_uid,m.reg_time,m.recommend_id,cn.consume_no,bi.id";
        //$where['investor_uid'] = array('in',$idList);
        //$where1 = " bi.investor_uid in (".implode(',',$idList).') ';
        $where1 = " bi.investor_uid in (".implode(',',$idList).')  and bi.add_time >  '.C("VC_FROM");

        $where1 .= $where;
        
        $count = M('borrow_investor bi')->join("lzh_members m on m.id=bi.investor_uid")
                               ->join("lzh_member_info mi on mi.uid=bi.investor_uid")
                               ->join("lzh_members recm on recm.id=m.recommend_id ")
                               ->join("(select count_0+count_1+count_2+count_3+count_4 as count_left,uid,prize,consume_no from lzh_vc_count where 1 ) cn on bi.investor_uid=cn.uid")
                               ->field($field)
                               ->where($where1)
                               ->count();

        //分页
        if ($_REQUEST['execl'] == "execl") {
            $ispage = 1;
            $limit = "0,1000000";
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }
        
        $list = M('borrow_investor bi')->join("lzh_members m on m.id=bi.investor_uid")
                               ->join("lzh_member_info mi on mi.uid=bi.investor_uid")
                               ->join("lzh_members recm on recm.id=m.recommend_id ")
                               ->join("(select count_0+count_1+count_2+count_3+count_4 as count_left,uid,prize,consume_no from lzh_vc_count where 1 ) cn on bi.investor_uid=cn.uid")
                               ->field($field)
                               ->where($where1)
                               ->limit($limit)
                               ->select();
        if ($ispage == 1) {
            $header = array('ID','手机号码','真实姓名','注册时间','推荐人手机号','单笔投资金额','累计抽奖次数','剩余抽奖次数','获得奖品');
            $data = $list;
            foreach ($data as $key => $v) {
              unset($data[$key]['investor_uid']);
              unset($data[$key]['reg_time']);
              unset($data[$key]['recommend_id']);
              unset($data[$key]['consume_no']);
            }
            
            exportToCSV($header,$data,"laoyonghuchoujiang.csv");
            die;
        }
                               
        $this->assign("list", $list);
         // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "prizewheel");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();  
    }

    /**
     * 新老用户推荐有奖
     * @return [type] [description]
     */
    public function recommend()
    {
        //手机号
        if ($_REQUEST['user_phone']) {
            $where .= " AND m.user_phone=".$_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
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

        $idList = M('vc_recom')->where(array('parent_id'=>array("NEQ",0)))->field('parent_id')->group('parent_id')->select();
        $idList = array_column($idList, "parent_id");

        $field = "m.id,m.user_phone,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d') as reg_date,re.rec_total,case  when re_invest.rec_invest_total >= 4 then 20 when re_invest.rec_invest_total >=3  then 15 when re_invest.rec_invest_total >= 2 then 10 when re_invest.rec_invest_total >=1 then 5 end ,re_invest.rec_invest_total,m.reg_time";

        $where1 = " m.id in (".implode(',',$idList).') ';
        $where1 .= $where;
        
        $count = M('members m')->join("lzh_member_info mi on m.id=mi.uid")
                              //累计邀请人数
                              ->join("(select parent_id,count(*) as rec_total from lzh_vc_recom where parent_id<>0 group by parent_id ) re on m.id=re.parent_id ")
                              //投资人数
                              ->join("(select parent_id,count(*) as rec_invest_total from lzh_vc_recom where parent_id<>0 and invest_money<>0 group by parent_id) re_invest on m.id=re_invest.parent_id")
                              ->field($field)
                              ->where($where1)
                              ->count();

        //分页
        if ($_REQUEST['execl'] == "execl") {
            $ispage = 1;
            $limit = "0,1000000";
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }  

        $list = M('members m')->join("lzh_member_info mi on m.id=mi.uid")
                              //累计邀请人数
                              ->join("(select parent_id,count(*) as rec_total from lzh_vc_recom where parent_id<>0 group by parent_id ) re on m.id=re.parent_id ")
                              //投资人数
                              ->join("(select parent_id,count(*) as rec_invest_total from lzh_vc_recom where parent_id<>0 and invest_money<>0 group by parent_id) re_invest on m.id=re_invest.parent_id")
                              ->field($field)
                              ->where($where1)
                              ->limit($limit)
                              ->select();

        if ($ispage == 1) {
            $header = array('邀请人id','邀请人手机号码','邀请人真实姓名','邀请人注册时间','累计邀请人数','累计返利金额','投资人数');
            $data = $list;
            foreach ($data as $key => $v) {
              unset($data[$key]['reg_time']);
            }
            
            exportToCSV($header,$data,"laoyonghutuijian.csv");
            die;
        }


        $this->assign('list', $list);
         // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "recommend");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();  
    }

    /**
     * 老用户礼品兑换
     * @return [type] [description]
     */
    public function gift()
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
            $where .= " AND m.dream_invest_total <= ".$_REQUEST['invest_max']."  ";
        }

        if (!empty($_REQUEST['invest_min'])) {
            $search['invest_min'] = $_REQUEST['invest_min'];
            $where .= " AND m.dream_invest_total >= ".$_REQUEST['invest_min']." ";
        }


        //实名人数
        $field = "m.id,m.user_phone,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d'),mi2.cell_phone ,m.dream_invest_total,m.reg_time";
        $where1 = " m.dream_invest_total<>0 ";
        $where1.= $where;

        $count = M('members m')->join('lzh_member_info mi on m.id = mi.uid')
                                        ->join("lzh_member_info mi2 on m.recommend_id = mi2.uid")
                                        ->where($where1)
                                        ->field($field)
                                        ->order("m.id desc")
                                        ->count();

        //分页
        if ($_REQUEST['execl'] == "execl") {
            $ispage = 1;
            $limit = "0,1000000";
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }  

        $list = M('members m')->join('lzh_member_info mi on m.id = mi.uid')
                                        ->join("lzh_member_info mi2 on m.recommend_id = mi2.uid")
                                        ->where($where1)
                                        ->field($field)
                                        ->order("m.id desc")
                                        ->limit($limit)
                                        ->select();

        if ($ispage == 1) {
            $header = array('用户id','手机号码','真实姓名','注册时间','推荐人号码','活动期间累计投资额');
            $data = $list;
            foreach ($data as $key => $v) {
              unset($data[$key]['reg_time']);
            }
            
            exportToCSV($header,$data,"xinlaoyonghuduihuan.csv");
            die;
        }

        $this->assign('list', $list);
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "gift");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();  
    }

}