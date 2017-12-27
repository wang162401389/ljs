CREATE TABLE `lzh_jiekuan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) DEFAULT NULL COMMENT '用户编号',
  `purpose` varchar(20) DEFAULT NULL COMMENT '借款用途',
  `amount` decimal(6,2) DEFAULT NULL COMMENT '借款金额',
  `deadline` char(15) DEFAULT NULL COMMENT '借款期限名称',
  `addtime` datetime DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态0 默认 1 已处理 2 还款已完结',
  `user_type` tinyint(1) NOT NULL COMMENT '1个人 2 企业',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='借款申请';

CREATE TABLE `lzh_jiekuan_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '外键关联表lzh_jiekuan_personinfo',
  `sort` tinyint(1) NOT NULL COMMENT '联系人需序号 1 ，2,3',
  `relation` varchar(20) DEFAULT NULL COMMENT '联系人与借款人关系',
  `name` varchar(20) DEFAULT NULL COMMENT '联系人姓名',
  `phone` varchar(11) DEFAULT NULL COMMENT '联系人手机号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='联系人表和借款通道中个人信息表关联';

CREATE TABLE `lzh_jiekuan_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL COMMENT '用户编号',
  `realname` varchar(30) NOT NULL COMMENT '真实姓名',
  `idcard` varchar(25) NOT NULL COMMENT '身份证号',
  `id_card_front_pic` varchar(400) NOT NULL COMMENT '正面照大小图',
  `id_card_reverse_pic` varchar(400) NOT NULL COMMENT '反面照大小图',
  `handcard_pic` varchar(400) NOT NULL COMMENT '手持身份证照大小图',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


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
  `idcard` char(20) DEFAULT NULL COMMENT '银行卡号',
  `zhengxin_pic` varchar(40) DEFAULT NULL COMMENT '个人征信报告图片',
  `bank_state` varchar(40) DEFAULT NULL COMMENT '个人银行流水图片',
  `marray` tinyint(1) DEFAULT NULL COMMENT '是否结婚',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='借款通道的个人信息表';


CREATE TABLE `lzh_zhengxin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addtime` int(11) DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT ' 1 征信报告  2银行流水',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;