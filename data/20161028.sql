
CREATE TABLE `lzh_debt_borrow_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标号',
  `borrow_name` varchar(50) NOT NULL COMMENT '标题',
  `borrow_uid` int(11) NOT NULL COMMENT '借款人uid',
  `borrow_duration` tinyint(3) unsigned NOT NULL COMMENT '借款时间',
  `borrow_duration_txt` varchar(50) NOT NULL COMMENT '借款时间的文字描述',
  `borrow_money` decimal(15,2) NOT NULL COMMENT '借款金额',
  `borrow_interest` decimal(15,2) NOT NULL COMMENT '借款利息',
  `borrow_interest_rate` decimal(5,2) NOT NULL COMMENT '借款利率',
  `borrow_fee` decimal(15,2) NOT NULL COMMENT 'NC',
  `has_borrow` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投资者已投金额',
  `borrow_times` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '投资次数',
  `repayment_money` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'NC',
  `repayment_interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'NC',
  `expired_money` decimal(15,2) NOT NULL DEFAULT '0.00',
  `product_type` tinyint(3) NOT NULL COMMENT '1提单质押 2提单转现货 3.现货 4.生产金融  5分期购  6 信金链',
  `n_interest` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'n+n提单利息',
  `n_colligate_fee` decimal(15,2) DEFAULT NULL COMMENT 'n+n综合服务费',
  `colligate_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '综合管理费',
  `repayment_type` tinyint(3) unsigned NOT NULL COMMENT '1 天标 2 按月分期还款 3按季分期还款 4每月还息到期还本 5一次性还款 7 等本降息',
  `borrow_type` tinyint(3) unsigned NOT NULL COMMENT '1.信用标 2担保标',
  `borrow_status` tinyint(3) unsigned NOT NULL COMMENT '0：发标 1：初审失败 2 初审通过 3 流标  4.满标 5.复审失败 6复审成功 7还款完成, 8表示定时发标，初审通过，但是还没发标 9 网站代还 10 网站代还逾期',
  `borrow_use` tinyint(3) unsigned NOT NULL COMMENT '借款用途，1 ： ''短期周转'', 2： ''生意周转'', 3： ''生活周转'', 4 ：''购物消费'', 5 ： ''不提现借款'', 6 ： ''创业借款'', 7 ： ''其它借款''，8：''装修借款''（4：对应乐购分期，8对应：乐装分期）',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '发标时间',
  `collect_day` tinyint(3) unsigned NOT NULL COMMENT '投标有效时间',
  `collect_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投标有效时间搓',
  `full_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '满标时间',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最总还款日',
  `first_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初审时间',
  `second_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复审时间',
  `add_ip` varchar(16) NOT NULL DEFAULT '',
  `borrow_info` text COMMENT '项目阐述',
  `total` tinyint(4) NOT NULL DEFAULT '0' COMMENT '总期数',
  `has_pay` tinyint(4) NOT NULL DEFAULT '0' COMMENT '已经还款的期数号',
  `substitute_money` decimal(15,2) NOT NULL DEFAULT '0.00',
  `reward_vouch_rate` float(5,2) NOT NULL DEFAULT '0.00',
  `reward_vouch_money` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `reward_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reward_num` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `reward_money` decimal(15,2) unsigned NOT NULL DEFAULT '0.00',
  `borrow_min` mediumint(8) unsigned NOT NULL COMMENT '最小投资金额',
  `borrow_max` mediumint(8) unsigned NOT NULL COMMENT '最大投资金额',
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
  `apply_status` tinyint(4) NOT NULL DEFAULT '0',
  `test` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1表示是测试标，前台不显示',
  PRIMARY KEY (`id`),
  KEY `borrow_status` (`borrow_status`,`collect_time`,`borrow_interest_rate`,`borrow_money`,`borrow_duration`,`id`),
  KEY `borrow_uid` (`borrow_uid`,`borrow_status`),
  KEY `danbao` (`danbao`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC  COMMENT='债权转让标详情表';


CREATE TABLE `lzh_borrow_debt` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
`borrow_id` int(11) UNSIGNED NOT NULL  COMMENT '原始标号' ,
`level`  tinyint(1) NOT NULL COMMENT '转让层级' ,
`debt_borrow_uid`  int(11) NOT NULL COMMENT '转让人uid' ,
`debt_totalmoney`  decimal(8,2) NOT NULL COMMENT '债权价值' ,
`debt_captial`    decimal(8,2) NOT NULL COMMENT '转让本金' ,
`debt_addtime`  datetime NOT NULL COMMENT '添加时间' ,
`debt_starttime`  datetime NOT NULL COMMENT '转让成功开始持有时间' ,
`debt_endtime`  datetime NOT NULL COMMENT '下次转让出去时间' ,
`debt_status`  tinyint(3) NOT NULL COMMENT '转让状态  0初始化申请 ，1审核通过 ， 2审核否决，3时间过期 ，4主动撤销  ，5未满标撤销，6满标转让通过  7 已支付手续费',
`debt_rate`  float(4,2) NOT NULL COMMENT '折算率' ,
`debt_leftmoney`  decimal(8,2) NOT NULL COMMENT '债权剩余金额' ,
`debt_expire`  datetime NOT NULL COMMENT '债权到期时间' ,
`debt_newname`  varchar(40) NOT NULL COMMENT '债权新名称' ,
`debt_parent_borrow_id` int(11) NOT NULL COMMENT '债权上一级标号' ,
`debt_borrow_id`  int(11) NOT NULL COMMENT '债权新标号' ,
`debt_fee`  decimal(6,2) NULL COMMENT '手续费' ,
`debt_price` decimal(6,2) NULL COMMENT '转让价格' ,
`invest_id` int(10) unsigned NOT NULL COMMENT '上一级投资列表号与lzh_debt_borrow_investor或者lzh_borrow_investor 的ID对应 ',
PRIMARY KEY (`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='债权';


CREATE TABLE `lzh_debt_borrow_investor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
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
  `reward_money` decimal(15,2) NOT NULL,
  `debt_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '债权转让状态 1:等待复审 2:标未满 3：审核未通过，返回  4：审核通过，还款中  5:正常完成  6:网站代还完成 7：逾期还款',
  `debt_uid` int(11) NOT NULL COMMENT '债权转让人ID',
  PRIMARY KEY (`id`),
  KEY `investor_uid` (`investor_uid`,`status`),
  KEY `borrow_id` (`borrow_id`,`investor_uid`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='债权记录投资人信息';

create table `lzh_debt_count`
(
  `id`             int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
  `adddate`        date NOT NULL COMMENT '每日日期' ,
  `count`          int(8) NOT NULL COMMENT '每日转让总笔数',
  primary key (`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='每日债权转让总笔数';

create table `lzh_dict`
(
  `id`                  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
  `number`               char(4) NOT NULL COMMENT '字典编号' ,
 `name`                  varchar(30) NOT NULL COMMENT '字典字段名称' ,
  `value`                varchar(10)  NOT NULL COMMENT '字典字段值' ,
  `mark`                 char(10) COMMENT '字典字段备注',
  primary key (`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='全局数据字典';


create table `lzh_system_setting`
(
  `id`                  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
  `number`                 char(4) NOT NULL COMMENT '系统设置编号',
  `name`                  varchar(30) NOT NULL COMMENT '系统设置字段名称' ,
  `value`                varchar(30)  NOT NULL COMMENT '系统设置字段值' ,
  `mark`                 char(10) COMMENT '系统设置字段备注',
  primary key (`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='全局系统设置';

CREATE TABLE `lzh_debt_borrow_info_lock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `suo` int(10) NOT NULL COMMENT '用于锁表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1826 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='债权转让锁';

CREATE TABLE `lzh_debt_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `borrow_id` int(10) unsigned NOT NULL COMMENT '标号',
  `info` varchar(256) NOT NULL COMMENT '信息',
  `time`  datetime NOT NULL COMMENT '添加时间' ,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='债权转让日志';


CREATE TABLE `lzh_debt_borrow_confirm` (
  `bid` int(11) NOT NULL AUTO_INCREMENT COMMENT '标号',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '债权服务费',
  `fee_status` int(11) NOT NULL DEFAULT '0' COMMENT '债权服务费支付状态',
  `add_time` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='债权服务费';

alter table lzh_investor_detail add debt_borrow_id int(10)  COMMENT '对应债权标的标号';
alter table lzh_investor_detail add `is_debt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是转让标 1 是 0否 默认0';
alter table lzh_investor_detail  MODIFY COLUMN status int(3) COMMENT '-1 对应删除';






