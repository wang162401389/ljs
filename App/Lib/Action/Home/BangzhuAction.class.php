<?php
// 本类由系统自动生成，仅供测试用途
class BangzhuAction extends HCommonAction {
    public function index(){
		$this->display();
    }

    public function phone(){
    	$title="链金所手机客户端_iphone/Android版App下载_链金所";
        $keyword="链金所手机客户端,链金所App,链金所App下载";
        $description="链金所手机客户端包括_iphone/Android版App免费下载";
        $this->assign('title',$title);
		$this->assign('keyword',$keyword);
		$this->assign('description',$description);
        $this->display();
    }

	
	
}