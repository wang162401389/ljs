<?php
    class InvestAction extends HCommonAction
    {
        public function detail()
        {
            $pre = C('DB_PREFIX');
            $id = intval($_GET['id']);
            $this->assign("borrow_id",$id);
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";

            $borrowinfo = M("borrow_info bi")
                            ->field('bi.*,ac.title,ac.id as aid')
                            ->join('lzh_article ac on ac.id= bi.danbao')
                            ->where('bi.id='.$id)
                            ->find();
            if(!$borrowinfo || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) )
                $this->error("数据有误");
            $borrowinfo['biao'] = $borrowinfo['borrow_times'];
            $borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
            $borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
            $borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
            $this->assign("vo",$borrowinfo);
            
            $simple_header_info=array("url"=>"/M","title"=>"项目详情");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("Bconfig",$Bconfig);
            $this->assign("gloconf",$this->gloconf);

            //判断是否进行风险评估 1 已经登录未填写   2 已经登录但是忽略 3已经填写
            $res = 0;
            $fxpg_popup_status = 1;
            if($this->uid){
                if(isset($_COOKIE["touzitype"])){//是否跳过
                    $res=2;
                }else{
                    $risk = M('risk_result')->where("uid = '{$this->uid}'")->select();
                    if($risk){
                        $res=3;
                    }else{
                        $res=1;
                    }   
                }
                session('riskbid', $id);
                
                $fxpg_popup_status = M("members_status")->where(array("uid" => $this->uid))->getField('fxpg_popup_status');
                $risk = M("risk_result")->where(array("uid" => $this->uid))->limit(1)->find();
                $fxpg_popup_status = empty($risk) ? $fxpg_popup_status : 0;
            }
            $this->assign("fxpg_popup_status", $fxpg_popup_status);
            session("lastphone",$id);
            $this->assign('res',$res);
            $this->display();
        }
		
		
		//项目信息
		 public function projectcontent()
        {

           if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后查看");
                }else{
                    $this->success("请先登录",'/M/pub/login');
                    exit;
                }
            }
            $pre = C('DB_PREFIX');
            $id = intval($_GET['id']);
            $this->assign("borrow_id",$id);
            $borrowinfo = M("borrow_info bi")
                            ->field('bi.*,ac.title,ac.id as aid')
                            ->join('lzh_article ac on ac.id= bi.danbao')
                            ->where('bi.id='.$id)
                            ->find();
            
            if(!$borrowinfo || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) )
                $this->error("数据有误");
            $borrowinfo['biao'] = $borrowinfo['borrow_times'];
            $borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
            $borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
            $borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
            $this->assign("vo",$borrowinfo);  
            $simple_header_info=array("url"=>"/M/invest/detail/id/".$id,"title"=>"项目信息");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("gloconf",$this->gloconf);
            if($borrowinfo["product_type"]==4){
                $this->display("projectcontent_v1");
            } else if($borrowinfo["product_type"]==6){
                $this->display("projectcontent_v2");
            }else if($borrowinfo["product_type"]==5){
                $this->display("projectcontent_fqg");
            }
            else{
                $this->display();
            }
        }
		//项目阐述
		 public function projectspeak()
        {
           if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后查看");
                }else{
                    $this->success("请先登录",'/M/pub/login');
                    exit;
                }
            }
            $pre = C('DB_PREFIX');
            $id = intval($_GET['id']);
            $this->assign("borrow_id",$id);
            $borrowinfo = M("borrow_info bi")
                            ->field('bi.*,ac.title,ac.id as aid')
                            ->join('lzh_article ac on ac.id= bi.danbao')
                            ->where('bi.id='.$id)
                            ->find();
            $this->assign("vo",$borrowinfo);  
            if($borrowinfo["product_type"]==6){
                $simple_header_info=array("url"=>"/M/invest/detail/id/".$id,"title"=>"风控措施");    
            }else{
                $simple_header_info=array("url"=>"/M/invest/detail/id/".$id,"title"=>"项目阐述");    
            }
            $this->assign("simple_header_info",$simple_header_info);
            if($borrowinfo["product_type"]==4){
                $this->display("projectspeak_v1");
            }else if($borrowinfo["product_type"]==6){
                $this->display("projectspeak_x");
            }else{
                $this->display();
            }
        }
		//风险控制
         public function riskcontrol()
        {
           if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后查看");
                }else{
                    $this->success("请先登录",'/M/pub/login');
                    exit;
                }
            }
            $pre = C('DB_PREFIX');
            $id = intval($_GET['id']);
           // $this->investRecord($id);
            $this->assign("borrow_id",$id);
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";

            $borrowinfo = M("borrow_info bi")
                            ->field('bi.*,ac.title,ac.id as aid')
                            ->join('lzh_article ac on ac.id= bi.danbao')
                            ->where('bi.id='.$id)
                            ->find();
            
            if(!$borrowinfo || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) )
                $this->error("数据有误");
            $borrowinfo['biao'] = $borrowinfo['borrow_times'];
            $borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
            $borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
            $borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
            $this->assign("vo",$borrowinfo);  
            //borrowinfo
       
            //此标借款利息还款相关情况
            //memberinfo
            $memberinfo = M("members")
                            ->field("id,customer_name,customer_id,user_name,reg_time,credits")
                            ->where("id={$borrowinfo['borrow_uid']}")
                            ->find();
            $simple_header_info=array("url"=>"/M/invest/detail/id/".$id,"title"=>"风险控制");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("minfo",$memberinfo);
            //memberinfo
            $this->assign("Bconfig",$Bconfig);
            $this->assign("gloconf",$this->gloconf);
            $this->display();
        }
		
        /**
        * 手机普通标投资
        */
        public function invest()
        {
            $borrow_id = $this->_get('bid');
            if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后投资");   
                }else{
                    $this->redirect('/M/pub/login?type=1');
                }
            }

            //如果没有设置支付密码则，跳转到支付密码设置
            // if(!check_set_pinpass($this->uid)){
            //     $simple_header_info=array("url"=>"/M/more/announcement.html","title"=>"投标");
            //     $this->assign("simple_header_info",$simple_header_info);
            //    $this->display("Pub:no_pinpass");exit;
            // }

            if($this->isAjax()){   // ajax提交投资信息
                $borrow_id = intval($this->_get('bid'));
                $invest_money = intval($this->_post('invest_money'));
                $coupons = $this->_post('coupons_money');
                $jx = $this->_post('jx');//加息券序列号
               // $paypass = $this->_post('paypass');
                //$invest_pass = isset($_POST['invest_pass'])?$_POST['invest_pass']:'';
                $status = checkInvest($this->uid, $borrow_id, $invest_money,$coupons,$jx);
                die($status);
            }else{
                $borrow_info = M("borrow_info")
                    ->field('id,borrow_name,borrow_duration, borrow_money, borrow_interest, borrow_interest_rate, has_borrow,
                             borrow_min, borrow_max, password, repayment_type,is_beginnercontract')
                    ->where("id='{$borrow_id}'")
                    ->find();
                $this->assign('borrow_info', $borrow_info);    
                
                $user_info = M('member_money')
                                ->field("account_money+back_money as money ")
                                ->where("uid='{$this->uid}'")
                                ->find();
                $this->assign('user_info', $user_info);
                //$paypass = M("members")->field('pin_pass')->where('id='.$this->uid)->find();
                 $this->assign('saving',querysaving($this->uid));
                 $this->assign('balance',querybalance($this->uid));
                //$this->assign('paypass', $paypass['pin_pass']);
                
                //计算投资期限
                $days = $borrow_info['repayment_type'] == 1?$borrow_info['borrow_duration']:$borrow_info['borrow_duration']*30;
                $coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")
                                          ->where("m.id = {$this->uid} AND c.status = 0 AND c.type = 1 AND c.min_investrange <= {$days}")
                                          ->count();

                 $this->assign("coupons_count",$coupons);
                 $coupons = getCoupons($this->uid,1,$days);
                 $this->assign("coupons", $coupons);
                //加息劵
                $jiaxi = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")->where("m.id = {$this->uid} AND c.status = 0 AND c.type = 3")->group("c.money")->select();        
                $is_jiaxi = empty($jiaxi)?0:1;
                $this->assign('jiaxi',$jiaxi);
                $this->assign('is_jiaxi',$is_jiaxi);
                $this->assign('is_beginnercontract',$borrow_info['is_beginnercontract']);
                $model = new MembersStatusModel();
                $userstatus = $model->getUserStatus();
                $this->assign('is_newhand',intval(intval($userstatus&8)!=8));
                
                $is_white_investor = in_array($this->uid, ['73661']) ? 1 : 0;
                $this->assign('is_white_investor', $is_white_investor);
                
                $this->display();   
            }
        }

        //投资记录
        public function bidhistory($borrow_id=0){
            if(!$this->uid){
                if($this->isAjax()){
                    die("请先登录后查看");
                }else{
                    $this->success("请先登录",'/M/pub/login');
                    exit;
                }
            }
            isset($_GET['borrow_id']) && $borrow_id = intval($_GET['borrow_id']);
            $Page = D('Page');
            import("ORG.Util.Page");
            $count = M("borrow_investor")->where('borrow_id='.$borrow_id)->count('id');
            $Page     = new Page($count,10);
            $Page->setConfig('theme',"%upPage% %downPage% 共%totalPage% 页");
            $show = $Page->show();
            $this->assign('page', $show);
            $this->assign("total_page",$Page->get_total_page());
            if($_GET['borrow_id']){
                $list = M("borrow_investor as b")
                    ->join(C(DB_PREFIX)."members as m on  b.investor_uid = m.id")
                    ->join(C(DB_PREFIX)."borrow_info as i on  b.borrow_id = i.id")
                    ->field('i.borrow_interest_rate, i.repayment_type, b.investor_capital, b.add_time, b.is_auto, m.user_name,m.user_phone')
                    ->where('b.borrow_id='.$borrow_id)->order('b.id')->limit($Page->firstRow.','.$Page->listRows)->select();
                $string = '';
                 foreach($list as $k=>$v){
                    $relult=$k%2;
                    if(!$relult){
                        $string .= "<tr>
                   <td width='32%'>".hidecard($v['user_phone'],2)."</td>";
                    }else{
                        $string .= "<tr>
                   <td width='32%'>".hidecard($v['user_phone'],2)."</td>";
                    }
                    $string .= "
                      <td width='32%' class='money_orange'>".Fmoney($v['investor_capital'])."元</td>
                      <td width='36%'>".date("Y-m-d H:i",$v['add_time'])."</td>
                     </tr>";
                }
                if($string == null){
                    $string = '<tr><td colspan="3">暂时没有投资记录</td></tr>';
                }
                $borrow = M("borrow_info")->where("id = {$borrow_id}")->field("borrow_money,has_borrow")->find();
                if($borrow["borrow_money"] == $borrow["has_borrow"]){
                    $borrow["remaining"] = "0.00";
                }else{
                    $borrow["remaining"] = $borrow["borrow_money"] - $borrow["has_borrow"];
                }
                $this->assign("borrow",$borrow);
                $this->assign("list",$string);
            }
            $simple_header_info=array("url"=>"/M/invest/detail/id/".$borrow_id,"title"=>"项目信息");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        public function lists(){
            session("salesman_usrid",$_REQUEST["salesman_usrid"]);
            session("salesman_2_usrid",$_REQUEST["salesman_2_usrid"]);
            session("way",$_REQUEST["way"]);
            $disdata["usrid"] = $_SESSION["salesman_usrid"];
            $disdata["source"] = $_SESSION["way"];
            setDistribut($disdata);
            $searchMap1['borrow_status']=array("in",'2,4,6,7,8,10');
            $searchMap1['product_type']=array("in",$_REQUEST["ptype"]);
            $searchMap1['is_beginnercontract'] = 0;
            $searchMap1['test'] = 0;
           
            $searchMap['_complex'] = $searchMap1;
            $searchMap['_logic'] = 'or';
            $ptype = $_REQUEST["ptype"];
            $searchMap['_string'] = " test=0 and is_beginnercontract = 1 and borrow_status in ('4','6','7') and product_type in ('{$ptype}') ";

            $parm["type"]=1;//列表页  列表页不限制条数 首页限制条数 1 列表页面   0 首页
            $parm['map'] = $searchMap;
            $parm['orderby']="INSTR('2,8,4,6,7',borrow_status) ASC,b.id DESC";


            $list = getBorrowList($parm);

            $waitMap['borrow_status']=8;
            $wparm["type"]=1;//列表页  列表页不限制条数 首页限制条数 1 列表页面   0 首页
            $wparm['map'] = $waitMap;
            $wparm['orderby']="b.add_time ASC";
            $waitlist = getBorrowList($wparm);
            $this->assign('waittime', $waitlist['list'][0]["add_time"]);
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
        
            if($_REQUEST["ptype"] == "1,2,3"){
                $type= 'ZJ';
            }elseif($_REQUEST["ptype"] == "4"){
                $type= 'RJ';
            }elseif($_REQUEST["ptype"] == "5,6"){
                $type= 'XJ';
            }elseif($_REQUEST["ptype"] == "7"){
                $type= 'YJ';
            }elseif($_REQUEST["ptype"] == "8"){
                $type= 'BJ';
            }elseif($_REQUEST["ptype"] == "10"){
                $type= 'ZJB';
            }
            $this->assign("tab",'list');
            $this->assign("listtab",$type);
            $this->assign('list', $list);

            $this->assign('Bconfig', $Bconfig);
            $this->assign("no_footer_seg","1");
            $this->display();
        }
    }

?>
