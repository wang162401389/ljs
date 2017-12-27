ALTER TABLE `lzh_members`  ADD COLUMN `user_img` varchar(50) DEFAULT NULL COMMENT '用户头像图片路劲';

CREATE TABLE `lzh_partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_txt` varchar(50) NOT NULL COMMENT '链接文字',
  `link_href` varchar(500) NOT NULL COMMENT '链接地址',
  `link_img` varchar(100) NOT NULL DEFAULT ' ' COMMENT '链接图片',
  `link_order` int(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `link_type` int(1) NOT NULL DEFAULT '0' COMMENT '显示位置',
  `is_show` int(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='合作伙伴表';
