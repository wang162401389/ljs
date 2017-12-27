<?php
/**
 *  红木专题页
 */
class HongmuAction extends HCommonAction
{
    public function _initialize(){
        parent::_initialize();
        $title="网络借贷,网络理财,P2P网贷有哪些安全保障措施？信任首选链金所";
        $keyword="网络借贷有哪些安全保障措施,网络理财有哪些安全保障措施,P2P网贷有哪些安全保障措施";
        $description="链金所结合金融专业的风控手段,为用户提供全面的网络贷款,网络借贷,网络理财,P2P理财,P2P网贷安全保障服务.更好的保障投资人的资金安全.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
    }

    public function index()
    {
        // 渲染页面
        if (is_mobile()) {
            // H5 页面,上一页跳转地址
            $simple_header_info=array("url"=>"/","title"=>"一分钟了解红木家具模式");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5index");
        } else {
            $this->display("index");
        }
    }
    
}