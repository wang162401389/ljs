<?php
return array(
	'REPAYMENT_TYPE'=>array(
			'1'=>'按天计息，一次性还本付息',
			'2'=>'等额本息',
			'3'=>'按季分期还款',
			'4'=>'每月还息到期还本',
			'5'=>'一次性还款',
			'6'=>'利息复投',
			'7'=>'等额本金',
		),
	
	'BORROW_TYPE'=>array(
			'1'=>'信用标',
			'2'=>'担保标',
			'3'=>'秒还标',
			'4'=>'净值标',
			'5'=>'抵押标',
			'6'=>'定投宝',
		),
	
	'IS_REWARD'=>array(
			'1'=>'按投标金额比例奖励',
			'2'=>'按固定金额分摊奖励',
		),

	'BORROW_STATUS'=>array(
			'0'=>'初审待审核',
			'1'=>'初审未通过',
			'2'=>'初审通过，借款中',
			'3'=>'流标',
			'4'=>'标满，复审中',
			'5'=>'复审未通过',
			'6'=>'复审通过，还款中',
			'7'=>'已完成',
			'8'=>'已逾期',
			'9'=>'网站代还',
			'10'=>'逾期还款'
		),

	'BORROW_STATUS_SHOW'=>array(
			'0'=>'初审待审核',
			'1'=>'初审未通过',
			'2'=>'正在招标中',
			'3'=>'流标',
			'4'=>'标满，复审中',
			'5'=>'复审未通过',
			'6'=>'还款中',
			'7'=>'已完成',
			'8'=>'已逾期',
			'9'=>'网站代还',
			'10'=>'逾期还款'
		),
	'DATA_STATUS'=>array(
			'0'=>'待审核',
			'1'=>'审核通过',
			'2'=>'审核未通过'
		),	
	'APPLY_TYPE'=>array(
			'1'=>'借款信用额度',
		),
	'PRODUCT_TYPE'=>array(
			'1'=>'提单质押',
			'3'=>'现货质押',
			'4'=>'融金链',
			'5'=>'分期购',
            '6'=>'信金链',
            '7'=>'优金链',
		    '8'=>'保金链',
            '10'=>'质金链（保）',
		),
);
?>