ALTER TABLE `sp2p`.`lzh_investor_detail` ADD COLUMN `jiaxi_money` DECIMAL(15,2) DEFAULT 0 NULL COMMENT '加息金额' AFTER `is_debt`;
ALTER TABLE `sp2p`.`lzh_borrow_info` ADD COLUMN `jiaxi_rate` DECIMAL(15,2) DEFAULT 0 NULL COMMENT '标的加息百分比' AFTER `test`; 

