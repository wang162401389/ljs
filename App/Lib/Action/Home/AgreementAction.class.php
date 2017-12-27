<?php
/**
* 协议
*/
class AgreementAction extends HCommonAction
{
    public function index()
    {
        $rurl = explode("?",$_SERVER['REQUEST_URI']);
        $xurl_tmp = explode("/",str_replace(array("index.html",".html"),array('',''),$_SERVER['REQUEST_URI']));//获得组合的type_nid
        $where["type_nid"] = $xurl_tmp[2];
        $vo = M("article_category")->where($where)->find();
        $this->assign("vo",$vo);
        $this->assign("mycid",$vo["type_nid"]);
        $this->display();
    }
}