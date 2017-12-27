<?php
    class borrow_info_additionalModel  extends ACommonModel {

      public function add_item($id){
            $data['bid'] = $id;
            $data['frist_rate']= floatval($_POST['borrow_interest_rate']);
            $data['frist_server']=floatval( $_POST['colligate_fee']);
            if( ($_POST['product_type']==1)&&(isset($_POST['second_duration']))&&($_POST['second_duration']>0)){
                $data['second_rate']=floatval( $_POST['seconde_rate']);
                $data['second_server']=floatval($_POST['second_server']);
            }
            if($_POST['start_return_day']!=""){
                 $start_return_day=strtotime($_POST['start_return_day']);
                 $start_return_day=strtotime(date("Y-m-d 23:59:59",$start_return_day));
                 $data['start_return_day']=$start_return_day;
            }
            if($_POST['colligate']!=''){
                $data['colligate']=floatval($_POST['colligate']);
            }
            $data['pay_frist']=1; //提前手续手续费


            $this->add($data);
        }

        public function update_review($id){
            $data['frist_time']=strtotime("now");
            $where['bid']=$id;
            $this->where($where)->save($data);
        }

        public function second_xianhuo($id){
            $where['bid']=$id;
            $data['second_time']=strtotime("now");
            $data['second_rate']=text($_POST['borrow_interest_rate']);
            $data['second_server']=text($_POST['colligate_fee']);

            $this->where($where)->save($data);
        }

        public function update_end($id){
            $where['bid']=$id;
            $data['end_time']=strtotime("now");
            $this->where($where)->save($data);
        }

        /*************************
         *
         */
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
            $investor_uid = M('investor_detail')->where('borrow_id='.$binfo['bid'] . ' and is_debt =0')->select();
            foreach ($investor_uid as $iteme) {
                $tou_interest = getFloatValue($iteme['capital']*$day_rate*$diff, 2);
                $custom_fee += $tou_interest;
                //$Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id}");
            }
            $cost=$colligate_fee+$custom_fee+$binfo["borrow_money"];
            return $cost;

        }
        /************************************
         * @param int $end
         * $end==1 默认表示结束标
         * $end=0: 正在还款的标
         */

        public  function get_borrow_info($end=1,$limit,$where){
            $where['frist_time']=array("neq",0);//不等于0表示开始还款
            if($end){
                $where['end_time']=array("neq",0);
            }else{
                $where['end_time']=0;
            }

            $field="b.id,b.borrow_duration_txt,b.borrow_name,b.borrow_uid,b.borrow_money,b.borrow_interest_rate,b.borrow_duration,b.full_time,b.second_verify_time,b.colligate_fee,b.deadline,b.n_interest,b.n_colligate_fee,b.product_type,b.add_time,b.product_type,b.danbao,a.*,m.user_name";
            $result=M("borrow_info_additional a")->field($field)->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($where)->limit($limit)->order("a.bid DESC")->select();
            $info=array();
            foreach($result as $key=>$val){
                $info[$key]['id']=$val['bid'];
                $info[$key]['user_name']=$val['user_name'];
                $info[$key]['borrow_uid']=$val['borrow_uid'];
                $info[$key]['bname']=$val['borrow_name'];
                $info[$key]['product_type']=$val['product_type'];
                $info[$key]['danbao']=$val['danbao'];
                $info[$key]['borrow_money']=$val['borrow_money']."元";
                $info[$key]['borrow_interest_rate']=$val['borrow_interest_rate']."%";
                $info[$key]['colligate_fee']=$val['colligate_fee']."%";
                $info[$key]['borrow_duration']=$val['borrow_duration']."天";
                $info[$key]['full_time']=date("Y-m-d",$val['full_time']);
                $info[$key]['second_verify_time']=date("Y-m-d",$val['second_verify_time']);
                $val['deadline']=cal_deadline($val['bid']);
                $info[$key]["end"]=date("Y-m-d",$val['deadline']);
                $info[$key]["dur_text"]=$val['borrow_duration_txt'];
                if($val['end_time']!="0"){
                    $end_time=$val['end_time'];
                }else{
                    $end_time=strtotime("now");
                }
                $end=strtotime("now");
                if(($val['product_type']==1)||($val['product_type']==3)||($val['product_type']==6)||($val['product_type']==7)||($val['product_type']==8)){
                    $start_time=$this->change_day($val['second_verify_time']);//00：00：00 正点
                    $diff=ceil(($end_time-$start_time)/3600/24);
                    $info[$key]["cost"]=$this->cal_moeny($val,$diff,$val['pay_frist']);
                }else{ //提单转现货
                    $start_time=$this->change_day($val['add_time']);
                    $diff=ceil(($end_time-$start_time)/3600/24);
                    if($val['pay_frist'])
                        $cost=$this->cal_moeny($val,$diff,$val['pay_frist'])+$val["n_interest"];
                    else
                         $cost=$this->cal_moeny($val,$diff,$val['pay_frist'])+$val['n_colligate_fee']+$val["n_interest"];
                    $info[$key]["cost"]=$cost;
                    $info[$key]['before_interest_rate']=$val['frist_rate']."%";
                    $info[$key]['before_server']=$val['frist_server']."%";
                    $info[$key]['change_data']=date("Y-m-d",$val['add_time']);
                    $send_time=$this->change_day($val['second_verify_time']);//00：00：00 正点
                    $info[$key]['before_during']=(ceil(($val['add_time']-$send_time)/3600/24)-1)."天";//提单转现货当天转为现货时间
                }

            }
            return $info;
        }
        /********************************
         * 更新申请状态，
         * borrow_info_additional 是后面加的数据库，在更新申请状态的时候，同步数据，目前最好的选择
         *
         */
        public  function  apply_repayment($id,$info){
            //1. 判断id是否存在
            $where['bid']=$id;
            $result=$this->where($where)->select();
            if(count($result)==0){
                $seach['id']=$id;
                $field="b.second_verify_time,b.borrow_interest_rate,b.colligate_fee";
                $binfo=M("borrow_info b")->field($field)->where($seach)->select();
                $data['bid']=$id;
                $data['frist_time']=$binfo[0]['second_verify_time'];
                $data['frist_rate']=$binfo[0]['borrow_interest_rate'];
                $data['frist_server']=$binfo[0]['colligate_fee'];
                $data['apply_info']=$info;
                $this->add($data);
            }else{
                $data['apply_info']=$info;
                $where['bid']=$id;
                $this->where($where)->save($data);
            }
        }
        /*****************************************************
         * 待审核的借款信息
         */
        public  function  get_replay_borrow(){
            $where['b.apply_status']=1;
            $where['b.borrow_status']=6;
            $field="b.id,b.borrow_duration_txt,b.borrow_name,b.borrow_uid,b.borrow_money,b.borrow_interest_rate,b.borrow_duration,b.full_time,b.second_verify_time,b.colligate_fee,b.deadline,b.n_interest,b.n_colligate_fee,b.product_type,b.add_time,b.product_type,b.danbao,a.*,m.user_name";
            $result=M("borrow_info_additional a")->field($field)->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($where)->select();
            $info=array();
            foreach($result as $key=>$val){
                $info[$key]['id']=$val['bid'];
                $info[$key]['user_name']=$val['user_name'];
                $info[$key]['bname']=$val['borrow_name'];
                $info[$key]['product_type']=$val['product_type'];
                $info[$key]['danbao']=$val['danbao'];
                $info[$key]['borrow_money']=$val['borrow_money']."元";
                $info[$key]['borrow_interest_rate']=$val['borrow_interest_rate']."%";
                $info[$key]['colligate_fee']=$val['colligate_fee']."%";
                $info[$key]['borrow_duration']=$val['borrow_duration']."天";
                $info[$key]['full_time']=date("Y-m-d",$val['full_time']);
                $info[$key]['second_verify_time']=date("Y-m-d",$val['second_verify_time']);
                $val['deadline']=cal_deadline($val['bid']);
                $info[$key]["end"]=date("Y-m-d",$val['deadline']);
                $info[$key]["dur_text"]=$val['borrow_duration_txt'];
                $info[$key]['apply_info']=$val['apply_info'];
                $info[$key]['borrow_uid']=$val['borrow_uid'];
                if($val['end_time']!="0"){
                    $end_time=$val['end_time'];
                }else{
                    $end_time=strtotime("now");
                }
                $end=strtotime("now");
                if(($val['product_type']==1)||($val['product_type']==3)||($val['product_type']==6)||($val['product_type']==7)||($val['product_type']==8)||($val['product_type']==10)){
                    $start_time=$this->change_day($val['second_verify_time']);//00：00：00 正点
                    $diff=ceil(($end_time-$start_time)/3600/24);
                    $info[$key]["cost"]=$this->cal_moeny($val,$diff,$val['pay_frist']);
                }else{ //提单转现货
                    $start_time=$this->change_day($val['add_time']);
                    $diff=ceil(($end_time-$start_time)/3600/24);
                    if($val['pay_frist'])
                        $cost=$this->cal_moeny($val,$diff,$val['pay_frist'])+$val["n_interest"];
                    else
                         $cost=$this->cal_moeny($val,$diff,$val['pay_frist'])+$val['n_colligate_fee']+$val["n_interest"];
                    $info[$key]["cost"]=$cost;
                    $info[$key]['before_interest_rate']=$val['frist_rate']."%";
                    $info[$key]['before_server']=$val['frist_server']."%";
                    $info[$key]['change_data']=date("Y-m-d",$val['add_time']);
                    $send_time=$this->change_day($val['second_verify_time']);//00：00：00 正点
                    $info[$key]['before_during']=(ceil(($val['add_time']-$send_time)/3600/24)-1)."天";//提单转现货当天转为现货时间
                }

            }
            return $info;
        }

        /***********************************
         * save
         */
        public function save_extra_info($id,$extra_info){
            //1. 判断id是否存在
            $where['bid']=$id;
            $result=$this->where($where)->select();
            if(count($result)==0){
                $seach['id']=$id;
                $field="b.second_verify_time,b.borrow_interest_rate,b.colligate_fee";
                $binfo=M("borrow_info b")->field($field)->where($seach)->select();
                $data['bid']=$id;
                $data['frist_time']=$binfo[0]['second_verify_time'];
                $data['frist_rate']=$binfo[0]['borrow_interest_rate'];
                $data['frist_server']=$binfo[0]['colligate_fee'];
                $data['extra_info']=$extra_info;
                $this->add($data);
            }else{
                $data['extra_info']=$extra_info;
                $where['bid']=$id;
                $this->where($where)->save($data);
            }

        }

        public function get_extra_info($id){
            $where['bid']=$id;
            $field='extra_info';
            $result=$this->where($where)->field($field)->select();
            $info=$result[0]['extra_info'];
            return $info;
        }
        public function get_additional_info($id){
            $where['bid']=$id;
            $result=$this->where($where)->select();
            $info=$result[0];
            return $info;
        }
        public function get_return_day($id){
            $where['bid']=$id;
            $field="start_return_day";
            $time_info=$this->where($where)->field($field)->select();
            if($time_info[0]['start_return_day']!=0)
                return $time_info[0]['start_return_day'];
            else
                return 0;
        }
        //提前还款1
        public function is_pay_frist($id){
            $where['bid']=$id;
            $info=$this->where($where)->select();
            return $info[0]['pay_frist'];
        }
        public function pay_first_money($id){
            $where['bid']=$id;
            $info=$this->where($where)->select();
            $money=getFloatValue($info[0]['colligate'],2);
            return $money ;
        }

    }
?>