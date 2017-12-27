<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/29
 * Time: 10:33
 */
class EventAction extends Action{
    public function index(){
		if(session("u_id")){
				$this->uid = session("u_id");
				$this->assign('UID',$this->uid);
		}		
        if(C("EVENT_INFO.enable")){
            $this->assign("event_enable",C("EVENT_INFO.enable"));
            $this->assign("event_url",C("EVENT_INFO.mobile_url"));
            $this->assign("event_prom",C("EVENT_INFO.mobile_prom"));
        }
        $this->display();
    }
}
