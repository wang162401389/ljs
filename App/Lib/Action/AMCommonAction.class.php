<?php
// 全局设置
class AMCommonAction extends Action
{
    var $admin_id=0;
    //验证身份
    function _initialize(){
        $this->pre = C('DB_PREFIX');

        $ip=$_SERVER['REMOTE_ADDR'];
        //检测ip是否合法
        //$this->checkip($ip);
      //  check_other_login();
        $query_string = explode("/",$_SERVER['REQUEST_URI']);

        !isset($this->justlogin)?$this->justlogin=false:$this->justlogin=$this->justlogin;
        if(session('admin')){//dump(session('adminname'));exit;
            $this->admin_id = session("admin");
            $this->assign('adminname',session('adminname'));
        }elseif(( strtolower(ACTION_NAME) != 'login')&&( strtolower(ACTION_NAME) != 'verify')){
            header("Location:/adminm/index/login");
            exit;
        }


        if( !get_user_acl(session('admin')) && !$this->justlogin){
            echo "<script>alert('对不起，权限不足,请重新登录');window.location.href='/adminm/index/logout'</script>";
            exit;
        }

        if (method_exists($this, '_MyInit')) {
            $this->_MyInit();
        }

        $datag = get_global_setting();
        $this->glo = $datag;//供PHP里面使用
        $this->assign("glo",$datag);

        $bconf = get_bconf_setting();
        $this->gloconf = $bconf;//供PHP里面使用
        $this->assign("gloconf",$bconf);
    }


}
?>