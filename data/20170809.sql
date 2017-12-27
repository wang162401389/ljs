#消耗次数
ALTER TABLE lzh_vc_count ADD consume_no int(11) default 0;
ALTER TABLE lzh_vc_count ADD prize varchar(200) default "";
ALTER TABLE lzh_vc_count ADD log_ids text ;

DROP TABLE IF EXISTS `lzh_vc_tmp`;
CREATE TABLE `lzh_vc_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL DEFAULT '0' COMMENT '已经处理的log_id',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '标记类型 type = 101 从dream_log 转换type 101 的操作',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


INSERT INTO `lzh_vc_tmp` (`log_id`, `type`) VALUES (0, '101');

INSERT INTO `lzh_cps_index` (`name`, `code`, `value`, `created_at`) VALUES
('网贷东方', 'wddf', 7, 0);

