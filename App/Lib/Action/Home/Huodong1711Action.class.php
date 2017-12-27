<?php

/**
 * 2017 11月活动 
 * 
 */
class Huodong1711Action extends HCommonAction
{

    /**
     * 9 月活动首页
     * @return [type] [description]
     */
    public function index()
    {
        $model = new MembersStatusModel();
        $userstatus = $model->getUserStatus();
        $firstlist = [];
        $totallist = [];
        if($userstatus  == 0)
        {
            $this->assign('uid',0);
        }else{
            $this->assign('uid',session('u_id'));
            // 获取推荐人投资信息列表
            $model = new Huodong201711CountModel();
            $totallist = $model->get201711ReleaseTotalList(session('u_id'));
            $firstlist = $model->get201711ReleaseFirstList(session('u_id'));
            $this->assign('totallist', $totallist);
            $this->assign('firstlist', $firstlist);
            $this->assign('empty','<p class="inviteempty">暂无数据！</p>');
        }

        $this->assign('userstatus',$userstatus);

        // 渲染页面
        if (is_mobile()) {
            // H5 页面,上一页跳转地址
            $simple_header_info=array("url"=>"/","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5index");
        } else {
            $this->display("index");
        }
    }

    public function getTimerange()
    {
        S('global_setting', null);
        $glo = get_global_setting();
        $start_time = date("Y-m-d H:i:s", $glo['start_201711']);
        $end_time = date("Y-m-d H:i:s", $glo['end_201711']);
        exit('start = '.$start_time.'   end ='.$end_time);
    }
}
