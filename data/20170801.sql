##　201708 8月份活动

# 创建 vc_count
#  vc count 记录抽奖次数
# id count_0  count_1 count_2 count_3 count_4
#

DROP TABLE IF EXISTS `lzh_vc_count`;
CREATE TABLE `lzh_vc_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `count_0` int(11) NOT NULL DEFAULT '0' COMMENT '1档抽奖次数(100-1000)',
  `count_1` int(11) NOT NULL DEFAULT '0' COMMENT '2档抽奖次数(1001-5000)',
  `count_2` int(11) NOT NULL DEFAULT '0' COMMENT '3档抽奖次数(5001-20000)',
  `count_3` int(11) NOT NULL DEFAULT '0' COMMENT '4档抽奖次数(20001-100000)',
  `count_4` int(11) NOT NULL DEFAULT '0' COMMENT '5档抽奖次数100000以上',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `lzh_vc_prize` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='奖品库存表' ;

INSERT INTO `lzh_vc_prize` (`id`, `info`, `mark`, `type`, `value`, `odds_0`, `odds_1`, `odds_2`, `odds_3`, `odds_4`, `minnum_0`, `maxnum_0`, `minnum_1`, `maxnum_1`, `minnum_2`, `maxnum_2`, `minnum_3`, `maxnum_3`, `minnum_4`, `maxnum_4`, `angle`) VALUES
                           (1, '1元投资券', '1元投资券', 0,   '1.00',  '5.00', '50.00', '30.00', '20.00', '20.00', 0, 499, 0, 4999, 0, 2999, 0, 1999, 0, 1999, 300),
                           (2, '5元投资券', '5元投资券', 0,   '5.00',  '0.00', '10.00', '30.00', '30.00', '40.00', 0, 0, 5000, 5999, 3000, 5999, 2000, 5999, 2000, 5999, 360),
                           (3, '15元投资券', '15元投资券', 0, '15.00', '0.00', '0.00', '3.00', '10.00', '25.00', 0, 0, 0, 0, 6000, 6299, 5000, 5999, 6000, 8499, 60),
                           (4, '25元投资券', '25元投资券', 0, '25.00', '0.00', '0.00', '0.00', '6.00', '10.00', 0, 0, 0, 0, 0, 0, 6000, 6599, 8500, 9499, 120),
                           (5, '55元投资券', '55元投资券', 0, '55.00', '0.00', '0.00', '0.00', '0.00', '3.00', 0, 0, 0, 0, 0, 0, 0, 0, 9500, 9799, 240),
                           (6, '谢谢参与', '谢谢参与', 1,      '0.00', '95.00', '40.00', '37.00', '34.00', '2.00', 500, 10000, 6000, 10000, 6300, 10000, 6600, 10000, 9800, 10000, 180);




DROP TABLE IF EXISTS `lzh_vc_recom`;
CREATE TABLE `lzh_vc_recom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '被推荐人用户id',
  `user_phone` varchar(20) NOT NULL  COMMENT '被推荐人用户手机号',
  `parent_id` int(11) NOT NULL COMMENT '被推荐人用户id',
  `invest_money` int(11) NOT NULL DEFAULT '0' COMMENT '被推荐人投资金额',
  `create_time` int(13) NOT NULL  COMMENT '被推荐人注册时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


# /home/vc/getrange
# /home/vc/rockandroll
# dream_log kickback 103
# dream_log invest 102 no vc  102 invest
# dream_log dice  101





