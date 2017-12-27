<?php

/**
 * 各种活动
 * Class IndexAction
 */
class ActiveAction extends HCommonAction
{

    //双蛋活动
    public function christmas()
    {
        //投资金额
        $startime = strtotime("2016-12-13 00:00:00");
        $endtime  = strtotime("2017-01-18 23:59:59");
        $xu_count = 0;
        if ($this->uid != null) {
            $where["investor_uid"] = $this->uid;
            $where["add_time"] = array("between",$startime.",".$endtime);
            $invest_money = M("borrow_investor")->where($where)->sum("investor_capital");
            if ($invest_money == null) {
                $invest_money = 0;
            }
            $this->assign("invest_money", $invest_money);
            $gift_first = M("christmas_have")->where(array("uid"=>$this->uid,"is_xu"=>0))->find();
            $first_have = null;
            $next_have = 0;
            //更新一重礼奖品
            if ($invest_money < 50000 && $invest_money != 0) {
                $first_gift["gift_id"] = 1;//千分之一返现
                $first_gift["gift_set_no"] = 5;
                $first_have = "1‰返现";
                $next_have = 50000 - $invest_money;
            } elseif ($invest_money >= 50000 && $invest_money < 100000) {
                $first_gift["gift_id"] = 2;//京东E卡50元
                $first_gift["gift_set_no"] = 6;
                $first_have = "50元京东E卡1张";
                $next_have = 100000 - $invest_money;
            } elseif ($invest_money >= 100000 && $invest_money < 200000) {
                $first_gift["gift_id"] = 3;//京东E卡100元
                $first_gift["gift_set_no"] = 7;
                $first_have = "100元京东E卡1张";
                $next_have = 200000 - $invest_money;
            } elseif ($invest_money >= 200000 && $invest_money < 500000) {
                $first_gift["gift_id"] = 4;//京东E卡200元
                $first_gift["gift_set_no"] = 8;
                $first_have = "200元京东E卡1张";
                $next_have = 500000 - $invest_money;
            } elseif ($invest_money >= 500000) {
                $first_gift["gift_id"] = 5;//京东E卡500元
                $first_gift["gift_set_no"] = 9;
                $first_have = "500元京东E卡1张";
                $next_have = 0;
            } else {
                $first_gift["gift_id"] = 6;
                $next_have = 1;
            }
            if (time() <= $endtime) {
                if ($invest_money != 0) {
                    if ($gift_first) {
                        if ($gift_first["gift_id"] != $first_gift["gift_id"]) {
                            M("christmas_have")->where(array("uid"=>$this->uid,"is_xu"=>0))->save($first_gift);
                        }
                    } else {
                        $first_gift["uid"] = $this->uid;
                        $first_gift["is_xu"] = 0;
                        M("christmas_have")->add($first_gift);
                    }
                }
            }
            $all_xu_count = floor($invest_money/1000);
            $used_xu_count = M("christmas_number")->where(array("uid"=>$this->uid))->count();
            $xu_count = $all_xu_count - $used_xu_count; //可许愿次数
            $this->assign("first_gift", $first_gift["gift_id"]);
            $this->assign("first_have", $first_have);
            $this->assign("next_have", $next_have);
            $this->assign("invest_money", $invest_money);
            $this->assign("is_login", 1);
        } else {
            session("login_next", "/Home/active/christmas");
            $this->assign("is_login", 0);
            $this->assign("first_gift", 0);
        }
        $xu_gift = M("christmas_gift")->where(array("is_xu"=>1,"have_uid"=>0))->select();
        $kaodian = array();
        $xiaomi = array();
        $jianeng = array();
        $apple = array();
        foreach ($xu_gift as $key => $xu) {
            if ($xu['gift_set_no'] == 1) {
                $kaodian["gift_id"]     = $xu["id"];
                $kaodian["gift_set_no"] = $xu["gift_set_no"];
                $kaodian["qi"]          = $xu["gift_no"];
                $kaodian["send_number"] = $xu["send_number"];
                $kaodian["gift_number"] = $xu["gift_number"];
                $kaodian["is_open"]     = $xu["is_open"];
                $kaodian["jindu"]       = ceil(($xu["send_number"]/$xu["gift_number"]*100));
            } elseif ($xu['gift_set_no'] == 2) {
                $xiaomi["gift_id"]     = $xu["id"];
                $xiaomi["gift_set_no"] = $xu["gift_set_no"];
                $xiaomi["qi"]          = $xu["gift_no"];
                $xiaomi["send_number"] = $xu["send_number"];
                $xiaomi["gift_number"] = $xu["gift_number"];
                $xiaomi["is_open"]      = $xu["is_open"];
                $xiaomi["jindu"]        = ceil(($xu["send_number"]/$xu["gift_number"]*100));
            } elseif ($xu['gift_set_no'] == 3) {
                $jianeng["gift_id"]     = $xu["id"];
                $jianeng["gift_set_no"] = $xu["gift_set_no"];
                $jianeng["qi"]          = $xu["gift_no"];
                $jianeng["send_number"] = $xu["send_number"];
                $jianeng["gift_number"] = $xu["gift_number"];
                $jianeng["is_open"]     = $xu["is_open"];
                $jianeng["jindu"]       = ceil(($xu["send_number"]/$xu["gift_number"]*100));
            } elseif ($xu['gift_set_no'] == 4) {
                $apple["gift_id"]       = $xu["id"];
                $apple["gift_set_no"]   = $xu["gift_set_no"];
                $apple["qi"]            = $xu["gift_no"];
                $apple["send_number"]   = $xu["send_number"];
                $apple["gift_number"]   = $xu["gift_number"];
                $apple["is_open"]       = $xu["is_open"];
                $apple["jindu"]         = ceil(($xu["send_number"]/$xu["gift_number"]*100));
            }
        }
        $this->assign("kaodian", $kaodian);
        $this->assign("xiaomi", $xiaomi);
        $this->assign("jianeng", $jianeng);
        $this->assign("apple", $apple);
        $list_where["add_time"] = array("egt",$startime);
        //中奖列表
        $zhong_list = M("christmas_have ch")
                    ->join("lzh_members m ON m.id = ch.uid")
                    ->join("lzh_borrow_investor b ON b.investor_uid = ch.uid")
                    ->join("lzh_christmas_gift cg ON cg.id = ch.gift_id")
                    ->where($list_where)
                    ->field("SUM(b.investor_capital) as have_invest_money,m.user_phone,cg.gift_name,ch.gift_no,ch.have_number,ch.is_xu")
                    ->group("ch.gift_no,ch.uid")
                    ->select();
        $this->assign("zhonglist", $zhong_list);
        if (time() >= $endtime) {
            $xu_count = 0;
        }
        $this->assign("xu_count", $xu_count);
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5christmas");
        } else {
            $this->display("christmas");
        }
    }

    //我的新年礼物
    /**
     * *
     *
     */
    public function mynewyears()
    {
        //this is comment
        $startime = strtotime("2016-12-13 00:00:00");
        $endtime  = strtotime("2017-01-18 23:59:59");
        $list = M("christmas_have ch")
              ->join("lzh_christmas_gift cg ON cg.id = ch.gift_id")
              ->where(array("ch.uid"=>$this->uid))
              ->field("cg.gift_name,ch.gift_no,ch.have_number,ch.is_xu")
              ->select();
        $where["investor_uid"] = $this->uid;
        $where["add_time"] = array("between",$startime.",".$endtime);
        $usr_inveset = M("borrow_investor ")
                  ->where($where)
                  ->sum("investor_capital");
        $member = M("members")->where(array("id"=>$this->uid))->find();
        $usr_info["user_name"] = $member["user_name"];
        if ($usr_inveset != 0 || $usr_inveset != null) {
            $usr_info["invest_money"] = $usr_inveset;
        } else {
            $usr_info["invest_money"] = 0;
        }
        $data[0] = $list;
        $data[1] = $usr_info;
        if ($this->is_mobile()) {
            if ($this->uid) {
                $simple_header_info=array("url"=>"/Home/active/christmas","title"=>"我的新年礼物");
                $this->assign("simple_header_info", $simple_header_info);
                $this->assign("list", $list);
                $this->assign("usr_info", $usr_info);
                $this->display();
            } else {
                redirect(__APP__."/M/pub/login.html");
            }
        } else {
            $this->ajaxReturn($data);
        }
    }

    //我的许愿
    public function mywish()
    {
        $wishlist = M("christmas_number cn")
                  ->join("lzh_christmas_gift cg ON cg.id = cn.gift_id")
                  ->where(array("cn.uid"=>$this->uid))
                  ->field("cg.gift_no,cg.gift_name,cg.have_number,cg.have_uid")
                  ->group("cg.gift_no")
                  ->select();

        if ($this->is_mobile()) {
            if ($this->uid) {
                $simple_header_info=array("url"=>"/Home/active/christmas","title"=>"我的许愿");
                $this->assign("simple_header_info", $simple_header_info);
                $this->assign("wishlist", $wishlist);
                $this->assign("uid", $this->uid);
                $this->display();
            } else {
                redirect(__APP__."/M/pub/login.html");
            }
        } else {
            $data[0]["uid"] = $this->uid;
            $data[1] = $wishlist;
            $this->ajaxReturn($data);
        }
    }

    //新年幸运号
    public function myluckynum()
    {
        $gift_set_no = isset($_REQUEST["gift_set"])?$_REQUEST["gift_set"]:1;
        $lucknumlist = M("christmas_gift cg")
                     ->join("lzh_members m ON m.id = cg.have_uid")
                     ->where(array("gift_set_no"=>$gift_set_no,"have_number"=>array("neq",'')))
                     ->field("cg.gift_no,cg.have_number,m.user_phone,cg.id")
                     ->select();
        foreach ($lucknumlist as $key => $value) {
            $lucknumlist[$key]['user_phone'] = hidecard($value['user_phone'], 2);
        }
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/Home/active/christmas","title"=>"新年幸运号");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("lucknumlist", $lucknumlist);
            $this->assign("gift_set", $gift_set_no);
            $this->display();
        } else {
            $this->ajaxReturn($lucknumlist);
        }
    }

    //幸运号计算详情
    public function luckyhnuminfo()
    {
        $gift_id = $_REQUEST["gift_set"];
        $luckyinfo = M("christmas_gift")
                   ->where(array("id"=>$gift_id))
                   ->field("gift_no,gift_name,avg_number,have_number")
                   ->find();
        $luckynum = M("christmas_number cn")
                  ->join("lzh_members m ON m.id = cn.uid")
                  ->where(array("cn.gift_id"=>$gift_id))
                  ->field("GROUP_CONCAT(cn.xu_number) as xu_num,cn.uid,cn.is_zhong,cn.add_time,m.user_phone")
                  ->group("cn.add_time")
                  ->order("cn.add_time ASC")
                  ->select();
        foreach ($luckynum as $key => $value) {
            $luckynum[$key]["user_phone"] = hidecard($value['user_phone'], 2);
            $luckynum[$key]["add_time"] = date("Y-m-d H:i:s", $value['add_time']);
        }
        $data[0] = $luckyinfo;
        $data[1] = $luckynum;
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/Home/active/christmas","title"=>"幸运号计算详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->assign("luckyinfo", $luckyinfo);
            $this->assign("luckynum", $luckynum);
            $this->display();
        } else {
            $this->ajaxReturn($data);
        }
    }

    //我的幸运号详情
    public function myluckynuminfo()
    {
        $myluckynum = M("christmas_number cn")
                    ->join("lzh_christmas_gift cg ON cn.gift_id = cg.id")
                    ->field("cg.gift_no,cg.gift_name,cg.have_number as zhong_num,cn.gift_id")
                    ->where(array("cn.uid"=>$this->uid))
                    ->group("cg.id")
                    ->select();
        foreach ($myluckynum as $key => $value) {
            $luckynum = null;
            $numlist = M("christmas_number")->where(array("uid"=>$this->uid,"gift_id"=>$value['gift_id']))->field("xu_number")->select();
            foreach ($numlist as $n) {
                $luckynum .= $n['xu_number']."，";
            }
            $myluckynum[$key]["luckynum"] = $luckynum;
            if ($value["zhong_num"] == null) {
                $myluckynum[$key]["zhong_num"] = "未开奖";
            }
        }
        if ($this->is_mobile()) {
            if ($this->uid) {
                $simple_header_info=array("url"=>"/Home/active/christmas","title"=>"我的幸运号详情");
                $this->assign("simple_header_info", $simple_header_info);
                $this->assign("myluckynum", $myluckynum);
                $this->display();
            } else {
                redirect(__APP__."/M/pub/login.html");
            }
        } else {
            $this->ajaxReturn($myluckynum);
        }
    }

    //向她许愿
    public function wishtoit()
    {
        $gift_id = $_REQUEST["gift_id"];
        $startime = strtotime("2016-12-13 00:00:00");
        $endtime  = strtotime("2017-01-18 23:59:59");
        $where["investor_uid"] = $this->uid;
        $where["add_time"] = array("between",$startime.",".$endtime);
        $invest_money = M("borrow_investor")->where($where)->sum("investor_capital");
        $all_xu_count = floor($invest_money/1000);
        $used_xu_count = M("christmas_number")->where(array("uid"=>$this->uid))->count();
        $xu_count = $all_xu_count - $used_xu_count; //可许愿次数
        $gift = M("christmas_gift")->where(array("id"=>$gift_id))->find();
        if ($gift["gift_set_no"] == 1) {
            $data["gift_name"] = "颈部腰部肩部按摩椅垫（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 2) {
            $data["gift_name"] = "小米空气净化器（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 3) {
            $data["gift_name"] = "佳能EOS 700D 单反套机（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 4) {
            $data["gift_name"] = "iPhone 7 （128g颜色随机）（".$gift["gift_no"]."期）";
        }
        if (time() >= $endtime) {
            $xu_count = 0;
        }
        $data["xu_count"] = $xu_count;
        $data["gift_id"] = $gift_id;
        $this->ajaxReturn($data);
    }



    /**
     *  fullfill your dreams
     *
     */
    public function fullfillDream()
    {
        $gameover = 0;
        $glo = get_global_setting();
        $end = $glo['dream_end_time'];
        if ($end < time()) {
            $gameover = 1;
        }

        $this->checkSchedual();
        $dream_feed = 0;
        $dream_invest = 0;
        $dream_invest_total = 0;

        //prize list
        for ($i=1; $i < 12; $i++){
            unset($list);
            $list = M('dream_prizehistory')->where(array('prize_type' => $i, 'status' => 0))
                                           ->order('qishu desc,id desc')
                                           ->select();

            if (is_array($list)) {
                $tmp = $list[sizeof($list) - 1 ];
            } else {
                // default prize
                $overcond['prize_type'] = $i;
                $latest = M('dream_prizehistory')->where(array('prize_type' => $i))
                                                 ->order('qishu desc,id desc')
                                                 ->find();
                $tmp = null;
                $tmp['id']                = $latest['id'];
                $tmp['prize_id']          = $latest['prize_id'];
                $tmp['prize_name']        = $latest['prize_name'];
                $tmp['prize_min_feeds']   = $latest['prize_min_feeds'];
                $tmp['prize_total_feeds'] = $latest['prize_total_feeds'];
                $tmp['prize_type']        = $i;
                $tmp['create_time']       = $latest['create_time'];
                $tmp['feeds_left']        = $latest['feeds_left'];
                $tmp['invest_times']      = $latest['invest_times'];
                $tmp['qishu']             = $latest['qishu'];
                $over = $latest['qishu']%5;
            }

            if (($i == 1)||($i == 2)||($i == 3)) {
                $xpri = M('dream_prize')->where(array('type'=>$i))->find();
                $inventory = $xpri['inventory'];
                $tmp['inventory'] = $inventory;
            }

            $tmp['total'] = $tmp['prize_total_feeds']/$tmp['prize_min_feeds'];
            $tmp['invested'] = ($tmp['prize_total_feeds'] - $tmp['feeds_left'])/$tmp['prize_min_feeds'];
            $tmp['remaining'] = $tmp['feeds_left']/$tmp['prize_min_feeds'];
            $tmp['pct'] = intval($tmp['invested']*100/$tmp['total']);

            if ($tmp['qishu']%5==0) {
                $tmp['over']          = 1;
            }

            if ($tmp['qishu']%10==0) {
                $tmp['red']          = 1;
            }
            if($i==9){
                //echo $tmp['prize_name'];
                //die;
            }
            $this->assign('prize_'.$i, $tmp);
        }

        //the lucky guy list
        $lucky_list = M('dream_true')->where(1)->order('id desc ')->select();
        foreach ($lucky_list as $key => $value) {
            $lucky_list[$key]['money'] = number_format($value['money'], 2, '.', ',');
            $lucky_list[$key]['mobile'] = substr_replace($value['mobile'], '****', 3, 4);
        }

        //check if user login and have realname verification
        if (intval(session('u_id'))) {
            $mstatus = M('members_status')->where(array('uid'=>session('u_id')))->find();
            //$this->assign('uid', intval(session('u_id')));
            $this->assign('uid', 1);
            if ($mstatus['id_status'] == 1) {
                $this->assign('realnameed', 1);
            } else {
                $this->assign('realnameed', 0);
            }

            $minfo = M('members')->find(session('u_id'));
            $dream_feed = $minfo['dream_feeds'];

            $dream_invest_total = $minfo['dream_invest_total'];
            $dream_invest = $minfo['dream_invest_total'] - $minfo['dream_invested'];
        } else {
            $this->assign('uid', '0');
            $this->assign('realnameed', "0");
        }

        $this->assign('lucky_list', $lucky_list);
        $this->assign('dream_feed', $dream_feed);
        $this->assign('dream_investmoney', $dream_invest);
        $this->assign('dream_investmoney_total', $dream_invest_total);
        $this->assign('gameover', $gameover);

        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5fullfilldream");
        } else {
            $this->display("fullfilldream");
        }
    }

    /**
     *
     *
     */
    public function fullfillHistory()
    {
    $this->checkSchedual();

        $dream_feed = 0;
        $dream_invest = 0;
        $dream_invest_total = 0;
        $dreamhis = array();
        $luck = array();
        $login = false;

        if (intval(session('u_id'))) {
            $login = true;

            $minfo = M('members')->find(session('u_id'));
            $dream_feed = $minfo['dream_feeds'];

            $dream_invest_total = $minfo['dream_invest_total'];
            $dream_invest = $minfo['dream_invest_total'] - $minfo['dream_invested'];

            //investory history
            $dreamhis = M('dream_invest')->where(array('uid' => session('u_id')))
                                         ->order("id desc")
                                         ->select();

            //prize you vote
            foreach ($dreamhis as $key => $value) {
                $tmp[$value['prize_id']][$value['qishu']]['prize_name']= $value['prize_name'] ;
                $tmp[$value['prize_id']][$value['qishu']]['arr'][] = $value['feed_no'] ;
                $tmp[$value['prize_id']][$value['qishu']]['qishu'] = $value['qishu'] ;
            }


            //prize you win
            $luck = M('dream_true')->where(array('uid'=>session('u_id')))
                                   ->order('id desc')
                                   ->select();

            foreach ($luck as $key => $value) {
                $tmp[$value['prize_id']][$value['qishu']]['luck'] = $value['feed_no'];
            }

            //prize ongoing
            foreach ($tmp as $key1 => $pri1) {
                foreach ($pri1 as $key2 => $spec) {
                    $res = M('dream_true')->where(array('prize_id'=>$key1))->find();
                    if (!$res) {
                        $tmp[$key1][$key2]['luck'] = "尚未揭晓";
                    } else {
                        $tmp[$key1][$key2]['luck'] = $res['feed_no'];
                    }
                }
            }
        } else {
            $login = false;
        }


        $this->assign('dream_feed', $dream_feed);
        $this->assign('dream_investmoney', $dream_invest);
        $this->assign('dream_investmoney_total', $dream_invest_total);
        $this->assign('dream_investhistory', $tmp);
        $this->assign('dream_true', $luck);

        if ($this->is_mobile()) {
            if (!$login) {
                redirect("/M/pub/login.html");
            }

            $simple_header_info=array("url"=>"/home/active/fullfilldream","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5fullfillhistory");
        } else {
            if (!$login) {
                redirect("/member/common/login/");
            }
            $this->display("fullfillhistory");
        }
    }

    public function begin()
    {
        exit('false');

        $prize_list = M('dream_prize')->where(array('default' => 1,'type'=>array('lt','9')))->select();
        foreach ($prize_list as $key => $pri) {
            $cond['prize_id'] = $pri['id'];
            $cond['qishu'] = 1;
            if (!M('dream_prizehistory')->where($cond)->find()) {
                $data['prize_id'] = $pri['id'];
                $data['prize_name'] = $pri['name'];
                $data['prize_min_feeds'] = $pri['min_feeds'];
                $data['prize_total_feeds'] = $pri['total_feeds'];
                $data['prize_type'] = $pri['type'];
                $data['create_time'] = time();
                $data['feeds_left'] = $data['prize_total_feeds'];
                $data['invest_times'] = 0;
                $data['qishu'] = 1;
                echo $data['prize_name'];
                echo json_encode(M('dream_prizehistory')->add($data));
            }
        }
        echo json_encode(M('dream_prizehistory')->where(1)->select());
        die;
    }

    public function insertPrize(){
        $per = $_REQUEST['op'];
        if($per != 'lsx'){
            exit('-1');
        }

        $result = M('dream_prize')->where(array('default'=>1,'type'=>array('gt','8')))->select();
        if(is_array($result)&&!empty($result))
             exit('success,already exist');

        $model = new Model();
        try {
            $model->startTrans();
            for ($i=0; $i < 3; $i++) { 
                if($i == 2){
                    //tcl
                    $data['name']        = "TCL家用除湿干衣机(899元)";
                    $data['min_feeds']   = "4";
                    $data['total_feeds'] ="2200";
                }elseif($i == 1){
                    //tcl
                    $data['name']        = "小米空气净化器2代(699元)";
                    $data['min_feeds']   = "4";
                    $data['total_feeds'] ="1800";
                }elseif($i == 0){
                    
                    //tcl
                    $data['name']        = "小米移动电源／加湿器(79元)";
                    $data['min_feeds']   = "1";
                    $data['total_feeds'] ="200";
                }
                $data['create_time'] = time();
                $data['type']        = $i + 9;
                $data['inventory']   = 1000;
                $data['default']     = 1;

                $res = M('dream_prize')->add($data);
                if($res === false)
                    throw new Exception("add error {$i}", 1);
            }

            $model->commit();
    
        } catch (Exception $e) {
            $model->rollback();
            echo $e->getMessage;
        }
        echo 'success';
        
    }

    public function synPrize(){
        $per = $_REQUEST['op'];
        if($per != 'lsx'){
            exit('-1');
        }
        
        $prize_list = M('dream_prize')->where(array('default' => 1,'type'=>array('gt',8)))->select();
        $prizehis = M('dream_prizehistory')->where(array('prize_type'=>array('gt',8)))->select();
        if(is_array($prizehis)&&!empty($prizehis))
            exit('success ,already exist');

        $model = new Model();
        try {
            $model->startTrans();

            foreach ($prize_list as $key => $pri) {
                $cond['prize_id'] = $pri['id'];
                $cond['qishu'] = 1;
                if(!M('dream_prizehistory')->where($cond)->find()){
                        unset($data);
                        $data['prize_id'] = $pri['id'];
                        $data['prize_name'] = $pri['name'];
                        $data['prize_min_feeds'] = $pri['min_feeds'];
                        $data['prize_total_feeds'] = $pri['total_feeds'];
                        $data['prize_type'] = $pri['type'];
                        $data['create_time'] = time();
                        $data['feeds_left'] = $data['prize_total_feeds'];
                        $data['invest_times'] = 0;
                        $data['qishu'] = 1;
                        
                        $res = M('dream_prizehistory')->add($data);
                        if($res === false)
                            throw new Exception("syn error {$pri['name']}", 1);
                }
            }

            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            exit($e->getMessage());
        }

        exit('success');
        
    }

    public function playAgain(){
        $per = $_REQUEST['op'];
        if($per != 'lsx'){
            exit('-1');
        }
        $model = new Model();
        try {
            $model->startTrans();

            $res = M('dream_prizehistory')->where(1)->delete();
            if($res === false){
                throw new Exception("an error occure during dream_prizehistory clean ", 1);
            }

            $res1 = M('dream_invest')->where(1)->delete();
            if($res === false){
                throw new Exception("an error occure during dream_invest clean ", 1);
            }


            $res2 = M('dream_true')->where(1)->delete();
            if($res === false){
                throw new Exception("an error occure during dream_true clean ", 1);
            }


            $res2 = M('dream_log')->where(1)->delete();
            if($res === false){
                throw new Exception("an error occure during dream_log clean ", 1);
            }

            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            exit('0');
        }
        exit('1');
    }
    /**
     * invest
     *
     * prize_id
     * feed_amount
     */
    public function ajaxInvest()
    {
        $prizeHisId = $_POST['prize_history_id'];
        $feedAmount = $_POST['feed_amount'];
        $glo        = get_global_setting();
        $start      = $glo['dream_start_time'];
        $end        = $glo['dream_end_time'];
        $status     = $glo['dream_status'];

        if (!isset($_POST['prize_history_id'])||intval($_POST['prize_history_id']) === false) {
            $this->ajaxError('奖品不存在!');
        }

        if (!isset($_POST['feed_amount'])||intval($_POST['feed_amount']) === false) {
            $this->ajaxError('投注次数必须是数字!');
        }

        if (intval($_POST['feed_amount']) < 0) {
            $this->ajaxError('投注不能为负数!');
        }

        if (!$status) {
            $this->ajaxError('活动已结束!');
        }

        if ($start > time()) {
            $this->ajaxError('活动尚未开始!');
        }

        if ($end < time()) {
            $this->ajaxError('活动已结束,谢谢参与 !');
        }

        if (intval(session('u_id')) === false) {
            $this->ajaxError('请登录');
        }

        $memInfo = M('members')->find(session('u_id'));
        $prizeInfo = M('dream_prizehistory')->find($prizeHisId);

        if ($prizeInfo['feeds_left'] < $feedAmount) {
            $this->ajaxError("余数不足,最多还可投".$prizeInfo['feeds_left']."");
        }

        if ($prizeInfo['prize_type']< 4) {
            $this->investZhuiMeng();
        } else {
            $this->investYuanmeng();
        }

        //reveal the winner
        $this->revealTheWinner();

        $this->ajaxSuccess("congratuation ,invest success ! prizeId = ".$_POST['prize_history_id'].' feed amount = '.$_POST['feed_amount']);
    }


    public function investZhuiMeng()
    {
        logw(' enter into investzhuimeng');
        $prizeHisId = $_POST['prize_history_id'];
        $feedAmount = $_POST['feed_amount'];
        $glo        = get_global_setting();
        $start      = $glo['dream_start_time'];
        $end        = $glo['dream_end_time'];
        $status     = $glo['dream_status'];
        $memInfo = M('members')->find(session('u_id'));

        $model = new Model();
        try {
            $model->startTrans();



            //check prize is avaialbe
            //exclude query
            $readlock = $prizeInfo = M('dream_prizehistory')->lock(true)->find($prizeHisId);

            //concurrency test
            if (APP_DEBUG) {
                sleep(2);
            }

            logw(' get last sql = '.M('dream_prizehistory')->getLastSql());
            logw(' readlock result = '.json_encode($readlock));
            if ($memInfo['dream_feeds'] < $feedAmount) {
                $this->ajaxError("梦想种子不足,最多可投".$memInfo['dream_feeds']."个");
            }

            if ($prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds']<$feedAmount) {
                $feedAmount = $prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds'];
                logw('  over vote  feed amount = '.$feedAmount);
            }

            $pi['invest_times'] = array('exp','invest_times+1');
            $pi['feeds_left'] = array('exp','feeds_left-'.$feedAmount*$prizeInfo['prize_min_feeds']);
            if (!(M('dream_prizehistory')->where(array('id'=>$prizeInfo['id']))->save($pi))) {
                throw new Exception("dream prize history save exception ", 1);
            }

            $prizeMax = $prizeInfo['prize_total_feeds'];
            $prizeLeft = $prizeInfo['feeds_left'];
            $prizeInvested = ($prizeMax - $prizeLeft)/$prizeInfo['prize_min_feeds'];

            //gen feeds
            $sql = "insert into lzh_dream_invest (`uid`,`mobile`,`money`,`prize_id`,`prize_name`,`prize_type`,`qishu`,`feeds_amount`,`create_time`,`feed_no`) values ";
            for ($i = $prizeInvested+1; $i < $prizeInvested + $feedAmount+1; $i++) {
                $data['uid']          = session('u_id');
                $data['mobile']       = $memInfo['user_phone'];
                $data['money']        = 0;
                $data['prize_id']     = $prizeInfo['id'];
                $data['prize_name']   = $prizeInfo['prize_name'];
                $data['prize_type']   = $prizeInfo['prize_type'];
                $data['qishu']        = $prizeInfo['qishu'];
                $data['feeds_amount'] = $feedAmount;
                $data['create_time']  = time();
                $data['feed_no']      = 10000000 + intval($i);

                if ($i==($prizeInvested+$feedAmount)) {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."');";
                } elseif ($i == $prizeInvested +1) {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
                } else {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
                }
            }

            $Model = new Model();
            $resultx = $Model->execute($sql);
            if ($resultx === false) {
                throw new Exception("投资种子生成失败!", 1);
            }

            //reduce money
            $mi['dream_feeds'] = array('exp','`dream_feeds` -'.$feedAmount);
            $res3 = M('members')->where(array('id' => $memInfo['id']))->save($mi);
            if ($res3 === false) {
                throw new Exception("update member failed !", 1);
            }

            //end transaction commmit
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            logw('msg = '.$e->getMessage());
            logw('trace = '.json_encode($e->getTrace()));
            $this->ajaxError($e->getMessage());
        }
    }

    public function investYuanmeng()
    {
        $prizeHisId = $_POST['prize_history_id'];
        $feedAmount = $_POST['feed_amount'];
        $glo        = get_global_setting();
        $start      = $glo['dream_start_time'];
        $end        = $glo['dream_end_time'];
        $status     = $glo['dream_status'];
        $memInfo = M('members')->find(session('u_id'));

        //begin transaction
        $model = new Model();
        try {
            $model->startTrans();

            //check prize is avaialbe
            //exclude query
            $prizeInfo = M('dream_prizehistory')->lock(true)->find($prizeHisId);

            //concurrency test
            if (APP_DEBUG) {
                sleep(1);
            }

            if (($memInfo['dream_invest_total'] - $memInfo['dream_invested']) < $prizeInfo['prize_min_feeds']*100*$feedAmount) {
                $this->ajaxError("圆梦种子不足,最多可投 ".$memInfo['dream_invest']/(100*$prizeInfo['prize_min_feeds'])." 个");
            }

            if ($prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds']<$feedAmount) {
                $feedAmount = $prizeInfo['feeds_left']/$prizeInfo['prize_min_feeds'];
                logw('  over vote  feed amount = '.$feedAmount);
            }

            //reduce inventory && increase inves time
            $pi['invest_times'] = array('exp','invest_times+1');
            $pi['feeds_left'] = array('exp','feeds_left-'.$feedAmount*$prizeInfo['prize_min_feeds']);
            if (!(M('dream_prizehistory')->where(array('id'=>$prizeInfo['id']))->save($pi))) {
                throw new Exception("更新奖品信息失败 !", 1);
            }

            $prizeMax = $prizeInfo['prize_total_feeds'];
            $prizeLeft = $prizeInfo['feeds_left'];
            $prizeInvested = ($prizeMax - $prizeLeft)/$prizeInfo['prize_min_feeds'];

            $sql = "insert into lzh_dream_invest (`uid`,`mobile`,`money`,`prize_id`,`prize_name`,`prize_type`,`qishu`,`feeds_amount`,`create_time`,`feed_no`) values ";
            //gen feed no
            for ($i = $prizeInvested + 1; $i < $prizeInvested + 1 + $feedAmount; $i++) {
                $data['uid']          = session('u_id');
                $data['mobile']       = $memInfo['user_phone'];
                //total money
                $data['money']        = $prizeInfo['prize_min_feeds']*100*$feedAmount;
                $data['prize_id']     = $prizeInfo['id'];
                $data['prize_name']   = $prizeInfo['prize_name'];
                $data['prize_type']   = $prizeInfo['prize_type'];
                $data['qishu']        = $prizeInfo['qishu'];
                $data['feeds_amount'] = $feedAmount;
                $data['create_time']  = time();
                $data['feed_no']      = 10000000 + intval($i);


                if ($i==($prizeInvested+$feedAmount)) {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."');";
                } elseif ($i == $prizeInvested +1) {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
                } else {
                    $sql .="('".$data['uid']."','".$data['mobile']."','".$data['money']."','".$data['prize_id']."','".$data['prize_name']."','".$data['prize_type']."','".$data['qishu']."','".$data['feeds_amount']."','".$data['create_time']."','".$data['feed_no']."'),";
                }
            }


            $Model = new Model();
            $resultx = $Model->execute($sql);
            if ($resultx === false) {
                throw new Exception("投资种子生成失败!", 1);
            }

            //reduce money
            $mi['dream_invested'] = array('exp','dream_invested+'.$prizeInfo['prize_min_feeds']*100*$feedAmount);
            $res3 = M('members')->where(array('id' => $memInfo['id']))->save($mi);
            if ($res3 === false) {
                throw new Exception("update dream invested failed", 1);
            }

            //commit
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            logw('msg = '.$e->getMessage());
            logw('trace = '.json_encode($e->getTrace()));
            $this->ajaxError($e->getMessage());
        }
    }

    public function ajaxSuccess($msg)
    {
        logw($msg);
        $this->ajaxReturn1(1, $msg);
    }

    public function ajaxError($msg)
    {
        logw($msg);
        $this->ajaxReturn1(0, $msg);
    }

    public function ajaxReturn1($code, $msg)
    {
        $tmp['code'] = $code;
        $tmp['msg'] = $msg;
        exit(json_encode($tmp));
    }

    /**
     *
     * check timetable for activity
     */
    public function checkSchedual()
    {
        $glo = get_global_setting();
        $start = $glo['dream_start_time'];
        $end = $glo['dream_end_time'];
        $status = $glo['dream_status'];

        if ($status == 0) {
        }


        if ($start > time()) {
        }


        if ($end < time()) {
            checkDreamOver();
        }
    }

    /**
     * get default prize
     */
    private function getDefaultPrize()
    {
    }

    private function getInvestSql()
    {
    }

    /**
     * [validateInvestRequest description]
     * @return [type] [description]
     */
    private function validateInvestRequest()
    {
    }

    /**
     * reveal the winner
     * @return  [description]
     */
    public function revealTheWinner()
    {
        $prizeHisId = $_POST['prize_history_id'];
        $winFeedNo = 10000001;

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
                throw new Exception("奖品不存在!", 1);
            }

            logw(' prize id = '.$isFull['id']);
            $count = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time');
            //$counthour = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time/3600');
            $counthour = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 3600 mod 24 ');
            $countmin = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 60 mod 60');
            $countsec = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time mod 60');

            $winFeedNo = $count % $prizeInfo['prize_total_feeds'];
            $to = $counthour + $countmin + $countsec;
            $m = $prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds'];
            logw(' total = '.$to.' m = '.$m);
            $ran = $to%$m;
            //$winFeedNo = 10000000 + rand(1,$prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds']);
            $winFeedNo = 10000001 + $ran;
            //find winner
            $wincon['prize_id'] = $prizeHisId;
            $wincon['feed_no']  = $winFeedNo;
            logw('==win connd = '.json_encode($wincon));
            $winner = M('dream_invest')->where($wincon)->find();
            logw(' winner = '.json_encode($winner));
            if (!$winner) {
                throw new Exception("没有中奖人信息 !", 1);
            }

            //write winner
            $winnerinfo['prize_id']   = $prizeInfo['id'];
            $winnerinfo['prize_name'] = $prizeInfo['prize_name'];
            $winnerinfo['qishu']      = $prizeInfo['qishu'];
            $winnerinfo['uid']        = $winner['uid'];
            $winnerinfo['mobile']     = $winner['mobile'];
            $winnerinfo['money']      = $winner['money'];
            $winnerinfo['feed_no']    = $winFeedNo;
            $winnerinfo['create_time'] = time();
            if (!(M('dream_true')->add($winnerinfo))) {
                throw new Exception("保存中奖人信息失败!", 1);
            }

            $content1 = "尊敬的用户，恭喜您获得平台圆梦活动第{$prizeInfo['qishu']}期{$prizeInfo['prize_name']}，请登录账户了解具体详情或拨打客服专线4006626985";
            sendsms($winner['mobile'], $content1);

            //update winner record
            $full['status'] = 1;
            $full['luck_no'] = $winFeedNo;
            if (!(M('dream_prizehistory')->where(array('id' => $prizeHisId))->save($full))) {
                throw new Exception("更新奖品信息失败 !", 1);
            }

            //check if another prize is accessiable
            $priType        = $prizeInfo['prize_type'];
            $curqishu       = $prizeInfo['qishu'];
            $oricon['type'] = $priType;
            $oricon['default'] = 1;
            $pri = M('dream_prize')->where($oricon)->find();
            logw(' pri  =  '.json_encode($pri));
            $maxqishu = $pri['inventory'];
            if ($maxqishu > $curqishu) {
                logw('xxxxxxxxxxxxxxxxxx');
                //create a new record
                $newPrizeHistory['prize_id']          = $pri['id'];
                $newPrizeHistory['prize_name']        = $pri['name'];
                $newPrizeHistory['prize_min_feeds']   = $pri['min_feeds'];
                $newPrizeHistory['prize_total_feeds'] = $pri['total_feeds'];
                $newPrizeHistory['prize_type']        = $pri['type'];
                $newPrizeHistory['create_time']       = time();
                $newPrizeHistory['feeds_left']        = $newPrizeHistory['prize_total_feeds'];
                $newPrizeHistory['invest_times']      = 0;
                $newPrizeHistory['qishu']             = $curqishu + 1;
                if (!(M('dream_prizehistory')->add($newPrizeHistory))) {
                    throw new Exception("奖品释放失败!", 1);
                }
            }

            //commit
            $model->commit();
        } catch (Exception $e) {
            logw('reveal the winner msg = '.$e->getMessage());
            logw('trace = '.json_encode($e->getTrace()));
            $model->rollback();
            $this->ajaxError($e->getMessage());
        }
    }

    /**
     * [getNextPrize description]
     * @return [type] [description]
     */
    private function getNextPrize()
    {
    }



    /**
     *
     */
    public function activityNotReady()
    {
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5activitynotready");
        } else {
            $this->display("activitynotready");
        }
    }

    /*
     *
     * activity is over
     */
    public function activityIsOver()
    {
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5activityisover");
        } else {
            $this->display("activityisover");
        }
    }

        /*
     *
     * activity is over
     */
    public function activityClose()
    {
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5activityClosed");
        } else {
            $this->display("activityClosed");
        }
    }

    //许愿
    public function wish()
    {
        if (time() >= strtotime("2017-01-19 00:00:00")) {
            $this->ajaxReturn("该活动结束了", "错误", -1);
            exit;
        }
        $gift_id = $_REQUEST["gift_id"];
        $wish_count = $_REQUEST["wish_count"];
        $dataname = C('DB_NAME');
        $db_host = C('DB_HOST');
        $db_user = C('DB_USER');
        $db_pwd = C('DB_PWD');
        $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
        $bdb->beginTransaction();
        $sql1 ="SELECT suo FROM lzh_christmas_gift_lock WHERE id = ? FOR UPDATE";
        $stmt1 = $bdb->prepare($sql1);
        $stmt1->bindParam(1, $gift_id);    //绑定第一个参数值
        $stmt1->execute();
        $christmas = M('christmas_gift');
        $christmas->startTrans();
        $gift = M("christmas_gift")->where(array("id"=>$gift_id))->find();
        if ($gift["is_open"] == 0) {
            $this->ajaxReturn("该礼品赠送完了", "错误", -1);
            exit;
        }
        $user_phone = M("members")->where(array("id"=>$this->uid))->field("user_phone")->find();
        $user_phone = substr($user_phone["user_phone"], -2);
        $luckynum = array();
        $mywishinfo = array();
        if ($gift["gift_set_no"] == 1) {
            $mywishinfo["gift_name"] = "颈部腰部肩部按摩椅垫（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 2) {
            $mywishinfo["gift_name"] = "小米空气净化器（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 3) {
            $mywishinfo["gift_name"] = "佳能EOS 700D 单反套机（".$gift["gift_no"]."期）";
        } elseif ($gift["gift_set_no"] == 4) {
            $mywishinfo["gift_name"] = "iPhone 7 （128g颜色随机）（".$gift["gift_no"]."期）";
        }
        $startime = strtotime("2016-12-13 00:00:00");
        $endtime  = strtotime("2017-01-18 23:59:59");
        $where["investor_uid"] = $this->uid;
        $where["add_time"] = array("between",$startime.",".$endtime);
        $invest_money = M("borrow_investor")->where($where)->sum("investor_capital");
        $all_xu_count = floor($invest_money/1000);
        $used_xu_count = M("christmas_number")->where(array("uid"=>$this->uid))->count();
        $xu_count = $all_xu_count - $used_xu_count; //可许愿次数
        if ($xu_count<=0) {
            $this->ajaxReturn("许愿次数已用完", "错误", -1);
            exit;
        }
        if (($wish_count+$gift["send_number"]) > $gift["gift_number"]) {
            //许愿次数大于当前剩余次数
            $this->ajaxReturn("许愿次数不足请重新输入", "错误", -1);
            exit;
        } else {
            for ($i=0; $i <$wish_count ; $i++) {
                $luckynum[$i]["gift_id"] = $gift_id;
                $luckynum[$i]["uid"] = $this->uid;
                $luckynum[$i]["xu_number"] = mt_rand(1000, 9999).$user_phone;
                $luckynum[$i]["add_time"] = time();
                if (($wish_count-1) == $i) {
                    $mywishinfo["luckynum"] .= $luckynum[$i]["xu_number"];
                } else {
                    $mywishinfo["luckynum"] .= $luckynum[$i]["xu_number"]."，";
                }
            }
            M("christmas_number")->addAll($luckynum);
            M("christmas_gift")->where(array("id"=>$gift_id))->save(array("send_number"=>$wish_count+$gift["send_number"]));
        }

        //开奖
        if (($wish_count+$gift["send_number"]) == $gift["gift_number"]) {
            $lucksum = M("christmas_number")->where(array("gift_id"=>$gift_id))->sum("xu_number");
            $lucyavg = $lucksum/$gift["gift_number"];
            $lucklist = M("christmas_number")->where(array("gift_id"=>$gift_id))->select();
            foreach ($lucklist as $key => $value) {
                $lucky_data["number_poor"] = abs($lucyavg-$value["xu_number"]);
                M("christmas_number")->where(array("xu_number"=>$value["xu_number"],"gift_id"=>$gift_id))->save($lucky_data);
            }
            $luck_usr = M("christmas_number")->where(array("gift_id"=>$gift_id))->order("number_poor ASC")->find();
            M("christmas_number")->where(array("xu_number"=>$luck_usr["xu_number"],"gift_id"=>$gift_id))->save(array("is_zhong"=>1));
            $have_data["uid"] = $luck_usr["uid"];
            $have_data["gift_no"] = $gift["gift_no"];
            $have_data["gift_id"] = $gift["id"];
            $have_data["have_number"] = $luck_usr["xu_number"];
            $have_data["gift_set_no"] = $gift["gift_set_no"];
            $have_data["is_xu"] = 1;
            M("christmas_have")->add($have_data);
            $gift_up["have_uid"] = $luck_usr["uid"];
            $gift_up["have_number"] = $luck_usr["xu_number"];
            $gift_up["have_poor"] = $luck_usr["number_poor"];
            $gift_up["avg_number"] = $lucyavg;
            M("christmas_gift")->where(array("id"=>$gift["id"]))->save($gift_up);
            $user = M("members")->where("id=".$luck_usr["uid"])->find();
            $smscontent = "尊敬的链金所用户！恭喜您获得链金所新年许愿活动第".$gift["gift_no"]."期".$gift["gift_name"]."1台，您可登录活动页查看礼物，如有疑问请与客服中心联系400-6626-985。";
            sendsms($user["user_phone"], $smscontent);
            $new_gift["gift_set_no"] = $gift["gift_set_no"];
            $new_gift["gift_no"] = $gift["gift_no"]+1;
            $new_gift["gift_name"] = $gift["gift_name"];
            $new_gift["gift_number"] = $gift["gift_number"];
            $new_gift["is_open"] = 1;
            M("christmas_gift")->add($new_gift);
            M("christmas_gift_lock")->add(array("suo"=>0));
        }
        $christmas->commit();
        $this->ajaxReturn($mywishinfo);
    }

    public function recommend()
    {
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"推荐有奖");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5recommend");
        } else {
            $this->display();
        }
    }

    public function report()
    {
        if ($this->is_mobile()) {
            $simple_header_info=array("url"=>"/M/index","title"=>"运营报告");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5report");
        } else {
            $this->display();
        }
    }

    /**
     * 五月活动
     * @return [type] [description]
     */
    public function theMayActive()
    {
        //一重礼
        //邀请链接
        $recommend_url = null;
        //总共的体验金张数
        $total_count = 0;
        //剩余的体验金张数
        $remaining_count = 0;
        //剩余的体验金金额
        $remaining_money = 0;
        //抽奖总次数
        $total_lucky = 0;
        //抽奖剩余次数
        $remaining_lucky = 0;

        if ($this->uid) {
            $recommend_url = "https://".$_SERVER['HTTP_HOST']."/member/common/register?invite=".$this->uid;
            //查询体验金
            $recommend_info = M("recommend_first")->where(array("recommend_uid"=>$this->uid))->find();
            if ($recommend_info) {
                $total_count = intval($recommend_info['experience_money']/1000);
                $remaining_money = $recommend_info['experience_money']-$recommend_info['used_money'];
                $remaining_count = intval($remaining_money/1000);
            }

            //查询抽奖次数
            $user_lucky = M("recommend_lucky")->where(array("uid"=>$this->uid))->find();
            $total_lucky = $user_lucky["total_count"]?$user_lucky["total_count"]:0;
            $remaining_lucky = $user_lucky["total_count"]-$user_lucky["used_count"];
            if ($remaining_lucky<0) {
                $remaining_lucky = 0;
            }

            //我的抽奖记录
            $my_winner = M("recommend_winner rw")->join("lzh_recommend_prize rp ON rp.id = rw.prize_id")->where(array("uid"=>$this->uid,"prize_id"=>array("neq",9)))->field("rp.prize_name")->order("rw.add_time DESC")->select();
        }

        //中奖名单
        $winner_list = M("recommend_winner rw")->join("lzh_members m ON m.id = rw.uid")->join("lzh_recommend_prize rp ON rp.id = rw.prize_id")->where("rw.prize_id <> 9")->field("m.user_phone,rp.prize_name")->order("rw.add_time DESC")->limit(10)->select();
        foreach ($winner_list as $key => $value) {
            $winner_list[$key]["user_phone"] = $this->mask_name($value["user_phone"]);
        }

        $protocol = (!empty($_SERVER[HTTPS]) && $_SERVER[HTTPS] !== off || $_SERVER[SERVER_PORT] == 443) ? "https://" : "http://";

        if (strtotime(C("THE_MAY_ACTIVE.start_time"))<=time() && strtotime(C("THE_MAY_ACTIVE.end_time"))>=time()) {
            $active_open = 0;
        } else {
            $active_open = 1;
        }

        //赋值
        $this->assign("active_open", $active_open);
        $this->assign("img_url", $protocol.$_SERVER[HTTP_HOST]."/Style/H/images/recomactive/themayrecommend.jpg");
        $this->assign("recommend_url", $recommend_url);
        $this->assign("total_count", $total_count);
        $this->assign("remaining_count", $remaining_count);
        $this->assign("remaining_money", $remaining_money);
        $this->assign("total_lucky", $total_lucky);
        $this->assign("remaining_lucky", $remaining_lucky);
        $this->assign("winner_list", $winner_list);
        $this->assign("my_winner", $my_winner);
        $this->assign('empty','<li class="recom-empty">暂无数据！</li>');

        //判断输出模版
        if ($this->is_mobile()) {
            session("login_next", "/Home/active/h5themayactive");
            $simple_header_info=array("url"=>"/M/index","title"=>"5月活动");
            $this->weixin_token();
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5themayactive");
        } else {
            session("login_next", "/Home/active/themayactive");
            $this->display();
        }
    }

    /**
     * 活动体验标
     * @return [type] [description]
     */
    public function invest_borrow()
    {
        if (strtotime(C("THE_MAY_ACTIVE.start_time")) > time()) {
            $this->ajaxReturn("活动还未开始", "活动还未开始", "100");
            exit;
        }
        if (strtotime(C("THE_MAY_ACTIVE.end_time")) < time()) {
            $this->ajaxReturn("活动结束了", "活动结束了", "101");
            exit;
        }
        if ($this->uid) {
            $real_info = M("members_status")->where(array("uid"=>$this->uid))->field("id_status")->find();
            $is_set_pwd = checkissetpaypwd($this->uid);
            if ($real_info["id_status"] == 0) {
                if (!$this->is_mobile()) {
                    $real_url = "/member/verify?id=1#fragment-1";
                } else {
                    $real_url = "/M/user/verify.html";
                }
                $this->ajaxReturn($real_url, '您还没有实名验证哦！快快实名验证，领取奖励吧！', '-3');
            }

            if ($is_set_pwd["is_set_paypass"] != 'Y') {
                if (!$this->is_mobile()) {
                    $pwd_url = "/member/promotion/checkissetpwd?i=2";
                } else {
                    $pwd_url = "/M/user/sinapass.html";
                }

                $this->ajaxReturn($pwd_url, '您还没有设置支付密码哦！快快设置支付密码，领取奖励吧！', '-4');
            }
            $type = trim($_POST["type"]);
            switch ($type) {
                //确认提示
                case 'confirm':
                    //查询体验金
                    $recommend_info = M("recommend_first")->where(array("recommend_uid"=>$this->uid))->find();
                    if($recommend_info["is_freeze"] == 1){
                        $this->ajaxReturn('', '账号异常，请联系客服。', '-7');
                    }
                    $remaining_money = $recommend_info['experience_money']-$recommend_info['used_money'];
                    $remaining_count = intval($remaining_money/1000);
                    session("token",rand("100000","999999"));
                    $data["remaining_money"] = $remaining_money?$remaining_money:'0.00';
                    $data["remaining_count"] = $remaining_count?$remaining_count:0;
                    $data["token"] = session("token");
                    $this->ajaxReturn($data, '', 0);
                    break;

                //投标
                case 'invest':
                    $token = $_POST["token"];
                    if($token != session("token")){
                        $this->ajaxReturn('', '请刷新页面', '-6');
                    }
                    $recommend_info = M("recommend_first")->where(array("recommend_uid"=>$this->uid))->find();
                    if($recommend_info["is_freeze"] == 1){
                        $this->ajaxReturn('', '账号异常，请联系客服。', '-7');
                    }
                    $remaining_money = $recommend_info['experience_money']-$recommend_info['used_money'];
                    $remaining_count = intval($remaining_money/1000);
                    if($remaining_count <= 0){
                        $this->ajaxReturn('', '体验金用完了', '-5');
                    }

                    $money = intval($_POST["invest_money"]);
                    //获取标的信息
                    $binfo = M("borrow_info_experience")->find(2);
                    //处理投标信息
                    $b_data['borrow_id'] = 2;
                    $b_data['investor_uid'] = $this->uid;
                    $b_data['capital'] = $money;
                    $interest = getFloatValue($binfo['borrow_interest_rate']/100/360*$binfo['borrow_duration']*$money, 2);
                    $b_data['interest'] = round($interest, 2);
                    $b_data['status'] = 1;
                    $b_data['deadline'] = strtotime(date("Y-m-d 23:59:59", strtotime("+4 days")));
                    $b_data['add_time'] = time();
                    M("investor_detail_experience")->add($b_data);
                    //更新标的信息
                    $binfo_data['has_borrow'] = $binfo['has_borrow']+$money;
                    $binfo_data['borrow_times'] = $binfo['borrow_times'] + 1;
                    M("borrow_info_experience")->where("id = 2")->save($binfo_data);
                    //处理使用结果
                    $recommend_info = M("recommend_first")->where(array("recommend_uid"=>$this->uid))->find();
                    $recommend_data["used_money"] = $recommend_info["used_money"] + $money;
                    M("recommend_first")->where(array("recommend_uid"=>$this->uid))->save($recommend_data);
                    session("token",null);
                    $this->ajaxReturn('', '', 0);
                    break;
            }
        } else {
            $this->ajaxReturn('', '', '-1');
            exit;
        }
    }

    /**
     * 二维码生成
     * @return [type] [description]
     */
    public function create_qr()
    {
        $url="https://".$_SERVER['HTTP_HOST']."/member/common/register?invite=".$this->uid;
        import("@.phpqrcode.phpqrcode");
        ob_clean();
        //生成二维码图片
        QRcode::png($url, '/tmp/'.$this->uid.'_qrcode.png', 'L', 10, 2);
        $logo = 'qrcode-logo.png';//准备好的logo图片
        $QR = '/tmp/'.$this->uid.'_qrcode.png';//已经生成的原始二维码图
        if ($logo !== false) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);//二维码图片宽度
             $QR_height = imagesy($QR);//二维码图片高度
             $logo_width = imagesx($logo);//logo图片宽度
             $logo_height = imagesy($logo);//logo图片高度
             $logo_qr_width = $QR_width / 6;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
             //重新组合图片并调整大小
             imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
             $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片
        Header("Content-type: image/png");
        ImagePng($QR);
    }

    public function themayprize()
    {
        if (strtotime(C("THE_MAY_ACTIVE.start_time")) > time()) {
            $this->ajaxReturn("活动还未开始", "活动还未开始", "100");
            exit;
        }
        if (strtotime(C("THE_MAY_ACTIVE.end_time")) < time()) {
            $this->ajaxReturn("活动结束了", "活动结束了", "101");
            exit;
        }
        if (!$this->uid) {
            $this->ajaxReturn('', '', '-1');
            exit;
        }
        $prize_list = M("recommend_prize")->where("active = 0 and send_count < prize_count")->select();
        $user_lucky = M("recommend_lucky")->where(array("uid"=>$this->uid))->find();
        if (($user_lucky["total_count"]-$user_lucky["used_count"]) > 0) {
            foreach ($prize_list as $key => $value) {
                $arr[$value["id"]] = $value["prize_probability"];
            }
            $p_id = $this->get_rand($arr);
            $p_data = M("recommend_prize")->where(array("id"=>$p_id))->find();
            $data["uid"] = $this->uid;
            $data["prize_id"] = $p_id;
            $data["add_time"] = time();
            M("recommend_winner")->add($data);
            if ($p_id == 4 || $p_id == 5 || $p_id == 6 || $p_id == 7) {
                $user_info = M("members")->where(array("id"=>$this->uid))->find();
                if ($p_id == 4) {
                    $c_data['money'] = 1;
                    $c_data['type'] = 3;
                    $c_data['use_money'] = 0;
                } elseif ($p_id == 5) {
                    $c_data['money'] = 50;
                    $c_data['type'] = 1;
                    $c_data['use_money'] = 5000;
                } elseif ($p_id == 6) {
                    $c_data['money'] = 20;
                    $c_data['type'] = 1;
                    $c_data['use_money'] = 2000;
                } elseif ($p_id == 7) {
                    $c_data['money'] = 10;
                    $c_data['type'] = 1;
                    $c_data['use_money'] = 1000;
                }
                $c_data['user_phone'] = $user_info['user_phone'];
                $c_data['endtime'] = strtotime(date("Y-m-d 23:59:59", strtotime("+14 days")));
                $c_data['status'] = 0;
                $c_data['addtime'] = date("Y-m-d H:i:s",time());
                $c_data["name"] = "五月活动";
                $c_data['serial_number'] = date('YmdHis') . mt_rand(100000, 999999);
                M("coupons")->add($c_data);
            }
            $lucky_data["used_count"] = $user_lucky["used_count"] + 1;
            M("recommend_lucky")->where(array("uid"=>$this->uid))->save($lucky_data);

            $p_data["send_count"] = $p_data["send_count"]+1;
            M("recommend_prize")->where(array("id"=>$p_id))->save($p_data);

            $user = M("members")->where(array("id"=>$this->uid))->field("user_phone")->find();
            $rs_data["p_id"] = $p_id;
            $rs_data["user_phone"] = $this->mask_name($user["user_phone"]);
            $user_lucky_info = M("recommend_lucky")->where(array("uid"=>$this->uid))->find();
            $rs_data["total_count"] = $user_lucky_info["total_count"];
            $rs_data["remain_count"] = $user_lucky_info["total_count"]-$user_lucky_info["used_count"];
            $this->ajaxReturn($rs_data, "", 0);
        } else {
            $this->ajaxReturn("您的抽奖次数已用完", "", "-2");
        }
    }

    /*
     *周年庆
    */
    public function zhounian17(){
        //查询周年有奖标的
        $searchMap = array();
        $searchMap['b.borrow_status'] = array("in", '2,4,6,7,9');
        $searchMap['b.test'] = 0;//不是测试标
        $searchMap['b.is_zhounianbiao'] = 1;//不是测试标
        $parm = array();
        $parm['map'] = $searchMap;
        $parm['limit'] = 3;
        $parm['orderby']="b.borrow_status ASC,b.id DESC";
        $Borrow = getBorrowList($parm);
        $this->assign("Borrow", $Borrow);
        $this->assign("Borrowcount", count($Borrow["list"]));

        //周年庆幸运儿
        $borrow_list = M("borrow_info b")->where($searchMap)->field("id,borrow_name,borrow_status")->order("b.id desc")->select();
        foreach ($borrow_list as $k => $b) {
            $borrow_list[$k]["borrow_id"] = borrowidlayout1($b["id"]);
            if($b["borrow_status"] < 6){
                $borrow_list[$k]["rich_man"] = "统计中,待公布";
                $borrow_list[$k]["first_man"] = "统计中,待公布";
                $borrow_list[$k]["last_man"] = "统计中,待公布";
            }else{
                //土豪财主
                $rich_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON mi.uid = m.id")
                            ->where("bi.borrow_id = ".$b['id'])
                            ->order("money desc,bi.add_time desc")
                            ->field("m.id,m.user_name,m.user_phone,sum(bi.investor_capital) as money,mi.real_name,mi.idcard")
                            ->group("m.id")
                            ->find();
                if($rich_man){
                    $borrow_list[$k]["rich_man"] = mb_substr($rich_man["real_name"],0,1,'utf-8').$this->get_xingbie($rich_man["idcard"])."：".$this->mask_name($rich_man["user_phone"]);
                }else{
                    $borrow_list[$k]["rich_man"] = "很遗憾！土豪财主奖未形成";
                }
                //抢标先锋
                    $first_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON mi.uid = m.id")
                            ->where("bi.investor_capital >= 50000 AND bi.borrow_id = ".$b['id'])
                            ->order("bi.add_time asc")
                            ->field("m.id,m.user_name,m.user_phone,mi.real_name,mi.idcard")
                            ->find();
                
                if($first_man && $rich_man["id"] != $first_man["id"]){
                    $borrow_list[$k]["first_man"] = mb_substr($first_man["real_name"],0,1,'utf-8').$this->get_xingbie($first_man["idcard"])."：".$this->mask_name($first_man["user_phone"]);
                }else{
                    $borrow_list[$k]["first_man"] = "很遗憾！抢标先锋奖未形成";
                }
                //完美收官
                    $last_man = M("borrow_investor bi")
                            ->join("lzh_members m ON m.id = bi.investor_uid")
                            ->join("lzh_member_info mi ON mi.uid = m.id")
                            ->where("bi.borrow_id = ".$b['id'])
                            ->order("bi.add_time desc")
                            ->field("m.id,m.user_name,m.user_phone,mi.real_name,mi.idcard")
                            ->find();
                
                if($last_man && $rich_man["id"] != $last_man["id"] && $first_man["id"] != $last_man["id"]){
                    $borrow_list[$k]["last_man"] = mb_substr($last_man["real_name"],0,1,'utf-8').$this->get_xingbie($last_man["idcard"])."：".$this->mask_name($last_man["user_phone"]);
                }else{
                    $borrow_list[$k]["last_man"] = "很遗憾！完美收官奖未形成";
                }
            }
        }

        $this->assign("borrow_list",$borrow_list);
        // print_r($borrow_list);die;
        //判断输出模版
        if ($this->is_mobile()) {
            session("login_next", "/Home/active/h5zhounian17");
            $simple_header_info=array("url"=>"/M/index","title"=>"热烈庆祝链金所成立两周年");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5zhounian17");
        } else {
            session("login_next", "/Home/active/zhounian17");
            $this->display();
        }
    }

    /*
     *月底家具大冲关
    */
    public function furniturewin(){
        $start_time = strtotime(C("THE_OCTOBER_ACTIVE.start_time"));
        $end_time = strtotime(C("THE_OCTOBER_ACTIVE.end_time"));
        
        $prize_arr = [
            8 => ['min' => 800001, 'tips' => '龙行天下（价值6688元）'],
            7 => ['min' => 500001, 'max' => 800000, 'tips' => '生财雄狮（价值3588元）'],
            6 => ['min' => 300001, 'max' => 500000, 'tips' => '登高虎（价值1288元）'],
            5 => ['min' => 100001, 'max' => 300000, 'tips' => '金钱豹（价值618元）'],
            4 => ['min' => 50001, 'max' => 100000, 'tips' => '旺势神牛（价值308元）'],
            3 => ['min' => 30001, 'max' => 50000, 'tips' => '如意象（价值168元）'],
            2 => ['min' => 10001, 'max' => 30000, 'tips' => '招财狗（价值68元）'],
            1 => ['min' => 5000, 'max' => 10000, 'tips' => '吉祥葫芦（价值38元）']
        ];
        
        $model = M('borrow_investor');
        if (!empty($this->uid)) {
            $con['investor_uid'] = $this->uid;
            $con['add_time'] = ['between', [$start_time, $end_time]];
            $my_total_invest = $model->where($con)->sum('investor_capital');
            $this->assign('my_total_invest', !empty($my_total_invest) ? $my_total_invest : 0);
        }
        
        $my_prize = '您还未获得家具';
        foreach ($prize_arr as $key => &$value)
        {
            static $now_step = '';
            $field = 'investor_uid,sum(investor_capital) as sum_invest';
            if (isset($value['max']))
            {
                if ($my_total_invest >= $value['min'] && $my_total_invest <= $value['max'])
                {
                    $my_prize = $value['tips'];
                }
                if (!empty($this->uid) && $now_step == '' && $my_total_invest >= $value['max'])
                {
                    $now_step = abs(8 - $key);
                }
                $having = 'sum_invest between '.$value['min'].' and '.$value['max'];
            }
            else
            {
                if ($my_total_invest >= $value['min'])
                {
                    $my_prize = $value['tips'];
                    $now_step = abs(8 - $key);
                }
                $having = 'sum_invest >= '.$value['min'];
            }
            
            $wh['add_time'] = ['between', [$start_time, $end_time]];
            $rank_list = $model->where($wh)->field($field)->group('investor_uid')->having($having)->order('sum_invest desc')->select();
            
            if (count($rank_list) < 3)
            {
                $k = count($rank_list);
                do
                {
                    $rank_list[$k] = ['investor_uid' => '', 'sum_invest' => '', 'invest_name' => '', 'is_me' => 0];
                    $k++;
                } while ($k < 3);
            }
            
            $m_model = M('members');
            $mi_model = M('member_info');
            foreach ($rank_list as &$val)
            {
                $val['is_me'] = !empty($this->uid) && $this->uid == $val['investor_uid'] ? 1 : 0;
                $user_phone = $m_model->where(['id' => $val['investor_uid']])->getField('user_phone');
                if (empty($user_phone)) {
                    $user_phone = $mi_model->where(['uid' => $val['investor_uid']])->getField('cell_phone');
                }
                $val['invest_name'] = substr_replace($user_phone, '*****', 3, 5);
            }
            
            $value['list'] = $rank_list;
        }
        
        $this->assign('my_prize', $my_prize);
        $this->assign('uid', $this->uid);
        $this->assign('list', $prize_arr);
        
        //判断输出模版
        if ($this->is_mobile())
        {
            $this->assign('now_step', 8 - (!empty($now_step) ? $now_step : 8));
            session("login_next", "/Home/active/h5furniturewin");
            $simple_header_info = ["url" => "/M/index", "title" => "月底家具大冲关"];
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5furniturewin");
        }
        else
        {
            $this->assign('now_step', !empty($now_step) ? $now_step : 8);
            session("login_next", "/Home/active/furniturewin");
            $this->display();
        }
    }
    
    /*
     * 经典的概率算法，
     * $proArr是一个预先设置的数组，
     * 假设数组为：array(100,200,300，400)，
     * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内，
     * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
     * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
     * 这样 筛选到最终，总会有一个数满足要求。
     * 就相当于去一个箱子里摸东西，
     * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
     * 这个算法简单，而且效率非常 高，
     * 关键是这个算法已在我们以前的项目中有应用，尤其是大数据量的项目中效率非常棒。
     */
    private function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }

    private function is_mobile()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }

    /**
     * 打码
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    private function mask_name($str)
    {
        $count=mb_strlen($str, "UTF-8");
        if ($count==2) {
            $name=mb_substr($str, 0, 1, "UTF-8")."*";
        } else {
            $num=$count-7;
            $xin="";
            for ($i=0;$i<$num;$i++) {
                $xin.="*";
            }
            $name=mb_substr($str, 0, 3, "UTF-8").$xin.mb_substr($str, $count-4, 4, "UTF-8");
        }
        return $name;
    }

    private function get_xingbie($cid) {  
        //根据身份证号，自动返回性别  
        $sexint = (int)substr($cid,16,1);  
        return $sexint % 2 === 0 ? '女士' : '先生';  
    }

    public function weixin_token()
        {
            $token = M("weixin_token")->where(array("type"=>1,"expires_time"=>array("gt",time())))->find();
            $access_token = null;
            if ($token) {
                $access_token = $token["content"];
            } else {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C("WEIXIN.appid")."&secret=".C("WEIXIN.secret");
                $rs = curl_get($url);
                log::write("微信access_token:".$rs);
                $get_token = json_decode($rs, true);
                $data['content'] = $get_token["access_token"];
                $data['type'] = 1;
                $data['expires_time'] = time()+7200;
                M("weixin_token")->add($data);
                $access_token = $get_token["access_token"];
            }
            $ticket_info = M("weixin_token")->where(array("type"=>2,"expires_time"=>array("gt",time())))->find();
            $ticket = null;
            if ($ticket_info) {
                $ticket = $ticket_info["content"];
            } else {
                $ticket_url  = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
                $rs = curl_get($ticket_url);
                log::write("微信ticket:".$rs);
                $get_ticket = json_decode($rs, true);
                $data['content'] = $get_ticket["ticket"];
                $data['type'] = 2;
                $data['expires_time'] = time()+7200;
                M("weixin_token")->add($data);
                $ticket = $get_ticket["ticket"];
            }

            $noncestr ='wwwccfaxcn123321lianjinsuoshenzhen';
            $timestamp = time();
            $protocol = (!empty($_SERVER[HTTPS]) && $_SERVER[HTTPS] !== off || $_SERVER[SERVER_PORT] == 443) ? "https://" : "http://";
            $url = $protocol.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
            $string="jsapi_ticket=".$ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
            log::write("微信签名字符串:".$string);
            $signature = sha1($string);
            $this->assign("noncestr", $noncestr);
            $this->assign("timestamp", $timestamp);
            $this->assign("signature", $signature);
            $this->assign("img_url", $protocol.$_SERVER[HTTP_HOST]."/Style/H/images/recomactive/themayrecommend.jpg");
        }
}