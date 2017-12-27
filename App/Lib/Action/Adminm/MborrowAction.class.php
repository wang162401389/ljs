<?php
// 全局设置
class MborrowAction extends AMCommonAction{

    protected function error($message,$jumpUrl='',$ajax=false) {
        $url=$_SERVER["HTTP_REFERER"];
        echo "<script>alert('".$message."');window.location.href='".$url."'</script>";
    }

    protected function success($message,$jumpUrl='',$ajax=false) {
        $url=$_SERVER["HTTP_REFERER"];
        echo "<script>alert('".$message."');window.location.href='".$jumpUrl."'</script>";
    }
    
    private function change_day($time){
        return strtotime(date("Ymd",$time));
     }

     private function cal_moeny($binfo,$diff,$pay_frist){
         $day_rate =  $binfo['borrow_interest_rate']/36000;//计算出天标的天利率
         if($pay_frist)
             $colligate_fee=0;
         else
              $colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$diff, 2);

         $custom_fee=0;
         $investor_uid = M('investor_detail')->where('borrow_id='.$binfo['bid'])->select();
         foreach ($investor_uid as $iteme) {
             $tou_interest = getFloatValue($iteme['capital']*$day_rate*$diff, 2);
             $custom_fee += $tou_interest;
             //$Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id}");
         }
         $cost=$colligate_fee+$custom_fee+$binfo["borrow_money"];
         return $cost;

     }
        
    public function waitverify()
    {
        // $map=array();
        // $map['b.borrow_status'] = 0;

        // $field= 'b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.updata,b.borrow_money,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.add_time,m.user_name,m.id mid,b.is_tuijian,b.money_collect';
        // $list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->order("b.id DESC")->select();
        // $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        // $this->assign("list", $list);
        // $this->assign("xaction",ACTION_NAME);
        $this->display();
    }


    public function  waitverify2(){
        $map['b.borrow_status'] = 4;
        $field= 'b.id,b.has_borrow,b.borrow_status,b.borrow_money,b.borrow_name,b.borrow_interest_rate,b.borrow_interest_rate,b.borrow_duration,b.borrow_info,b.repayment_type';
        $list = M('borrow_info b')->field($field)->where($map)->order("b.id DESC")->select();
        foreach($list as $key=>$v){
            $list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
        }
        $list = $this->assign("list",$list);
        $this->display();
    }
    public function waitverify3(){
        $where['b.apply_status']=1;
        $where['b.borrow_status']=6;
        $field="b.id,b.has_borrow,b.borrow_status,b.borrow_name,b.borrow_money,b.borrow_interest_rate,b.borrow_duration,b.borrow_info,b.repayment_type,a.*,m.user_name";
        $info=M("borrow_info_additional a")->field($field)->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($where)->select(); 
        $this->assign("info",$info);
        $this->display();
    }
    
//编辑
    public function edit3(){
        $id = intval($_REQUEST['id']);
        $info=  $this->doedit3($id);
        if(empty($info['bid']) && empty($info['user_name'])){
            echo "<script>alert('没有获取相关的信息');window.location.href='/adminm/Mborrow/waitverify3'</script>";
        }else{
            $this->assign("item",$info);
            $this->display();
        }
    }
    

    public function edit() {
        $id = intval($_REQUEST['id']);
        $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
        $map['b.id']=$id;
        $map['b.borrow_status'] = 4;
        $list = M('borrow_info b')->where($map)->order("b.id DESC")->select();
        if(empty($list)){
            echo "<script>alert('没有获取相关的信息');window.location.href='/adminm/Mborrow/waitverify2'</script>";
        }else{
            $this->assign("item",$list[0]);
            $this->get_additional_info($id);
            $danbao_list=D("Members_company")->getDanBaoList();
            $this->assign("danbao",$danbao_list);
             $this->assign('product_type',$Bconfig['PRODUCT_TYPE']);
            $this->display();
        }
    }
    public function  edit0(){
        $id = intval($_REQUEST['id']);
        $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
        $map['b.id']=$id;
        $map['b.borrow_status'] = 0;
        $list = M('borrow_info b')->where($map)->order("b.id DESC")->select();
        if(empty($list)){
            echo "<script>alert('没有获取相关的信息');window.location.href='/adminm/Mborrow/waitverify'</script>";
        }else{
            $this->assign("item",$list[0]);
            $this->get_additional_info($id);
            $danbao_list=D("Members_company")->getDanBaoList();
            $this->assign("danbao",$danbao_list);
            $this->assign('product_type',$Bconfig['PRODUCT_TYPE']);
            $this->display();
        }
    }
    public function code(){
        import("@.sms.Notice");
        import("ORG.Util.String");
        $token= String::randString(6, 1);
        session("token",$token);
        $notice=new Notice();
        $op=text($_POST['op']);
        $tel["chu"]=C("NOTICE_TEL.chu");
        $tel["fu"]=C("NOTICE_TEL.fu");
        $notice->verify2($tel[$op],$token);

        echo "ok";
    }
    private  function  get_additional_info($id){
        $addition=D("borrow_info_additional");
        $info=$addition->get_additional_info($id);
        $map['id']=$id;
        $borrow_info=M("borrow_info")->field("borrow_duration_txt,borrow_uid")->where($map)->select();
        $vm = M("member_money")->where("uid =".$borrow_info[0]['borrow_uid'])->find();
        $this->assign('vm',$vm);
        $duration_list=explode("+",$borrow_info[0]['borrow_duration_txt']);
        if((count($duration_list)==2)||($info["second_rate"]!=0)){
            $this->assign("show_all",1);
            $this->assign("second_rate",$info["second_rate"]);
            $this->assign("second_time",$duration_list[1]);
            $this->assign("frist_time",$duration_list[0]);
            $this->assign("colligate",$info["colligate"]);
        }
        $colligate=$addition->pay_first_money($id);
        $this->assign("colligate",$colligate);
    }
    private  function check_additional_info($type=1){
        $id=intval($_POST['id']);
        $where['bid']=$id;
        if($type==1){
            $danwei="天";
        }else{
            $danwei="月";
        }
        if(isset($_POST["xh_date"])){ //只有提单+现货模式才需要显示以及修改这个部分
            $date['second_time']=text($_POST['xh_date']);
            $date['second_rate']=text($_POST['xh_lx']);
            $date["frist_time"]=text($_POST['td_date']);
            $date["frist_rate"]=text($_POST["borrow_interest_rate"]);

            $data1['borrow_duration_txt']=intval($date["frist_time"]).$danwei."+". intval($date['second_time']).$danwei;
        }else{
            $data1["borrow_duration_txt"]=$_POST['borrow_duration'].$danwei;
        }

        $map['id']=$id;
        M("borrow_info")->where($map)->save($data1);

        $date["colligate"]=getFloatValue($_POST['colligate'],2);
        M("borrow_info_additional")->where($where)->save($date);
    }
    public function doEditWaitverify(){
        $token=intval($_POST['token']);
        $tokens=session("token");
        if(($tokens=="")||($token!=$tokens)){
            $this->error("动态口令错误");
            exit;
        }
        session("token",null);
        $m = D("Borrow");
        if((!isset($_POST['danbao']))||(intval($_POST['danbao'])==0)){//没有担保公司
            if(intval($_POST['vouch_money'])>0){
                $this->error("没有担保公司，设置了担保金额",0);exit;
            }
        }else{
            if(intval($_POST['vouch_money'])==0){
                $this->error("担保金额不能为0",0);exit;
            }else{
                $m->danbao=intval($_POST['danbao']);
                $m->vouch_money=getFloatValue($_POST['vouch_money'],2);
            }
        }
        //提单转现货
        if(isset($_POST['td_date'])){
            $td_date=intval($_POST['td_date']);
            $xh_date=intval($_POST['xh_date']);
            $borrow_duration=intval($_POST['borrow_duration']);
            if(($td_date+$xh_date)!=$borrow_duration){
                $this->error("提单+现货时间与总时间不符合");exit;
            }
        }

        if (false === $m->create()) {
            $this->error($m->getError());
        }
        $vm = M('borrow_info')->field('borrow_uid,borrow_status,borrow_type,product_type,first_verify_time,password,updata,danbao,vouch_money,money_collect,can_auto')->find($m->id);

        $rate_lixt = explode("|",$this->glo['rate_lixi']);
        $borrow_duration = explode("|",$this->glo['borrow_duration']);
        $borrow_duration_day = explode("|",$this->glo['borrow_duration_day']);
        if(floatval($_POST['borrow_interest_rate'])>$rate_lixt[1] || floatval($_POST['borrow_interest_rate'])<$rate_lixt[0]){
            $this->error("提交的借款利率超出允许范围，请重试",0);exit;
        }
        if($m->repayment_type=='1'&&($m->borrow_duration>$borrow_duration_day[1] || $m->borrow_duration<$borrow_duration_day[0])){
            $this->error("提交的借款期限超出允许范围，请去网站设置处重新设置系统参数",0);exit;
        }
        if($m->repayment_type!='1'&&($m->borrow_duration>$borrow_duration[1] || $m->borrow_duration<$borrow_duration[0])){
            $this->error("提交的借款期限超出允许范围，请去网站设置处重新设置系统参数",0);exit;
        }

        ////////////////////图片编辑///////////////////////
        if(!empty($_POST['swfimglist'])){
            foreach($_POST['swfimglist'] as $key=>$v){
                $row[$key]['img'] = substr($v,1);
                $row[$key]['info'] = $_POST['picinfo'][$key];
            }
            $m->updata=serialize($row);
        }
        ////////////////////图片编辑///////////////////////

        if($vm['borrow_status']<>2 && $m->borrow_status==2){
            //新标提醒
            //newTip($m->id);
            MTip('chk8',$vm['borrow_uid'],$m->id);
            //自动投标
            if($m->borrow_type==1){
                memberLimitLog($vm['borrow_uid'],1,-($m->borrow_money),$info="{$m->id}号标初审通过");
            }elseif($m->borrow_type==2){
                memberLimitLog($vm['borrow_uid'],2,-($m->borrow_money),$info="{$m->id}号标初审通过");
            }
            $vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
            SMStip("firstV",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$m->id));
        }
        //if($m->borrow_status==2) $m->collect_time = strtotime("+ {$m->collect_day} days");
        if($m->borrow_status==2){
            $m->collect_time = strtotime("+ {$m->collect_day} days");
            //$m->is_tuijian = 1;
        }
        $m->borrow_interest = getBorrowInterest($m->repayment_type,$m->borrow_money,$m->borrow_duration,$m->borrow_interest_rate);
        //保存当前数据对象
        if($m->borrow_status==2 || $m->borrow_status==1) $m->first_verify_time = time();
        else unset($m->first_verify_time);
        unset($m->borrow_uid);
        $bs = intval($_POST['borrow_status']);

        $repayment_type=$m->repayment_type;
        if ($result = $m->save()) { //保存成功
            if($bs==2 || $bs==1){
                $verify_info['borrow_id'] = intval($_POST['id']);
                $verify_info['deal_info'] = text($_POST['deal_info']);
                $verify_info['deal_user'] = $this->admin_id;
                $verify_info['deal_time'] = time();
                $verify_info['deal_status'] = $bs;
                if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
                else  M('borrow_verify')->add($verify_info);
            }
            if($vm['borrow_status']<>2 && $_POST['borrow_status']==2 && $vm['can_auto']==1 && empty($vm['password'])==true) {
                autoInvest(intval($_POST['id']));
            }
            //if($vm['borrow_status']<>2 && $_POST['borrow_status']==2)) autoInvest(intval($_POST['id']));
            alogs("doEditWait",$result,1,'初审操作成功！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            if($bs==2)
               $this->check_additional_info($repayment_type);
           if($bs==1&&$vm['product_type']==6){
                $credit['uid'] = $vm['borrow_uid'];
                M('member_money')->where($credit)->setInc('credit_limit',$vm['borrow_money']);
            }
            $this->success(L('修改成功'),'/adminm/mborrow/waitverify');
        } else {
            alogs("doEditWait",$result,0,'初审操作失败！');//管理员操作日志
            //失败提示
            $this->error(L('修改失败'));
        }
    }

    public function doEditWaitverify2(){
        $token=intval($_POST['token']);
        $tokens=session("token");
        if(($tokens=="")||($token!=$tokens)){
            $this->error("动态口令错误");
            exit;
        }
        session("token",null);
        $m = D("Borrow");
        if (false === $m->create()) {
            $this->error($m->getError());
        }
        $vm = M('borrow_info')->field('borrow_uid,borrow_money,borrow_status,product_type,first_verify_time,updata,danbao,vouch_money,borrow_fee,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time,money_collect')->find($m->id);
        $info=D("borrow_info_additional")->get_additional_info($m->id);
        if($vm['borrow_type']==1){
                if($vm['borrow_money']<>$m->borrow_money ||
                            $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
                            $vm['borrow_duration']<>$m->borrow_duration ||
                            $vm['repayment_type']<>$m->repayment_type 
                     ){

                           $this->error('复审中的借款不能再更改‘还款方式’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’,‘担保机构’');
                           exit;
                   }
         }  else {
                if($vm['borrow_money']<>$m->borrow_money ||
                    $vm['borrow_interest_rate']<>$m->borrow_interest_rate ||
                    $vm['borrow_duration']<>$m->borrow_duration ||
                    $vm['repayment_type']<>$m->repayment_type ||
                    $vm['danbao'] <> $m->danbao||
                    $vm['vouch_money']<>$m->vouch_money
                ) {
                     $this->error('复审中的借款不能再更改‘还款方式’，‘借款金额’，‘年化利率’，‘借款期限’,‘借款管理费’');
                    exit;
                }
         }
        if((isset($_POST['colligate']))&&( $info['colligate']<>$_POST['colligate']) && $vm['product_type'] != 5){
            $this->error('复审中的借款不能再更改,担保服务费’');
            exit;
        }

        if($m->borrow_status<>5 && $m->borrow_status<>6){
            $this->error('已经满标的的借款只能改为复审通过或者复审未通过');
            exit;
        }

        ////////////////////图片编辑///////////////////////
        if(!empty($_POST['swfimglist'])){
            foreach($_POST['swfimglist'] as $key=>$v){
                $row[$key]['img'] = substr($v,1);
                $row[$key]['info'] = $_POST['picinfo'][$key];
            }
            $m->updata=serialize($row);
        }
        ////////////////////图片编辑///////////////////////
        //复审投标检测
        //$capital_sum1=M('investor_detail')->where("borrow_id={$m->id}")->sum('capital');
        $capital_sum2=M('borrow_investor')->where("borrow_id={$m->id}")->sum('investor_capital');
        if(($vm['borrow_money']!=$capital_sum2)){
            $this->error('投标金额不统一，请确认！');
            exit;
        }
        if($m->borrow_status==6){//复审通过
            // if($_POST["pay_type"] == 1){
            //     import("@.Oauth.sina.Sina");
            //     $sina = new Sina();
            //     $bindcard = $sina->querycard($vm['borrow_uid']);
            //     if(empty($bindcard)){
            //         $this->error('借款人没有绑卡，不能使用代付卡功能');
            //         exit;
            //     }
            //     $exp_data["is_tocard"] = 1;
            //     M("borrow_info_additional")->where("bid = {$m->id}")->save($exp_data);
            // }
           $appid = borrowApproved($m->id);
            if(!$appid){ $this->error("复审失败");exit;}
            $list = M("sinalog")->where("type=3 AND borrow_id = {$m->id} AND status = 2")->select();
            $a=0;
            $b=0;
            $c=0;
            $trade_list = null;
            $newbid=borrowidlayout1($m->id);
            foreach ($list as $i) {
                if($i['coupons'] != null && $i['coupons'] != ""){
                    $coupons_money = M('coupons c')->join("lzh_members m on m.user_phone = c.user_phone")->where("c.serial_number='".$i["coupons"]."' AND m.id = ".$i['uid'])->find();
                    $i["money"] = $i["money"]-$coupons_money['money'];
                }
                if($a<100){
                    if($b === 0){
                        $trade_list[$c] = date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第".$newbid."号标投资成功";
                        $b++;
                    }else{
                        $trade_list[$c] .= '$'.date('YmdHis').mt_rand( 100000,999999)."~".$i["order_no"]."~".$i["money"]."~第".$newbid."号标投资成功";
                    }
                    $a++;
                    if($a===100){$a = 0;$b=0;$c++;}
                }
            }
            foreach ($trade_list as $list) {
                sinafinishpretrade($list);
            }
            //更新时间,复审通过后执行
            D('borrow_info_additional')->update_review(intval($_POST['id']));
            //复审通过后，判断是否需要提前支付综合服务费
            $need=D("borrow_info_additional")->is_pay_frist(intval($_POST['id']));
            if($need && $vm['product_type'] != 5){
                $map['bid']=intval($_POST['id']);
                $map['uid']=$vm['borrow_uid'];
                $map['danbao_id']= $vm['danbao'];
                $map["danbao"] = $vm['vouch_money'];
                $map["fee"] = D("borrow_info_additional")->pay_first_money(intval($_POST['id']));
                D("Confirm")->addConfirmList($map);
            }
            if($vm['product_type'] != 5) {
                $vss = M("members")->field("user_phone,user_name")->where("id = {$vm['borrow_uid']}")->find();
                SMStip("approve", $vss['user_phone'], array("#USERANEM#", "ID"), array($vss['user_name'], $newbid));
            }
        }elseif($m->borrow_status==5){//复审未通过
            $appid = borrowRefuse($m->id,3);
            if(!$appid) $this->error("复审失败");
        }
        
        //保存当前数据对象
        $m->second_verify_time = time();
        unset($m->borrow_uid);
        $bs = intval($_POST['borrow_status']);
        if ($result = $m->save()) { //保存成功
            $verify_info['borrow_id'] = intval($_POST['id']);
            $verify_info['deal_info_2'] = text($_POST['deal_info_2']);
            $verify_info['deal_user_2'] = $this->admin_id;
            $verify_info['deal_time_2'] = time();
            $verify_info['deal_status_2'] = $bs;
            if($vm['first_verify_time']>0) M('borrow_verify')->save($verify_info);
            else  M('borrow_verify')->add($verify_info);


            //全木行冻结资金
                $allwood_config = C('ALLWOOD_ORDER');
                $order = M("allwood_ljs")->where("borrow_id = ".intval($_POST['id']))->find();
                if($order){
                    $order_no = $order["allwood_orderno"];
                    $datas["order_sn"] = $order_no;
                    $datas["collect_money"] =  $vm['borrow_money'];
                    $all_result = curl_post($allwood_config['DONG_URL'],$datas);
                    file_put_contents('javalog.txt', var_export($all_result,true), FILE_APPEND);
                }
            
            alogs("borrowApproved",$result,1,'复审操作成功！');//管理员操作日志
            if($bs==5&&$vm['product_type']==6){
                $credit['uid'] = $vm['borrow_uid'];
                M('member_money')->where($credit)->setInc('credit_limit',$vm['borrow_money']);
            }
            
            //成功提示
            $this->success('修改成功','/adminm/mborrow/waitverify2');
        } else {
            alogs("borrowApproved",$result,0,'复审操作失败！');//管理员操作日志
            //失败提示
            $this->error('修改失败');
        }

    }


    public function ajaxedit3(){
            $id=intval($_POST['id']);
            $status=intval($_POST['status']);
            if($status==1){
                $data['apply_status']=2;
                $where['id']=$id;
                M("borrow_info")->where($where)->save($data);
                import("@.sms.Notice");
                $notice=new Notice();
                $notice->replay($id);
                $notice->agreen_reply($id);
                echo "ok";
            }else if($status==2){   
                $data['apply_status']=0;
                $where['id']=$id;
                M("borrow_info")->where($where)->save($data);
                import("@.sms.Notice");
                $notice=new Notice();
                $notice->disagreen_reply($id);
                echo "ok";
            } 
    }

    public function doedit3($id){
        
        $where['a.id']=$id;
        $where['b.apply_status']=1;
        $where['b.borrow_status']=6;
        $result=M("borrow_info_additional a")->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($where)->select(); 
        
        $info['bid']=$result[0]['bid'];
        $info['user_name']=$result[0]['user_name'];
        $info['dur_text']=$result[0]['borrow_duration_txt'];
        $info['borrow_money']=$result[0]['borrow_money']."元";
        $info['borrow_interest_rate']=$result[0]['borrow_interest_rate']."%";
        $info['colligate_fee']=$result[0]['colligate_fee']."%";
        $info['borrow_duration']=$result[0]['borrow_duration']."天";
        $info['full_time']=date("Y-m-d",$result[0]['full_time']);
        $info['second_verify_time']=date("Y-m-d",$result[0]['second_verify_time']);
        $result[0]['deadline']=cal_deadline($result[0]['bid']);
        $info['end']=date("Y-m-d",$result[0]['deadline']);
        $b=  explode(']', $result[0]['apply_info']);
        $info['apply_info']=$b[1];
        $info['duration_time']=substr($b[0],1);
        if($result[0]['end_time']!="0"){
                $end_time=$result[0]['end_time'];
            }else{
                $end_time=strtotime("now");
            }
            $end=strtotime("now");
            
            if(($result[0]['product_type']==1)||($result[0]['product_type']==3)||($result[0]['product_type']==6)||($result[0]['product_type']==7)){
                $start_time=  $this->change_day($result[0]['second_verify_time']);//00：00：00 正点
                $diff=ceil(($end_time-$start_time)/3600/24);
                $info["cost"]=$this->cal_moeny($result[0],$diff,$result[0]['pay_frist']);
            }else{ //提单转现货
                $start_time=$this->change_day($result[0]['add_time']);
                $diff=ceil(($end_time-$start_time)/3600/24);
                if($result[0]['pay_frist'])
                    $cost=$this->cal_moeny($result[0],$diff,$result[0]['pay_frist'])+$result[0]["n_interest"];
                else
                     $cost=$this->cal_moeny($result[0],$diff,$result[0]['pay_frist'])+$result[0]['n_colligate_fee']+$result[0]["n_interest"];
            $info['cost']=$cost;
            $info['before_interest_rate']=$result[0]['frist_rate']."%";
            $info['before_server']=$result[0]['frist_server']."%";
            $info['change_data']=date("Y-m-d",$result[0]['add_time']);
            $send_time=$this->change_day($result[0]['second_verify_time']);
            $info['before_during']=(ceil(($result[0]['add_time']-$send_time)/3600/24)-1)."天";
            }
            return $info;
    }
}