<?php
    class ProjectAction extends Action{
        public  function info(){
            $simple_header_info=array("url"=>"/M/user/index.html","title"=>"活动分享页面");
            $this->assign("simple_header_info",$simple_header_info);

            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if (strpos($user_agent, 'MicroMessenger') === false) {
                $this->assign("weixin",0);
            } else {
                $this->assign("weixin",1);
                import("@.weixin_js.jssdk");
                $jssdk = new JSSDK("wx4523f6c280b89aa9", "f7a7a86658f43675901bcde21b7322b2");
                $signPackage = $jssdk->GetSignPackage();
                $this->assign("signPackage",$signPackage);
                $img_url="http://".$_SERVER['SERVER_NAME']."/Style/Phone/img/project/a3.png";
                $this->assign("img_url",$img_url);
            }
            if(session('u_id')) {
				if(!partake_filter(session('u_id'))){
                    redirect('/M/user/index');
                }
                $this->assign("mine",1);
                $this->assign("uid",session('u_id'));
            }else{
                $this->assign("mine",0);
                if($_GET['uid']!=''){
                    $uid=intval($_GET['uid']);
                    $url="/M/pub/regist.html?type=weixin&uid=".$uid;
                    $this->assign("uid",$uid);
                }
                else
                    $url="/M/pub/regist.htm";
                $this->assign("register",$url);
            }
            $this->display();
        }
        public  function  create_qr(){
            $uid = intval($_GET['uid']);
            $url="http://".$_SERVER['SERVER_NAME']."/M/pub/regist.html?type=weixin&uid=".$uid;
            import("@.phpqrcode.phpqrcode");
            ob_clean();
            QRcode::png($url);
        }
    }
