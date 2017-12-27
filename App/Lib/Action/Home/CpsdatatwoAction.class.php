<?php
/**
* 
*/
class CpsdatatwoAction extends HCommonAction
{
	
	public function showcpstwo()
    {

        if (!empty($_REQUEST['code'])) {
            $search['code'] = htmlspecialchars(trim($_REQUEST['code']));

	        $sql = "SELECT mf.`id`,mf.`user_name`,mi.`real_name` as  pf_user_name,mf.`reg_time`,FROM_UNIXTIME(bi.`add_time`) AS tou_time,b.`borrow_name`,b.`borrow_duration_txt`,bi.`investor_capital`,mf.`equipment` FROM lzh_members mf
	                    INNER JOIN lzh_borrow_investor bi ON bi.`investor_uid` = mf.`id`
	                    INNER JOIN lzh_borrow_info b ON b.`id` = bi.`borrow_id`
	                    INNER JOIN lzh_member_info mi on mi.`uid` = mf.`id` where mf.`equipment` = '{$search['code']}' order by tou_time  desc ";

	        $list = M()->query($sql);
	        import("ORG.Util.PageFilter");
	        $p = new PageFilter(count($list), $search, 10);
	        $page = $p->show();
	        $limit = "{$p->firstRow},{$p->listRows}";
	        $sql .= "limit $limit";
	        $list = M()->query($sql);

	        $this->assign('search', $search);
	        $this->assign("pagebar", $page);
	        $this->assign("info", $list);
	        
        }

        $this->assign("cpstwo",C("BLOCK_CPSTWO"));
        $this->display();
    }
}