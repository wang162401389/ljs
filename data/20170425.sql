#ＣＰＳ接入

#模式一
#PC端:https://miror.ccfax.cn/member/common/register/utm_source/testsource/uid/0.html
#移动端:https://miror.ccfax.cn/M/pub/regist/utm_source/test_sourc2/uid/0.html

#模式二
#PC端:https://miror.ccfax.cn/member/common/register/utm_source/testsource/uid/0/mode/2.html
#移动端:https://miror.ccfax.cn/M/pub/regist/utm_source/test_sourc2/uid/0/mode/2.html

#创建表 lzh_cps_index

/*
Navicat MySQL Data Transfer

Source Server         : 172.16.20.42
Source Server Version : 50626
Source Host           : 172.16.20.42:3306
Source Database       : sp2p

Target Server Type    : MYSQL
Target Server Version : 50626
File Encoding         : 65001

Date: 2017-04-25 15:54:54
*/

SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lzh_cps_index
-- ----------------------------
INSERT INTO `lzh_cps_index` VALUES ('1', '富爸爸', 'fubaba', '1', '0');
INSERT INTO `lzh_cps_index` VALUES ('3', '融普惠', 'rph', '3', '0');
INSERT INTO `lzh_cps_index` VALUES ('4', '搜利网', 'souli', '4', '0');
INSERT INTO `lzh_cps_index` VALUES ('5', '银桥', 'yinqiao', '5', '0');
SET FOREIGN_KEY_CHECKS=1;


#后期的介入规则
#1.根据CPS供应商提供链接
#2.如果涉及到回调，增加回调函数
#３．数据库增加CPS供应商记录