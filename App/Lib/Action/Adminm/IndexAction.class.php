<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends AMCommonAction {

    var $justlogin = true;
    var $op_waitverify=1020;
    var $op_waitverify2=1030;
    var $op_waitverify3=1040;

    public function  index(){
        if(!$this->admin_id){
            header("Location:/adminm/index/login");
        }else{
            header("Location:/adminm/index/logout");

        }
    }
    public function verify(){
        import("ORG.Util.Image");
        Image::buildImageVerify();
    }

    public function login()
    {
        require C("APP_ROOT")."Common/menu.inc.php";
        if( session("admin") > 0){
            $this->redirect('index');
            exit;
        }
        if($_POST){
            $op_code=intval($_POST['op_code']);
            if(($op_code!=$this->op_waitverify)&&($op_code!=$this->op_waitverify2)&&($op_code!=$this->op_waitverify3)){
                echo '<script>alert("操作码错误");window.location.href=window.location.href</script>';exit;
            }

            if($_SESSION['verify'] != md5( strtolower($_POST['code']))){
                echo '<script>alert("验证码错误");window.location.href=window.location.href</script>';exit;
            }

            $data['user_name'] = text($_POST['admin_name']);
            $data['user_pass'] = md5(strtolower($_POST['admin_pass']));
            $data['is_ban'] = array('neq','1');
            $data['user_word'] = text($_POST['user_word']);
            $admin = M('ausers')->field('id,user_name,u_group_id,real_name,is_kf,area_id,user_word,last_log_time,last_log_ip')->where($data)->find();
            if(is_array($admin) && count($admin)>0 ){
                foreach($admin as $key=>$v){
                    session("admin_{$key}",$v);
                }
                if(session("admin_area_id")==0) session("admin_area_id","-1");
                session('admin',$admin['id']);
                session('adminname',$admin['user_name']);
                $info['last_log_time'] = time();
                $info['last_log_ip'] = get_client_ip();
                M("ausers")->where('id='.$admin['id'])->save($info);

                alogs("login",'','1',"管理员登陆成功");//管理员操作日志之登陆日志
                if($op_code===$this->op_waitverify2){
                    header("Location:/adminm/mborrow/waitverify2");//进入复审流程
                }
                else if ($op_code==$this->op_waitverify){
                        echo '<script>alert("手机端初审关闭");window.location.href=window.location.href</script>';exit;
                    // header("Location:/adminm/mborrow/waitverify");//进入复审流程
                }else if ($op_code==$this->op_waitverify3){
                    header("Location:/adminm/mborrow/waitverify3");//进入申请还款流程
                }
            }else{
               echo '<script>alert("账号或密码不正确");window.location.href=window.location.href</script>';exit;
            }
        }else{
            //$this->error("非法请求");
            $this->display();
        }

    }


    public function logout()
    {
        alogs("logout",'','1',"管理员退出");
        //require C("APP_ROOT")."Common/menu.inc.php";
        session(null);
        header("Location:/adminm/index/login");
    }

}