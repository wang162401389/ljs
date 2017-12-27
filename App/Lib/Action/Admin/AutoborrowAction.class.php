<?php
// 自动投标设置
class AutoborrowAction extends ACommonAction
{
    public function index()
    {
        if(is_ajax()){
            //更新自动投标设置
            $res = M('system_setting')->where('number ='.$_POST['num'])->save(array('value'=>$_POST['val']));
            if($res){
               $this->ajaxReturn(array('code'=>1,'msg'=>'设置成功！'));
            }else{
               $this->ajaxReturn(array('code'=>1,'msg'=>'设置失败或未做修改！'));
            }            
        }else{
            //自动投标单笔投标限额
            $auto_borrow_limit=M('system_setting')->where('number =1001')->find();
            //用户设置投标限额上限
            $user_borrow_max=M('system_setting')->where('number =1002')->find();
            //单笔自动投标金额占标是否开启
            $auto_borrow_isopen=M('system_setting')->where('number =1003')->find();
            //单笔自动投标金额占标
            $auto_borrow_value=M('system_setting')->where('number =1004')->find();
            $this->assign(array('auto_borrow_limit'=>$auto_borrow_limit,'user_borrow_max'=>$user_borrow_max,'auto_borrow_isopen'=>$auto_borrow_isopen,'auto_borrow_value'=>$auto_borrow_value));
            $leveconfig = FS("Webconfig/leveconfig");
            $this->assign('leve',$leveconfig);
            $this->display();
        }
    }
}
?>
