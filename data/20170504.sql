/*
SQLyog Ultimate v11.42 (64 bit)
MySQL - 5.6.26-log : Database - sp2p
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `lzh_outside_profit` */

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_store_outside` */

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_recommend_first` */

DROP TABLE IF EXISTS `lzh_recommend_first`;

CREATE TABLE `lzh_recommend_first` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommend_uid` int(11) NOT NULL COMMENT '推荐人ID',
  `recommend_count` int(11) NOT NULL DEFAULT '0' COMMENT '邀请实名人数',
  `coupons_count` int(11) DEFAULT '0' COMMENT '获得投资券数量',
  `experience_money` decimal(15,2) DEFAULT '0.00' COMMENT '体验金',
  `used_money` decimal(15,2) DEFAULT '0.00' COMMENT '已使用体验金',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间，人数增加才更新',
  KEY `id` (`id`),
  KEY `recommend_uid` (`recommend_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_recommend_invest` */

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_recommend_lucky` */

DROP TABLE IF EXISTS `lzh_recommend_lucky`;

CREATE TABLE `lzh_recommend_lucky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `total_count` int(11) NOT NULL DEFAULT '0' COMMENT '抽奖次数',
  `used_count` int(11) NOT NULL DEFAULT '0' COMMENT '已使用次数',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_recommend_prize` */

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

/*Data for the table `lzh_recommend_prize` */

insert  into `lzh_recommend_prize`(`id`,`prize_name`,`prize_count`,`send_count`,`prize_probability`,`active`,`is_edit`) values (1,'1000元现金',0,0,'0.00',0,1),(2,'爱奇艺一月会员',60,0,'4.00',0,0),(3,'10元话费',75,0,'5.00',0,0),(4,'1%加息券',45,0,'3.00',0,0),(5,'50元投资券',75,0,'5.00',0,0),(6,'20元投资券',150,0,'10.00',0,0),(7,'10元投资券',720,0,'48.00',0,0),(8,'跑腿免单',35,0,'5.00',0,0),(9,'谢谢参与',1000,0,'20.00',0,0);

/*Table structure for table `lzh_recommend_seconde` */

DROP TABLE IF EXISTS `lzh_recommend_seconde`;

CREATE TABLE `lzh_recommend_seconde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_type` tinyint(4) NOT NULL COMMENT '1.独占鳌头,2尊享，3普利',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `add_time` int(11) NOT NULL COMMENT '获奖时间',
  `send_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未发放1已返现',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_recommend_winner` */

DROP TABLE IF EXISTS `lzh_recommend_winner`;

CREATE TABLE `lzh_recommend_winner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '中奖uid',
  `prize_id` int(11) NOT NULL COMMENT '奖品ID',
  `add_time` int(11) NOT NULL COMMENT '中奖时间',
  PRIMARY KEY (`id`),
  KEY `prize_id` (`prize_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `lzh_weixin_token` */

DROP TABLE IF EXISTS `lzh_weixin_token`;

CREATE TABLE `lzh_weixin_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL COMMENT '1access_token2ticket',
  `content` text NOT NULL COMMENT '凭证',
  `expires_time` int(11) NOT NULL COMMENT '结束时间错',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
