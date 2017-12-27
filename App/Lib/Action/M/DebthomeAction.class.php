<?php

/**
 * 债权转让手机端：首页和列表页面
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/11/17
 * Time: 10:14
 */
class DebthomeAction extends HCommonAction
{
    /**
     *债权列表页面
     */
    public function  index(){
        $field="d.id,d.borrow_name,d.borrow_money,b.borrow_interest_rate,d.borrow_duration_txt,d.borrow_status,d.has_borrow";
        $map["d.borrow_status"]=array("in",array("2","4","6","7"));
        // $map["d.collect_time"]=array("egt",time());
        $map["b.product_type"]=array('in','1,2,3');
        $zhaiquanlist= M('debt_borrow_info d')->join("lzh_borrow_info b on b.id = d.old_borrow_id")->field($field)->where($map)->order("d.borrow_status ASC,d.id DESC")->limit($Lsql)->select();
        foreach ($zhaiquanlist as $k=>$v){
            $zhaiquanlist[$k]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
            $zhaiquanlist[$k]["left"]=getFloatValue($v["borrow_money"]-$v["has_borrow"],2);
        }
        $this->assign("list",$zhaiquanlist);
        $this->display();
    }

    /**
     * 获取不同类型的标的 1 质金链 2保金链 3荣金莲 4信金链 5优金链
     */
    public function content(){
        $field="d.id,d.borrow_name,d.borrow_money,b.borrow_interest_rate,d.borrow_duration_txt,d.borrow_status,d.has_borrow";
        $map["d.borrow_status"]=array("in",array("2","4","6","7"));
        // $map["d.collect_time"]=array("egt",time());
        $type=$_POST['type'];
        if($type==1){//质金链
            $map["b.product_type"]=array('in','1,2,3');
        }elseif($type==2){//保金链
            $map["b.product_type"]=8;
        }elseif($type==3){//荣金莲
            $map["b.product_type"]=4;
        }elseif($type==4){//信金链
            $map["b.product_type"]=array('in','5,6');
        }elseif($type==5){//优金链
            $map["b.product_type"]=7;
        }else{
            $this->outmessage(1,"参数错误");
        }
        $zhaiquanlist= M('debt_borrow_info d')->join("lzh_borrow_info b on b.id = d.old_borrow_id")->field($field)->where($map)->order("d.borrow_status ASC,d.id DESC")->limit($Lsql)->select();
        foreach ($zhaiquanlist as $k=>$v){
            $zhaiquanlist[$k]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
            $zhaiquanlist[$k]["left"]=getFloatValue($v["borrow_money"]-$v["has_borrow"],2);
        }
        $this->assign("list",$zhaiquanlist);
        $html=$this->fetch();
        $this->outmessage(0,"",array("html"=>$html));
    }


    /**
     * 债权的详情页面
     */
    public function detail(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error("参数缺失");
        }
        session("lastpcid",$id);
        $field = "d.borrow_name,d.id,d.old_borrow_id,d.debt_rate,d.collect_time,d.borrow_times,d.borrow_min,d.borrow_duration_txt,d.add_time,d.borrow_money,d.totalmoney,d.has_borrow,b.borrow_interest_rate,b.repayment_type,b.product_type,b.borrow_use,b.borrow_use_desc,d.borrow_status";
        $zhaiquanlist=M("debt_borrow_info d")
            ->join("lzh_borrow_info b on b.id = d.old_borrow_id")
            ->where(array("d.id"=>$id))
            ->field($field)
            ->find();
        if($zhaiquanlist){
            $zhaiquanlist["progress"]=getFloatValue($zhaiquanlist['has_borrow']/$zhaiquanlist['borrow_money']*100,2);
            $zhaiquanlist['need'] = $zhaiquanlist['borrow_money'] - $zhaiquanlist['has_borrow'];//可投金额
            $zhaiquanlist['lefttime'] =$zhaiquanlist['collect_time'] - time();//剩余时间$sinasaving = querysaving($this->uid);
            $sinasaving = querysaving($this->uid);
            $sinabalance = querybalance($this->uid);
            $zhaiquanlist['account_money']=$sinasaving+$sinabalance;
        }else{
            $this->error("数据有误");
        }

        /**
         * 是否登录 0未登录  1 已经登录
         */
        $islogin=0;
        if($this->uid){
            $islogin=1;
        }
        $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";//确定还款方式和
        $this->assign("bconfig",$Bconfig);
        $this->assign("gloconf",$this->gloconf);
        $this->assign("islogin",$islogin);
        $this->assign("info",$zhaiquanlist);
        $simple_header_info=array("url"=>"/M/Debthome/index.html","title"=>$zhaiquanlist["borrow_name"]);
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 投资页面
     */
    public function invest(){
        $id=$_GET["bid"];
        $zhaiquanlist=M("debt_borrow_info")->where(array("id"=>$id))->find();
        if($zhaiquanlist && count($zhaiquanlist)){
            $zhaiquanlist['borrow_money']=getFloatValue($zhaiquanlist['borrow_money'], 2);
            $zhaiquanlist['need'] = $zhaiquanlist['borrow_money'] - $zhaiquanlist['has_borrow'];//可投金额
            $zhaiquanlist['need']=getFloatValue($zhaiquanlist['need'],2);
            $zhaiquanlist['lefttime'] =$zhaiquanlist['collect_time'] - time();//剩余时间
            $sinasaving = querysaving($this->uid);
            $sinabalance = querybalance($this->uid);
            $account_money=$sinasaving+$sinabalance;
            $this->assign("vo",$zhaiquanlist);
            $this->assign("id",$id);
            $this->assign("account_money",$account_money);//账户剩余金额
        }else{
            $this->error("参数错误");
        }
        if(!is_array($zhaiquanlist) || ($zhaiquanlist['borrow_status']==0)){
            $this->error("数据有误");
        }

        $simple_header_info=array("url"=>"/M/debthome/detail/id/".$id,"title"=>"投标");
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 投资记录
     */
    public function recode(){
        $id=$_GET["id"];
        if(!$id){
            $this->error("参数缺失");
        }
        $borrow_id = intval($id);
        $simple_header_info=array("url"=>"/M/debthome/detail/id/".$borrow_id,"title"=>"投资记录");
        import("ORG.Util.Page");
        $count = M("debt_borrow_investor")->where('borrow_id='.$borrow_id)->count('id');
        $Page     = new Page($count,10);
        $Page->setConfig('theme',"%upPage% %downPage% 共%totalPage% 页");
        $show = $Page->show();
        $this->assign('page', $show);
        $this->assign("total_page",$Page->get_total_page());
        if($_GET['id']){
            $list = M("debt_borrow_investor as b")
                ->join("lzh_members as m on  b.investor_uid = m.id")
                ->join("lzh_debt_borrow_info as i on  b.borrow_id = i.id")
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
            $borrow = M("debt_borrow_info")->where("id = {$borrow_id}")->field("borrow_money,has_borrow")->find();
            if($borrow["borrow_money"] == $borrow["has_borrow"]){
                $borrow["remaining"] = "0.00";
            }else{
                $borrow["remaining"] = $borrow["borrow_money"] - $borrow["has_borrow"];
            }
            $this->assign("borrow",$borrow);
            $this->assign("list",$string);
        }
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 投标之后检测并发送数据给新浪
     */
    public function investmoney(){
        if(!$this->uid) {
            $this->error('请先登录',3);
            exit;
        }

        $money = $_POST['money'];//投资金额
        $money=getFloatValue($money,2);
        $borrow_id = intval($_POST['borrow_id']);//标号
        $sinasaving = querysaving($this->uid);
        $sinabalance = querybalance($this->uid);
        $amoney = $sinabalance+$sinasaving;
        $uname = session('u_user_name');
        if($amoney<$money){//如果金额不足则跳转到充值页面
            $this->error("尊敬的{$uname}，您准备投标{$money}元，但您的账户可用余额为{$amoney}元，请先去充值再投标.",__APP__."/member/charge#fragment-1");
        }

        $binfo = M("debt_borrow_info d")
                ->join("lzh_borrow_info b ON b.id = d.old_borrow_id")
                ->field('d.borrow_uid as debt_uid,b.borrow_uid as old_borrow_uid,d.borrow_money,d.has_borrow,b.borrow_min')->where(array("d.id"=>$borrow_id))->find();
        if($this->uid == $binfo['debt_uid'] || $this->uid == $binfo['old_borrow_uid']) {
            $this->error('不能去投自己的标');
            exit;
        };

        $need = $binfo['borrow_money'] - $binfo['has_borrow'];
        $caninvest = $need - $binfo['borrow_min'];
        if( $money>$caninvest && $need==0){
            $msg = "尊敬的{$uname}，此标已被抢投满了,下次投标手可一定要快呦！";
            $this->error($msg);
        }
        if(($binfo['borrow_min']-$money)>0 ){
            $this->error("尊敬的{$uname}，本标最低投标金额为{$binfo['borrow_min']}元，请重新输入投标金额");
        }
        $binfo=M("debt_borrow_info")->where(array("id"=>$borrow_id))->field("has_borrow,borrow_money,repayment_type,borrow_interest_rate,borrow_duration")->find();
        $leftmoney=$binfo["borrow_money"]-$binfo["has_borrow"]-$money;
        $jingdu=0.00001;//精度
        if($leftmoney<100 && $leftmoney>$jingdu){
            $this->error("尊敬的{$uname}，此标剩余金额不足100元");
        }
        
        $need=number_format($need, 2,'.', '');
        $money=number_format($money, 2,'.', '');
        if(($need-$money)<0 ){
            $this->error("尊敬的{$uname}，此标还差{$need}元满标,您最多只能再投{$need}元");
        }else{
            moneyactlog($this->uid,16,$money,0,"投资人发起对ZQ".$borrow_id."号标付款",1);
            //新浪代收接口
            $sina['money'] = $money;
            $sina['uid'] = $this->uid;
            $sina['content'] = "对第ZQ".$borrow_id."号标投资付款";
            $sina['bid'] = $borrow_id;
            $sina['code'] = "1001";
            $sina['return_url'] = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/debthome/jumpsuccess?borrow_id=".$borrow_id."&money=".$money;
            $sina['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquanborrownotify";
            $sina['coupons_num'] =null;
            echo sinacollecttrade($sina,2);
            exit();
        }
    }

    /**
     * 投标成功跳转页面
     */
    public function jumpsuccess(){
        $borrow_id = $_REQUEST['borrow_id'];
        $count = M("investor_detail")->where("investor_uid = {$this->uid} and status!=-1")->count();
        $this->assign("count",$count);
        $this->assign("url","/M/debthome/invest?bid=".$borrow_id);
        $this->display();
    }

    /**
     * 原始项目信息
     */
    public function oldinfo(){
        if(!$this->uid) {
            $this->error('请先登录',3);
            exit;
        }
         $id=intval($_GET["id"]);
        $borrowinfo=M("debt_borrow_info")->where(array("id"=>$id))->find();
        if(!$borrowinfo || ($borrowinfo['borrow_status']==0 && $this->uid!=$borrowinfo['borrow_uid']) )
        {
            $this->error("数据有误");
        }else{
            $borrowinfo['biao'] = $borrowinfo['borrow_times'];
            $borrowinfo['need'] = $borrowinfo['borrow_money'] - $borrowinfo['has_borrow'];
            $borrowinfo['lefttime'] =$borrowinfo['collect_time'] - time();
            $borrowinfo['progress'] = getFloatValue($borrowinfo['has_borrow']/$borrowinfo['borrow_money']*100,2);
            $this->assign("vo",$borrowinfo);
            $simple_header_info=array("url"=>"/M/debthome/detail/id/".$id,"title"=>"原项目信息");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("gloconf",$this->gloconf);
            $this->display();
        }
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
}