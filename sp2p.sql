/*
Navicat MySQL Data Transfer

Source Server         : 42
Source Server Version : 50626
Source Host           : 172.16.20.42:3306
Source Database       : sp2p

Target Server Type    : MYSQL
Target Server Version : 50626
File Encoding         : 65001

Date: 2017-12-28 11:25:27
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for foo
-- ----------------------------
DROP TABLE IF EXISTS `foo`;
CREATE TABLE `foo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `val` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_acl
-- ----------------------------
DROP TABLE IF EXISTS `lzh_acl`;
CREATE TABLE `lzh_acl` (
  `controller` longtext COLLATE utf8_unicode_ci,
  `group_id` int(10) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lzh_activity
-- ----------------------------
DROP TABLE IF EXISTS `lzh_activity`;
CREATE TABLE `lzh_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户编号',
  `uname` varchar(12) DEFAULT NULL COMMENT '用户名称',
  `phone` char(11) NOT NULL COMMENT '手机号',
  `registertime` date NOT NULL COMMENT '注册时间',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活动期间累计投资',
  `priceid` int(10) DEFAULT NULL COMMENT '奖品编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='周年庆活动';

-- ----------------------------
-- Table structure for lzh_activity_price
-- ----------------------------
DROP TABLE IF EXISTS `lzh_activity_price`;
CREATE TABLE `lzh_activity_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `goodsname` varchar(30) NOT NULL COMMENT '周年庆奖品名称',
  `mark` varchar(100) DEFAULT NULL COMMENT '奖品备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='周年庆奖品';

-- ----------------------------
-- Table structure for lzh_activity_rule
-- ----------------------------
DROP TABLE IF EXISTS `lzh_activity_rule`;
CREATE TABLE `lzh_activity_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `money` decimal(10,2) NOT NULL COMMENT '累计投资，万元为单位',
  `goodsid` int(11) unsigned NOT NULL COMMENT '奖品编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='周年庆活动奖励规则表';

-- ----------------------------
-- Table structure for lzh_ad
-- ----------------------------
DROP TABLE IF EXISTS `lzh_ad`;
CREATE TABLE `lzh_ad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(5000) NOT NULL,
  `start_time` int(10) NOT NULL,
  `end_time` int(10) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  `ad_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_allwood_ljs
-- ----------------------------
DROP TABLE IF EXISTS `lzh_allwood_ljs`;
CREATE TABLE `lzh_allwood_ljs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '标号',
  `allwood_orderno` varchar(100) NOT NULL COMMENT '全木行订单号',
  `fee` decimal(15,2) NOT NULL COMMENT '每期服务费',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=836 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_ancun_investrecord
-- ----------------------------
DROP TABLE IF EXISTS `lzh_ancun_investrecord`;
CREATE TABLE `lzh_ancun_investrecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `bid` int(11) DEFAULT NULL,
  `invest_recordNo` varchar(50) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2460 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_ancun_userrecord
-- ----------------------------
DROP TABLE IF EXISTS `lzh_ancun_userrecord`;
CREATE TABLE `lzh_ancun_userrecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `type` int(11) DEFAULT NULL COMMENT '1:用户信息保全2:充值数据保全3:提现数据保全',
  `recordNo` varchar(50) DEFAULT NULL COMMENT '保全号',
  `add_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3762 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_apr_bean
-- ----------------------------
DROP TABLE IF EXISTS `lzh_apr_bean`;
CREATE TABLE `lzh_apr_bean` (
  `uid` int(11) NOT NULL COMMENT '用编号',
  `user_name` varchar(20) NOT NULL COMMENT '用户名',
  `user_phone` char(11) NOT NULL COMMENT '用户手机号',
  `beancount` int(5) unsigned NOT NULL COMMENT '快乐豆数量',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_apr_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_apr_info`;
CREATE TABLE `lzh_apr_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `reg_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `user_phone` char(11) NOT NULL COMMENT '手机号',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='中奖的人员信息，每个人一条记录';

-- ----------------------------
-- Table structure for lzh_apr_prize
-- ----------------------------
DROP TABLE IF EXISTS `lzh_apr_prize`;
CREATE TABLE `lzh_apr_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '奖品总数量',
  `left` int(11) NOT NULL DEFAULT '0' COMMENT '奖品剩余数量',
  `info` varchar(20) NOT NULL DEFAULT '0' COMMENT '奖品名称',
  `mark` varchar(50) DEFAULT NULL COMMENT '奖品备注',
  `type` tinyint(1) NOT NULL COMMENT '奖品类型 0 投资券 1 快乐豆 2 体验金  3现金 4 实物',
  `value` decimal(6,2) NOT NULL COMMENT '对应奖品的价值或者数量',
  `odds` decimal(4,2) unsigned NOT NULL COMMENT '中奖概率',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 启用  2 不使用',
  `minnum` int(6) unsigned NOT NULL COMMENT '随机数隐射概率的区间下限',
  `maxnum` int(6) unsigned NOT NULL COMMENT '随机数隐射概率的区间上限',
  `angel` int(3) DEFAULT NULL COMMENT '所转的角度',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='奖品库存表';

-- ----------------------------
-- Table structure for lzh_apr_zhongjiang
-- ----------------------------
DROP TABLE IF EXISTS `lzh_apr_zhongjiang`;
CREATE TABLE `lzh_apr_zhongjiang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) unsigned NOT NULL COMMENT '中奖人编号',
  `time` datetime NOT NULL COMMENT '中奖时间',
  `goodsid` int(11) unsigned NOT NULL COMMENT '礼品编号',
  `goodsname` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=972 DEFAULT CHARSET=utf8 COMMENT='中奖表';

-- ----------------------------
-- Table structure for lzh_area
-- ----------------------------
DROP TABLE IF EXISTS `lzh_area`;
CREATE TABLE `lzh_area` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `reid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(120) NOT NULL DEFAULT '',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_open` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`reid`,`sort_order`),
  KEY `is_open` (`is_open`,`domain`,`sort_order`)
) ENGINE=MyISAM AUTO_INCREMENT=3414 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_article
-- ----------------------------
DROP TABLE IF EXISTS `lzh_article`;
CREATE TABLE `lzh_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '文章标题',
  `art_info` varchar(255) NOT NULL COMMENT '文章简介',
  `art_keyword` varchar(200) NOT NULL COMMENT '文章关键字',
  `art_content` text NOT NULL COMMENT '文章内容',
  `art_writer` varchar(20) NOT NULL COMMENT '文章作者',
  `art_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `type_id` smallint(5) unsigned NOT NULL COMMENT '所属栏目编号',
  `art_url` varchar(200) NOT NULL COMMENT '文件名称',
  `art_img` varchar(200) NOT NULL COMMENT '缩略图',
  `art_userid` smallint(5) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL COMMENT '文章顺序',
  `art_click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `art_set` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章属性 0|普通,1|跳转',
  `art_attr` tinyint(4) NOT NULL DEFAULT '0',
  `source` varchar(50) NOT NULL COMMENT '文章来源',
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=239 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_article_area
-- ----------------------------
DROP TABLE IF EXISTS `lzh_article_area`;
CREATE TABLE `lzh_article_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `art_info` varchar(255) NOT NULL,
  `art_keyword` varchar(200) NOT NULL,
  `art_content` text NOT NULL,
  `art_writer` varchar(20) NOT NULL,
  `art_time` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` smallint(5) unsigned NOT NULL,
  `art_url` varchar(200) NOT NULL,
  `art_img` varchar(200) NOT NULL,
  `art_userid` smallint(5) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  `art_click` int(10) unsigned NOT NULL DEFAULT '0',
  `art_set` int(1) unsigned NOT NULL DEFAULT '0',
  `art_attr` tinyint(4) NOT NULL DEFAULT '0',
  `area_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_article_category
-- ----------------------------
DROP TABLE IF EXISTS `lzh_article_category`;
CREATE TABLE `lzh_article_category` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(40) NOT NULL,
  `type_url` varchar(200) NOT NULL,
  `type_keyword` varchar(200) NOT NULL,
  `type_info` varchar(400) NOT NULL,
  `type_content` longtext NOT NULL,
  `sort_order` int(11) NOT NULL,
  `type_set` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` smallint(6) NOT NULL,
  `type_nid` varchar(50) NOT NULL,
  `is_hiden` int(1) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `is_sys` tinyint(3) unsigned NOT NULL,
  `model` char(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_article_category_area
-- ----------------------------
DROP TABLE IF EXISTS `lzh_article_category_area`;
CREATE TABLE `lzh_article_category_area` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(40) NOT NULL,
  `type_url` varchar(200) NOT NULL,
  `type_keyword` varchar(200) NOT NULL,
  `type_info` varchar(400) NOT NULL,
  `type_content` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  `type_set` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` smallint(6) NOT NULL,
  `type_nid` varchar(50) NOT NULL,
  `is_hiden` int(1) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `is_sys` tinyint(3) unsigned NOT NULL,
  `area_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=344 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_auser_dologs
-- ----------------------------
DROP TABLE IF EXISTS `lzh_auser_dologs`;
CREATE TABLE `lzh_auser_dologs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL COMMENT '日志操作类型',
  `tid` int(10) unsigned NOT NULL,
  `tstatus` tinyint(4) unsigned NOT NULL,
  `deal_ip` varchar(16) NOT NULL COMMENT '操作者IP',
  `deal_time` int(10) unsigned NOT NULL COMMENT '操作者时间',
  `deal_user` varchar(50) NOT NULL COMMENT '操作者用户名',
  `deal_info` varchar(200) NOT NULL COMMENT '操作备注',
  PRIMARY KEY (`id`),
  KEY `deal_user` (`deal_user`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=12291 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_ausers
-- ----------------------------
DROP TABLE IF EXISTS `lzh_ausers`;
CREATE TABLE `lzh_ausers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_pass` varchar(50) NOT NULL,
  `u_group_id` smallint(6) NOT NULL,
  `real_name` varchar(20) NOT NULL DEFAULT '匿名',
  `last_log_time` int(10) NOT NULL DEFAULT '0',
  `last_log_ip` varchar(30) NOT NULL DEFAULT '0',
  `is_ban` int(1) NOT NULL DEFAULT '0',
  `area_id` int(11) NOT NULL,
  `area_name` varchar(10) NOT NULL,
  `is_kf` int(10) unsigned NOT NULL DEFAULT '0',
  `qq` varchar(20) NOT NULL COMMENT '管理员qq',
  `phone` varchar(20) NOT NULL COMMENT '客服电话',
  `user_word` varchar(100) NOT NULL COMMENT '密码口令',
  PRIMARY KEY (`id`),
  KEY `is_kf` (`is_kf`,`area_id`)
) ENGINE=MyISAM AUTO_INCREMENT=211 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_auto_borrow
-- ----------------------------
DROP TABLE IF EXISTS `lzh_auto_borrow`;
CREATE TABLE `lzh_auto_borrow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `duration_from` tinyint(3) unsigned NOT NULL,
  `duration_to` tinyint(3) unsigned NOT NULL,
  `account_money` decimal(15,2) NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `is_auto_full` int(11) NOT NULL,
  `invest_money` decimal(15,2) NOT NULL,
  `is_use` tinyint(4) NOT NULL DEFAULT '1',
  `borrow_type` tinyint(4) NOT NULL,
  `min_invest` decimal(15,2) NOT NULL COMMENT '最小投资金额',
  `invest_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `is_use` (`is_use`,`borrow_type`,`end_time`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_auto_temp
-- ----------------------------
DROP TABLE IF EXISTS `lzh_auto_temp`;
CREATE TABLE `lzh_auto_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=214 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_balance_actlog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_balance_actlog`;
CREATE TABLE `lzh_balance_actlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_time` date NOT NULL COMMENT '执行时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_assets
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_assets`;
CREATE TABLE `lzh_borrow_assets` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '产金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COMMENT='产金链标号自增长id记录表';

-- ----------------------------
-- Table structure for lzh_borrow_auto
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_auto`;
CREATE TABLE `lzh_borrow_auto` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `money` decimal(10,2) NOT NULL COMMENT '单笔最低投标额',
  `borrow_type` tinyint(3) NOT NULL COMMENT '投标种类：0不限 8保金链 1质金链 4融金链 6信金链 7优金链',
  `repayment_type` tinyint(3) unsigned NOT NULL COMMENT '还款方式：0不限 1到期还本付息 2等额本息',
  `is_borrow_day` tinyint(3) DEFAULT '0' COMMENT '天标选择0关闭 1开启',
  `day_start` tinyint(3) unsigned DEFAULT NULL COMMENT '天标开始时间',
  `day_end` tinyint(3) unsigned DEFAULT NULL COMMENT '天标结束时间',
  `is_borrow_month` tinyint(3) DEFAULT '0' COMMENT '月标选择0关闭 1开启',
  `month_start` tinyint(3) unsigned DEFAULT NULL COMMENT '月标开始时间',
  `month_end` tinyint(3) unsigned DEFAULT NULL COMMENT '月标结束时间',
  `rate_start` tinyint(3) unsigned DEFAULT NULL COMMENT '年利率开始时间',
  `rate_end` tinyint(3) unsigned DEFAULT NULL COMMENT '年利率结束时间',
  `ticket_type` tinyint(3) DEFAULT '0' COMMENT '使用投资券 1金额大的优先 2即将过期的优先 3不使用',
  `open_type` tinyint(3) DEFAULT '0' COMMENT '自动投标开启状态:0禁用 1已开启 2已保存未开通代扣',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='自动投标设置表';

-- ----------------------------
-- Table structure for lzh_borrow_check_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_check_log`;
CREATE TABLE `lzh_borrow_check_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(10) DEFAULT NULL,
  `deal_user` int(10) DEFAULT NULL,
  `deal_info` varchar(255) DEFAULT NULL,
  `deal_time` int(11) DEFAULT NULL,
  `borrow_add_time` int(11) DEFAULT NULL,
  `operation` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1008 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_confirm
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_confirm`;
CREATE TABLE `lzh_borrow_confirm` (
  `bid` int(11) NOT NULL COMMENT '标号',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '综合服务费金额',
  `fee_status` int(11) NOT NULL DEFAULT '0' COMMENT '综合服务费支付状态',
  `danbao` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '咨询服务费金额',
  `danbao_status` int(11) NOT NULL DEFAULT '0' COMMENT '咨询服务费支付状态',
  `danbao_id` int(11) DEFAULT '0' COMMENT '担保机构UID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='综合服务费\r\n';

-- ----------------------------
-- Table structure for lzh_borrow_credit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_credit`;
CREATE TABLE `lzh_borrow_credit` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '信金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8 COMMENT='信金链标号自增长记录表';

-- ----------------------------
-- Table structure for lzh_borrow_debt
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_debt`;
CREATE TABLE `lzh_borrow_debt` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `level` tinyint(1) NOT NULL COMMENT '转让层级',
  `borrow_id` int(11) NOT NULL COMMENT '原始普通标号',
  `debt_borrow_uid` int(11) NOT NULL COMMENT '转让人uid',
  `debt_totalmoney` decimal(8,2) NOT NULL COMMENT '转让总金额',
  `debt_endtime` datetime DEFAULT NULL COMMENT '下次转让出去时间',
  `debt_addtime` datetime NOT NULL COMMENT '添加时间',
  `debt_starttime` datetime NOT NULL COMMENT '转让成功开始持有时间',
  `debt_status` tinyint(3) NOT NULL COMMENT '转让状态  0初始化申请 ，1审核通过 ， 2审核否决，3时间过期 ，4主动撤销  ，5未满标撤销，6满标转让通过 7收取手续费已完成',
  `debt_rate` float(4,2) NOT NULL COMMENT '折算率',
  `debt_leftmoney` decimal(8,2) NOT NULL COMMENT '债权剩余金额',
  `debt_expire` datetime NOT NULL COMMENT '债权到期时间',
  `debt_parent_borrow_id` int(11) NOT NULL COMMENT '债权上一级标号',
  `debt_newname` varchar(40) NOT NULL COMMENT '债权新名称',
  `debt_borrow_id` int(11) NOT NULL COMMENT '债权新标号',
  `debt_fee` decimal(6,2) DEFAULT NULL COMMENT '手续费',
  `debt_captial` decimal(8,2) DEFAULT NULL COMMENT '转让本金',
  `debt_price` decimal(8,2) DEFAULT NULL COMMENT '转让价格',
  `invest_id` int(10) DEFAULT NULL COMMENT '上一级投资列表号与lzh_debt_borrow_investor或者lzh_borrow_investor 的ID对应',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COMMENT='债权';

-- ----------------------------
-- Table structure for lzh_borrow_finance
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_finance`;
CREATE TABLE `lzh_borrow_finance` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '融金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8 COMMENT='融金链标号自增长记录表';

-- ----------------------------
-- Table structure for lzh_borrow_guarantee
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_guarantee`;
CREATE TABLE `lzh_borrow_guarantee` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '保金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='保金链标号自增长id记录表';

-- ----------------------------
-- Table structure for lzh_borrow_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_info`;
CREATE TABLE `lzh_borrow_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标号',
  `borrow_name` varchar(50) NOT NULL COMMENT '标题',
  `borrow_uid` int(11) NOT NULL COMMENT '借款人uid',
  `borrow_duration` int(10) unsigned NOT NULL COMMENT '借款时间',
  `borrow_duration_txt` varchar(50) NOT NULL COMMENT '借款时间的文字描述',
  `borrow_money` decimal(15,2) NOT NULL COMMENT '借款金额',
  `borrow_interest` decimal(15,2) NOT NULL COMMENT '借款利息',
  `borrow_interest_rate` decimal(5,2) NOT NULL COMMENT '借款利率',
  `up_rete` decimal(5,2) DEFAULT NULL COMMENT '加息',
  `borrow_fee` decimal(15,2) NOT NULL COMMENT 'NC',
  `has_borrow` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投资者已投金额',
  `borrow_times` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '投资次数',
  `repayment_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'NC',
  `repayment_interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'NC',
  `expired_money` decimal(15,2) NOT NULL DEFAULT '0.00',
  `product_type` tinyint(3) NOT NULL COMMENT '1提单质押(质金链) 2提单转现货(质金链) 3.现货(质金链) 4.生产金融(融金链)  5分期购(分期购)  6 信金链(信金链) 7优金链 8保金链 9.新手标,10:产金链',
  `n_interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'n+n提单利息',
  `n_colligate_fee` decimal(15,2) DEFAULT NULL COMMENT 'n+n综合服务费',
  `colligate_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '综合管理费',
  `repayment_type` tinyint(3) unsigned NOT NULL COMMENT '1 天标 2 按月分期还款 3按季分期还款 4每月还息到期还本 5一次性还款 7 等本降息',
  `borrow_type` tinyint(3) unsigned NOT NULL COMMENT '1.信用标 2担保标 3秒还标',
  `borrow_status` tinyint(3) unsigned NOT NULL COMMENT '0：发标 1：初审失败 2 初审通过 4.满标 5.复审失败 6复审成功7还款完成,8表示定时发标，初审通过，但是还没发标',
  `borrow_use` tinyint(3) unsigned NOT NULL COMMENT '借款用途，1 ： ''短期周转'', 2： ''生意周转'', 3： ''生活周转'', 4 ：''购物消费'', 5 ： ''不提现借款'', 6 ： ''创业借款'', 7 ： ''其它借款''，8：''装修借款''（4：对应乐购分期，8对应：乐装分期）',
  `borrow_use_desc` varchar(48) DEFAULT NULL COMMENT '借款用途文字描述',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '发标时间',
  `collect_day` int(11) unsigned NOT NULL COMMENT '投标有效时间',
  `collect_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投标有效时间搓',
  `full_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '满标时间',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最总还款日',
  `first_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初审时间',
  `second_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复审时间',
  `add_ip` varchar(16) NOT NULL DEFAULT '',
  `borrow_info` text COMMENT '项目阐述',
  `total` tinyint(4) NOT NULL DEFAULT '0',
  `has_pay` tinyint(4) NOT NULL DEFAULT '0',
  `substitute_money` decimal(15,2) NOT NULL DEFAULT '0.00',
  `reward_vouch_rate` float(5,2) NOT NULL DEFAULT '0.00',
  `reward_vouch_money` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `reward_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reward_num` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投资返现，返现率，百分率，比如返现10%，这里填写10',
  `reward_money` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投资返现金额(如10,000  返现率 10%)，此处就是1000',
  `borrow_min` mediumint(8) unsigned NOT NULL COMMENT '最小投资金额',
  `borrow_max` mediumint(8) unsigned NOT NULL COMMENT '最大投资金额',
  `province` int(10) unsigned NOT NULL DEFAULT '0',
  `city` int(10) unsigned NOT NULL DEFAULT '0',
  `area` int(10) unsigned NOT NULL DEFAULT '0',
  `vouch_member` varchar(100) NOT NULL DEFAULT '',
  `has_vouch` decimal(15,2) NOT NULL DEFAULT '0.00',
  `password` char(32) NOT NULL DEFAULT '',
  `is_tuijian` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐0：不推荐；1：推荐',
  `can_auto` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `is_huinong` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否是惠农标 1：是；0：否',
  `updata` varchar(3000) DEFAULT NULL,
  `danbao` int(11) NOT NULL DEFAULT '0' COMMENT '担保公司id',
  `vouch_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '担保金额',
  `money_collect` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '投标待收限制金额，默认为0，即无待收限制',
  `risk_control` varchar(2000) NOT NULL DEFAULT '',
  `warehousing` text NOT NULL COMMENT '入仓单',
  `apply_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 提前还款申请',
  `test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1表示是测试标，前台不显示',
  `jiaxi_rate` decimal(15,2) DEFAULT '0.00' COMMENT '标的加息百分比',
  `is_beginnercontract` tinyint(3) DEFAULT '0' COMMENT '是否新手标0.不是  1.是 默认为0',
  `is_zhounianbiao` int(11) DEFAULT '0' COMMENT '是否未周年标 0 不是 1 是,默认为 0',
  PRIMARY KEY (`id`),
  KEY `borrow_status` (`borrow_status`,`collect_time`,`borrow_interest_rate`,`borrow_money`,`borrow_duration`,`id`),
  KEY `borrow_uid` (`borrow_uid`,`borrow_status`),
  KEY `danbao` (`danbao`)
) ENGINE=MyISAM AUTO_INCREMENT=6387 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for lzh_borrow_info_additional
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_info_additional`;
CREATE TABLE `lzh_borrow_info_additional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '借款id',
  `frist_time` int(11) NOT NULL DEFAULT '0' COMMENT '记入第一次时间,复审时间',
  `frist_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '记录第一次的利率',
  `frist_server` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '记入第一次的服务费',
  `second_time` int(11) NOT NULL DEFAULT '0' COMMENT '记入第二次时间，提单转现货时间',
  `second_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '记入第二次的利率',
  `second_server` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '记入第二次的服务费',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '还款时间',
  `salesman` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0.00' COMMENT '业务员姓名',
  `salesman_phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0.00' COMMENT '业务员手机号码',
  `extra_info` text CHARACTER SET utf8 COLLATE utf8_bin COMMENT '附加信息',
  `apply_info` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '用户提交提现还款，看到的还款信息',
  `start_return_day` int(11) unsigned zerofill DEFAULT '00000000000' COMMENT '用于记录月表开始还款日期。默认复审后，下一个月开始还款',
  `colligate` decimal(15,2) unsigned DEFAULT '0.00' COMMENT '综合服务费',
  `pay_frist` tinyint(1) unsigned zerofill DEFAULT '0' COMMENT '0: 不提前收， 1：提前收',
  `is_tocard` int(1) DEFAULT '0' COMMENT '0:代付余额 1:代付提现卡',
  PRIMARY KEY (`id`),
  KEY `uid` (`bid`)
) ENGINE=MyISAM AUTO_INCREMENT=6368 DEFAULT CHARSET=utf8 COMMENT='borrow_info 补充资料';

-- ----------------------------
-- Table structure for lzh_borrow_info_experience
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_info_experience`;
CREATE TABLE `lzh_borrow_info_experience` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键，自增',
  `borrow_name` varchar(50) NOT NULL COMMENT '新手体验标名称',
  `borrow_duration` tinyint(3) unsigned NOT NULL COMMENT '投资周期',
  `borrow_duration_txt` varchar(50) NOT NULL COMMENT '投资周期中文',
  `borrow_interest_rate` decimal(5,2) NOT NULL COMMENT '投资利率，年化(如投新手体验标的收益为年化12%，此处填12.00）',
  `has_borrow` decimal(15,2) NOT NULL COMMENT '已投资金额',
  `borrow_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已投资次数',
  `add_time` int(10) NOT NULL,
  `borrow_info` text NOT NULL COMMENT '项目阐述',
  `total` tinyint(4) NOT NULL,
  `borrow_min` mediumint(8) unsigned NOT NULL COMMENT '新手体验标起投金额',
  `updata` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_status` (`borrow_interest_rate`,`has_borrow`,`borrow_duration`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='体验标标的信息';

-- ----------------------------
-- Table structure for lzh_borrow_info_lock
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_info_lock`;
CREATE TABLE `lzh_borrow_info_lock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `suo` int(10) NOT NULL COMMENT '用于锁表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6387 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for lzh_borrow_installment
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_installment`;
CREATE TABLE `lzh_borrow_installment` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '分期购',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=768 DEFAULT CHARSET=utf8 COMMENT='分期购标号自增长id记录表';

-- ----------------------------
-- Table structure for lzh_borrow_investor
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_investor`;
CREATE TABLE `lzh_borrow_investor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1:等待复审 2:标未满，返回 3：审核未通过，返回  4：审核通过，还款中  5:正常完成  6:网站代还完成 7：逾期还款',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的ID',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人ID',
  `borrow_uid` int(11) NOT NULL COMMENT '借款人ID',
  `investor_capital` decimal(15,2) NOT NULL COMMENT '充值资金池的投资金额',
  `investor_interest` decimal(15,2) NOT NULL COMMENT '投资利息',
  `receive_capital` decimal(15,2) NOT NULL COMMENT '回款资金存放池的投资金额',
  `receive_interest` decimal(15,2) NOT NULL,
  `substitute_money` decimal(15,2) NOT NULL,
  `expired_money` decimal(15,2) NOT NULL,
  `invest_fee` decimal(15,2) NOT NULL,
  `paid_fee` decimal(15,2) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reward_money` decimal(15,2) NOT NULL COMMENT '投资返现金额',
  `debt_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '债权转让状态：1申请中，2转让中，3已转让',
  `debt_id` int(10) DEFAULT '0' COMMENT '债权标号',
  `debt_uid` int(10) DEFAULT '0' COMMENT '转让人uid',
  `debt_percent` decimal(15,4) DEFAULT '0.0000' COMMENT '转让份额占比',
  PRIMARY KEY (`id`),
  KEY `investor_uid` (`investor_uid`,`status`),
  KEY `borrow_id` (`borrow_id`,`investor_uid`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2792 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_optimal
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_optimal`;
CREATE TABLE `lzh_borrow_optimal` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '优金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8 COMMENT='优金链标号自增长记录表';

-- ----------------------------
-- Table structure for lzh_borrow_pledge
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_pledge`;
CREATE TABLE `lzh_borrow_pledge` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '质金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1296 DEFAULT CHARSET=utf8 COMMENT='质金链标号自增长记录表\r\n';

-- ----------------------------
-- Table structure for lzh_borrow_tip
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_tip`;
CREATE TABLE `lzh_borrow_tip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `borrow_type` tinyint(3) unsigned NOT NULL,
  `duration_from` tinyint(3) unsigned NOT NULL,
  `duration_to` tinyint(3) unsigned NOT NULL,
  `account_money` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_type` (`borrow_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_type
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_type`;
CREATE TABLE `lzh_borrow_type` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_use
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_use`;
CREATE TABLE `lzh_borrow_use` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_verify
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_verify`;
CREATE TABLE `lzh_borrow_verify` (
  `borrow_id` int(11) unsigned NOT NULL,
  `deal_user` mediumint(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(50) NOT NULL,
  `deal_time_2` int(10) unsigned NOT NULL,
  `deal_user_2` mediumint(10) unsigned NOT NULL,
  `deal_info_2` varchar(50) NOT NULL,
  `deal_status` tinyint(3) unsigned NOT NULL,
  `deal_status_2` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`borrow_id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_borrow_vouch
-- ----------------------------
DROP TABLE IF EXISTS `lzh_borrow_vouch`;
CREATE TABLE `lzh_borrow_vouch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrow_id` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `uname` varchar(20) NOT NULL,
  `vouch_money` decimal(15,2) NOT NULL,
  `vouch_reward_rate` decimal(4,2) NOT NULL,
  `vouch_reward_money` decimal(15,2) NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `vouch_time` int(11) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `substitute_money` decimal(15,2) NOT NULL,
  `get_back` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_christmas_gift
-- ----------------------------
DROP TABLE IF EXISTS `lzh_christmas_gift`;
CREATE TABLE `lzh_christmas_gift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gift_set_no` tinyint(4) NOT NULL DEFAULT '0' COMMENT '固定码',
  `gift_no` varchar(50) NOT NULL DEFAULT '' COMMENT '礼物期数',
  `gift_name` varchar(100) NOT NULL DEFAULT '' COMMENT '礼品名称',
  `gift_number` int(11) NOT NULL DEFAULT '0' COMMENT '礼物份数',
  `send_number` int(11) NOT NULL DEFAULT '0' COMMENT '已购买份数',
  `have_uid` int(11) DEFAULT '0' COMMENT '中奖人',
  `have_number` int(50) DEFAULT NULL COMMENT '中奖许愿码',
  `have_poor` int(11) DEFAULT NULL COMMENT '中将差值',
  `avg_number` decimal(15,2) DEFAULT NULL COMMENT '平均值',
  `is_xu` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否为许愿礼品',
  `is_open` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='礼品';

-- ----------------------------
-- Table structure for lzh_christmas_gift_lock
-- ----------------------------
DROP TABLE IF EXISTS `lzh_christmas_gift_lock`;
CREATE TABLE `lzh_christmas_gift_lock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `suo` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_christmas_have
-- ----------------------------
DROP TABLE IF EXISTS `lzh_christmas_have`;
CREATE TABLE `lzh_christmas_have` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `gift_no` int(11) NOT NULL DEFAULT '0' COMMENT '礼品期数',
  `gift_set_no` int(11) DEFAULT '0',
  `gift_id` int(11) NOT NULL DEFAULT '0' COMMENT '中奖礼品',
  `have_number` int(11) DEFAULT NULL COMMENT '中奖码',
  `is_send` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否发送',
  `is_xu` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为许愿',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_christmas_number
-- ----------------------------
DROP TABLE IF EXISTS `lzh_christmas_number`;
CREATE TABLE `lzh_christmas_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gift_id` int(11) NOT NULL COMMENT '礼品ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `xu_number` int(50) NOT NULL COMMENT '许愿码',
  `number_poor` int(11) DEFAULT NULL COMMENT '差值',
  `add_time` int(11) NOT NULL COMMENT '许愿时间',
  `is_zhong` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否中奖',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1031 DEFAULT CHARSET=utf8 COMMENT='许愿码';

-- ----------------------------
-- Table structure for lzh_comment
-- ----------------------------
DROP TABLE IF EXISTS `lzh_comment`;
CREATE TABLE `lzh_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `uname` varchar(20) NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL,
  `comment` varchar(500) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(500) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`tid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_company_profit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_company_profit`;
CREATE TABLE `lzh_company_profit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '标的ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '获利者ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '获利金额',
  `buid` int(11) NOT NULL DEFAULT '0' COMMENT '投资人UID',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '建立时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `investor_id` int(11) NOT NULL COMMENT '投资序列ID',
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `uid` (`uid`),
  KEY `add_time` (`add_time`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB AUTO_INCREMENT=757 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_coupons
-- ----------------------------
DROP TABLE IF EXISTS `lzh_coupons`;
CREATE TABLE `lzh_coupons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_phone` varchar(11) DEFAULT NULL,
  `money` decimal(15,2) NOT NULL COMMENT '投资卷为:抵扣金额，体验券为:实际金额',
  `endtime` int(10) NOT NULL,
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '0:未使用，1:已使用,2：已过期',
  `serial_number` varchar(20) NOT NULL COMMENT '序列号',
  `type` int(2) NOT NULL COMMENT '1:投资券，2:体验券,3:加息券',
  `name` varchar(40) DEFAULT NULL COMMENT '券的名称',
  `addtime` datetime DEFAULT NULL COMMENT '添加日期',
  `isexperience` tinyint(1) unsigned DEFAULT '1' COMMENT '是否是体验标使用1 否  2是',
  `use_money` decimal(15,2) DEFAULT '0.00' COMMENT '投资券使用规则',
  `admin` int(11) DEFAULT '0',
  `admin_name` varchar(100) DEFAULT '',
  `min_investrange` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16943 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_cps_index
-- ----------------------------
DROP TABLE IF EXISTS `lzh_cps_index`;
CREATE TABLE `lzh_cps_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'cps名称,如融普惠,富爸爸',
  `code` varchar(50) NOT NULL COMMENT '英文代码',
  `value` int(11) NOT NULL DEFAULT '0' COMMENT 'cps键值',
  `created_at` int(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_debt_borrow_confirm
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_confirm`;
CREATE TABLE `lzh_debt_borrow_confirm` (
  `bid` int(11) NOT NULL AUTO_INCREMENT COMMENT '标号',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '债权服务费费金额',
  `fee_status` int(11) NOT NULL DEFAULT '0' COMMENT '综合服务费支付状态',
  `add_time` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='债权服务费';

-- ----------------------------
-- Table structure for lzh_debt_borrow_credit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_credit`;
CREATE TABLE `lzh_debt_borrow_credit` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '信金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_debt_borrow_finance
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_finance`;
CREATE TABLE `lzh_debt_borrow_finance` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '融金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_debt_borrow_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_info`;
CREATE TABLE `lzh_debt_borrow_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标号',
  `borrow_name` varchar(50) NOT NULL COMMENT '标题',
  `borrow_uid` int(11) NOT NULL COMMENT '借款人uid',
  `borrow_duration` int(11) DEFAULT NULL COMMENT '天数',
  `borrow_duration_txt` varchar(50) NOT NULL COMMENT '借款时间的文字描述',
  `borrow_money` decimal(15,2) NOT NULL COMMENT '借款金额',
  `has_borrow` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投资者已投金额',
  `borrow_times` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '投资次数',
  `debt_rate` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '折让率',
  `totalmoney` decimal(15,2) NOT NULL COMMENT '债权价值',
  `colligate_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '债权手续费',
  `borrow_status` tinyint(3) unsigned NOT NULL COMMENT '0：发标 1：初审失败 2 初审通过  3未满标流标 4.满标 5.复审失败 6复审成功 7还款完成',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '发标时间',
  `collect_day` tinyint(3) unsigned NOT NULL COMMENT '投标有效时间',
  `collect_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投标有效时间搓',
  `full_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '满标时间',
  `first_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初审时间',
  `second_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复审时间',
  `borrow_min` mediumint(8) unsigned NOT NULL DEFAULT '100' COMMENT '最小投资金额',
  `test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1表示是测试标，前台不显示',
  `old_borrow_id` int(10) NOT NULL COMMENT '原始标号',
  `invest_id` int(10) NOT NULL COMMENT '投标序号',
  `debt_captial` decimal(15,2) NOT NULL COMMENT '转让本金',
  `debt_interest` decimal(15,2) DEFAULT NULL COMMENT '转让利息',
  `pay_fee` int(11) DEFAULT '0' COMMENT '服务费是否支付0:未支付1已支付',
  PRIMARY KEY (`id`),
  KEY `borrow_uid` (`borrow_uid`,`borrow_status`),
  KEY `borrow_status` (`borrow_status`,`collect_time`,`borrow_money`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='债权转让标详情表';

-- ----------------------------
-- Table structure for lzh_debt_borrow_info_lock
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_info_lock`;
CREATE TABLE `lzh_debt_borrow_info_lock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `suo` int(10) NOT NULL COMMENT '用于锁表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='债权转让锁';

-- ----------------------------
-- Table structure for lzh_debt_borrow_investor
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_investor`;
CREATE TABLE `lzh_debt_borrow_investor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的ID',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人ID',
  `borrow_uid` int(11) NOT NULL COMMENT '借款人ID',
  `investor_capital` decimal(15,2) NOT NULL COMMENT '充值资金池的投资金额',
  `debt_percent` decimal(15,2) DEFAULT NULL COMMENT '占比',
  `add_time` int(10) unsigned NOT NULL,
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `investor_uid` (`investor_uid`,`status`),
  KEY `borrow_id` (`borrow_id`,`investor_uid`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='债权记录投资人信息';

-- ----------------------------
-- Table structure for lzh_debt_borrow_optimal
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_optimal`;
CREATE TABLE `lzh_debt_borrow_optimal` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '优金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_debt_borrow_pledge
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_borrow_pledge`;
CREATE TABLE `lzh_debt_borrow_pledge` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '质金链',
  `borrow_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_debt_count
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_count`;
CREATE TABLE `lzh_debt_count` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `adddate` date NOT NULL COMMENT '每日日期',
  `count` int(8) NOT NULL COMMENT '每日转让总笔数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='每日债权转让总笔数';

-- ----------------------------
-- Table structure for lzh_debt_investor_detail
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_investor_detail`;
CREATE TABLE `lzh_debt_investor_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repayment_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '还款时间',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的号',
  `invest_id` int(10) unsigned NOT NULL COMMENT '投资列表号，与lzh_borrow_debt 的ID对应',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人UID',
  `borrow_uid` int(10) unsigned NOT NULL COMMENT '借款人UID',
  `capital` decimal(15,2) NOT NULL COMMENT '投资金额',
  `interest` decimal(15,2) NOT NULL COMMENT '利息',
  `interest_fee` decimal(15,2) NOT NULL COMMENT '利率',
  `status` tinyint(3) unsigned NOT NULL COMMENT '状态',
  `receive_interest` decimal(15,2) NOT NULL,
  `receive_capital` decimal(15,2) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL COMMENT '期数',
  `total` tinyint(3) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL COMMENT '还款日期',
  `expired_money` decimal(15,2) NOT NULL COMMENT '逾期罚息',
  `expired_days` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '逾期天数',
  `call_fee` decimal(5,2) NOT NULL,
  `substitute_money` decimal(15,2) NOT NULL COMMENT '代还款金额',
  `substitute_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代还款时间',
  `debt_borrow_id` int(10) DEFAULT NULL COMMENT '债权标号',
  PRIMARY KEY (`id`),
  KEY `invest_id` (`invest_id`,`status`,`deadline`),
  KEY `borrow_id` (`borrow_id`,`sort_order`,`investor_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8 COMMENT='债权转让临时表记录投资人';

-- ----------------------------
-- Table structure for lzh_debt_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_debt_log`;
CREATE TABLE `lzh_debt_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `debt_borrow_id` int(10) unsigned NOT NULL COMMENT '债权转让标号',
  `info` varchar(256) NOT NULL COMMENT '信息',
  `time` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COMMENT='债权转让日志';

-- ----------------------------
-- Table structure for lzh_detail_actlog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_detail_actlog`;
CREATE TABLE `lzh_detail_actlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_time` date DEFAULT NULL COMMENT '执行时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dict
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dict`;
CREATE TABLE `lzh_dict` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `number` int(4) NOT NULL COMMENT '字典编号',
  `name` varchar(30) NOT NULL COMMENT '字典字段名称',
  `value` varchar(10) NOT NULL COMMENT '字典字段值',
  `mark` char(10) DEFAULT NULL COMMENT '字典字段备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='全局数据字典';

-- ----------------------------
-- Table structure for lzh_distribution
-- ----------------------------
DROP TABLE IF EXISTS `lzh_distribution`;
CREATE TABLE `lzh_distribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_id` int(11) NOT NULL COMMENT '分销员ID',
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT '点击数',
  `customer_cnt` int(11) DEFAULT '0' COMMENT '有效客户数',
  `invest_amount` decimal(15,2) DEFAULT '0.00' COMMENT '投资总金额',
  `estimate_commission` decimal(15,2) DEFAULT '0.00' COMMENT '预估收入',
  `form_1` int(11) DEFAULT '0' COMMENT '第三方',
  `form_2` int(11) DEFAULT '0' COMMENT '链接',
  `form_3` int(11) DEFAULT '0' COMMENT '二维码',
  `cps_date` datetime NOT NULL COMMENT '统计日期',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=439 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_donate
-- ----------------------------
DROP TABLE IF EXISTS `lzh_donate`;
CREATE TABLE `lzh_donate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `age` tinyint(3) unsigned NOT NULL,
  `area_id` tinyint(4) NOT NULL,
  `donate_name` varchar(50) NOT NULL,
  `need_money` int(11) NOT NULL,
  `bank_num` varchar(30) NOT NULL,
  `use` varchar(20) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `address` varchar(150) NOT NULL,
  `bank_address` varchar(150) NOT NULL,
  `idcard` varchar(30) NOT NULL,
  `education` varchar(20) NOT NULL,
  `sex` varchar(5) NOT NULL,
  `zhiwei` varchar(20) NOT NULL,
  `danwei` varchar(60) NOT NULL,
  `qq` varchar(30) NOT NULL,
  `info` text NOT NULL,
  `thumb` varchar(100) NOT NULL,
  `title` varchar(40) NOT NULL,
  `resource` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `use` (`use`,`area_id`,`age`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dream_invest
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dream_invest`;
CREATE TABLE `lzh_dream_invest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) NOT NULL,
  `feeds_amount` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `money` int(11) NOT NULL,
  `qishu` varchar(255) NOT NULL,
  `feed_no` varchar(100) NOT NULL,
  `prize_type` int(11) NOT NULL DEFAULT '0',
  `prize_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=205543 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dream_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dream_log`;
CREATE TABLE `lzh_dream_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(13) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '0  充值　２　投资',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4096 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dream_prize
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dream_prize`;
CREATE TABLE `lzh_dream_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `min_feeds` int(11) NOT NULL DEFAULT '1',
  `total_feeds` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `inventory` int(11) NOT NULL COMMENT '追梦活动奖品表',
  `default` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dream_prizehistory
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dream_prizehistory`;
CREATE TABLE `lzh_dream_prizehistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) NOT NULL,
  `prize_name` varchar(100) NOT NULL,
  `prize_min_feeds` int(11) NOT NULL,
  `prize_total_feeds` int(11) NOT NULL,
  `prize_type` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `feeds_left` int(11) NOT NULL,
  `invest_times` int(11) NOT NULL,
  `qishu` int(11) NOT NULL,
  `status` int(2) NOT NULL DEFAULT '0',
  `luck_no` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1062 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_dream_true
-- ----------------------------
DROP TABLE IF EXISTS `lzh_dream_true`;
CREATE TABLE `lzh_dream_true` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '1213123123',
  `prize_id` int(11) NOT NULL,
  `prize_name` varchar(255) NOT NULL,
  `qishu` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `money` int(11) NOT NULL,
  `feed_no` varchar(255) NOT NULL,
  `create_time` int(13) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_face_apply
-- ----------------------------
DROP TABLE IF EXISTS `lzh_face_apply`;
CREATE TABLE `lzh_face_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `apply_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `deal_user` int(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_feedback
-- ----------------------------
DROP TABLE IF EXISTS `lzh_feedback`;
CREATE TABLE `lzh_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `msg` varchar(500) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_friend
-- ----------------------------
DROP TABLE IF EXISTS `lzh_friend`;
CREATE TABLE `lzh_friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_txt` varchar(50) NOT NULL COMMENT '链接文字',
  `link_href` varchar(500) NOT NULL COMMENT '链接地址',
  `link_img` varchar(100) NOT NULL DEFAULT ' ' COMMENT '链接图片',
  `link_order` int(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `link_type` int(1) NOT NULL DEFAULT '0' COMMENT '显示位置',
  `is_show` int(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `game_name` char(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`)
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_global
-- ----------------------------
DROP TABLE IF EXISTS `lzh_global`;
CREATE TABLE `lzh_global` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `text` text NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT ' ',
  `tip` varchar(200) NOT NULL DEFAULT ' ',
  `order_sn` int(11) NOT NULL DEFAULT '0',
  `code` varchar(20) NOT NULL DEFAULT ' ',
  `is_sys` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_hetong
-- ----------------------------
DROP TABLE IF EXISTS `lzh_hetong`;
CREATE TABLE `lzh_hetong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hetong_img` varchar(500) NOT NULL,
  `thumb_hetong_img` varchar(500) NOT NULL,
  `add_time` int(11) NOT NULL,
  `deal_user` varchar(100) NOT NULL COMMENT '操作人',
  `name` varchar(100) NOT NULL COMMENT '公司名称',
  `dizhi` varchar(200) NOT NULL COMMENT '公司地址',
  `tel` varchar(50) NOT NULL COMMENT '公司电话',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_hongbao
-- ----------------------------
DROP TABLE IF EXISTS `lzh_hongbao`;
CREATE TABLE `lzh_hongbao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `money` decimal(15,2) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL,
  `order_no` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10012 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_huodong
-- ----------------------------
DROP TABLE IF EXISTS `lzh_huodong`;
CREATE TABLE `lzh_huodong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `money` decimal(15,2) NOT NULL COMMENT '投资金额',
  `add_time` int(11) NOT NULL COMMENT '投资时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '判断1W以下红包领取',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=188 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_huodong_201711_count
-- ----------------------------
DROP TABLE IF EXISTS `lzh_huodong_201711_count`;
CREATE TABLE `lzh_huodong_201711_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `bid` int(11) NOT NULL COMMENT '首投标的id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '推荐人用户id',
  `first_invest` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人首投投资金额',
  `invest_total` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人总投资额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `is_released` int(11) NOT NULL DEFAULT '0' COMMENT 'shifoufanxian',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_huodong_201711_detail
-- ----------------------------
DROP TABLE IF EXISTS `lzh_huodong_201711_detail`;
CREATE TABLE `lzh_huodong_201711_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_id` int(11) NOT NULL COMMENT '用户id',
  `invest` int(11) NOT NULL DEFAULT '0' COMMENT '单次投资金额',
  `create_time` int(13) NOT NULL COMMENT '投资时间',
  `rebate` decimal(6,2) NOT NULL COMMENT '本次投资奖励',
  `bid` int(11) NOT NULL COMMENT '本次投标id',
  `days` int(11) NOT NULL COMMENT '本次标的的时间，以日为单位',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_inner_msg
-- ----------------------------
DROP TABLE IF EXISTS `lzh_inner_msg`;
CREATE TABLE `lzh_inner_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `msg` text NOT NULL,
  `send_time` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=6366 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_interval
-- ----------------------------
DROP TABLE IF EXISTS `lzh_interval`;
CREATE TABLE `lzh_interval` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `min` int(10) NOT NULL COMMENT '最小值',
  `max` int(10) NOT NULL COMMENT '最大值',
  `type` int(5) NOT NULL COMMENT '1:风险评估投资者类型',
  `status` int(5) NOT NULL COMMENT '风险评估：1：保守型，2：谨慎型，3：稳健型，4：积极型',
  `time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_invest_aggregate
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invest_aggregate`;
CREATE TABLE `lzh_invest_aggregate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户UID',
  `first_invest_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '首次投资金额',
  `firstmonth_invest_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '首次投资金额',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '首次投资时间',
  `borrow_investor_id` int(11) NOT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经完成',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_invest_credit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invest_credit`;
CREATE TABLE `lzh_invest_credit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `borrow_id` int(10) unsigned NOT NULL,
  `invest_money` decimal(15,2) unsigned NOT NULL,
  `invest_type` tinyint(3) unsigned NOT NULL,
  `duration` tinyint(3) unsigned NOT NULL,
  `get_credit` float(15,2) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_invest_debt
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invest_debt`;
CREATE TABLE `lzh_invest_debt` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `debt_invest_uid` int(11) NOT NULL COMMENT '债权受让人uid',
  `debt_id` int(11) unsigned NOT NULL COMMENT '关联债权转让主键',
  `debt_invest_money` decimal(8,2) NOT NULL COMMENT '投资债权金额',
  `debt_addtime` datetime NOT NULL COMMENT '债权投资添加时间',
  `debt_status` tinyint(1) NOT NULL COMMENT '转让状态 0 失效 1 有效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='债权受让人';

-- ----------------------------
-- Table structure for lzh_invest_detb
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invest_detb`;
CREATE TABLE `lzh_invest_detb` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invest_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '99',
  `sell_uid` int(10) unsigned NOT NULL,
  `transfer_price` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `money` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `period` tinyint(5) unsigned NOT NULL DEFAULT '0',
  `total_period` tinyint(5) unsigned NOT NULL DEFAULT '0',
  `valid` int(10) unsigned NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  `serialid` varchar(15) NOT NULL,
  `cancel_time` int(10) unsigned NOT NULL,
  `cancel_times` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `buy_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `buy_time` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(19) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_invest_profit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invest_profit`;
CREATE TABLE `lzh_invest_profit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '标号',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '返现UID',
  `return_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '返现金额',
  `invest_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '投资金额',
  `return_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0未返现，1已返现',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '建立时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `investor_id` int(11) NOT NULL DEFAULT '0' COMMENT '投资序列ID',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `investor_id` (`investor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_investor_detail
-- ----------------------------
DROP TABLE IF EXISTS `lzh_investor_detail`;
CREATE TABLE `lzh_investor_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repayment_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '还款时间',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的号',
  `invest_id` int(10) unsigned NOT NULL COMMENT '投资列表号，与lzh_borrow_investor 的ID对应',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人UID',
  `borrow_uid` int(10) unsigned NOT NULL COMMENT '借款人UID',
  `capital` decimal(15,2) NOT NULL COMMENT '投资金额',
  `interest` decimal(15,2) NOT NULL COMMENT '利息',
  `interest_fee` decimal(15,2) NOT NULL COMMENT '利率',
  `status` int(3) NOT NULL COMMENT '状态 -1 作废',
  `receive_interest` decimal(15,2) NOT NULL,
  `receive_capital` decimal(15,2) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL COMMENT '期数',
  `total` tinyint(3) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL COMMENT '还款日期',
  `expired_money` decimal(15,2) NOT NULL COMMENT '逾期罚息',
  `expired_days` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '逾期天数',
  `call_fee` decimal(5,2) NOT NULL,
  `substitute_money` decimal(15,2) NOT NULL COMMENT '代还款金额',
  `substitute_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代还款时间',
  `is_debt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是转让标 1 是 0否 默认0',
  `jiaxi_money` decimal(15,2) DEFAULT '0.00' COMMENT '加息金额',
  `jiaxi_rate` decimal(15,2) DEFAULT '0.00' COMMENT '加息利率',
  `debt_borrow_id` int(10) unsigned DEFAULT NULL COMMENT '当前期的债权标号',
  PRIMARY KEY (`id`),
  KEY `invest_id` (`invest_id`,`status`,`deadline`),
  KEY `borrow_id` (`borrow_id`,`sort_order`,`investor_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6059 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_investor_detail_experience
-- ----------------------------
DROP TABLE IF EXISTS `lzh_investor_detail_experience`;
CREATE TABLE `lzh_investor_detail_experience` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repayment_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '还款时间',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的号',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人UID',
  `capital` decimal(15,2) NOT NULL COMMENT '投资金额',
  `interest` decimal(15,2) NOT NULL COMMENT '利息',
  `status` tinyint(3) unsigned NOT NULL COMMENT '状态 1默认 2已还款',
  `deadline` int(10) unsigned NOT NULL COMMENT '还款日期',
  `add_time` int(11) NOT NULL COMMENT '投标时间',
  PRIMARY KEY (`id`),
  KEY `invest_id` (`status`,`deadline`),
  KEY `borrow_id` (`borrow_id`,`investor_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=132 DEFAULT CHARSET=utf8 COMMENT='体验金投标';

-- ----------------------------
-- Table structure for lzh_investor_detail_overdue
-- ----------------------------
DROP TABLE IF EXISTS `lzh_investor_detail_overdue`;
CREATE TABLE `lzh_investor_detail_overdue` (
  `id` int(10) unsigned NOT NULL,
  `repayment_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '还款时间',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标的号',
  `invest_id` int(10) unsigned NOT NULL COMMENT '投资列表号，与lzh_borrow_investor 的ID对应',
  `investor_uid` int(10) unsigned NOT NULL COMMENT '投资人UID',
  `borrow_uid` int(10) unsigned NOT NULL COMMENT '借款人UID',
  `capital` decimal(15,2) NOT NULL COMMENT '投资金额',
  `interest` decimal(15,2) NOT NULL COMMENT '利息',
  `interest_fee` decimal(15,2) NOT NULL COMMENT '利率',
  `status` int(3) NOT NULL COMMENT '状态 0:还未确认通过 1:正常还完 2:提前还款 3:迟还 4:网站代还本金 5:逾期还款 6:逾期未还 7:复审通过，还款中',
  `receive_interest` decimal(15,2) NOT NULL,
  `receive_capital` decimal(15,2) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL COMMENT '期数',
  `total` tinyint(3) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL COMMENT '还款日期',
  `expired_money` decimal(15,2) NOT NULL COMMENT '逾期罚息',
  `expired_days` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '逾期天数',
  `call_fee` decimal(5,2) NOT NULL,
  `substitute_money` decimal(15,2) NOT NULL COMMENT '代还款金额',
  `substitute_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代还款时间',
  `is_debt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是转让标 1 是 0否 默认0',
  `jiaxi_money` decimal(15,2) DEFAULT '0.00' COMMENT '加息金额',
  `jiaxi_rate` decimal(15,2) DEFAULT '0.00' COMMENT '加息利率',
  `debt_borrow_id` int(10) unsigned DEFAULT NULL COMMENT '当前期的债权标号',
  PRIMARY KEY (`id`),
  KEY `invest_id` (`invest_id`,`status`,`deadline`),
  KEY `borrow_id` (`borrow_id`,`sort_order`,`investor_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='借款人还款逾期表';

-- ----------------------------
-- Table structure for lzh_invite_count
-- ----------------------------
DROP TABLE IF EXISTS `lzh_invite_count`;
CREATE TABLE `lzh_invite_count` (
  `uid` int(11) unsigned DEFAULT NULL COMMENT '用户id',
  `invite_count` int(11) unsigned DEFAULT '1' COMMENT '邀请人数',
  `invite_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_java_sina_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_java_sina_log`;
CREATE TABLE `lzh_java_sina_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `param_log` text,
  `before_deal_data` text,
  `result_log` text,
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35760 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_jiekuan
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jiekuan`;
CREATE TABLE `lzh_jiekuan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) DEFAULT NULL COMMENT '用户编号',
  `purpose` varchar(20) DEFAULT NULL COMMENT '借款用途',
  `amount` decimal(8,2) DEFAULT NULL COMMENT '借款金额',
  `deadline` char(15) DEFAULT NULL COMMENT '借款期限名称',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '0 默认 1资料填写完毕 2已处理 3还款已完结 4 作废',
  `user_type` tinyint(1) NOT NULL COMMENT '1个人 2 企业',
  `uname` varchar(20) DEFAULT NULL COMMENT '用户名',
  `phone` char(11) DEFAULT NULL COMMENT '手机号',
  `idcard` varchar(20) DEFAULT NULL COMMENT '身份证号',
  `qudao` varchar(20) DEFAULT NULL COMMENT '借款渠道',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8 COMMENT='借款申请';

-- ----------------------------
-- Table structure for lzh_jiekuan_companyinfo
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jiekuan_companyinfo`;
CREATE TABLE `lzh_jiekuan_companyinfo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(40) DEFAULT NULL COMMENT '公司名称',
  `address` varchar(80) DEFAULT NULL COMMENT '公司地址',
  `license_no` varchar(20) DEFAULT NULL COMMENT '执照号',
  `license_expire_date` varchar(20) DEFAULT NULL COMMENT '执照过期日',
  `license_address` varchar(80) DEFAULT NULL COMMENT '营业执照地址',
  `business_scope` varchar(50) DEFAULT NULL COMMENT '营业范围',
  `summary` varchar(100) DEFAULT NULL COMMENT '企业简介',
  `organization_no` varchar(20) DEFAULT NULL COMMENT '企业组织机构代码',
  `license_pic` varchar(1000) DEFAULT NULL COMMENT '企业资质图片',
  `duration` varchar(40) DEFAULT NULL COMMENT '开户行名称',
  `bank_num` varchar(25) DEFAULT NULL COMMENT '银行账号',
  `addr_province` varchar(20) DEFAULT NULL COMMENT '开户行所在省份',
  `addr_city` varchar(20) DEFAULT NULL COMMENT '开户行所在市区',
  `txt_bankName` varchar(30) DEFAULT NULL COMMENT '所在地区支行名称',
  `legal_person` varchar(15) DEFAULT NULL COMMENT '企业法人名称',
  `telephone` varchar(15) DEFAULT NULL COMMENT '企业电话',
  `legal_person_phone` varchar(15) DEFAULT NULL COMMENT '法人手机号码',
  `email` varchar(25) DEFAULT NULL COMMENT '企业邮箱',
  `cert_no` varchar(20) DEFAULT NULL COMMENT '企业法人证件号码',
  `agent_name` varchar(20) DEFAULT NULL COMMENT '经办人姓名',
  `agent_mobile` varchar(20) DEFAULT NULL COMMENT '经办人手机',
  `alicense_no` varchar(20) DEFAULT NULL COMMENT '经办人身份证',
  `uid` int(11) DEFAULT NULL COMMENT '填写人编号',
  `jiekuan_id` int(11) DEFAULT NULL COMMENT '和lzh_jiekuan关联作为外键',
  `addtime` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_jiekuan_contact
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jiekuan_contact`;
CREATE TABLE `lzh_jiekuan_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '外键关联表lzh_jiekuan_personinfo',
  `sort` tinyint(1) NOT NULL COMMENT '联系人需序号 1 ，2,3',
  `relation` varchar(20) DEFAULT NULL COMMENT '联系人与借款人关系',
  `name` varchar(20) DEFAULT NULL COMMENT '联系人姓名',
  `phone` varchar(11) DEFAULT NULL COMMENT '联系人手机号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8 COMMENT='联系人表和借款通道中个人信息表关联';

-- ----------------------------
-- Table structure for lzh_jiekuan_personinfo
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jiekuan_personinfo`;
CREATE TABLE `lzh_jiekuan_personinfo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户编号',
  `phone` char(11) DEFAULT NULL COMMENT '用户手机号',
  `province` varchar(20) DEFAULT NULL COMMENT '省份',
  `city` varchar(20) DEFAULT NULL COMMENT '市',
  `area` varchar(40) DEFAULT NULL COMMENT '区',
  `work` varchar(50) DEFAULT NULL COMMENT '现工作单位行业',
  `position` varchar(30) DEFAULT NULL COMMENT '职位',
  `work_time` varchar(30) DEFAULT NULL COMMENT '现单位工作年限',
  `income` varchar(30) DEFAULT NULL COMMENT '年收入',
  `idcard` char(20) DEFAULT NULL COMMENT '身份卡号',
  `marray` varchar(10) DEFAULT NULL COMMENT '是否结婚',
  `realname` varchar(30) DEFAULT NULL,
  `id_card_front_pic` varchar(500) DEFAULT NULL,
  `id_card_reverse_pic` varchar(500) DEFAULT NULL,
  `handcard_pic` varchar(500) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '0实名认证  1填写个人信息   2上传征信报告或者上传银行流水 3上传征信报告或者上传银行流水   4完成',
  `bankcard` varchar(20) DEFAULT NULL COMMENT '银行卡号',
  `zhufang` varchar(30) DEFAULT NULL COMMENT '住房情况',
  `xueli` varchar(30) DEFAULT NULL COMMENT '学历',
  `asset` varchar(40) DEFAULT NULL COMMENT '固定资产',
  `zhengxin_pic` text COMMENT '征信报告',
  `bank_state` text COMMENT '银行流水',
  `jiekuan_id` int(11) DEFAULT NULL COMMENT '和lzh_jiekuan关联作为外键',
  `addtime` int(11) DEFAULT NULL COMMENT '添加或者修改时间',
  `now_province` varchar(100) DEFAULT NULL COMMENT '现住址省份',
  `now_city` varchar(20) DEFAULT NULL COMMENT '现住址城市',
  `now_county` varchar(20) DEFAULT NULL COMMENT '现住址区',
  `now_area` varchar(40) DEFAULT NULL COMMENT '现住址详细地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COMMENT='借款通道的个人信息表';

-- ----------------------------
-- Table structure for lzh_jifen_choujiang
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jifen_choujiang`;
CREATE TABLE `lzh_jifen_choujiang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `jp_id` int(11) DEFAULT '0' COMMENT '奖品ID',
  `jp_title` varchar(100) DEFAULT '' COMMENT '奖品名称',
  `process` int(10) DEFAULT '0' COMMENT '0未处理1已处理2测试不用处理',
  `userip` varchar(100) DEFAULT NULL COMMENT '用户IP',
  `addtime` int(11) DEFAULT '0' COMMENT '抽奖时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_jubao
-- ----------------------------
DROP TABLE IF EXISTS `lzh_jubao`;
CREATE TABLE `lzh_jubao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uemail` varchar(60) NOT NULL,
  `b_uid` int(11) NOT NULL,
  `b_uname` varchar(50) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `text` varchar(500) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_kvtable
-- ----------------------------
DROP TABLE IF EXISTS `lzh_kvtable`;
CREATE TABLE `lzh_kvtable` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(50) NOT NULL,
  `nid` varchar(10) NOT NULL,
  `sort_order` int(11) NOT NULL,
  `son_count` int(10) unsigned NOT NULL,
  `field_1` int(10) unsigned NOT NULL,
  `field_2` int(10) unsigned NOT NULL,
  `field_3` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nid` (`nid`,`value`,`sort_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_log_message
-- ----------------------------
DROP TABLE IF EXISTS `lzh_log_message`;
CREATE TABLE `lzh_log_message` (
  `uid` int(11) DEFAULT NULL,
  `balance` varchar(200) DEFAULT NULL,
  `saving` varchar(200) DEFAULT NULL,
  `message` varchar(200) DEFAULT NULL,
  `orderno` varchar(200) DEFAULT NULL,
  `time` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_market_address
-- ----------------------------
DROP TABLE IF EXISTS `lzh_market_address`;
CREATE TABLE `lzh_market_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '收货人ID',
  `proid` int(11) NOT NULL COMMENT '产品ID',
  `province` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '省',
  `city` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '市',
  `area` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '区/街道',
  `address` varchar(300) CHARACTER SET utf8 NOT NULL COMMENT '收货详细地址',
  `remark` text CHARACTER SET utf8 NOT NULL COMMENT '备注',
  `add_ip` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '添加者IP',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for lzh_market_goods
-- ----------------------------
DROP TABLE IF EXISTS `lzh_market_goods`;
CREATE TABLE `lzh_market_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `style` varchar(200) NOT NULL,
  `img` varchar(50) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `small_img` varchar(100) NOT NULL COMMENT '小缩略图地址',
  `middle_img` varchar(100) NOT NULL COMMENT '中图',
  `big_img` varchar(100) CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL COMMENT '商品大图',
  `price` int(10) NOT NULL,
  `cost` int(8) NOT NULL,
  `order_sn` int(8) NOT NULL DEFAULT '0',
  `add_time` int(12) unsigned NOT NULL,
  `is_sys` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `jianjie` text NOT NULL,
  `canshu` text NOT NULL,
  `number` int(10) NOT NULL COMMENT '物品数量',
  `category` tinyint(4) NOT NULL DEFAULT '1' COMMENT '物品类别 1：实物；2：虚拟物品',
  `amount` int(10) NOT NULL COMMENT '限购数量',
  `convert` int(10) NOT NULL COMMENT '已兑换数量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_market_jifenlist
-- ----------------------------
DROP TABLE IF EXISTS `lzh_market_jifenlist`;
CREATE TABLE `lzh_market_jifenlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(10) NOT NULL DEFAULT '2' COMMENT '物品类别 1：金钱；2：积分',
  `title` varchar(100) DEFAULT NULL COMMENT '奖品名称',
  `num` int(10) DEFAULT '0' COMMENT '奖品数量',
  `last_num` int(10) NOT NULL COMMENT '剩余数量',
  `hits` int(10) DEFAULT '0' COMMENT '已中奖次',
  `rate` int(10) DEFAULT '0' COMMENT '中奖机率',
  `value` int(10) NOT NULL COMMENT '可兑换价值',
  `order_sn` int(10) NOT NULL COMMENT '排序',
  `is_sys` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否上线 0：下线；1：上线',
  `add_ip` varchar(16) NOT NULL COMMENT '添加者IP',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `b_img` varchar(200) NOT NULL COMMENT '奖品图片',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_market_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_market_log`;
CREATE TABLE `lzh_market_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `way` int(11) NOT NULL COMMENT '领取方式',
  `gid` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `cost` int(10) unsigned NOT NULL,
  `num` tinyint(4) NOT NULL,
  `style` varchar(50) NOT NULL,
  `info` varchar(200) NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_address
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_address`;
CREATE TABLE `lzh_member_address` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `name` varchar(10) NOT NULL,
  `main_phone` varchar(20) NOT NULL,
  `secondary_phone` varchar(20) NOT NULL,
  `address` varchar(100) NOT NULL,
  `post_code` varchar(10) NOT NULL,
  `address_type` tinyint(4) NOT NULL DEFAULT '0',
  `province` smallint(5) unsigned NOT NULL,
  `city` smallint(5) unsigned NOT NULL,
  `district` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`address_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_apply
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_apply`;
CREATE TABLE `lzh_member_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `apply_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `credit_money` decimal(15,2) NOT NULL,
  `deal_user` int(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(50) NOT NULL,
  `apply_type` tinyint(3) unsigned NOT NULL,
  `apply_money` decimal(15,2) NOT NULL,
  `apply_info` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_banks
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_banks`;
CREATE TABLE `lzh_member_banks` (
  `uid` int(10) unsigned NOT NULL,
  `bank_num` varchar(50) NOT NULL,
  `bank_province` varchar(20) NOT NULL,
  `bank_city` varchar(20) NOT NULL,
  `bank_address` varchar(100) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `companyname` varchar(50) DEFAULT NULL COMMENT '公司名称',
  `card_id` varchar(32) DEFAULT NULL COMMENT '钱包系统卡ID',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_borrow_show
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_borrow_show`;
CREATE TABLE `lzh_member_borrow_show` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `data_url` varchar(100) NOT NULL,
  `data_name` varchar(50) NOT NULL,
  `sort` int(8) unsigned NOT NULL,
  `deal_user` varchar(50) NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_contact_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_contact_info`;
CREATE TABLE `lzh_member_contact_info` (
  `uid` int(10) unsigned NOT NULL,
  `address` varchar(200) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `contact1` varchar(50) NOT NULL,
  `contact1_re` varchar(20) NOT NULL,
  `contact1_tel` varchar(50) NOT NULL,
  `contact2` varchar(50) NOT NULL,
  `contact2_re` varchar(20) NOT NULL,
  `contact2_tel` varchar(20) NOT NULL,
  `contact1_other` varchar(100) NOT NULL,
  `contact2_other` varchar(100) NOT NULL,
  `contact3` varchar(40) DEFAULT NULL,
  `contact3_re` varchar(20) DEFAULT NULL,
  `contact3_tel` varchar(100) DEFAULT NULL,
  `contact3_other` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_creditslog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_creditslog`;
CREATE TABLE `lzh_member_creditslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `affect_credits` mediumint(9) NOT NULL,
  `account_credits` mediumint(9) NOT NULL,
  `info` varchar(50) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2730 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_data_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_data_info`;
CREATE TABLE `lzh_member_data_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `data_url` varchar(100) NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `data_name` varchar(50) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `ext` varchar(10) NOT NULL,
  `deal_info` varchar(40) NOT NULL,
  `deal_credits` smallint(5) unsigned NOT NULL,
  `deal_user` int(11) NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_department_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_department_info`;
CREATE TABLE `lzh_member_department_info` (
  `uid` int(11) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `department_tel` varchar(20) NOT NULL,
  `department_address` varchar(200) NOT NULL,
  `department_year` varchar(20) NOT NULL,
  `voucher_name` varchar(20) NOT NULL,
  `voucher_tel` varchar(20) NOT NULL,
  `institution_code` varchar(100) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_detaillog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_detaillog`;
CREATE TABLE `lzh_member_detaillog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sina_orderno` varchar(50) DEFAULT NULL COMMENT '交易订单号',
  `ccfax_orderno` varchar(50) DEFAULT NULL COMMENT '商户订单号',
  `order_type` varchar(50) DEFAULT NULL COMMENT '业务类型',
  `order_type_1` varchar(50) DEFAULT NULL COMMENT '子类型',
  `sina_payer` varchar(50) DEFAULT NULL COMMENT '支付人',
  `sina_pay_account` varchar(50) DEFAULT NULL COMMENT '支付帐号',
  `order_name` varchar(200) DEFAULT NULL COMMENT '商品名称',
  `order_money` decimal(10,2) DEFAULT NULL COMMENT '订单金额',
  `order_fee` decimal(10,2) DEFAULT NULL COMMENT '手续费',
  `order_tui` decimal(10,2) DEFAULT NULL COMMENT '退款金额',
  `payee_name` varchar(50) DEFAULT NULL COMMENT '收款人',
  `sina_payee_account` varchar(50) DEFAULT NULL COMMENT '收款人帐号',
  `order_status` varchar(50) DEFAULT NULL COMMENT '订单状态',
  `complete_time` datetime DEFAULT NULL COMMENT '交易完成时间',
  `create_time` datetime DEFAULT NULL COMMENT '订单创建时间',
  `pay_channel` varchar(50) DEFAULT NULL COMMENT '支付渠道',
  `pay_equiment` varchar(50) DEFAULT NULL COMMENT '支付终端',
  `order_note` varchar(500) DEFAULT NULL COMMENT '备注',
  `ping_account` varchar(50) DEFAULT NULL COMMENT '平台方',
  `ping_fee` decimal(10,2) DEFAULT NULL COMMENT '平台手续费',
  `pay_uid` varchar(50) DEFAULT NULL COMMENT '付款方',
  `payee_uid` varchar(50) DEFAULT NULL COMMENT '收款方',
  `ccfax_batchno` varchar(50) DEFAULT NULL COMMENT '商户批次号',
  `sina_batchno` varchar(50) DEFAULT NULL COMMENT '交易批次号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_ensure_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_ensure_info`;
CREATE TABLE `lzh_member_ensure_info` (
  `uid` int(11) NOT NULL,
  `ensuer1_name` varchar(20) NOT NULL,
  `ensuer1_re` varchar(20) NOT NULL,
  `ensuer1_tel` varchar(20) NOT NULL,
  `ensuer2_name` varchar(20) NOT NULL,
  `ensuer2_re` varchar(20) NOT NULL,
  `ensuer2_tel` varchar(20) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_financial_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_financial_info`;
CREATE TABLE `lzh_member_financial_info` (
  `uid` int(10) unsigned NOT NULL,
  `fin_monthin` varchar(20) NOT NULL,
  `fin_incomedes` varchar(2000) NOT NULL,
  `fin_monthout` varchar(20) NOT NULL,
  `fin_outdes` varchar(2000) NOT NULL,
  `fin_house` varchar(50) NOT NULL,
  `fin_housevalue` varchar(20) NOT NULL,
  `fin_car` varchar(20) NOT NULL,
  `fin_carvalue` varchar(20) NOT NULL,
  `fin_stockcompany` varchar(50) NOT NULL,
  `fin_stockcompanyvalue` varchar(50) NOT NULL,
  `fin_otheremark` varchar(2000) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_friend
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_friend`;
CREATE TABLE `lzh_member_friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `friend_id` int(10) unsigned NOT NULL,
  `apply_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_genzong
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_genzong`;
CREATE TABLE `lzh_member_genzong` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `remark` varchar(500) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `borrow_id` int(11) NOT NULL COMMENT '标号',
  `remark_type` int(11) NOT NULL DEFAULT '1' COMMENT '类型1提单签收',
  `admin_real_name` varchar(50) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2002 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_house_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_house_info`;
CREATE TABLE `lzh_member_house_info` (
  `uid` int(11) NOT NULL,
  `house_dizhi` varchar(200) NOT NULL,
  `house_mianji` float(10,2) NOT NULL,
  `house_nian` varchar(10) NOT NULL,
  `house_gong` varchar(20) NOT NULL,
  `house_suo1` varchar(20) NOT NULL,
  `house_suo2` varchar(20) NOT NULL,
  `house_feng1` float(10,2) NOT NULL,
  `house_feng2` float(10,2) NOT NULL,
  `house_dai` int(11) NOT NULL,
  `house_yuegong` float(10,2) NOT NULL,
  `house_shangxian` float(10,2) NOT NULL,
  `house_anjiebank` varchar(20) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_info`;
CREATE TABLE `lzh_member_info` (
  `uid` int(10) unsigned NOT NULL,
  `sex` varchar(20) NOT NULL DEFAULT '',
  `zy` varchar(40) NOT NULL DEFAULT '',
  `cell_phone` varchar(11) NOT NULL DEFAULT '',
  `info` varchar(500) NOT NULL DEFAULT '',
  `marry` varchar(20) NOT NULL DEFAULT '',
  `education` varchar(50) NOT NULL DEFAULT '',
  `income` varchar(20) NOT NULL DEFAULT '',
  `age` int(11) NOT NULL DEFAULT '18',
  `idcard` varchar(20) NOT NULL DEFAULT '',
  `card_img` varchar(200) NOT NULL DEFAULT '' COMMENT '身份证正面照',
  `real_name` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(100) NOT NULL DEFAULT '',
  `province` int(11) NOT NULL DEFAULT '0',
  `province_now` int(11) NOT NULL DEFAULT '0',
  `city` int(11) NOT NULL DEFAULT '0',
  `city_now` int(11) NOT NULL DEFAULT '0',
  `area` int(11) NOT NULL DEFAULT '0',
  `area_now` int(11) NOT NULL DEFAULT '0',
  `up_time` int(10) unsigned NOT NULL DEFAULT '0',
  `card_back_img` varchar(200) NOT NULL DEFAULT '' COMMENT '身份证反面照',
  `card_in_hand` varchar(200) DEFAULT '' COMMENT '手持身份证照片',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_integrallog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_integrallog`;
CREATE TABLE `lzh_member_integrallog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `affect_integral` mediumint(9) NOT NULL,
  `active_integral` mediumint(9) NOT NULL,
  `account_integral` mediumint(9) NOT NULL,
  `info` varchar(50) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3365 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_limitlog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_limitlog`;
CREATE TABLE `lzh_member_limitlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `credit_limit` float(15,2) NOT NULL,
  `borrow_vouch_limit` float(15,2) NOT NULL,
  `invest_vouch_limit` float(15,2) NOT NULL,
  `info` varchar(50) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=690 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_login
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_login`;
CREATE TABLE `lzh_member_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13457 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_money
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_money`;
CREATE TABLE `lzh_member_money` (
  `uid` int(10) unsigned NOT NULL,
  `money_freeze` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `money_collect` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '待收金额',
  `account_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '充值资金存放池_可用余额',
  `back_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '回款资金存放池_可用余额',
  `credit_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credit_cuse` decimal(15,2) NOT NULL DEFAULT '0.00',
  `borrow_vouch_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `borrow_vouch_cuse` decimal(15,2) NOT NULL DEFAULT '0.00',
  `invest_vouch_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `invest_vouch_cuse` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_moneyactlog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_moneyactlog`;
CREATE TABLE `lzh_member_moneyactlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT NULL COMMENT '资金变动类型:[1]充值;[2]提现;[3]投标;[4]奖励;[5]还款;[6]付给借款人',
  `all_money` int(11) DEFAULT NULL COMMENT '用户总额',
  `money` int(11) DEFAULT NULL COMMENT '变动金额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `optime` int(11) DEFAULT NULL COMMENT '操作时间',
  `ip` varchar(255) DEFAULT NULL COMMENT '操作ip',
  `status` int(11) DEFAULT '1' COMMENT '状态：[1]发起[2]返回',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11909 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_moneylog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_moneylog`;
CREATE TABLE `lzh_member_moneylog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `affect_money` decimal(15,2) NOT NULL COMMENT '影响金额',
  `account_money` decimal(15,2) NOT NULL COMMENT '充值资金存放池_可用余额',
  `back_money` decimal(15,2) NOT NULL COMMENT '回款资金存放池_可用余额',
  `collect_money` decimal(15,2) NOT NULL COMMENT '待收金额',
  `freeze_money` decimal(15,2) NOT NULL COMMENT '冻结金额',
  `info` varchar(50) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `target_uid` int(11) NOT NULL DEFAULT '0',
  `target_uname` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19327 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_msg
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_msg`;
CREATE TABLE `lzh_member_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL,
  `from_uname` varchar(20) NOT NULL,
  `to_uid` int(11) NOT NULL,
  `to_uname` varchar(20) NOT NULL,
  `title` varchar(50) NOT NULL,
  `msg` varchar(2000) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `is_read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL,
  `to_del` tinyint(4) NOT NULL DEFAULT '0',
  `from_del` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_payonline
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_payonline`;
CREATE TABLE `lzh_member_payonline` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `nid` char(32) NOT NULL,
  `money` decimal(15,2) NOT NULL,
  `fee` decimal(8,2) NOT NULL,
  `way` varchar(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `tran_id` varchar(50) NOT NULL,
  `off_bank` varchar(50) NOT NULL,
  `off_way` varchar(100) NOT NULL,
  `deal_user` varchar(40) NOT NULL,
  `deal_uid` int(11) NOT NULL,
  `payimg` varchar(1000) NOT NULL COMMENT '上传打款凭证',
  `requestId` varchar(50) DEFAULT NULL COMMENT '订单号',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`status`,`nid`,`id`),
  KEY `uid_2` (`uid`,`money`,`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=6554 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_piggbanklog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_piggbanklog`;
CREATE TABLE `lzh_member_piggbanklog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(30) NOT NULL COMMENT '存钱罐操作日志名称',
  `time` int(11) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='存钱罐日志记录时间，防止存钱罐重复下载';

-- ----------------------------
-- Table structure for lzh_member_piggfaillog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_piggfaillog`;
CREATE TABLE `lzh_member_piggfaillog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `content` varchar(100) NOT NULL COMMENT '存钱罐失败原因',
  `status` tinyint(1) NOT NULL COMMENT '状态 0 失败   1记录成功',
  `addtime` date NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_piggybank
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_piggybank`;
CREATE TABLE `lzh_member_piggybank` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `available_balance` decimal(15,2) NOT NULL COMMENT '可用余额',
  `amount_frozen` decimal(15,2) NOT NULL COMMENT '冻结余额',
  `total_balance` decimal(15,2) NOT NULL COMMENT '总余额',
  `earnings_yesterday` decimal(15,2) NOT NULL COMMENT '昨日收益',
  `thirty_earnings` decimal(15,2) NOT NULL COMMENT '30日收益',
  `total_revenue` decimal(15,2) NOT NULL COMMENT '总收益',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_remark
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_remark`;
CREATE TABLE `lzh_member_remark` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `remark` varchar(500) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_real_name` varchar(50) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_safequestion
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_safequestion`;
CREATE TABLE `lzh_member_safequestion` (
  `uid` int(10) unsigned NOT NULL,
  `question1` varchar(100) NOT NULL,
  `answer1` varchar(100) NOT NULL,
  `question2` varchar(100) NOT NULL,
  `answer2` varchar(100) NOT NULL,
  `add_time` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_sina_balance
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_sina_balance`;
CREATE TABLE `lzh_member_sina_balance` (
  `sinauid` varchar(20) NOT NULL,
  `balance` decimal(15,2) NOT NULL COMMENT '可用余额',
  `balance_freeze` decimal(15,2) NOT NULL COMMENT '冻结余额',
  `all_balance` decimal(15,2) NOT NULL COMMENT '总余额',
  `yes_shou` decimal(15,2) NOT NULL COMMENT '昨日收益',
  `thirty_shou` decimal(15,2) NOT NULL COMMENT '30天收益',
  `all_shou` decimal(15,2) NOT NULL COMMENT '总收益',
  PRIMARY KEY (`sinauid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_source
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_source`;
CREATE TABLE `lzh_member_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `source_pt` varchar(50) NOT NULL COMMENT '来源',
  PRIMARY KEY (`id`,`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_member_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `lzh_member_withdraw`;
CREATE TABLE `lzh_member_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `withdraw_money` decimal(15,2) NOT NULL,
  `withdraw_status` tinyint(4) NOT NULL,
  `withdraw_fee` decimal(15,2) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_user` varchar(50) NOT NULL,
  `deal_info` varchar(200) NOT NULL,
  `second_fee` decimal(15,2) NOT NULL COMMENT '修改后的提现手续费',
  `success_money` decimal(15,2) NOT NULL COMMENT '实际到账金额',
  `withdrawid` varchar(20) NOT NULL COMMENT '提现订单号',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`withdraw_status`,`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=758 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members`;
CREATE TABLE `lzh_members` (
  `id` int(10) unsigned NOT NULL,
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `user_pass` char(32) NOT NULL COMMENT '密码',
  `user_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '普通，VIP',
  `user_regtype` tinyint(3) DEFAULT '1' COMMENT '注册类型',
  `pin_pass` char(32) DEFAULT NULL COMMENT '平台支付密码',
  `user_email` varchar(50) DEFAULT NULL COMMENT '用户邮箱',
  `user_phone` varchar(11) NOT NULL COMMENT '手机',
  `reg_time` int(10) unsigned NOT NULL COMMENT '注册时间',
  `reg_ip` varchar(15) DEFAULT NULL COMMENT '注册IP',
  `user_leve` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'NG',
  `time_limit` int(10) unsigned DEFAULT NULL COMMENT 'NG',
  `credits` int(10) DEFAULT NULL COMMENT 'NG',
  `recommend_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐注册人ID',
  `customer_id` int(10) unsigned DEFAULT NULL COMMENT 'NG',
  `customer_name` varchar(20) DEFAULT NULL COMMENT 'NG',
  `province` int(10) unsigned DEFAULT NULL COMMENT '省',
  `city` int(10) unsigned DEFAULT NULL COMMENT '市',
  `area` int(10) unsigned DEFAULT NULL COMMENT '区',
  `is_ban` int(11) NOT NULL DEFAULT '0' COMMENT '是否冻结0：否； 1：是',
  `reward_money` decimal(15,2) DEFAULT NULL COMMENT '奖金金额',
  `is_reward` int(11) NOT NULL DEFAULT '0' COMMENT '是否领取注册奖金0否1是',
  `invest_credits` decimal(15,2) unsigned DEFAULT NULL,
  `integral` int(15) DEFAULT NULL COMMENT '会员总积分',
  `active_integral` int(15) DEFAULT NULL COMMENT '会员活跃积分',
  `is_borrow` int(2) NOT NULL DEFAULT '0' COMMENT '是否允许会员发标。0：不允许；1：允许',
  `is_transfer` int(2) NOT NULL DEFAULT '0' COMMENT '是否是流转会员 0代表非流转会员，1代表是流转会员',
  `is_vip` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否开启特权发标，0：不开启；1：开启',
  `last_log_ip` char(15) DEFAULT NULL COMMENT '最后登录IP',
  `last_log_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `from` tinyint(3) NOT NULL DEFAULT '0' COMMENT '注册来源活动来源',
  `equipment` varchar(10) NOT NULL DEFAULT 'PC' COMMENT '来源设备',
  `argument1` varchar(10) DEFAULT '0' COMMENT '活动来源的第一个参数',
  `user_img` varchar(50) DEFAULT NULL COMMENT '用户头像图片路劲',
  `dream_feeds` int(11) NOT NULL DEFAULT '0',
  `dream_invest_total` int(11) NOT NULL DEFAULT '0',
  `dream_invested` int(11) NOT NULL DEFAULT '0',
  `fubabaid` int(11) NOT NULL DEFAULT '0' COMMENT '富爸爸推荐人id',
  `is_rebate` int(2) NOT NULL DEFAULT '0' COMMENT '是否返利',
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`),
  KEY `user_name` (`user_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2616 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_chelun
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_chelun`;
CREATE TABLE `lzh_members_chelun` (
  `uid` int(11) NOT NULL COMMENT '平台用户ID',
  `cl_user_id` varchar(50) NOT NULL COMMENT '车轮用户id',
  `mobile` varchar(11) NOT NULL COMMENT '手机',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_company
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_company`;
CREATE TABLE `lzh_members_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `company_name` varchar(90) NOT NULL COMMENT '公司名称',
  `address` varchar(90) NOT NULL COMMENT '企业地址',
  `license_no` varchar(50) NOT NULL COMMENT '营业执照号',
  `license_address` varchar(50) NOT NULL COMMENT '营业执照所在地',
  `license_expire_date` int(11) NOT NULL COMMENT '执照过期日',
  `business_scope` varchar(256) NOT NULL COMMENT '营业范围',
  `telephone` varchar(20) NOT NULL COMMENT '联系电话',
  `email` varchar(50) NOT NULL COMMENT 'Email',
  `organization_no` varchar(32) NOT NULL COMMENT '组织机构代码',
  `summary` varchar(512) NOT NULL COMMENT '企业简介',
  `legal_person` varchar(32) NOT NULL COMMENT '企业法人',
  `cert_no` varchar(18) NOT NULL COMMENT '法人证件号码',
  `legal_person_phone` varchar(20) NOT NULL COMMENT '法人手机号码',
  `addtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `audit_order_no` varchar(20) DEFAULT NULL COMMENT '订单号',
  `result` varchar(50) DEFAULT NULL COMMENT '审核结果',
  `article` int(11) NOT NULL DEFAULT '0' COMMENT '文章ID',
  `is_danbao` int(11) NOT NULL DEFAULT '0' COMMENT '是否担保机构',
  `max_money` int(11) NOT NULL COMMENT '担保额度',
  `agent_name` varchar(32) NOT NULL COMMENT '经办人姓名',
  `agent_mobile` varchar(20) NOT NULL COMMENT '经办人手机号',
  `alicense_no` varchar(18) NOT NULL COMMENT '经办人身份证',
  `zizhi` text COMMENT '企业资质',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_fengche
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_fengche`;
CREATE TABLE `lzh_members_fengche` (
  `uid` int(11) NOT NULL COMMENT '平台用户ID',
  `wrb_user_id` varchar(50) NOT NULL COMMENT '风车理财用户id',
  `pf_user_name` varchar(50) NOT NULL COMMENT '平台用户名',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL COMMENT '手机',
  `id_no` varchar(50) NOT NULL COMMENT '身份证号',
  `true_name` varchar(50) NOT NULL COMMENT '姓名',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_money
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_money`;
CREATE TABLE `lzh_members_money` (
  `uid` int(11) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '总余额',
  `available_balance` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '可用余额',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_status
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_status`;
CREATE TABLE `lzh_members_status` (
  `uid` int(10) unsigned NOT NULL,
  `phone_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `phone_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `id_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '实名认证状态   0:未上传1:验证通过2:等待验证',
  `id_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `face_status` tinyint(4) NOT NULL DEFAULT '0',
  `face_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `email_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `account_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `account_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `credit_status` tinyint(4) NOT NULL DEFAULT '0',
  `credit_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `safequestion_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `safequestion_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `video_status` tinyint(4) NOT NULL DEFAULT '0',
  `video_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `vip_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vip_credits` int(10) unsigned NOT NULL DEFAULT '0',
  `company_status` int(11) DEFAULT '0',
  `sina_member_status` int(11) NOT NULL DEFAULT '0' COMMENT '新浪激活会员状态：0未激活 1已激活',
  `sina_phone` int(11) NOT NULL DEFAULT '0' COMMENT '新浪手机认证',
  `is_pay_passwd` int(1) DEFAULT '0' COMMENT '是否设置新浪支付密码 1 是 0 否',
  `is_authentication` tinyint(3) DEFAULT '0' COMMENT '是否在上上签申请过CA证书：0：否，1：是，默认0',
  `fxpg_popup_status` tinyint(1) unsigned DEFAULT '1' COMMENT '0：首次设置弹窗无效 1：弹窗有效 2：设置弹窗无效',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_members_temp
-- ----------------------------
DROP TABLE IF EXISTS `lzh_members_temp`;
CREATE TABLE `lzh_members_temp` (
  `id` int(10) unsigned NOT NULL,
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `user_pass` char(32) NOT NULL COMMENT '密码',
  `user_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '普通，VIP',
  `user_regtype` tinyint(3) DEFAULT '1' COMMENT '注册类型',
  `pin_pass` char(32) DEFAULT NULL COMMENT '平台支付密码',
  `user_email` varchar(50) DEFAULT NULL COMMENT '用户邮箱',
  `user_phone` varchar(11) NOT NULL COMMENT '手机',
  `reg_time` int(10) unsigned NOT NULL COMMENT '注册时间',
  `reg_ip` varchar(15) DEFAULT NULL COMMENT '注册IP',
  `user_leve` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'NG',
  `time_limit` int(10) unsigned DEFAULT NULL COMMENT 'NG',
  `credits` int(10) DEFAULT NULL COMMENT 'NG',
  `recommend_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐注册人ID',
  `customer_id` int(10) unsigned DEFAULT NULL COMMENT 'NG',
  `customer_name` varchar(20) DEFAULT NULL COMMENT 'NG',
  `province` int(10) unsigned DEFAULT NULL COMMENT '省',
  `city` int(10) unsigned DEFAULT NULL COMMENT '市',
  `area` int(10) unsigned DEFAULT NULL COMMENT '区',
  `is_ban` int(11) NOT NULL DEFAULT '0' COMMENT '是否冻结0：否； 1：是',
  `reward_money` decimal(15,2) DEFAULT NULL COMMENT '奖金金额',
  `is_reward` int(11) NOT NULL DEFAULT '0' COMMENT '是否领取注册奖金0否1是',
  `invest_credits` decimal(15,2) unsigned DEFAULT NULL,
  `integral` int(15) DEFAULT NULL COMMENT '会员总积分',
  `active_integral` int(15) DEFAULT NULL COMMENT '会员活跃积分',
  `is_borrow` int(2) NOT NULL DEFAULT '0' COMMENT '是否允许会员发标。0：不允许；1：允许',
  `is_transfer` int(2) NOT NULL DEFAULT '0' COMMENT '是否是流转会员 0代表非流转会员，1代表是流转会员',
  `is_vip` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否开启特权发标，0：不开启；1：开启',
  `last_log_ip` char(15) DEFAULT NULL COMMENT '最后登录IP',
  `last_log_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `from` tinyint(3) NOT NULL DEFAULT '0' COMMENT '注册来源活动来源',
  `equipment` varchar(10) NOT NULL DEFAULT 'PC' COMMENT '来源设备',
  `argument1` varchar(10) NOT NULL DEFAULT '0' COMMENT '活动来源的第一个参数',
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`),
  KEY `user_name` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_message
-- ----------------------------
DROP TABLE IF EXISTS `lzh_message`;
CREATE TABLE `lzh_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_phone` char(11) NOT NULL COMMENT '已经发送短信的手机号',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `type` char(1) NOT NULL COMMENT '1 体验金明天过期发送短信',
  `desc` varchar(100) NOT NULL COMMENT '描述',
  `content` varchar(500) NOT NULL COMMENT '短信内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='记录发送短信信息';

-- ----------------------------
-- Table structure for lzh_name_apply
-- ----------------------------
DROP TABLE IF EXISTS `lzh_name_apply`;
CREATE TABLE `lzh_name_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `up_time` int(10) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `idcard` varchar(20) NOT NULL,
  `deal_info` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2246 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_navigation
-- ----------------------------
DROP TABLE IF EXISTS `lzh_navigation`;
CREATE TABLE `lzh_navigation` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(40) NOT NULL,
  `type_url` varchar(200) NOT NULL,
  `type_keyword` varchar(200) NOT NULL,
  `type_info` varchar(400) NOT NULL,
  `type_content` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  `type_set` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` smallint(6) NOT NULL,
  `type_nid` varchar(50) NOT NULL,
  `is_hiden` int(1) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) unsigned NOT NULL,
  `is_sys` tinyint(3) unsigned NOT NULL,
  `model` char(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_oauth
-- ----------------------------
DROP TABLE IF EXISTS `lzh_oauth`;
CREATE TABLE `lzh_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_bind` tinyint(30) NOT NULL DEFAULT '0',
  `site` varchar(30) NOT NULL DEFAULT '',
  `openid` varchar(255) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `logintimes` int(10) unsigned NOT NULL DEFAULT '0',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0',
  `bind_uid` int(10) NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `site` (`site`,`openid`),
  KEY `uname` (`is_bind`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_olympic_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_olympic_log`;
CREATE TABLE `lzh_olympic_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_phone` varchar(11) NOT NULL,
  `gold_num` int(5) NOT NULL,
  `updatetime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_outside_profit
-- ----------------------------
DROP TABLE IF EXISTS `lzh_outside_profit`;
CREATE TABLE `lzh_outside_profit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '标号',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '返现UID',
  `return_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '返现金额',
  `invest_uid` int(11) NOT NULL DEFAULT '0' COMMENT '投资人',
  `invest_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '投资金额',
  `return_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0未返现，1已返现',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '建立时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `investor_id` int(11) NOT NULL DEFAULT '0' COMMENT '投资序列ID',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `invest_uid` (`invest_uid`),
  KEY `investor_id` (`investor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_p9_count
-- ----------------------------
DROP TABLE IF EXISTS `lzh_p9_count`;
CREATE TABLE `lzh_p9_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '剩余砸冰块的机会数',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '抢券次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5776 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_p9_count2
-- ----------------------------
DROP TABLE IF EXISTS `lzh_p9_count2`;
CREATE TABLE `lzh_p9_count2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '砸冰块的机会数',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '抢券次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9552 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_p9_prize
-- ----------------------------
DROP TABLE IF EXISTS `lzh_p9_prize`;
CREATE TABLE `lzh_p9_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info` varchar(20) NOT NULL DEFAULT '0' COMMENT '奖品名称',
  `mark` varchar(50) DEFAULT NULL COMMENT '奖品备注',
  `type` tinyint(1) NOT NULL COMMENT '奖品类型 0 投资券 1 快乐豆 2 体验金  3现金 4 实物 5.加息券 6谢谢参与',
  `active_type` tinyint(1) NOT NULL COMMENT '活动类型 1 送 2砸 3抢',
  `value` decimal(6,2) NOT NULL COMMENT '对应奖品的价值或者数量',
  `odds_0` decimal(4,2) unsigned NOT NULL COMMENT '1档中奖概率(100-1000)',
  `minnum_0` int(6) unsigned NOT NULL COMMENT '1档下限(100-1000)',
  `maxnum_0` int(6) unsigned NOT NULL COMMENT '1档上限(100-1000)',
  `num_total` int(6) unsigned NOT NULL COMMENT '总数量',
  `num_left` int(6) unsigned NOT NULL COMMENT '剩余数量',
  `angle` int(3) DEFAULT NULL COMMENT '所转的角度',
  `status` int(6) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COMMENT='奖品库存表';

-- ----------------------------
-- Table structure for lzh_p9_win
-- ----------------------------
DROP TABLE IF EXISTS `lzh_p9_win`;
CREATE TABLE `lzh_p9_win` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `value` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `name` varchar(50) DEFAULT NULL COMMENT '奖品名',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `status` int(13) NOT NULL DEFAULT '0' COMMENT '是否已经发放 0 未发放 1 已发放',
  `desc` varchar(50) NOT NULL COMMENT '描述',
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=489 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_partners
-- ----------------------------
DROP TABLE IF EXISTS `lzh_partners`;
CREATE TABLE `lzh_partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_txt` varchar(50) NOT NULL COMMENT '链接文字',
  `link_href` varchar(500) NOT NULL COMMENT '链接地址',
  `link_img` varchar(100) NOT NULL DEFAULT ' ' COMMENT '链接图片',
  `link_order` int(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `link_type` int(1) NOT NULL DEFAULT '0' COMMENT '显示位置',
  `is_show` int(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='合作伙伴表';

-- ----------------------------
-- Table structure for lzh_policy
-- ----------------------------
DROP TABLE IF EXISTS `lzh_policy`;
CREATE TABLE `lzh_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(100) NOT NULL COMMENT '策略名称',
  `commission_rate` decimal(10,4) DEFAULT NULL COMMENT '佣金比例',
  `is_permanent` int(11) DEFAULT '0' COMMENT '是否永久',
  `begin_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `check_status` int(11) NOT NULL DEFAULT '0' COMMENT '状态0:待审核;1:有效;2:无效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_product_type
-- ----------------------------
DROP TABLE IF EXISTS `lzh_product_type`;
CREATE TABLE `lzh_product_type` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_qq
-- ----------------------------
DROP TABLE IF EXISTS `lzh_qq`;
CREATE TABLE `lzh_qq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qq_num` varchar(50) NOT NULL,
  `qq_title` varchar(100) NOT NULL,
  `qq_order` int(2) NOT NULL,
  `is_show` int(1) NOT NULL DEFAULT '1',
  `type` int(1) NOT NULL COMMENT '0：qq号；1：qq群；2：客服电话',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_first
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_first`;
CREATE TABLE `lzh_recommend_first` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommend_uid` int(11) NOT NULL COMMENT '推荐人ID',
  `recommend_count` int(11) NOT NULL DEFAULT '0' COMMENT '邀请实名人数',
  `coupons_count` int(11) DEFAULT '0' COMMENT '获得投资券数量',
  `experience_money` decimal(15,2) DEFAULT '0.00' COMMENT '体验金',
  `used_money` decimal(15,2) DEFAULT '0.00' COMMENT '已使用体验金',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间，人数增加才更新',
  `is_freeze` tinyint(4) DEFAULT '0' COMMENT '是否冻结',
  KEY `id` (`id`),
  KEY `recommend_uid` (`recommend_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_invest
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_invest`;
CREATE TABLE `lzh_recommend_invest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommend_uid` int(11) NOT NULL COMMENT '推荐人UID',
  `invest_uid` int(11) NOT NULL COMMENT '被推荐人UID',
  `invest_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '累计投资金额',
  `verify_time` int(11) NOT NULL DEFAULT '0' COMMENT '实名时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间（金额变动更新）',
  PRIMARY KEY (`id`),
  KEY `recommend_uid` (`recommend_uid`),
  KEY `invest_uid` (`invest_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_log`;
CREATE TABLE `lzh_recommend_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verify_id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `status` int(2) NOT NULL COMMENT '状态:0,提交；2，一审通过；4，二审通过；6，一审不通过；8，二审不通过；5，撤销',
  `content` varchar(200) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_lucky
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_lucky`;
CREATE TABLE `lzh_recommend_lucky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `total_count` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
  `used_count` int(11) NOT NULL DEFAULT '0' COMMENT '已使用次数',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_permissions
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_permissions`;
CREATE TABLE `lzh_recommend_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `permissions` int(2) NOT NULL COMMENT '权限：0,无权限；1，申请人；2，一审人员；3，二审人员',
  `modify_time` int(10) NOT NULL,
  `modify_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_prize
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_prize`;
CREATE TABLE `lzh_recommend_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_name` varchar(50) NOT NULL COMMENT '奖品',
  `prize_count` int(11) NOT NULL COMMENT '奖品数量',
  `send_count` int(11) NOT NULL DEFAULT '0' COMMENT '已抽中数量',
  `prize_probability` decimal(15,2) NOT NULL COMMENT '奖品概率',
  `active` int(11) NOT NULL DEFAULT '0' COMMENT '0开启1关闭',
  `is_edit` int(11) NOT NULL DEFAULT '0' COMMENT '0可编辑1不可编辑',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_seconde
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_seconde`;
CREATE TABLE `lzh_recommend_seconde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_type` int(4) NOT NULL COMMENT '1.独占鳌头,2尊享，3普利',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `add_time` int(11) NOT NULL COMMENT '获奖时间',
  `send_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未发放1已返现',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_verify
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_verify`;
CREATE TABLE `lzh_recommend_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `new_recommend_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `status` int(2) NOT NULL COMMENT '状态:0,提交；2，一审通过；4，二审通过；6，一审不通过；8，二审不通过；5，撤销',
  `application_time` int(10) unsigned NOT NULL,
  `review_time` int(10) unsigned NOT NULL,
  `old_recommend_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_recommend_winner
-- ----------------------------
DROP TABLE IF EXISTS `lzh_recommend_winner`;
CREATE TABLE `lzh_recommend_winner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '中奖uid',
  `prize_id` int(11) NOT NULL COMMENT '奖品ID',
  `add_time` int(11) NOT NULL COMMENT '中奖时间',
  PRIMARY KEY (`id`),
  KEY `prize_id` (`prize_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=508 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_repayment_type
-- ----------------------------
DROP TABLE IF EXISTS `lzh_repayment_type`;
CREATE TABLE `lzh_repayment_type` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_risk_answer
-- ----------------------------
DROP TABLE IF EXISTS `lzh_risk_answer`;
CREATE TABLE `lzh_risk_answer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `problem_id` int(10) NOT NULL COMMENT '风险问题ID',
  `answer` varchar(500) NOT NULL COMMENT '答案',
  `score` int(3) NOT NULL COMMENT '答案对应分数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_risk_problem
-- ----------------------------
DROP TABLE IF EXISTS `lzh_risk_problem`;
CREATE TABLE `lzh_risk_problem` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `problem` varchar(500) NOT NULL COMMENT '问题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_risk_result
-- ----------------------------
DROP TABLE IF EXISTS `lzh_risk_result`;
CREATE TABLE `lzh_risk_result` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `problem_id` int(10) NOT NULL COMMENT '问题ID',
  `answer_id` int(10) NOT NULL COMMENT '答案ID',
  `time` int(10) NOT NULL COMMENT '测试时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2221 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_send_coupons
-- ----------------------------
DROP TABLE IF EXISTS `lzh_send_coupons`;
CREATE TABLE `lzh_send_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL COMMENT '类型名称',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `money` int(11) NOT NULL COMMENT '面值',
  `days` int(11) NOT NULL COMMENT '有效天数',
  `nologin_days` int(11) DEFAULT NULL COMMENT '未登录时间',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_shangshang
-- ----------------------------
DROP TABLE IF EXISTS `lzh_shangshang`;
CREATE TABLE `lzh_shangshang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL,
  `sign_id` varchar(50) DEFAULT NULL,
  `doc_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=285 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_sinalog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_sinalog`;
CREATE TABLE `lzh_sinalog` (
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `borrow_id` int(11) DEFAULT NULL COMMENT '标号',
  `type` int(11) NOT NULL COMMENT '1.充值2.提现3.投标4.还款5.退款6.红包7.付款8.付提现手续费9.收提现手续费10.综合服务费12担保费代收13担保费代付14.代付提现卡15等本降息还款 16债权标投标 17 债权标还款18 债权等本降息还款，19债权标支付手续费 20 债权付款 21 债权代付卡 22 债权退款25外部推荐奖励',
  `order_no` varchar(100) DEFAULT NULL COMMENT '订单号',
  `money` decimal(15,2) NOT NULL COMMENT '金额',
  `addtime` int(11) NOT NULL COMMENT '创建时间',
  `completetime` int(11) DEFAULT NULL COMMENT '完成时间',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '1.处理中2.冻结已完成3.交易失败4.扣款完成',
  `sort_order` varchar(100) DEFAULT NULL COMMENT '还款字段（其他无需填写）',
  `coupons` varchar(20) DEFAULT NULL COMMENT '投资卷序号',
  `is_auto` tinyint(3) DEFAULT '0' COMMENT '是否自动投标，0：否，1：是',
  `jx_coupons` varchar(20) DEFAULT NULL COMMENT '加息券序号'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_smslog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_smslog`;
CREATE TABLE `lzh_smslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `admin_real_name` varchar(50) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_phone` varchar(50) NOT NULL,
  `title` varchar(20) NOT NULL,
  `content` varchar(500) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_store_outside
-- ----------------------------
DROP TABLE IF EXISTS `lzh_store_outside`;
CREATE TABLE `lzh_store_outside` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_id` int(11) NOT NULL COMMENT '标号',
  `recommend_uid` int(11) NOT NULL DEFAULT '0' COMMENT '推荐人uid',
  `store_uid` int(11) NOT NULL DEFAULT '0' COMMENT '店铺uid',
  `invest_uid` int(11) NOT NULL DEFAULT '0' COMMENT '投资人uid',
  `invest_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '投资金额',
  `return_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '返现金额',
  `investor_id` int(11) NOT NULL DEFAULT '0' COMMENT '投资序号',
  `add_time` int(11) NOT NULL COMMENT '新增时间',
  PRIMARY KEY (`id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `recommend_uid` (`recommend_uid`),
  KEY `store_uid` (`store_uid`),
  KEY `invest_uid` (`invest_uid`),
  KEY `investor_id` (`investor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_sys_tip
-- ----------------------------
DROP TABLE IF EXISTS `lzh_sys_tip`;
CREATE TABLE `lzh_sys_tip` (
  `uid` int(10) unsigned NOT NULL,
  `tipset` varchar(300) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `tipset` (`tipset`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `lzh_system_setting`;
CREATE TABLE `lzh_system_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `number` int(4) NOT NULL COMMENT '系统设置编号',
  `name` varchar(30) NOT NULL COMMENT '系统设置字段名称',
  `value` varchar(30) NOT NULL COMMENT '系统设置字段值',
  `mark` char(40) DEFAULT NULL COMMENT '系统设置字段备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='全局系统设置';

-- ----------------------------
-- Table structure for lzh_test1
-- ----------------------------
DROP TABLE IF EXISTS `lzh_test1`;
CREATE TABLE `lzh_test1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onid` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_test2
-- ----------------------------
DROP TABLE IF EXISTS `lzh_test2`;
CREATE TABLE `lzh_test2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onid` int(11) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `onid2` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_test3
-- ----------------------------
DROP TABLE IF EXISTS `lzh_test3`;
CREATE TABLE `lzh_test3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onid2` int(11) DEFAULT NULL,
  `gender` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_testinnodb
-- ----------------------------
DROP TABLE IF EXISTS `lzh_testinnodb`;
CREATE TABLE `lzh_testinnodb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `money` decimal(15,2) DEFAULT NULL,
  `borrow_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_testisam
-- ----------------------------
DROP TABLE IF EXISTS `lzh_testisam`;
CREATE TABLE `lzh_testisam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `money` decimal(15,2) DEFAULT NULL,
  `borrow_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3501 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_today_reward
-- ----------------------------
DROP TABLE IF EXISTS `lzh_today_reward`;
CREATE TABLE `lzh_today_reward` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrow_id` int(10) unsigned NOT NULL,
  `reward_uid` int(10) unsigned NOT NULL,
  `invest_money` decimal(15,2) unsigned NOT NULL,
  `reward_money` decimal(10,2) unsigned NOT NULL,
  `reward_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_time` int(10) NOT NULL,
  `deal_time` int(10) NOT NULL,
  `add_ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_transfer_borrow_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_transfer_borrow_info`;
CREATE TABLE `lzh_transfer_borrow_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `borrow_name` varchar(50) NOT NULL,
  `borrow_uid` int(11) NOT NULL,
  `borrow_duration` tinyint(3) unsigned NOT NULL,
  `borrow_money` decimal(15,2) NOT NULL,
  `borrow_interest` decimal(15,2) NOT NULL,
  `borrow_interest_rate` decimal(5,2) NOT NULL,
  `repayment_money` decimal(15,2) NOT NULL,
  `repayment_interest` decimal(15,2) NOT NULL,
  `repayment_type` tinyint(3) unsigned NOT NULL,
  `borrow_status` tinyint(3) unsigned NOT NULL,
  `transfer_out` int(10) NOT NULL,
  `transfer_back` int(10) unsigned NOT NULL,
  `transfer_total` int(10) NOT NULL,
  `per_transfer` int(10) NOT NULL,
  `add_time` int(10) NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `deal_user` int(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(500) NOT NULL,
  `borrow_info` varchar(2000) NOT NULL,
  `ensure_department` varchar(10) NOT NULL,
  `updata` varchar(2000) NOT NULL,
  `progress` tinyint(3) unsigned NOT NULL,
  `total` tinyint(4) NOT NULL,
  `is_show` tinyint(4) NOT NULL DEFAULT '1',
  `min_month` tinyint(4) NOT NULL DEFAULT '0',
  `reward_rate` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '网站奖励(每月)',
  `increase_rate` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '每月增加年利率',
  `borrow_fee` decimal(15,2) NOT NULL COMMENT '借款管理费',
  `level_can` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0:允许普通会员投标；1:只允许VIP投标',
  `borrow_min` int(11) NOT NULL COMMENT '最低投标额度',
  `borrow_max` int(11) NOT NULL COMMENT '最高投标额度',
  `danbao` decimal(15,2) NOT NULL COMMENT '担保机构',
  `is_tuijian` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否设为推荐标 0表示不推荐；1表示推荐',
  `borrow_type` int(11) NOT NULL DEFAULT '6' COMMENT '刘',
  `b_img` varchar(200) NOT NULL COMMENT '流转标展示图片',
  `collect_day` int(10) NOT NULL COMMENT '允许投标的期限',
  `is_auto` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否允许自动投标 0：否；1：是。',
  `is_jijin` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否是定投宝 0：企业直投；1：定投宝',
  `online_time` int(10) NOT NULL DEFAULT '0' COMMENT '上线时间',
  `on_off` tinyint(2) NOT NULL COMMENT '是否显示 0：显示；1：不显示',
  PRIMARY KEY (`id`),
  KEY `borrow_uid` (`borrow_uid`,`borrow_status`) USING BTREE,
  KEY `borrow_status` (`is_show`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_transfer_borrow_info_lock
-- ----------------------------
DROP TABLE IF EXISTS `lzh_transfer_borrow_info_lock`;
CREATE TABLE `lzh_transfer_borrow_info_lock` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `suo` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_transfer_borrow_investor
-- ----------------------------
DROP TABLE IF EXISTS `lzh_transfer_borrow_investor`;
CREATE TABLE `lzh_transfer_borrow_investor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `borrow_id` int(10) unsigned NOT NULL,
  `investor_uid` int(10) unsigned NOT NULL,
  `borrow_uid` int(11) NOT NULL,
  `investor_capital` decimal(15,2) NOT NULL,
  `investor_interest` decimal(15,2) NOT NULL,
  `invest_fee` decimal(15,2) NOT NULL,
  `receive_capital` decimal(15,2) NOT NULL,
  `receive_interest` decimal(15,2) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  `is_auto` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reward_money` decimal(15,2) NOT NULL,
  `transfer_num` int(10) unsigned NOT NULL DEFAULT '0',
  `transfer_month` int(10) unsigned NOT NULL DEFAULT '0',
  `back_time` int(10) unsigned NOT NULL,
  `final_interest_rate` float(5,2) NOT NULL DEFAULT '0.00',
  `is_jijin` tinyint(3) NOT NULL COMMENT '是否定投保：1：定投宝；0：直投',
  PRIMARY KEY (`id`),
  KEY `investor_uid` (`investor_uid`,`status`) USING BTREE,
  KEY `borrow_id` (`borrow_id`,`investor_uid`,`status`) USING BTREE,
  KEY `deadline` (`deadline`,`status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_transfer_detail
-- ----------------------------
DROP TABLE IF EXISTS `lzh_transfer_detail`;
CREATE TABLE `lzh_transfer_detail` (
  `borrow_id` int(10) unsigned NOT NULL,
  `borrow_breif` varchar(2000) NOT NULL,
  `borrow_capital` varchar(2000) NOT NULL,
  `borrow_use` varchar(2000) NOT NULL,
  `borrow_risk` varchar(2000) NOT NULL,
  `borrow_guarantee` varchar(50) NOT NULL,
  `borrow_img` varchar(2000) NOT NULL,
  PRIMARY KEY (`borrow_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_transfer_investor_detail
-- ----------------------------
DROP TABLE IF EXISTS `lzh_transfer_investor_detail`;
CREATE TABLE `lzh_transfer_investor_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repayment_time` int(10) unsigned NOT NULL DEFAULT '0',
  `borrow_id` int(10) unsigned NOT NULL,
  `invest_id` int(10) unsigned NOT NULL,
  `investor_uid` int(10) unsigned NOT NULL,
  `borrow_uid` int(10) unsigned NOT NULL,
  `capital` decimal(15,2) NOT NULL,
  `interest` decimal(15,2) NOT NULL,
  `interest_fee` decimal(15,2) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `receive_interest` decimal(15,2) NOT NULL,
  `receive_capital` decimal(15,2) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL,
  `total` tinyint(3) unsigned NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  `expired_money` decimal(15,2) NOT NULL,
  `expired_days` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `call_fee` decimal(5,2) NOT NULL,
  `substitute_money` decimal(15,2) NOT NULL,
  `substitute_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `invest_id` (`invest_id`,`status`,`deadline`) USING BTREE,
  KEY `borrow_id` (`borrow_id`,`sort_order`,`investor_uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_vc_count
-- ----------------------------
DROP TABLE IF EXISTS `lzh_vc_count`;
CREATE TABLE `lzh_vc_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `count_0` int(11) NOT NULL DEFAULT '0' COMMENT '1档抽奖次数(100-1000)',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '2档抽奖次数(1001-5000)',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '3档抽奖次数(5001-20000)',
  `count_3` int(11) NOT NULL DEFAULT '0' COMMENT '4档抽奖次数(20001-100000)',
  `count_4` int(11) NOT NULL DEFAULT '0' COMMENT '5档抽奖次数100000以上',
  `consume_no` int(11) DEFAULT '0',
  `prize` varchar(200) DEFAULT '',
  `log_ids` text,
  `rebate` decimal(6,2) NOT NULL COMMENT '本次投资奖励',
  `bid` int(11) NOT NULL COMMENT '本次投标id',
  `days` int(11) NOT NULL COMMENT '本次标的的时间，以日为单位',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_vc_prize
-- ----------------------------
DROP TABLE IF EXISTS `lzh_vc_prize`;
CREATE TABLE `lzh_vc_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info` varchar(20) NOT NULL DEFAULT '0' COMMENT '奖品名称',
  `mark` varchar(50) DEFAULT NULL COMMENT '奖品备注',
  `type` tinyint(1) NOT NULL COMMENT '奖品类型 0 投资券 1 快乐豆 2 体验金  3现金 4 实物',
  `value` decimal(6,2) NOT NULL COMMENT '对应奖品的价值或者数量',
  `odds_0` decimal(4,2) unsigned NOT NULL COMMENT '1档中奖概率(100-1000)',
  `odds_1` decimal(4,2) unsigned NOT NULL COMMENT '2档中奖概率(1001-5000)',
  `odds_2` decimal(4,2) unsigned NOT NULL COMMENT '3档中奖概率(5001-20000)',
  `odds_3` decimal(4,2) unsigned NOT NULL COMMENT '4档中奖概率(20001-100000)',
  `odds_4` decimal(4,2) unsigned NOT NULL COMMENT '5档中奖概率100000以上',
  `minnum_0` int(6) unsigned NOT NULL COMMENT '1档下限(100-1000)',
  `maxnum_0` int(6) unsigned NOT NULL COMMENT '1档上限(100-1000)',
  `minnum_1` int(6) unsigned NOT NULL COMMENT '2档下限(1001-5000)',
  `maxnum_1` int(6) unsigned NOT NULL COMMENT '2档上限(1001-5000)',
  `minnum_2` int(6) unsigned NOT NULL COMMENT '3档下限(5001-20000)',
  `maxnum_2` int(6) unsigned NOT NULL COMMENT '3档上限(5001-20000)',
  `minnum_3` int(6) unsigned NOT NULL COMMENT '4档下限(20001-100000)',
  `maxnum_3` int(6) unsigned NOT NULL COMMENT '4档上限(20001-100000)',
  `minnum_4` int(6) unsigned NOT NULL COMMENT '5档下限100000以上',
  `maxnum_4` int(6) unsigned NOT NULL COMMENT '5档上限100000以上',
  `angle` int(3) DEFAULT NULL COMMENT '所转的角度',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='奖品库存表';

-- ----------------------------
-- Table structure for lzh_vc_recom
-- ----------------------------
DROP TABLE IF EXISTS `lzh_vc_recom`;
CREATE TABLE `lzh_vc_recom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '被推荐人用户id',
  `user_phone` varchar(20) NOT NULL,
  `parent_id` int(11) NOT NULL COMMENT '被推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_vc_tmp
-- ----------------------------
DROP TABLE IF EXISTS `lzh_vc_tmp`;
CREATE TABLE `lzh_vc_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL DEFAULT '0' COMMENT '已经处理的log_id',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '标记类型 type = 101 从dream_log 转换type 101 的操作',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_verify
-- ----------------------------
DROP TABLE IF EXISTS `lzh_verify`;
CREATE TABLE `lzh_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `send_time` int(10) NOT NULL,
  `ukey` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '1:邮件激活验证',
  PRIMARY KEY (`id`),
  KEY `code` (`ukey`,`type`,`send_time`,`code`)
) ENGINE=MyISAM AUTO_INCREMENT=786 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_video_apply
-- ----------------------------
DROP TABLE IF EXISTS `lzh_video_apply`;
CREATE TABLE `lzh_video_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `add_ip` varchar(16) NOT NULL,
  `apply_status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `deal_user` int(10) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_info` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_view_borrow_info
-- ----------------------------
DROP TABLE IF EXISTS `lzh_view_borrow_info`;
CREATE TABLE `lzh_view_borrow_info` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标号',
  `product_type` tinyint(3) NOT NULL COMMENT '1提单质押(质金链) 2提单转现货(质金链) 3.现货(质金链) 4.生产金融(融金链)  5分期购(分期购)  6 信金链(信金链) 7优金链 8保金链 9.新手标',
  `borrow_interest` decimal(15,2) NOT NULL COMMENT '借款利息',
  `investor_uid` int(10) unsigned DEFAULT NULL COMMENT '投资人ID',
  `invest_id` int(10) unsigned DEFAULT NULL,
  `repayment_type` tinyint(3) unsigned NOT NULL COMMENT '1 天标 2 按月分期还款 3按季分期还款 4每月还息到期还本 5一次性还款 7 等本降息',
  `has_pay` tinyint(4) NOT NULL DEFAULT '0',
  `borrow_duration` tinyint(3) unsigned NOT NULL COMMENT '借款时间',
  `borrow_name` varchar(50) NOT NULL COMMENT '标题',
  `borrow_id` int(10) unsigned DEFAULT NULL COMMENT '标的ID',
  `investor_capital` decimal(15,2) DEFAULT NULL COMMENT '充值资金池的投资金额',
  `second_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复审时间',
  `borrow_interest_rate` decimal(5,2) NOT NULL COMMENT '借款利率'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_vip_apply
-- ----------------------------
DROP TABLE IF EXISTS `lzh_vip_apply`;
CREATE TABLE `lzh_vip_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `kfid` int(10) unsigned NOT NULL,
  `province_now` int(10) unsigned NOT NULL,
  `city_now` int(11) NOT NULL,
  `area_now` int(11) NOT NULL,
  `des` varchar(1000) NOT NULL,
  `add_time` int(10) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `deal_time` int(10) unsigned NOT NULL,
  `deal_user` int(10) unsigned NOT NULL,
  `deal_info` varchar(200) NOT NULL COMMENT '处理意见',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_admin_user`;
CREATE TABLE `lzh_weixin_admin_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uname` varchar(100) NOT NULL COMMENT '用户名',
  `password` varchar(500) NOT NULL COMMENT '密码',
  `auth_key` varchar(50) DEFAULT NULL COMMENT '自动登录key',
  `last_ip` varchar(50) DEFAULT NULL COMMENT '最近一次登录ip',
  `is_online` char(1) DEFAULT 'n' COMMENT '是否在线',
  `domain_account` varchar(100) DEFAULT NULL COMMENT '域账号',
  `status` smallint(6) NOT NULL DEFAULT '10' COMMENT '状态',
  `create_user` varchar(100) NOT NULL COMMENT '创建人',
  `create_date` datetime NOT NULL COMMENT '创建时间',
  `update_date` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_chouqian
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_chouqian`;
CREATE TABLE `lzh_weixin_chouqian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) DEFAULT NULL COMMENT '用户openid',
  `addtime` datetime DEFAULT NULL,
  `qianid` int(11) DEFAULT NULL COMMENT '签文编号',
  `type` tinyint(1) DEFAULT NULL COMMENT '签类型 1上上签 2上签 3中签 4下签 5下下签',
  `cate` tinyint(1) DEFAULT NULL COMMENT '1 财运签 2姻缘签',
  `picid` tinyint(1) DEFAULT NULL COMMENT '头像类别',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1083 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_log
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_log`;
CREATE TABLE `lzh_weixin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info` varchar(1500) DEFAULT NULL,
  `times` datetime DEFAULT NULL,
  `openid` char(30) DEFAULT NULL,
  `event` char(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14415 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_menu
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_menu`;
CREATE TABLE `lzh_weixin_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menuname` varchar(20) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1 click  2 view',
  `url_key` varchar(100) DEFAULT NULL COMMENT '对于click则是key,对于view则是url',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_msg
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_msg`;
CREATE TABLE `lzh_weixin_msg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) DEFAULT NULL COMMENT '消息标题',
  `desc` varchar(50) DEFAULT NULL COMMENT '图文消息的消息描述或者文本消息的消息内容',
  `imgpath` varchar(50) DEFAULT NULL COMMENT '图片路劲',
  `url` varchar(30) DEFAULT NULL COMMENT '针对图文消息链接路劲',
  `type` tinyint(1) DEFAULT NULL COMMENT '1图文消息 2 文本消息',
  `chufa` tinyint(1) NOT NULL COMMENT '触发类型：1关注 2 取消关注 3关键词',
  `keyword` varchar(15) DEFAULT NULL COMMENT '关键词',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_qian
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_qian`;
CREATE TABLE `lzh_weixin_qian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(200) NOT NULL COMMENT '签文内容',
  `type` tinyint(1) NOT NULL COMMENT '签类型 1上上签 2上签 3中签 4下签 5下下签',
  `dianping` varchar(200) DEFAULT NULL COMMENT '专家点评',
  `sex` tinyint(1) DEFAULT NULL COMMENT '签归属性别： 1 男 2女 3 不限',
  `cate` tinyint(1) DEFAULT NULL COMMENT '1 财运签 2姻缘签',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_token
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_token`;
CREATE TABLE `lzh_weixin_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL COMMENT '1access_token2ticket',
  `content` text NOT NULL COMMENT '凭证',
  `expires_time` int(11) NOT NULL COMMENT '结束时间错',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_user
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_user`;
CREATE TABLE `lzh_weixin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '微信昵称',
  `sex` tinyint(4) DEFAULT NULL COMMENT '性别1时是男性，值为2时是女性，值为0时是未知',
  `headimgurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '头像',
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '国家',
  `province` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '城市',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `choosesex` tinyint(1) DEFAULT NULL COMMENT '进入页面选择男或者女: 1男 2女',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17859 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_welcome
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_welcome`;
CREATE TABLE `lzh_weixin_welcome` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(1000) DEFAULT NULL COMMENT '抽签文案活动',
  `pic` varchar(100) DEFAULT NULL COMMENT '抽签二维码',
  `rate` tinyint(2) unsigned DEFAULT NULL COMMENT '第二只签相同的概率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_zhongjian
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_zhongjian`;
CREATE TABLE `lzh_weixin_zhongjian` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(30) DEFAULT NULL,
  `addtime` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1' COMMENT '1 未处理 2已处理',
  `nickname` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_weixin_zhuanfa
-- ----------------------------
DROP TABLE IF EXISTS `lzh_weixin_zhuanfa`;
CREATE TABLE `lzh_weixin_zhuanfa` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(30) DEFAULT NULL COMMENT '用户openid',
  `addtime` datetime DEFAULT NULL,
  `nickname` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25151 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lzh_withdrawlog
-- ----------------------------
DROP TABLE IF EXISTS `lzh_withdrawlog`;
CREATE TABLE `lzh_withdrawlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '提现手续费',
  `fee_status` int(11) NOT NULL DEFAULT '0' COMMENT '提现手续费支付状态：0未支付1暂时收取2已收取3退回',
  `fee_orderno` varchar(50) DEFAULT NULL COMMENT '手续费订单号',
  `money` decimal(15,2) DEFAULT NULL COMMENT '提现金额',
  `money_status` int(11) DEFAULT '0' COMMENT '提现：0没回来1回来了2提现成功3提现失败',
  `money_orderno` varchar(50) DEFAULT NULL COMMENT '提现订单号',
  `add_time` int(11) NOT NULL COMMENT '新增时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for Sheet1
-- ----------------------------
DROP TABLE IF EXISTS `Sheet1`;
CREATE TABLE `Sheet1` (
  `158` varchar(255) DEFAULT NULL,
  `杨树斌` varchar(255) DEFAULT NULL,
  `200133c25dd478d92660cc23e0913dd3` varchar(255) DEFAULT NULL,
  `1` varchar(255) DEFAULT NULL,
  `11` varchar(255) DEFAULT NULL,
  `F` varchar(255) DEFAULT NULL,
  `13380553015@189.cn` varchar(255) DEFAULT NULL,
  `18902766999` varchar(255) DEFAULT NULL,
  `1444823716` varchar(255) DEFAULT NULL,
  `14.30.31.29` varchar(255) DEFAULT NULL,
  `0` varchar(255) DEFAULT NULL,
  `01` varchar(255) DEFAULT NULL,
  `10` varchar(255) DEFAULT NULL,
  `02` varchar(255) DEFAULT NULL,
  `03` varchar(255) DEFAULT NULL,
  `P` varchar(255) DEFAULT NULL,
  `04` varchar(255) DEFAULT NULL,
  `05` varchar(255) DEFAULT NULL,
  `06` varchar(255) DEFAULT NULL,
  `07` varchar(255) DEFAULT NULL,
  `10.00` varchar(255) DEFAULT NULL,
  `08` varchar(255) DEFAULT NULL,
  `0.00` varchar(255) DEFAULT NULL,
  `09` varchar(255) DEFAULT NULL,
  `010` varchar(255) DEFAULT NULL,
  `011` varchar(255) DEFAULT NULL,
  `012` varchar(255) DEFAULT NULL,
  `013` varchar(255) DEFAULT NULL,
  `14.30.31.291` varchar(255) DEFAULT NULL,
  `14448237161` varchar(255) DEFAULT NULL,
  `PC` varchar(255) DEFAULT NULL,
  `014` varchar(255) DEFAULT NULL,
  `AG` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for test
-- ----------------------------
DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `openid` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
