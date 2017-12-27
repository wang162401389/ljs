##　201708 9月份活动

# 创建 pc_count

DROP TABLE IF EXISTS `lzh_p9_count`;
CREATE TABLE IF NOT EXISTS `lzh_p9_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '剩余砸冰块的机会数',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '抢券次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `lzh_p9_count2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_phone` varchar(20) NOT NULL COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL COMMENT '被推荐人注册时间',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '砸冰块的机会数',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '抢券次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `lzh_p9_prize`;
CREATE TABLE IF NOT EXISTS `lzh_p9_prize` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='奖品库存表' AUTO_INCREMENT=1 ;

INSERT INTO `lzh_p9_prize` (`id`, `info`, `mark`, `type`, `active_type`, `value`, `odds_0`, `minnum_0`, `maxnum_0`, `num_total`, `num_left`, `angle`, `status`) VALUES
(49, '1元', '1元', 3, 1, '1.00', '10.60', 0, 1059, 2000, 1995, 300, 0),
(50, '2元', '2元', 3, 1, '2.00', '35.40', 1060, 4599, 3000, 2993, 360, 0),
(51, '2.8元', '2.8元', 3, 1, '2.80', '14.00', 4600, 5999, 10000, 9995, 60, 0),
(52, '3元', '3元', 3, 1, '3.00', '20.30', 6000, 8029, 10000, 9998, 120, 0),
(53, '4元', '4元', 3, 1, '4.00', '10.70', 8030, 9099, 10000, 9997, 240, 0),
(54, '5元', '4元', 3, 1, '5.00', '0.00', 0, 0, 10000, 10000, 180, 0),
(55, '99元', '99元', 3, 1, '99.00', '8.30', 9100, 9929, 10000, 9998, 180, 0),
(56, '199元元', '199元', 3, 1, '199.00', '0.70', 9930, 10000, 10000, 30000, 180, 0),
(57, 'ipad pro', 'ipad pro', 4, 2, '4800.00', '0.30', 0, 29, 10000, 25, 1, 0),
(58, '索尼榨汁机', '索尼榨汁机', 4, 2, '799.00', '0.70', 30, 99, 10000, 10000, 2, 0),
(59, '美的榨汁机', '美的榨汁机', 4, 2, '300.00', '3.00', 100, 399, 10000, 9994, 3, 0),
(60, '小米手环', '小米手环', 4, 2, '150.00', '3.00', 400, 699, 10000, 10000, 4, 0),
(61, '运动毛巾', '运动毛巾', 4, 2, '60.00', '7.00', 700, 1399, 10000, 9993, 5, 0),
(62, '手持小风扇', '手持小风扇', 4, 2, '50.00', '40.00', 1400, 5399, 10000, 9992, 6, 0),
(63, '10元投资券', '10元投资券', 0, 2, '10.00', '3.00', 5400, 5699, 10000, 9998, 7, 0),
(64, '6元投资券', '6元投资券', 0, 2, '6.00', '5.00', 5700, 6199, 10000, 9986, 8, 0),
(65, '5元投资券', '5元投资券', 0, 2, '5.00', '5.00', 6200, 6699, 10000, 9988, 9, 0),
(66, '8.88元现金', '8.88元现金', 3, 2, '8.88', '5.00', 6700, 7199, 10000, 9996, 10, 0),
(67, '6.66元现金', '6.66元现金', 3, 2, '6.66', '8.00', 7200, 7999, 10000, 9999, 11, 0),
(68, '1.88元现金', '1.88元现金', 3, 2, '1.88', '10.00', 8000, 8999, 10000, 9983, 12, 0),
(69, '0.5%加息券', '0.5%加息券', 5, 3, '0.50', '40.00', 0, 3999, 40, 1, 300, 0),
(70, '1%加息券', '1%加息券', 5, 3, '1.00', '30.00', 4000, 6999, 30, 1, 360, 0),
(71, '1.5%加息券', '1.5%加息券', 5, 3, '1.50', '20.00', 7000, 8999, 20, 1, 60, 0),
(72, '2%加息券', '2%加息券', 5, 3, '2.00', '10.00', 9000, 10000, 10, 1, 60, 0),
(73, '谢谢参与', '谢谢参与', 6, 2, '0.00', '10.00', 9000, 10000, 10000, 19998, 0, 0);





CREATE TABLE IF NOT EXISTS `lzh_p9_win` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO `lzh_global` (`type`, `text`, `name`, `tip`, `order_sn`, `code`, `is_sys`) VALUES
('input', '1491786000', '9月活动开始', '9月活动开始', 0, 'p9_start', 0),
('input', '13828729631', '9月活动结束', '9月活动结束', 0, 'p9_end', 0);


INSERT INTO `lzh_vc_tmp` (`log_id`, `type`) VALUES
# p9_count2
(0, 103),
# p9_count
(0, 104);

# 增加定时任务,每日01:00 刷新抢票次数
0 1 * * * {url/to/ccfax/home/pro9/refreshqiang?username=lushixin&pwd=lushixin}

# 清理数据库缓存