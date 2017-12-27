<?php
 /**
     * 庆祝链金所 A 轮融资成功, 活动页面
     * 1. 首投返现
     * 2. 投资返利
     * 3. 投资抽奖
     * 4. 投资额度兑换奖品
     */
class VcAction extends HCommonAction
{
    public function _initialize(){
        parent::_initialize();
        $title="网络借贷,网络理财,P2P网贷有哪些安全保障措施？信任首选链金所";
        $keyword="网络借贷有哪些安全保障措施,网络理财有哪些安全保障措施,P2P网贷有哪些安全保障措施";
        $description="链金所结合金融专业的风控手段,为用户提供全面的网络贷款,网络借贷,网络理财,P2P理财,P2P网贷安全保障服务.更好的保障投资人的资金安全.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
    }
    

    public function index()
    {
        //token 值crsf 用
        $token=mt_rand(1000000,9999999);
        session("token",$token);
        // 获取可投次数
        $totalIndex = $this->getLeftCount();
        // 获取活动开始后的总投资金额
        

        $this->assign("left",$totalIndex['total']);
        $this->assign("uid",$this->uid?$this->uid:-1);
        $this->assign("token",$token);
        $this->assign("ret",$this->getReturn());
        $this->assign('investTotal',$this->getInvestTotal());
        $this->display();
    }

    public function getRange()
    {
        $start = date('Y-m-d H:i:s',C('VC_FROM'));
        $end = date('Y-m-d H:i:s',C('VC_TO'));
        $block = (time()<=C('VC_FROM'))||(time()>=C('VC_TO'));
        exit($start.'----'.$end.'  block = '.json_encode($block));
    }

    /**
     * 开始活动
     * @return [type] [description]
     */
    public function rockAndRoll()
    {
        // if (trim($_GET['username'])!="lushixin"||trim($_GET['pwd'])!="lushixin")
        // {
        //     exit(" You are not authorized!");
        // }

        // $countBefore = M('members')->where(array('dream_invest_total'=>array("GT",0)))->count();
        // $result = M('members')->execute("update lzh_members set dream_invest_total=0,dream_invested=0");
        // $countAfter = M('members')->where(array('dream_invest_total'=>array("GT",0)))->count();
        // exit("vc active rock and roll , before = {$countBefore}, exec result = {$result}, after = {$countAfter}");
    }

    /**
     * dream log 转 记录
     * @return [type] [description]
     */
    public function logToRecord()
    {
        if (trim($_GET['username'])!="lushixin"||trim($_GET['pwd'])!="lushixin")
        {
            exit(" You are not authorized!");
        }    

        $code = M('vc_tmp')->where(['type'=>101])->find();
        if (null == $code)
            exit(" can not find vc_tmp record!");

        $list = M('dream_log')->where(['type'=>101,'id'=>array('gt',$code['log_id'])])->limit(100)->select();
        if ($list == null){
            exit('success');
        }

        foreach ($list as $key => $item) {
            //解析 uid 奖品信息
            $desc = $item['desc'];
            //exit(json_encode($desc));
            preg_match('/uid = (\d*) vcindex = (\d*) prize info = (\d*)/',$desc,$tmpmath);

            if (sizeof($tmpmath)<4){
                $logdata['create_time'] = time();
                $logdata['desc'] ='logid='.$item['id'].'  len<4 fail';
                $logdata['type'] = 200;
                M('dream_log')->add($logdata);
                continue;
            }

            $uid    = $tmpmath[1];
            $vindex = $tmpmath[2];
            $val    = $tmpmath[3];
            
            if (intval($uid) === false){
                $logdata['create_time'] = time();
                $logdata['desc']        ='logid='.$item['id'].'  uid not digit fail';
                $logdata['type']        = 201;
                M('dream_log')->add($logdata);
                continue;
            }

            //根据 uid 修改 vc_count 中打 consumno
            $this->increConsume($uid, $item['id'],$val);
            
        }
        exit('complete');
    }

    /**
     * vc_count 中 consumeno 加 1
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    private function increConsume($uid,$log_id,$val)
    {
        //exit('uid='.$uid.'   log_id='.$log_id.'  val='.$val);
        $info = M('vc_count')->where(['uid'=>$uid])->find();
        if ($info == null){
            $logdata['create_time'] = time();
            $logdata['desc']        ='logid='.$log_id.'  uid = '.$uid.'  vc_count record not found';
            $logdata['type']        = 203;
            M('dream_log')->add($logdata);
            return;
        }

        $info['consume_no'] = array('exp','consume_no+1');
        if ($val > 0&&$info['prize']== null)
        {
            $info['prize'] = $val.'元';
        }elseif($val>0){
            $info['prize'] = $info['prize'].', '.$val.'元';
        }else{
            $logdata['create_time'] = time();
            $logdata['desc'] ='logid='.$log_id.' prize eq 0 ';
            $logdata['type'] = 202;
            M('dream_log')->add($logdata);
        }

        $result = M('vc_count')->save($info);
        if($result)
        {
            $update = M('vc_tmp')->where(['type'=>101])->find();
            if ($update['log_id']<$log_id)
            {
                $update['log_id'] = $log_id;
                M('vc_tmp')->save($update);
            }
        }
    }

    /**
     * 单击转盘获取结果
     */
    public function getResult(){
        if(empty($this->uid)){
            $this->message(1,"请登录");//没有登录，请登录
        }
        $token=mt_rand(1000000,9999999);

        //是否开启
        $enabled = time()>=C('VC_FROM')&&time()<=C('VC_TO');
        if ($enabled)
        {
            session("token",$token);
        }else{
            $this->ajaxFail("活动已经结束",['left'=>0,'angle'=>"0"]);
        }

        $mytoken=$_POST["token"];
        //防止页面狂提交或者用脚本攻击
        // if(!$mytoken ||  session("token")!=$mytoken){
        //     session("token",$token);
        //     $this->ajaxFail("页面错误,请刷新重试!",['left'=>0,'angle'=>"0"]);
        // }

        $totalIndex=$this->getLeftCount();//抽奖剩余次数
        $count = $totalIndex['total'];
        $index = $totalIndex['index'];

        //获得返利金额
        $ret = $this->getReturn();

        if($count<=0){
            $this->ajaxFail('您抽奖机会已经用完，请再接再厉',['left'=>0,'angle'=>"0","token"=>session('token'),"ret"=>$ret]);
        }

        $a=mt_rand(0,10000);

        if($index == -1){
            //报错
        }else{
            $min = "minnum_".$index;
            $max = "maxnum_".$index;
            $where[$min] = array('ELT',$a);
            $where[$max] = array('EGT',$a);
            
        }
        $res =M("vc_prize")->where($where)->find();
        if($res == null){
            $data['left'] = $count;
            $data['angle'] = 0;
            $data['token'] = session('token');
            $data['ret'] = $ret;
            $this->ajaxFail('没有找到奖品信息',$data);
        }else{
            $model = M('vc_count');
            $model->startTrans();
            try{
                $message = $res['type'] == 0?intval($res['value']):0;
                $data['left'] = $count -1;
                $data['angle'] = $res['angle'];
                $data['token'] = session('token');
                $data['ret'] = $ret;

                if($res['type'] == 0){
                    // 插入投资券记录
                    //$this->addCoupon($this->uid,$res['value']);
                    $this->commissionAlloc($this->uid,$res['value']);
                }

                //更新抽奖次数信息
                $this->decreaseVcCount($index,$this->uid,$res['value']);

                //写入日志
                $this->vclog("uid = {$this->uid} vcindex = {$index} prize info = {$message}");
                $model->commit();
                //返回
                $this->ajaxSuccess($message,$data);
            }catch(Exception $e){
                //写入日志
                $this->vclog("failed, uid = {$this->uid} vcindex = {$index} prize info = {$message}");    
                $model->rollback();
                $this->ajaxFail("抽奖失败", $data);
            }
        }

    }

    /**
     * 大转盘抽奖获现金
     * @return [type] [description]
     */
    private function commissionAlloc($uid, $money)
    {
        $total = $money;
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $result = $sina->collecttradecompany($total, "融资大转盘抽奖");
        if ($result == "APPLY_SUCCESS") {
            
            $order_no = date('YmdHis') . mt_rand(100000, 999999);
            $account_type = 'SAVING_POT';
            $val = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$uid.'~UID~'.$account_type.'~'.$total.'~~大转盘抽奖获现金返现';
            $sina->batchpaytrade($order_no, "", "", $val, "vcCommission2");
            sinalog(0, "", 25, $order_no, $total, time(), 0);
        }
    }

    /**
     * 获取投资人
     * @return [type] [description]
     */
    public function getRecom()
    {
        if(intval($this->uid) === false)
        {
            $data['list'] = [];
            $this->ajaxSuccess('success',$data);
        }
        $list = M('vc_recom')->where(array('parent_id'=>$this->uid))->order('invest_money desc,create_time desc')->select();
        if (empty($list)){
            $datalist = [];
        }else{
            $datalist = [];
            //最多显示4个人
            $limit = 0;
            foreach ($list as $key => $value) {
                $limit  = $limit + 1;
                $tmp['user_phone'] = substr_replace($value['user_phone'], "****", 3, 4);
                $tmp['isInvested'] = $value['invest_money'] > 0 ?true:false;
                $tmp['money'] = $limit > 4 ? 0:($tmp['isInvested']?5:0);
                $datalist[] = $tmp;

            }
        }
        $data['list'] = $datalist;
        $this->ajaxSuccess('success',$data);
    }

    /**
     * ajax输出消息
     * @param $status
     * @param $message
     * @param array $data
     */
    private function message($status,$message,$data=array()){
        $da=[];
        $da["status"]=$status;
        $da["message"]=$message;
        if(count($data)){
            $da["data"]=$data;
        }
        echo json_encode($da);
        exit();
    }

    /**
     * 剩余次数和中奖序列
     */
    private function getLeftCount(){
        $ret=0;
        $apr_beanModel=M("vc_count");
        $list=$apr_beanModel->where(array("uid"=>$this->uid))->find();
        if($list){
            $total = $list['count_0'] + $list['count_1'] + $list['count_2']+ $list['count_3']+ $list['count_4'];
            if($list['count_4'] >= 1){
                $index = 4;
            }elseif($list['count_3'] >= 1){
                $index = 3;
            }elseif($list['count_2'] >= 1){
                $index = 2;
            }elseif($list['count_1'] >=1){
                $index = 1;
            }elseif($list['count_0'] >=1){
                $index = 0;
            }else{
                $index = -1;
            }

        }else{
            $total = 0;
            $index = 0;
        }
        
        return ['total'=>$total,'index'=>$index];
    }

    /**
     * 送投资券
     * @param [type] $phone [description]
     * @param [type] $value [description]
     */
    private function addCoupon($uid, $value)
    {
        $usrinfo = M('members')->where(array('id'=>$uid))->find();

        if($usrinfo == null){
            throw new Exception("没有找到用户信息", 1);
            
        }

        //判断手机号有效性

        M("coupons")->add(array(
                            "user_phone"=>$usrinfo['user_phone'],
                            "money"=>$value,
                            "endtime"=>strtotime("+3 month",time()),
                            "status"=>0,
                            "serial_number"=>date('YmdHis').mt_rand(100000,999999),
                            "type"=>1,
                            "name"=>"转盘抽奖送".$value."元投资券",
                            "addtime"=>date("Y-m-d H:i:s",time()),
                            "isexperience"=>1,
                            "use_money"=>$value*100
                        ));
    }

    private function ajaxJson($code,$message,$data)
    {
        $tmp['code'] = $code;
        $tmp['message'] = $message;
        $tmp['data'] = $data;
        exit(json_encode($tmp));
    }

    private function ajaxSuccess($message,$data)
    {
        $this->ajaxJson(1,$message,$data);
    }

    private function ajaxFail($message,$data)
    {
        $this->ajaxJson(0,$message,$data);
    }

    private function decreaseVcCount($index,$uid,$val)
    {
        $info = M('vc_count')->where(['uid'=>$uid])->find();

        if(null == $info)
            return;

        $field = "count_".$index;
        $update['count_'.$index] = array('exp',"{$field}-1");
        $update['consume_no'] = array('exp','consume_no+1');

        if ($val > 0&&$info['prize']== null)
        {
            $update['prize'] = $val.'元';
        }elseif($val>0){
            $update['prize'] = $info['prize'].', '.$val.'元';
        }else{
        }

        $res = M('vc_count')->where(array("uid"=>$uid))->save($update);
        if($res === false){
            throw new Exception("数据库更新失败", 1);
        }
    }

    /**
     * 记录日志 type = 101
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    private function vclog($message)
    {
        $logdata['create_time'] = time();
        $logdata['desc'] =$message;
        $logdata['type'] = 101;
        M('dream_log')->add($logdata);
    }

    /**
     * 新注册用户投资返利
     * 100-999     5
     * 1000-4999  15
     * 5000-19999 25
     * 200000 -   35
     * @return [type] [description]
     */
    private function getReturn()
    {
        if (!$this->uid)
        {
            return 0;
        }

        $result = M('vc_recom')->where(array('uid'=>$this->uid))->find();
        if ($result == null)
        {
            return 0;
        }else{
            $invest = intval($result['invest_money']);
            if ($invest>=20000)
            {
                return 35;
            }elseif($invest >=5000){
                return 25;
            }elseif($invest >=1000){
                return 15;
            }elseif($invest >=100){
                return 5;
            }else{
                return 0;
            }
        }
    }

    /**
     * 获取8月份活动后的累计投资金额
     */
    private function getInvestTotal()
    {
        if (!$this->uid)
        {
            return 0;
        }

        $result = M('members')->where(array('id'=>$this->uid))->find();

        if ($result == null)
        {
            return 0;
        }else{
            return $result['dream_invest_total'];
        }
    }

    /**
     * 获得每个月的投资人增量和借款人增量
     * @return [type] [description]
     */
    public function getDelta()
    {
        for ($i=0; $i < 13; $i++) { 
            $current = strtotime("-".$i." month");
            $previous = strtotime("-".($i+1)." month");
            $after = strtotime("-".($i-1)." month");

            // firt day 
            $curFirst = date("Y-m-1 00:00:00",$current);
            $afterFist = date("Y-m-1 00:00:00",$after);
            $preFirst = date("Y-m-1 00:00:00",$previous);
            //echo 'pre='.$preFirst.'  cur='.$curFirst.'    after='.$afterFist;

            // timestamp
            $curTime = strtotime($curFirst);
            $preTime = strtotime($preFirst);
            $afterTime = strtotime($afterFist);

            //get previous id list
            $cond['add_time'] = array('lt',$curTime);
            $idlist = M('borrow_info')->field('borrow_uid')->where($cond)->group('borrow_uid')->select();
            $idlist = array_column($idlist, "borrow_uid");
            //echo json_encode($idlist);

            $con['borrow_uid'] = array("not in",$idlist);
            $con['add_time'] = array("between",$curTime.','.$afterTime);
            $ids = M('borrow_info')->where($con)->group('borrow_uid')->count();
            $ids2 = M('borrow_info')->field('borrow_uid')->where($con)->group('borrow_uid')->select();
            $ids2 = array_column($ids2, "borrow_uid");
            //echo M('borrow_info')->getLastSql();
            if (null == $ids)
                $ids = 0;

            //echo 'date ='.$curFirst.'  delta = '.$ids.' ids = '.json_encode($ids2);
            echo 'date ='.$curFirst.'  delta = '.$ids;
            echo "<br/>";
        }

        echo 'investor_uid'."<br/>";

        for ($i=0; $i < 13; $i++) { 
            $current = strtotime("-".$i." month");
            $previous = strtotime("-".($i+1)." month");
            $after = strtotime("-".($i-1)." month");

            // firt day 
            $curFirst = date("Y-m-1 00:00:00",$current);
            $afterFist = date("Y-m-1 00:00:00",$after);
            $preFirst = date("Y-m-1 00:00:00",$previous);

            // timestamp
            $curTime = strtotime($curFirst);
            $preTime = strtotime($preFirst);
            $afterTime = strtotime($afterFist);

            //get previous id list
            $cond['add_time'] = array('lt',$curTime);
            $idlist = M('borrow_investor')->field('investor_uid')->where($cond)->group('investor_uid')->select();
            $idlist = array_column($idlist, "investor_uid");
            //echo json_encode($idlist);

            $con['investor_uid'] = array("not in",$idlist);
            $con['add_time'] = array("between",$curTime.','.$afterTime);
            $ids = M('borrow_investor')->where($con)->group('investor_uid')->count();
            $ids2 = M('borrow_investor')->field('investor_uid')->where($con)->group('investor_uid')->select();
            $ids2 = array_column($ids2, "investor_uid");
            //echo M('borrow_info')->getLastSql();
            if (null == $ids)
                $ids = 0;

            //echo 'date ='.$curFirst.'  delta = '.$ids.'  ids='.json_encode($ids2);
            echo 'date ='.$curFirst.'  delta = '.$ids;
            echo "<br/>";
        }
        
    }

    public function repair()
    {
        //23549
        // $code = M('vc_tmp')->where(['type'=>102])->find();
        // if (null == $code)
        //     exit(" can not find vc_tmp record!");

        // //获取8.4号后且id>vc_tmp中记录打投资的所有用户的数据
        // $con['add_time'] = array("gt","1501776000");
        // $con['id'] = array('gt',$code['log_id']);
        // $list = M('borrow_investor')->where($con)->limit(500)->select();
        // if ($list == null){
        //     exit('success');
        // }

        // foreach ($list as $key => $item) {
        //     //获取投资金额和用户id
        //     $investor_uid     = $item['investor_uid'];
        //     $investor_capital = $item['investor_capital'];
        //     $id               = $item['id'];
        //     //echo 'id='.$id.' uid='.$investor_uid.'  capital='.$investor_capital;

        //     //根据 uid 修改 vc_count 中打 consumno
        //     $this->increInvest($investor_uid, $id,$investor_capital);
            
        // }
        // exit('complete,tobe continue');
    }


     /**
     * 增加投资金额
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    private function increInvest($uid,$borrow_investor_id,$capital)
    {
        // $info = M('members')->where(['id'=>$uid])->find();
        // //echo 'info = '.json_encode($info);
        // if ($info == null){
        //     $logdata['create_time'] = time();
        //     $logdata['desc']        ='id='.$uid.' user record not found';
        //     $logdata['type']        = 303;
        //     M('dream_log')->add($logdata);
        //     exit();
        // }

        // // 增加用户投资金额
        // $info['dream_invest_total'] = array('exp','dream_invest_total+'.$capital);
        // $result = M('members')->save($info);

        // if($result)
        // {
        //     $update = M('vc_tmp')->where(['type'=>102])->find();
        //     if ($update['log_id']<$borrow_investor_id)
        //     {
        //         $update['log_id'] = $borrow_investor_id;
        //         M('vc_tmp')->save($update);

        //         $logdata['create_time'] = time();
        //         $logdata['desc']        ="borrow_investor_id = {$borrow_investor_id} , uid={$uid}, invest money = {$capital}";
        //         $logdata['type']        = 305;
        //         M('dream_log')->add($logdata);
        //     }
        // }
    }


}