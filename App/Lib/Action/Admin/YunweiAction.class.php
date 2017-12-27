<?php
// 全局设置
class YunweiAction extends ACommonAction
{
    /*******************************
     * 常规活动
     */
    public function common1()
    {
        //取出从起始时间到现在已经复审的标的投资者
      $start_date="2016-02-25 00:00:00";
        $start_time=strtotime($start_date);
        $cur_time=time();
        if (!empty($_REQUEST['tuname'])) {
            $search['m.user_name'] =array('like',"%".$_REQUEST['tuname']."%");
            $map['tuname']=$_REQUEST['tuname'];
        }
        if (!empty($_REQUEST['uname'])) {
            $search['mm.user_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        if (!empty($_REQUEST['tuid'])) {
            $search['m.id'] =intval($_REQUEST['tuid']);
            $map['tuid']=intval($_REQUEST['tuid']);
        }
        if (!empty($_REQUEST['uid'])) {
            $search['mm.id'] =intval($_REQUEST['uid']);
            $map['uid']=intval($_REQUEST['uid']);
        }
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $search['b.second_verify_time'] = array("between","{$start_time},{$end_time}");
            $map['start_time'] = $_REQUEST['start_time'];
            $map['end_time'] = $_REQUEST['end_time'];
            $xtime['start_time'] = $_REQUEST['start_time'];
            $xtime['end_time'] = $_REQUEST['end_time'];
        } else {
            $search['b.second_verify_time']=array("between",array($start_time,$cur_time));
        }
        $search['m.recommend_id']=array("neq",0);

        $field="i.investor_uid,i.investor_capital,b.borrow_name,b.id as borrow_id,b.borrow_duration,b.product_type,b.second_verify_time,m.recommend_id,mm.user_name as tuijian,m.user_name,mm.user_phone,mm.id as tuijian_id,b.borrow_duration_txt";
        $result=M("borrow_investor i")->join("lzh_borrow_info b on i.borrow_id=b.id")->join("lzh_members m on m.id=i.investor_uid")->join("lzh_members mm on m.recommend_id=mm.id")->field($field)->where($search)->order('mm.id,b.second_verify_time desc')->select();
        $info=array();
        $money=0;
        $return_money=0;
        foreach ($result as $key=>$val) {
            if (partake_filter($val['recommend_id'])) {
                $val['borrow_duration']=get_day($val['borrow_duration_txt']);
                $money+=intval($val['investor_capital']);
                $val['second_verify_time']=date("Y-m-d", $val['second_verify_time']);
                $val['return_money']=getFloatValue($val['investor_capital']*$val['borrow_duration']*0.012/360, 2);
                $info[]=$val;
                $return_money+=$val['return_money'];
            }
        }
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count, $map, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
       //过滤
       $info1=array();
        $sum=0;
        $i=0;
        if ($_REQUEST['execl']=="execl") {
            $limit =1;
        } else {
            $limit =0;
        }
        foreach ($info as $key=>$val) {
            if ((($i>=$min)&&($i<$max))||($limit==1)) {
                $info1[$key]=$val;
            }
            $i++;
        }
        foreach ($info1 as $k => $v) {
            $info1[$k]['borrow_id']=borrowidlayout1($v['borrow_id']);
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("common1", 0, 1, '执行了内部返利活动信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('投资人id','投资人','投资标号','标号名称','标复审时间','标期限','投资金额','推荐人','推荐人id','推荐人手机号码','获取返利');
            $i=1;
            foreach ($info1 as $key=>$v) {
                $row[$i]['investor_uid'] = $v['investor_uid'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['bid'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration'] = $v['borrow_duration'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['tuijian'] = $v['tuijian'];
                $row[$i]['tuijian_id'] = $v['tuijian_id'];
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'common1');
            $xls->addArray($row);
            $xls->generateXML("common1".date("YmdHis", time()));
            exit;
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction', "common1");
        $this->assign("info", $info1);
        $this->assign("money", $money);
        $map['execl']="execl";
        $this->assign("query", http_build_query($map));
        $this->assign("return_money", $return_money);
        $this->display();
    }

    //3●8妇女节活动
    public function activity38()
    {
        $Model = new Model();
        $info = $Model->query("SELECT SUM(h.money) AS totalmoney,m.user_name,h.uid,m.user_phone,m.reg_time,mi.real_name FROM lzh_huodong h INNER JOIN lzh_members m ON m.id = h.uid INNER JOIN lzh_member_info mi ON h.uid = mi.uid WHERE h.add_time BETWEEN 1457280000 AND 1457539199 GROUP BY h.uid ORDER BY totalmoney DESC");

        $list = array();
        $j=0;
        foreach ($info as $f) {
            if ($f["totalmoney"]>=20000) {
                $list[$j] = $f;
                $j++;
            }
        }

        $k = 0;
        foreach ($list as $i) {
            $list[$k]["reg_time"] = date("Y-m-d H:i:s", $i["reg_time"]);
            if ($i["totalmoney"] >= 20000 && $i["totalmoney"] <= 50000) {
                $list[$k]["gift"] = "50元京东E卡";
            } elseif ($i["totalmoney"] >50000 && $i["totalmoney"] <= 80000) {
                $list[$k]["gift"] = "100元京东E卡";
            } elseif ($i["totalmoney"] >80000 && $i["totalmoney"] <= 150000) {
                $list[$k]["gift"] = "200元京东E卡";
            } elseif ($i["totalmoney"] >150000) {
                $list[$k]["gift"] = "500元京东E卡";
            }
            $k++;
        }
        $this->assign("list", $list);
        $this->display();
    }
    
    public function friend()
    {
        $filter = C("CCFAX_USER");
        $search = $map = [];
        if (!empty($_REQUEST['uid'])) {
            $map['mm.id'] = intval($_REQUEST['uid']);
            $search['uid']= intval($_REQUEST['uid']);
        }

        if (!empty($_REQUEST['mobile'])) {
            $map['mm.user_phone'] = intval($_REQUEST['mobile']);
            $search['mobile'] = intval($_REQUEST['mobile']);
        }

        if (!empty($_REQUEST['real_name'])) {
            $map['mi.real_name'] = ['like', '%'.$_REQUEST['real_name'].'%'];
            $search['real_name'] = intval($_REQUEST['real_name']);
        }

        if (!empty($_REQUEST['status'])) {
            if ($_REQUEST['status'] == 1) {
                $map['mm.id'] = ['in', $filter];
                $search['status'] = 1;
            } else {
                $map['mm.id'] = ['not in', $filter];
                $search['status'] = 2;
            }
        }
        
        import("@.conf.friend_invest");
        import("ORG.Util.PageFilter");
        
        $friend = new friend_invest();
        $info = $friend->get_friend_list($map);

        $p = new PageFilter(count($info), $search, C('ADMIN_PAGE_SIZE'));
        $min = $p->firstRow;
        $max = $p->listRows + $min;
        $info1 = [];
        $limit = 0;
        if ($_REQUEST['execl'] == "execl") {
            $limit = 1;
        }
        $i = 0;
        foreach ($info as $key => $val) {
            if (($i >= $min && $i < $max) || $limit == 1) {
                $info1[$key] = $val;
            }
            $i++;
        }
        if ($_REQUEST['execl'] == "execl") {
            import("ORG.Io.Excel");
            alogs("friend", 0, 1, '执行了所有邀请人列表！');//管理员操作日志
            $row[0] = ['会员ID','手机号码','会员名称','真实姓名','是否为内部员工','注册人数','实名人数','投资人数','推荐人投资','本人投资','共投资'];
            $i = 1;
            foreach ($info1 as $key => $v) {
                $row[$i]['id'] = $key;
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['real_name'] = $v['real_name'];
                $row[$i]['staff'] = $v['staff_type'] == 1 ? "是" : "否";
                $row[$i]['register_num'] = $v['register_num'];
                $row[$i]['real_num'] = $v['real_num'];
                $row[$i]['investor_num'] = $v['investor_num'];
                $row[$i]['friend_investor'] = $v['friend_investor'];
                $row[$i]['mine_capital'] = $v['mine_capital'];
                $row[$i]['total'] = getFloatValue(($v['mine_capital'] + $v['friend_investor']), 2);
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'friend');
            $xls->addArray($row);
            $xls->generateXML("friend".date("YmdHis"));
            exit;
        }
        $this->assign("pagebar", $p->show());
        $this->assign("info", $info1);
        $this->assign("xaction", "friend");
        $search['execl'] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    public function friend_invest()
    {
        $map=array();
        $search=array();
        if (!empty($_REQUEST['start_time'])) {
            $search['start_time']=$_REQUEST['start_time'];
            $search['end_time']=$_REQUEST['end_time'];
            $map['i.add_time']=array("between",array(strtotime($search['start_time']),strtotime($search['end_time'])));
        }
        if (!empty($_REQUEST['id'])) {
            $search['id']=$_REQUEST['id'];
            $id=intval($_REQUEST['id']);
        }
        if (!empty($_REQUEST['uid'])) {
            $search['id']=$_REQUEST['uid'];
            $map['m.id']=intval($_REQUEST['uid']);
        }

        import("@.conf.friend_invest");
        $friend=new friend_invest();
        $info=$friend->get_friend_invest($id, $map);
        $count=count($info);
        import("ORG.Util.PageFilter");
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        $info1=array();
        $sum=0;
        $i=0;
        if ($_REQUEST['execl']=="execl") {
            $limit =1;
        } else {
            $limit =0;
        }
        foreach ($info as $key=>$val) {
            if ((($i>=$min)&&($i<$max))||($limit==1)) {
                $val['reg_time']=date("Y-m-d H:i:s", $val['reg_time']);
                $val['add_time']=date("Y-m-d H:i:s", $val['add_time']);
                if ($val['repayment_type']==1) {
                    $val['borrow_duration']=$val['borrow_duration']."天";
                } else {
                    $val['borrow_duration']=$val['borrow_duration']."月";
                }
                $info1[$key]=$val;
            }
            $i++;
            $sum=getFloatValue(($val['investor_capital']+$sum), 2);
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("friend", 0, 1, '执行了所有邀请人列表详情！');//管理员操作日志
            $row=array();
            $row[0]=array('用户ID','用户名称','注册时间','投资标号','投资时间','投资期限','投资金额');
            $i=1;
            foreach ($info1 as $v) {
                $row[$i]['id'] = $v['id'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['reg_time'] = $v['reg_time'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['add_time'] = $v['add_time'];
                $row[$i]['borrow_duration'] = $v['borrow_duration'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $i++;
            }

            $xls = new Excel_XML('UTF-8', false, 'friend_invest');
            $xls->addArray($row);
            $xls->generateXML("friend_invest".date("YmdHis", time()));
            exit;
        }

        $this->assign("sum", $sum);
        $this->assign("pagebar", $page);
        $this->assign("info", $info1);
        $this->assign("xaction", "friend_invest");
        $this->assign("id", $id);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }


    public function registerlist()
    {
        $map['m.recommend_id']=$search['id']=intval($_REQUEST['id']);
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $map['m.reg_time'] = array("between","{$start_time},{$end_time}");
            $search['start_time'] = $_REQUEST['start_time'];
            $search['end_time'] = $_REQUEST['end_time'];
        }
        import("ORG.Util.PageFilter");
        $count = M('members m')->where($map)->count('m.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }
        $list = M('members m')->field('m.*,mm.user_name as recommendname')->join('lzh_members mm on m.recommend_id=mm.id')->where($map)->limit($limit)->order('m.reg_time desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['reg_time'] = date("Y-m-d", $v['reg_time']);
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("registerlist", 0, 1, '推荐注册信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','推荐注册时间','推荐人','推荐人ID');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['id'] = $v['id'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['reg_time'] = $v['reg_time'];
                $row[$i]['recommendname'] = $v['recommendname'];
                $row[$i]['recommend_id'] = $v['recommend_id'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'registerlist');
            $xls->addArray($row);
            $xls->generateXML("registerlist".date("YmdHis", time()));
            exit;
        }
        $this->assign('id', intval($_REQUEST['id']));
        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign("xaction", "registerlist");
        $this->assign('list', $list);
        $this->display();
    }


    public function reallist()
    {
        $map['m.recommend_id']=$search['id']=intval($_REQUEST['id']);
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $map['mi.up_time'] = array("between","{$start_time},{$end_time}");
            $search['start_time'] = $_REQUEST['start_time'];
            $search['end_time'] = $_REQUEST['end_time'];
        }
        import("ORG.Util.PageFilter");
        $count = M('members m')->join("lzh_member_info as mi on mi.uid=m.id")->where($map)->count('m.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }
        $list1=M('members m')->field('m.*,mi.real_name,mi.up_time,mm.user_name as recommendname')->join("lzh_member_info as mi on mi.uid=m.id")->join("lzh_members mm on m.recommend_id=mm.id")->where($map)->limit($limit)->order('mi.up_time desc')->select();
        $list=array();
        foreach ($list1 as $k=>$v) {
            if (!empty($v['real_name'])) {
                $list[$k]=$v;
                $list[$k]['up_time'] = date('Y-m-d', $v['up_time']);
            }
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("reallist", 0, 1, '推荐实名信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','实名时间','推荐人','推荐人ID');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['id'] = $v['id'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['up_time'] = $v['up_time'];
                $row[$i]['recommendname'] = $v['recommendname'];
                $row[$i]['recommend_id'] = $v['recommend_id'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'reallist');
            $xls->addArray($row);
            $xls->generateXML("reallist".date("YmdHis", time()));
            exit;
        }
        $this->assign('id', intval($_REQUEST['id']));
        $this->assign("pagebar", $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign("xaction", reallist);
        $this->assign('list', $list);
        $this->display();
    }

    public function recommendinvest()
    {
        $map['m.recommend_id']=$search['id']=intval($_REQUEST['id']);
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $map['b.second_verify_time'] = array("between","{$start_time},{$end_time}");
            $search['start_time'] = $_REQUEST['start_time'];
            $search['end_time'] = $_REQUEST['end_time'];
        }
        $field="i.investor_uid,i.investor_capital,b.borrow_name,b.id as borrow_id,b.borrow_duration,b.borrow_duration_txt,b.second_verify_time,mm.user_name as tuijian,m.user_name,mm.user_phone,mm.id as tuijian_id";
        $result=M("borrow_investor i")->join("lzh_borrow_info b on i.borrow_id=b.id")->join("lzh_members m on m.id=i.investor_uid")->join("lzh_members mm on m.recommend_id=mm.id")->field($field)->where($map)->order('mm.id,b.second_verify_time desc')->select();
        $info=array();
        $money=0;
        foreach ($result as $key=>$val) {
            $val['second_verify_time']=date("Y-m-d", $val['second_verify_time']);
            $info[]=$val;
        }
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
       //过滤
       $info1=array();
        $sum=0;
        $i=0;
        if ($_REQUEST['execl']=="execl") {
            $limit =1;
        } else {
            $limit =0;
        }
        foreach ($info as $key=>$val) {
            if ((($i>=$min)&&($i<$max))||($limit==1)) {
                $info1[$key]=$val;
            }
            $i++;
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("recommendinvest", 0, 1, '推荐人投资金额信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','标号名称','标复审时间','标期限','投资金额','推荐人','推荐人id');
            $i=1;
            foreach ($info1 as $key=>$v) {
                $row[$i]['investor_uid'] = $v['investor_uid'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['tuijian'] = $v['tuijian'];
                $row[$i]['tuijian_id'] = $v['tuijian_id'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'recommendinvest');
            $xls->addArray($row);
            $xls->generateXML("recommendinvest".date("YmdHis", time()));
            exit;
        }
        $this->assign('id', intval($_REQUEST['id']));
        $this->assign("pagebar", $page);
        $this->assign('xaction', "recommendinvest");
        $this->assign("info", $info1);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    public function selfinvest()
    {
        $map['i.investor_uid']=$search['id']=intval($_REQUEST['id']);
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $map['b.second_verify_time'] = array("between","{$start_time},{$end_time}");
            $search['start_time'] = $_REQUEST['start_time'];
            $search['end_time'] = $_REQUEST['end_time'];
        }
        import("ORG.Util.PageFilter");
        $count = M("borrow_investor i")->join("lzh_members m on m.id=i.investor_uid")->join("lzh_borrow_info b on b.id=i.borrow_id")->where($map)->count('i.investor_uid');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }
        $list = M("borrow_investor i")->field("i.*,m.user_name,b.borrow_name,b.second_verify_time,b.borrow_duration_txt")->join("lzh_members m on m.id=i.investor_uid")->join("lzh_borrow_info b on b.id=i.borrow_id")->where($map)->limit($limit)->order('b.second_verify_time desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['second_verify_time'] = date("Y-m-d", $v['second_verify_time']);
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("selfinvest", 0, 1, '推荐注册信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','标号名称','标复审时间','标期限','投资金额');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['investor_uid'] = $v['investor_uid'];
                $row[$i]['user_name'] = $v['user_name'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'selfinvest');
            $xls->addArray($row);
            $xls->generateXML("selfinvest".date("YmdHis", time()));
            exit;
        }
        $this->assign('id', intval($_REQUEST['id']));
        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', "selfinvest");
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 数据统计
     */
    public function statistics()
    {
        $where=' 1=1';
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $where="m.reg_time between {$start_time} and {$end_time}";
        }

        $sql="SELECT count(DISTINCT m.id) as zhuce,
                    sum(case when m.equipment='PC'      then 1 else 0 end) as pc ,
                    sum(case when m.equipment='APP'     then 1 else 0 end) as app,
                    sum(case when m.equipment='WeChat'  then 1 else 0 end) as wechat,
                    sum(case when mi.id_status=1 or mi.company_status=3 then 1 else 0 end ) as real_name FROM lzh_members m
                    LEFT JOIN lzh_members_status mi on m.id=mi.uid
                    LEFT JOIN lzh_coupons c on c.user_phone=m.user_phone
                    WHERE ".$where;
        $sql1="select count(DISTINCT m.id)as  touzi, sum(i.investor_capital) as investors from  lzh_members  m inner JOIN lzh_borrow_investor i on i.investor_uid=m.id  WHERE ".$where;
        $list1 = M('members m')->query($sql1);
        $list = M('members m')->query($sql);
        $this->assign('regnum', $list[0]["zhuce"]);//注册人数
        $this->assign('realnum', $list[0]["real_name"]?$list[0]["real_name"]:0);//实名人数
        $this->assign('pcnum', $list[0]["pc"]?$list[0]["pc"]:0);
        $this->assign('appnum', $list[0]['app']?$list[0]['app']:0);
        $this->assign('wechatnum', $list[0]["wechat"]?$list[0]["wechat"]:0);
        $this->assign('investnum', $list1[0]["touzi"]?$list1[0]["touzi"]:0);
        $this->assign('investmoney', getFloatValue($list1[0]["investors"], 2));
        $this->assign('xaction', "statistics");

        $where = '1=1';
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
            $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time']." 00:00:00" : '1970-01-01 00:00:00';
            $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time']." 23:59:59" : date('Y-m-d H:i:s');
            $where = C('DB_PREFIX')."coupons.addtime > '{$start_time}' and ".C('DB_PREFIX')."coupons.addtime < '{$end_time}'";
        }
        $count = M('members')->join('LEFT JOIN __COUPONS__ ON __MEMBERS__.user_phone = __COUPONS__.user_phone')
        ->where(C('DB_PREFIX').'coupons.type=2 and '.C('DB_PREFIX').'coupons.status=1 and '.$where)->count();
        $this->assign('experienced_count', $count);

        $this->display();
    }

    public function olympicactivity()
    {
        if (!empty($_REQUEST['userphone'])) {
            $map['o.user_phone'] = $_REQUEST['userphone'];
            $search['userphone'] = $_REQUEST['userphone'];
        }
        if (!empty($_REQUEST['goldnum'])) {
            $map['o.gold_num'] = $_REQUEST['goldnum'];
            $search['goldnum'] = $_REQUEST['goldnum'];
        }
        if (!empty($_REQUEST['start_time'])&&!empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time = strtotime($_REQUEST['end_time']." 23:59:59");
            $map['o.updatetime'] = array("between","{$start_time},{$end_time}");
            $search['start_time'] = $_REQUEST['start_time'];
            $search['end_time'] = $_REQUEST['end_time'];
        }

        import("ORG.Util.PageFilter");
        $count = M('olympic_log o')->join('lzh_members m on o.user_phone = m.user_phone')->where($map)->count('o.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }
        $info = M('olympic_log o')->field('o.user_phone,o.gold_num,o.updatetime,m.id')->join('lzh_members m on o.user_phone = m.user_phone')->where($map)->limit($limit)->select();
        $i=1;
        foreach ($info as $k => $v) {
            $list[$i]['user_phone'] = $v['user_phone'];
            $list[$i]['gold_num'] = $v['gold_num'];
            $list[$i]['updatetime'] = date("Y-m-d", $v['updatetime']);
            $list[$i]['id'] = $v['id'];
            $i++;
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("olympicactivity", 0, 1, '奥运活动信息列表！');//管理员操作日志
            $row=array();
            $row[0]=array('序号','手机号','竞猜金牌数','提交日期','用户ID号');
            $i=1;
            foreach ($info as $key=>$v) {
                $row[$i]['num'] = $i;
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['gold_num'] = $v['gold_num'];
                $row[$i]['updatetime'] = date("Y-m-d", $v['updatetime']);
                $row[$i]['id'] = $v['id'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'olympicactivity');
            $xls->addArray($row);
            $xls->generateXML("olympicactivity".date("YmdHis", time()));
            exit;
        }
        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', "olympicactivity");
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 投资用户数统计
     */
    public function investcount1()
    {
        $map1=$map2=$par='1=1';
        $search=[];
        $type=0;// 0 没有按照时间搜索 1 提现  2投资   3都有
        $ispage=1; //是否翻页  1翻页，用在列表  2不翻页用在导出
        if ($_REQUEST["username"]) {
            $par.=" and user_name='".$_REQUEST["username"]."'";
            $search["username"]=$_REQUEST["username"];
        }
        if (!empty($_REQUEST['start_time1'])&&!empty($_REQUEST['end_time1'])) {
            $start_time1 = strtotime($_REQUEST['start_time1']." 00:00:00");
            $end_time1 = strtotime($_REQUEST['end_time1']." 23:59:59");
            $search['start_time1'] = $_REQUEST['start_time1'];
            $search['end_time1'] = $_REQUEST['end_time1'];
            $map1.=" and add_time between {$start_time1} and {$end_time1}";
            $type=1;
        }
        if (!empty($_REQUEST['start_time2'])&&!empty($_REQUEST['end_time2'])) {
            $start_time2 = strtotime($_REQUEST['start_time2']." 00:00:00");
            $end_time2= strtotime($_REQUEST['end_time2']." 23:59:59");
            $search['start_time2'] = $_REQUEST['start_time2'];
            $search['end_time2'] = $_REQUEST['end_time2'];
            $map2.=" and add_time between {$start_time2} and {$end_time2} ";
            if ($type==1) {
                $type=3;
            } else {
                $type=2;
            }
        }
        if ($_REQUEST['is_vip']=='yes') {
            $par.=" and is_vip=1";
            $search['is_vip'] = 'yes';
        } elseif ($_REQUEST['is_vip']=='no') {
            $par.=" and is_vip=0";
            $search['is_vip'] = 'no';
        } elseif ($_REQUEST['is_vip']=='all') {
            $par.=" and is_vip>=0";
            $search['is_vip'] = 'all';
        }
        if (!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $start_time = strtotime($_REQUEST['start_time']." 00:00:00");
            $end_time= strtotime($_REQUEST['end_time']." 23:59:59");
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
            $par.=" and reg_time between {$start_time} and {$end_time} ";
        }

        import("ORG.Util.PageFilter");
        $mywhere=" ";
        if ($type==1) {
            $mywhere.="  and tixian>0 ";
        } elseif ($type==2) {
            $mywhere.="  and touzicount>0 ";
        } else {
            $mywhere.=" and (tixian>0 or touzicount>0)  " ;
        }
        $countsql="select a.id,tixian, touzicount from lzh_members a
         left join (SELECT uid,COUNT(*) as tixian,SUM(withdraw_money) as tixianmoney FROM  `lzh_member_withdraw`   WHERE $map1   GROUP BY uid ) w   on  w.uid=a.id
         left join (select investor_uid, count(*) as touzicount,sum(investor_capital) as totalmoney from lzh_borrow_investor g where $map2 GROUP BY investor_uid) h on h.investor_uid=a.id where $par
         $mywhere";
        $countlist= M("members")->query($countsql);

        $sql="select a.id,user_name,reg_time,is_vip, tixian,tixianmoney, touzicount,totalmoney from lzh_members a
         left join (SELECT uid,COUNT(*) as tixian,SUM(withdraw_money) as tixianmoney FROM  `lzh_member_withdraw`   WHERE $map1   GROUP BY uid ) w   on  w.uid=a.id
         left join (select investor_uid, count(*) as touzicount,sum(investor_capital) as totalmoney from lzh_borrow_investor g where $map2 GROUP BY investor_uid) h on h.investor_uid=a.id where $par
         $mywhere ";

        if ($_REQUEST['execl']=="execl") {
            $ispage=0;
        } else {
            $p = new PageFilter(count($countlist), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= "limit $limit";
        }

        $list=M("members")->query($sql);
        $ad=[];
        foreach ($list as $key => $va) {
            $ad[$key]["id"]=$va["id"];
            $ad[$key]["user_name"]=$va["user_name"];
            if ($va['is_vip']==1) {
                $ad[$key]['is_vip'] = "<span style='color:red'>投资人/借款人</span>";
            } else {
                $ad[$key]['is_vip'] ="投资人";
            }
            $ad[$key]["reg_time"]=date('Y-m-d', $va["reg_time"]);
            $ad[$key]["tixian"]=$va["tixian"];
            $ad[$key]["tixianmoney"]=$va["tixianmoney"];
            $ad[$key]["touzicount"]=$va["touzicount"];
            $ad[$key]["totalmoney"]=$va["totalmoney"];
        }
        if ($ispage==0) {
            import("ORG.Io.Excel");
            $row=array();
            $row[0]=array('用户编号','用户名','身份','注册时间','投资次数','投资金额','提现次数','提现金额');
            $i=1;
            foreach ($ad as $v) {
                $row[$i]['uid'] = $v['id'];
                $row[$i]['uname'] = $v['user_name'];
                if ($v['is_vip']==1) {
                    $row[$i]['is_vip'] = "投资人/借款人";
                } else {
                    $row[$i]['is_vip'] ="投资人";
                }
                $row[$i]["reg_time"]=$v["reg_time"];
                $row[$i]['touzicount'] = $v['touzicount'];
                $row[$i]['totalmoney'] = $v['totalmoney'];
                $row[$i]['tixian'] = $v['tixian'];
                $row[$i]['tixianmoney'] = $v['tixianmoney'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'tongjifenxi');
            $xls->addArray($row);
            $xls->generateXML("tongjifenxi");
            exit;
        }
        $this->assign("xaction", "investcount");
        $this->assign('pagebar', $page);
        $this->assign("list", $ad);
        $search["execl"]="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 投资用户数统计
     */
    public function investcount()
    {
        $map1=$map2=$par='1=1';
        $search=[];
        $type = 0;// 0 没有按照时间搜索 1 提现  2充值 3投资   3都有
        $ispage=1; //是否翻页  1翻页，用在列表  0不翻页用在导出
        //用户名
        if ($_REQUEST["username"]) {
            $par .= " and a.user_name='".$_REQUEST["username"]."'";
            $search["username"] = $_REQUEST["username"];
        }
        //身份
        if ($_REQUEST['is_vip'] == 'yes') {
            $par.=" and a.is_vip = 1";
            $search['is_vip'] = 'yes';
        } elseif ($_REQUEST['is_vip'] == 'no') {
            $par.=" and a.is_vip = 0";
            $search['is_vip'] = 'no';
        } elseif ($_REQUEST['is_vip'] == 'all') {
            $par.=" and a.is_vip >= 0";
            $search['is_vip'] = 'all';
        }

        //渠道
        if (isset($_REQUEST['equipment'])) {
            $par.=" and a.equipment = '".$_REQUEST['equipment']."'";
            $search['equipment'] = $_REQUEST['equipment'];
        }

        //推荐人姓名
        if ($_REQUEST["rec_real_name"]) {
            $par .= " and mmi.real_name like \"%".$_REQUEST["rec_real_name"]."%\"";
            $search["rec_real_name"] = $_REQUEST["rec_real_name"];
        }

        //手机号
        if ($_REQUEST["user_phone"]) {
            $par .= " and a.user_phone='".$_REQUEST["user_phone"]."'";
            $search["user_phone"] = $_REQUEST["user_phone"];
        }
        //真实姓名
        if ($_REQUEST["real_name"]) {
            $par .= " and mi.real_name='".$_REQUEST["real_name"]."'";
            $search["real_name"] = $_REQUEST["real_name"];
        }
        //注册时间
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
            $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
            $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
            $par .= " and a.reg_time between {$start_time} and {$end_time} ";
        }
        //提现时间
        if (!empty($_REQUEST['start_time1']) || !empty($_REQUEST['end_time1'])) {
            $type = 1;
            $start_time1 = !empty($_REQUEST['start_time1']) ? strtotime($_REQUEST['start_time1']." 00:00:00") : 0;
            $end_time1 = !empty($_REQUEST['end_time1']) ? strtotime($_REQUEST['end_time1']." 23:59:59") : time();
            $search['start_time1'] = $_REQUEST['start_time1'];
            $search['end_time1'] = $_REQUEST['end_time1'];
            $map1 .= " and sl.type = 2 and sl.completetime between {$start_time1} and {$end_time1}";
        }
        //充值时间
        if (!empty($_REQUEST['start_time2']) || !empty($_REQUEST['end_time2'])) {
            $start_time2 = !empty($_REQUEST['start_time2']) ? strtotime($_REQUEST['start_time2']." 00:00:00") : 0;
            $end_time2 = !empty($_REQUEST['end_time2']) ? strtotime($_REQUEST['end_time2']." 23:59:59") : time();
            $search['start_time2'] = $_REQUEST['start_time2'];
            $search['end_time2'] = $_REQUEST['end_time2'];
            $map1 .= " and sl.type = 1 and sl.completetime between {$start_time2} and {$end_time2}";
            $type = $type == 1 ? 3 : 2;
        }
        //投资时间
        if (!empty($_REQUEST['start_time3']) || !empty($_REQUEST['end_time3'])) {
            $start_time3 = !empty($_REQUEST['start_time3']) ? strtotime($_REQUEST['start_time3']." 00:00:00") : 0;
            $end_time3 = !empty($_REQUEST['end_time3']) ? strtotime($_REQUEST['end_time3']." 23:59:59") : time();
            $search['start_time3'] = $_REQUEST['start_time3'];
            $search['end_time3'] = $_REQUEST['end_time3'];
            $map2 .= " and g.add_time between {$start_time3} and {$end_time3} ";
            if ($type == 0) {
                $type = 4;
            } elseif ($type == 1) {
                $type = 5;
            } elseif ($type == 2) {
                $type = 6;
            } elseif ($type == 3) {
                $type = 7;
            }
        }

        import("ORG.Util.PageFilter");
        $mywhere=" ";
        if ($type == 0) {
        } elseif ($type == 1) {
            $mywhere .= "  and withdraw_total > 0";
        } elseif ($type == 2) {
            $mywhere .= "  and charge_total > 0";
        } elseif ($type == 3) {
            $mywhere .= " and (charge_total > 0 or withdraw_total > 0)";
        } elseif ($type == 4) {
            $mywhere .= "  and touzicount > 0";
        } elseif ($type == 5) {
            $mywhere .= " and (withdraw_total > 0 or touzicount > 0)";
        } elseif ($type == 6) {
            $mywhere .= " and (charge_total > 0 or touzicount > 0)";
        } elseif ($type == 7) {
            $mywhere .= " and (charge_total > 0 or withdraw_total > 0 or touzicount > 0)";
        }

        $sinalog_sql = "SELECT uid,
                        COUNT(CASE
                               WHEN sl.type = 1 THEN
                                'charge'
                             END) charge_total,
                        COUNT(CASE
                               WHEN sl.type = 2 THEN
                                'withdraw'
                             END) withdraw_total,
						sum(case when sl.type = 1 then money end ) as charge_money_total,
						sum(case when sl.type = 2 then money end ) as withdraw_money_total
                        FROM lzh_sinalog sl where sl.status = 2 and uid > 0 and type in ('1','2') and $map1 GROUP BY uid";

        $investor_sql = "select investor_uid, count(*) as touzicount,sum(investor_capital) as totalmoney from lzh_borrow_investor g where $map2 GROUP BY investor_uid";

        $sql="select a.id,a.user_name,a.reg_time,a.equipment,is_vip,user_phone,mi.real_name,charge_total,withdraw_total,charge_money_total,withdraw_money_total,touzicount,totalmoney,mmi.real_name as rec_real_name from lzh_members a
        left join lzh_member_info mi on a.id = mi.uid
        left join lzh_member_info mmi on a.recommend_id = mmi.uid
        left join ($sinalog_sql) sl on sl.uid = a.id
        left join ($investor_sql) h on h.investor_uid = a.id where $par $mywhere";
        $countlist = M()->query($sql);

        if ($_REQUEST['execl'] == "execl") {
            $ispage = 0;
        } else {
            $p = new PageFilter(count($countlist), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= " limit $limit";
        }
        $list = M()->query($sql);

        if ($ispage == 0) {
            import("ORG.Io.Excel");
            $row = array();
            $row[0] = array('用户编号','用户名','身份','手机号','真实姓名','注册渠道','推荐人姓名','注册时间','投资次数','投资金额','充值次数','充值金额','提现次数','提现金额');
            $i = 1;
            foreach ($list as $v) {
                $row[$i]['uid'] = $v['id'];
                $row[$i]['uname'] = $v['user_name'];
                if ($v['is_vip'] == 1) {
                    $row[$i]['is_vip'] = "投资人/借款人";
                } else {
                    $row[$i]['is_vip'] = "投资人";
                }
                $row[$i]["user_phone"] = $v["user_phone"];
                $row[$i]['real_name'] = $v['real_name'];
                $row[$i]['equipment'] = $v['equipment'];
                $row[$i]['rec_real_name'] = $v['rec_real_name'];
                $row[$i]["reg_time"] = date("Y-m-d", $v["reg_time"]);
                $row[$i]['touzicount'] = $v['touzicount'];
                $row[$i]['totalmoney'] = $v['totalmoney'];
                $row[$i]['charge_total'] = $v['charge_total'];
                $row[$i]['charge_money_total'] = $v['charge_money_total'];
                $row[$i]['withdraw_total'] = $v['withdraw_total'];
                $row[$i]['withdraw_money_total'] = $v['withdraw_money_total'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'tongjifenxi');
            $xls->addArray($row);
            $xls->generateXML("touzi");
            exit;
        }
        $this->assign("xaction", "investcount");
        $this->assign('pagebar', $page);
        $this->assign("list", $list);
        $search["execl"]="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /*
     * 投资/充值/提现 详情
     */
    public function countdetail()
    {
        $uid = $_GET['uid'];
        $type = $_GET['type'];
        $ispage = 1;//是否翻页  1翻页，用在列表  0不翻页用在导出
        $search = [];

        switch ($type) {
            case 'invest':
                $map['inv.investor_uid'] = $uid;
                $search = [];
                //$type = 0;// 0 没有按照时间搜索 1 提现  2充值 3投资   3都有
                $ispage=1; //是否翻页  1翻页，用在列表  2不翻页用在导出
                //id
                if ($_REQUEST["id"]) {
                    $map['inv.id'] = $_REQUEST["id"];
                    $search["id"] = $_REQUEST["id"];
                }
                //用户名
                if ($_REQUEST["username"]) {
                    $map['a.user_name'] = $_REQUEST["user_name"];
                    $search["username"] = $_REQUEST["username"];
                }
                //手机号
                if ($_REQUEST["user_phone"]) {
                    $map['a.user_phone'] = $_REQUEST["user_phone"];
                    $search["user_phone"] = $_REQUEST["user_phone"];
                }
                //投资时间
                if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
                    $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
                    $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
                    $search['start_time'] = urldecode($_REQUEST['start_time']);
                    $search['end_time'] = urldecode($_REQUEST['end_time']);
                    $map['inv.add_time'] = array('between', array($start_time, $end_time));
                }

                $field = "inv.id,m.user_name,m.user_phone,bi.borrow_name,inv.add_time,bi.borrow_duration,bi.repayment_type,inv.investor_capital";

                $result = M('borrow_investor inv')
                ->join('LEFT JOIN lzh_borrow_info bi on inv.borrow_id = bi.id')
                ->join('LEFT JOIN lzh_members m on inv.investor_uid = m.id')
                ->field($field)->where($map)->select();

                if ($_REQUEST['execl'] == "execl") {
                    $ispage = 0;
                } else {
                    import("ORG.Util.PageFilter");
                    $p = new PageFilter(count($result), $search, C('ADMIN_PAGE_SIZE'));
                    $page = $p->show();
                    $limit = "{$p->firstRow},{$p->listRows}";

                    $result = M('borrow_investor inv')
                    ->join('LEFT JOIN lzh_borrow_info bi on inv.borrow_id = bi.id')
                    ->join('LEFT JOIN lzh_members m on inv.investor_uid = m.id')
                    ->field($field)->where($map)->limit($limit)->select();
                }

                if ($ispage == 0) {
                    import("ORG.Io.Excel");
                    $row = array();
                    $row[0] = array('ID','用户名','手机号','借款名称','投资时间','借款期限','投资金额');
                    $i = 1;
                    foreach ($result as $v) {
                        $row[$i]['uid'] = $v['id'];
                        $row[$i]['uname'] = $v['user_name'];
                        $row[$i]["user_phone"] = $v["user_phone"];
                        $row[$i]['borrow_name'] = $v['borrow_name'];
                        $row[$i]["add_time"] = date("Y-m-d", $v["add_time"]);
                        $row[$i]["qixian"] = $v['borrow_duration'].($v['repayment_type'] == 1 ? '天' : '个月');
                        $row[$i]['investor_capital'] = $v['investor_capital'];
                        $i++;
                    }
                    $xls = new Excel_XML('UTF-8', false, 'tongjifenxi');
                    $xls->addArray($row);
                    $xls->generateXML("investdetail");
                    exit;
                }

                $this->assign("xaction", "countdetail/type/invest/uid/".$uid);
                break;

            case 'charge':
            case 'withdraw':
                $map['uid'] = $uid;
                $map['type'] = $type == 'charge' ? 1 : 2;
                $map['status'] = 2;

                $search = [];
                $ispage = 1;//是否翻页  1翻页，用在列表  2不翻页用在导出
                //投资时间
                if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
                    $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
                    $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
                    $search['start_time'] = urldecode($_REQUEST['start_time']);
                    $search['end_time'] = urldecode($_REQUEST['end_time']);
                    $map['completetime'] = array('between', array($start_time, $end_time));
                }

                $field = "uid,completetime,money";
                $model = M('sinalog');

                $result = $model->field($field)->where($map)->select();

                if ($_REQUEST['execl'] == "execl") {
                    $ispage = 0;
                } else {
                    import("ORG.Util.PageFilter");
                    $p = new PageFilter(count($result), $search, C('ADMIN_PAGE_SIZE'));
                    $page = $p->show();
                    $limit = "{$p->firstRow},{$p->listRows}";

                    $result = $model->field($field)->where($map)->limit($limit)->select();
                }

                $userinfo = M('members')->where(array('id' => $uid))->field('user_name,user_phone')->find();
                $username = $userinfo['user_name'];
                $userphone = $userinfo['user_phone'];

                if ($ispage == 0) {
                    import("ORG.Io.Excel");
                    $row = array();
                    $row[0] = array('ID','用户名','手机号',($type == 'charge' ? '充值' : '取现').'时间',($type == 'charge' ? '充值' : '取现').'金额');
                    $i = 1;
                    foreach ($result as $v) {
                        $row[$i]['uid'] = $v['uid'];
                        $row[$i]['uname'] = $username;
                        $row[$i]["user_phone"] = $userphone;
                        $row[$i]["completetime"] = date("Y-m-d", $v["completetime"]);
                        $row[$i]['money'] = $v['money'];
                        $i++;
                    }
                    $xls = new Excel_XML('UTF-8', false, 'tongjifenxi');
                    $xls->addArray($row);
                    $xls->generateXML($type);
                    exit;
                }

                $xaction = $type == 'charge' ? "countdetail/type/charge/uid/".$uid : "countdetail/type/withdraw/uid/".$uid;

                $this->assign("xaction", $xaction);
                $this->assign('username', $username);
                $this->assign('userphone', $userphone);

                break;
        }

        $this->assign('pagebar', $page);
        $this->assign("list", $result);
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->display($type.'detail');
    }

    /**
     * 运营推广数统计
     */
    public function generalcount()
    {
        $starttime = time();
        $conditional = false;

        $ispage = 1;//是否翻页  1翻页，用在列表  0不翻页用在导出
        $map = $search = array();
        $where = " user_phone<>'123'";
        $mwhere  = " user_phone<>'123' ";
        $idlist = [];
        $isIdlistEmpty = false;
        $idnotlist = [];
        $phonelist = [];

        //id
        if ($_REQUEST["id"]) {
            //$where .= " and m.id = $_REQUEST[id]";
            $mwhere .= " and id = $_REQUEST[id]";
            $search["id"] = $_REQUEST["id"];
            $conditional = true;
        }
        //身份
        if ($_REQUEST['is_vip']=='yes') {
            //$where .= " and m.is_vip = 1";
            $mwhere .= " and is_vip = 1";
            $search['is_vip'] = 'yes';
            $conditional = true;
        } elseif ($_REQUEST['is_vip']=='no') {
            //$where .= " and m.is_vip = 0";
            $mwhere .= " and is_vip = 0";
            $search['is_vip'] = 'no';
            $conditional = true;
        } elseif ($_REQUEST['is_vip']=='all') {
            //$where .= " and m.is_vip >= 0";
            $mwhere .= " and is_vip >= 0";
            $search['is_vip'] = 'all';
            $conditional = true;
        }
        //手机号
        if ($_REQUEST["user_phone"]) {
            
            //$where .= " and m.user_phone = $_REQUEST[user_phone]";
            $mwhere .= " and user_phone = $_REQUEST[user_phone]";
            $search["user_phone"] = $_REQUEST["user_phone"];
            $conditional = true;
        }
        //真实姓名
        if ($_REQUEST["real_name"]) {
            //$where .= " and real_name like '%".$_REQUEST['real_name']."%'";
            $con['real_name'] = array('like',$_REQUEST['real_name']);
            //获取id
            $nameres = M('member_info')->field("uid")->where($con)->select();
            if(is_null($nameres)){
                //如果无法查询导相关姓名的人员，查询结果位空
                $isIdlistEmpty = true;
            }else{
                //结果何idlist 求交集
                $ids = array_column($nameres, "uid");
                $idlist = empty($idlist)?$ids:array_intersect($ids, $idlist);    
            }
            
            //记录查询记过
            $search["real_name"] = $_REQUEST["real_name"];
            $conditional = true;
        }

        //籍贯
        if ($_REQUEST['jg']) {

            //
            $originres = M()->query("select uid,idcard from lzh_member_info where left(idcard,2) = $_REQUEST[jg]");
            if(is_null($originres)){
                //没有相关籍贯的用户，查询结果为空
                $isIdlistEmpty = true;
            }else{
                $ids2 = array_column($originres, 'uid');
                $idlist = empty($idlist)?$ids2:array_intersect($ids2, $idlist);    
            }

            //记录查询结果
            $search["jg"] = $_REQUEST["jg"];
            $conditional = true;
        }

        //是否揭阳地区
        if ($_REQUEST['is_jieyang'] != '') {
            if ($_REQUEST['is_jieyang'] == 0) {
                $jieyangres = M()->query("select uid,idcard from lzh_member_info where left(idcard,4) <> 4452 ");
            } elseif ($_REQUEST['is_jieyang'] == 1) {
                $jieyangres = M()->query("select uid,idcard from lzh_member_info where left(idcard,4) = 4452 ");
            }

            if(is_null($jieyangres)){
                //没有非揭阳用户，查询结果为空
                $isIdlistEmpty = true;
            }else{
                $ids3 = array_column($jieyangres, 'uid');
                $idlist = empty($idlist)?$ids3:array_intersect($ids3, $idlist);
            }

            $search["is_jieyang"] = $_REQUEST["is_jieyang"];
            $conditional = true;
        }

        //注册渠道
        if ($_REQUEST['equipment']) {
            //$where .= " and m.equipment = '$_REQUEST[equipment]'";
            $mwhere .= " and equipment = '$_REQUEST[equipment]'";
            $search['equipment'] = $_REQUEST['equipment'];
            $conditional = true;
        }
        //注册时间
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
            $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
            $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
            //$where .= " and m.reg_time between {$start_time} and {$end_time} ";
            $mwhere .= " and reg_time between {$start_time} and {$end_time} ";
            $conditional = true;
        }

        //首投时间
        if (!empty($_REQUEST['invest_start_time']) || !empty($_REQUEST['invest_end_time'])) {
            $invest_start_time = !empty($_REQUEST['invest_start_time']) ? strtotime($_REQUEST['invest_start_time']." 00:00:00") : 0;
            $invest_end_time = !empty($_REQUEST['invest_end_time']) ? strtotime($_REQUEST['invest_end_time']." 23:59:59") : time();
            $search['invest_start_time'] = urldecode($_REQUEST['invest_start_time']);
            $search['invest_end_time'] = urldecode($_REQUEST['invest_end_time']);
        
            unset($con);
            $con['add_time'] = array("between",strtotime($_REQUEST['invest_start_time']." 00:00:00").",".strtotime($_REQUEST['invest_end_time']." 23:59:59"));;
            $investAggRes = M('invest_aggregate')->field('uid')->where($con)->select();
            if(is_null($investAggRes)){
                $isIdlistEmpty = true;
            }else{
                $idsAgg = array_column($investAggRes, 'uid');
                $idlist = empty($idlist)?$idsAgg:array_intersect($idsAgg, $idlist);    
            }

            $conditional = true;
        }

        //是否实名
        if ($_REQUEST['is_realname'] == 'yes') {

            unset($con);
            $con['id_status'] = 1;
            $realnameres = M('members_status')->field('uid')->where($con)->select();
            if(is_null($realnameres)){
                $isIdlistEmpty = true;
            }else{
                $ids4 = array_column($realnameres, 'uid');
                $idlist = empty($idlist)?$ids4:array_intersect($ids4, $idlist);    
            }

            $search['is_realname'] = 'yes';
            $conditional = true;
        } elseif ($_REQUEST['is_realname']=='no') {
            unset($con);
            $con['id_status'] = 0;
            $realnameres = M('members_status')->field('uid')->where($con)->select();
            if(is_null($realnameres)){
                $isIdlistEmpty = true;
            }else{
                $ids5 = array_column($realnameres, 'uid');
                $idlist = empty($idlist)?$ids5:array_intersect($ids5, $idlist);    
            }

            $search['is_realname'] = 'no';
            $conditional = true;
        } elseif ($_REQUEST['is_realname']=='all') {
            $search['is_realname'] = 'all';
            $conditional = true;
        }

         //是否投资
        if ($_REQUEST['is_invest'] == 'yes') {
            $investres = M('borrow_investor')->field('investor_uid')->where(1)->group('investor_uid')->select();
            if(is_null($investres)){
                $isIdlistEmpty = true;
            }else{
                $ids6 = array_column($investres, 'investor_uid');
                $idlist = empty($idlist)?$ids6:array_intersect($ids6, $idlist);    
            }

            $search['is_invest'] = 'yes';
            $conditional = true;
            
        } elseif ($_REQUEST['is_invest'] == 'no') {
            $investres = M('borrow_investor')->field('investor_uid')->where(1)->group('investor_uid')->select();
            if(is_null($investres)){

            }else{
                $ids7 = array_column($investres, 'investor_uid');
                $idnotlist = array_merge($idnotlist,$ids7);
            }

            $search['is_invest'] = 'no';
            $conditional = true;
        } elseif ($_REQUEST['is_invest'] == 'all') {
            $search['is_invest'] = 'all';
            $conditional = true;
        }

        //是否使用体验金
        if ($_REQUEST['is_used_experience_money'] == 'yes') {
            unset($con);

            $con['type'] = 2;
            $con['status'] = 1;
            $coures = M('coupons')->field('user_phone')->where($con)->select();
            if(is_null($coures)){
                $isIdlistEmpty = true;
            }else{
                $phonelist1= array_column($coures, 'user_phone');
                $phonelist = empty($phonelist)?$phonelist1:array_intersect($phonelist1, $phonelist1);
            }
            $search['is_used_experience_money'] = 'yes';
            $conditional = true;
           
        } elseif ($_REQUEST['is_used_experience_money'] == 'no') {
            $con['type'] = 2;
            $con['status'] = 0;
            $coures = M('coupons')->field('user_phone,type,status')->where($con)->select();
            if(is_null($coures)){
                $isIdlistEmpty = true;
            }else{
                $phonelist1= array_column($coures, 'user_phone');
                $phonelist = empty($phonelist)?$phonelist1:array_intersect($phonelist1, $phonelist);
            }
            $search['is_used_experience_money'] = 'no';
            $conditional = true;
        } elseif ($_REQUEST['is_used_experience_money'] == 'all') {
            $search['is_used_experience_money'] = 'all';
            $conditional = true;
        }

        //推荐人
        if ($_REQUEST['recommend']) {
            unset($con);
            $con['user_phone'] = array('like', $_REQUEST['recommend']);
            $recommendres = M('members')->field('id,user_phone')->where($con)->select();
            if(is_null($recommendres)){
                $isIdlistEmpty = true;
            }else{

                $ids10= array_column($recommendres, 'id');
                $recommend_str = implode(",", $ids10);
                unset($con);
                $con['recommend_id'] = array('in',$recommend_str);
                $recres = M('members')->field('id')->where($con)->select();

                if(is_null($recres)){
                    $isIdlistEmpty = true;
                }else{
                    $ids16 = array_column($recres, "id");
                    $idlist = empty($idlist)?$ids16:array_intersect($ids16, $idlist);
                }
                
            }

            $search['recommend'] = $_REQUEST['recommend'];
            $conditional = true;
        }


        if($isIdlistEmpty){
            $mwhere .= " and 1=0";
        }

        //echo '#'.json_encode($idlist).'#'.json_encode($phonelist).'#'.json_encode($idnotlist).'#';
        if(!empty($idlist)){
            $str = implode(',', $idlist);
            $mwhere .=" and id in ( ".$str." )";
        }

        if(!empty($phonelist)){
            $str2 = implode(",", $phonelist);
            $mwhere .=" and user_phone in  ( ".$str2." )";
        }

        if(!empty($idnotlist)){
            $str3 = implode(",", $idnotlist);
            $mwhere .=" and id not in  ( ".$str3." )";
        }

         $field = 'bii.invest_total,co.co_min_endtime,rm.user_phone as recommend_name,id.add_time as tiyanjin_time,bi.invest_sum as invest_id,bi.investor_uid,m.recommend_id,ia.first_invest_amount,ia.firstmonth_invest_amount,ia.add_time as first_invest_time,m.id,m.is_vip,m.user_name,m.user_phone,m.equipment,m.reg_time,m.last_log_time,m.recommend_id,mi.real_name,mi.idcard,ms.id_status,';

        $field .= " case left(mi.idcard,2)
                    when '11' then '北京市'
                    when '12' then '天津市'
                    when '13' then '河北省'
                    when '14' then '山西省'
                    when '15' then '内蒙古自治区'
                    when '21' then '辽宁省'
                    when '22' then '吉林省'
                    when '23' then '黑龙江省'
                    when '31' then '上海市'
                    when '32' then '江苏省'
                    when '33' then '浙江省'
                    when '34' then '安徽省'
                    when '35' then '福建省'
                    when '36' then '江西省'
                    when '37' then '山东省'
                    when '41' then '河南省'
                    when '42' then '湖北省'
                    when '43' then '湖南省'
                    when '44' then '广东省'
                    when '45' then '广西壮族自治区'
                    when '46' then '海南省'
                    when '50' then '重庆市'
                    when '51' then '四川省'
                    when '52' then '贵州省'
                    when '53' then '云南省'
                    when '54' then '西藏自治区'
                    when '61' then '陕西省'
                    when '62' then '甘肃省'
                    when '63' then '青海省'
                    when '64' then '宁夏回族自治区'
                    when '65' then '新疆维吾尔自治区'
                    when '71' then '台湾省'
                    when '81' then '香港特别行政区'
                    when '82' then '澳门特别行政区'
                    else ''
                    end as jiguan,";
                    

        $field .= " case left(mi.idcard,4) when '4452' then 1 else 0 end as is_jieyang ";

        $sql = "SELECT $field from ( SELECT * from lzh_members where $mwhere  order by id desc limit limit123 ) m
        left join lzh_member_info mi on m.`id`=mi.`uid`
        left join lzh_members_status ms on m.`id`=ms.`uid`
        left join lzh_invest_aggregate ia on m.`id`=ia.`uid`
        left join lzh_members rm on m.`recommend_id`=rm.`id`
        left join (select investor_uid,min(add_time) as add_time from lzh_investor_detail_experience group by investor_uid) id on m.`id`=id.`investor_uid`
        left join (select count(*) as invest_sum,investor_uid from lzh_borrow_investor where id<>0 group by investor_uid ) bi on m.`id`=bi.`investor_uid`
        left join (select user_phone,min(endtime) as co_min_endtime from lzh_coupons where status=0 and type = 1 group by user_phone) co on m.`user_phone`=co.`user_phone`
        left join (select sum(investor_capital) as invest_total,investor_uid  from lzh_borrow_investor where id<>0 group by investor_uid ) bii on m.`id`=bii.`investor_uid`
        ";

        $nsql = "SELECT count(*) as no from (SELECT * from lzh_members where $mwhere  order by id desc)   m
        left join lzh_member_info mi on m.`id`=mi.`uid`
        left join lzh_members_status ms on m.`id`=ms.`uid`
        left join lzh_invest_aggregate ia on m.`id`=ia.`uid`
        left join lzh_members rm on m.`recommend_id`=rm.`id`
        left join (select investor_uid,min(add_time) as add_time from lzh_investor_detail_experience group by investor_uid) id on m.`id`=id.`investor_uid`
        left join (select count(*) as invest_sum,investor_uid  from lzh_borrow_investor where id<>0 group by investor_uid ) bi on m.`id`=bi.`investor_uid`
        left join (select user_phone,min(endtime) as co_min_endtime from lzh_coupons where status=0 and type = 1 group by user_phone) co on m.`user_phone`=co.`user_phone`
        left join (select sum(investor_capital) as invest_total,investor_uid  from lzh_borrow_investor where id<>0 group by investor_uid ) bii on m.`id`=bii.`investor_uid`
        ";


        $sql .= " order by m.`id` desc ";
       
        if(!$conditional){
            //members　的个数
            $count = M('members')->where(1)->count('id');
            //echo '.';
        }else{
            $count = M()->query($nsql);
            $count = intval($count[0]['no']);    
        }
        

        if ($_REQUEST['execl'] == "execl") {
            $ispage = 0;
            $limit = "0,1000000";
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
        }

        $sql = str_replace('limit123', $limit, $sql);
        $list = M()->query($sql); 

        if ($ispage == 0) {
            require_once 'CORE/Extend/spout-2.7.2/src/Spout/Autoloader/autoload.php';
            $writer = Box\Spout\Writer\WriterFactory::create(Box\Spout\Common\Type::CSV);
            $filePath = "yunyingtuiguangtongji.csv";
            $writer->openToBrowser($filePath);
            $header = array('ID','身份','用户名','身份证号','手机号','真实姓名','籍贯','揭阳地区','推荐人','注册渠道','注册时间','最后登录时间','是否实名','是否已投资','投资金额','使用体验金时间','投资券即将过期时间','首次投资时间','首次投资','首次投资当月总投资');
            $writer->addRow($header);

            $row = array();
            foreach ($list as $key=>$v) {
                $row[$key+1]['uid'] = $v['id'];
                $row[$key+1]['is_vip'] = $v['is_vip'] == 1 ? "投资人/借款人" : "投资人";
                $row[$key+1]['uname'] = $v['user_name'];
                $row[$key+1]['idcard'] = "\t".strval($v['idcard'])." ";
                $row[$key+1]["user_phone"] = $v["user_phone"];
                $row[$key+1]['real_name'] = $v['real_name'];
                $row[$key+1]['jiguan'] = $v['jiguan'];
                $row[$key+1]['is_jieyang'] = $v['is_jieyang'] == 1 ? "是" : "否";
                $row[$key+1]['recommend_name'] = $v['recommend_name'];
                $row[$key+1]['equipment'] = $v['equipment'];
                $row[$key+1]["reg_time"] = date("Y-m-d", $v["reg_time"]);
                $row[$key+1]["last_log_time"] = $v["last_log_time"] > 0 ? date("Y-m-d", $v["last_log_time"]) : '';
                $row[$key+1]['id_status'] = $v['id_status'] == 1 ? "是" : "否";
                $row[$key+1]['is_invest'] = intval($v['invest_id'])? "是" : "否";
                $row[$key+1]['invest_total'] = $v['invest_total'];
                $row[$key+1]["add_time"] = $v["tiyanjin_time"] > 0 ? date("Y-m-d", $v["tiyanjin_time"]) : '';
                $row[$key+1]['expire_time'] = empty($v['co_min_endtime'])?"":date('Y-m-d',$v["co_min_endtime"]);
                $row[$key+1]['first_invest_time'] = empty($v['first_invest_time'])?"尚未投资":date('Y-m-d',$v["first_invest_time"]);
                $row[$key+1]['first_invest_amount'] = empty($v['first_invest_time'])?"无":$v['first_invest_amount'];
                $row[$key+1]['firstmonth_invest_amount'] = empty($v['first_invest_time'])?"无":$v['firstmonth_invest_amount'];
            }

            $writer->addRows($row);
            $writer->close();
            die;
        }

        $this->assign('pagebar', $page);
        $this->assign("list", $list);
        $this->assign("xaction", "generalcount");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));

    

        $jg = array('11' => '北京市', '12' => '天津市', '13' => '河北省', '14' => '山西省', '15' => '内蒙古自治区', '21' => '辽宁省', '22' => '吉林省',
                    '23' => '黑龙江省', '31' => '上海市', '32' => '江苏省', '33' => '浙江省', '34' => '安徽省', '35' => '福建省', '36' => '江西省',
                    '37' => '山东省', '41' => '河南省', '42' => '湖北省', '43' => '湖南省', '44' => '广东省', '45' => '广西壮族自治区', '46' => '海南省',
                    '50' => '重庆市', '51' => '四川省', '52' => '贵州省', '53' => '云南省', '54' => '西藏自治区', '61' => '陕西省', '62' => '甘肃省',
                    '63' => '青海省', '64' => '宁夏回族自治区', '65' => '新疆维吾尔自治区', '71' => '台湾省', '81' => '香港特别行政区', '82' => '澳门特别行政区');
        $this->assign("jg", $jg);
        $this->assign('is_jieyang', array('0' => '否', '1' => '是'));
        $this->display();
    }



    public function risklist()
    {
        import("ORG.Util.PageFilter");
        $count = M('risk_result r')->field('sum(a.score) as score')->join('lzh_risk_answer a on a.id = r.answer_id')->group('r.uid')->select();
        $search=array();
        $p = new PageFilter(count($count), $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $list = M('risk_result r')->field('sum(a.score) as score,r.time,m.id,m.user_name')->join('lzh_members m on m.id = r.uid')->join('lzh_risk_answer a on a.id = r.answer_id')->group('r.uid')->order('r.time DESC')->limit($limit)->select();
        foreach ($list as $k => $v) {
            if ($v['score']>=7 && $v['score']<=12) {
                $list[$k]['ftype'] = '保守型';
            } elseif ($v['score']>=13 && $v['score']<=17) {
                $list[$k]['ftype'] = '谨慎型';
            } elseif ($v['score']>=18 && $v['score']<=23) {
                $list[$k]['ftype'] = '稳健型';
            } elseif ($v['score']>=24 && $v['score']<=28) {
                $list[$k]['ftype'] = '积极型';
            } else {
                $list[$k]['ftype'] = "无";
            }
        }
        $this->assign('list', $list);
        $this->assign('pagebar', $page);
        $this->assign("xaction", "risklist");
        $this->display();
    }

    public function reskproblem()
    {
        import("ORG.Util.PageFilter");
        $count = M('risk_problem')->count('id');
        $search=array();
        $p = new PageFilter(count($count), $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $list = M('risk_problem')->order('id')->limit($limit)->select();
        $this->assign('pagebar', $page);
        $this->assign('list', $list);
        $this->assign("xaction", "reskproblem");
        $this->display();
    }

    public function editproblem()
    {
        $id=intval($_REQUEST['id']);
        if (!empty($id)) {
            $info = M('risk_problem p')->field('p.problem,a.id,a.answer,a.score')->join('lzh_risk_answer a on p.id = a.problem_id')->where('p.id ='.$id)->order('a.id')->select();
            $this->assign('problem', $info[0]['problem']);
            $this->assign('id', $id);
            $this->assign('info', $info);
        }
        $this->display();
    }

    public function doedit()
    {
        $data = $_REQUEST;
        $map['problem'] = $data['problem'];
        if (empty($data['problem'])) {
            $this->error('问题不能为空');
        }
        if (empty($data['answer'])) {
            $this->error('答案不能为空');
        }
        if (empty($data['score'])) {
            $this->error('分数不能为空');
        }
        if (empty($data['id'])) {
            $res = M('risk_problem')->add($map);
            for ($i=0;$i<count($data['answer']);$i++) {
                $par[]=array('problem_id'=>$res,'answer'=>$data['answer'][$i],'score'=>$data['score'][$i]);
            }
            $res1 = M('risk_answer')->addAll($par);
        } else {
            for ($i=0;$i<count($data['answer']);$i++) {
                $amap['answer'] = $data['answer'][$i];
                $amap['score'] = $data['score'][$i];
                $res2[$i] = M('risk_answer')->where('id = '.$data['aid'][$i])->save($amap);
                if ($res2[$i]==0) {
                    unset($res2[$i]);
                }
            }
        }
        if ($res||$res1||$res2) {
            $this->success('操作成功', '/admin/yunwei/reskproblem');
        } else {
            $this->error('操作失败');
        }
    }

    public function delproblem()
    {
        $id = $_REQUEST['id'];
        $res = M('risk_problem')->where('id = '.$id)->delete();
        $res1 = M('risk_answer')->where('problem_id = '.$id)->delete();
        if ($res&&$res1) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 周年活动
     */
    public function zhounian()
    {
        import("ORG.Util.PageFilter");
        $search=array();
        if ($_POST["uid"]) {
            $search["a.uid"] =trim($_POST["uid"]);
        }
        if ($_POST["uname"]) {
            $search["g.real_name"] =trim($_POST["uname"]);
        }
        if ($_POST["phone"]) {
            $search["phone"] =trim($_POST["phone"]);
        }
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            $row=array();
            $row[0]=array('序号','用户ID','真实姓名','手机号','注册时间','活动期间总投资额','获得奖品');
            $i=1;
            $info = M('activity a')->field("a.id,a.uid as uid,g.real_name as real_name ,a.phone,registertime,money,goodsname")->join("lzh_member_info g on g.uid=a.uid")->join("lzh_activity_price t on t.id=a.priceid")->order('money desc')->where($search)->select();
            foreach ($info as $key=>$v) {
                $row[$i]['num'] = $i;
                $row[$i]['uid'] =$v["uid"];
                $row[$i]['uname'] =$v["real_name"];
                $row[$i]['phone'] = $v['phone'];
                $row[$i]['registertime'] = $v['registertime'];
                $row[$i]['money'] = $v['money'];
                $row[$i]['goodsname'] = $v['goodsname'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'zhounian');
            $xls->addArray($row);
            $xls->generateXML("zhounian".date("YmdHis", time()));
            exit;
        }
        $count=M('activity a')->where($search)->count();
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $list = M('activity a')->field("a.id,a.uid as uid,g.real_name as uname ,phone,registertime,money,goodsname")->join("lzh_member_info g on g.uid=a.uid")->join("lzh_activity_price t on t.id=a.priceid")->where($search)->order('money desc')->limit($limit)->select();
        $this->assign('pagebar', $page);
        $this->assign('list', $list);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign("search", $search);
        $this->display();
    }

    /**
     * 存钱罐：新浪下载失败后失败列表
     */
    public function cunqianguan()
    {
        import("ORG.Util.PageFilter");
        $search=array();
        if ($_POST["starttime"]) {
            $search["starttime"] =trim($_POST["starttime"]);
        }
        if ($_POST["endtime"]) {
            $search["endtime"] =trim($_POST["endtime"]);
        }

        $member_piggfaillog=M("member_piggfaillog");
        $count=$member_piggfaillog->where($search)->count();
        $p = new PageFilter(count($count), $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $list=$member_piggfaillog->where($search)->limit($limit)->order("addtime desc")->select();
        foreach ($list as $key=>$value) {
            if ($value["status"]==0) {
                $list[$key]["status"]="<span style='color:red;'>失败</span>";
                $list[$key]["op"]="<a class='download'>重新下载</a>";
            } else {
                $list[$key]["status"]="<span>成功</span>";
                $list[$key]["op"]="<span></span>";
            }
        }
        $this->assign("list", $list);
        $this->assign('pagebar', $page);
        $this->display();
    }

    /**
     * 存钱罐此处手动处理下载存钱罐信息
     */
    public function cunqianguan_download()
    {
        $date=$_POST["date"];
        $this->toolearn($date);
    }

    /**
     * ajax 输出json格式
     * @param $status
     * @param $message
     * @param null $data
     */
    private function outmessage($status, $message, $data=null)
    {
        $outdata=array();
        $outdata["status"]=$status;
        $outdata["msg"] =$message;
        if ($data) {
            $outdata["data"]=$data;
        }
        echo json_encode($outdata);
        exit();
    }

    /**
     * 存钱罐收益按照指定日期来下载数据
     * @param $mydate 日期
     */
    private function toolearn($mydate)
    {
        if (empty($mydate)) {
            $this->outmessage(1, "日期不能为空");
        }
        $is_date=strtotime($mydate)?strtotime($mydate):false;
        if ($is_date==false) {
            $this->outmessage(2, "日期格式错误");
        }
        $pigmodel=M("member_piggbanklog");
        $date=strtotime($mydate);//日期的时间戳
        $list=$pigmodel->where(array("time"=>$date))->find();
        if ($list) {
            $this->outmessage(3, "存钱罐已经处理");
        } else {
            $data = $this->getdata($mydate);
            $i=-1;
            foreach ($data as $k => $v) {
                $uid = substr(trim($v[0]), 8);
                if (!is_numeric($uid)) {
                    unset($data[$k]);
                } else {
                    $list[$i]['uid'] = $uid;
                    $list[$i]['type'] = 88;
                    $list[$i]['affect_money'] = $v[4];
                    $list[$i]['info'] = "用户存钱罐昨日收益";
                    $list[$i]['add_time'] = $date;
                    $list[$i]['add_ip'] = get_client_ip();
                    $list[$i]['target_uname'] = "@sina@";
                    $info[$i]['uid'] = $uid;
                    $info[$i]['available_balance'] = $v[1];//可用余额
                    $info[$i]['amount_frozen'] = $v[2];//冻结余额
                    $info[$i]['total_balance'] = $v[3];//总余额
                    $info[$i]['earnings_yesterday'] = $v[4];//昨日收益
                    $info[$i]['thirty_earnings'] = $v[5];//30日收益
                    $info[$i]['total_revenue'] = $v[6];//总收益
                    $info[$i]['time'] =$date;
                }
                $i++;
            }
            $res = M('member_moneylog')->addAll($list);
            $res1 = M('member_piggybank')->addAll($info);
            if ($res&&$res1) {
                $pigmodel->add(array("name"=>"用户存钱罐昨日收益","time"=>$date));
                file_put_contents('sftplog.txt', '入库成功'."\n", FILE_APPEND);
                M("member_piggfaillog")->where(array("addtime"=>$date))->save(array("status"=>1));
                $this->outmessage(0, "处理成功");
            } else {
                file_put_contents('sftplog.txt', '写入数据库失败'.$list."\n", FILE_APPEND);
                $this->outmessage(1, "处理失败");
            }
        }
    }

    /**
     * @param $date 年月日，日期字符串
     * @return array
     */
    private function getdata($date)
    {
        import("@.Oauth.sina.Weibopay");
        $weibopay = new Weibopay();
        $filename = array();
        $filename["zhye-yh-cqg"] = 'zhye-yh-cqg';//存钱罐账户余额及收益
        $filetype = ".zip";//目前对账文件都是打成zip压缩包提供下载
        //按照对账日期创建文件夹
        $zipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" . $date."/";
        $unzipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" .$date."/unzip/";
        $weibopay->mkFolder($zipflo);
        $weibopay->mkFolder($unzipflo);
        $zip = new ZipArchive;
        foreach ($filename as $key => $value) {
            $result = $weibopay->sftp_download($zipflo, $date . "_" . $value . $filetype);
            if ($result) {
                $res = $zip->open($zipflo.$date . "_" . $value . $filetype);
                if ($res === true) {
                    //解压缩到文件夹
                    $serveresult=$zip->extractTo($unzipflo);
                    if ($serveresult) {
                        file_put_contents('sftplog.txt', '保存成功'.$unzipflo."\n", FILE_APPEND);
                    } else {
                        cunqianguan_filelog($date);
                        file_put_contents('sftplog.txt', '保存失败'.$unzipflo."\n", FILE_APPEND);
                        $this->outmessage(6, "保存失败");
                        die();
                    }
                    $zip->close();
                } else {
                    cunqianguan_filelog($date);
                    file_put_contents('sftplog.txt', '解压缩失败'.$zipflo.$date . "_" . $value . $filetype."\n", FILE_APPEND);
                    $this->outmessage(5, "解压缩失败");
                }
            } else {
                cunqianguan_filelog($date);
                file_put_contents('sftplog.txt', '下载失败'.$unzipflo."\n", FILE_APPEND);
                $this->outmessage(4, "下载失败");
            }
        }
        $handler = opendir($unzipflo);
        while (($filename = readdir($handler)) !== false) {
            if ($filename !="." && $filename !="..") {
                $row = 1;
                $file=$unzipflo.$filename;
                $handle = fopen($file, "r");
                $resultarray=array();
                while ($data = fgetcsv($handle)) {
                    //统计数据行数
                    $num = count($data);
                    $row++;
                    //对数组进行迭代，迭代每条数据
                    for ($c = 0; $c < $num; $c++) {
                        //注意中文乱码问题
                        $data[$c] = iconv("gbk", "utf-8//IGNORE", $data[$c]);
                        //将数据放在2维数组进行存放
                        $resultarray[$row][$c] = $data[$c];
                    }
                }
                fclose($handle);
            }
        }
        closedir($handler);
        return $resultarray;
    }

    public function sendtouziquan()
    {
        $this->display();
    }

    public function dotouziquan()
    {
        $send_type=$_POST["send_type"];
        $reg_time=null;
        $phonelist=null;
        foreach ($send_type as $value) {
            if ($value=="regtime") {
                $reg_time = array("between",strtotime($_POST["start_time"]." 00:00:00").",".strtotime($_POST["end_time"]." 23:59:59"));
            } elseif ($value=="phonelist") {
                $p_str = trim($_POST["phone"], ';');
                $phonelist = explode(";", $p_str);
            }
        }
        if ($reg_time != null && $phonelist != null) {
            $where1["user_phone"] = array("in",$phonelist);
            $where1['_logic']="AND";
            $where1["user_name"] = array("in",$phonelist);
            $where1["id"] = array("in",$phonelist);
            $where1['_logic'] = 'OR';
            $where['_complex']=$where1;
            $where["reg_time"] = $reg_time;
            $where['_logic'] = 'AND';
        } elseif ($reg_time != null && $phonelist == null) {
            $where["reg_time"] = $reg_time;
        } elseif ($reg_time == null && $phonelist != null) {
            $where["user_phone"] = array("in",$phonelist);
            $where["user_name"] = array("in",$phonelist);
            $where["id"] = array("in",$phonelist);
            $where['_logic'] = 'OR';
        }
        $member = M("members")->where($where)->select();
        if (empty($member)) {
            $this->error('找不到对应的用户！');
        }
        $phonelist = null;
        foreach ($member as $key => $m) {
            $phonelist[$key] = $m["user_phone"];
        }
        $moneylist = $_POST["money"];
        $numberlist = $_POST["number"];
        $i = 0;
        $allmoney=0;
        $phonestr = null;
        foreach ($phonelist as $pk => $phone) {
            $allmoney = 0;
            foreach ($moneylist as $key => $money) {
                
                if ($numberlist[$key] != 0) {
                    for ($k=0; $k < $numberlist[$key]; $k++) {
                        $arr[$i]["user_phone"]    = $phone;
                        $arr[$i]["money"]         = $money;
                        $arr[$i]["endtime"]       = strtotime($_POST["quan_end_time"].' 23:59:59');
                        $arr[$i]["status"]        = 0;
                        $arr[$i]["serial_number"] = time() . rand(100000, 999999);
                        $arr[$i]["type"]          = 1;
                        $arr[$i]["name"]          = "平台赠送";
                        $arr[$i]["addtime"]       = date("Y-m-d H:i:s", time());
                        $arr[$i]["isexperience"]  = 1;
                        $arr[$i]["use_money"]     = $money*100;
                        $arr[$i]["admin"]         = session('admin');
                        $arr[$i]["admin_name"]    = session('admin_user_name');
                        $allmoney += $money;
                        $i++;
                    }
                }
            }
            if ($pk == 0) {
                $phonestr = $phone;
            } else {
                $phonestr .= ",".$phone;
            }
        }

    

        if (empty($arr)) {
            $this->success("发送失败");
        }
        $result =  M("coupons")->addAll($arr);
        if ($result) {
            if ($allmoney>0) {
                $content = "尊敬的链金所用户您好！{$allmoney}元投资券已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985.";
                sendsms($phonestr, $content);
            }
            $this->success("发放成功");
        }
    }

    public function sendjiaxiquan()
    {
        $this->display();
    }

    public function dojiaxiquan()
    {
        $send_type=$_POST["send_type"];
        $reg_time=null;
        $phonelist=null;
        foreach ($send_type as $value) {
            if ($value=="regtime") {
                $reg_time = array("between",strtotime($_POST["start_time"]." 00:00:00").",".strtotime($_POST["end_time"]." 23:59:59"));
            } elseif ($value=="phonelist") {
                $p_str = trim($_POST["phone"], ';');
                $phonelist = explode(";", $p_str);
            }
        }
        if ($reg_time != null && $phonelist != null) {
            $where1["user_phone"] = array("in",$phonelist);
            $where1['_logic']="AND";
            $where1["user_name"] = array("in",$phonelist);
            $where1["id"] = array("in",$phonelist);
            $where1['_logic'] = 'OR';
            $where['_complex']=$where1;
            $where["reg_time"] = $reg_time;
            $where['_logic'] = 'AND';
        } elseif ($reg_time != null && $phonelist == null) {
            $where["reg_time"] = $reg_time;
        } elseif ($reg_time == null && $phonelist != null) {
            $where["user_phone"] = array("in",$phonelist);
            $where["user_name"] = array("in",$phonelist);
            $where["id"] = array("in",$phonelist);
            $where['_logic'] = 'OR';
        }
        $member = M("members")->where($where)->select();
        if (empty($member)) {
            $this->error('找不到对应的用户！');
        }
        $phonelist = null;
        foreach ($member as $key => $m) {
            $phonelist[$key] = $m["user_phone"];
        }
        $moneylist = $_POST["money"];
        $numberlist = $_POST["number"];
        $i = 0;
        $phonestr = null;
        $juan_str = null;
        foreach ($phonelist as $pk => $phone) {
            $juan_str = null;
            foreach ($moneylist as $key => $money) {
                if ($numberlist[$key] != 0) {
                    for ($k=0; $k < $numberlist[$key]; $k++) {
                        $arr[$i]["user_phone"] = $phone;
                        $arr[$i]["money"] = $money;
                        $arr[$i]["endtime"] = strtotime($_POST["quan_end_time"].' 23:59:59');
                        $arr[$i]["status"] = 0;
                        $arr[$i]["serial_number"] = time() . rand(100000, 999999);
                        $arr[$i]["type"] = 3;
                        $arr[$i]["name"] = "平台赠送";
                        $arr[$i]["addtime"] = date("Y-m-d H:i:s", time());
                        $arr[$i]["isexperience"] = 1;
                        $arr[$i]["use_money"] = 0;
                        
                        $arr[$i]["admin"]         = session('admin');
                        $arr[$i]["admin_name"]    = session('admin_user_name');
                        $i++;
                    }
                    $juan_str .= $money.'%加息券'.$numberlist[$key].'张、';
                }
            }
            if ($pk == 0) {
                $phonestr = $phone;
            } else {
                $phonestr .= ",".$phone;
            }
        }
        if (empty($arr)) {
            $this->success("发送失败");
        }
        $result =  M("coupons")->addAll($arr);
        if ($result) {
            $juan_str = rtrim($juan_str, '、');
            $content = "尊敬的链金所用户您好！".$juan_str."已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985.";
            sendsms($phonestr, $content);
            $this->success("发放成功");
        }
    }

    public function dingxiang()
    {
        $list = M("send_coupons")->select();
        $this->assign("list", $list);
        $this->display();
    }

    public function editdxstatus()
    {
        $id = $_POST["id"];
        $where["id"] = $id;
        $info = M("send_coupons")->where($where)->find();
        if ($info["is_active"] == 0) {
            $data["is_active"] = 1;
        } else {
            $data["is_active"] = 0;
        }
        M("send_coupons")->where($where)->save($data);
        $this->ajaxReturn("修改成功");
    }
    public function dingxiangedit()
    {
        if ($this->isPost()) {
            $where["id"] = $_POST["id"];
            $data["days"] = $_POST["days"];
            $data["money"] = $_POST["money"];
            $data["nologin_days"] = $_POST["nologin_days"];
            $res = M("send_coupons")->where($where)->save($data);
            if ($res) {
                $this->success("保存成功", __URL__."/dingxiang");
            } else {
                $this->error("保存失败", __URL__."/dingxiang");
            }
        } else {
            $id = $_GET["id"];
            $where["id"] = $id;
            $info = M("send_coupons")->where($where)->find();
            $this->assign("info", $info);
            $this->display();
        }
    }

    public function fengche()
    {
        $where = "  bi.add_time<>'123' ";
        //注册时间
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
            $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
            $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
            $where .= " and mf.add_time between {$start_time} and {$end_time} ";
            
            //$mwhere .= " and reg_time between {$start_time} and {$end_time} ";
        }

        //投资时间
        if (!empty($_REQUEST['inv_start_time']) || !empty($_REQUEST['inv_end_time'])) {
            $inv_start_time = !empty($_REQUEST['inv_start_time']) ? strtotime($_REQUEST['inv_start_time']." 00:00:00") : 0;
            $inv_end_time = !empty($_REQUEST['inv_end_time']) ? strtotime($_REQUEST['inv_end_time']." 23:59:59") : time();
            $search['inv_start_time'] = urldecode($_REQUEST['inv_start_time']);
            $search['inv_end_time'] = urldecode($_REQUEST['inv_end_time']);
            $where .= " and bi.add_time between {$inv_start_time} and {$inv_end_time} ";
        }

        
        $sql = "SELECT mf.`uid`,mf.`pf_user_name`,FROM_UNIXTIME(mf.`add_time`) as reg_time ,FROM_UNIXTIME(bi.`add_time`) AS tou_time,b.`borrow_name`,b.`borrow_duration_txt`,bi.`investor_capital` FROM lzh_members_fengche mf INNER JOIN lzh_borrow_investor bi ON bi.`investor_uid` = mf.`uid`
                    INNER JOIN lzh_borrow_info b ON b.`id` = bi.`borrow_id` where $where";



        $list = M()->query($sql);

        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("friend", 0, 1, '执行了所有邀请人列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','注册时间','投资时间','标的名称','投资金额','投资期限');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['pf_user_name'] = $v['pf_user_name'];
                $row[$i]['reg_time'] = $v['reg_time'];
                $row[$i]['tou_time'] = $v['tou_time'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'fengche');
            $xls->addArray($row);
            $xls->generateXML("fengche".date("YmdHis", time()));
            exit;
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= "limit $limit";
            $list = M()->query($sql);
        }
        $this->assign("pagebar", $page);
        $this->assign("xaction", "fengche");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        //echo json_encode($list);
        $this->assign("info", $list);
        $this->display();
    }
    
    public function chelun()
    {
        $where = "  bi.add_time<>'123' ";
        //注册时间
        if (!empty($_REQUEST['start_time']) || !empty($_REQUEST['end_time'])) {
            $start_time = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']." 00:00:00") : 0;
            $end_time = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']." 23:59:59") : time();
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
            $where .= " and cl.add_time between {$start_time} and {$end_time} ";
        }
        
        //投资时间
        if (!empty($_REQUEST['inv_start_time']) || !empty($_REQUEST['inv_end_time'])) {
            $inv_start_time = !empty($_REQUEST['inv_start_time']) ? strtotime($_REQUEST['inv_start_time']." 00:00:00") : 0;
            $inv_end_time = !empty($_REQUEST['inv_end_time']) ? strtotime($_REQUEST['inv_end_time']." 23:59:59") : time();
            $search['inv_start_time'] = urldecode($_REQUEST['inv_start_time']);
            $search['inv_end_time'] = urldecode($_REQUEST['inv_end_time']);
            $where .= " and bi.add_time between {$inv_start_time} and {$inv_end_time} ";
        }
        
        $field = "bi.id as bid,cl.`uid`,cl.`mobile`,m.user_name,FROM_UNIXTIME(cl.`add_time`) as reg_time,FROM_UNIXTIME(bi.`add_time`) AS tou_time,b.`borrow_name`,b.`borrow_duration_txt`,bi.`investor_capital`";
        
        //是否首投
        if (!empty($_REQUEST['first_invest'])) {
            $exp = $_REQUEST['first_invest'];
            if ($exp == 1) {
                $exp = 'not in';
            }else{
                $exp = 'in';
            }
            
            $where .= 'and bi.id '.$exp.' (select min(id) from lzh_borrow_investor group by investor_uid )';
            $search['first_invest'] = $_REQUEST['first_invest'];
        }
        
        $sql = "SELECT $field
        FROM lzh_members_chelun cl
        INNER JOIN lzh_borrow_investor bi ON bi.`investor_uid` = cl.`uid`
        INNER JOIN lzh_members m ON cl.`uid` = m.`id`
        INNER JOIN lzh_borrow_info b ON b.`id` = bi.`borrow_id` where ".$where;
        
        $list = M()->query($sql);
        
        if (!empty($list)){
            if ($_REQUEST['execl']=="execl") {
                import("ORG.Io.Excel");
                $row[0] = array('会员ID','手机号码','会员名称','注册时间','投资时间','标的名称','投资金额','投资期限','是否首次投资');
                $i=1;
                $model = M('borrow_investor');
                foreach ($list as $key => $v) {
                    $min_id = $model->where(['investor_uid' => $v['uid']])->min('id');
                    $row[$i]['uid'] = $v['uid'];
                    $row[$i]['mobile'] = $v['mobile'];
                    $row[$i]['user_name'] = $v['user_name'];
                    $row[$i]['reg_time'] = $v['reg_time'];
                    $row[$i]['tou_time'] = $v['tou_time'];
                    $row[$i]['borrow_name'] = $v['borrow_name'];
                    $row[$i]['investor_capital'] = $v['investor_capital'];
                    $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                    $row[$i]['is_first_invest'] = $min_id == $v['bid'] ? '是' : '否';
                    $i++;
                }
                $xls = new Excel_XML('UTF-8', false, 'fengche');
                $xls->addArray($row);
                $xls->generateXML("chelun".date("YmdHis"));
                exit;
            } else {
                import("ORG.Util.PageFilter");
                $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
                $page = $p->show();
                $limit = "{$p->firstRow},{$p->listRows}";
                $sql .= "limit $limit";
                $list = M()->query($sql);
                
                if (!empty($list)) {
                    $model = M('borrow_investor');
                    foreach ($list as &$v){
                        $min_id = $model->where(['investor_uid' => $v['uid']])->min('id');
                        $v['is_first_invest'] = $min_id == $v['bid'] ? '是' : '否';
                    }
                }
            }
        }
        
        $this->assign("pagebar", $page);
        $this->assign("xaction", "chelun");
        $search["execl"] = "execl";
        $this->assign("query", http_build_query($search));
        $this->assign("info", $list);
        $this->display();
    }

    /**
     * rongpuhui statistisc
     *
     */
    public function rphStatistics()
    {
        $sql = "SELECT mf.`id`,mf.`user_name`,mi.`real_name` as  pf_user_name,mf.`reg_time`,FROM_UNIXTIME(bi.`add_time`) AS tou_time,b.`borrow_name`,b.`borrow_duration_txt`,bi.`investor_capital` FROM lzh_members mf
                    INNER JOIN lzh_borrow_investor bi ON bi.`investor_uid` = mf.`id`
                    INNER JOIN lzh_borrow_info b ON b.`id` = bi.`borrow_id`
                    INNER JOIN lzh_member_info mi on mi.`uid` = mf.`id` where mf.`equipment` = 'rph' order by tou_time  desc ";

        $list = M()->query($sql);
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("friend", 0, 1, '执行了所有邀请人列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','注册时间','投资时间','标的名称','投资金额','投资期限');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['uid'] = $v['id'];
                $row[$i]['pf_user_name'] = $v['user_name'];
                $row[$i]['reg_time'] = date("Y-m-d H:i:s", $v['reg_time']);
                $row[$i]['tou_time'] = $v['tou_time'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'fengche');
            $xls->addArray($row);
            $xls->generateXML("rph".date("YmdHis", time()));
            exit;
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter(count($list), '', C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= "limit $limit";
            $list = M()->query($sql);
        }
        $this->assign("pagebar", $page);
        $this->assign("info", $list);
        $this->display();
    }


    /**
     * 富爸爸 statistisc
     *
     */
    public function fubabaStatistics()
    {
        if (!empty($_REQUEST['code'])) {
            $search['code'] = htmlspecialchars(trim($_REQUEST['code']));
        }else{
            $search['code'] = 'fengche';
        }

        $cpslist = M('cps_index')->where(1)->select();

        $sql = "SELECT mf.`id`,mf.`user_name`,mi.`real_name` as  pf_user_name,mf.`reg_time`,FROM_UNIXTIME(bi.`add_time`) AS tou_time,b.`borrow_name`,b.`borrow_duration_txt`,bi.`investor_capital`,mf.`equipment` FROM lzh_members mf
                    INNER JOIN lzh_borrow_investor bi ON bi.`investor_uid` = mf.`id`
                    INNER JOIN lzh_borrow_info b ON b.`id` = bi.`borrow_id`
                    INNER JOIN lzh_member_info mi on mi.`uid` = mf.`id` where mf.`equipment` = '{$search['code']}' order by tou_time  desc ";

        $list = M()->query($sql);
        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("friend", 0, 1, '执行了所有邀请人列表！');//管理员操作日志
            $row=array();
            $row[0]=array('会员ID','会员名称','注册时间','投资时间','标的名称','投资金额','投资期限');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['uid'] = $v['id'];
                $row[$i]['pf_user_name'] = $v['user_name'];
                $row[$i]['reg_time'] = date("Y-m-d H:i:s", $v['reg_time']);
                $row[$i]['tou_time'] = $v['tou_time'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, "xxx");
            $xls->addArray($row);
            $code = $search['code'];
            $xls->generateXML($code.date("YmdHis", time()));
            exit;
        } else {
            import("ORG.Util.PageFilter");
            $p = new PageFilter(count($list), $search, C('ADMIN_PAGE_SIZE'));
            $page = $p->show();
            $limit = "{$p->firstRow},{$p->listRows}";
            $sql .= "limit $limit";
            $list = M()->query($sql);
        }

        $this->assign('cpslist', $cpslist);
        $this->assign('search', $search);
        $this->assign("pagebar", $page);
        $this->assign("info", $list);
        $this->display();
    }


    /**
     * 普通标返佣统计
     * @return [type] 页面
     */
    public function ordinarycommission()
    {
        $offline_uid = C('OFFLINE_UID');
        static $fan_fee = 0.012;
        if (!empty($_GET['tui_uid'])) {
            $mwhere['mmi.real_name'] = trim($_GET['tui_uid']);
            $search['tui_uid'] = trim($_GET['tui_uid']);
        } else {
            $where['m.recommend_id'] = array('in',$offline_uid);
            $where['m.id'] = array('in',$offline_uid);
            $where['_logic'] = 'OR';
        }
        if (!empty($_GET['tou_uid'])) {
            $mwhere["bi.investor_uid"] = intval($_GET["tou_uid"]);
            $search['tou_uid'] = intval($_GET['tou_uid']);
        }
        if (!empty($_GET['tou_name'])) {
            $mwhere["mi.real_name"] = trim($_GET["tou_name"]);
            $search['tou_name'] = trim($_GET['tou_name']);
        }

        if (!empty($_GET['b_type'])) {
            if (!empty($_GET['bid'])) {
                $borrow_id = intval($_GET["bid"]);
                $search['bid'] = intval($_GET['bid']);
            }
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 7) {
                $mwhere["db.id"] = $borrow_id;
            }
            $search['b_type'] = intval($_GET['b_type']);
        }
        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $mwhere['_string'] = '(b.second_verify_time >= '.strtotime($_GET['start_time']." 00:00:00") .' OR db.second_verify_time >= '.strtotime($_GET['start_time']." 00:00:00").')';
            $search['start_time'] = $_GET['start_time'];
        } elseif (!empty($_GET['start_time']) && !empty($_GET['end_time'])) {
            $mwhere['_string'] = '(b.second_verify_time BETWEEN '.strtotime($_GET['start_time']." 00:00:00") .' AND '.strtotime($_GET["end_time"]." 23:59:59").'  OR db.second_verify_time BETWEEN '.strtotime($_GET['start_time']." 00:00:00") .' AND '.strtotime($_GET["end_time"]." 23:59:59").')';
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }

        if (!empty($where)) {
            $mwhere['_complex'] = $where;
        }
        $mwhere['b.borrow_status'] = array('in','6,7');
        $field = 'mmi.real_name as off_name,m.id as uid,mi.real_name as tou_name,bi.borrow_id,bi.debt_id,b.borrow_name,db.borrow_name as debt_name,b.second_verify_time,db.second_verify_time as db_verify_time, bi.investor_capital,b.borrow_duration_txt,b.borrow_duration,db.borrow_duration_txt as debt_duration, db.borrow_duration_txt as debt_duratione_txt,b.repayment_type';
        $list = M("borrow_investor bi")
                ->join('lzh_members m ON bi.investor_uid = m.id')
                ->join('lzh_member_info mi ON mi.uid = m.id')
                ->join('lzh_member_info mmi ON mmi.uid = m.recommend_id')
                ->join('lzh_borrow_info b ON b.id = bi.borrow_id')
                ->join('lzh_debt_borrow_info db ON db.id = bi.debt_id')
                ->where($mwhere)
                ->field($field)
                ->order('b.second_verify_time DESC,db.second_verify_time DESC')
                ->select();
        $total_capital = 0;
        $total_return = 0;
        foreach ($list as $key => $value) {
            if ($value['debt_id']>0) {
                $list[$key]['borrow_id'] = 'ZQ'.$value['debt_id'];
                $list[$key]['borrow_name'] = $value['debt_name'];
                $list[$key]['second_verify_time'] = date('Y-m-d H:i:s', $value['db_verify_time']);
                $list[$key]['borrow_duration_txt'] = $value['debt_duratione_txt'];
                $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($value['debt_duration']/360), 2);
            } else {
                $list[$key]['borrow_id'] = borrowidlayout1($value['borrow_id']);
                $list[$key]['second_verify_time'] = date('Y-m-d H:i:s', $value['second_verify_time']);
                if ($value['repayment_type'] == 1) {
                    $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($value['borrow_duration']/360), 2);
                } else {
                    $day = 30*$value['borrow_duration'];
                    $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($day/360), 2);
                }
            }
            $total_capital += $value['investor_capital'];
            $total_return += $list[$key]['return_money'];
        }
        if ($_REQUEST['execl'] == 'execl') {
            import("ORG.Io.Excel");
            alogs("borrow_return", 0, 1, '执行了原始标返佣统计！');//管理员操作日志
            $row=array();
            $row[0]=array('推荐人','投资人ID','投资人','标号','项目名称','复审时间','标的期限','投资金额','返佣比例（年化）','返利金额');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['off_name'] = $v['off_name'];
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['tou_name'] = $v['tou_name'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['return_fee'] = '1.2%';
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }
            $row[$i]['off_name'] = '合计';
            $row[$i]['uid'] = '';
            $row[$i]['tou_name'] = '';
            $row[$i]['borrow_id'] = '';
            $row[$i]['borrow_name'] = '';
            $row[$i]['second_verify_time'] = '';
            $row[$i]['borrow_duration_txt'] = '';
            $row[$i]['investor_capital'] = $total_capital;
            $row[$i]['return_fee'] = '';
            $row[$i]['return_money'] = $total_return;
            $xls = new Excel_XML('UTF-8', false, 'borrow_return');
            $xls->addArray($row);
            $xls->generateXML("borrow_return".date("YmdHis", time()));
            exit;
        }
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('total_capital', $total_capital);
        $this->assign('total_return', $total_return);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 债转标返佣统计
     * @return [type] 页面
     */
    public function zhaiquancommission()
    {
        $offline_uid = C('OFFLINE_UID');
        static $fan_fee = 0.012;
        if (!empty($_GET['tui_uid'])) {
            $mwhere['mmi.real_name'] = trim($_GET['tui_uid']);
            $search['tui_uid'] = intval($_GET['tui_uid']);
        } else {
            $where['m.recommend_id'] = array('in',$offline_uid);
            $where['m.id'] = array('in',$offline_uid);
            $where['_logic'] = 'OR';
        }
        if (!empty($_GET['tou_uid'])) {
            $mwhere["db.borrow_uid"] = intval($_GET["tou_uid"]);
            $search['tou_uid'] = intval($_GET['tou_uid']);
        }
        if (!empty($_GET['tou_name'])) {
            $mwhere["mi.real_name"] = trim($_GET["tou_name"]);
            $search['tou_name'] = trim($_GET['tou_name']);
        }
        if (!empty($_GET['bid'])) {
            $mwhere["db.id"] = intval($_GET["bid"]);
            $search['bid'] = intval($_GET['bid']);
        }
        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $mwhere['db.second_verify_time'] = array('egt', strtotime($_GET['start_time']." 00:00:00"));
            $search['start_time'] = $_GET['start_time'];
        } elseif (!empty($_GET['start_time']) && !empty($_GET['end_time'])) {
            $mwhere['db.second_verify_time'] = array('between',strtotime($_GET['start_time']." 00:00:00").','.strtotime($_GET["end_time"]." 23:59:59"));
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }
        if (!empty($where)) {
            $mwhere['_complex'] = $where;
        }
        $mwhere['db.borrow_status'] =  array('in','6,7' );
        $field = 'mmi.real_name as off_name,m.id as uid,mi.real_name as tou_name,db.id as debt_id,db.old_borrow_id as borrow_id,b.borrow_name,db.borrow_name as debt_name,b.second_verify_time,db.second_verify_time as db_verify_time,b.borrow_duration_txt,b.borrow_duration,db.borrow_duration_txt as debt_duration, db.borrow_duration_txt as debt_duratione_txt,b.repayment_type,db.borrow_money,db.add_time as debt_time,db.debt_captial';
        $list = M("debt_borrow_info db")
              ->join('lzh_members m ON db.borrow_uid = m.id')
              ->join('lzh_member_info mi ON mi.uid = m.id')
              ->join('lzh_member_info mmi ON mmi.uid = m.recommend_id')
              ->join('lzh_borrow_info b ON b.id = db.old_borrow_id')
              ->where($mwhere)
              ->field($field)
              ->order('db.second_verify_time desc')
              ->select();
            //   print_r(M("debt_borrow_info db")->getLastsql());
      $total_capital = 0;
        $total_return = 0;
        foreach ($list as $key => $value) {
            if ($value['repayment_type'] > 1) {
                $month_day = $value['borrow_duration']*30;
                $zhuan_day = intval((strtotime(date('Y-m-d 23:59:59', $value['debt_time'])) - $value['second_verify_time'])/(3600*24));
                $days = $month_day - $zhuan_day ;
                $remaining_day = $days;
                $list[$key]['return_money'] = getFloatValue($value['debt_captial']*$fan_fee*($days/360), 2);
            } else {
                $days = intval($value['debt_duration']);
                $remaining_day = $days;
                $list[$key]['return_money'] = getFloatValue($value['debt_captial']*$fan_fee*($days/360), 2);
            }
            $list[$key]['remaining_day'] = $remaining_day.'天';
            $list[$key]['borrow_id'] = 'ZQ'.$value['debt_id'];
            $list[$key]['borrow_name'] = $value['debt_name'];
            $list[$key]['db_verify_time'] = date('Y-m-d H:i:s', $value['db_verify_time']);
            $list[$key]['second_verify_time'] = date('Y-m-d H:i:s', $value['second_verify_time']);
            $total_capital += $value['debt_captial'];
            $total_return += $list[$key]['return_money'];
        }
        if ($_REQUEST['execl'] == 'execl') {
            import("ORG.Io.Excel");
            alogs("debt_return", 0, 1, '执行了债转返佣统计！');//管理员操作日志
          $row=array();
            $row[0]=array('推荐人','投资人ID','投资人','标号','项目名称','原标复审时间','债权复审时间','原标期限','债权剩余期限','转让金额','返佣比例（年化）','扣项-返利金额');
            $i=1;
            foreach ($list as $key=>$v) {
                $row[$i]['off_name'] = $v['off_name'];
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['tou_name'] = $v['tou_name'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['db_verify_time'] = $v['db_verify_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['debt_duratione_txt'] = $v['remaining_day'];
                $row[$i]['borrow_money'] = $v['debt_captial'];
                $row[$i]['return_fee'] = '1.2%';
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }
            $row[$i]['off_name'] = '合计';
            $row[$i]['uid'] = '';
            $row[$i]['tou_name'] = '';
            $row[$i]['borrow_id'] = '';
            $row[$i]['borrow_name'] = '';
            $row[$i]['second_verify_time'] = '';
            $row[$i]['db_verify_time'] = '';
            $row[$i]['borrow_duration_txt'] = '';
            $row[$i]['debt_duratione_txt'] = '';
            $row[$i]['borrow_money'] = $total_capital;
            $row[$i]['return_fee'] = '';
            $row[$i]['return_money'] = $total_return;
            $xls = new Excel_XML('UTF-8', false, 'debt_return');
            $xls->addArray($row);
            $xls->generateXML("debt_return".date("YmdHis", time()));
            exit;
        }
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('total_capital', $total_capital);
        $this->assign('total_return', $total_return);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 提前还款
     * @return [type] 页面
     */
    public function repaymentinadvance()
    {
        $offline_uid = C('OFFLINE_UID');
        static $fan_fee = 0.012;

        if (!empty($_GET['tui_uid'])) {
                $mwhere['mmi.real_name'] = trim($_GET['tui_uid']);
                $search['tui_uid'] = trim($_GET['tui_uid']);    
        } else {
                $where['m.recommend_id'] = array('in',$offline_uid);
                $where['m.id'] = array('in',$offline_uid);
                $where['_logic'] = 'OR';
        }

        if (!empty($_GET['tou_uid'])) {
            $mwhere["bi.investor_uid"] = intval($_GET["tou_uid"]);
            $search['tou_uid'] = intval($_GET['tou_uid']);
        }
        if (!empty($_GET['tou_name'])) {
            $mwhere["mi.real_name"] = trim($_GET["tou_name"]);
            $search['tou_name'] = trim($_GET['tou_name']);
        }


        if (!empty($_GET['b_type'])) {
            if (!empty($_GET['bid'])) {
                $borrow_id = intval($_GET["bid"]);
                $search['bid'] = intval($_GET['bid']);
            }
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->find();
                $mwhere["b.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 7) {
                $mwhere["db.id"] = $borrow_id;
            }
            $search['b_type'] = intval($_GET['b_type']);
        }
        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $mwhere['_string'] = '(b.second_verify_time >= '.strtotime($_GET['start_time']." 00:00:00") .' OR db.second_verify_time >= '.strtotime($_GET['start_time']." 00:00:00").')';
            $search['start_time'] = $_GET['start_time'];
        } elseif (!empty($_GET['start_time']) && !empty($_GET['end_time'])) {
            $mwhere['_string'] = '(b.second_verify_time BETWEEN '.strtotime($_GET['start_time']." 00:00:00") .' AND '.strtotime($_GET["end_time"]." 23:59:59").'  OR db.second_verify_time BETWEEN '.strtotime($_GET['start_time']." 00:00:00") .' AND '.strtotime($_GET["end_time"]." 23:59:59").')';
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }

        //还款起始时间和终止时间
        if (!empty($_GET['repayment_start_time']) && empty($_GET['repayment_end_time'])) {
            //$mwhere['_string'] = '(d.repayment_time >= '.strtotime($_GET['repayment_start_time']." 00:00:00");
            $mwhere['_string'] = 'd.repayment_time > '.strtotime($_GET['repayment_start_time']."00:00:00");
            $search['repayment_start_time'] = $_GET['repayment_start_time'];
        } elseif (!empty($_GET['repayment_start_time']) && !empty($_GET['repayment_end_time'])) {
            $mwhere['_string'] = 'd.repayment_time BETWEEN '.strtotime($_GET['repayment_start_time']." 00:00:00") .' AND '.strtotime($_GET["repayment_end_time"]." 23:59:59");
            $search['repayment_start_time'] = $_GET['repayment_start_time'];
            $search['repayment_end_time'] = $_GET['repayment_end_time'];
        }


        if (!empty($where)) {
            $mwhere['_complex'] = $where;
        }
        $mwhere['b.has_pay'] =  array('gt',0 );
        $mwhere['d.is_debt'] =  array('eq',0 );
        $mwhere['d.status'] =  array('neq',-1 );
        $field = 'mmi.real_name as off_name,m.id as uid,mi.real_name as tou_name,bi.borrow_id,bi.debt_id,b.borrow_name,db.borrow_name as debt_name,b.second_verify_time,db.second_verify_time as db_verify_time, bi.investor_capital,b.borrow_duration_txt,b.borrow_duration,db.borrow_duration_txt as debt_duration, db.borrow_duration_txt as debt_duratione_txt,b.repayment_type,bi.deadline,d.repayment_time,d.sort_order,d.deadline as i_dead_line';
        $list = M("borrow_investor bi")
                ->join('lzh_members m ON bi.investor_uid = m.id')
                ->join('lzh_member_info mi ON mi.uid = m.id')
                ->join('lzh_member_info mmi ON mmi.uid = m.recommend_id')
                ->join('lzh_borrow_info b ON b.id = bi.borrow_id')
                ->join('lzh_investor_detail d ON d.invest_id = bi.id AND d.sort_order = b.has_pay')
                ->join('lzh_debt_borrow_info db ON db.id = bi.debt_id')
                ->where($mwhere)
                ->field($field)
                ->order('b.second_verify_time desc')
                // ->limit(10)
                ->select();
        $total_capital = 0;
        $total_return = 0;
        $days = 0;
        $new_list = array();
        $i = 0;
        foreach ($list as $key => $value) {
            if ($value['repayment_type'] > 1) {
                $list[$key]['repayment_time'] = date('Y-m-d H:i:s', $value['repayment_time'])."（第".$value["sort_order"]."期）";
                $days = intval(($value['i_dead_line'] - strtotime(date('Y-m-d 23:59:59', $value['repayment_time'])))/(3600*24));
                $list[$key]['in_advance'] = "（第".$value["sort_order"]."期）提前".$days.'天';
                $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($day/360), 2);
            } else {
                $list[$key]['repayment_time'] = date('Y-m-d H:i:s', $value['repayment_time']);
                $days = intval(($value['deadline'] - strtotime(date('Y-m-d 23:59:59', $value['repayment_time'])))/(3600*24));
                $list[$key]['in_advance'] = $days.'天';
                $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($days/360), 2);
            }
            if ($value['debt_id']>0) {
                $list[$key]['borrow_id'] = 'ZQ'.$value['debt_id'];
                $list[$key]['borrow_name'] = $value['debt_name'];
                $list[$key]['second_verify_time'] = date('Y-m-d H:i:s', $value['db_verify_time']);
                $list[$key]['borrow_duration_txt'] = $value['debt_duratione_txt'];
                $list[$key]['return_money'] = getFloatValue($value['investor_capital']*$fan_fee*($value['debt_duration']/360), 2);
            } else {
                $list[$key]['borrow_id'] = borrowidlayout1($value['borrow_id']);
                $list[$key]['second_verify_time'] = date('Y-m-d H:i:s', $value['second_verify_time']);
            }
            $list[$key]['deadline'] = date('Y-m-d H:i:s', $value['deadline']);
            if ($value['repayment_type'] == 1) {
                if ($days > 0) {
                    $new_list[$i] = $list[$key];
                    $total_capital += $list[$key]['investor_capital'];
                    $i ++;
                }
            } else {
                $new_list[$i] = $list[$key];
                $total_capital += $list[$key]['investor_capital'];
                $i ++;
            }
            // print_r($list[$key]);die;
            $total_return += $list[$key]['return_money'];
        }
        if ($_REQUEST['execl'] == 'execl') {
            import("ORG.Io.Excel");
            alogs("advance_return", 0, 1, '执行了提前还款统计！');//管理员操作日志
            $row=array();
            $row[0]=array('推荐人','投资人ID','投资人','标号','项目名称','复审时间','计划还款时间','实际还款时间','原标期限','提前还款天数','投资金额','返佣比例（年化）','返利金额');
            $i=1;
            foreach ($new_list as $key=>$v) {
                $row[$i]['off_name'] = $v['off_name'];
                $row[$i]['uid'] = $v['uid'];
                $row[$i]['tou_name'] = $v['tou_name'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['deadline'] = $v['deadline'];
                $row[$i]['repayment_time'] = $v['repayment_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['in_advance'] = $v['in_advance'];
                $row[$i]['investor_capital'] = $v['investor_capital'];
                $row[$i]['return_fee'] = '1.2%';
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }
            $row[$i]['off_name'] = '合计';
            $row[$i]['uid'] = '';
            $row[$i]['tou_name'] = '';
            $row[$i]['borrow_id'] = '';
            $row[$i]['borrow_name'] = '';
            $row[$i]['second_verify_time'] = '';
            $row[$i]['deadline'] = '';
            $row[$i]['repayment_time'] = '';
            $row[$i]['borrow_duration_txt'] = '';
            $row[$i]['in_advance'] = '';
            $row[$i]['investor_capital'] = $total_capital;
            $row[$i]['return_fee'] = '';
            $row[$i]['return_money'] = $total_return;
            $xls = new Excel_XML('UTF-8', false, 'advance_return');
            $xls->addArray($row);
            $xls->generateXML("advance_return".date("YmdHis", time()));
            exit;
        }

        $this->assign('list', $new_list);
        $this->assign('search', $search);
        $this->assign('total_capital', $total_capital);
        $this->assign('total_return', $total_return);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 推荐投资人投资列表
     * @return [type] 页面
     */
    public function recommentinvestlist()
    {
        $offline_uid = C('OFFLINE_UID');
        list($map, $search) = $this->getSearchMap();
        if (isset($map['mi2.real_name'])) {
            //如果选择了推荐人,精确查询
            $map3 = $map;
        } else {
            //如果没有选择推荐人
            $map2['m.id'] = array('in', $offline_uid);
            $map2['m.recommend_id'] = array('in', $offline_uid);
            $map2['_logic'] = "or";
            $map3['_complex'] = $map2;
            //只查询普通标，不包含债权转让标
            $map3['bi.debt_id'] = 0;
            if (isset($map['m.user_name'])) {
                $map3['mi.real_name'] = $map['m.user_name'];
            }

            if (isset($map['m.user_phone'])) {
                $map3['m.user_phone'] = $map['m.user_phone'];
            }

            if (isset($map['m.id'])) {
                $map3['m.id'] = $map['m.id'];
            }
            if (isset($map['bi.borrow_id'])) {
                $map3['bi.borrow_id'] = $map['bi.borrow_id'];
            }
        }

        $count = M('members m')->field('m.*,bi.*')
                              ->join("lzh_members_status s ON m.id=s.uid")
                              ->join("lzh_member_info mi on m.id=mi.uid")
                              ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                              ->join("lzh_borrow_investor bi ON bi.investor_uid = s.uid")
                              ->join("lzh_borrow_info binfo on binfo.id= bi.borrow_id")

                              ->where($map3)
                              ->order("bi.add_time DESC")
                              ->limit($limit)
                              ->count("*");

        import("ORG.Util.PageFilter");
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        $list = M('members m')->field('m.id,mi.real_name,m.user_name,m.user_phone,m.recommend_id,bi.investor_uid,bi.borrow_id,bi.investor_capital,binfo.borrow_name,binfo.borrow_duration,binfo.borrow_duration_txt,binfo.borrow_money,binfo.borrow_type,mi2.real_name as real_name2')
                              ->join("lzh_members_status s ON m.id=s.uid")
                              ->join("lzh_member_info mi on m.id=mi.uid")
                              ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                              ->join("lzh_borrow_investor bi ON bi.investor_uid = s.uid")
                              ->join("lzh_borrow_info binfo on binfo.id= bi.borrow_id")
                              ->where($map3)
                              ->order("bi.add_time DESC")
                              ->limit($limit)
                              ->select();

        //检查是否需要生成xls
        $arrRow = array("ID", "姓名", "手机号", "标号", "标的名称", "标的期限", "投资金额", "推荐人");
        $arrCol = array("id", "real_name", "user_phone", "borrow_id","borrow_name", "borrow_duration_txt", "investor_capital", "real_name2");
        $this->isXLSRequired(__FUNCTION__, $arrRow, $arrCol, $list);

        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', __FUNCTION__);
        $this->assign('list', $list);
        $this->assign('search', $search);

        $this->display();
    }

    /**
     * 推荐投资人回款统计
     * @return [type] 页面
     */
    public function recommentpaymentlist()
    {
        $offline_uid = C('OFFLINE_UID');
        list($map, $search) = $this->getSearchMap();
        import("ORG.Util.PageFilter");

        //已回收状态
        $map['bi.borrow_status'] = 7;
        if (isset($map['mi2.real_name'])) {
            //如果选择了推荐人,精确查询
            $map3 = $map;
        } else {
            //如果没有选择推荐人
            $map2['m.id'] = array('in', $offline_uid);
            $map2['m.recommend_id'] = array('in', $offline_uid);
            $map2['_logic'] = "or";
            $map3['_complex'] = $map2;
            if (isset($map['m.user_name'])) {
                $map3['mi.real_name'] = $map['m.user_name'];
            }

            if (isset($map['m.user_phone'])) {
                $map3['m.user_phone'] = $map['m.user_phone'];
            }

            if (isset($map['m.id'])) {
                $map3['m.id'] = $map['m.id'];
            }
            if (isset($map['bi.borrow_id'])) {
                $map3['binv.borrow_id'] = $map['bi.borrow_id'];
            }
            if (isset($map['bi.borrow_status'])) {
                $map3['bi.borrow_status'] = 7;
            }

            //搜索标号
        }


        $count = M('members m')->field('m.*,bi.*')
                              ->join("lzh_borrow_investor binv on binv.investor_uid = m.id")
                              ->join("lzh_member_info mi on m.id=mi.uid")
                              ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                              ->join("lzh_borrow_info bi ON bi.id = binv.borrow_id")
                               ->where($map3)
                               ->order("bi.add_time DESC")
                               ->limit($limit)
                               ->count("bi.id");

        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        $list = M('members m')->field('mi.real_name,m.id as xid,m.recommend_id as xrecommend_id,m.*,bi.id as borrow_id,bi.*,binv.investor_capital,binv.add_time,mi2.real_name as real_name2')
                              ->join("lzh_borrow_investor binv on binv.investor_uid = m.id")
                              ->join("lzh_member_info mi on m.id=mi.uid")
                              ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                              ->join("lzh_borrow_info bi ON bi.id = binv.borrow_id")
                              ->where($map3)
                              ->order("binv.add_time DESC")
                              ->limit($limit)
                              ->select();

        //检查是否需要生成xls
        $arrRow = array( "ID","姓名", "手机号", "标号", "标的名称", "标的期限", "投资金额", "推荐人");
        $arrCol = array( "xid","real_name", "user_phone", "borrow_id","borrow_name", "borrow_duration_txt", "investor_capital", "real_name2");
        $this->isXLSRequired(__FUNCTION__, $arrRow, $arrCol, $list);

        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', __FUNCTION__);
        $this->assign('list', $list);
        $this->assign('search', $search);

        $this->display();
    }

    /**
     * 未实名的投资人列表
     * @return [type] 页面
     */
    public function norealnameverify()
    {
        $offline_uid = C('OFFLINE_UID');
        list($map, $search) = $this->getSearchMap();

        //未实名
        $map['s.id_status'] = 0;
        if (isset($map['mi2.real_name'])) {
            //如果选择了推荐人,精确查询
            $map3 = $map;
        } else {
            //如果没有选择推荐人
            $map2['m.id'] = array('in', $offline_uid);
            $map2['m.recommend_id'] = array('in', $offline_uid);
            $map2['_logic'] = "or";
            $map3['_complex'] = $map2;
            $map3['s.id_status'] = 0;
            if (isset($map['m.user_name'])) {
                $map3['mi.real_name'] = $map['m.user_name'];
            }

            if (isset($map['m.user_phone'])) {
                $map3['m.user_phone'] = $map['m.user_phone'];
            }

            if (isset($map['m.id'])) {
                $map3['m.id'] = $map['m.id'];
            }

            //搜索标号
        }

        import("ORG.Util.PageFilter");
        $count = M('members_status s')->field('m.*,s.id_status')
                                     ->join("lzh_members m ON m.id=s.uid")
                                     ->join("lzh_member_info mi ON mi.uid=s.uid")
                                     ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                                      ->where($map3)
                                      ->count('s.uid');

        //分页
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        $list = M('members_status s')->field('s.uid,s.id_status,m.user_name,m.user_phone,m.recommend_id,m.id,mi.real_name,mi2.real_name as real_name2')
                                     ->join("lzh_members m ON m.id=s.uid")
                                     ->join("lzh_member_info mi ON mi.uid=s.uid")
                                     ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                                     ->where($map3)
                                     ->order("mi.up_time DESC")
                                     ->limit($limit)
                                     ->select();

        //检查是否需要生成xls
        $arrRow = array("ID", "姓名", "手机号", "推荐人");
        $arrCol = array("id", "user_name", "user_phone", "real_name2");
        $this->isXLSRequired(__FUNCTION__, $arrRow, $arrCol, $list);

        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', __FUNCTION__);
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->display();
    }

    /**
     * 未投资人明细
     * @return [type] 页面
     */
    public function noinvestlist()
    {
        $offline_uid = C('OFFLINE_UID');
        $ids_lastmonth = $this->investedLastMonth();

        //解析参数
        list($map, $search) = $this->getSearchMap();

        $map['m.id'] = array('NOT IN', $ids_lastmonth);
        if (isset($map['mi2.real_name'])) {
            //如果选择了推荐人,精确查询
            $map3 = $map;
        } else {
            //如果没有选择推荐人
            $map2['m.id'] = array('in', $offline_uid);
            $map2['m.recommend_id'] = array('in', $offline_uid);
            $map2['_logic'] = "or";
            $map3['_complex'] = $map2;
            $map3['m.id'] = array('NOT IN', $ids_lastmonth);
            if (isset($map['m.user_name'])) {
                $map3['mi.real_name'] = $map['m.user_name'];
            }

            if (isset($map['m.user_phone'])) {
                $map3['m.user_phone'] = $map['m.user_phone'];
            }

            if (isset($map['m.id'])) {
                $map3['m.id'] = $map['m.id'];
            }

            //搜索标号
        }



        import("ORG.Util.PageFilter");
        $count = M('members_status s')->join("lzh_members m ON m.id=s.uid")
                                     ->join("lzh_member_info mi ON mi.uid=s.uid")
                                     ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")

                                      ->where($map3)
                                      ->count('s.uid');

        //分页
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        $list = M('members_status s')->field('s.uid,s.id_status,m.user_name,m.user_phone,m.recommend_id,mi.real_name,mi2.real_name as real_name2')
                                     ->join("lzh_members m ON m.id=s.uid")
                                     ->join("lzh_member_info mi ON mi.uid=s.uid")
                                     ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                                     ->where($map3)
                                     ->order("mi.up_time DESC")
                                     ->limit($limit)
                                     ->select();

        //检查是否需要生成xls
        $arrRow = array("ID", "姓名", "手机号", "推荐人");
        $arrCol = array("uid", "real_name", "user_phone", "real_name2");
        $this->isXLSRequired(__FUNCTION__, $arrRow, $arrCol, $list);

        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', __FUNCTION__);
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->display();
    }

    /**
     * 站岗资金明细
     * @return [type] 页面
     */
    public function stillmoney()
    {
        $offline_uid = C('OFFLINE_UID');
        list($map, $search) = $this->getSearchMap();

        if (isset($map['mi2.real_name'])) {
            //如果选择了推荐人,精确查询
            $map3 = $map;
        } else {
            //如果没有选择推荐人
            $map2['m.id'] = array('in', $offline_uid);
            $map2['m.recommend_id'] = array('in', $offline_uid);
            $map2['_logic'] = "or";
            $map3['_complex'] = $map2;
            if (isset($map['m.user_name'])) {
                $map3['mi.real_name'] = $map['m.user_name'];
            }

            if (isset($map['m.user_phone'])) {
                $map3['m.user_phone'] = $map['m.user_phone'];
            }

            if (isset($map['m.id'])) {
                $map3['m.id'] = $map['m.id'];
            }
            if (isset($map['o.time'])) {
                $map3['o.time'] = $map['o.time'];

                //$map3['o.time'] = array('between',array($map['o.time'],$map['o.time']+86400));
            }
        }




        import("ORG.Util.PageFilter");
        $count = M('member_piggybank o')->join('lzh_members m on o.uid = m.id')
                                       ->join("lzh_member_info mi ON mi.uid=m.id")
                                       ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                                        ->where($map3)
                                        ->count('o.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }
        $info = M('member_piggybank o')->field('o.available_balance,o.total_balance,o.time,m.id,m.recommend_id,m.user_name,m.user_phone,mi.real_name,mi2.real_name as real_name2')
                                       ->join('lzh_members m on o.uid = m.id')
                                       ->join("lzh_member_info mi ON mi.uid=m.id")
                                       ->join("lzh_member_info mi2 on m.recommend_id=mi2.uid")
                                       ->where($map3)
                                       ->limit($limit)
                                       ->select();

         //检查是否需要生成xls
        $arrRow = array("ID", "姓名", "手机号", "可用账户余额", "账户总金额","推荐人");
        $arrCol = array("id", "real_name", "user_phone", "available_balance", "total_balance","real_name2");
        $this->isXLSRequired(__FUNCTION__, $arrRow, $arrCol, $info);

        $this->assign('pagebar', $page);
        $search['execl']="execl";
        $this->assign("query", http_build_query($search));
        $this->assign('xaction', __FUNCTION__);
        $this->assign('list', $info);
        $this->assign('search', $search);
        $this->assign('time', $info[0]['time']);
        $this->display();
    }

    /**
     * chase your dreams
     * @return [type] [description]
     */
    public function dream()
    {
        S('global_setting', null);
        $glo = get_global_setting();
        $start_time = date("Y-m-d", $glo['dream_start_time']);
        $end_time = date("Y-m-d", $glo['dream_end_time']);
        $dream_valid = $glo['dream_status'];
        $dream_is_over = $glo['dream_is_over'];

        if ($this->isPost()) {
            $start = strtotime($_POST['start_time']);
            $end = strtotime($_POST['end_time']);

            $savestarttime['text'] = $start +32400;
            $saveendtime['text']   = $end+86399;

            if ($start > $end) {
                $this->error("end time has to be bigger than start time", __URL__."/dream");
            }

            $savestatus['text']    = $_POST['dream_valid'];
            $res1 = M('global')->where(array('code'=>'dream_start_time'))->save($savestarttime);

            // if(($end+86399)<time()){
            //     $clear['dream_feeds'] = 0;
            //     $clear['dream_invest_total'] = 0;
            //     $clear['dream_invested'] = 0;
            //     $res = M('members')->where(1)->save($clear);
            //     if($res){
            //         $tmp['create_time'] = time();
            //         $tmp['type'] = 0;
            //         $tmp['desc'] = "activity over ,clear all dream feed ,dream invest feeds";
            //         M('dream_log')->add($tmp);
            //     }
            // }
            $res2 = M('global')->where(array('code'=>'dream_end_time'))->save($saveendtime);

            $res3 = M('global')->where(array('code'=>'dream_status'))->save($savestatus);

            S('global_setting', null);
            checkDreamStatus();
            S('global_setting', null);

            if (($res1!==false)&&($res2!==false)&&($res3!==false)) {
                $this->success("保存成功", __URL__."/dream");
            } else {
                $this->error("保存失败", __URL__."/dream");
            }
        }
        $this->assign('dream_valid', $dream_valid);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('dream_is_over', $dream_is_over);
        $this->assign('stime', $glo['dream_start_time']);
        $this->assign('etime', $glo['dream_end_time']);
        $this->display();
    }

    /**
     *
     * @return [type] [description]
     */
    public function revealAll(){
        $con['status'] = 0;
        $list = M('dream_prizehistory')->where($con)->select();

        if($this->isPost()){
            S('global_setting', null);
            $glo = get_global_setting();
            $targetmobile = $glo['dream_mobile'];
            $target = M('members')->where(array('user_phone'=>$targetmobile))->find();
            if($target === false){
                $tmp['code'] = 0;
                $tmp['msg'] = "未找到手机号 {$targetmobile} 的用户";
                echo json_encode($tmp);
                die;
            }
            $tarid = $target['id'];

            //type = 1
            //id = 980

            //开启事务
            //遍历商品，如果未满，补齐,记入日志
            //开奖
            $model = new Model();
            try {
                $model->startTrans();
                $condition['id'] = $_POST['id'];
                $condition['prize_type'] = $_POST['type'];
                $condition['status'] = 0;
                $prizeInfo = M('dream_prizehistory')->where($condition)->find();
                if($prizeInfo === false){
                    throw new Exception("没有找到 id = {$_POST['id']} type = {$_POST['type']} 的未开奖商品", 1);

                }

                //未满，满标
                //$prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds']
                if($prizeInfo['feeds_left'] > 0){
                    $left = $prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds'];
                    $this->invest($_POST['id'], $left, $tarid, $targetmobile);
                }

                //开奖
                libRevealWinner($_POST['id'], true, false, false);

                //写入日志
                $logdata['create_time'] = time();
                $logdata['type'] = 10;
                $logdata['desc'] = "terminate prize id = {$_POST['id']}, type = {$_POST['type']}, prize not full ,invest {$left} times , uid = {$tarid}, mobile = {$targetmobile}";
                M('dream_log')->add($logdata);

                $model->commit();
            } catch (Exception $e) {
                $model->rollback();
                $tmp['code'] = 0;
                $tmp['msg'] = $e->getMessage();
                echo json_encode($tmp);
                die;
            }

            //成功
            $tmp['code'] = 1;
            $tmp['msg'] = "function depricated !";
            echo json_encode($tmp);
            die;
        }
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 满标
     * @param  [type] $prizeHisId [description]
     * @return [type]             [description]
     */
    private function invest($prizeHisId, $amount, $uid, $mobile){
        $prizeInfo = M('dream_prizehistory')->find($prizeHisId);
        $feedAmount = $amount;
        $memInfo = M('members')->find($uid);
        if($memInfo === false){
            throw new Exception("没有找到内定用户 uid = {$uid}, mobile = {$mobile}　的信息", 1);

        }

        //reduce inventory && increase inves time
        $pi['invest_times'] = array('exp','invest_times+1');
        $pi['feeds_left'] = array('exp','feeds_left-'.$feedAmount*$prizeInfo['prize_min_feeds']);
        $res1 = M('dream_prizehistory')->where(array('id'=>$prizeInfo['id']))->save($pi);
        if($res1 === false){
            throw new Exception("更新奖品信息失败 !", 1);
        }

        if($prizeInfo === false){
            throw new Exception("没有找到奖品信息", 1);
        }
        $prizeMax = $prizeInfo['prize_total_feeds'];
        $prizeLeft = $prizeInfo['feeds_left'];

        if($prizeLeft/$prizeInfo['prize_min_feeds'] < $amount){
            throw new Exception("投资次数不能超过剩余次数", 1);
        }
        $prizeInvested = ($prizeMax - $prizeLeft)/$prizeInfo['prize_min_feeds'];

        $sql = "insert into lzh_dream_invest (`uid`,`mobile`,`money`,`prize_id`,`prize_name`,`prize_type`,`qishu`,`feeds_amount`,`create_time`,`feed_no`) values ";
        //gen feed no
        for($i = $prizeInvested + 1; $i < $prizeInvested + 1 + $feedAmount; $i++){
            $data['uid']          = $uid;
            $data['mobile']       = $memInfo['user_phone'];
            //total money
            if($prizeInfo['prize_type']<4){
                //追梦
                $data['money']        = 0;
            }else{
                //圆梦
                $data['money']        = $prizeInfo['prize_min_feeds']*100*$feedAmount;
            }

            $data['prize_id']     = $prizeInfo['id'];
            $data['prize_name']   = $prizeInfo['prize_name'];
            $data['prize_type']   = $prizeInfo['prize_type'];
            $data['qishu']        = $prizeInfo['qishu'];
            $data['feeds_amount'] = $feedAmount;
            $data['create_time']  = time();
            $data['feed_no']      = 10000000 + intval($i);


            if($i==($prizeInvested+$feedAmount)){
                $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."');";
            }elseif($i == $prizeInvested +1 ){
                $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
            }else{
                $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
            }
        }

        $Model = new Model();
        $resultx = $Model->execute($sql);
        if($resultx === false){
            throw new Exception("投资种子生成失败!", 1);
        }
    }


    public function indicator(){
        $con['status'] = 1;
        $con['feeds_left'] = array('elt',0);
        $list = M('dream_prizehistory')->where($con)->select();

        S('global_setting', null);
        $glo = get_global_setting();
        $start_time = date("Y-m-d",$glo['dream_start_time']);
        $end_time = date("Y-m-d",$glo['dream_end_time']);
        $dream_valid = $glo['dream_status'];
        $dream_is_over = $glo['dream_is_over'];

        foreach ($list as $key => $value) {
            $id = $value['id'];
            $min = M('dream_invest')->where(array('prize_id'=>$value['id']))->min('feed_no');
            $max = M('dream_invest')->where(array('prize_id'=>$value['id']))->max('feed_no');
            $total = M('dream_invest')->where(array('prize_id'=>$value['id']))->count();

            $total_feed = $value['prize_total_feeds']/$value['prize_min_feeds'];
            if ($total == $total_feed) {
                continue;
            }

            if (intval($min)!==false) {
                $tmp['min'] = $min;
            } else {
                $tmp['min'] = 0;
            }

            if (intval($max)!==false) {
                $tmp['max'] = $max;
            } else {
                $tmp['max'] = 0;
            }

            if (intval($total)!==false) {
                $tmp['total'] = $total;
            } else {
                $tmp['total'] = 0;
            }

            $tmp['total_feeds'] = $total_feed;

            $tmp['id']         = $value['id'];
            $tmp['prize_type'] = $value['prize_type'];
            $tmp['qishu']      = $value['qishu'];
            $tmp['prize_name'] = $value['prize_name'];
            $tmp['feeds_left'] = $value['feeds_left'];
            $xx[] = $tmp;
        }

        $prize_0 = M('dream_prize')->where(array('type'=>array('in', array('1','2','3'))))->select();
        foreach ($prize_0 as $key => $value) {
            if(time()-$glo['dream_start_time'] > 86400)
                continue;

            if($value['inventory'] == 5){
                unset($prize_0[$key]);
                continue;
            } else {
                $prize_0[$key]['right'] = 5;
                $tmp1[] = $prize_0[$key];
            }
        }


        //check if 30 30 12 6 21
        $con['type'] = array('in',array('4','5'));
        $prize_4 = M('dream_prize')->where($con)->select();
        foreach ($prize_4 as $key => $value) {
            if ($value['inventory'] == 1000) {
                unset($prize_4[$key]);
                continue;
            } else {
                $prize_4[$key]['right'] = 1000;
                $tmp1[] = $prize_4[$key];
            }
        }


        //check if 30 30 12 6 21
        $con2['type'] = array('in',array('６','７','8','9','10','11'));
        $prize_6 = M('dream_prize')->where($con2)->select();
        foreach ($prize_6 as $key => $value) {
            if ($value['inventory'] == 1000) {
                unset($prize_6[$key]);
                continue;
            } else {
                $prize_6[$key]['right'] = 1000;
                $tmp1[] = $prize_6[$key];
            }
        }

        $this->assign('notright', $tmp1);
        $this->assign('list', $xx);
        $this->display();
    }

    /**
     * set up dream prize
     * @return [type] [description]
     */
    public function dreamPrize()
    {
        $prizeLst = M('dream_prize')->where(1)
                                    ->order('type asc,id asc')
                                    ->select();
        if ($this->isPost()) {
            $type=$_POST['type'];
            $id = $_POST['id'];
            if ((intval($type)===false)||(intval($id)===false)) {
                $tmp['code'] = 0;
                $tmp['msg'] = 'type or id must be digit';
                echo json_encode($tmp);
                die;
            }

            $model = new Model();
            try {
                $model->startTrans();
                $res1 = M('dream_prize')->where(array('type'=>$type))->save(array('default'=>0));
                logw('last sql = '.M('dream_prize')->getLastsql());
                if (!$res1) {
                    throw new Exception("reset dream prize default value failed ", 1);
                }

                $res2 = M('dream_prize')->where(array('id'=>$id))->save(array('default'=>1,'create_time'=>time()));
                if (!$res2) {
                    throw new Exception("set default prize failed", 1);
                }
                $model->commit();
            } catch (Exception $e) {
                $model->rollback();
                $tmp['code'] = 0;
                $tmp['msg'] = $e->getMessage();
                echo json_encode($tmp);
                die;
            }

            $tmp['code'] = 1;
            $tmp['msg'] = "success";
            echo json_encode($tmp);
            die;
        }
        $this->assign('list', $prizeLst);
        $this->display();
    }



    /**
     * [dreamWinner description]
     * @return [type] [description]
     */
    public function dreamWinner()
    {
        //'mi.real_name,o.mobile,h.prize_name,h.prize_type,h.qishu,o.create_time,o.feed_no'
        $info = M('dream_true o')->field("mi.real_name,o.mobile,o.create_time,o.feed_no,h.prize_name,h.qishu,h.prize_type")
                                       ->join('lzh_members m on o.uid = m.id')
                                       ->join("lzh_member_info mi ON mi.uid=m.id")
                                       ->join("lzh_dream_prizehistory h  on h.id=o.prize_id")
                                       ->order('h.prize_type asc,h.qishu asc, o.id desc')
                                       ->where(1)
                                       ->select();
        // echo json_encode($info);
        // die;
        $this->assign('list', $info);
        $this->display();
    }

    /**
     * 接盘侠，活动尾声手动满标实用的账户
     * @return [type] [description]
     */
    public function dreamMobile(){
        $exist = M('global')->where(array('code'=>'dream_mobile'))->find();

        //自动添加
        if($exist === null){
            //如果没有这条记录，自动插入
            $newdata['type']     = 'input';
            $newdata['text']     = '13798298624';
            $newdata['name']     = '系统满标的手机号';
            $newdata['tip']      = '追梦活动结束，需要满标,这是满标手机号';
            $newdata['order_sn'] = '0';
            $newdata['code']     = 'dream_mobile';
            $newdata['is_sys']   = '0';
            M('global')->add($newdata);
            S('global_setting', null);
        }

        S('global_setting', null);
        $glo = get_global_setting();
        $targetmobile = $glo['dream_mobile'];

        if($this->isPost()){
            $addFeeds = intval($_POST['dreammobile']);

            if(!preg_match("/^1[0-9]{10}$/",$targetmobile)){
                $this->error("手机号码格式不正确!", __URL__."/dreammobile");
            }

            $info = M('members')->where(array('user_phone'=>$addFeeds))->find();
            if($info === false){
                $this->error("找不到手机号码为 {$targetmobile}　的用户信息!", __URL__."/dreammobile");
            }

            $res = M('global')->where(array('code'=>'dream_mobile'))->save(array('text'=>$addFeeds));

            if($res!==false){
                $res1 = M('dream_log')->add(array('create_time'=>time(),'desc' => " {$targetmobile} was the chosen one ",'type'=>20));
                S('global_setting', null);
                $this->success("保存成功", __URL__."/dreammobile");
                //log

            } else {
                $this->error("保存失败", __URL__."/dreammobile");
            }
        }

        $memInfo = M('members')->where(array('user_phone'=>$targetmobile))->find();
        $realName="没有找到内定用户实名信息";
        if($memInfo !== false){
            $nameInfo = M('member_info')->where(array('uid'=>$memInfo['id']))->find();
            $realName = $nameInfo['real_name'];
        }
        $this->assign('realname', $realName);
        $this->assign('dreammobile', $targetmobile);
        $this->assign('loglist', $loglist);
        $this->display();
    }
    /**
     * [dreamFeedsCharge description]
     * @return [type] [description]
     */
    public function dreamFeedsCharge()
    {
        if ($this->isPost()) {
            $addFeeds = intval($_POST['dream_feeds']);
            if ($add_feeds===false) {
                $this->error("dream feeds must be digit!", __URL__."/dreamfeedscharge");
            }
            $data['dream_feeds'] = array('exp','dream_feeds+'.$addFeeds);
            $res = M('members')->where(1)->save($data);
            if ($res!==false) {
                $res1 = M('dream_log')->add(array('create_time'=>time(),'desc' => "  {$addFeeds} dream feeds"));
                $this->success("保存成功", __URL__."/dreamfeedscharge");
                //log
            } else {
                $this->error("保存失败", __URL__."/dreamfeedscharge");
            }
        }
        //type 0 dream feeds give away to all
        //     2 invest
        //     3 regis
        //     5 crontab
        $con['type'] = array('in',array('0','2','3','5'));
        $loglist = M('dream_log')->where($con)->order('id desc')->select();
        $this->assign('loglist', $loglist);
        $this->display();
    }

    /**
     * [revealWinnerManually description]
     * @return [type] [description]
     */
    public function revealWinnerManually()
    {
        $trueid = M('dream_true')->field('prize_id')->where(1)->select();
        $arrTrueId = array_column($trueid, 'prize_id');

        $con['status'] = 0;
        $con['feeds_left'] = array('elt',0);
        $con['prize_id'] = array('not in', $arrTrueId);
        $list = M('dream_prizehistory')->where($con)->order('id desc')->select();

        if ($this->isPost()) {
            $this->revealTheWinner1($_POST['id']);
            $tmp['code'] = 1;
            $tmp['msg'] = "success";
            echo json_encode($tmp);
            die;
        }
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * reveal the winner
     * @return  [description]
     */
    public function revealTheWinner1($hisid)
    {
        $prizeHisId = $hisid;
        $winFeedNo = 100001;

        $model = new Model();
        try {
            $model->startTrans();

            $where['id'] = $prizeHisId;
            $where['status'] = 0;
            $where['feeds_left'] = array('elt', 0);
            $isFull = M('dream_prizehistory')->lock(true)->where($where)->find();
            if (!$isFull) {
                return true;
            }

            $prizeInfo = M('dream_prizehistory')->lock(true)->find($prizeHisId);
            if (!$prizeInfo) {
                throw new Exception("prize history not found !", 1);
            }

            $count = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time');
            $winFeedNo = $count % $prizeInfo['prize_total_feeds'];
            $winFeedNo = 100000 + rand(0, $prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds']);
            //find winner
            $wincon['prize_id'] = $prizeHisId;
            $wincon['feed_no']  = $winFeedNo;
            $winner = M('dream_invest')->where($wincon)->find();
            if (!$winner) {
                throw new Exception("no winner found !", 1);
            }

            //write winner
            $winnerinfo['prize_id']   = $prizeInfo['id'];
            $winnerinfo['prize_name'] = $prizeInfo['prize_name'];
            $winnerinfo['qishu']      = $prizeInfo['qishu'];
            $winnerinfo['uid']        = $winner['uid'];
            $winnerinfo['mobile']     = $winner['mobile'];
            $winnerinfo['money']      = $winner['money'];
            $winnerinfo['feed_no']    = $winFeedNo;
            if (!(M('dream_true')->add($winnerinfo))) {
                throw new Exception("insert record into dream true failed ", 1);
            }

            //update winner record
            $full['status'] = 1;
            $full['luck_no'] = $winFeedNo;
            if (!(M('dream_prizehistory')->where(array('id' => $prizeHisId))->save($full))) {
                throw new Exception("update prize history failed !", 1);
            }

            //check if another prize is accessiable
            $priType        = $prizeInfo['prize_type'];
            $curqishu       = $prizeInfo['qishu'];
            $oricon['type'] = $priType;
            $pri = M('dream_prize')->where($oricon)->find();
            $maxqishu = $pri['inventory'];
            if ($maxqishu > $curqishu) {
                //create a new record
                $newPrizeHistory['prize_id']          = $prizeInfo['prize_id'];
                $newPrizeHistory['prize_name']        = $pri['name'];
                $newPrizeHistory['prize_min_feeds']   = $pri['min_feeds'];
                $newPrizeHistory['prize_total_feeds'] = $pri['total_feeds'];
                $newPrizeHistory['prize_type']        = $pri['type'];
                $newPrizeHistory['create_time']       = time();
                $newPrizeHistory['feeds_left']        = $newPrizeHistory['prize_total_feeds'];
                $newPrizeHistory['invest_times']      = 0;
                $newPrizeHistory['qishu']             = $curqishu + 1;
                if (!(M('dream_prizehistory')->add($newPrizeHistory))) {
                    throw new Exception("add new prize history failed !", 1);
                }
            }

            //commit
            $model->commit();
        } catch (Exception $e) {
            logw('reveal the winner msg = '.$e->getMessage());
            logw('trace = '.$e->getTrace());
            $model->rollback();
            $tmp['code'] = 0;
            $tmp['msg'] = $e->getMessage();
            echo json_encode($tmp);
            die;
        }
    }




    /**
     * 根据get[post]参数构造查询提交
     * @return [type]       search map
     */
    private function genSearchMap()
    {
        if (is_array($_POST) && !is_null($_POST)) {
            //如果post有参数,默认从post中取参数
        }

        foreach ($_GET as $key => $value) {
        }
    }

    private function getStillmoneyLimit($search)
    {
        import("ORG.Util.PageFilter");
        $count = M('member_piggybank b')->join('lzh_members m on o.id = b.uid')->where($map)->count('o.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        return $limit;
    }

    /**
     * 获取上个月的timestamp 时间段
     * @return [type] array
     */
    private function getLastMonthRange()
    {
        //查询时间
        $first_day = 'first day of last month';
        $dt=date_create($first_day);
        $first_day_morning =  $dt->format('Y-m-d 00:00:00');
        $first = strtotime($first_day_morning);

        $last_day = "last day of last month";
        $dt=date_create($last_day);
        $last_day_night =  $dt->format('Y-m-d 23:59:59');
        $last = strtotime($last_day_night);

        $month[] = $first;
        $month[] = $last;
        return $month;
    }

    /**
     * 获取上个月投资过的投资者id数组
     * @return [type] array
     */
    private function investedLastMonth()
    {
        $monthrange = $this->getLastMonthRange();
        $this->assign('timerange', $monthrange);
        $condition['add_time'] = array('between', $monthrange);
        $result = M('borrow_investor')->field('investor_uid')->where($condition)->select();
        return array_column($result, "investor_uid");
    }

    /**
     * 获取search 和 map
     * @return [type] array
     */
    private function getSearchMap()
    {
        //用户名
        if (!empty($_REQUEST['user_name'])) {
            $map['m.user_name'] =  htmlspecialchars(trim($_REQUEST['user_name']));
            $search['user_name'] = htmlspecialchars(trim($_REQUEST['user_name']));
        }
        //手机
        if (!empty($_REQUEST['user_phone'])) {
            $map['m.user_phone'] = htmlspecialchars(trim($_REQUEST['user_phone']));
            $search['user_phone'] = htmlspecialchars(trim($_REQUEST['user_phone']));
        }
        //id
        if (!empty($_REQUEST['id'])) {
            $map['m.id'] = htmlspecialchars(trim($_REQUEST['id']));
            $search['id'] = htmlspecialchars(trim($_REQUEST['id']));
        }
        //推荐人
        if (!empty($_REQUEST['recommend_id'])) {
            $map['mi2.real_name'] = htmlspecialchars(trim($_REQUEST['recommend_id']));
            $search['recommend_id'] = htmlspecialchars(trim($_REQUEST['recommend_id']));
        }
        //标ID
        if (!empty($_REQUEST['borrow_id'])) {
            $map['bi.borrow_id'] = htmlspecialchars(trim($_REQUEST['borrow_id']));
            $search['borrow_id'] = htmlspecialchars(trim($_REQUEST['borrow_id']));
        }
        //querytime
        if (!empty($_REQUEST['query_time'])) {
            $t = strtotime(htmlspecialchars(trim($_REQUEST['query_time'])));

            $map['o.time'] = array('between',array($t, $t+86400));
            $search['query_time'] = htmlspecialchars(trim($_REQUEST['query_time']));
        }


        //根据标号搜索borrow_id
        list($bid, $borrow_id, $b_type) = $this->getBorrowid();
        if (!empty($bid)&&!empty($borrow_id)) {
            $map['bi.borrow_id'] = htmlspecialchars(trim($borrow_id));
            $search['bid'] = htmlspecialchars(trim($bid));
            $search['b_type'] = htmlspecialchars(trim($b_type));
            //unset b_type and bid
            unset($_REQUEST['bid']);
            unset($_REQUEST['b_type']);
        }


        $result[] = $map;
        $result[] = $search;
        return $result;
    }

    /**
     * 生成xml表格
     * @param  [type] $funname 需要生成xls的功能名称
     * @param  array  $arrrow  表头
     * @param  array  $arrCol  数据字段名
     * @param  [type] $info    数据集
     * @return [type]          [description]
     */
    private function genXLS($funname, array $arrrow, array $arrCol, $info)
    {
        import("ORG.Io.Excel");
        alogs($funname, 0, 1, $funname);//管理员操作日志
        $row=array();
        $row[0] = $arrrow;

        $i = 1;
        foreach ($info as $key=>$v) {
            foreach ($arrCol as $k => $col) {
                if ($col == "borrow_id") {
                    $row[$i][$col] = borrowidlayout1($v[$col]);
                } else {
                    $row[$i][$col] = $v[$col];
                }
            }
            $i++;
        }
        $xls = new Excel_XML('UTF-8', false, $funname);
        $xls->addArray($row);
        $xls->generateXML($funname.date("YmdHis", time()));
        exit;
    }

    /**
     * 检查是否需要生成xml
     * @return boolean [description]
     */
    private function isXLSRequired($funname, array $arrrow, array $arrCol, $info)
    {
        if ($_REQUEST['execl']=="execl") {
            $this->genXLS($funname, $arrrow, $arrCol, $info);
        }
    }

    /**
     *
     *
     */
    private function getBorrowid()
    {
        $tmp[] = 0;
        $tmp[] = 0;
        $tmp[] = 0;

        if (!empty($_REQUEST['b_type'])) {
            if (!empty($_REQUEST['bid'])) {
                $borrow_id = intval($_REQUEST["bid"]);
            }
            if ($_REQUEST["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->find();
            } elseif ($_REQUEST["b_type"] == 7) {
                $bid['borrow_id'] = $borrow_id;
            }

            $tmp[0] = $borrow_id;
            $tmp[1] = $bid['borrow_id'];
            $tmp[2] = $_REQUEST['b_type'];
        }

        return $tmp;
    }

    /**
     * 推广链接统计
     */
    public function regsourcecount()
    {
        import("ORG.Util.PageFilter");
        $count = M('member_source ms')->join('lzh_members m ON m.id = ms.uid')->count('ms.uid');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        $list = M('member_source ms')
                ->join("lzh_members m ON m.id = ms.uid")
                ->join("lzh_member_info mi ON mi.uid = ms.uid")
                ->field("ms.uid,m.user_name,m.user_phone,mi.real_name,m.reg_time,ms.source_pt")
                ->limit($limit)
                ->select();

        $this->assign('reg_source_count', $count);
        $this->assign('list', $list);
        $this->assign('pagebar', $page);
        $this->display();
    }

    /**
     * 长期返利明细统计
     * @return [type] [description]
     */
    public function recommendcommission()
    {
        if (!empty($_GET["tui_phone"])) {
            $where['m.user_phone'] = trim($_GET["tui_phone"]);
            $search['tui_phone'] = trim($_GET['tui_phone']);
        }

        if (!empty($_GET["tou_phone"])) {
            $where['mm.user_phone'] = trim($_GET["tou_phone"]);
            $search['tou_phone'] = trim($_GET['tou_phone']);
        }

        if (!empty($_GET['b_type'])) {
            if (!empty($_GET['bid'])) {
                $borrow_id = intval($_GET["bid"]);
                $search['bid'] = intval($_GET['bid']);
            }
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            }
            $search['b_type'] = intval($_GET['b_type']);
        }

        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $where["bi.second_verify_time"] = array("egt",strtotime($_GET['start_time']."  00:00:00"));
            $search['start_time'] = $_GET['start_time'];
        }
        if (!empty($_GET['end_time']) && empty($_GET['start_time'])) {
            $where["bi.second_verify_time"] = array("elt",strtotime($_GET['end_time']."  23:59:59"));
            $search['end_time'] = $_GET['end_time'];
        }

        if (!empty($_GET['end_time']) && !empty($_GET['start_time'])) {
            $where["bi.second_verify_time"] = array("between",strtotime($_GET['start_time']."  00:00:00").",".strtotime($_GET['end_time']." 23:59:59"));
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }
        $where["op.return_status"] = 1;
        $where["op.uid"] = array('neq',0);
        ;
        import("ORG.Util.PageFilter");
        $count = M("outside_profit op")
                ->join("lzh_members m ON m.id = op.uid")
                ->join("lzh_members mm ON mm.id = op.invest_uid")
                ->join("lzh_borrow_info bi ON bi.id = op.borrow_id")
                ->where($where)
                ->count('op.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";


        $field = "m.user_phone as tui_phone,mm.user_phone as invest_phone,bi.id as borrow_id,bi.borrow_name,bi.second_verify_time,bi.borrow_duration_txt,op.invest_money,op.return_money";
        $list = M("outside_profit op")
                ->join("lzh_members m ON m.id = op.uid")
                ->join("lzh_members mm ON mm.id = op.invest_uid")
                ->join("lzh_borrow_info bi ON bi.id = op.borrow_id")
                ->field($field)
                ->where($where)
                ->limit($limit)
                ->select();
                // echo M("outside_profit op")->getLastSql();die;
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('pagebar', $page);
        $this->display();
    }

    /**
     * 被推荐人返佣统计
     * @return [type] [description]
     */
    public function outsidecommission()
    {
        if (!empty($_GET["tui_phone"])) {
            $where['m.user_phone'] = trim($_GET["tui_phone"]);
            $search['tui_phone'] = trim($_GET['tui_phone']);
        }
        if (!empty($_GET["store_phone"])) {
            $where['mm.user_phone'] = trim($_GET["store_phone"]);
            $search['store_phone'] = trim($_GET['store_phone']);
        }

        if (!empty($_GET["tou_phone"])) {
            $where['mmm.user_phone'] = trim($_GET["tou_phone"]);
            $search['tou_phone'] = trim($_GET['tou_phone']);
        }

        if (!empty($_GET['b_type'])) {
            if (!empty($_GET['bid'])) {
                $borrow_id = intval($_GET["bid"]);
                $search['bid'] = intval($_GET['bid']);
            }
            if ($_GET["b_type"] == 1) {
                $bid = M('borrow_pledge')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 2) {
                $bid = M('borrow_optimal')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 3) {
                $bid = M('borrow_finance')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 4) {
                $bid = M('borrow_credit')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 5) {
                $bid = M('borrow_guarantee')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            } elseif ($_GET["b_type"] == 6) {
                $bid = M('borrow_installment')->where("id=".$borrow_id)->find();
                $where["bi.id"] = $bid['borrow_id'];
            }
            $search['b_type'] = intval($_GET['b_type']);
        }
        if (!empty($_GET['start_time'])) {
            $where["bi.second_verify_time"] = array("egt",$_GET['start_time']);
            $search['start_time'] = $_GET['start_time'];
        }
        if (!empty($_GET['end_time'])) {
            $where["bi.second_verify_time"] = array("elt",$_GET['end_time']);
            $search['end_time'] = $_GET['end_time'];
        }

        import("ORG.Util.PageFilter");
        $count = M("store_outside so")
                ->join("lzh_members m ON m.id = so.recommend_uid")
                ->join("lzh_members mm ON mm.id = so.store_uid")
                ->join("lzh_members mmm ON mmm.id = so.invest_uid")
                ->join("lzh_borrow_info bi ON bi.id = so.borrow_id")
                ->where($where)
                ->count('so.id');
        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        if ($_REQUEST['execl']=="execl") {
            $limit =0;
        }

        $field = "mm.user_phone as store_phone,m.user_phone as tui_phone,mmm.user_phone as invest_phone,bi.id as borrow_id,bi.borrow_name,bi.second_verify_time,bi.borrow_duration_txt,so.invest_money,so.return_money";
        $list = M("store_outside so")
                ->join("lzh_members m ON m.id = so.recommend_uid")
                ->join("lzh_members mm ON mm.id = so.store_uid")
                ->join("lzh_members mmm ON mmm.id = so.invest_uid")
                ->join("lzh_borrow_info bi ON bi.id = so.borrow_id")
                ->field($field)
                ->where($where)
                ->limit($limit)
                ->select();
                // echo M("store_outside so")->getLastSql();die;
        foreach ($list as $key => $value) {
            $list[$key]['borrow_id'] = borrowidlayout1($value["borrow_id"]);
            $list[$key]['second_verify_time'] = date("Y-m-d H:i:s", $value["second_verify_time"]);
        }

        if ($_REQUEST['execl']=="execl") {
            import("ORG.Io.Excel");
            alogs("outsidecommission", 0, 1, '执行了被推荐人返佣统计列表！');//管理员操作日志
            $row=array();
            $row[0]=array('邀请人手机号','被邀请人手机号','投资人手机号','标号','项目名称','复审时间','标的期限','投资金额','返利金额');
            $i=1;
            foreach ($info1 as $key=>$v) {
                $row[$i]['store_phone'] = $v['store_phone'];
                $row[$i]['tui_phone'] = $v['tui_phone'];
                $row[$i]['invest_phone'] = $v['invest_phone'];
                $row[$i]['borrow_id'] = $v['borrow_id'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['second_verify_time'] = $v['second_verify_time'];
                $row[$i]['borrow_duration_txt'] = $v['borrow_duration_txt'];
                $row[$i]['invest_money'] = $v['invest_money'];
                $row[$i]['return_money'] = $v['return_money'];
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'outsidecommission');
            $xls->addArray($row);
            $xls->generateXML("outsidecommission".date("YmdHis", time()));
            exit;
        }
        $this->assign('list', $list);
        $search['execl']="execl";
        $this->assign('search', $search);
        $this->assign('pagebar', $page);
        $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 邀请人数
     * @return [type] [description]
     */
    public function themayrecommendcount()
    {
        if (!empty($_GET["tui_phone"])) {
            $where['m.user_phone'] = trim($_GET["tui_phone"]);
            $search['tui_phone'] = intval($_GET['tui_phone']);
        }

        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $where["m.reg_time"] = array("egt",strtotime($_GET['start_time']."  00:00:00"));
            $search['start_time'] = $_GET['start_time'];
        }
        if (!empty($_GET['end_time']) && empty($_GET['start_time'])) {
            $where["m.reg_time"] = array("elt",strtotime($_GET['end_time']."  23:59:59"));
            $search['end_time'] = $_GET['end_time'];
        }

        if (!empty($_GET['end_time']) && !empty($_GET['start_time'])) {
            $where["m.reg_time"] = array("between",strtotime($_GET['start_time']."  00:00:00").",".strtotime($_GET['end_time']." 23:59:59"));
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }

        if (!empty($_GET['count_type']) && !empty($_GET['recommend_count'])) {
            if ($_GET["count_type"] == 1) {
                $where["rf.recommend_count"] = array("egt",intval($_GET['recommend_count']));
            } elseif ($_GET["count_type"] == 2) {
                $where["rf.recommend_count"] = array("lt",intval($_GET['recommend_count']));
            }
            $search['count_type'] = intval($_GET['count_type']);
            $search['recommend_count'] = intval($_GET['recommend_count']);
        }

        if (!empty($_GET['invest_type']) && !empty($_GET['invest_sum'])) {
            if ($_GET["invest_type"] == 1) {
                $having = "SUM(ri.invest_money) >= ".intval($_GET['invest_sum']);
            } elseif ($_GET["invest_type"] == 2) {
                $having = "SUM(ri.invest_money) < ".intval($_GET['invest_sum']);
            }
            $search['invest_type'] = intval($_GET['invest_type']);
            $search['invest_sum'] = intval($_GET['invest_sum']);
        }


        import("ORG.Util.PageFilter");
        $count = M("recommend_first rf")
                ->join("lzh_members m ON m.id = rf.recommend_uid")
                ->join("lzh_recommend_invest ri ON rf.recommend_uid = ri.recommend_uid")
                ->join("lzh_recommend_lucky rl ON rl.uid = rf.recommend_uid")
                ->where($where)
                ->having($having)
                ->group("rf.recommend_uid")
                ->select();
                // print_r($count);die;
                // echo count($count);die;
        $p = new PageFilter(count($count), $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        if (!empty($_GET["top"])) {
            $limit = 3;
            $where["rf.is_freeze"] = 0;
        }
        $field = "m.id,m.user_phone,m.user_name,rf.recommend_count,rf.experience_money,rf.used_money,rf.coupons_count,rf.is_freeze,SUM(ri.invest_money) as invest_money,m.reg_time,rl.total_count,rl.used_count";
        $list = M("recommend_first rf")
                ->join("lzh_members m ON m.id = rf.recommend_uid")
                ->join("lzh_recommend_invest ri ON rf.recommend_uid = ri.recommend_uid")
                ->join("lzh_recommend_lucky rl ON rl.uid = rf.recommend_uid")
                ->where($where)
                ->having($having)
                ->limit($limit)
                ->field($field)
                ->group("rf.recommend_uid")
                ->order("rf.recommend_count DESC")
                ->select();
        foreach ($list as $key => $value) {
            $list[$key]["experience_money"] = intval(($value['experience_money']-$value['used_money'])/1000);
        }
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('pagebar', $page);
        // $this->assign("query", http_build_query($search));
        $this->display();
    }

    /**
     * 抽奖结果
     * @return [type] [description]
     */
    public function themayprizeresult()
    {
        if (!empty($_GET["tui_phone"])) {
            $where['m.user_phone'] = trim($_GET["tui_phone"]);
            $search['tui_phone'] = trim($_GET['tui_phone']);
        }

        if (!empty($_GET['start_time']) && empty($_GET['end_time'])) {
            $where["m.reg_time"] = array("egt",strtotime($_GET['start_time']."  00:00:00"));
            $search['start_time'] = $_GET['start_time'];
        }
        if (!empty($_GET['end_time']) && empty($_GET['start_time'])) {
            $where["m.reg_time"] = array("elt",strtotime($_GET['end_time']."  23:59:59"));
            $search['end_time'] = $_GET['end_time'];
        }

        if (!empty($_GET['end_time']) && !empty($_GET['start_time'])) {
            $where["m.reg_time"] = array("between",strtotime($_GET['start_time']."  00:00:00").",".strtotime($_GET['end_time']." 23:59:59"));
            $search['start_time'] = $_GET['start_time'];
            $search['end_time'] = $_GET['end_time'];
        }

        import("ORG.Util.PageFilter");
        $count = M("recommend_winner rw")
                ->join("lzh_members m ON m.id = rw.uid")
                ->join("lzh_recommend_prize rp ON rp.id = rw.prize_id")
                ->where($where)
                ->count();

        $p = new PageFilter($count, $search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        $field = "m.id,m.user_phone,m.user_name,rp.prize_name,m.reg_time,rw.add_time";
        $list = M("recommend_winner rw")
                ->join("lzh_members m ON m.id = rw.uid")
                ->join("lzh_recommend_prize rp ON rp.id = rw.prize_id")
                ->where($where)
                ->limit($limit)
                ->field($field)
                ->order("rw.add_time DESC")
                ->select();
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('pagebar', $page);
        $this->display();
    }

    /**
     * 奖品概率设置
     * @return [type] [description]
     */
    public function themayprizeset()
    {
        $list = M("recommend_prize")->where("active = 0")->select();
        $total_count = M("recommend_prize")->where("active = 0")->sum("prize_count");
        $send_count = M("recommend_prize")->where("active = 0")->sum("send_count");
        $remaining_count = $total_count-$send_count;
        if ($this->isPost()) {
            $id = $_POST["id"];
            $prize_count = $_POST["prize_count"];
            foreach ($prize_count as $key => $value) {
                $data[$key]["prize_count"] = $value;
            }
            $prize_probability_check = 0;
            $prize_probability = $_POST["prize_probability"];
            foreach ($prize_probability as $key => $value) {
                $data[$key]["prize_probability"] = $value;
                $prize_probability_check += $value;
            }
            if ($prize_probability_check != 100) {
                $this->error('概率总和必须等于100', '/admin/yunwei/themayprizeset');
                exit;
            }
            foreach ($id as $key => $value) {
                M("recommend_prize")->where(array("id"=>$value))->save($data[$key]);
            }
            $this->success('操作成功', '/admin/yunwei/themayprizeset');
        }
        $this->assign("list", $list);
        $this->assign("total_count", $total_count);
        $this->assign("remaining_count", $remaining_count);
        $this->display();
    }

    /**
     * 二重礼中奖情况
     * @return [type] [description]
     */
    public function themayseconde()
    {
        if (!empty($_GET["tui_phone"])) {
            $where['m.user_phone'] = trim($_GET["tui_phone"]);
            $search['tui_phone'] = trim($_GET['tui_phone']);
        }

        import("ORG.Util.PageFilter");
        $count = M("recommend_seconde rs")
                ->join("lzh_members m ON m.id = rs.uid")
                ->where($where)
                ->count();

        $p = new PageFilter($count, $search, 30);
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";

        $field = "m.id,rs.id as p_id,m.user_phone,m.user_name,rs.prize_type,m.reg_time,rs.add_time,rs.send_status";
        $list = M("recommend_seconde rs")
                ->join("lzh_members m ON m.id = rs.uid")
                ->limit($limit)
                ->where($where)
                ->field($field)
                ->order("rs.add_time DESC")
                ->select();
        $this->assign('list', $list);
        $this->assign('search', $search);
        $this->assign('pagebar', $page);
        $this->display();
    }

    public function sendmoney()
    {
        $seconde_id = $_GET["id"];
        $info = M("recommend_seconde")->where(array("id"=>$seconde_id))->find();
        if ($info["send_status"] == 1) {
            $this->error("已发过了");
            exit;
        }

        if ($info["prize_type"] == 2) {
            $money=338;
        } elseif ($info["prize_type"] == 3) {
            $money=138;
        }
        $rs = sinarewardhongdong($info["uid"], $money, "SAVING_POT", "五月活动奖励");
        if ($rs['response_code'] == 'APPLY_SUCCESS') {
            M("recommend_seconde")->where(array("id"=>$seconde_id))->save(array("send_status"=>1));
            $this->success("发放成功");
        } else {
            print_r($rs);
        }
    }

    /**
     * 活动帐号异常冻结
     */
    public function themayfreezeuser(){
        $uid = $_POST["uid"];
        $status = $_POST["status"];
        $data["is_freeze"] = $status;
        M("recommend_first")->where(array("recommend_uid"=>$uid))->save($data);
        echo 1;
    }

    /**
     * 揭阳手机号段
     * @return [type] [description]
     */
    public function getJieYangMobile(){
        return array("1300526","1300527","1301669","1302526","1302527","1304923","1304924","1304925","1304926","1304927","1304928","1304929","1305844","1305845","1306055","1306056","1306057","1306058","1306059","1307650","1307651","1307652","1307653","1307654","1307655","1307656","1307657","1307658","1307659","1310693","1310694","1311210","1311211","1311212","1311213","1311214","1311215","1311216","1311217","1311218","1311219","1312830","1312831","1312832","1312833","1312834","1312835","1312836","1312837","1312838","1312839","1314407","1314408","1314409","1316925","1316926","1316927","1316928","1316929","1317280","1317281","1317282","1317283","1317284","1318480","1318481","1318482","1318483","1318484","1319290","1319291","1319292","1319293","1319294","1319295","1319296","1319297","1319298","1319299","1320220","1320221","1320222","1320223","1320224","1320255","1320256","1320257","1320258","1320259","1322615","1322616","1322617","1322618","1322619","1322917","1322918","1322919","1324225","1324226","1324227","1324228","1324229","1324690","1324691","1325044","1325045","1325066","1328810","1328811","1328850","1328851","1328852","1328853","1328854","1328855","1328856","1328857","1328858","1328859","1330275","1330276","1331817","1331818","1331819","1332275","1332276","1335270","1335271","1335272","1335273","1335274","1336038","1336039","1336078","1336079","1337651","1337652","1337653","1337654","1337663","1338055","1338056","1338057","1338058","1338059","1341390","1341391","1341392","1341393","1341394","1341395","1341396","1341397","1341398","1341399","1341480","1341481","1341482","1341483","1341484","1341760","1341761","1341762","1341763","1341764","1341765","1341766","1341767","1341768","1341769","1342110","1342111","1342112","1342113","1342114","1342115","1342116","1342117","1342118","1342119","1343000","1343001","1343002","1343003","1343004","1343005","1343006","1343007","1343008","1343009","1343490","1343491","1343492","1343493","1343494","1343495","1343496","1343497","1343498","1343499","1348030","1348031","1348032","1348033","1348034","1350015","1350016","1350143","1350144","1350251","1350256","1350260","1350265","1350266","1350267","1350268","1350269","1350903","1350904","1353190","1353191","1353192","1353193","1353194","1353195","1353196","1353197","1353198","1353199","1353450","1353451","1353452","1353453","1353454","1353925","1353926","1353927","1353928","1353929","1354220","1354221","1354222","1354223","1354224","1354390","1354391","1354392","1354393","1354394","1354395","1354396","1354397","1354398","1354399","1358015","1358016","1358017","1358018","1358019","1358020","1358021","1358022","1358023","1358024","1358025","1358026","1358027","1358028","1358029","1359290","1359291","1359292","1359293","1359294","1360011","1361242","1361243","1362026","1362027","1362028","1362029","1362273","1364035","1364036","1364037","1364038","1364229","1364246","1364247","1365295","1365296","1365297","1367055","1367056","1367057","1367058","1367059","1368270","1368271","1368272","1368273","1368274","1368275","1368276","1368277","1368278","1368279","1368280","1368281","1368282","1368283","1368284","1368745","1368746","1368747","1368748","1368749","1369510","1369511","1369512","1369513","1369514","1372930","1372931","1372932","1372933","1372934","1372935","1372936","1372937","1372938","1372939","1372940","1372941","1372942","1372943","1372944","1372945","1372946","1372947","1372948","1372949","1375165","1375166","1375167","1375168","1375169","1376055","1376056","1376057","1376058","1376059","1380231","1380232","1382200","1382201","1382202","1382203","1382204","1382205","1382206","1382207","1382208","1382209","1382290","1382291","1382292","1382293","1382294","1382295","1382296","1382297","1382298","1382299","1382810","1382811","1382812","1382813","1382814","1382815","1382816","1382817","1382818","1382819","1390275","1390276","1390308","1392267","1392268","1392353","1392354","1392355","1392356","1392443","1392444","1392560","1392561","1392562","1392563","1392564","1392565","1392566","1392567","1392568","1392569","1392700","1392701","1392702","1392703","1392704","1392705","1392706","1392707","1392708","1392709","1452978","1452979","1454826","1459075","1459076","1459077","1459078","1459079","1471479","1471527","1471545","1471589","1471622","1471808","1471826","1471829","1471830","1471831","1471832","1471837","1471840","1471846","1471847","1471850","1501447","1501448","1501458","1501459","1501652","1501653","1501654","1501655","1501656","1501657","1501821","1501822","1501823","1501824","1501825","1501826","1501827","1501828","1501829","1508935","1508936","1508937","1508938","1508939","1508944","1509983","1511375","1511376","1511377","1511890","1511939","1521860","1521861","1521862","1521863","1521864","1521865","1521866","1521867","1521868","1521869","1521936","1521937","1521938","1521960","1521961","1521962","1521963","1530250","1530251","1530252","1530253","1530254","1530255","1530256","1530257","1530258","1530259","1532320","1532321","1532322","1534750","1534751","1536120","1536121","1536122","1536123","1536124","1552148","1552170","1552187","1552188","1552189","1552190","1552191","1552192","1552193","1552194","1552195","1560275","1560276","1560308","1562267","1562268","1562518","1562519","1562560","1562561","1562562","1562563","1562564","1562565","1562566","1562567","1562568","1562569","1562700","1562701","1562702","1562703","1562704","1562705","1562706","1562707","1562708","1562709","1562793","1562794","1562798","1569765","1570663","1571837","1572413","1572831","1572882","1576612","1576613","1576615","1576632","1576664","1576673","1576675","1576676","1576677","1576679","1576688","1576689","1576699","1577509","1580767","1581350","1581351","1581352","1581353","1581354","1581355","1581356","1581357","1581358","1581359","1581954","1581955","1581956","1581957","1581958","1581959","1581960","1581961","1581962","1581963","1581964","1581965","1581966","1581967","1581968","1581969","1587519","1588910","1588911","1588912","1588913","1588914","1588915","1588916","1588917","1588918","1588919","1588980","1591493","1591494","1591560","1591561","1591562","1591563","1591564","1591565","1591566","1591567","1591568","1591569","1591719","1591720","1591721","1591722","1591795","1591796","1591797","1591798","1591799","1597510","1597511","1597512","1597513","1597514","1597515","1597516","1597517","1597518","1597519","1597520","1597521","1597522","1597523","1597524","1598690","1598691","1599250","1599251","1599252","1599253","1599254","1599255","1599256","1599257","1599258","1599259","1752060","1752061","1752062","1752063","1752064","1760663","1768806","1770767","1771882","1771883","1772218","1772225","1772297","1772411","1772412","1772575","1772585","1772594","1772595","1772596","1772825","1772826","1772839","1772842","1772886","1780663","1781708","1781719","1781729","1781764","1781915","1782026","1782027","1787511","1787515","1787545","1787576","1787596","1787608","1787637","1787652","1787663","1787665","1787675","1789660","1800094","1800095","1800764","1802250","1802251","1802252","1802253","1802254","1802255","1802256","1802257","1802258","1802259","1802499","1802600","1802601","1802602","1802603","1802604","1802605","1802606","1802607","1802608","1802609","1802670","1802671","1802672","1802673","1802674","1802994","1802995","1802996","1808887","1810764","1812260","1812261","1812262","1812263","1812264","1812265","1812266","1812267","1812268","1812269","1812443","1812594","1812595","1812596","1812597","1812831","1812832","1812833","1812834","1816346","1820091","1820663","1820669","1821158","1821823","1821831","1821862","1821915","1830011","1830015","1830663","1831208","1831219","1831221","1831230","1831231","1831232","1831235","1831243","1831244","1831274","1831281","1831665","1831671","1831672","1831673","1831804","1831807","1831814","1831830","1831832","1831835","1831842","1831843","1831885","1832050","1832051","1832052","1832053","1832054","1832055","1832056","1832057","1832058","1832059","1834410","1834412","1834413","1834424","1842038","1847510","1847511","1847512","1847524","1850663","1850666","1851261","1852900","1852901","1852902","1852903","1852904","1852905","1852967","1852968","1852969","1857868","1857869","1860765","1866552","1866630","1866631","1866632","1866633","1866634","1867549","1867591","1868012","1868013","1868014","1868804","1868806","1870754","1870768","1871807","1871823","1871826","1871828","1871845","1871846","1871860","1871861","1871862","1871899","1880663","1881189","1882290","1882291","1882292","1882293","1882294","1882353","1882354","1882355","1882356","1882443","1882444","1882565","1882566","1882567","1882568","1882569","1889867","1889984","1890275","1890276","1890308","1892267","1892268","1892353","1892354","1892355","1892356","1892444","1892560","1892561","1892562","1892563","1892564","1892565","1892566","1892567","1892568","1892569","1892705","1892706","1892707","1892708","1892709","1893311","1893312","1893369","1893383","1893384","1893385","1893409","1893834","1894696","1894697","1894836","1894837","1894838","1894843","1894844","1894845","1899825","1899826","1899827");
    }
}
