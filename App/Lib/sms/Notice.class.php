<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/23
 * Time: 17:31
 */

class Notice {
    private function get_borrower_tel($id){
        $where['b.id']=$id;
        $result=M("borrow_info b")->join("lzh_members m on m.id=b.borrow_uid")->where($where)->field("user_phone")->select();
        return $result[0]['user_phone'];
    }
    private function  get_input_person_tel($id){
        $where['i.borrow_id']=$id;
        $result=M("investor_detail i")->join("lzh_members m on m.id=i.investor_uid ")->where($where)->field("user_phone")->select();

        foreach($result as $key=>$v){
            $tel .= (empty($tel))?$v['user_phone']:','.$v["user_phone"];
        }
        return $tel;
    }
    public  function  replay($id,$type=0){
        $newbid=borrowidlayout1($id);
        $info="尊敬的链金所用户您好！您投资的第".$newbid."号标，现借款人已申请提前还款，我们正在审核中，如有疑问请与客服中心联系400-6626-985。";
		if($type==0)
			$tel=$this->get_input_person_tel($id);
        else
            $tel=C("NOTICE_TEL.fengkong");
        sendsms($tel,$info);
    }

    public function agreen_reply($id){
        $newbid=borrowidlayout1($id);
        $info=" 尊敬的链金所用户您好！您申请借款的第".$newbid."号标，现经链金所审核，同意您提前还款，您可以登录平台账户查询详情。";

        $tel=$this->get_borrower_tel($id);
       sendsms($tel,$info);
    }
    public function disagreen_reply($id){
        $newbid=borrowidlayout1($id);
        $info="尊敬的链金所用户您好！您申请借款的第".$newbid."号标，现经链金所审核，不同意借款人提前还款，详情请与跟单业务员联系。";
        $tel=$this->get_borrower_tel($id);
        sendsms($tel,$info);
    }
    public function verify2($tel,$token){
        $info="您的动态审核口令为".$token;
        $this->sent($tel,$info);
    }
    public function super_replay_code($tel,$token,$borrow_id){
      //  $info="网站将对".$borrow_id."标做代还款处理,动态验证码为".$token;
        $info="您的动态审核口令为".$token;
        $this->sent($tel,$info);
    }
    public function notice_borrower($borrow_id){
        $where['id']=$borrow_id;
        $field='borrow_name,second_verify_time,borrow_money,borrow_uid';
        $info=M("borrow_info")->field($field)->where($where)->select();
        $mem['uid']=$info[0]['borrow_uid'];
        $mem_info = M("member_info")->field("cell_phone,real_name")->where($mem)->select();
        $time=date("Y年m月d日",$info[0]['second_verify_time']);
        $txt="尊敬的借款人{$mem_info[0]['real_name']}，您好！您于{$time}在链金所平台成功融资{$info[0]['borrow_money']}元，现未能按照借款合同约定归还本金和利息。为避免此次逾期行为影响您的信用记录，特速金融集团暂时为您垫付了此笔逾期款项，请您尽快按照补充协议约定归还本息和罚息到指定账户。在此温馨提示：拥抱诚信，远离逾期。";
        echo $mem_info[0]['cell_phone'];
        echo $txt;
        $this->sent($mem_info[0]['cell_phone'],$txt);
    }
    public function notic_supper($money,$borrow_id,$supper_login){
        $add_function=C("ADD_FUNCTION");
        if($supper_login == 1){
            $tel=$add_function['repayment']['tel'];
        }else{
            $tel=$add_function['repayment']['tel1'];
        }
        
        $where['id']=$borrow_id;
        $field='borrow_name,second_verify_time,borrow_money,borrow_uid';
        $info=M("borrow_info")->field($field)->where($where)->select();
        $mem['uid']=$info[0]['borrow_uid'];
        $mem_info = M("member_info")->field("cell_phone,real_name")->where($mem)->select();
        $time=date("Y年m月d日",time());
        $txt="您好，您为借款人{$mem_info[0]['real_name']}与{$time}在链金所平台融资的{$money}元逾期款项垫付还款成功，请及时跟进贷款本息和罚息的催收工作，感谢您的配合。";
        $this->sent($tel,$txt);

    }
    public function remind_replay($tel){
        $info="温馨提醒：尊敬的客户，您在链金所借款，快到期了,请按时还款.";
        Log::write($info);
        $this->sent($tel,$info);

    }
    private function sent($tel,$info){
        sendsms($tel,$info);
    }

}
