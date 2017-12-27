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

        public function index()
        {
            //token 值crsf 用
            $token=mt_rand(1000000,9999999);
            session("token",$token);
            // 获取可投次数
            $totalIndex = $this->getLeftCount();

            $this->assign("left",$totalIndex['total']);
            $this->assign("uid",$this->uid?$this->uid:-1);
            $this->assign("token",$token);
            $this->assign("ret",$this->getReturn());
            $this->assign('investTotal',$this->getInvestTotal());
            $this->display();
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

    }
?>
