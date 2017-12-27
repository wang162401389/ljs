# 11 huodong 
# 
INSERT INTO `lzh_global` (`type`, `text`, `name`, `tip`, `order_sn`, `code`, `is_sys`) VALUES
# 20171101 0:0:0
('input', '1509465600', '2017111月活动开始', '11月活动开始', 0, 'start_201711', 0),
# 20171130 23:59:59
('input', '1512057599', '2011-11月活动结束', '11月活动结束', 0, 'end_201711', 0);

CREATE TABLE IF NOT EXISTS `lzh_huodong_201711_count` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `lzh_huodong_201711_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_id` int(11) NOT NULL COMMENT '用户id',
  `invest` int(11) NOT NULL DEFAULT '0' COMMENT '单次投资金额',
  `rebate` decimal(6,2) NOT NULL COMMENT '本次投资奖励',
  `bid` int(11) NOT NULL COMMENT '本次投标id',
  `days` int(11) NOT NULL COMMENT '本次标的的时间，以日为单位',
  `create_time` int(13) NOT NULL COMMENT '投资时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;