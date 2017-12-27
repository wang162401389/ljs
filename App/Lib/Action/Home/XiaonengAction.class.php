<?php
/*
 * 分期购发标参数接收接口
 * @return json
 */

class XiaonengAction extends HCommonAction {
	public function getborrowinfo(){
			$bid = intval($_REQUEST['id']);
			$list = M('borrow_info')->where('id = '.$bid)->find();
			$data['item']['id'] = $list['id'];	//标号
			$data['item']['name'] = $list['borrow_name'];  //名称
			$data['item']['imageurl'] = 'http://'.$_SERVER['HTTP_HOST'].'/UF/Uploads/Article/20160307094758.png';  //图片路径
			$data['item']['url'] = 'http://'.$_SERVER['HTTP_HOST'].getInvestUrl($list['id']);	 //商品详情页地址
			if(in_array($list['product_type'],array('1','2','3'))){
				$data['item']['category'] = '质金链';   //标的类型
			}else if($list['product_type'] == 4){
				$data['item']['category'] = '融金链'; 
			}else if(in_array($list['product_type'],array('5','6'))){
				$data['item']['category'] = '信金链'; 
			}else if($list['product_type'] == 7){
				$data['item']['category'] = '优金链'; 
			}
			$data['item']['custom1'] = ['借款金额' , "￥".$list['borrow_money'] ];
			$data['item']['custom2'] = ['年化收益' , $list['borrow_interest_rate']."%" ];
			if(empty($list['borrow_duration_txt'])){
				if($list['repayment_type']==1){
					$bduration = $list['borrow_duration']."天";
				}else{
					$bduration = $list['borrow_duration']."个月";
				}
			}else{
				$bduration = $list['borrow_duration_txt'];
			}
			$data['item']['custom3'] = ['借款期限' , $bduration ];
			$data['item']['custom4'] = ['借款用途' , $this->gloconf['BORROW_USE'][$list['borrow_use']] ];
			$data['item']['custom5'] = ['发布时间' , date('Y-m-d H:i',$list['add_time']) ];
			echo json_encode($data);
	}
}