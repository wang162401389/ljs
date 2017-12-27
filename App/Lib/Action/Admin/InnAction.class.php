<?php
/**
 * 9、10月内部投资返利
 * 
 */
class InnAction extends ACommonAction
{
    //9月活动开启时间（含当日）
    const sep_start_time = '2017-09-05';
    //9月活动结束时间（不含当日）
    const sep_end_time = '2017-10-01';
    //10月活动开启时间（含当日）
    const oct_start_time = '2017-10-01';
    //10月活动结束时间（不含当日）
    const oct_end_time = '2017-11-01';
    
    /**
     * 自身
     */
    public function self()
    {
        $start_time = $_GET['month'] == 1 ? self::sep_start_time : self::oct_start_time;
        $end_time = $_GET['month'] == 1 ? self::sep_end_time : self::oct_end_time;
        
        $where['m.id'] = ['in', C('CCFAX_USER')];
        $where['bi.add_time'] = ['between', [strtotime($start_time), strtotime($end_time)]];
        
        //id
        if ($_REQUEST['id']) {
            $where['m.id'] = $_REQUEST['id'];
            $search['id'] = $_REQUEST['id'];
        }
        //手机号
        if ($_REQUEST['user_phone']) {
            $where['m.user_phone'] = $_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        $field = "m.id,m.user_phone,mi.real_name,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d %H:%i:%s') as reg_date,sum(bi.`investor_capital`) as total_invest";
        
        $list = M('members m')
                ->join('lzh_member_info mi on m.id = mi.uid')
                ->join('lzh_borrow_investor bi on m.id = bi.investor_uid')
                ->field($field)
                ->where($where)
                ->group('m.id')
                ->select();
                
        if (!empty($list)) {
            if ($_REQUEST['execl'] == "execl") {
                $ispage = 1;
                $limit = "0,1000000";
            } else {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
                $limit = "{$p->firstRow},{$p->listRows}";
                // 分页
                $this->assign('pagebar', $page);
            }
            
            $list = M('members m')
                    ->join('lzh_member_info mi on m.id = mi.uid')
                    ->join('lzh_borrow_investor bi on m.id = bi.investor_uid')
                    ->where($where)
                    ->field($field)
                    ->limit($limit)
                    ->order("total_invest desc")
                    ->group('m.id')
                    ->select();
            
            foreach ($list as &$v)
            {
                $v['return'] = $v['total_invest'] * 0.02;
            }
            
            if ($ispage == 1) {
                $header = array('ID','手机号码','真实姓名','注册时间','活动期间累计投资额','活动自身累计返利');
                exportToCSV($header, $list, "self.csv");
                die;
            }
            
            // 数据
            $this->assign('list', $list);
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 方法
        $this->assign("xaction", "self");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 推荐
     */
    public function recommend()
    {
        $start_time = $_GET['month'] == 1 ? self::sep_start_time : self::oct_start_time;
        $end_time = $_GET['month'] == 1 ? self::sep_end_time : self::oct_end_time;
        
        $where['mi.uid'] = ['in', C('CCFAX_USER')];
        $where['bi.add_time'] = ['between', [strtotime($start_time), strtotime($end_time)]];
        
        //id
        if ($_REQUEST['id']) {
            $where['mi.uid'] = $_REQUEST['id'];
            $search['id'] = $_REQUEST['id'];
        }
        //手机号
        if ($_REQUEST['user_phone']) {
            $where['mi.cell_phone'] = $_REQUEST['user_phone'];
            $search['user_phone'] = $_REQUEST['user_phone'];
        }
        
        $field = "mi.uid,mi.cell_phone,mi.real_name,m.id,date_format(from_unixtime(m.`reg_time`),'%Y-%m-%d %H:%i:%s') as reg_date,
                date_format(from_unixtime(bi.`add_time`),'%Y-%m-%d %H:%i:%s') as invest_date,bi.investor_capital";
        
        $list = M('borrow_investor bi')
                ->join('lzh_members m on bi.investor_uid = m.id')
                ->join('lzh_member_info mi on m.recommend_id = mi.uid')
                ->where($where)
                ->select();
        
        if (!empty($list)) {
            if ($_REQUEST['execl'] == "execl") {
                $ispage = 1;
                $limit = "0,1000000";
            } else {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
                $limit = "{$p->firstRow},{$p->listRows}";
                // 分页
                $this->assign('pagebar', $page);
            }
            
            $list = M('borrow_investor bi')
                    ->join('lzh_members m on bi.investor_uid = m.id')
                    ->join('lzh_member_info mi on m.recommend_id = mi.uid')
                    ->where($where)
                    ->field($field)
                    ->limit($limit)
                    ->order("invest_date desc")
                    ->select();

            foreach ($list as &$v)
            {
                $is_inner_staff = partake_filter($v['id']);
                $v['is_inner_staff'] = $is_inner_staff ? '是' : '否';
                $v['return'] = $is_inner_staff ? 0 : $v['investor_capital'] * 0.02;
            }
            
            if ($ispage == 1) {
                $header = array('内部在职员工ID','内部在职员工电话号码','真实姓名','推荐的用户ID','推荐的用户注册时间','推荐的用户投资时间','推荐的用户单笔投资金额','推荐的用户是否为内部员工','内部在职员工返利');
                exportToCSV($header, $list, "recommend.csv");
                die;
            }
            
            // 数据
            $this->assign('list', $list);
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 方法
        $this->assign("xaction", "recommend");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
}