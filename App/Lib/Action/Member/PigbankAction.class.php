<?php
/**
 * 存钱罐记录
 * Class pigbankAction
 */
class PigbankAction extends MCommonAction {
	public function index(){
		$this->display();
    }


    public function pigbanklog(){
        	$map['uid']=$this->uid;
        	$regtime = M('members')->field('reg_time')->where('id='.$this->uid)->find();
        	$start = C('EARNINGS.starting');
        	$list = M('member_piggybank')->field('earnings_yesterday,time,total_revenue')->where($map)->order('time DESC')->select();
        	foreach($list as $k => $v){
        		if($v['time']>strtotime(date('Y-m-d',time())) && $v['time']<strtotime(date('Y-m-d',strtotime('+1 day')))){
        			$zrshouyi = $v['earnings_yesterday'];//
                }
				/**
                if($v['time']>strtotime($start)){
                	$zshouyi += $v['earnings_yesterday'];
                }
				 * **/
        		$list[$k]['time']=date("Y-m-d",$v['time']-24*3600);
        	}
        	$cqglist = piggybankearnings();
            $cqglist1 = explode('|', $cqglist['yield_list']);
            foreach($cqglist1 as $k => $v){
                $cqglist2[$k] = explode('^',$v);
            }
            $this->assign('thousandsincome',$cqglist2[0][2]);
            $this->assign('yields',$cqglist2[0][1]);	
        	$this->assign('list',$list);
        	$this->assign('start',$start);
        	$this->assign('regtime',$regtime['reg_time']);
        	$this->assign('zrshouyi',$zrshouyi);
        	$this->assign('zonshouyi',$list[0]["total_revenue"]?$list[0]["total_revenue"]:0);
        	$data['html'] = $this->fetch();
			exit(json_encode($data));
        }
}
?>