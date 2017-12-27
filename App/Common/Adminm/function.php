<?php

function borrow_status($borrow_id , $status=0)
{
    switch($status){
        case 0:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#48A4CE;border-color:#48A4CE"  href="'.U('Adminm/Mborrow/edit0', array('id'=>$borrow_id)).'">初审</a> ';
            break;
        case 4:
            $href =  '<a  type="button" class="btn btn-info "  style="background-color:#48A4CE;border-color:#48A4CE"  href="'.U('Adminm/Mborrow/edit', array('id'=>$borrow_id)).'" >复审</a> ';
            break;
        case 6:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC" >还款中</a> ';
            break;
        default:
            $href =  '<a  type="button" class="btn btn-info" style="background-color:#CCC;border-color:#CCC"  >已结束</a> ';
    }

    return $href;
}

function repayment_info($repayment_type){
    switch($repayment_type){
        case 1:
            $info="按天计息，一次性还本付息";
            break;
        case 2:
            $info="按月分期还款";
            break;
        case 3:
            $info="按季分期还款";
            break;
        case 4:
            $info="每月还息到期还本";
            break;
        case 5:
            $info="一次性还款";
            break;
        case 6:
            $info="利息复投";
            break;
    }
    return $info;
}

?>