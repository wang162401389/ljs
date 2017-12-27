<?php
// 全局设置
class Huodong201711Action extends ACommonAction
{
    
    public function index()
    {
    	$where = '1=1 ';
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND i1.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];

        }
        
        //手机号
        if ($_REQUEST['parent_cell_phone']) {
            $where .= " AND i2.cell_phone=".$_REQUEST['parent_cell_phone'];
            $search['parent_cell_phone'] = $_REQUEST['parent_cell_phone'];
        }
        
        
        $field = "i2.cell_phone as parent_cell_phone,i2.real_name as parent_real_name,i1.cell_phone,c.create_time,i1.real_name,d.create_time as invest_time,d.invest,d.rebate";
        
        $list = M('huodong_201711_count c')
                ->join('lzh_huodong_201711_detail d on c.id = d.count_id')
                ->join('lzh_member_info i1 on i1.uid = c.uid')
                ->join('lzh_member_info i2 on i2.uid = c.parent_id')
                ->where($where)
                ->field($field)
                ->order("c.id desc")
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

        $list = M('huodong_201711_count c')
            ->join('lzh_huodong_201711_detail d on c.id = d.count_id')
            ->join('lzh_member_info i1 on i1.uid = c.uid')
            ->join('lzh_member_info i2 on i2.uid = c.parent_id')
            ->where($where)
            ->limit($limit)
            ->field($field)
            ->order("c.id desc")
            ->select();
        
        if ($ispage == 1) {
            $header = array('邀请人手机号','邀请人姓名','被邀请人手机号','注册时间','被邀请人姓名','投资时间','投资金额',"返利金額");

            foreach ($list as $key => $value) {
                $list[$key]['create_time'] = date("Y-m-d H:i:s",$list[$key]['create_time']);
                $list[$key]['invest_time'] = date("Y-m-d H:i:s",$list[$key]['invest_time']);
            }
            exportToCSV($header, $list, "letusinvest.csv");
            die;
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "index");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        //exit("search=".json_encode($search)."  query ".json_encode(http_build_query($search)));
        $this->display();
    }
	

	public function firstInvest()
	{
		$where = '1=1 ';
        //推荐人手机号
        if ($_REQUEST['cell_phone']) {
            $where .= " AND i1.cell_phone=".$_REQUEST['cell_phone'];
            $search['cell_phone'] = $_REQUEST['cell_phone'];
        }
        
        //手机号
        if ($_REQUEST['parent_cell_phone']) {
            $where .= " AND i2.cell_phone=".$_REQUEST['parent_cell_phone'];
            $search['parent_cell_phone'] = $_REQUEST['parent_cell_phone'];
        }
        
        
        $field = "i2.cell_phone as parent_cell_phone,i2.real_name as parent_real_name,i1.cell_phone,c.create_time,i1.real_name,d.create_time as invest_time,c.first_invest,'reb'";
        
        $list = M('huodong_201711_count c')
                ->join('lzh_huodong_201711_detail d on c.id = d.count_id')
                ->join('lzh_member_info i1 on i1.uid = c.uid')
                ->join('lzh_member_info i2 on i2.uid = c.parent_id')
                ->where($where)
                ->field($field)
                ->group("c.uid")
                ->order("c.id desc")
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

        $list = M('huodong_201711_count c')
                ->join('lzh_huodong_201711_detail d on c.id = d.count_id')
                ->join('lzh_member_info i1 on i1.uid = c.uid')
                ->join('lzh_member_info i2 on i2.uid = c.parent_id')
                ->where($where)
                ->group("c.uid")
                ->field($field)
                ->limit($limit)
                ->order("c.id desc")
                ->select();
        
        if ($ispage == 1) {
            $header = array('邀请人手机号','邀请人姓名','被邀请人手机号','注册时间','被邀请人姓名','投资时间','投资金额',"返利金额");


            foreach ($list as $key => $value) {
                $list[$key]['create_time'] = date("Y-m-d H:i:s",$list[$key]['create_time']);
                $list[$key]['invest_time'] = date("Y-m-d H:i:s",$list[$key]['invest_time']);
                $investMoney = $list[$key]['first_invest'];
                if(5000 <= $investMoney && $investMoney < 10000)
                {
                    $list[$key]['reb'] = 30;
                } else if (10000 <= $investMoney && $investMoney < 20000) {
                    $list[$key]['reb'] = 60;
                } else if (20000 <= $investMoney && $investMoney < 50000) {
                    $list[$key]['reb'] = 200;
                } else if (50000 <= $investMoney && $investMoney < 100000) {
                    $list[$key]['reb'] = 500;
                } else if (100000 <= $investMoney) {
                    $list[$key]['reb'] = 1200;
                } else {
                    $list[$key]['reb'] = 0;
                }

            }
            exportToCSV($header, $list, "firstinvest.csv");
            die;
        }
        
        // 记录查询条件
        $this->assign("search", $search);
        // 分页
        $this->assign('pagebar', $page);
        // 数据
        $this->assign('list', $list);
        // 方法
        $this->assign("xaction", "firstinvest");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
	}
	
}
?>