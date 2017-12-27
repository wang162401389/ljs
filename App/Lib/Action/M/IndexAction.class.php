<?php
    /**
    * 手机版(wap)默认首页
    * @author  张继立  
    * @time 2014-02-24
    */
    class IndexAction extends HCommonAction
    {
        public function index()
        {
            $searchMap['borrow_status']=array("in",'2,4,6,7,8');
            $searchMap['is_beginnercontract'] = 0;
            $searchMap['test']                = 0;
            
            $parm["type"]=0;//列表页  列表页不限制条数 首页限制条数 1 列表页面   0 首页
            $parm['map'] = $searchMap;
            $parm['pagesize'] = 5;
            $parm['orderby']="b.borrow_status ASC,b.id DESC";
            $list = getBorrowList($parm);
            $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";

            /********新手标********************/
            $searchMap = array();
            $searchMap['b.borrow_status']       = array("in", '2');
            $searchMap['b.is_beginnercontract'] = 1;
            $searchMap['b.test']                = 0;
            $zparm = array();
            $zparm['type'] = 1;
            $zparm['map'] = $searchMap;
            $zparm['limit'] = 1;
            $zparm['orderby']="b.borrow_status ASC,b.id DESC";
            $xsBorrow = getBorrowList($zparm);
            $this->assign("xsBorrow", $xsBorrow);
            $this->assign("xsBorrowcount", count($xsBorrow));
            /************************************/
                
            $parm = array();
            $Map  = ' b.borrow_status = 2 and b.is_show=1 and b.transfer_total > b.transfer_out';
            $parm['map'] = $Map;
            $parm['orderby'] = "b.is_show desc,b.id DESC";
           
            ///////////////企业直投列表结束 /////////////
            $parm['type_id'] = 9;
            $parm['limit'] =4;

            $tmp=getArticleList($parm);
            $this->assign("noticeList",$tmp['list']);

            $this->assign('list', $list);
            $this->assign('Bconfig', $Bconfig);
            $this->assign('tab','index');
            $this->assign("no_footer_seg","1");
            if(C("EVENT_INFO.enable")){
                $mobile_banner=C("EVENT_INFO.mobile_banner");
                if($mobile_banner){
                    $this->assign("mobile_banner",$mobile_banner);
                    $this->assign("mobile_url",C("EVENT_INFO.mobile_url"));
                }
            }
            
            /**************** 轮播图优化************************/
            $id = 27;
            $adlist = M('ad')->field('ad_type,content')->find($id);
            $this->assign("adlist", unserialize($adlist['content']));
            
             /********待上标********************/
            $searchMap['borrow_status']=array("eq",8);
            $searchMap['is_beginnercontract'] = 0;
            $searchMap['test']                = 0;
            $parm["type"]=1;//列表页  列表页不限制条数 首页限制条数 1 列表页面   0 首页
            $parm['map'] = $searchMap;
            $parm['limit'] = 3;
            $parm['orderby']="b.add_time ASC,b.borrow_money DESC";
            $listBorrow = getBorrowList($parm);
            $this->assign("waitBorrow",$listBorrow);
            $this->assign("waitBorrowcount",count($listBorrow["list"]));
            /************************************/
            // $tiyanbiao=M("borrow_info_experience")->find();
            // $this->assign("tiyanbiao",$tiyanbiao);
            $this->display();
        }

        function arjx(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"按日计息");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        function aqbz(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"安全保障");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        function xszd(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"新手指导");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        function crjh(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"产融结合");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
		function fnj_activity(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"3·8 富你节");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        public function introduce5(){
            $simple_header_info=array("url"=>"/M/index/index.html","title"=>"理财产品");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
    }
?>