<?php
// 自动投标控制器
class AutoborrowAction extends MCommonAction {
    public function index()
    {
       
        //实名认证的状态
        $is_pass = M('members_status')->where('uid ='.$this->uid)->getField('id_status');
        if($is_pass != 1){
            redirect('/member/verify?id=1#fragment-1');
        }
        
         //单笔最低投标额最大限制
        $min_money = M('system_setting')->where('number =1002')->getField('value');

        $auto_info = M('borrow_auto')->where('uid='.$this->uid)->find();
        $this->assign(array('min_money'=>$min_money,'auto_info'=>$auto_info));
        $this->display();
    }
    
    /*
     * 保存自动投标参数
     */
    public function autoBorrowSet()
    { 
        //校验数据
        $new_params =  $this->verifyBorrowDate();
        $auto_info = M('borrow_auto')->where('uid='.$this->uid)->find();
//        需要判断是否有委托代扣
        $is_open = $this->getUserBuckle();
        $new_params['open_type'] = $is_open=='Y'?1:2;
        if($auto_info){
            //更新数据            
            $new_params['update_time'] = time();
            $result = M('borrow_auto')->where('uid='.$this->uid)->save($new_params);
        }else{
            //插入数据
            $new_params['create_time'] = time();
            $new_params['update_time'] = $new_params['create_time'];
            $new_params['uid'] = $this->uid;
            $result = M('borrow_auto')->add($new_params);
        }
        if($is_open == 'Y'){
            //已开通代扣
            $msg['is_open'] = 1;
            if($result){                
                $msg['message'] ='保存成功！';
                ajaxmsg($msg);
            }else{
                $msg['message'] ='保存失败或未做修改！';
                ajaxmsg($msg,0);
            }
        }else if($is_open == 'N'){
            $msg['is_open'] = 0;
            //没有开通委托代扣  跳到新浪页面
            $msg['url'] = $this->openBuckle();//新浪开通委托代扣地址
            if($result){
                $msg['message'] ='保存成功！';
                ajaxmsg($msg);
            }else{
                $msg['message'] ='保存失败或未做修改！';
                ajaxmsg($msg,0);
            }
        }else{
            //新浪接口调用有问题
            ajaxmsg('可能网络出问题了，请稍后再开启吧！',0);
        }
    }

    /*
     * 校验数据
     */
    private function verifyBorrowDate()
    {
        $params = array();
        if(empty($_POST)){
            ajaxmsg('数据错误！',0);
        }
        //单笔最低投标额最大限制
        $min_money = M('system_setting')->where('number =1002')->getField('value');
        //填写最低限制
        $params['money'] = intval($_POST['lowmoney']);
        if(empty($params['money'])|| $params['money']<100 || $params['money']>$min_money){
            ajaxmsg('单笔最低投标额不能为空，范围100-'.$min_money.'！',0);
        }

        $params['borrow_type'] = intval($_POST['borrow']);
        $params['repayment_type'] = intval($_POST['hk']);
        //天标
        $params['is_borrow_day'] = intval($_POST['db']);
        if($params['is_borrow_day'] == 1){
            $params['day_start'] = intval($_POST['startday']);
            $params['day_end'] = intval($_POST['endday']);
            if($params['day_start'] > $params['day_end']||$params['day_start']>180||$params['day_end']>180||$params['day_start']<1||$params['day_end']<1){
                ajaxmsg('天标范围1-180天,且开始天数不能大于结束天数！',0);
            }
        }
        //月标
        $params['is_borrow_month'] = intval($_POST['mb']);
        if($params['is_borrow_month'] == 1){
            $params['month_start'] = intval($_POST['startmonth']);
            $params['month_end'] = intval($_POST['endmonth']);
            if($params['month_start'] > $params['month_end']||$params['month_start']>12||$params['month_end']>12||$params['month_start']<1||$params['month_end']<1){
                ajaxmsg('月标范围1-12月,且开始月数不能大于结束月数！',0);
            }
        }
        //预期年化率
        $params['rate_start'] = intval($_POST['startnhl']);
        $params['rate_end'] = intval($_POST['endnhl']);
        if($params['rate_start'] > $params['rate_end']||$params['rate_start']>15||$params['rate_end']>15||$params['rate_start']<1||$params['rate_end']<1){
            ajaxmsg('年化收益率范围1%-15%，且开始年化收益率不能大于结束年化收益率！',0);
        }
        //使用投资卷
        $params['ticket_type'] = intval($_POST['usejuan']);
        return $params;
    }
    /*
     * 禁用自动投标
     */
    public function stopAutoBorrow()
    {
        $auto_m = M('borrow_auto');
        $auto_info = $auto_m->where('uid='.$this->uid)->find();
        if($auto_info){
            $updata['open_type'] = 0;
            $result = $auto_m->where('uid='.$this->uid)->save($updata);
            if($result)
            ajaxmsg('禁用成功！');
        }else{
            ajaxmsg('非法操作！',0);
        }
    }
    
    /*
     * 查询用户是否有委托代扣
     */
    private function getUserBuckle()
    {
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $is_open = $sina->queryauthority($this->uid);
        return $is_open;
    }
    /*
     * 新浪开通代扣页面
     */
    private function openBuckle()
    {
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $url = U('Autoborrow/changeState');
        return $sina->handleauthority($this->uid,$url);
    }
    /*
     * 开通委托代扣后更新状态
     */
    public function changeState()
    {
        $is_open = $this->getUserBuckle();
        if($is_open == 'Y'){
            $data['open_type'] =1;
            M('borrow_auto')->where('uid='.$this->uid)->save($data);
        }
        $this->redirect('Autoborrow/index');
    }
}
?>

