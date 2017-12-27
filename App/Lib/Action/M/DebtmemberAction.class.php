<?php

/**
 * 债权转让手机端用户中心部分
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/11/17
 * Time: 10:16
 */
class DebtmemberAction extends HCommonAction
{
    /**
     *对应字典编号：债权每日转让笔数
     */
    const ZHUANRANG_COUNT=1006;
    const ZHUANRANG_TYPE=1005;

    /**
     * 是否是测试0 1 测试
     */
    const TEST=0;

    /**
     * 债权展示列表页面：可转让
     */
     public function index(){
         $simple_header_info=array("url"=>"/M/user/index.html","title"=>"投资列表");
         $this->assign("simple_header_info",$simple_header_info);
         $html=$this->kezhuanrang();
         $this->assign("html",$html);
         $this->display();
     }

    /**
     * 转让列表
     */
    public function mylists(){
        $type=$_POST["type"];
        if($type==1){
            $html=$this->kezhuanrang();
        }else if($type==2){
            $html=$this->zhuanrangzhong();
        }else{
            $html=$this->yizhuanrang();
        }
        $this->outmessage(0,"ok",$html);
    }

    /**
     * 可转让
     */
    private function kezhuanrang(){
        // import("ORG.Util.Page");
        $where["bi.investor_uid"] = $this->uid;
        $where["bi.investor_capital"] = array("egt",1000);
        $where["bi.debt_status"] = array("eq",0);
        $where["bi.debt_id"] = array("eq",0);
        $where["bi.deadline"] = array("egt",array("exp","unix_timestamp(now()) + 1296000"));
        $where["ii.repayment_time"] = array("eq" , 0);
        $field = "b.id,b.borrow_name,bi.investor_capital,b.second_verify_time,i.deadline,b.has_pay,b.total,b.borrow_interest_rate,i.invest_id,b.repayment_type,b.borrow_duration,sum(ii.capital + ii.interest) as daishou";
        //可转让列表
        $list = M("borrow_investor bi")
                ->join("lzh_borrow_info b ON b.id = bi.borrow_id")
                ->join("lzh_investor_detail i ON bi.id = i.invest_id AND b.has_pay + 1 = i.sort_order")
                ->join("lzh_investor_detail ii ON bi.id = ii.invest_id")
                ->field($field)
                ->where($where)
                ->group("bi.id")
                ->select();
        $zhuanlist = null;
        $i = 0;
        foreach ($list as $key => $value) {
            if((($value["repayment_type"] == 1 && $value["borrow_duration"] >= 90 && $value["apply_status"] == 0 && time() > strtotime(date("Y-m-d 23:59:59",strtotime(date("Y-m-d",$value["second_verify_time"] )." + 29 day")))) || ($value["repayment_type"] != 1 && $value["borrow_duration"] >= 3 && time() > strtotime(date("Y-m-d 23:59:59",strtotime(date("Y-m-d",$value["second_verify_time"] )." + 1 month"))))) && $value["daishou"] >= 1000 && $value["deadline"]>time()){
                $zhuanlist[$i] = $list[$key];
                $i++;
            }

        }

        //可转让时间
        $nowtime=date("H",time());
        $nowtime=intval($nowtime);
        $flag = false;
        if($nowtime>=10 && $nowtime <= 17){
            $flag = true;
        }
        $this->assign("flag",$flag);
        $this->assign('list', $zhuanlist);
        $this->assign('today',date("Y-m-d",time()));
        $html=$this->fetch("kezhuanrang");
        return $html;
    }

    /**
     * 转让中
     */
    private function  zhuanrangzhong(){
        $where["borrow_uid"] = $this->uid;
        $where["borrow_status"] = array("in","0,2,4");
        $list = M("debt_borrow_info")
                ->where($where)
                ->select();
        $this->assign('list', $list);
        $html=$this->fetch("zhuanrangzhong");
        return $html;
    }

    /**
     * 已转让
     */
    private function yizhuanrang(){
        $list = M('debt_borrow_info')->where(array("borrow_uid"=>$this->uid,"borrow_status"=>array("IN","6,7")))->order('id DESC')->select();
        $this->assign('list', $list);
        $html=$this->fetch("yizhuanrang");
        return $html;
    }

    /**
     * ajax 输出json格式
     * @param $status
     * @param $message
     * @param null $data
     */
    private function outmessage($status,$message,$data=null){
        $outdata=array();
        $outdata["status"]=$status;
        $outdata["msg"] =$message;
        if($data) {
            $outdata["data"]=$data;
        }
        echo json_encode($outdata);
        exit();
    }

    /**
     * 债权转让
     * type : 1 原始债权转让 2 已转让债权再转让
     */
    public function zhuanrang(){
        $borrow_id=$_POST["borrow_id"];
        $type=$_POST["type"];
        $model=M("debt_count");
        $list=$model->field("count")->where(array("adddate"=>date("Y-m-d")))->find();
        if($list["count"]>20){
            $this->outmessage(1,"已经超过今天可转让的最大笔数");
        }
        $this->outmessage(0,"可以转让");
    }


    /**
     * 确认页面
     */
    public function sellhtml(){
        $simple_header_info=array("url"=>"/M/Debtmember/index.html","title"=>"债权确认");
        $id=$_GET["id"];
        $type=$_GET["type"];
        $invest_id=$_GET["investid"];//上一级投资人也就是转让人投资记录的id,区分同一个投资人同一个标投多次无法区分的问题因而记录上一个投资人的投资记录id
        if($type!=1 && $type!=2)  {
            $this->error("参数错误或者缺失");
        }
        if(empty($invest_id)){
            $this->error("参数缺失");
        }
        if($type==1){//原始标转让
            $invest_info = M("borrow_info b")->field("b.borrow_duration,b.repayment_type,b.borrow_interest_rate,b.second_verify_time,SUM(i.capital) as capital,SUM(i.interest) as interest,b.has_pay,b.total")
                    ->join("lzh_investor_detail i on i.borrow_id = b.id ")
                    ->where(array("b.id"=>$id,"i.invest_id"=>$invest_id,"i.investor_uid"=>$this->uid,"repayment_time"=>0,"status"=>array("neq",-1)))
                    ->find();
            if(!$invest_info){
                $this->error("参数错误");
            }
            $borrow_interest_rate   =  $invest_info['borrow_interest_rate']; //借款利率
            if($invest_info["repayment_type"]==1){
                //天标利息计算
                $day_rate      =  $borrow_interest_rate/36000;//计算出天标的天利率
                $currentTime   = strtotime(date('Y-m-d')); //当前需还款时间
                $issueTime     = strtotime(date('Y-m-d',$invest_info["second_verify_time"]));//复审后的时间
                $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);
                $lixi = getFloatValue($invest_info['capital']*$day_rate*$BorrowingDays, 2);
                $totalmoney = $invest_info["capital"]+$lixi;
                $fenqi=0;
            }else{
                //月标计算
                //当月持有
                $where["sort_order"] = $invest_info["has_pay"]+1;
                $where["investor_uid"] = $this->uid;
                $where["invest_id"] = $invest_id;
                $monthnow = M("investor_detail")->where($where)->find();
                //当月持有天数
                $last_repay_date =  strtotime((date("Y-m-d",$monthnow["deadline"]))." - 1 month + 1 day");
                $now_time = strtotime(date("Y-m-d"));
                $BorrowingDays = ceil(($now_time - $last_repay_date)/3600/24);
                //当月利息
                $now_interest = getFloatValue($monthnow["interest"]*($BorrowingDays/30), 2);
                //转让价值
                $totalmoney = $invest_info["capital"]+$now_interest;
                $fenqi=1;
            }

        }
            if($totalmoney<1000){
                $this->error("申请转让的债权价值必须大于1000元");
            }
            $fuwufei=$totalmoney*0.005;
            if($fuwufei<5){
                $fuwufei=5;
            }
            $token=md5($id).time();
            cookie("token",$token);
            $initzhuanrang=$totalmoney-$fuwufei;
            $this->assign("investor_capital",$invest_info['capital']);//本金
            $this->assign("investor_interest",$invest_info['interest']);//利息
            $this->assign("totalmoney",getFloatValue($totalmoney, 2)) ;//可转让金额
            $this->assign("borrow_interest_rate",$borrow_interest_rate) ;//年利率
            $this->assign("deadline",date("Y-m-d",strtotime((date("Y-m-d")."+ 3 day")))) ;
            $this->assign("fuwufei",getFloatValue($fuwufei, 2)) ;//服务费
            $this->assign("initzhuanrang",getFloatValue($initzhuanrang, 2));//原始的可转让金额
            $this->assign("borrow_id",$id);//标号
            $this->assign("type",$type);//转让标类型：type  1 原始债权转让 2 已转让债权再转让
            $this->assign("fenqi",$fenqi);//是否是分期标
            $this->assign("token",$token);
            $this->assign("invest_id",$invest_id);//上一次投资人的投资记录id，区分同一人统一标投多次
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 处理债权转让
     */
    public function confirm(){
        $id=$_POST["id"];//标号可能是原始标号或者转让新标号
        $fenqi=$_POST["qi"];//是否是分期;
        $type=$_POST["type"];//转让标类型：type  1 原始债权转让 2 已转让债权再转让
        $zhejialv=$_POST["zhejialv"];//折价率
        $yzmcode=$_POST["yzmcode"];//短信验证码
        $totalmoney=$_POST["totalmoney"];//总费用，也即债权价值，原始的剩余本金和利息之和
        $fuwufei=$_POST["fuwufei"];//服务费
        $captial=$_POST["capital"];//原始本金
        $interest=$_POST["interest"];//原始本金
        $huishou=$_POST["huishou"];// 回收价值=折价后的本金利息-服务费
        $myvalue=$_POST["myvalue"];//折价后的本金利息=债权价值*(1-折价率)
        $token=$_POST["token"];
        $invest_id=$_POST["invest_id"];//投资记录编号

        if(!$id || !$type || !isset($fenqi) || !isset($zhejialv) || !$totalmoney|| !$fuwufei || !$captial|| !$huishou|| !$token|| !$invest_id){
            $this->outmessage(1,"参数为空");
        }
        if($yzmcode!=cookie("num") && APP_DEBUG == 0){
            $this->outmessage(2,"手机验证码错误");
        }
        if($type!=1 && $type!=2){
            $this->outmessage(3,"参数错误");
        }
        if($token!= cookie("token")){
            $this->outmessage(4,"请刷新后再试");
        }
        $zhejialv=$zhejialv*1.00;
        $zhai=$totalmoney*(1-$zhejialv/100);
        $zhai=number_format($zhai, 2,'.', '');
        if($zhai!=$myvalue){
            $this->outmessage(5,"传入参数错误");
        }
        $systemlist=M("system_setting")->where(array("number"=>self::ZHUANRANG_COUNT))->find();//每日债权总笔数
        if(!$systemlist){
            $this->outmessage(7, "未设置每日债权总笔数");
        }
        $countlist=M("debt_count")->where(array("adddate"=>date("Y-m-d")))->find();
        if($countlist){
            if($systemlist["value"]<=$countlist["count"]){
                $this->outmessage(8, "债权转让笔数已达当日最高次数");
            }else{
                $start_num = $countlist["count"] + 1;
            }
        }else{
            $start_num = 1;
        }
        if($type==1) {
            //原始标转让
            $borrowlist = M("borrow_info")->where(array("id" => $id))->find();
            if (count($borrowlist)) {
                if($borrowlist["repayment_type"]==1){//天标
                     $borrow_duration=$borrowlist["borrow_duration"]-ceil((strtotime(date("Y-m-d",time()))-strtotime(date("Y-m-d",$borrowlist["second_verify_time"])))/(3600*24));
                    $borrow_duration_txt=$borrow_duration."天";
                    $total=1;//天标默认1
                }else{
                    $borrow_duration=$borrowlist["total"]-$borrowlist["has_pay"];
                    $borrow_duration_txt=$borrow_duration."个月";
                    $total=$borrow_duration;
                    $borrow_duration = $borrow_duration*30;
                }

                $st="";
                if($borrowlist['product_type']=="1"|| $borrowlist['product_type']=="2"|| $borrowlist['product_type']=="3"){
                    $st="质金链: ";
                }else if($borrowlist['product_type']=="7"){
                    $st="优金链: ";
                }elseif($borrowlist['product_type']=="4"){
                    $st="融金链: ";
                }elseif($borrowlist['product_type']=="5" || $borrowlist['product_type']=="6" ){
                    $st="信金链: ";
                }elseif($borrowlist['product_type']=="8"){
                    $st="保金链: ";
                }elseif($borrowlist['product_type']=="9"){
                    $st="分期购: ";
                }
                $borrow_num = str_pad($start_num,3,'0',STR_PAD_LEFT);
                $borrow_newname=$st."ZQ".date("ymd",time()).$borrow_num."期";
                $zqtype = M("system_setting")->where(array("number"=>self::ZHUANRANG_TYPE))->find();
                if($zqtype["value"] == 1){
                    $borrow_status = 0;
                    $collect_time = 0;
                    $first_verify_time = 0;

                }else{
                    $borrow_status = 2;
                    $collect_time = time()+72*3600;
                    $first_verify_time = time();
                }

                $debt_borrow_info=array(
                    "borrow_name"=>$borrow_newname,
                    "borrow_uid"=>$this->uid,
                    "borrow_duration"=>$borrow_duration,
                    "borrow_duration_txt"=>$borrow_duration_txt,
                    "borrow_money"=>$myvalue,
                    "debt_rate"=>$zhejialv,
                    "totalmoney"=>$totalmoney,
                    "colligate_fee"=>$fuwufei,
                    "borrow_status" => $borrow_status,
                    "add_time" => time(),
                    "collect_day"=>3,
                    "collect_time"=>$collect_time,
                    "first_verify_time"=>$first_verify_time,
                    "old_borrow_id"=>$id,
                    "invest_id"=>$invest_id,
                    "debt_captial"=>$captial,
                    "debt_interest"=>$interest,
                );
                $newid=M("debt_borrow_info")->add($debt_borrow_info);
                if($newid){
                    if($countlist && count($countlist)){
                        M("debt_count")->where(array("adddate"=>date("Y-m-d")))->save(array("count"=>$countlist["count"]+1));
                    }else{
                        M("debt_count")->add(array("adddate"=>date("Y-m-d"),"count"=>1));
                    }
                    M("debt_borrow_info_lock")->add(array("suo"=>0));
                    M("borrow_investor")->where(array("id"=>$invest_id))->save(array("debt_status"=>1));
                    if($zqtype["value"] == 1){
                        $fk_phone = C('NOTICE_TEL.fengkong');
                        $user_phone = M('members')->where(array('id'=>$this->uid))->find();
                        $msg = '用户（'.$user_phone['user_phone'].'）提交债转申请，请及时登录平台审批。';
                        sendsms($fk_phone,$msg);
                    }
                    $this->outmessage(0,"发债权转让成功");
                }else{
                    $this->outmessage(8,"发债权转让标失败");
                }
            }else{
                $this->outmessage(4,"参数错误");
            }
        }
    }


    /**
     * 转让协议
     */
    public function xieyi(){
        $simple_header_info=array("url"=>"/M/user/index.html","title"=>"债权转让协议");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 发送手机验证码
     */
    public function sendphone(){
        $num=rand(100000,999999);
        $content="验证码：{$num},请您在3分钟内填写。如非本人操作，请忽略此短信。";
        $userlist=M("members")->field("user_phone")->where(array("id"=>$this->uid))->find();
        if($userlist&&$userlist["user_phone"] ){
            sendsms($userlist["user_phone"],$content);
            cookie("num",$num);
            $this->outmessage(0,"发送成功",$num);
        }else{
            $this->outmessage(1,"用户手机号为空");
        }

    }

    /**
     * 主动取消债权
     */
    public function cancel(){
        $id=$_POST["borrow_id"];
        $debt_info = M("debt_borrow_info")->where(array("id"=>$id,"borrow_uid"=>$this->uid))->find();
        if($debt_info){
            if($debt_info["borrow_times"]>0){
                $this->outmessage(1,"已经存在投资，不能主动撤销");
            }else{
                $debt_res = M("debt_borrow_info")->where(array("id"=>$id))->save(array("borrow_status"=>8));
                $investor_res = M("borrow_investor")->where(array("id"=>$debt_info["invest_id"]))->save(array("debt_status"=>0));
                $debtcount = M("debt_count")->where(array("adddate"=>date("Y-m-d",$debt_info["add_time"])))->find();
                if($debt_count["count"]>0){
                    $debtcount_res = M("debt_count")->where(array("adddate"=>date("Y-m-d",$debt_info["add_time"])))->save(array("count"=>$debtcount["count"]-1));
                }else{
                     $debtcount_res = true;
                }

                if($debt_res && $investor_res && $debtcount_res){
                    $this->outmessage(0,"撤销成功");
                }else{
                    $this->outmessage(2,"撤销失败");
                }
            }
        }else{
            $this->outmessage(3,"刷新后再试");
        }
    }
}
