<?php
/*array(菜单名，菜单url参数，是否显示)*/
//error_reporting(E_ALL);
/*
$acl_inc[$i]['low_leve']['global']  global是model
每个action前必须添加eqaction_前缀'eqaction_websetting'  => 'at1','at1'表示唯一标志,可独自命名,eqaction_后面跟的action必须统一小写


*/
$acl_inc = array();
$i = 0;
$acl_inc[$i]['low_title'][] = '全局设置';
$acl_inc[$i]['low_leve']['global']= array( "网站设置" =>array(
                                                 "列表"       => 'at1',
                                                 "增加"       => 'at2',
                                                 "删除"       => 'at3',
                                                 "修改"       => 'at4',
                                                ),
                                            "友情链接" =>array(
                                                 "列表"       => 'at5',
                                                 "增加"       => 'at6',
                                                 "删除"       => 'at7',
                                                 "修改"       => 'at8',
                                                 "搜索"       => 'att8',
                                            ),
                                            "合作伙伴" =>array(
                                                 "列表"       => 'at9',
                                                 "增加"       => 'at10',
                                                 "删除"       => 'at11',
                                                 "修改"       => 'at12',
                                                 "搜索"       => 'at13',
                                            ),
                                            "所有缓存" =>array(
                                                 "清除"       => 'at22',
                                            ),
                                            "后台操作日志" =>array(
                                                 "列表"       => 'at23',
                                                 "删除"           =>'at24',
                                                 "删除一月前操作日志"=>'at25',
                                            ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_websetting'  => 'at1',
                                                'eqaction_doadd'    => 'at2',
                                                'eqaction_dodelweb'    => 'at3',
                                                'eqaction_doedit'   => 'at4',
                                                //友情链接
                                                'eqaction_friend'      => 'at5',
                                                'eqaction_dodeletefriend'    => 'at7',
                                                'eqaction_searchfriend'    => 'att8',
                                                'eqaction_addfriend'   => array(
                                                                'at6'=>array(
                                                                    'POST'=>array(
                                                                        "fid"=>'G_NOTSET',
                                                                    ),
                                                                 ),
                                                                'at8'=>array(
                                                                    'POST'=>array(
                                                                        "fid"=>'G_ISSET',
                                                                    ),
                                                                ),
                                                    ),
                                                //合作伙伴
                                                'eqaction_partners'        => 'at9',
                                                'eqaction_dodeletepartners'    => 'at11',
                                                'eqaction_searchpartners'    => 'at13',
                                                'eqaction_addpartners'   => array(
                                                                'at10'=>array(
                                                                    'POST'=>array(
                                                                        "pid"=>'G_NOTSET',
                                                                    ),
                                                                 ),
                                                                'at12'=>array(
                                                                    'POST'=>array(
                                                                        "pid"=>'G_ISSET',
                                                                    ),
                                                                ),
                                                    ),
                                                //清除缓存
                                                'eqaction_cleanall'  => 'at22',
                                                'eqaction_adminlog'  => 'at23',
                                                'eqaction_dodeletelog'=>'at24',
                                                'eqaction_dodellogone'=>'at25',//删除近期一个月内的后台操作日志
                                            )
                            );
$acl_inc[$i]['low_leve']['ad']= array( "广告管理" =>array(
                                                 "列表"       => 'ad1',
                                                 "增加"       => 'ad2',
                                                 "删除"       => 'ad4',
                                                 "修改"       => 'ad3',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'ad1',
                                                'eqaction_add'    => 'ad2',
                                                'eqaction_doadd'    => 'ad2',
                                                'eqaction_edit'    => 'ad3',
                                                'eqaction_doedit'    => 'ad3',
                                                'eqaction_swfupload'    => 'ad3',
                                                'eqaction_dodel'    => 'ad4',
                                            )
                            );

$acl_inc[$i]['low_leve']['loginonline']= array( "登陆接口管理" =>array(
                                                 "查看"       => 'dl1',
                                                 "修改"       => 'dl2',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'dl1',
                                                'eqaction_save'    => 'dl2',
                                            )
                            );
$acl_inc[$i]['low_leve']['auto'] = array("自动执行参数" => array(
                                                "查看" => "atjb1",
                                                "修改" => "atjb2",
                                                "开启程序" => "atjb3",
                                                "关闭程序" => "atjb4",
                                                "开启服务" => "atjb5",
                                                "卸载服务" => "atjb7",
                                                "当前运行状态" => "atjb6",
                                                ),
                                                "data" => array(
                                                "eqaction_index" => "atjb1",
                                                "eqaction_save" => "atjb2",
                                                "eqaction_start" => "atjb3",
                                                "eqaction_close" => "atjb4",
                                                "eqaction_startserver" => "atjb5",
                                                "eqaction_stopserver" => "atjb7",
                                                "eqaction_showstatus" => "atjb6",
                                                )
                            );

$i++;
$acl_inc[$i]['low_title'][] = '借款管理';
$acl_inc[$i]['low_leve']['borrow']= array(
                                            "已逾期借款" =>array(
                                                "列表"        => 'br19',
                                            ),
                                            "初审待审核借款" =>array(
                                                 "列表"       => 'br1',
                                                 "审核"       => 'br2',
                                                ),
                                           "复审待审核借款" =>array(
                                                 "列表"       => 'br3',
                                                 "审核"       => 'br4',
                                            ),
                                           "招标中的借款" =>array(
                                                 "列表"       => 'br5',
                                                 "审核"       => 'br6',
                                                 "人工处理"     => 'br8',
                                            ),
                                           "还款中的借款" =>array(
                                                 "列表"       => 'br7',
                                                 "一周内到期标" =>'br7',
                                                 "投资记录" =>'br15',
                                            ),
                                           "已完成的借款" =>array(
                                                 "列表"       => 'br9',
                                            ),
                                           "已流标借款" =>array(
                                                 "列表"       => 'br11',
                                            ),
                                           "初审未通过的借款" =>array(
                                                 "列表"       => 'br13',
                                            ),
                                           "复审未通过的借款" =>array(
                                                 "列表"       => 'br14',
                                            ),
                                            "异常未满的借款" =>array(
                                                 "列表"       => 'br16',
                                                 "人工处理"         => 'br17',
                                            ),
                                            "债权转让标复审" =>array(
                                                "列表"        => 'br19',
                                                "审核"        => 'br20',
                                                "比对原有债权"=>'br21',
                                                "财务审核"=>'br22',
                                            ),
                                            "转现货" =>array(
                                                 "编辑"       => 'br18',
                                            ),

                                       "data" => array(
                                                //网站设置
                                                'eqaction_waitverify'  => 'br1',
                                                'eqaction_edit' =>'br2',
                                                'eqaction_edit' =>'br4',
                                                'eqaction_edit' =>'br6',
                                                'eqaction_doeditwaitverify' =>'br2',
                                                'eqaction_waitverify2'  => 'br3',
                                                'eqaction_doeditwaitverify2'  => 'br4',
                                                'eqaction_waitmoney'  => 'br5',
                                                'eqaction_doeditwaitmoney'  => 'br6',
                                                'eqaction_repaymenting'    => 'br7',
                                                'eqaction_doweek'    => 'br7',
                                                'eqaction_done'    => 'br9',
                                                'eqaction_unfinish'    => 'br11',
                                                'eqaction_fail'    => 'br13',
                                                'eqaction_fail2'    => 'br14',
                                                'eqaction_swfupload'  => 'br2',
                                                'eqaction_dowaitmoneycomplete'  => 'br8',
                                                'eqaction_doinvest'  => 'br15',
                                                'eqaction_borrowfull'  => 'br16',
                                                'eqaction_domoneycomplete'  => 'br17',
                                                'eqaction_editxianhuo'  => 'br18',
                                                'eqaction_computationtime' => 'br18',
                                                'eqaction_editdoxianhuo'  => 'br18',
                                                'eqaction_sum'  => 'br7',
                                                'eqaction_debtcheckindex'=>'br19',
                                                'eqaction_debtcheck'=>'br20',
                                                'eqaction_compare'=>'br21',
                                                'eqaction_debtdetail'=>'br22'
                                            )
                            );
// $acl_inc[$i]['low_leve']['debt'] = array("财务管理" => array(
//                                        '债权手续费' => 'debt1',
//                                     ),
//                                     "data" => array(
//                                         'eqaction_index' => 'debt1',
//                                     ),

// );
$acl_inc[$i]['low_leve']['expired']= array( "逾期借款管理" =>array(
                                                 "查看"       => 'yq1',
                                                 "代还"       => 'yq2',
                                                ),
                                           "逾期会员列表" =>array(
                                                 "列表"       => 'yq3',
                                            ),
                                       "data" => array(
                                                'eqaction_index'  => 'yq1',
                                                'eqaction_doexpired'  => 'yq2',
                                                'eqaction_member'  => 'yq3',
                                            )
                            );
$acl_inc[$i]['low_title'][] = '定投宝管理';
$acl_inc[$i]['low_leve']['fund'] = array("定投宝管理" => array(
                                                "列表" => "fund1",
                                                "添加" => "fund2",
                                                "修改" => "fund3",
                                                "删除" => "fund5",
                                                "投资记录" =>'fund4',),
                                        "data" => array(
                                        "eqaction_endtran" => "fund1",
                                        "eqaction_index" => "fund1",
                                        "eqaction_repayment" => "fund1",
                                        "eqaction_getusername" => "fund2",
                                        "eqaction_swfupload" => "fund2",
                                        "eqaction_add" => "fund2",
                                        "eqaction_doadd" => "fund2",
                                        "eqaction_getusername" => "fund3",
                                        "eqaction_swfupload" => "fund3",
                                        "eqaction_edit" => "fund3",
                                        "eqaction_doedit" => "fund3",
                                        "eqaction_dodel" => "fund5",
                                        'eqaction_doinvest'  => 'fund4',
                                        )
);
$i++;
$acl_inc[$i]['low_title'][] = '会员管理';
$acl_inc[$i]['low_leve']['members']= array( "会员列表" =>array(
                                                 "列表"       => 'me1',
                                                 "调整余额"     => 'mx2',
                                                 "调整授信"     => 'mx3',
                                                 "删除会员"     => 'mxw',
                                                 "修改客户类型"   => 'xmxw',
                                                 "发布借款"     => 'mx4',
                                                ),
                                           "会员资料" =>array(
                                                 "列表"       => 'me3',
                                                 "查看"       => 'me4',
                                            ),
                                           "额度申请待审核" =>array(
                                                 "列表"       => 'me7',
                                                 "审核"       => 'me6',
                                            ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'           => 'me1',
                                                'eqaction_info'            => 'me3',
                                                'eqaction_viewinfom'       => 'me4',
                                                'eqaction_infowait'        => 'me7',
                                                'eqaction_viewinfo'        => 'me6',
                                                'eqaction_doeditcredit'    => 'me6',
                                                'eqaction_domoneyedit'     => 'mx2',
                                                'eqaction_moneyedit'       => 'mx2',
                                                'eqaction_creditedit'      => 'mx3',
                                                'eqaction_dodel'           => 'mxw',
                                                'eqaction_edit'            => 'xmxw',
                                                'eqaction_doedit'          => 'xmxw',
                                                'eqaction_docreditedit'    => 'mx3',
                                                'eqaction_idcardedit'      => 'xmxw',
                                                'eqaction_doidcardedit'    => 'xmxw',
                                                'eqaction_issuesign'       => 'xmxw',
                                                'eqaction_post'            => 'xmxw',
                                                'eqaction_save'            => 'xmxw',
                                                'eqaction_issuesign'       => 'mx4',
                                                'eqaction_swfupload'       => 'mx4',
                                                'eqaction_ajax_company_credit'=>'xmxw'
                                            )
                            );
$acl_inc[$i]['low_leve']['common']= array( "会员详细资料" =>array(
                                                 "查询"       => 'mex5',
                                                 "账户通讯"         => 'sms1',
                                                 "具体通讯"         => 'sms2',
                                                 "节日通讯"         => 'sms3',
                                                 "通讯记录"         => 'sms4',
                                                ),
                                       "data" => array(
                                                'eqaction_member'  => 'mex5',
                                                'eqaction_sms'  => 'sms1',
                                                'eqaction_sendsms'  => 'sms2',
                                                'eqaction_sendgala'  => 'sms3',
                                                'eqaction_smslog'  => 'sms4',
                                            )
                            );
$acl_inc[$i]['low_leve']['refereedetail']= array("推荐人管理" =>array(
                                                 "列表"       => 'referee_1',
                                                 "导出"       => 'referee_2',
                                                 "推荐人修改"        =>  'referees',
                                                 "推荐人审核"    =>   'audit',
                                                 "审核配置"     =>   'auditconf',
                                                ),
                                               "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'referee_1',
                                                    'eqaction_export'  => 'referee_2',
                                                    'eqaction_referees'     =>   'referees',
                                                    'eqaction_refereemodify'        =>   'referees',
                                                    'eqaction_dorefereemodify'      =>   'referees',
                                                    'eqaction_refereephone'     =>   'referees',
                                                    'eqaction_refereeslog'      =>   'referees',
                                                    'eqaction_audit'        =>   'audit',
                                                    'eqaction_editaudit'        =>   'audit',
                                                    'eqaction_doeditaudit'      =>   'audit',
                                                    'eqaction_delaudit'       =>    'audit',
                                                    'eqaction_auditlog'       =>    'audit',
                                                    'eqaction_auditconf'    =>   'auditconf',
                                                    'eqaction_auditconf1'   =>   'auditconf',
                                                    'eqaction_doauditconf'  =>   'auditconf',
                                                    'eqaction_ajaxaudit'    =>    'auditconf',
                                                )
                            );
$acl_inc[$i]['low_leve']['jubao']= array( "举报信息" =>array(
                                                 "列表"       => 'me5',
                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'me5',
                                            )
                            );

$i++;
$acl_inc[$i]['low_title'][] = '认证及申请管理';
$acl_inc[$i]['low_leve']['vipapply']= array( "VIP申请列表" =>array(
                                                 "列表"       => 'vip1',
                                                 "审核"       => 'vip2',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'vip1',
                                                    'eqaction_edit' =>'vip2',
                                                    'eqaction_doedit'  => 'vip2',
                                                )
                            );
$acl_inc[$i]['low_leve']['memberid']= array( "会员实名认证管理" =>array(
                                                 "列表"       => 'me10',
                                                 "审核"       => 'me9',
                                                  "导出"      => 'me8',
                                                ),

                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'me10',
                                                'eqaction_edit'  => 'me9',
                                                'eqaction_doedit'  => 'me9',
                                                'eqaction_export'  => 'me8',
                                            )
                            );

$acl_inc[$i]['low_leve']['verifyvideo']= array( "会员视频证管理" =>array(
                                                 "列表"       => 'me10',
                                                 "审核"       => 'me9',
                                                  "导出"      => 'me8',
                                                ),

                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'me10',
                                                'eqaction_edit'  => 'me9',
                                                'eqaction_doedit'  => 'me9',
                                                'eqaction_export'  => 'me8',
                                            )
                            );

$acl_inc[$i]['low_leve']['verifyface']= array( "会员现场证管理" =>array(
                                                 "列表"       => 'me10',
                                                 "审核"       => 'me9',
                                                  "导出"      => 'me8',
                                                ),

                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'me10',
                                                'eqaction_edit'  => 'me9',
                                                'eqaction_doedit'  => 'me9',
                                                'eqaction_export'  => 'me8',
                                            )
                            );




$acl_inc[$i]['low_leve']['memberdata']= array( "会员上传资料管理" =>array(
                                                 "列表"       => 'dat1',
                                                 "审核"       => 'dat3',
                                                 "上传资料"     => 'dat4',
                                                 "上传展示资料" => 'dat5',
                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'dat1',
                                                'eqaction_swfupload'  => 'dat1',
                                                'eqaction_edit'   => 'dat3',
                                                'eqaction_doedit'  => 'dat3',

                                                'eqaction_upload'  => 'dat4',
                                                'eqaction_doupload'  => 'dat4',
                                                'eqaction_uploadshow'  => 'dat5',
                                                'eqaction_douploadshow'  => 'dat5',
                                            )
                            );
$acl_inc[$i]['low_leve']['verifyphone']= array( "手机认证会员" =>array(
                                                 "列表"       => 'vphone1',
                                                 "导出"       => 'vphone2',
                                                 "审核"       => 'vphone3',
                                                ),



                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'   => 'vphone1',
                                                'eqaction_export'  => 'vphone2',
                                                'eqaction_edit'    => 'vphone3',
                                                'eqaction_doedit'  => 'vphone3',
                                            )
                            );

$acl_inc[$i]['low_leve']['comment']= array( "评论管理" =>array(
                                                 "列表"       => 'vphone1',
                                                 "导出"       => 'vphone2',
                                                 "审核"       => 'vphone3',
                                                ),



                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'   => 'vphone1',
                                                'eqaction_export'  => 'vphone2',
                                                'eqaction_edit'    => 'vphone3',
                                                'eqaction_doedit'  => 'vphone3',
                                            )
                            );


$i++;
$acl_inc[$i]['low_title'][] = '积分管理';
$acl_inc[$i]['low_leve']['market']= array( "投资积分管理" =>array(
                                                 "投资积分操作记录" => 'mk0',
                                                 "获取列表"         => 'mk1',
                                                 "获取操作"         => 'mk2',
                                                 "商城商品列表"   => 'mk3',
                                                 "商品操作"         => 'mk4',
                                                 "上传商品图片"   => 'mk5',
                                                ),
                                            "抽奖管理" =>array(
                                                 "列表"       => 'mk6',
                                                 "编辑"       => 'mk7',
                                                 "删除"       => 'mk8',
                                                ),
                                            "评论管理" =>array(
                                                 "列表"       => 'mkcom1',
                                                 "查看"       => 'mkcom2',
                                                 "删除"       => 'mkcom3',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'mk0',
                                                    'eqaction_getlog'  => 'mk1',
                                                    'eqaction_getlog_edit'  => 'mk2',
                                                    'eqaction_dologedit'  => 'mk2',
                                                    'eqaction_goods'  => 'mk3',
                                                    'eqaction_good_edit'  => 'mk4',
                                                    'eqaction_dogoodedit'  => 'mk4',
                                                    'eqaction_good_del'  => 'mk4',
                                                    'eqaction_lottery'  => 'mk6',
                                                    'eqaction_lottery_edit'  => 'mk7',
                                                    'eqaction_dolotteryedit'  => 'mk7',
                                                    'eqaction_lottery_del'  => 'mk8',
                                                    'eqaction_upload_shop_pic'  => 'mk5',
                                                    'eqaction_comment'  => 'mkcom1',
                                                    'eqaction_dodel'  => 'mkcom3',
                                                    'eqaction_edit'  => 'mkcom2',
                                                    'eqaction_doedit'  => 'mkcom2',
                                                )
                            );

$i++;
$acl_inc[$i]['low_title'][] = '充值提现';
$acl_inc[$i]['low_leve']['paylog']= array( "充值记录" =>array(
                                                 "列表"       => 'cz',
                                                 "充值处理"         => 'czgl',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'cz',
                                                    'eqaction_paylogonline'  => 'cz',
                                                    'eqaction_paylogoffline'  => 'cz',
                                                    'eqaction_edit'  => 'czgl',
                                                    'eqaction_doedit'  => 'czgl'

                                                )
                            );


$i++;
$acl_inc[$i]['low_title'][] = '财务';
$acl_inc[$i]['low_leve']['caiwu']= array( "财务" =>array(
        "满标对帐"      => 'cw',
        "充值对帐"      => 'cz',
        "提现对帐"         =>'tx',
        "退款对帐"         =>'tk',
        "用户资金"         =>'uzj',
        "查看新浪余额"         =>'xlye',
        "借款合同"         =>'jkht',
        "查看合同"         =>'ck',
        "红包奖励"         =>'hb',
        "还款对账"         =>'hk',
        "投资券对账"      =>'tzq',
        "加息券对账"      =>'jxq',
        "手续费"          =>'sxf',
        "综合服务费"          =>'fee',
        "咨询服务费" => 'danbao',
        "内部员工推荐"=>'cwcc',
       "投标统计明细"=>'cwii',
       "借款统计明细"=>"cwimi",
       "债权手续费"=>"zqfee",
       "外部推荐"=>"outside",
),
        "data" => array(
                //网站设置
                'eqaction_manbiao'  => 'cw',
                'eqaction_chongzhi'  => 'cz',
                'eqaction_tixian'  => 'tx',
                'eqaction_tuikuang'  => 'tk',
                'eqaction_zhiji'  => 'uzj',
                'eqaction_loadsina'  => 'xlye',
                'eqaction_borrowlist'  => 'jkht',
                'eqaction_showhetong'  => 'ck',
                'eqaction_redbag'  => 'hb',
                'eqaction_huankuang'  => 'hk',
                'eqaction_touziquan'  => 'tzq',
                'eqaction_jxquan'  => 'jxq',
                'eqaction_shouxufei'  => 'sxf',
                'eqaction_feemoney'  => 'fee',
                'eqaction_danbao'   => 'danbao',
                'eqaction_company'=>'cwcc',
                'eqaction_invest_info'=>'cwii',
                'eqaction_invest_member_info'=>'cwimi',
                'eqaction_showusermoney'=>'cwii',
                'eqaction_zhaiquanfee'=>'zqfee',
                'eqaction_outside'=>'outside'
        )
);

$i++;
$acl_inc[$i]['low_title'][] = '风控';
$acl_inc[$i]['low_leve']['fengkong']= array( "风控" =>array(
                "同盾"        => 'tongd',
                '还款中标'=>'fkb1',
                '已经结束标'=>'fkb2',
                '提前还款申请'=>'fkb3',
                "借款合同"   =>'fkjkht',
                "查看合同"    =>'fkck',
                '企业账号列表'=>'fkb4',
                '设置担保企业'=>'fkb5',
                '担保企业列表'=>'fkb6',
                '担保投资明细'=>'fkb7'
            ),
                "data" => array(
                    //网站设置
                    'eqaction_tongdun'  => 'tongd',
                    'eqaction_pay_borrow_info'=>'fkb1',
                    'eqaction_end_borrow_info'=>'fkb2',
                    'eqaction_answer_borrow'=>'fkb3',
                    'eqaction_borrowlist'  => 'fkjkht',
                    'eqaction_showhetong'  => 'fkck',
                    'eqaction_company_list'=>'fkb4',
                    'eqaction_set_danbao'=>'fkb5',
                    'eqaction_danbao_list'=>'fkb6',
                    'eqaction_danbo_des'=>'fkb7'

                )
            );
$i++;
$acl_inc[$i]['low_title'][] = '运营';
$acl_inc[$i]['low_leve']['yunwei']=array("运营"=>array(
                "内部返利活动"=>'ywc1',
                "38活动"=>'ywc2',
                "邀请好友"=>'ywc3',
                "好友投资列表"=>'ywc4',
                "数据统计"=>'ywc5',
                "奥运竞猜活动"=>'ywc6',
                "投资用户数统计"=>'ywc7',
                "风险评估"=>'ywc8',
                "风险评估问卷"=>'ywc9',
                "周年庆活动"=>'ywc10',
                "运营推广数统计"=>'ywc11',
                "投资券"=>'ywc12',
                "加息券"=>'ywc13',
                "定向用户投资券"=>'ywc14',
                "风车用户统计"=>'ywc15',
                "融普惠用户统计"=>'ywc24',
                "普通标返佣统计"=>'ywc16',
                "债转标返佣统计"=>'ywc17',
                "提前还款"=>'ywc18',
                "投资统计"=>'ywc19',
                "回款统计"=>'ywc20',
                "未实名明细"=>'ywc21',
                "未投资明细"=>'ywc22',
                "站岗自己"=>'ywc23',
                '追梦活动'=>'ywc29',
                '奖品配置'=>'ywc25',
                '中奖信息'=>'ywc26',
                '梦想种子充值'=>'ywc27',
                '手动开奖'=>'ywc28',
                '开奖剩余未满标'=>'ywc30',
                '异常检查'=>'ywc31',
                '推广链接统计'=>'ywc33',
                'CPS用户统计'=>'ywc32',
                '接盘侠'    => 'ywc34',
                '推广链接统计'=>'ywc33',
                '被推荐人返佣统计'=>'ywc34',
                '长期返利明细统计'=>'ywc35',
                '邀请人数'=>'tmyqrs',
                '抽奖结果'=>'tmcjjg',
                '奖品概率设置'=>'tmjpsz',
                '二重礼奖品'=>'tmsp',
                '返现操作'=>'tmsmy',
                '活动异常用户处理'=>'tmfu',
                '车轮用户统计' => 'ywc36'
                ),
                "data"=>array(
                    "eqaction_common1"=>"ywc1",
                    "eqaction_activity38"=>"ywc2",
                    "eqaction_friend"=>"ywc3",
                    "eqaction_registerlist"=>"ywc3",
                    "eqaction_reallist"=>"ywc3",
                    "eqaction_recommendinvest"=>"ywc3",
                    "eqaction_selfinvest"=>"ywc3",
                    'eqaction_friend_invest'=>"ywc4",
                    'eqaction_statistics'=>"ywc5",
                    'eqaction_olympicactivity'=>"ywc6",
                    'eqaction_investcount'=>'ywc7',
                    'eqaction_risklist'=>'ywc8',
                    'eqaction_reskproblem'=>'ywc9',
                    'eqaction_editproblem'=>'ywc9',
                    'eqaction_doedit'=>'ywc9',
                    'eqaction_delproblem'=>'ywc9',
                    'eqaction_zhounian'=>'ywc10',
                    'eqaction_generalcount'=>'ywc11',
                    'eqaction_countdetail'=>'ywc11',
                    'eqaction_sendtouziquan'=>'ywc12',
                    'eqaction_dotouziquan'=>'ywc12',
                    'eqaction_sendjiaxiquan'=>'ywc13',
                    'eqaction_dojiaxiquan'=>'ywc13',
                    'eqaction_dingxiang'=>'ywc14',
                    'eqaction_dingxiangedit'=>'ywc14',
                    'eqaction_editdxstatus'=>'ywc14',
                    'eqaction_fengche'=>'ywc15',
                    'eqaction_ordinarycommission'=>'ywc16',
                    'eqaction_zhaiquancommission'=>'ywc17',
                    'eqaction_repaymentinadvance'=>'ywc18',
                    'eqaction_recommentinvestlist'=>'ywc19',
                    'eqaction_recommentpaymentlist'=>'ywc20',
                    'eqaction_norealnameverify'=>'ywc21',
                    'eqaction_noinvestlist'=>'ywc22',
                    'eqaction_stillmoney'=>'ywc23',

                    'eqaction_rphstatistics'=>'ywc24',
                    'eqaction_dream'=>'ywc29',
                    'eqaction_dreamprize'=>'ywc25',
                    'eqaction_dreamwinner'=>'ywc26',
                    'eqaction_dreamfeedscharge'=>'ywc27',
                    'eqaction_revealwinnermanually'=>'ywc28',
                    'eqaction_revealall'=>'ywc30',
                    'eqaction_indicator'=>'ywc31',
                    'eqaction_regsourcecount'=>'ywc33',
                    'eqaction_fubabastatistics'=>'ywc32',
                    'eqaction_dreammobile'=>'ywc34',
                    
                    
                    
                    'eqaction_fubabastatistics'=>'ywc32',
                    'eqaction_outsidecommission'=>'ywc34',
                    'eqaction_recommendcommission'=>'ywc35',
                    'eqaction_themayrecommendcount'=>'tmyqrs',
                    'eqaction_themayprizeresult'=>'tmcjjg',
                    'eqaction_themayprizeset'=>'tmjpsz',
                    'eqaction_themayseconde'=>'tmsp',
                    'eqaction_sendmoney'=>'tmsmy',
                    'eqaction_themayfreezeuser'=>'tmfu',
                    'eqaction_chelun'=>'ywc36',
                )
    );


$i++;
$acl_inc[$i]['low_title'][] = '运营统计';
$acl_inc[$i]['low_leve']['ywagg']=array("运营统计"=>array(
                '单日运营数据'=>'regaggregate',
                '投资数据'=>'investaggregate',
                '各渠道相关数据'=>'cpsaggregate',
                '拉新投资数据'=>'recommendaggregate',
                '充值回款数据'=>'rechargeaggregate',
                '单日标的数据'=>'contractaggregate',

                ),
                "data"=>array(
                    //运营统计标
                    'eqaction_regaggregate'=>'regaggregate',
                    'eqaction_investaggregate'=>'investaggregate',
                    'eqaction_cpsaggregate'=>'cpsaggregate',
                    'eqaction_recommendaggregate'=>'recommendaggregate',
                    'eqaction_rechargeaggregate'=>'rechargeaggregate',
                    'eqaction_contractaggregate'=>'contractaggregate',
                )
    );


$i++;
$acl_inc[$i]['low_title'][] = '8月贺A轮活动';
$acl_inc[$i]['low_leve']['vc']=array("活动---8月贺A轮融资活动"=>array(
                '新人返利'=>'openaccountoffer',
                '老用户抽奖'=>'prizewheel',
                '新老用户推荐'=>'recommend',
                '新老用户兑换'=>'gift',
                ),
                "data"=>array(
                    // 8月贺A轮融资活动
                    'eqaction_openaccountoffer' =>'openaccountoffer',
                    'eqaction_prizewheel'       =>'prizewheel',
                    'eqaction_recommend'        =>'recommend',
                    'eqaction_gift'             =>'gift',
                )
    );

$i++;
$acl_inc[$i]['low_title'][] = '9月活动';
$acl_inc[$i]['low_leve']['pro9']=array("9月活动"=>array(
    '活动设置'=>'config',
    '送现金'=>'nineone',
    '砸冰块'=>'ninetwo',
    '限量加息'=>'ninethree',
    '荣耀之争1'=>'ryone',
    '荣耀之争2'=>'rytwo',
),
    "data"=>array(
        'eqaction_config' =>'config',
        'eqaction_nineone' =>'nineone',
        'eqaction_ninetwo' =>'ninetwo',
        'eqaction_ninethree' =>'ninethree',
        'eqaction_ryone'=>'ryone',
        'eqaction_rytwo'=>'rytwo',
    )
);

$i++;
$acl_inc[$i]['low_title'][] = '9月内部投资返利';
$acl_inc[$i]['low_leve']['inn']=array("9月内部投资返利"=>array(
    '自身'=>'self',
    '推荐'=>'recommend',
),
    "data"=>array(
        'eqaction_self' =>'self',
        'eqaction_recommend' =>'recommend',
    )
);

$i++;
$acl_inc[$i]['low_title'][] = '10月活动';
$acl_inc[$i]['low_leve']['octact']=array("10月活动"=>array(
    '周年庆抢标活动'=>'zhounian17',
    '月底家具大闯关'=>'furniturewin',
),
    "data"=>array(
        'eqaction_zhounian17' =>'zhounian17',
        'eqaction_furniturewin' =>'furniturewin',
    )
);

$i++;
$acl_inc[$i]['low_title'][] = '11月双11活动';
$acl_inc[$i]['low_leve']['huodong201711']=array("11月双11活动"=>array(
    '邀请好友投资返现'=>'index',
    '邀请好友首投返现'=>'firstinvest',
),
    "data"=>array(
        'eqaction_index' =>'index',
        'eqaction_firstinvest' =>'firstinvest',
    )
);

$i++;
$acl_inc[$i]['low_leve']['withdrawlog']= array("提现管理" =>array(
                                                 "列表"       => 'cg2',
                                                 "审核"       => 'cg3',
                                            ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'cg2',
                                                    'eqaction_edit' =>'cg3',
                                                    'eqaction_doedit'  => 'cg3',
                                                    'eqaction_withdraw0'  => 'cg2',//待提现      新增加2012-12-02 fanyelei
                                                    'eqaction_withdraw1'  => 'cg2',//提现处理中  新增加2012-12-02 fanyelei
                                                    'eqaction_withdraw2'  => 'cg2',//提现成功       新增加2012-12-02 fanyelei
                                                    'eqaction_withdraw3'  => 'cg2',//提现失败       新增加2012-12-02 fanyelei

                                                )
                            );
$acl_inc[$i]['low_title'][] = '待提现列表';
$acl_inc[$i]['low_leve']['withdrawlogwait']= array( "待提现列表" =>array(
                                                 "列表"       => 'cg4',
                                                 "审核"       => 'cg5',
                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'cg4',
                                                    'eqaction_edit' =>'cg5',
                                                    'eqaction_doedit'  => 'cg5',
                                            )
                            );
$acl_inc[$i]['low_title'][] = '提现处理中列表';
$acl_inc[$i]['low_leve']['withdrawloging']= array( "提现处理中列表" =>array(
                                                 "列表"       => 'cg6',
                                                 "审核"       => 'cg7',
                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'cg6',
                                                    'eqaction_edit' =>'cg7',
                                                    'eqaction_doedit'  => 'cg7',
                                            )
                            );

$i++;
$acl_inc[$i]['low_title'][] = '文章管理';
$acl_inc[$i]['low_leve']['article']= array( "文章管理" =>array(
                                                 "列表"       => 'at1',
                                                 "添加"       => 'at2',
                                                 "删除"       => 'at3',
                                                 "修改"       => 'at4',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'at1',
                                                    'eqaction_add'  => 'at2',
                                                    'eqaction_doadd'  => 'at2',
                                                    'eqaction_dodel'  => 'at3',
                                                    'eqaction_edit'  => 'at4',
                                                    'eqaction_doedit'  => 'at4',
                                                )
                            );
$acl_inc[$i]['low_leve']['acategory']= array("文章分类" =>array(
                                                 "列表"       => 'act1',
                                                 "添加"       => 'act2',
                                                 "批量添加"     => 'act5',
                                                 "删除"       => 'act3',
                                                 "修改"       => 'act4',
                                            ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'act1',
                                                    'eqaction_listtype'  => 'act1',
                                                    'eqaction_add'  => 'act2',
                                                    'eqaction_doadd'  => 'act2',
                                                    'eqaction_dodel'  => 'act3',
                                                    'eqaction_edit'  => 'act4',
                                                    'eqaction_doedit'  => 'act4',
                                                    'eqaction_addmultiple'  => 'act5',
                                                    'eqaction_doaddmul'  => 'act5',
                                                )
                            );
$i++;
$acl_inc[$i]['low_title'][] = '导航菜单管理';
$acl_inc[$i]['low_leve']['navigation']= array("导航菜单" =>array(
                                                 "列表"      => 'nav1',
                                                 "添加"       => 'nav2',
                                                 "批量添加"     => 'nav5',
                                                 "删除"       => 'nav3',
                                                 "修改"       => 'nav4',
                                            ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'nav1',
                                                    'eqaction_listtype'  => 'nav1',
                                                    'eqaction_add'  => 'nav2',
                                                    'eqaction_doadd'  => 'nav2',
                                                    'eqaction_dodel'  => 'nav3',
                                                    'eqaction_edit'  => 'nav4',
                                                    'eqaction_doedit'  => 'nav4',
                                                    'eqaction_addmultiple'  => 'nav5',
                                                    'eqaction_doaddmul'  => 'nav5',
                                                )
                            );
$i++;
$acl_inc[$i]['low_title'][] = '快捷借款管理';
$acl_inc[$i]['low_leve']['feedback']= array( "快捷借款管理" =>array(
                                                 "列表"       => 'msg1',
                                                 "查看"       => 'msg2',
                                                 "删除"       => 'msg3',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'msg1',
                                                    'eqaction_dodel'  => 'msg3',
                                                    'eqaction_edit'  => 'msg2',
                                                )
                            );
$i++;
$acl_inc[$i]['low_title'][] = '资金统计';
$acl_inc[$i]['low_leve']['capitalaccount']= array( "会员帐户" =>array(
                                                 "列表"       => 'capital_1',
                                                 "导出"       => 'capital_2',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'capital_1',
                                                    'eqaction_export'  => 'capital_2',
                                                )
                            );
$acl_inc[$i]['low_leve']['capitalrepay']= array("待还资金统计" =>array(
                                                 "查看"       => 'capitalrepay_1',
                                                 "导出"       => 'capitalrepay_2',
                                                ),
                                               "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'capitalrepay_1',
                                                    'eqaction_export'  => 'capitalrepay_2',
                                                )
                            );
$acl_inc[$i]['low_leve']['capitalonline']= array("充值记录" =>array(
                                                 "列表"       => 'capital_3',
                                                 "导出"       => 'capital_4',
                                                ),
                                               "提现记录" =>array(
                                                     "列表"       => 'capital_5',
                                                     "导出"       => 'capital_6',
                                                ),
                                               "data" => array(
                                                    //网站设置
                                                    'eqaction_charge'  => 'capital_3',
                                                    'eqaction_withdraw'  => 'capital_5',
                                                    'eqaction_chargeexport'  => 'capital_4',
                                                    'eqaction_withdrawexport'  => 'capital_6',
                                                )
                            );
$acl_inc[$i]['low_leve']['remark']= array( "备注信息" =>array(
                                                 "列表"       => 'rm1',
                                                 "增加"       => 'rm2',
                                                 "修改"       => 'rm3',
                                                 "跟踪列表"     => 'rm4',
                                                 "跟踪增加"     => 'rm5',
                                                 "跟踪修改"     => 'rm6',
                                                ),
                                       "data" => array(
                                                'eqaction_index'    => 'rm1',
                                                'eqaction_add'      => 'rm2',
                                                'eqaction_doadd'    => 'rm2',
                                                'eqaction_edit'     => 'rm3',
                                                'eqaction_doedit'   => 'rm3',
                                                'eqaction_gzindex'  => 'rm4',
                                                'eqaction_gzedit'   => 'rm5',
                                                'eqaction_gzdoedit' => 'rm6',
                                            )
                            );
$acl_inc[$i]['low_leve']['capitaldetail']= array("会员资金记录" =>array(
                                                 "列表"       => 'capital_7',
                                                 "导出"       => 'capital_8',
                                                ),
                                               "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'capital_7',
                                                    'eqaction_export'  => 'capital_8',
                                                )
                            );
$acl_inc[$i]['low_leve']['capitalall']= array("网站资金统计" =>array(
                                                 "查看"       => 'capital_9',
                                                ),
                                               "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'capital_9',
                                                )
                            );
//权限管理
$i++;
$acl_inc[$i]['low_title'][] = '权限管理';
$acl_inc[$i]['low_leve']['acl']= array( "权限管理" =>array(
                                                 "列表"       => 'at73',
                                                 "增加"       => 'at74',
                                                 "删除"       => 'at75',
                                                 "修改"       => 'at76',
                                                ),
                                           "data" => array(
                                                //权限管理
                                                'eqaction_index'  => 'at73',
                                                'eqaction_doadd'    => 'at74',
                                                'eqaction_add'    => 'at74',
                                                'eqaction_dodelete'    => 'at75',
                                                'eqaction_doedit'   => 'at76',
                                                'eqaction_edit'     => 'at76',
                                            )
                            );
//管理员管理
$i++;
$acl_inc[$i]['low_title'][] = '管理员管理';
$acl_inc[$i]['low_leve']['adminuser']= array( "管理员管理" =>array(
                                                 "列表"       => 'at77',
                                                 "增加"       => 'at78',
                                                 "删除"       => 'at79',
                                                 "上传头像" => 'at99',
                                                 "修改"       => 'at80',
                                                ),
                                              "data" => array(
                                                //权限管理
                                                'eqaction_index'  => 'at77',
                                                'eqaction_dodelete'    => 'at79',
                                                'eqaction_header'    => 'at99',
                                                'eqaction_memberheaderuplad'    => 'at99',
                                                'eqaction_addadmin' =>array(
                                                                'at78'=>array(//增加
                                                                    'POST'=>array(
                                                                        "uid"=>'G_NOTSET',
                                                                    ),
                                                                 ),
                                                                'at80'=>array(//修改
                                                                    'POST'=>array(
                                                                        "uid"=>'G_ISSET',
                                                                    ),
                                                                 ),
                                                ),
                                            )
                            );
//权限管理
$i++;
$acl_inc[$i]['low_title'][] = '数据库管理';
$acl_inc[$i]['low_leve']['db']= array( "数据库信息" =>array(
                                                 "查看"       => 'db1',
                                                 "备份"       => 'db2',
                                                 "查看表结构" => 'db3',
                                                ),
                                       "数据库备份管理" =>array(
                                             "备份列表"         => 'db4',
                                             "删除备份"         => 'db5',
                                             "恢复备份"         => 'db6',
                                             "打包下载"         => 'db7',
                                        ),
                                       "清空数据" =>array(
                                             "清空数据"         => 'db8',
                                        ),
                                           "data" => array(
                                                //权限管理
                                                'eqaction_index'  => 'db1',
                                                'eqaction_set'  => 'db2',
                                                'eqaction_backup'  => 'db2',
                                                'eqaction_showtable'  => 'db3',
                                                'eqaction_baklist'  => 'db4',
                                                'eqaction_delbak'  => 'db5',
                                                'eqaction_restore'  => 'db6',
                                                'eqaction_dozip'  => 'db7',
                                                'eqaction_downzip'  => 'db7',
                                                'eqaction_truncate'  => 'db8',
                                            )
                            );
$i++;
$acl_inc[$i]['low_title'][] = '图片上传';
$acl_inc[$i]['low_leve']['kissy']= array( "图片上传" =>array(
                                                 "图片上传"         => 'at81',
                                                ),
                                              "data" => array(
                                                //权限管理
                                                'eqaction_index'  => 'at81',
                                              )
                            );

$i++;
$acl_inc[$i]['low_title'][] = '投资券';
$acl_inc[$i]['low_leve']['experience']= array( "投资券管理" =>array(
    "投资券管理"         => 'ag81',
),
    "data" => array(
        'eqaction_index' => 'ag81',
    )
);

$i++;
$acl_inc[$i]['low_title'][] = '营销活动';
$acl_inc[$i]['low_leve']['marketactive']= array( "投资活动" =>array(
    "赠送投资券"         => 'mkt',
    "礼品赠送"          => 'mkt1',
    /* "双旦活动"           => 'mkt2',
    "活动奖品设置"            => 'mkt3', */
),
    "data" => array(
        'eqaction_givecoupons' => 'mkt',
        'eqaction_send' => 'mkt',
        'eqaction_giftgive'  => 'mkt1',
        'eqaction_editgift'  => 'mkt1',
        'eqaction_doeditgift'  => 'mkt1',
        /* 'eqaction_index'  => 'mkt2',
        'eqaction_giftsetting'  => 'mkt3', */
    )
);
$acl_inc[$i]['low_leve']['doubleactivity']= array( "双旦活动" =>array(
    "双旦活动"          => 'mkt2',
    "活动奖品设置"            => 'mkt3',
),
    "data" => array(
        'eqaction_index'  => 'mkt2',
        'eqaction_shuangdanfan'  => 'mkt2',
        'eqaction_giftsetting'  => 'mkt3',
        'eqaction_changestatus'  => 'mkt3',
    )
);
$acl_inc[$i]['low_leve']['wbtuijian']= array( "外部推荐活动" =>array(
    "外部推荐统计"            => 'mkt4',
),
    "data" => array(
        'eqaction_index'  => 'mkt4',
        'eqaction_fafang'  => 'mkt4',
    )
);

$i++;
$acl_inc[$i]['low_title'][] = '扩展管理';
$acl_inc[$i]['low_leve']['scan']= array( "安全检测" =>array(
                                                 "安全检测"         => 'scan1',
                                                ),
                                                 "data" => array(
                                                   //权限管理
                                                'eqaction_index'  => 'scan1',
                                                'eqaction_scancom'=>'scan1',
                                                'eqaction_updateconfig'=>'scan1',
                                                'eqaction_filefilter'  => 'scan1',
                                                'eqaction_filefunc' =>'scan1',
                                                'eqaction_filecode' =>'scan1',
                                                'eqaction_scanreport'=>'scan1',
                                                'eqaction_view'=>'scan1',
                                              )
                            );
$acl_inc[$i]['low_leve']['mfields']= array( "文件管理" =>array(
                                                 "文件管理"         => 'at82',
                                                 "空间检查"                 =>'at83',
                                                ),
                                              "data" => array(
                                                //文件管理
                                                'eqaction_index'  => 'at82',
                                                'eqaction_checksize'  => 'at83',
                                              )
                            );

$acl_inc[$i]['low_leve']['bconfig']= array( "业务参数管理" =>array(
                                                 "查看"       => 'fb1',
                                                 "修改"       => 'fb2',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'fb1',
                                                'eqaction_save'    => 'fb2',
                                            )
                            );

$acl_inc[$i]['low_leve']['debtsetting']= array( "债权转让参数管理" =>array(
    "修改"        => 'kb9',
    ),
    "data" => array(
        'eqaction_index'  => 'kb9',
    )
);
$acl_inc[$i]['low_leve']['leve']= array( "信用级别管理" =>array(
                                                 "查看"       => 'jb1',
                                                 "修改"       => 'jb2',
                                                ),
                                         "投资级别管理" =>array(
                                                 "查看"       => 'jb3',
                                                 "修改"       => 'jb4',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'jb1',
                                                'eqaction_save'    => 'jb2',
                                                'eqaction_invest'    => 'jb3',
                                                'eqaction_investsave'  => 'jb4',
                                            )
                            );
$acl_inc[$i]['low_leve']['age']= array( "会员年龄别称" =>array(
                                                 "查看"       => 'bc1',
                                                 "修改"       => 'bc2',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'bc1',
                                                'eqaction_save'    => 'bc2',
                                            )
                            );
$acl_inc[$i]['low_leve']['hetong']= array( "合同居间方资料上传管理" =>array(
                                                 "查看"       => 'ht1',
                                                 "上传"           =>  'ht2',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'ht1',
                                                'eqaction_upload'  =>'ht2',
                                            )
                            );
$acl_inc[$i]['low_title'][] = '在线客服管理';
$acl_inc[$i]['low_leve']['qq']= array("QQ客服管理" =>array(
                                                 "列表"       => 'qq5',
                                                 "增加"       => 'qq6',
                                                 "删除"       => 'qq7',

                                                ),
                                      "QQ群管理" =>array(
                                                 "列表"       => 'qun5',
                                                 "增加"       => 'qun6',
                                                 "删除"       => 'qun7',

                                                ),
                                      "客服电话管理" =>array(
                                                 "列表"       => 'tel5',
                                                 "增加"       => 'tel6',
                                                 "删除"       => 'tel7',

                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'   => 'qq5',
                                                'eqaction_addqq'   => 'qq6',
                                                'eqaction_dodeleteqq'    => 'qq7',
                                                'eqaction_qun'   => 'qun5',
                                                'eqaction_addqun'   => 'qun6',
                                                'eqaction_dodeletequn'    => 'qun7',
                                                'eqaction_tel'   => 'tel5',
                                                'eqaction_addtel'   => 'tel6',
                                                'eqaction_dodeletetel'    => 'tel7',

                                            )
                            );

//$acl_inc[$i]['low_title'][] = '在线通知管理';
$acl_inc[$i]['low_leve']['payonline']= array( "线上支付接口管理" =>array(
                                                 "查看"       => 'jk1',
                                                 "修改"       => 'jk2',
                                                ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'jk1',
                                                'eqaction_save'    => 'jk2',
                                            )
                            );
$acl_inc[$i]['low_leve']['payoffline']= array( "线下充值银行管理" =>array(
                                                 "查看"       => 'offline1',
                                                 "修改"       => 'offline2',
                                                ),
                                           "data" => array(
                                                    //网站设置
                                                    'eqaction_index'  => 'offline1',
                                                    'eqaction_saveconfig' => 'offline2',
                                                )
                            );
$acl_inc[$i]['low_leve']['msgonline']= array( "通知信息接口管理" =>array(
                                                 "查看"       => 'jk3',
                                                 "修改"       => 'jk4',
                                                ),
                                             "通知信息模板管理" =>array(
                                                 "查看"       => 'jk5',
                                                 "修改"       => 'jk6',
                                            ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'jk3',
                                                'eqaction_save'    => 'jk4',
                                                'eqaction_templet'  => 'jk5',
                                                'eqaction_templetsave'    => 'jk6',
                                            )
                            );

$acl_inc[$i]['low_leve']['sinasftp']= array( "对账管理" =>array(
                                                 "手动对账"         => 'jk3',
                                                ),
                                       "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'jk3',
                                                'eqaction_execut'    => 'jk3',
                                            )
                            );
$acl_inc[$i]['low_leve']['baidupush']= array( "百度云推送" =>array(
                                                 "首页"       => 'bd27',
                                                 "消息推送"     => 'bd26',
                                             ),
                                           "data" => array(
                                                //网站设置
                                                'eqaction_index'  => 'bd27',
                                                'eqaction_push_message_android'=>'bd26',
                                            )
                            );
$i++;
$acl_inc[$i]['low_leve']['']='手机操作';
$acl_inc[$i]['low_leve']['mborrow']=array('手机操作'=>array(
                                                '复审列表'=>'sh1',
                                                '复审投资'=>'sh2',
                                                '复审'=>'sh3',
                                                '初审列表'=>'sh4',
                                                '初审投资'=>'sh5',
                                                '初审'=>'sh6',
                                                 '获取验证码'=>'sh7',
                                                 '申请还款列表'=>'sh8',
                                                 '申请还款详情'=>'sh9',
                                                 '申请还款审核'=>'sh10',
                                            ),
                                            "data"=>array(
                                                "eqaction_waitverify2"=>"sh1",
                                                "eqaction_edit"=>"sh2",
                                                "eqaction_doeditwaitverify2"=>"sh3",
                                                "eqaction_waitverify"=>"sh4",
                                                "eqaction_edit0"=>"sh5",
                                                "eqaction_doeditwaitverify"=>"sh6",
                                                "eqaction_code"=>"sh7",
                                                "eqaction_waitverify3"=>"sh8",
                                                "eqaction_edit3"=>"sh9",
                                                "eqaction_ajaxedit3"=>"sh10",

                                            )
                                        );
$i++;
$acl_inc[$i]['low_title'][] = '功能管理';
$acl_inc[$i]['low_leve']['autoborrow']= array( "功能管理" =>array(
    "自动投标功能"        => 'ab',
),
    "data" => array(
        'eqaction_index' => 'ab',
        'eqaction_setAutoBorrow' => 'ab',
                                        )
);
?>