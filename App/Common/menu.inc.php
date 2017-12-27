<?php
/*array(菜单名，菜单url参数，是否显示)*/
$i = $j = $k = 0;
$menu_left = array();
$menu_left[$i]=array('全局','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('全局设置','#',1);
$menu_left[$i][$i."-".$j][] = array('欢迎页',U('/admin/welcome/index'),1);
$menu_left[$i][$i."-".$j][] = array('网站设置',U('/admin/global/websetting'),1);
$menu_left[$i][$i."-".$j][] = array('友情链接',U('/admin/global/friend'),1);
$menu_left[$i][$i."-".$j][] = array('合作伙伴',U('/admin/global/partners'),1);
$menu_left[$i][$i."-".$j][] = array('广告管理',U('/admin/ad/index'),1);

$menu_left[$i][$i."-".$j][] = array('登陆接口管理',U('/admin/loginonline/index'),1);
$menu_left[$i][$i."-".$j][] = array("自动执行参数",U("/admin/auto/index"),1);
$menu_left[$i][$i."-".$j][] = array("后台操作日志",U("/admin/global/adminlog"),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('缓存管理','#',1);
$menu_left[$i][$i."-".$j][] = array('清除缓存',U('/admin/global/cleanall'),1);

$i++;
$menu_left[$i]= array('借款管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('借款列表','#',1);
// $menu_left[$i][$i."-".$j][] = array('初审待审核借款',U('/admin/borrow/waitverify'),1);
//$menu_left[$i][$i."-".$j][] = array('已逾期借款',U('/admin/borrow/overdue'),1);
$menu_left[$i][$i."-".$j][] = array('复审待审核借款',U('/admin/borrow/waitverify2'),1);
$menu_left[$i][$i."-".$j][] = array('招标中借款',U('/admin/borrow/waitmoney'),1);
$menu_left[$i][$i."-".$j][] = array('还款中借款',U('/admin/borrow/repaymenting'),1);
$menu_left[$i][$i."-".$j][] = array('已完成的借款',U('/admin/borrow/done'),1);
$menu_left[$i][$i."-".$j][] = array('已流标借款',U('/admin/borrow/unfinish'),1);
$menu_left[$i][$i."-".$j][] = array('初审未通过的借款',U('/admin/borrow/fail'),1);
$menu_left[$i][$i."-".$j][] = array('复审未通过的借款',U('/admin/borrow/fail2'),1);
$menu_left[$i][$i."-".$j][] = array('异常未满的借款',U('/admin/borrow/borrowfull'),1);
$menu_left[$i][$i."-".$j][] = array('债权转让标复审',U('/admin/borrow/debtcheckindex'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array("定投宝管理","#",1);
$menu_left[$i][$i."-".$j][] = array('添加定投宝',U('/admin/fund/add'),1);
$menu_left[$i][$i."-".$j][] = array("认购中的定投宝",U("/admin/fund/index"),1);
$menu_left[$i][$i."-".$j][] = array("还款中的定投宝",U("/admin/fund/repayment"),1);
$menu_left[$i][$i."-".$j][] = array("已完成的定投宝",U("/admin/fund/endtran"),1);

$j++;
// $menu_left[$i]['low_title'][$i."-".$j] = array("财务管理","#",1);
// $menu_left[$i][$i."-".$j][] = array('债权转让手续费',U('/admin/debt/index'),1);

// $j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('逾期借款管理','#',1);
$menu_left[$i][$i."-".$j][] = array('逾期统计',U('/admin/expired/detail'),0);
$menu_left[$i][$i."-".$j][] = array('已逾期借款',U('/admin/expired/index'),1);
$menu_left[$i][$i."-".$j][] = array('逾期会员列表',U('/admin/expired/member'),1);

$i++;
$menu_left[$i]= array('会员管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('会员管理','#',1);
$menu_left[$i][$i."-".$j][] = array('会员列表',U('/admin/members/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员资料列表',U('/admin/members/info'),1);
$menu_left[$i][$i."-".$j][] = array('举报信息',U('/admin/jubao/index'),1);
$j++;

$menu_left[$i]['low_title'][$i."-".$j] = array('推荐人管理','#',1);
//$menu_left[$i][$i."-".$j][] = array('投资记录',U('/admin/refereedetail/index'),1);
$menu_left[$i][$i."-".$j][] = array('推荐人修改',U('/admin/refereedetail/referees'),1);
$menu_left[$i][$i."-".$j][] = array('推荐人审核',U('/admin/refereedetail/audit'),1);
$menu_left[$i][$i."-".$j][] = array('审核配置',U('/admin/refereedetail/auditconf'),1);
$j++;

$menu_left[$i]['low_title'][$i."-".$j] = array('认证及申请管理','#',1);
$menu_left[$i][$i."-".$j][] = array('手机认证会员',U('/admin/verifyphone/index'),1);
$menu_left[$i][$i."-".$j][] = array('视频认证申请',U('/admin/verifyvideo/index'),1);
$menu_left[$i][$i."-".$j][] = array('现场认证申请',U('/admin/verifyface/index'),1);
$menu_left[$i][$i."-".$j][] = array('VIP申请管理',U('/admin/vipapply/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员实名认证申请',U('/admin/memberid/index'),1);
$menu_left[$i][$i."-".$j][] = array('额度申请待审核',U('/admin/members/infowait'),1);
$menu_left[$i][$i."-".$j][] = array('上传资料管理',U('/admin/memberdata/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('留言反馈','#',1);
$menu_left[$i][$i."-".$j][] = array('管理列表',U('/admin/feedback/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('评论管理','#',1);
$menu_left[$i][$i."-".$j][] = array('评论列表',U('/admin/comment/index'),1);

$i++;
$menu_left[$i]= array('积分管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('投资积分管理','#',1);
$menu_left[$i][$i."-".$j][] = array('投资积分操作记录',U('/admin/market/index'),1);
$menu_left[$i][$i."-".$j][] = array('商品兑换管理',U('/admin/market/getlog'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('积分商城管理','#',1);
$menu_left[$i][$i."-".$j][] = array('商城商品列表',U('/admin/market/goods'),1);
$menu_left[$i][$i."-".$j][] = array('抽奖商品列表',U('/admin/market/lottery'),1);
$menu_left[$i][$i."-".$j][] = array('评论列表',U('/admin/market/comment'),1);

$i++;
$menu_left[$i]= array('充值提现','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('充值管理','#',1);
$menu_left[$i][$i."-".$j][] = array('在线充值',U('/admin/Paylog/paylogonline'),1);
$menu_left[$i][$i."-".$j][] = array('线下充值',U('/admin/Paylog/paylogoffline'),1);
$menu_left[$i][$i."-".$j][] = array('充值记录总列表',U('/admin/Paylog/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('提现管理','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核提现',U('/admin/Withdrawlogwait/index'),1);
$menu_left[$i][$i."-".$j][] = array('审核通过,处理中',U('/admin/Withdrawloging/index'),1);
$menu_left[$i][$i."-".$j][] = array('已提现 ',U('/admin/Withdrawlog/withdraw2'),1);
$menu_left[$i][$i."-".$j][] = array('审核未通过',U('/admin/Withdrawlog/withdraw3'),1);
$menu_left[$i][$i."-".$j][] = array('提现申请总列表',U('/admin/Withdrawlog/index'),1);

$i++;
$menu_left[$i]= array('财务','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('财务管理','#',1);
$menu_left[$i][$i."-".$j][] = array('标满对帐',U('/admin/Caiwu/manbiao'),1);
$menu_left[$i][$i."-".$j][] = array('充值对帐',U('/admin/Caiwu/chongzhi'),1);
$menu_left[$i][$i."-".$j][] = array('提现对帐',U('/admin/Caiwu/tixian'),1);
$menu_left[$i][$i."-".$j][] = array('退款对帐',U('/admin/Caiwu/tuikuang'),1);
$menu_left[$i][$i."-".$j][] = array('用户资金',U('/admin/Caiwu/zhiji'),1);
$menu_left[$i][$i."-".$j][] = array('借款合同',U('/admin/Caiwu/borrowlist'),1);
$menu_left[$i][$i."-".$j][] = array('红包奖励',U('/admin/Caiwu/redbag'),1);
$menu_left[$i][$i."-".$j][] = array('还款对账',U('/admin/Caiwu/huankuang'),1);
$menu_left[$i][$i."-".$j][] = array('投资券对账',U('/admin/Caiwu/touziquan'),1);
$menu_left[$i][$i."-".$j][] = array('加息券对账',U('/admin/Caiwu/jxquan'),1);
$menu_left[$i][$i."-".$j][] = array('手续费',U('/admin/Caiwu/shouxufei'),1);
$menu_left[$i][$i."-".$j][] = array('综合服务费',U('/admin/Caiwu/feemoney'),1);
$menu_left[$i][$i."-".$j][] = array('咨询服务费',U('/admin/Caiwu/danbao'),1);
$menu_left[$i][$i."-".$j][] = array('内部员工推荐',U('/admin/Caiwu/company'),1);
$menu_left[$i][$i."-".$j][] = array('债权转让手续费',U('/admin/Caiwu/zhaiquanfee'),1);
$menu_left[$i][$i."-".$j][] = array('外部推荐',U('/admin/Caiwu/outside'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('标的管理','#',1);
$menu_left[$i][$i."-".$j][] = array('投标统计明细',U('/admin/Caiwu/invest_info'),1);
$menu_left[$i][$i."-".$j][] = array('借款统计明细',U('/admin/Caiwu/invest_member_info'),1);

$i++;
$menu_left[$i]= array('风控','#',1);
$menu_left[$i]['low_title'][$i."-".$j]=array("标信息","#",1);
$menu_left[$i][$i."-".$j][]=array('还款中标',U("/admin/fengkong/pay_borrow_info"),1);
$menu_left[$i][$i."-".$j][]=array('已完成标',U("/admin/fengkong/end_borrow_info"),1);
$menu_left[$i][$i."-".$j][]=array('提前还款申请',U("/admin/fengkong/answer_borrow"),1);
$menu_left[$i][$i."-".$j][]=array('查看合同',U("/admin/fengkong/borrowlist"),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j]=array("担保机构管理","#",1);
$menu_left[$i][$i."-".$j][]=array('企业账号列表',U("/admin/fengkong/company_list"),1);
$menu_left[$i][$i."-".$j][]=array('担保账号列表',U("/admin/fengkong/danbao_list"),1);
/*
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('信用查询','#',1);
$menu_left[$i][$i."-".$j][] = array('同盾查询',U('/admin/fengkong/tongdun'),1);*/
$i++;
$menu_left[$i]= array('运营','#',1);
$menu_left[$i]['low_title'][$i."-".$j]=array("常规数据","#",1);
// $menu_left[$i][$i."-".$j][]=array('推荐人明细',U("/admin/yunwei/friend"),1);
//$menu_left[$i][$i."-".$j][]=array('注册数据统计',U("/admin/yunwei/statistics"),1);
$menu_left[$i][$i."-".$j][]=array('投资用户数统计',U("/admin/yunwei/investcount"),1);
$menu_left[$i][$i."-".$j][]=array('运营推广数统计',U("/admin/yunwei/generalcount"),1);
$menu_left[$i][$i."-".$j][]=array('风险评估',U("/admin/yunwei/risklist"),1);
$menu_left[$i][$i."-".$j][]=array('风险评估问卷',U("/admin/yunwei/reskproblem"),1);
// $menu_left[$i][$i."-".$j][]=array('融普惠用户统计',U("/admin/yunwei/rphstatistics"),1);
// $menu_left[$i][$i."-".$j][]=array('长期返利明细统计',U("/admin/yunwei/recommendcommission"),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j]=array("渠道用户统计","#",1);
$menu_left[$i][$i."-".$j][]=array('风车用户统计',U("/admin/yunwei/fengche"),1);
$menu_left[$i][$i."-".$j][]=array('车轮用户统计',U("/admin/yunwei/chelun"),1);
$menu_left[$i][$i."-".$j][]=array('CPS用户统计',U("/admin/yunwei/fubabastatistics"),1);
$menu_left[$i][$i."-".$j][]=array('推广链接统计',U("/admin/yunwei/regsourcecount"),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j]=array("平台赠券发放","#",1);
$menu_left[$i][$i."-".$j][]=array('投资券',U("/admin/yunwei/sendtouziquan"),1);
$menu_left[$i][$i."-".$j][]=array('加息券',U("/admin/yunwei/sendjiaxiquan"),1);
$menu_left[$i][$i."-".$j][]=array('定向用户投资券',U("/admin/yunwei/dingxiang"),1);

//线下店铺统计
// $j++;
// $menu_left[$i]['low_title'][$i."-".$j]=array("数据统计1","#",1);
// $menu_left[$i][$i."-".$j][]=array('投资统计',U("/admin/yunwei/recommentinvestlist"),1);
// $menu_left[$i][$i."-".$j][]=array('回款统计',U("/admin/yunwei/recommentpaymentlist"),1);
// $menu_left[$i][$i."-".$j][]=array('未实名明细',U("/admin/yunwei/norealnameverify"),1);
// $menu_left[$i][$i."-".$j][]=array('未投资明细',U("/admin/yunwei/noinvestlist"),1);
// $menu_left[$i][$i."-".$j][]=array('站岗资金',U("/admin/yunwei/stillmoney"),1);

// //线下店铺统计
// $j++;
// $menu_left[$i]['low_title'][$i."-".$j]=array("数据统计2","#",1);
// $menu_left[$i][$i."-".$j][]=array('原始标统计',U("/admin/yunwei/ordinarycommission"),1);
// $menu_left[$i][$i."-".$j][]=array('债转表统计',U("/admin/yunwei/zhaiquancommission"),1);
// $menu_left[$i][$i."-".$j][]=array('提前还款',U("/admin/yunwei/repaymentinadvance"),1);
// $menu_left[$i][$i."-".$j][]=array('被推荐人统计',U("/admin/yunwei/outsidecommission"),1);

//线下店铺统计

$j++;
$menu_left[$i]['low_title'][$i.'-'.$j] = ['链金所活动', '#', 1];
$menu_left[$i][$i."-".$j][] = ['内部员工活动', U("/admin/yunwei/common1"), 1];
$menu_left[$i][$i."-".$j][] = ['38活动奖品明细表', U("/admin/yunwei/activity38"), 1];
$menu_left[$i][$i."-".$j][] = ['奥运竞猜活动', U("/admin/yunwei/olympicactivity"), 1];
$menu_left[$i][$i."-".$j][] = ['周年庆活动', U("/admin/yunwei/zhounian"), 1];

$k = 4;
$menu_left[$i][$i."-".$j][$k] = ['追梦活动', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['追梦活动', U("/admin/yunwei/dream"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['奖品配置', U("/admin/yunwei/dreamprize"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['中奖信息', U("/admin/yunwei/dreamwinner"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['梦想种子充值', U("/admin/yunwei/dreamfeedscharge"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['异常检查', U("/admin/yunwei/indicator"), 1];

$k++;
$menu_left[$i][$i."-".$j][$k] = ['广开财路', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['邀请人数', U("/admin/yunwei/themayrecommendcount"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['抽奖结果', U("/admin/yunwei/themayprizeresult"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['奖品概率设置', U("/admin/yunwei/themayprizeset"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['二重礼奖品', U("/admin/yunwei/themayseconde"), 1];

$k++;
$menu_left[$i][$i."-".$j][] = ['8月贺A轮融资', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['新人返利', U("/admin/vc/openaccountoffer"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['老用户抽奖', U("/admin/vc/prizewheel"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['新老用户推荐', U("/admin/vc/recommend"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['新老用户兑换', U("/admin/vc/gift"), 1];

$k++;
$menu_left[$i][$i."-".$j][] = ['9月活动', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['送现金', U("/admin/pro9/nineone"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['送现金设置', U("/admin/pro9/config/1"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['砸冰块', U("/admin/pro9/ninetwo"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['砸冰块设置', U("/admin/pro9/config/2"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['限量加息', U("/admin/pro9/ninethree"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['限量加息设置', U("/admin/pro9/config/3"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['荣耀之争1', U("/admin/pro9/ryone"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['荣耀之争2', U("/admin/pro9/rytwo"), 1];

$k++;
$menu_left[$i][$i."-".$j][] = ['9月内部投资返利', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['自身', U("/admin/inn/self/month/1"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['推荐', U("/admin/inn/recommend/month/1"), 1];

$k++;
$menu_left[$i][$i."-".$j][]=array("10月活动","#",2);
$menu_left[$i][$i."-".$j][$k]['low_title'][]=array('周年庆抢标活动',U("/admin/octact/zhounian17"),1);
$menu_left[$i][$i."-".$j][$k]['low_title'][]=array('月底家具大闯关',U("/admin/octact/furniturewin"),1);

$k++;
$menu_left[$i][$i."-".$j][] = ['10月内部投资返利', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['自身', U("/admin/inn/self/month/2"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['推荐', U("/admin/inn/recommend/month/2"), 1];

$k++;
$menu_left[$i][$i."-".$j][] = ['11月双11活动', '#', 2];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['邀请好友投资送0.8%', U("/admin/huodong201711/index"), 1];
$menu_left[$i][$i.'-'.$j][$k]['low_title'][] = ['邀请好友首投返现', U("/admin/huodong201711/firstinvest"), 1];

//运营统计
$j++;
$menu_left[$i]['low_title'][$i."-".$j]=array("运营统计","#",1);
$menu_left[$i][$i."-".$j][]=array('单日运营数据',U("/admin/ywagg/regaggregate"),1);
$menu_left[$i][$i."-".$j][]=array('投资数据',U("/admin/ywagg/investaggregate"),1);
$menu_left[$i][$i."-".$j][]=array('渠道相关数据',U("/admin/ywagg/cpsaggregate"),1);
$menu_left[$i][$i."-".$j][]=array('拉新数据',U("/admin/ywagg/recommendaggregate"),1);
$menu_left[$i][$i."-".$j][]=array('充值回款数据',U("/admin/ywagg/rechargeaggregate"),1);
$menu_left[$i][$i."-".$j][]=array('单日标的数据',U("/admin/ywagg/contractaggregate"),1);

$i++;
$menu_left[$i]= array('文章管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('文章管理','#',1);
$menu_left[$i][$i."-".$j][] = array('文章列表',U('/admin/article/index'),1);
$menu_left[$i][$i."-".$j][] = array('文章分类',U('/admin/acategory/index'),1);
$i++;
$menu_left[$i]= array('菜单管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('菜单管理','#',1);
$menu_left[$i][$i."-".$j][] = array('导航菜单',U('/admin/navigation/index'),1);

$i++;
$menu_left[$i]= array('资金统计','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('会员帐户','#',1);
$menu_left[$i][$i."-".$j][] = array('会员帐户',U('/admin/CapitalAccount/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('充值提现','#',1);
$menu_left[$i][$i."-".$j][] = array('充值记录',U('/admin/CapitalOnline/charge'),1);
$menu_left[$i][$i."-".$j][] = array('提现记录',U('/admin/CapitalOnline/withdraw'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('会员资金变动记录','#',1);
$menu_left[$i][$i."-".$j][] = array('资金记录',U('/admin/CapitalDetail/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('网站资金统计','#',1);
$menu_left[$i][$i."-".$j][] = array('网站资金统计',U('/admin/CapitalAll/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('待还款资金统计','#',1);
$menu_left[$i][$i."-".$j][] = array('待还款资金统计',U('/admin/CapitalRepay/index'),1);

$i++;
$menu_left[$i]= array('权限','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('用户权限管理',"#",1);
$menu_left[$i][$i."-".$j][] = array('管理员管理',U('/admin/Adminuser/index'),1);
$menu_left[$i][$i."-".$j][] = array('用户组权限管理',U('/admin/acl/index'),1);


$i++;
$menu_left[$i]= array('数据库','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('数据库管理','#',1);
$menu_left[$i][$i."-".$j][] = array('数据库信息',U('/admin/db/index'),1);
$menu_left[$i][$i."-".$j][] = array('备份管理',U('/admin/db/baklist'),1);
$menu_left[$i][$i."-".$j][] = array('清空数据',U('/admin/db/truncate'),1);

$i++;
$menu_left[$i]= array('投资券','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('投资券管理','#',1);
$menu_left[$i][$i."-".$j][] = array('投资券管理',U('/admin/experience/index'),1);

$i++;
$menu_left[$i]= array('营销活动','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('投资活动','#',1);
$menu_left[$i][$i."-".$j][] = array('赠送投资券',U('/admin/marketactive/givecoupons'),1);
$menu_left[$i][$i."-".$j][] = array('礼品赠送',U('/admin/marketactive/giftgive'),1);
$menu_left[$i][$i."-".$j][] = array('双旦活动',U('/admin/doubleactivity/index'),1);
$menu_left[$i][$i."-".$j][] = array('活动奖品设置',U('/admin/doubleactivity/giftsetting'),1);
$menu_left[$i][$i."-".$j][] = array('外部推荐',U('/admin/wbtuijian/index'),1);

$i++;
$menu_left[$i]= array('扩展管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('参数管理','#',1);
$menu_left[$i][$i."-".$j][] = array('业务参数管理',U('/admin/bconfig/index'),1);
$menu_left[$i][$i."-".$j][] = array('合同居间方资料上传',U('/admin/hetong/index'),1);
$menu_left[$i][$i."-".$j][] = array('信用级别管理',U('/admin/leve/index'),1);
$menu_left[$i][$i."-".$j][] = array('投资级别管理',U('/admin/leve/invest'),1);
$menu_left[$i][$i."-".$j][] = array('会员年龄别称',U('/admin/age/index'),1);
$menu_left[$i][$i."-".$j][] = array('债权转让参数管理',U('/admin/debtsetting/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('充值银行管理','#',1);
$menu_left[$i][$i."-".$j][] = array('线下充值银行管理',U('/admin/payoffline/index'),1);
$menu_left[$i][$i."-".$j][] = array('线上支付接口管理',U('/admin/payonline/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('在线客服管理','#',1);
$menu_left[$i][$i."-".$j][] = array('QQ客服管理',U('/admin/QQ/index'),1);
$menu_left[$i][$i."-".$j][] = array('QQ群管理',U('/admin/QQ/qun'),1);
$menu_left[$i][$i."-".$j][] = array('客服电话管理',U('/admin/QQ/tel/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('在线通知管理','#',1);
$menu_left[$i][$i."-".$j][] = array('通知信息接口管理',U('/admin/msgonline/index'),1);
$menu_left[$i][$i."-".$j][] = array('通知信息模板管理',U('/admin/msgonline/templet/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('百度云推送管理','#',0);
$menu_left[$i][$i."-".$j][] = array('手机客户端云推送',U('/admin/baidupush/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('对账管理','#',1);
$menu_left[$i][$i."-".$j][] = array('手动对账',U('/admin/sinasftp/index'),1);


$i++;
$menu_left[$i]= array('功能管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('功能管理','#',1);
$menu_left[$i][$i."-".$j][] = array('自动投标功能',U('/admin/autoborrow/index'),1);


// $j++;
// $menu_left[$i]['low_title'][$i."-".$j] = array('安全检测','#',1);
// $menu_left[$i][$i."-".$j][] = array('文件管理',U('/admin/mfields/'),1);
// $menu_left[$i][$i."-".$j][] = array('木马查杀',U('/admin/scan/'),1);

?>
