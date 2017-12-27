<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

// 会员模型
class MembersStatusModel extends ACommonModel {

	public function getUserStatus()
	{
		$status = 0;

        if(!isset($_SESSION['u_id'])||intval(session('u_id'))===false)
        {
            return $status;
        }

        $info = $this->find(session('u_id'));

        if(null == $info)
        {
            //
            $status = 1024;
        }

        // 开通新浪支付
        if($info['is_pay_passwd'] == 1)
        {
            $status = $status|1;
        }

        //实名认证
        if($info['id_status'] == 1)
        {
            $status = $status|2;
        }

        //绑卡
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $bindcard = $sina->querycard(session('u_id'));
        if(!empty($bindcard)){
            $status = $status|4;
        }

        //已注册,但未投资
        $list = M('borrow_investor')->where(['investor_uid'=>session('u_id')])->select();
        if(is_array($list)&&sizeof($list)>0)
        {
            $status = $status|8;
        }

        return $status;
	}

    public function getRealnameIdList()
    {
        $list = $this->where(['id_status'=>1])->select();
        return array_column($list, 'uid');
    }

    public function getPaypwdIdList()
    {
        $list = $this->where(['is_pay_passwd'=>1])->select();
        return array_column($list, 'uid');
    }
}
?>