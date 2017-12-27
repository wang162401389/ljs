<?php

/**
 * pc端首页
 * Class IndexAction
 */
class IndexAction extends HCommonAction {
    public function index(){
        $title = "『链金所官网』-网络贷款,网络借贷,网络理财,P2P理财,P2P网贷平台";
        $keyword = "网络贷款,网络借贷,网络理财,P2P理财,P2P网贷,网贷平台";
        $description = "链金所是国内领先的集网络贷款,网络借贷,网络理财,P2P理财等业务的P2P网贷平台.为有资金需求的借款人和网络理财需求的投资者搭建一个安全,高效,便捷的一站式P2P网贷平台";
        $this->assign("title", $title);
        $this->assign("keyword", $keyword);
        $this->assign("descript", $description);
        $is_close = cookie('upclose');
        if($is_close == null || $is_close != 'yes'){
            $is_close = 'no';
        }
        $this->assign('close', $is_close);
        $this->assign('xieyi', session('xieyi'));
      

        /**************** 轮播图优化************************/
        $id = 4;
        $stype = "home_ad".$id;
        if(!S($stype)){
            $condition['id'] = $id;
            $adlist = M('ad')->field('ad_type,content')->where($condition)->find();
            if($adlist['ad_type'] == 1){
                $adlist['content'] = unserialize($adlist['content']);
            }
            S($stype, $adlist, 864000);//缓存10天
        }else{
            $adlist = S($stype);
        }
        $this->assign("adlist", $adlist['content']);
        
        $parm['limit'] = 4;
        
		$parm['type_id'] = 9;//网站公告
		$this->assign("noticeList", getArticleList($parm));

        $parm['type_id'] = 2;//新闻媒体
        $this->assign("noticephoto", getArticleList($parm));

        $parm['type_id'] = 45;//公司动态
        $this->assign("conpany", getArticleList($parm));

        $parm['type_id'] = 47;//理财知识
        $this->assign("licai", getArticleList($parm));

        /********已合规平稳运营 累计注册人数 不良项目总金额********************/
        // 注册人数统计
        $now = time();
        import("@.Phpconectjava.usersapi");
        $users = new usersapi();
        $java_data["platform_source"] = 0;
        $java_result = $users->getmembercount($java_data);
        $count_list = json_decode($java_result, true);
        $resultText = json_decode($count_list["resultText"], true);
        $regnumber = 0;
        foreach ($resultText["Logincntlist"] as $key => $value) {
            $regnumber += $value["logincnt"];
        }
        $onlinetime = "2015-10-7";
        // 当前时间
        $compliance = ($now - strtotime($onlinetime)) / (24 *3600);
        $badset = get_global_setting();
        $badnessmony = $badset['badnessmony'];    //不良项目总金额 后台配置

        $this->assign("compliance", intval($compliance));
        $this->assign("regnumber", $regnumber);
        $this->assign("badnessmony", $badnessmony);
        /********************************** **/

        /********新手标********************/
        $searchMap = array();
        $searchMap['b.borrow_status']       = array("in", '2');
        $searchMap['b.is_beginnercontract'] = 1;
        $searchMap['b.test']                = 0;
        $zparm = array();
        $zparm['map'] = $searchMap;
        $zparm['limit'] = 1;
        $zparm['orderby']="b.borrow_status ASC,b.id DESC";
        $xsBorrow = getBorrowList($zparm);
        $this->assign("xsBorrow", $xsBorrow);
        $this->assign("xsBorrowcount", count($xsBorrow));
        /************************************/

        /********推荐项目********************/
        $searchMap = array();
        $searchMap['b.borrow_status']       = array("in", '2,4,6,7');
        $searchMap['b.is_beginnercontract'] = 0;
        $searchMap['b.test']                = 0;

        $zparm = array();
        $zparm['map'] = $searchMap;
        $zparm['limit'] = 3;
        $zparm['orderby'] = "b.borrow_status ASC,b.id DESC";
        $tuijianlist = getBorrowList($zparm, 2);
        $this->assign("tuijianlist", $tuijianlist);
        /********************************** **/

        /********待上标********************/
        $searchMap = array();
        $searchMap['b.borrow_status'] = 8;
        $searchMap['b.is_beginnercontract'] = 0;
        $searchMap['b.test'] = 0;

        $zparm = array();
        $zparm['map'] = $searchMap;
        $zparm['limit'] = 2;
        $zparm['orderby'] = "b.add_time ASC,b.borrow_money DESC";
        $listBorrow = getBorrowList($zparm);
        $this->assign("waitBorrow", $listBorrow);
        $this->assign("waitBorrowcount", count($listBorrow["list"]));
        /******************** ****************/

        /********质金链（保）********************/
        $searchMap = array();
        $searchMap['b.borrow_status']       = array("in", '2,4,6,7');
        $searchMap['b.product_type']        = 10;
        $searchMap['b.is_beginnercontract'] = 0;
        $searchMap['b.test']                = 0;
        $zparm = array();
        $zparm['map'] = $searchMap;
        $zparm['limit'] = 2;
        $zparm['orderby']="b.borrow_status ASC,b.id DESC";
        $zbBorrow = getBorrowList($zparm);
        $this->assign("zbBorrow", $zbBorrow);
        $this->assign("zbBorrowcount", count($zbBorrow));
        /************************************/
        
        /********质金链********************/
        $searchMap = array();
        $searchMap['b.borrow_status'] = array("in", '2,4,6,7');
        $searchMap['b.product_type'] = array('in', '1,2,3');
        $searchMap['b.is_beginnercontract'] = 0;
        $searchMap['b.test'] = 0;

        $zparm = array();
        $zparm['map'] = $searchMap;
        $zparm['limit'] = 2;
        $zparm['orderby']="b.borrow_status ASC,b.id DESC";
        $listBorrow = getBorrowList($zparm);
        $this->assign("listBorrow", $listBorrow);
        $this->assign("listBorrowcount", count($listBorrow));
        /********************************** **/

        /********保金链********************/
        $bsearchMap = array();
        $bsearchMap['b.borrow_status'] = array("in", '2,4,6,7');
        $bsearchMap['b.product_type'] = '8';
        $bsearchMap['b.is_beginnercontract'] = 0;
        $bsearchMap['b.test'] = 0;

        $bparm = array();
        $bparm['map'] = $bsearchMap;
        $bparm['limit'] = 2;
        $bparm['orderby'] = "b.borrow_status ASC,b.id DESC";
        $blistBorrow = getBorrowList($bparm);
        $this->assign("blistBorrow", $blistBorrow);
        $this->assign("blistBorrowcount", count($blistBorrow));
        /*********************************/

        //优金链
        $ysearchMap = array();
        $ysearchMap['b.borrow_status'] = array("in", '2,4,6,7');
        $ysearchMap['b.product_type'] = '7';
        $ysearchMap['b.is_beginnercontract'] = 0;
        $ysearchMap['b.test'] = 0;

        $yparm = array();
        $yparm['map'] = $ysearchMap;
        $yparm['limit'] = 2;
        $yparm['orderby'] = "b.borrow_status ASC,b.id DESC";
        $ylistBorrow = getBorrowList($yparm);
        $this->assign("ylistBorrow", $ylistBorrow);
        $this->assign("ylistBorrowcount", count($ylistBorrow));
       /********************************************/

        /***********融金链************************/
        $rsearchMap = array();
        $rsearchMap['b.borrow_status'] = array("in", '2,4,6,7');
        $rsearchMap['b.product_type'] = '4';
        $rsearchMap['b.is_beginnercontract'] = 0;
        $rsearchMap['b.test'] = 0;

        $rparm = array();
        $rparm['map'] = $rsearchMap;
        $rparm['limit'] = 2;
        $rparm['orderby'] = "b.borrow_status ASC,b.id DESC";
        $rlistBorrow = getBorrowList($rparm);
        $this->assign("rlistBorrow", $rlistBorrow);
        $this->assign("rlistBorrowcount", count($rlistBorrow));
        /************* *******************************/

        /***************信金链******************************/
        $xsearchMap = array();
        $xsearchMap['b.borrow_status'] = array("in", '2,4,6,7');
        $xsearchMap['b.product_type'] = array('in', '5,6');
        $xsearchMap['b.is_beginnercontract'] = 0;
        $xsearchMap['b.test'] = 0;
        $xparm = array();
        $xparm['map'] = $xsearchMap;
        $xparm['limit'] = 2;
        $xparm['orderby'] = "b.borrow_status ASC,b.id DESC";
        $xlistFBorrow = getBorrowList($xparm);
        $this->assign("xinBorrow", $xlistFBorrow);
        $this->assign("xinBorrowcount", count($xlistFBorrow));
        /***************** *********************************/

        /************************体验金20161028 进度问题取3的模是25的倍数，25%，50%，75% *****************************/
        // $expinfo = M('borrow_info_experience')->field('id,borrow_interest_rate,borrow_duration_txt')->find();
        // $this->assign('expmoney', C('EXPERIENCE_MONEY'));
        // $this->assign('expinfo', $expinfo);
        // $count = M("investor_detail_experience")->count();
        // $this->assign("jindu", 25 * ($count % 3 + 1));
        /****************************************************************************************************/

        /*******************************************债券转让******************************************************/
        // $field = "b.id,b.borrow_name,b.borrow_money,bi.borrow_interest_rate,b.borrow_duration_txt,b.borrow_status,b.has_borrow";
        // $zhai_where = "(b.borrow_status in (4,6,7)) OR ( b.borrow_status = 2 AND b.collect_time >= ".$now.")";
        // $join = "lzh_borrow_info bi ON bi.id = b.old_borrow_id";
        // $zhaiquanlistcount = M('debt_borrow_info b')->join($join)->where($zhai_where)->count();
        // if ($zhaiquanlistcount) {
        //     $zhaiquanlist = M('debt_borrow_info b')->join($join)->field($field)->where($zhai_where)->order("b.borrow_status ASC,b.id DESC")->limit("2")->select();
        //     foreach ($zhaiquanlist as $k => $v){
        //         $zhaiquanlist[$k]['progress'] = getFloatValue($v['has_borrow'] / $v['borrow_money'] * 100, 2);
        //         $zhaiquanlist[$k]["borrow_money"] = number_format($v["borrow_money"], 2, '.', '');
        //     }
        // }
        // $this->assign("zhaiquanlist", $zhaiquanlist);
        // $this->assign("zhaiquanlistcount", $zhaiquanlistcount);
        /*******************************************债权转让end***************************************************/

        $borrowInvestor = D('borrow_investor');
        $borrow_Info = M("borrow_info");
        $members = M('members');
        $investorDetail = M('investor_detail');
        $memberMoney = M("member_money");
        $memberMoneylog = M('member_moneylog');
        $borrowVerify = M('borrow_verify');
        
        //合作伙伴
        $partners = M('partners')->where('link_type = 1 and is_show = 1')->order('link_order DESC')->select();
        $this->assign('partners', $partners);

	    //加入用户数
	    $invest_num = M('transfer_borrow_investor')->where("is_jijin = 1")->group("investor_uid")->select();

	    $list = $memberMoneylog->field('type,sum(affect_money) as money')->group('type')->select();
        $row = array();
        foreach($list as $v){
            $row[$v['type']]['money'] = $v['money'] > 0 ? $v['money'] : $v['money'] * (-1);
        }
		$this->assign('list',$row);

        /****************************募集期内标未满,自动流标 新增 2013-03-13****************************/
        $mapT = array();
        $mapT['collect_time'] = array("lt", $now);
        $mapT['borrow_status'] = 2;
        $tlist = $borrow_Info->field("id,borrow_uid,borrow_type,borrow_money,first_verify_time,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time")->where($mapT)->select();

        if (!empty($tlist)) {
            foreach($tlist as $key => $vbx){
                $borrow_id = $vbx['id'];
                //流标
                $done = false;
                $binfo = $borrow_Info->field("borrow_type,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
                $investorList = $borrowInvestor->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
                $investorDetail->where("borrow_id={$borrow_id}")->delete();
                if($binfo['borrow_type'] == 1){
                    $limit_credit = memberLimitLog($binfo['borrow_uid'], 12, $binfo['borrow_money'], $info="{$binfo['id']}号标流标");//返回额度
                }
                $borrowInvestor->startTrans();
        
                $bstatus = 3;
                $upborrow_info = $borrow_Info->where("id={$borrow_id}")->setField("borrow_status", $bstatus);
                //处理借款概要
                $buname = $members->getFieldById($binfo['borrow_uid'], 'user_name');
                //处理借款概要
                if(!empty($investorList)){
                    $upsummary_res = $borrowInvestor->where("borrow_id={$borrow_id}")->setField("status", $type);
                    foreach($investorList as $v){
                        MTip('chk15', $v['investor_uid']);//sss
                        $accountMoney_investor = $memberMoney->field(true)->find($v['investor_uid']);
                        $datamoney_x['uid'] = $v['investor_uid'];
                        $datamoney_x['type'] = $type == 3 ? 16 : 8;
                        $datamoney_x['affect_money'] = $v['investor_capital'];
                        $datamoney_x['account_money'] = ($accountMoney_investor['account_money'] + $datamoney_x['affect_money']);//投标不成功返回充值资金池
                        $datamoney_x['collect_money'] = $accountMoney_investor['money_collect'];
                        $datamoney_x['freeze_money'] = $accountMoney_investor['money_freeze'] - $datamoney_x['affect_money'];
                        $datamoney_x['back_money'] = $accountMoney_investor['back_money'];
        
                        //会员帐户
                        $mmoney_x['money_freeze'] = $datamoney_x['freeze_money'];
                        $mmoney_x['money_collect'] = $datamoney_x['collect_money'];
                        $mmoney_x['account_money'] = $datamoney_x['account_money'];
                        $mmoney_x['back_money'] = $datamoney_x['back_money'];
        
                        //会员帐户
                        $_xstr = $type == 3 ? "复审未通过" : "募集期内标未满,流标";
                        $datamoney_x['info'] = "第{$borrow_id}号标".$_xstr."，返回冻结资金";
                        $datamoney_x['add_time'] = $now;
                        $datamoney_x['add_ip'] = get_client_ip();
                        $datamoney_x['target_uid'] = $binfo['borrow_uid'];
                        $datamoney_x['target_uname'] = $buname;
                        $moneynewid_x = $memberMoneylog->add($datamoney_x);
                        if($moneynewid_x){
                            $bxid = $memberMoney->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
                        }
                    }
                }else{
                    $moneynewid_x = $bxid = $upsummary_res = true;
                }
        
                if($moneynewid_x && $upsummary_res && $bxid && $upborrow_info){
                    $done = true;
                    $borrowInvestor->commit();
                }else{
                    $borrowInvestor->rollback();
                }
                if(!$done){
                    continue;
                }
        
                MTip('chk11', $vbx['borrow_uid'], $borrow_id);
                $verify_info['borrow_id'] = $borrow_id;
                $verify_info['deal_info_2'] = text($_POST['deal_info_2']);
                $verify_info['deal_user_2'] = 0;
                $verify_info['deal_time_2'] = $now;
                $verify_info['deal_status_2'] = 3;
                if($vbx['first_verify_time'] > 0){
                    $borrowVerify->save($verify_info);
                }else{
                    $borrowVerify->add($verify_info);
                }
        
                $vss = $members->field("user_phone,user_name")->where("id = {$vbx['borrow_uid']}")->find();
                SMStip("refuse", $vss['user_phone'], array("#USERANEM#","ID"), array($vss['user_name'], $verify_info['borrow_id']));
                
                //updateBinfo
                $newBinfo = array();
                $newBinfo['id'] = $borrow_id;
                $newBinfo['borrow_status'] = 3;
                $newBinfo['second_verify_time'] = $now;
                $borrow_Info->save($newBinfo);
            }
        }
        
        $this->display();
    }

    /**
     * 水滴直播
     */
    public function shuidi(){
        $this->display();
    }
    public function shuidia(){
        $this->display();
    }
    public function shuidib(){
       $this->display();
    }
    public function shuidic(){
        $this->display();
    }
    public function shuidid(){
        $this->display();
    }
}