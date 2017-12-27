<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/9
 * Time: 10:21
 */
class Members_companyModel extends  ACommonModel{

    public function getCompanyList(){
        $where['s.company_status']=3;
        $result=M("members_company c")->where($where)->join("lzh_members_status as s on s.uid=c.uid")->select();
        return $result;
    }
    public function  set_danbao($uid,$money){
        $where['uid']=$uid;
        $date['is_danbao']=1;
        $date['max_money']=$money;
        $result=$this->where($where)->save($date);
        return $result;
    }
    public function  getDanBaoList(){
        $field="c.uid,c.company_name";
        $where['s.company_status']=3;
        $where['c.is_danbao']=1;
        $result=M("members_company c")->where($where)->join("lzh_members_status as s on s.uid=c.uid")->field($field)->select();
        return $result;
    }
    public function get_danbao_name($id){
        $field="company_name";
        $where['uid']=$id;
        $info=$this->field($field)->where($where)->find();
        return $info["company_name"];
    }
    public function  get_left_credit_money($uid){
        $field="sum(b.borrow_money) as total";
        $where["b.danbao"]=$uid;
        $where["b.borrow_status"]=array("in",array(2,4,6));
        $result=M("borrow_info b")->field($field)->where($where)->group("b.danbao")->find();

        $where1["uid"]=$uid;
        $max_money=$this->field("max_money")->where($where1)->find();

        $left=getFloatValue(($max_money["max_money"]-$result['total']),2);

        return $left;
    }
    public  function get_danbao_info($where=array()){
        $where['mc.is_danbao']=1;
       // $where['bi.borrow_status']=array("egt",6);
        $field="mc.uid,mc.company_name,mc.legal_person,mc.telephone,mc.address,sum(bi.vouch_money) as vouch_money,mc.max_money";
        $info1=M("members_company mc")->join("lzh_borrow_info as bi on bi.danbao=mc.uid")->field($field)->where($where)->group("mc.uid")->select();
        //获取
        $where['bi.borrow_status']=array("in",array(2,4,6));//投资，复审，还款状态
        $field1="mc.uid,sum(bi.borrow_money) as borrow_money";
        $info2=M("members_company mc")->join("lzh_borrow_info as bi on bi.danbao=mc.uid")->field($field1)->where($where)->group("mc.uid")->select();
        $result=array();
        foreach($info1 as $key=>$val){
            $result[$val['uid']]=$val;
        }
        foreach($info2 as $key=>$val){
            $result[$val['uid']]['borrow_money']=$val['borrow_money'];
        }
		$result1=array();
		foreach($result as $key=>$val){
			$result1[]=$val;
		}
        return $result1;
    }
    public  function  get_danbao_vouch_money($uid=false){
        $where['danbao']=$uid;
        $field="sum(vouch_money) as total";
        $where['borrow_status']=array("egt",6);//标的为还款的时候，已经收取了担保费用
        $result=M("borrow_info")->field($field)->where($where)->group("danbao")->find();
        return $result['total'];
    }

}

?>