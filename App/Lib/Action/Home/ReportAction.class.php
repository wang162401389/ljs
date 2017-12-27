<?php

/**
 * 各种报告
 * Class IndexAction
 */
class ReportAction extends HCommonAction {

	//2016年度报告
	public function year2016(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2016年年度运营报告");
            $this->assign("simple_header_info",$simple_header_info);
			$this->display("h5year2016");
		}else{
			$this->display();
		}
	}

	//2016.12月度报告
	public function month201612(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2016年12月运营报告");
            $this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201612");
		}else{
			$this->display();
		}
	}

	//2017.01月度报告
	public function month201701(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年1月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201701");
		}else{
			$this->display();
		}
	}

	//2017.02月度报告
	public function month201702(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年2月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201702");
		}else{
			$this->display();
		}
	}

	//2017.03月度报告
	public function month201703(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年3月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201703");
		}else{
			$this->display();
		}
	}

	//2017.04月度报告
	public function month201704(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年4月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201704");
		}else{
			$this->display();
		}
	}

	//2017.05月度报告
	public function month201705(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年5月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201705");
		}else{
			$this->display();
		}
	}

	//2017.06月度报告
	public function month201706(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年6月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201706");
		}else{
			$this->display();
		}
	}
	


	//2017.07月度报告
	public function month201707(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年7月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201707");
		}else{
			$this->display();
		}
	}

	//2017.08月度报告
	public function month201708(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年8月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201708");
		}else{
			$this->display();
		}
	}

	//2017.09月度报告
	public function month201709(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年9月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201709");
		}else{
			$this->display();
		}
	}

	//2017.10月度报告
	public function month201710(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年10月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201710");
		}else{
			$this->display();
		}
	}
	
	//2017.11月度报告
	public function month201711(){
		if($this->is_mobile()) {
			$simple_header_info=array("url"=>"/M/index","title"=>"2017年11月运营报告");
			$this->assign("simple_header_info",$simple_header_info);
			$this->display("h5month201711");
		}else{
			$this->display();
		}
	}

	private function is_mobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $is_mobile;
    }
}