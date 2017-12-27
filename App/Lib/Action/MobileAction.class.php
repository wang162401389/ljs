<?php
    /**
    * 手机版用户中心公共类
    */
    class MobileAction extends Action
    {
        public $uid;
        public $uname;
        public $glo;
        
        function _initialize(){
            if(session('u_id')){ 
                $this->uid = session('u_id');
                $this->uname = session('u_user_name');  
                $this->assign('uname', $this->uname); 
                $datag = get_global_setting();
                $this->glo = $datag;//供PHP里面使用
                $this->assign("glo",$datag);//公共参数
                ccfaxapibalace($this->uid);
            }else{   
                $this->redirect('M/Pub/login');
            }

            $hetong = M('hetong')->field('name,dizhi,tel')->find();
			$this->assign("web",$hetong);
            $this->checkconfirm();
            // import("@.conf.single_login");
            // $single= single_login::getInstance();
            // $single->check_login($this->uid);
        }

        //检查满标确认
        protected function checkconfirm(){
            if($this->uid){
                $vo = M("borrow_confirm")->where("uid={$this->uid}")->select();
                if($vo != null){
                    foreach ($vo as $i) {
                        if($i["fee_status"] == 0 || ($i["danbao_status"] == 0 && $i["danbao_id"] != 0)){
                            redirect(__APP__."/confirm/index/m_mbtx"); //标满列表页
                            exit;
                        }
                    }
                }

                /**
                 * 债权费用
                 */
                $zhaiquan_fee=M("debt_borrow_info")->where("borrow_uid={$this->uid} AND pay_fee = 0 AND borrow_status = in (6,7)")->count();
                 if($zhaiquan_fee > 0){//如果存在
                     redirect(__APP__."/confirm/index/zhaiquanfee"); 
                     exit;
                  }
            }
        }
    }
?>
