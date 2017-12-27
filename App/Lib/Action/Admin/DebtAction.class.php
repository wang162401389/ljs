<?php

/**
 * 债权转让后台财务统计
 */
    class DebtAction extends ACommonAction
    {
        /**
         * 债权手续费
         */
       public function index(){
           $map=" where 1=1 ";
           $search=array();
           if(isset($_POST["borrow_id"]) &&$_POST["borrow_id"] ){
               $map.=" and id=".trim($_POST["borrow_id"]);
               $search["borrow_id"]=trim($_POST["borrow_id"]);
           }
           if(isset($_POST["uname"]) &&$_POST["uname"] ){
               $map.=" and user_name=".trim($_POST["uname"]);
               $search["uname"]=trim($_POST["uname"]);
           }
           if(isset($_POST["start_time"]) &&$_POST["start_time"] ){
               $map.="  and second_verify_time >=".strtotime(trim($_POST["start_time"]));
               $search["uname"]=trim($_POST["start_time"]);
           }
           if(isset($_POST["end_time"]) && $_POST["end_time"]){
               $a=trim($_POST["end_time"]);
               $a.=" 23:59:59";
               $map.=" and second_verify_time>=".strtotime($a);
               $search["uname"]=trim($_POST["end_time"]);
           }

           import("ORG.Util.Page");
           $countsql="select count(*) as mycount from (
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_debt_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
union
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
) a {$map} ";
           $ispage=true;
           if($_REQUEST['execl']=="execl"){
               $ispage=false;
           }
           $countlist = M('borrow_info b')->query($countsql);
           $p = new Page($countlist[0]["mycount"],8);
           $show = $p->show();
           $Lsql = "{$p->firstRow},{$p->listRows}";
           if($ispage==true){
               $sql=" select * from (
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_debt_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
union
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
) a {$map} limit $Lsql";

           }else{
               $sql=" select * from (
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_debt_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
union
select g.id,m.`user_name`,g.`borrow_name`,h.debt_borrow_uid as debt_borrow_uid, g.`borrow_interest_rate`,g.`repayment_type`,h.debt_parent_borrow_id as debt_parent_borrow_id,h.borrow_id as old_borrow_id,h.debt_addtime,g.total as total,g.borrow_duration as borrow_duration,
            h.`debt_captial`,h.`debt_totalmoney`,h.`debt_price`,h.`debt_rate`,g.`second_verify_time`,h.`debt_fee`,h.debt_borrow_id from lzh_borrow_debt h
 inner join lzh_borrow_info g on g.id=h.`debt_parent_borrow_id`
 LEFT JOIN lzh_members m ON m.`id`=g.`borrow_uid`
 WHERE (h.debt_status=1)
)a {$map} ";
           }
           $list = M("debt_borrow_info")->query($sql);
           //代收
           foreach ($list as $key=>$value){//代收本金，代收利息,代收期数或者天数
                    if($value["debt_parent_borrow_id"]==$value["old_borrow_id"]){//如果是原始标
                        $mylist=M("borrow_investor t")->query("select sum(investor_capital) as investor_capital  from lzh_borrow_investor t where t.borrow_id={$value["debt_parent_borrow_id"]} and is_debt=0  ");
                    }else{//是债权标再次转让标
                        $mylist=M("debt_borrow_investor t")->query("select sum(investor_capital) as investor_capital from lzh_borrow_investor t where t.borrow_id={$value["debt_parent_borrow_id"]} is_debt=1");
                    }
                    $newlist=M("debt_borrow_info")->where(array("id"=>$value["debt_borrow_id"]))->find();
                    $list[$key]["investor_capital"]=$mylist[0]["investor_capital"];//投资金额
                    $list[$key]["daishou_lixi"]=$value["debt_totalmoney"]-$value["debt_captial"];
                    $list[$key]["daishou_benjin"]=$value["debt_captial"];
                    $list[$key]["daishu_qishu"]=$newlist["borrow_duration"];
                    $list[$key]["daishu_total"]=$value["borrow_duration"];//总期数或者天数
           }
           if($ispage==false) {//不翻页导出
               import("ORG.Io.Excel");
               $row = array();
               $row[0] = array('标号ID', '转让人', '项目名称', '投资金额', '年化利率', '待收期数/天数', '转让期数/天数', '总期数/天数','待收本金','待收利息','债权价值','转让价格','折价率','转让成功时间','手续费（元）');
               $i = 1;
               foreach ($list as $k=> $v){
                   $row[$i]=$v;
                   $i++;
               }
               $xls = new Excel_XML('UTF-8', false, 'zhaiquanzhuanrang');
               $xls->addArray($row);
               $xls->generateXML("tongjifenxi");
               exit;
           }
           $this->assign('page', $show);
           $this->assign("list",$list);
           $this->assign("search",$search);
           $search["execl"]="execl";
           $this->assign("query",http_build_query($search));
           $this->display();
       }
    }
?>
