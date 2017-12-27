<?php
// 回收中的投资视图模型
class TendingListViewModel extends ViewModel
{
    public $viewFields = array(
        'BorrowInvestor'=>array('id','status','borrow_id','investor_uid','borrow_uid','investor_capital','investor_interest','receive_capital','receive_interest','substitute_money','expired_money','invest_fee','paid_fee','add_time'=>'invest_time','deadline','deadline'=>'times','is_auto','reward_money','debt_id','_type'=>'LEFT'),
         'Borrow'=>array('id'=>'borrowid','borrow_name','borrow_duration','borrow_money','borrow_interest','borrow_interest_rate','product_type',
         'borrow_fee','has_borrow','borrow_times','repayment_money','repayment_interest','expired_money','repayment_type','borrow_type',
         'borrow_status','borrow_use','add_time'=>'borrow_time','collect_day','collect_time','full_time','first_verify_time','second_verify_time',
         'add_ip','borrow_info','total','has_pay','substitute_money','reward_vouch_rate','reward_vouch_money','reward_type','reward_num',
         'reward_money','borrow_min','borrow_max','province','city','area','vouch_member','has_vouch','password','is_tuijian','can_auto',
         'is_huinong','updata','jiaxi_rate'=>'jx_rate','_on'=>'Borrow.id=BorrowInvestor.borrow_id','_type'=>'LEFT'),
        'Members'=>array('user_name'=>'borrow_user','user_phone'=>'userphone','credits','_on'=>'Members.id=Borrow.borrow_uid','_type'=>'LEFT'),
         'Invest_detb'=>array('status'=>'detb_status','period','_on'=>'Invest_detb.invest_id=BorrowInvestor.id','_type'=>'LEFT'),
         'DebtBorrowInfo'=>array('id'=>'debt_id','borrow_name'=>'debt_name','borrow_money'=>'debt_money','add_time'=>'debt_time','borrow_duration'=>'debt_duration','borrow_duration_txt'=>'debt_date','_on'=>'DebtBorrowInfo.old_borrow_id=Borrow.id AND BorrowInvestor.debt_id = DebtBorrowInfo.id','_type'=>'LEFT'),

    );
}
