<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/4
 * Time: 15:08
 */
class friend_invest {
    var $enable;
    function __construct(){
        $this->enable = C("Frind_INFO.enable");
    }

    function is_show(){
        return $this->enable;
    }
    public function  get_friend_invest($id,$map){
       $info=array();
        if($this->is_show()){
            $where['m.id']=$id;
            $where['m.recommend_id']=$id;
            $where['_logic'] = 'OR';
            $map['_complex'] = $where;
            $field="m.user_name,m.reg_time,i.investor_capital,b.borrow_name,b.borrow_duration,i.add_time,i.borrow_id,b.repayment_type,m.id";
            $info=M("borrow_investor i")->field($field)->join("lzh_members as m on m.id=i.investor_uid ")->join("lzh_borrow_info as b on b.id=i.borrow_id")->where($map)->order("i.add_time desc")->select();
        }
        return $info;
    }

    public function get_friend_list($map){
        $info = [];
        if(!$this->is_show()){
            return $info;
        }
        $map["m.recommend_id"] = ["neq", 0];
        $field = "mm.id,mm.user_name,m.id as investor_uid,m.user_name as investor_user_name,sum(i.investor_capital) as friend_investor,mi.real_name,mm.user_phone";
        $result = M("members m")->field($field)
                              ->join("lzh_members as mm on mm.id = m.recommend_id")
                              ->join("lzh_borrow_investor as i on i.investor_uid = m.id")
                              ->join("lzh_member_info as mi on mi.uid = m.recommend_id")
                              ->where($map)
                              ->group("m.id")
                              ->select();

        if (!empty($result)) {
            $field = "m.id,sum(i.investor_capital) as mine_capital";
            $where['m.id'] = ["in", array_unique(array_column($result, 'id'))];
            $own = M("members m")->field($field)->where($where)->join("lzh_borrow_investor as i on i.investor_uid=m.id")->group("m.id")->select();
            $info1 = [];
            foreach($own as $key => $val){
                $info1[$val['id']] = $val['mine_capital'];
            }
    
            foreach($result as $key => $val){
                if(!isset($info[$val['id']])){
                    $info[$val['id']]['staff_type'] = partake_filter($val['id']) ? 1 : 2;
                    $info[$val['id']]['real_name'] = $val['real_name'];
                    $info[$val['id']]['user_phone'] = $val['user_phone'];
                    $info[$val['id']]['user_name'] = $val['user_name'];
                    $info[$val['id']]["register_num"] = 1;
                    $info[$val['id']]['mine_capital'] = $info1[$val['id']];
                    $info[$val['id']]["friend_investor"] = $val['friend_investor'];
                    $info[$val['id']]["investor_num"] = $info[$val['id']]["real_num"] = 0;
                    if($val['real_name'] != ""){
                        $info[$val['id']]["real_num"]++;
                    }
                    if($val['friend_investor'] != 0){
                        $info[$val['id']]["investor_num"]++;
                    }
                }else{
                    if($val['real_name'] != ""){
                        $info[$val['id']]["real_num"]++;
                    }
                    if($val['friend_investor'] != 0){
                        $info[$val['id']]["investor_num"]++;
                    }
                    $info[$val['id']]["register_num"]++;
                    $info[$val['id']]["friend_investor"] = getFloatValue(($val['friend_investor'] + $info[$val['id']]["friend_investor"]), 2);
                }
            }
        }
        return $info;
    }
}