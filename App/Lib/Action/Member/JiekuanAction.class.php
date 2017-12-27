<?php
// 本类由系统自动生成，仅供测试用途
class JiekuanAction extends MCommonAction {
    public function index(){
        $list = M('jiekuan')->where(array('uid' => $this->uid, 'status' => array('in', array('1', '2', '4'))))->order('addtime desc')->select();
        $this->assign('list', $list);
		$this->display();
    }
}