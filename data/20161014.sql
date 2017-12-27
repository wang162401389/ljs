CREATE TABLE `lzh_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户编号',
  `uname` varchar(12) DEFAULT NULL COMMENT '用户名称',
  `phone` char(11) NOT NULL COMMENT '手机号',
  `registertime` date NOT NULL COMMENT '注册时间',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '活动期间累计投资',
  `priceid` int(10) DEFAULT NULL COMMENT '奖品编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='周年庆活动';

CREATE TABLE `lzh_activity_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `goodsname` varchar(30) NOT NULL COMMENT '周年庆奖品名称',
  `mark` varchar(100) DEFAULT NULL COMMENT '奖品备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='周年庆奖品';

-- ----------------------------
-- Records of lzh_activity_price
-- ----------------------------
INSERT INTO `lzh_activity_price` VALUES ('1', '塞班岛至尊行程深度美食5晚7日双人游', null);
INSERT INTO `lzh_activity_price` VALUES ('2', 'Iphone7 plus(32G 颜色随机）', null);
INSERT INTO `lzh_activity_price` VALUES ('3', 'Iphone7 (32G 颜色随机）', null);
INSERT INTO `lzh_activity_price` VALUES ('4', 'Iphone6s(64G 颜色随机）', null);
INSERT INTO `lzh_activity_price` VALUES ('5', 'Apple Watch 运动版', null);
INSERT INTO `lzh_activity_price` VALUES ('6', 'IPAD mini2 32G', null);
INSERT INTO `lzh_activity_price` VALUES ('7', '小米空气净化器2代', null);
INSERT INTO `lzh_activity_price` VALUES ('8', '小米20000mAh', null);
INSERT INTO `lzh_activity_price` VALUES ('9', '小米充电宝10000mAh', null);

CREATE TABLE `lzh_activity_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `money` decimal(10,2) NOT NULL COMMENT '累计投资，万元为单位',
  `goodsid` int(11) unsigned NOT NULL COMMENT '奖品编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='周年庆活动奖励规则表';

-- ----------------------------
-- Records of lzh_activity_rule
-- ----------------------------
INSERT INTO `lzh_activity_rule` VALUES ('1', '150.00', '1');
INSERT INTO `lzh_activity_rule` VALUES ('2', '80.00', '2');
INSERT INTO `lzh_activity_rule` VALUES ('3', '60.00', '3');
INSERT INTO `lzh_activity_rule` VALUES ('4', '50.00', '4');
INSERT INTO `lzh_activity_rule` VALUES ('5', '30.00', '5');
INSERT INTO `lzh_activity_rule` VALUES ('6', '20.00', '6');
INSERT INTO `lzh_activity_rule` VALUES ('7', '10.00', '7');
INSERT INTO `lzh_activity_rule` VALUES ('8', '1.00', '8');
INSERT INTO `lzh_activity_rule` VALUES ('9', '0.50', '9');