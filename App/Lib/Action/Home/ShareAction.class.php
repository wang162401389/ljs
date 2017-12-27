<?php 
class ShareAction extends HCommonAction {
	public function share(){
		$url = $_REQUEST["bid"];
		$this->assign("url",$url);
		$this->display();
	}

}
?>