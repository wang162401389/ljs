<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/14
 * Time: 16:00
 */
class borrow_expired{
    var $enable;
    var $expired__day;
    var $expired__money;
    var $borrow_money;
    var $expired_rate;
    var $is_expired;
    var $expired_list;
    var $borrow_id;

    function __construct($borrow_id,$sort_order=1){
        $add=C('ADD_FUNCTION');
        $this->enable=$add['borrow_expired']['enable'];
        $this->borrow_id=$borrow_id;
        if($this->enable){
            $map['i.borrow_id']=$borrow_id;
            $map['i.sort_order']=$sort_order;
            $map['status']=array("neq",-1);
            $filed="i.deadline,bi.repayment_type,bi.borrow_money";
            $info=M("investor_detail i")->join("lzh_borrow_info as bi on bi.id=i.borrow_id")->field("i.deadline,bi.repayment_type,i.substitute_time,i.repayment_time")->where($map)->find();
            if(($info['deadline']>=time())||($info['substitute_time']!=0)||($info['repayment_time']!=0)){
                $this->is_expired=0;
                $this->expired__day=0;
                $this->expired__money=0;
            }else if($info['repayment_type']==1){
				$this->is_expired=1;
                $deadline=cal_deadline($borrow_id);
                $this->expired__day=ceil( (time()-$deadline)/3600/24 );
                $this->borrow_money=$info['borrow_money'];
                $this->expired_rate=0.003;
                $investor_uid = M('investor_detail')->where('borrow_id='.$borrow_id." and status!=-1")->select();
                $this->expired__money=0;
                foreach ($investor_uid as $iteme) {
                    $money = getFloatValue(($iteme['capital']+$iteme['interest'])*$this->expired__day* $this->expired_rate, 2);
                    $expired_list['money']=$money;
                    $expired_list['investor_uid']=$iteme['investor_uid'];
                    $expired_list['where']=array("id"=>$iteme['id']);
                    $this->expired_list[]=$expired_list;
                    $this->expired__money+=$money;
                }
                $message="天标标号".borrowidlayout1($info['id'])."逾期".$this->expired__day."天还有".$this->borrow_money."未还"."逾期罚款金额".$this->expired__money;
                file_put_contents("log.txt",$message."\n\r",FILE_APPEND);
                //file_put_contents("log.txt",var_export($this->expired_list)."\n\r",FILE_APPEND);
            }else{
				$this->is_expired=1;
                $deadline=$info['deadline'];
                $this->expired__day=ceil( (time()-$deadline)/3600/24 );
                $this->expired_rate=0.003;
                $where1['borrow_id']=$borrow_id;
                $where1['sort_order']=array("egt",$sort_order);
                $where1["status"]=array("neq",-1);
                $filed="sum(capital) as total_catial,invest_id,investor_uid,sum(interest) as total_interest";
                $result=M("investor_detail")->field($filed)->where($where1)->group("invest_id")->order("id")->select();
                //计算总金额
                $this->expired__money=0;
                foreach($result as $key=>$val){
                    $money = getFloatValue(($val['total_catial']+$val['total_interest'])*$this->expired__day* $this->expired_rate, 2);
                    $expired_list['money']=$money;
                    $expired_list['investor_uid']=$val['investor_uid'];
                    $expired_list['where']=array("borrow_id"=>$borrow_id,"sort_order"=>$sort_order,"invest_id"=>$val['invest_id']);
                    $this->expired_list[]=$expired_list;
                    $this->expired__money+=$money;
                }
                $message="月标标号".borrowidlayout1($info['id'])."逾期".$this->expired__day."天还有".$this->borrow_money."未还"."逾期罚款金额".$this->expired__money;
                file_put_contents("log.txt",$message."\n\r",FILE_APPEND);
               // file_put_contents("log.txt",var_export($this->expired_list)."\n\r",FILE_APPEND);
            }

        }
    }
    public function  get_expired__money(){
        return $this->expired__money;
    }
    public function get_expired_day(){
        return $this->expired__day;
    }
    public function update_expired_info(){
        if($this->is_expired==0){
            return 0;
        }
        foreach($this->expired_list as $key=>$val){
            $where=$val['where'];
            $where['status']=array("neq",-1);
            $date['expired_money']=$val['money'];
            $date['expired_days']=$this->expired__day;
			Log::write("更新逾期信息");
			Log::write(var_export($where,true));
            M("investor_detail")->where($where)->save($date);
        }
        return 0;
    }
    public function is_expired(){
        return $this->is_expired;
    }
	 private function save_money_log($type,$uid,$money,$repayment_name){
        $accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($uid);
        $datamoney['uid'] =$uid;
        $datamoney['type'] = "30";
        $datamoney['affect_money'] = $money;//利息加本金
        $investor_detail = M('investor_detail');
        $collect = $investor_detail->where('investor_uid= '.$datamoney['uid'].' AND repayment_time = 0 and status!=-1')->sum('capital+interest');

        if($collect == null){
            $collect = 0;
        }
        // 从借款人账户减掉本金加利息
        // $accountMoney['money_collect'] 到期还的利息   $datamoney['affect_money']提前还按天的利息
        $datamoney['collect_money'] = $collect;
        $datamoney['freeze_money'] = $accountMoney['money_freeze'];
        ///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////
        //$binfo borrow_info表查询 borrow_type标的类型
        if($this->borrow_type<>3 ){//如果不是秒标，那么回的款会进入回款资金池，如果是秒标，回款则会进入充值资金池
            $datamoney['account_money'] = $accountMoney['account_money'];
            $datamoney['back_money'] = ($accountMoney['back_money'] + $datamoney['affect_money']);
        }else{
            $datamoney['account_money'] = $accountMoney['account_money'] + $datamoney['affect_money'];
            $datamoney['back_money'] = $accountMoney['back_money'];
        }

        ///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////

        //会员帐户
        $mmoney['money_freeze']=$datamoney['freeze_money'];
        $mmoney['money_collect']=$datamoney['collect_money'];
        $mmoney['account_money']=$datamoney['account_money'];
        $mmoney['back_money']=$datamoney['back_money'];
        //会员帐户
        $datamoney['info'] = ($type==2)?"收到{$repayment_name}对".borrowidlayout1($this->borrow_id)."号标第{$this->sort_order}逾期罚息":"收到会员对".borrowidlayout1($this->borrow_id)."号标第{$this->sort_order}逾期罚息";
        //如果债权流水号存在
      //  $debt['serialid'] &&  $datamoney['info'] = ($type==2)?"{$repayment_name}对{$debt['serialid']}号债权第{$sort_order}期代还":"收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";
        $datamoney['add_time'] = time();
        $datamoney['add_ip'] = get_client_ip();
        if($type==2){
            $datamoney['target_uid'] = 0;
            $datamoney['target_uname'] = '@网站管理员@';
        }else{
            $datamoney['target_uid'] = 0;
            $datamoney['target_uname'] =$repayment_name;
        }

        //echo M('member_moneylog')->getLastSql();
        $moneynewid = M('member_moneylog')->add($datamoney);
        if($moneynewid){
            $xid = M('member_money')->where("uid={$datamoney['uid']}")->save($mmoney);
        }
    }
    public function sent_expired_money($type,$repayment_name){
        $i=0;
        $k=0;
        $j=0;
        $trade_list="";
        $newbid=borrowidlayout1($this->borrow_id);
        foreach($this->expired_list as $key=>$val){
			$this->save_money_log($type,$val['investor_uid'],$val['money'],$repayment_name);
            if($i < 200){
                if($k === 0){
                    $trade_list[$j] = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$val['investor_uid'].'~UID~SAVING_POT~'.$val['money'].'~~第'.$newbid.'号标投资收益还款';
                    $k++;
                }else{
                    $trade_list[$j] .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$val['investor_uid'].'~UID~SAVING_POT~'.$val['money'].'~~第'.$newbid.'号标投资收益还款';
                }
                $i++;
                if($i === 200){$i = 0;$k = 0;$j++;}
            }
        }
        foreach ($trade_list as $list) {
            sinabatchpay($list,$this->borrow_id);
        }
        $this->update_expired_info();
    }
    static public function get_over_expired__money($id,$sort=1){
        $where['repayment_time']=array("neq",0);
        $where['substitute_time']=array("neq",0);
        $where['_logic'] = 'or';
        $map['borrow_id']=$id;
        $map['sort_order']=$sort;
        $map['_complex'] = $where;
        $map["status"]=array("neq",-1);
        $field="sum(expired_money) as expired_money ";
        $result=M("investor_detail")->field($field)->where($map)->find();
        return $result['expired_money'];
    }
}