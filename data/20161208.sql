lzh_investor_detail 表新增字段
ALTER TABLE `sp2p`.`lzh_investor_detail` ADD COLUMN `jiaxi_rate` DECIMAL(15,2) DEFAULT 0 NULL COMMENT '加息利率' AFTER `jiaxi_money`; 

