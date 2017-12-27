##　20170515 周一上线内容
## 1.投资券赠送增加　admin_id admin_name
## 2.借款人后台借款列表增加借款合同
## 3.推荐查看


ALTER TABLE lzh_coupons ADD admin int(11) default 0;
ALTER TABLE lzh_coupons ADD admin_name varchar(100) default "";


## 历史投资统计表
CREATE TABLE `lzh_invest_aggregate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户UID',
  `borrow_investor_id` int(11) NOT NULL DEFAULT '0' COMMENT 'borrow_investor_id',
  `first_invest_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '首次投资金额',
  `firstmonth_invest_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '首次投资金额',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '首次投资时间',
  `complete` int(11) NOT NULL DEFAULT '0' COMMENT '是否完成',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

## 上线步骤
#１．倒入数据
#２．home/aggregate/migrate
#3. home/aggregate/recursive
#4. 加入定时任务 home/synchronized/aggregate  每个月　１　２　３　４　５　凌晨