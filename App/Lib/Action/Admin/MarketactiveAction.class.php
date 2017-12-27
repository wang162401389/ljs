<?php 
/*
*@active 营销活动
*@author hejianping@ccfax.cn
*@datetime 2016-08-24 11:13
*
*/
class MarketactiveAction extends ACommonAction{
	public function index(){
		//
	}
	//赠送老用户投资券
	public function givecoupons(){
		if($_POST){
			$regtime = strtotime($_POST['regtime']);
			$num = $_POST['num'];
			$active = $_POST['activetime'];
			$active1 = $_POST['activetime1'];
			$minfo = M('members')->field('user_phone')->where('reg_time <='.$regtime)->select();
			if(time()<strtotime($active)){
				$this->error('活动未开始');
			}
			if(time()>strtotime($active1)){
				$this->error('活动已结束');
			}
			foreach ($minfo as $k => $v) {
				if(empty($v['user_phone'])){
					continue;
				}
				$par['user_phone'] = $v['user_phone'];
				$par['addtime'] = array('between',array($active,$active1));
				$limittime = M('coupons')->where($par)->find();
				if($limittime){
					$this->error('投资券已送出');exit;
				}
				for($i=1;$i<=$num[0];$i++){
					$map[]=array('user_phone'=>$v['user_phone'],'money'=>'10','endtime'=>strtotime(date("Y-m-d 23:59:59",strtotime("+3 months -1 days"))),'status'=>'0','serial_number'=>date('YmdHis').rand(100000,999999),'type'=>1,'name'=>'老用户赠送投资券','addtime'=>date('Y-m-d H:i:s'),'isexperience'=>1,'use_money'=>'1000');
				}
				for($i=1;$i<=$num[1];$i++){
					$map[]=array('user_phone'=>$v['user_phone'],'money'=>'20','endtime'=>strtotime(date("Y-m-d 23:59:59",strtotime("+3 months -1 days"))),'status'=>'0','serial_number'=>date('YmdHis').rand(100000,999999),'type'=>1,'name'=>'老用户赠送投资券','addtime'=>date('Y-m-d H:i:s'),'isexperience'=>1,'use_money'=>'2000');
				}
				for($i=1;$i<=$num[2];$i++){
					$map[]=array('user_phone'=>$v['user_phone'],'money'=>'50','endtime'=>strtotime(date("Y-m-d 23:59:59",strtotime("+3 months -1 days"))),'status'=>'0','serial_number'=>date('YmdHis').rand(100000,999999),'type'=>1,'name'=>'老用户赠送投资券','addtime'=>date('Y-m-d H:i:s'),'isexperience'=>1,'use_money'=>'5000');
				}
				for($i=1;$i<=$num[3];$i++){
					$map[]=array('user_phone'=>$v['user_phone'],'money'=>'100','endtime'=>strtotime(date("Y-m-d 23:59:59",strtotime("+3 months -1 days"))),'status'=>'0','serial_number'=>date('YmdHis').rand(100000,999999),'type'=>1,'name'=>'老用户赠送投资券','addtime'=>date('Y-m-d H:i:s'),'isexperience'=>1,'use_money'=>'10000');
				}
				$uphone[] = $v['user_phone'];
			}
			$res = M('coupons')->addAll($map);
			//将手机号拆分并组合成每组500个的几组字符串
			$groupnum = ceil(count($uphone)/500);
			$start = 0;
			for($i=0;$i<$groupnum;$i++){
				$list[] = array_slice($uphone, $start,500);
				$start+=500;
			}
			if($res){
				$content = "尊敬的链金所用户您好！200元投资券已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
				//短信群发
				if($_SERVER['HTTP_HOST']=="ccfax.cn"){
					foreach($list as $k => $v){
						$send['user_phone'] = implode(',',$v);
						sendsms($send["user_phone"],$content);
					}	
				}else{
					$send['user_phone'] = '13714211057,15113248523';
					sendsms($send["user_phone"],$content);
				}
				$this->success('提交成功');
			}else{
				$this->error('提交失败');
			}
		}else{
			$this->display();
		}
	}

	//转盘赠送礼品
	public function giftgive(){
		$list = M('apr_prize')->select();
		foreach($list as $k => $v){
			if($v['type']==0){
				$list[$k]['type'] = '投资券';
			}else if($v['type']==1){
				$list[$k]['type'] = '链金豆';
			}else if($v['type']==2){
				$list[$k]['type'] = '体验金';
			}else if($v['type']==3){
				$list[$k]['type'] = '现金';
			}else if($v['type']==4){
				$list[$k]['type'] = '实物';
			}
			if($v['status'] == 1){
				$list[$k]['status'] = '是';
			}else{
				$list[$k]['status'] = '否';
			}
		}
		$this->assign('list',$list);
		$this->display();
	}

	public function editgift(){
		$id = intval($_REQUEST['id']);
		$list = M('apr_prize')->where('id = '.$id)->find();
		$typelist = array(
			'0' => '投资券',
			'1' => '链金豆',
			'2' => '体验金',
			'3' => '现金',
			'4' => '实物',
			);
		$this->assign('typelist',$typelist);
		$this->assign('list',$list);
		$this->display();
	}
	public function doeditgift(){
		$id = intval($_POST['id']);
		$map['total'] = $_POST['total'];
		$map['left'] = $_POST['left'];
		$map['mark'] = $_POST['mark'];
		//$map['status'] = $_POST['status'];
		$res = M('apr_prize')->where('id = '.$id)->save($map);
		if($res){
			$this->success('修改成功');
		}else{
			$this->error('修改失败或未做修改');
		}
	}
}
