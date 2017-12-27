<?php

/**
 * Class HelpAction
 * 文章管理
 */
class HelpAction extends HCommonAction {

    public function index(){
		$is_subsite=false;

		$rurl = explode("?",$_SERVER['REQUEST_URI']);
		$xurl_tmp = explode("/",str_replace(array("index.html",".html"),array('',''),$rurl[0]));//获得组合的type_nid
		$zu = implode("-",array_filter($xurl_tmp));//组合
		$categroylist=M("article_category")->where(array("type_nid"=>$zu))->find();
		$typeinfo['typeset'] = $categroylist["type_set"];
		if($typeinfo['typeset']==1){//列表
			$templet = "list_index";
			$this->assign("typename",$xurl_tmp[1]);
		}else{//单页
			$templet = "index_index";
			$this->assign("typename",$xurl_tmp[2]);
		}
		$typeinfo['templet'] = $templet;
		$typeinfo['typeid'] = $categroylist["id"];
		if($typeinfo['typeset']==0){
			$typeinfo= get_type_infos();
			$typeid = $typeinfo['typeid'];
			$listparm['type_id']=$typeid;
		}
		if(intval($typeinfo['typeid'])<1){
			$typeinfo = get_area_type_infos($this->siteInfo['id']);
			$is_subsite=true;
		}

		$typeid = $typeinfo['typeid'];
		$typeset = $typeinfo['typeset'];
		$listparm['type_id']=$typeid;
		$listparm['limit']=20;
		if($is_subsite===false){
			$leftlist = getTypeListActa($listparm);
		}
		else{
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);
		if($typeid==27){
			$title="网络理财平台账户管理,网络理财平台账户常见问题_链金所";
	        $keyword="网络理财平台账户管理,网络理财平台账户常见问题,链金所账户管理,链金所账户常见问题";
	        $description="链金所账户管理细节说明,操作细节指导,平台账户常见问题,网络理财平台首选链金所.";
		}else if($typeid==36){
			$title="网络理财平台_链金所公司简介";
	        $keyword="链金所公司简介";
	        $description="网络理财平台_链金所依托高效的互联网信息发布渠道,为网贷投资人搭建一个稳健,高效的网络理财中心和平台.致力于为有网络理财需求的投资者提供服务.";	
		}else if($typeid==40){
			$title="网络理财平台_链金所管理团队介绍";
	        $keyword="链金所管理团队,链金所管理团队介绍";
	        $description="网络理财平台_链金所管理团队介绍.专业团队，品质服务.链金所管理团队为了链金所的美好愿景而努力.";	
		}else if($typeid==37){
			$title="企业合作机构介绍_链金所";
	        $keyword="链金所合作机构,链金所合作企业";
	        $description="链金所合作机构包括新浪,广东华商律师事务所,嘉实资本管理有限公司,鹏元征信有限公司,微财富,中国银行,杭州同盾科技有限公司,锦腾小额贷款有限公司.";	
		}else if($typeid==42){
			$title="新浪存钱罐是什么？存钱罐账户说明简介_链金所";
	        $keyword="新浪存钱罐是什么,存钱罐简介";
	        $description="新浪存钱罐是什么？存钱罐是新浪旗下推出的理财增值服务,链金所为增加用户收益,客户在注册充值之后,资金便进入存钱罐用户.即使不投标,也同样每天都会有利息产生,不让资金闲置.";	
		}else if($typeid==46){
			$title="公司荣誉_链金所";
	        $keyword="链金所荣誉";
	        $description="链金所母公司“特速集团”荣获中国木材与木制品流通协会副会长单位安全联盟信誉企业,中国互联网优秀企业等企业荣誉.";	
		}else if($typeid==38){
			$title="链金所风险互助基金计划_网络理财平台的风险互助基金有什么用?用途在哪？";
	        $keyword="风险互助基金,链金所风险互助基金,网络理财平台风险互助基金有什么用,网络理财平台风险互助基金用途在哪";
	        $description="风险互助基金计划是链金所为了保护平台资金借出方的共同利益而建立的信用风险保护机制,全力保护资金安全.";
		}else if($typeid==17){
			$title="网络借贷政策法规,网络贷款政策法规,网络理财政策法规_链金所";
	        $keyword="网络借款政策法规,网络贷款政策法规,网络理财政策法规";
	        $description="链金所严格按照网络借贷政策法规,网络贷款政策法规,网络理财政策法规进行金融服务.";	
		}else if($typeid==16){
			$title="网络理财平台费用,资费说明_链金所";
	        $keyword="网络理财平台资费说明,网络理财平台费用";
	        $description="网络理财平台费用,资费说明,链金所免充值费,提现费,居间服务费,管理费.";	
		}else if($typeid==26){
			$title="如何投资网络理财平台,网络理财平台投资注意事项_链金所";
	        $keyword="如何投资网络理财平台,网络理财平台投资注意事项";
	        $description="如何投资网络理财平台,网络理财平台投资有哪些注意事项,想了解链金所投资操作指南,都可登录此页面进行了解.";	
		}else if($typeid==43){
			$title="链金所账户提现操作指南";
	        $keyword="链金所账户提现,链金所体现操作";
	        $description="链金所账户提现操作指南.链金所账户提现额度是多少?到账时间?提现手续费?如何添加银行卡等相关问题都可以登录此页面进行了解.";	
		}else if($typeid==28){
			$title="链金所账户充值操作指南";
	        $keyword="链金所账户充值,链金所账户充值操作";
	        $description="链金所账户充值操作指南.如何在链金所充值?使用网银支付进行充值有限额吗?使用快捷支付进行充值有限额吗?等相关问题都可以登录此页面进行了解.";	
		}else if($typeid==34){
			$title="网络理财投资业务名词解释_链金所";
	        $keyword="投资名词解释,投资业务名词解释";
	        $description="提单,提单质押,现货质押,借贷利率,等额本息,年化利率,散标,弃标违约金,信用认证标,信用报告,信用审核,信用额度,数字证书,个人信用信息等网络理财投资名词解释都可登录此页面进行了解.";	
		}else if($typeid==35){
			$title="联系我们_链金所";
	        $keyword="链金所联系方式";
	        $description="链金所官网联系方式,包括:公司地址,客服电话,客服邮箱,链金所官方微博,链金所微信订阅号,链金所官网qq群.";	
		}
		if($zu=='aboutus-gwtd'){
			$title="网络理财平台_公司文化";
			$keyword="公司文化";
			$description="链金所公司文化.";
		}
		if($zu=='aboutus-tdjs'){
			$title="网络理财平台_链金所管理团队介绍";
			$keyword="管理团队";
			$description="网络理财平台_链金所管理团队介绍.";
		}
		if($zu=='aboutus-report'){
			$title="运营报告";
			$keyword="";
			$description="";
		}
		if($zu=="licai"){
			$title="热门网络投资理财项目知识-互联网金融投资知识-网络投资理财知识大全-链金所";
			$keyword="网络投资理财项目,互联网金融投资知识,网络投资理财知识";
			$description="链金所为投资者提供最新,最热门的网络投资理财项目知识,互联网金融投资知识等网络投资理财知识大全信息,避免投资者进行盲目投资.";
		}
		if($zu=="gonggao"){
			$title="链金所网站公告";
			$keyword="链金所公告";
			$description="网络理财平台—链金所网站公告.";
		}
		if($zu=="news"){
			$title="链金所公司新闻_网络理财平台动态—链金所";
			$keyword="链金所新闻动态报导,网络理财平台新闻资讯";
			$description="链金所新闻资讯栏目提供链金所与网络理财最全,全新的资讯与新闻讯息.更多最新的网络理财新闻资讯请关注链金所新闻资讯.";
		}
		if($zu=="dongtai"){
			$title="网络理财平台动态—链金所";
			$keyword="链金所动态,网络理财平台动态";
			$description="平台最新动态—链金所.";
		}
	
		$this->assign('title',$title);
		$this->assign('keyword',$keyword);
		$this->assign('description',$description);
		if($typeset==1){//列表页
			$parm['pagesize']=15;
			$parm['type_id']=$typeid;
			if($is_subsite===false){
				$list = getArticleList($parm);
				$vo = D('Acategory')->find($typeid);
				if($vo['parent_id']<>0){
					$this->assign('cname',D('Acategory')->getFieldById($vo['parent_id'],'type_name'));
				}
				else{
					$this->assign('cname',$vo['type_name']);
				}
			}
			else{
				$vo = D('Aacategory')->find($typeid);
				if($vo['parent_id']<>0){
					$this->assign('cname',D('Aacategory')->getFieldById($vo['parent_id'],'type_name'));
				}
				else {
					$this->assign('cname',$vo['type_name']);
				}
				$parm['area_id']= $this->siteInfo['id'];
				$list = getAreaArticleList($parm);
			}
			$this->assign("vo",$vo);
			$this->assign("list",$list['list']);
			if($list["count"]>=$parm['pagesize']){//大于1页才显示分页
				$this->assign("pagebar",$list['page']);
			}
		}else{

			if($is_subsite===false){
				$vo = D('Acategory')->find($typeid);
				if($vo['parent_id']<>0){
					$this->assign('cname',D('Acategory')->getFieldById($vo['parent_id'],'type_name'));
				}
				else {
					$this->assign('cname',$vo['type_name']);
				}
			}
			else{
				$vo = D('Aacategory')->find($typeid);
				if($vo['parent_id']<>0) {
					$this->assign('cname',D('Aacategory')->getFieldById($vo['parent_id'],'type_name'));
				}
				else{
					$this->assign('cname',$vo['type_name']);
				}
			}

			$this->assign("vo",$vo);
			$this->assign("mycid",$vo["type_nid"]);
		}
		$condition=array("gsjj","gsry","gwtd","hzjg","lxwm","tdjs","gonggao","news","dongtai","licai","report");
			if(in_array($vo["type_nid"],$condition)){
				$a=1;
			}else{
				$a=2;
			}
			$this->assign("type",$a);//标记加载那一个菜单
		$this->display($typeinfo['templet']);
    }

	/**
	 * 文章具体内容
	 */
	public function view(){
		$id = intval($_GET['id']);
		$articleModel=M("article a");
		$vo =$articleModel->find($id);
		if($vo){
			$articleModel->where(array("id"=>$id))->save(array("art_click"=>$vo["art_click"]+1));
			$tid = $vo['type_id'];
			$wo = M('article_category')->find($tid);
			$typeid = $vo['type_id'];
			$listparm['type_id']=$typeid;
			$listparm['limit']=15;
			$leftlist = getTypeListActa($listparm);

			$this->assign("wo",$wo);
			$this->assign("vo",$vo);
			$this->assign("leftlist",$leftlist);
			$this->assign("cid",$typeid);
			$vop = D('Acategory')->field('type_name,parent_id,type_nid')->find($typeid);
			if($vop['parent_id']<>0){
				$this->assign('cname',D('Acategory')->getFieldById($vop['parent_id'],'type_name'));
			}
			else {
				$this->assign('cname',$vop['type_name']);
			}

			/****************热门文章 按点击量倒序显示10篇************************/
			$artist=$articleModel->field("a.*,type_nid")->join("lzh_article_category t on t.id=a.type_id")->where(array("a.id"=>array("neq",$id)))->order("art_click desc")->limit(10)->select();
			//防止热门文章和最新文章显示相同了
			$ad=" where a.id not in(".$id.", ";
			$co=0;
			$suffix=C("URL_HTML_SUFFIX");
            foreach ($artist as $key=>$value){
				$co++;
                $ad.=$value["id"].", ";
				if($value['art_set']==1){
					$artist[$key]['arturl'] = (stripos($value['art_url'],"http://")===false)?"http://".$value['art_url']:$value['art_url'];
				}
				else {
					$artist[$key]['arturl'] = MU("Home/{$value["type_nid"]}","article",array("id"=>$value['id'],"suffix"=>$suffix));
				}
			}
			$ad=trim($ad);
			$ad=substr($ad, 0, strlen($ad)-1);//去除右侧的逗号
			$ad.=") and type_id={$tid}";
			//如果有10篇文章，则可能有多余的文章，反之没有多余文章就不显示最新的5篇文章
			$remencount=0;
			if($co==10){
               $zuijinlist=$articleModel->query("select a.*,t.type_nid from lzh_article a left join lzh_article_category t on t.id=a.type_id ".$ad." order by art_time desc limit 5");
				foreach ($zuijinlist as $k=>$v){
					if($v['art_set']==1){
						$zuijinlist[$k]['arturl'] = (stripos($v['art_url'],"http://")===false)?"http://".$v['art_url']:$v['art_url'];
					}
					else {
						$zuijinlist[$k]['arturl'] = MU("Home/{$v["type_nid"]}","article",array("id"=>$v['id'],"suffix"=>$suffix));
					}
				}
				$remencount=count($zuijinlist);
			   $this->assign("zuijinlist",$zuijinlist)	;
			}
			$this->assign("remencount",$remencount);//热门文章数量
			$this->assign("artist",$artist)	;
			/**********************相关文章  固定在底部，显示最新的5篇*****************************/
		}else{
			$this->error("当前文章不存在");
		}
		$this->assign("type",1);//标记加载那一个菜单
		$this->display();
	}
	
	public function kf(){
		$kflist = M("ausers")->where("is_kf=1")->select();
		$this->assign("kflist",$kflist);
		//left
		$listparm['type_id']=0;
		$listparm['limit']=20;
		if($_GET['type']=="subsite"){
			$listparm['area_id'] = $this->siteInfo['id'];
			$leftlist = getAreaTypeList($listparm);
		}else	$leftlist = getTypeList($listparm);
		
		$this->assign("leftlist",$leftlist);
		$this->assign("cid",$typeid);
		
		if($_GET['type']=="subsite"){
			$vop = D('Aacategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Aacategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}else{
			$vop = D('Acategory')->field('type_name,parent_id')->find($typeid);
			if($vop['parent_id']<>0) $this->assign('cname',D('Acategory')->getFieldById($vop['parent_id'],'type_name'));
			else $this->assign('cname',$vop['type_name']);
		}

		$this->display();
	}
	
	public function tuiguang(){
		$_P_fee=get_global_setting();
		$this->assign("reward",$_P_fee);	
		$field = " m.id,m.user_name,sum(ml.affect_money) jiangli ";
		$list = M("members m")->field($field)->join(" lzh_member_moneylog ml ON m.id = ml.target_uid ")->where("ml.type=13")->group("ml.uid")->order('jiangli desc')->limit(10)->select();
		$this->assign("list",$list);	
		
		$this->display();
	}
	
	//秒标未能自动复审时，管理员手动处理方法之应急处理方案  fan  2013-10-22
	//使用方法：直接在浏览器访问该方法。例如：http://www.lvmaquebeat.cn/help/domiao?borrow_id=15
	 public function domiao()
    {
		$borrow_id = intval($_REQUEST['borrow_id']);
		$vm = M('borrow_info')->field('borrow_uid,borrow_money,has_borrow,borrow_type,borrow_status')->find($borrow_id);
		if(($vm['borrow_status']==7) ||($vm['borrow_status']==9) || ($vm['borrow_status']==10)){
			$this->error('该标已还款完成，请不要重复还款！');
			exit;
		}
		
		//复审投标检测
		$capital_sum1=M('investor_detail')->where("borrow_id={$borrow_id}")->sum('capital');
		$capital_sum2=M('borrow_investor')->where("borrow_id={$borrow_id}")->sum('investor_capital');
		if(($vm['borrow_money']!=$capital_sum2) || ($capital_sum1 != $capital_sum2) || ($vm['borrow_money'] !=$vm['has_borrow'])){
			$this->error('投标金额不统一，请确认！');
			exit;
		}else{
			if($vm['borrow_type']==3){
				borrowApproved($borrow_id); 
				$done = borrowRepayment($borrow_id,1);
				if(!$done){
					$this->error('还款失败，请确认！');exit;
				}else{
					$this->success('还款成功，请确认！');
					exit;
				}
			}else{
				$this->error('非秒标类型，不能执行此操作，请确认！');exit;
			}
		}
	}

	/**
	 * 公共的左侧列表菜单
	 * @param $typename
	 */
	private function commonfunc($typename){
		$listparm['type_id']=9;
		$listparm['limit']=15;
		$zu="gsjj";
		$categroylist=M("article_category")->where(array("type_nid"=>$zu))->find();
		$listparm['type_id']= $categroylist["id"];
		$leftlist = getTypeListActa($listparm);
		$this->assign("leftlist",$leftlist);
		$this->assign("mycid",$typename);
	}
	/**
	 * 企业发展历程
	 */
	public function fazhan()
	{
		$this->assign("typename","fazhan");
		$this->commonfunc("fazhan");
		$this->display();
	}

	/**
	 * 运营数据
	 */
	public function yunying()
	{
		$this->commonfunc("yunying");
		$this->display();
	}

	/**
	 * 投资团队
	 */
	public function touzi(){
		$this->assign("typename","touzi");
		$this->commonfunc("touzi");
		$this->display();
	}

}