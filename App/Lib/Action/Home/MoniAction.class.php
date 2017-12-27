<?php
/**
* 
*/
class MoniAction extends HCommonAction
{
	
	/**
	 * 30 天 is_beginnercontract ,是否有5万标,当前最高金额,剩余最大金额
	 * @return [type] [description]
	 */
	public function index()
	{
		// 选择所有周年标
		// '2,4,6,7'
		$zhouniansql = "SELECT id from lzh_borrow_info where is_zhounianbiao = 1 and borrow_status in ('2','4','6','7') ";
		$list = M()->query($zhouniansql);
		$zhounianids = array_column($list, "id");
		$strzhounianid = implode(',', $zhounianids);

		$maxsql = "SELECT borrow_id,FORMAT(max(investor_capital), 2) as `max` from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id";
		$maxlist = M()->query($maxsql);

		$investsql = "SELECT investor_uid,borrow_id,FORMAT(sum(investor_capital), 2) as `sum` from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id";
		$investlist = M()->query($investsql);

		// $winsql = "SELECT investor_uid,borrow_id,sum(investor_capital) as winner from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id,investor_uid ";
  //           $winlist = M()->query($winsql);
  //           exit(json_encode($winlist));
            
  		
  		$winsql = "SELECT a.`investor_uid`,a.`borrow_id`,max(a.`winner`) as winner from  (SELECT investor_uid,borrow_id,sum(investor_capital) as winner from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id,investor_uid) as a where 1 group by a.`borrow_id` ";
            $winlist = M()->query($winsql);
            //exit(json_encode($winlist));



		$sql = "SELECT bi.`borrow_interest_rate`,bi.`borrow_duration`,bi.`id`,bi.`borrow_name`,bi.`borrow_money`,maxt.`max`,invt.`sum`,FORMAT(bi.`borrow_money` - invt.`sum`, 2) as `left`,wint.`winner`  from lzh_borrow_info  as bi, (SELECT borrow_id,FORMAT(max(investor_capital), 2) as `max` from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id) as maxt,(SELECT borrow_id,sum(investor_capital) as `sum` from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id) as invt, (SELECT a.`investor_uid`,a.`borrow_id`,max(a.`winner`) as winner from  (SELECT investor_uid,borrow_id,sum(investor_capital) as winner from lzh_borrow_investor where borrow_id in ({$strzhounianid}) group by borrow_id,investor_uid) as a where 1 group by a.`borrow_id` ) as wint where bi.`id` = maxt.`borrow_id` and maxt.`borrow_id` = invt.`borrow_id` and invt.`borrow_id` = wint.`borrow_id` ";
		$list = M()->query($sql);
		
		import("ORG.Util.PageFilter");
		$search = [];
        $p = new PageFilter(count($list), $search, 10);
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $sql .= "limit $limit";
        $list2 = M()->query($sql);

        $this->assign('search', $search);
        $this->assign("pagebar", $page);
        $this->assign("info", $list2);
        $this->display();
	}

}