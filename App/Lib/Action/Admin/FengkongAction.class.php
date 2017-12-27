<?php
// 全局设置
class FengkongAction extends ACommonAction
{
    private function is_ajax(){
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            return 1;
        }
        else
            return 0;
    }

    private function  do_tongdun_submit(){

        if(session("tongdun_token")!=htmlspecialchars($_POST['token'])){
            echo "false";
            exit;
        }
        else{
            $name=htmlspecialchars(trim($_POST['user_name']));
            $user_tel=htmlspecialchars(trim($_POST['user_tel']));
            $user_id=htmlspecialchars(trim($_POST['user_id']));
            $tongdun= new tongdun();
            $id=$tongdun->submit($name, $user_tel, $user_id);
            if($id==0){
                echo "fail";
            }else{
                echo "ok";
            }
            exit;
        }
    }
    private  function  do_tongdun_query(){
        $tongdun= new tongdun();
        $name=htmlspecialchars(trim($_POST['user_name']));
        $user_tel=htmlspecialchars(trim($_POST['user_tel']));
        $user_id=htmlspecialchars(trim($_POST['user_id']));
        $result=$tongdun->get_user_result($name, $user_tel, $user_id);
        echo $result;
        exit;
    }

    public function tongdun(){
        import("@.fengkong.tongdun");

        if($this->is_ajax()){
            $method=htmlspecialchars($_POST['method']);
            if($method=="submit"){
                $this->do_tongdun_submit();
              //  echo "ok";exit;
            }
            else
                $this->do_tongdun_query();
        }
        else{
            $token=uniqid();
            session("tongdun_token",$token);
            $this->assign("token",$token);
            $this->display();
        }
    }


    /***********************************
     * 为风控添加财务对账功能
     */
    public  function  pay_borrow_info(){
        if(!empty($_REQUEST['uname'])){
            $search['m.user_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['uid'])){
            $search['b.borrow_uid'] =intval($_REQUEST['uid']);
            $map['uid']=intval($_REQUEST['uid']);
        }
        if(!empty($_REQUEST['borrow_id'])){
            $search['b.id'] = intval($_REQUEST['borrow_id']);
            $map['borrow_id']=intval($_REQUEST['borrow_id']);
        }
        $renumber = C('RENUMBER_BORROW.new_grade');
        if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $where['b.product_type']= array('in','1,2,3');
                $where['b.id'] = array('lt',$renumber);
                $where['_logic'] = 'or';
                $search['_complex'] = $where;
            }else if($_REQUEST['protype']==2){
                $search['b.product_type']= array('eq','4');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $search['b.product_type']=array('eq','6');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $search['b.product_type']=array('eq','7');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $search['b.product_type']=array('eq','8');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $search['b.product_type']=array('eq','10');
                $search['b.id']=array('egt',$renumber);
            }
            $map['protype']= $_REQUEST['protype'];
        }

        import("ORG.Util.PageFilter");
        $search['frist_time']=array("neq",0);//不等于0表示开始还款
        $search['end_time']=0;
        $search['repayment_type']=1;
        $count =M("borrow_info_additional a")->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($search)->count('bid');
        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $info=D("borrow_info_additional")->get_borrow_info(0,$limit,$search);

        //计算本页的总数
        $total=0;
        foreach($info as $key=>$val){
            $total+=$val['cost'];
        }
        foreach($info as $k => $v){
            $info[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("info",$info);
        $this->assign("total",$total);
        $this->assign("pagebar", $page);
        $this->assign("last_des","截止今天应还款");
        $this->assign('xaction',"pay_borrow_info");
        $this->display();
    }

    /****************
     * 还款结束
     */
    public  function end_borrow_info(){
        if(!empty($_REQUEST['uname'])){
            $search['m.user_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        if(!empty($_REQUEST['uid'])){
            $search['b.borrow_uid'] =intval($_REQUEST['uid']);
            $map['uid']=intval($_REQUEST['uid']);
        }
        if(!empty($_REQUEST['borrow_id'])){
            $search['b.id'] = intval($_REQUEST['borrow_id']);
            $map['borrow_id']=intval($_REQUEST['borrow_id']);
        }
        $renumber = C('RENUMBER_BORROW.new_grade');
        if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $where['b.product_type']= array('in','1,2,3');
                $where['b.id'] = array('lt',$renumber);
                $where['_logic'] = 'or';
                $search['_complex'] = $where;
            }else if($_REQUEST['protype']==2){
                $search['b.product_type']= array('eq','4');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $search['b.product_type']=array('eq','6');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $search['b.product_type']=array('eq','7');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $search['b.product_type']=array('eq','8');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $search['b.product_type']=array('eq','10');
                $search['b.id']=array('egt',$renumber);
            }
            $map['protype']= $_REQUEST['protype'];
        }

        import("ORG.Util.PageFilter");
        $search['frist_time']=array("neq",0);//不等于0表示开始还款
        $search['end_time']=array("neq",0);
        $search['repayment_type']=1;
        $count =M("borrow_info_additional a")->join("lzh_borrow_info b on b.id=a.bid")->join("lzh_members m on m.id=b.borrow_uid")->where($search)->count('bid');
        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $limit = "{$p->firstRow},{$p->listRows}";
        $info=D("borrow_info_additional")->get_borrow_info(1,$limit,$search);

        //计算本页的总数
        $total=0;
        foreach($info as $key=>$val){
            $total+=$val['cost'];
        }
        foreach($info as $k => $v){
            $info[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("last_des","最终还款");
        $this->assign("info",$info);
        $this->assign("total",$total);
        $this->assign("pagebar", $page);
        $this->assign('xaction',"end_borrow_info");
        $this->display("Fengkong:pay_borrow_info");
    }
    /****************************************************
     *
     */
    public function answer_borrow(){
        if($this->is_ajax()){
            $id=intval($_POST['id']);
            $status=intval($_POST['status']);
            if($status==1){
                $data['apply_status']=2;
                $where['id']=$id;
                M("borrow_info")->where($where)->save($data);
                import("@.sms.Notice");
                $notice=new Notice();
                $notice->replay($id);
                $notice->agreen_reply($id);
                cancelDebt($id);
                echo "ok";
            }else if($status==2){
                $data['apply_status']=0;
                $where['id']=$id;
                M("borrow_info")->where($where)->save($data);
                import("@.sms.Notice");
                $notice=new Notice();
                $notice->disagreen_reply($id);
                echo "ok";
            }

            exit;
        }
        $this->assign("last_des","应还款");
        $info=D("borrow_info_additional")->get_replay_borrow();
        foreach($info as $k => $v){
            $info[$k]['bid']=borrowidlayout1($v['id']);
        }
        $this->assign("info",$info);
        $this->display();
    }

    public function company_list(){
        if(!empty($_REQUEST['uname'])){
            $search['c.company_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }
        $info=D("Members_company")->getCompanyList();
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        //过滤
        $info1=array();
        foreach($info as $key=>$val){
            if(($key>=$min)&&($key<$max)){
                $info1[]=$val;
            }
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"company_list");
        $this->assign("info",$info1);
        $this->display();
    }
    public function  set_danbao(){
        $info=D("Members_company")->set_danbao(intval($_POST['uid']),getFloatValue($_POST['money'],2));
        if($info){
            echo "ok";
        }else{
            echo "fail";
        }
        exit;
    }
    public function danbao_list(){
        $search=array();
        $map=array();
        if(!empty($_REQUEST['uname'])){
            $search['c.company_name'] =array('like',"%".$_REQUEST['uname']."%");
            $map['uname']=$_REQUEST['uname'];
        }

        $info=D("Members_company")->get_danbao_info();
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        //过滤
        $info1=array();
        foreach($info as $key=>$val){
            if(($key>=$min)&&($key<$max)){
                $info1[]=$val;
            }
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"danbao_list");
        $this->assign("info",$info1);
        $this->display();
    }
    public function danbo_des(){
        $search=array();
        $map=array();

        $where["danbao"]=intval($_GET['uid']);
        $type=intval($_GET['type']);
        if($type==1){
            $where['borrow_status']=array("in",array(2,4,6));
        }
        else if($type==2){
            $where['borrow_status']=array("egt",6);
        }
        $info=M("borrow_info")->field("id,borrow_name,borrow_duration,borrow_money,vouch_money,second_verify_time,repayment_type")->where($where)->select();
        $count=count($info);
        import("ORG.Util.PageFilter");

        $p = new PageFilter($count,$map,C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $min =$p->firstRow;
        $max=$p->listRows+$min;
        //过滤
        $info1=array();
        $total=0;
        $vouch_money=0;
        foreach($info as $key=>$val){
            if(($key>=$min)&&($key<$max)){
                if($val['repayment_type']==1){
                    $val['borrow_duration']=$val['borrow_duration']."天";
                }else{
                    $val['borrow_duration']=$val['borrow_duration']."月";
                }
                $val['second_verify_time']=date("Y-m-d",$val['second_verify_time']);
                $info1[]=$val;
            }
            $total=getFloatValue(($total+$val['borrow_money']),2);
            $vouch_money=getFloatValue(($vouch_money+$val['vouch_money']),2);
        }
        $this->assign("pagebar", $page);
        $this->assign('xaction',"danbo_des");
        $this->assign("total",$total);
        $this->assign("vouch_money",$vouch_money);
        $this->assign("info",$info1);
        $this->display();
    }


    /***所以标的列表***/
    public function borrowlist(){
        $map['b.borrow_status'] = array("in","6,7,9");//还款中
        if(!empty($_REQUEST['uname'])){
            $map['b.borrow_uid'] =$this->find_name($_REQUEST['uname']);
            //$search['uid'] = $map['b.borrow_uid'];
            $search['uname'] = $_REQUEST['uname'];
        }
        $renumber = C('RENUMBER_BORROW.new_grade');
        if (!empty($_REQUEST['protype'])) {
            if($_REQUEST['protype']==1){
                $check['b.product_type']= array('in','1,2,3');
                $check['b.id'] = array('lt',$renumber);
                $check['_logic'] = 'or';
                $map['_complex'] = $check;
            }else if($_REQUEST['protype']==2){
                $map['b.product_type']= array('eq','4');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==3){
                $map['b.product_type']=array('eq','6');
                $map['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==4){
                $search['b.product_type']=array('eq','7');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==5){
                $search['b.product_type']=array('eq','8');
                $search['b.id']=array('egt',$renumber);
            }else if($_REQUEST['protype']==6){
                $search['b.product_type']=array('eq','10');
                $search['b.id']=array('egt',$renumber);
            }
            $search['protype']= $_REQUEST['protype'];
        }
        if(!empty($_REQUEST['uid'])){
            $search['uid'] =$_REQUEST['uid'];
            $borrowid = $this->bidsousuo($_REQUEST['borrow_id']);
            $map['b.id'] = intval($borrowid);
        }       
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['b.deadline'] = array("between",$timespan);
            $search['start_time'] = urldecode($_REQUEST['start_time']);
            $search['end_time'] = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['b.deadline'] = array("gt",$xtime);
            $search['start_time'] = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['b.deadline'] = array("lt",$xtime);
            $search['end_time'] = $xtime;
        }

        //分页处理
        import("ORG.Util.PageFilter");
        $count = M('borrow_info b')->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->count('b.id');
        $p = new PageFilter($count,$search, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        if($_REQUEST['execl']=="execl"){
            $Lsql =0;
        }
        //分页处理
        $field= 'm.id as mid,m.customer_name,b.id,b.borrow_name,b.borrow_uid,b.borrow_duration,b.borrow_type,b.product_type,b.borrow_money,b.borrow_interest,b.repayment_money,b.product_type,b.danbao,b.repayment_interest,b.borrow_fee,b.borrow_interest_rate,b.repayment_type,b.deadline,m.user_name,m.user_phone,b.is_tuijian,b.money_collect';
        $list = M('borrow_info b')->field($field)->join("{$this->pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order("b.id DESC")->select();
        
        $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
        $listType = $Bconfig['REPAYMENT_TYPE'];
        
        foreach ($list as $k => $v) {
            $vx = M('investor_detail')->field('deadline,sort_order,status')->where(" borrow_id={$v['id']} AND status in(4,7) ")->order("deadline ASC")->find();
            if($list[$k]['repayment_type']==1){
                $list[$k]['borrow_duration'].="天";
            }else{
                $list[$k]['borrow_duration'].="月";
            }
            $list[$k]['repayment_time'] = $vx['deadline'];
            $list[$k]['sort_order'] = $vx['sort_order'];
            $list[$k]['auto'] = "auto";
            $list[$k]['repayment_type'] = $listType[$v['repayment_type']];
            $need = M('investor_detail')->field(' sum(capital + interest) as need')->where(" borrow_id={$v['id']} AND deadline=$vx[deadline] ")->find();
            $list[$k]['need_money'] = $need['need'];
        }
        foreach($list as $k => $v){
            $list[$k]['bid']=borrowidlayout1($v['id']);
            if($v['product_type'] == 5){
                $fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
                $list[$k]['borrow_fee'] = $fee['fee'];    
            }else{
                $list[$k]['borrow_fee'] = $v['borrow_fee'];    
            }
        }
        if($_REQUEST['execl']=="execl"){
            import("ORG.Io.Excel");
            alogs("Fengkong",0,1,'执行了导出借款合同列表！');//管理员操作日志
            $row=array();
            $row[0]=array('标号','用户名','手机号','客服','标题','借款金额','已还金额','借款期限','借款手续费','还款方式','最近还款时间');
            $i=1;
            foreach($list as $v){
                $row[$i]['bid'] = $v['bid'];
                $row[$i]['uname'] = $v['user_name'];
                $row[$i]['user_phone'] = $v['user_phone'];
                $row[$i]['customer_name'] = $v['customer_name'];
                $row[$i]['borrow_name'] = $v['borrow_name'];
                $row[$i]['borrow_money'] = $v['borrow_money'];
                $row[$i]['repaymented'] = $v['repayment_money']+$v['repayment_interest'];
                $row[$i]['borrow_duration'] = $v['borrow_duration'];
                if($v['product_type'] == 5){
                    $fee = M('allwood_ljs')->where("borrow_id = {$v['id']}")->find();
                    $row[$i]['borrow_fee'] = $fee['fee'];    
                }else{
                    $row[$i]['borrow_fee'] = $v['borrow_fee'];    
                }
                
                $row[$i]['repayment_type'] = $v['repayment_type'];
                $row[$i]['deadline'] = isset($v['deadline']) ? date("Y-m-d",$v['deadline']) : '-';
                $i++;
            }
            $xls = new Excel_XML('UTF-8', false, 'borrowlist');
            $xls->addArray($row);
            $xls->generateXML("borrowlist");
            exit;               
        }
        $this->assign("bj", array("gt"=>'大于',"eq"=>'等于',"lt"=>'小于'));
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $search['execl']="execl";
        $this->assign("xaction","borrowlist");
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    private function  find_name($name){
        $map['user_name']=array("like","%".$name."%");
        $uid=M("members")->where($map)->field('id')->select();
        $list=array();
        foreach($uid as $key=>$val){
            $list[]=$val['id'];
        }
        $mine="综合服务费";
        if(strstr($mine,$name)){
            $list[]=0;
        }
        return array("in",$list);
    }
    
    /***差看合同***/
    public function  showhetong(){
        $per = C('DB_PREFIX');
        $uid=intval($_GET['uid']);
        $borrow_id=intval($_GET['id']);
        // if($borrow_id > C("SHANG_HETONG"))
        //  {
        //     $shanglist = M("shangshang")->where("borrow_id = ".$borrow_id)->find();
        //     if($shanglist){
        //         import("@.Oauth.ancun.Shang");
        //         $shang = new Shang();
        //         $rs = $shang->gethetong($shanglist["sign_id"],$shanglist["doc_id"]);
        //         redirect($rs["resultText"]["url"]);
        //     }else{
        //         $this->error("合同生成中，请稍候再试");
        //     }
        //  }else{
            //$invest_id=intval($_GET['id']);
            // show_contract($borrow_id);
            //所以投标记录
            $iinfos = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->select();
            //标详情
            $binfo = M('borrow_info')->field('id,borrow_use,repayment_type,borrow_duration,borrow_uid,borrow_type,borrow_use,borrow_money,full_time,add_time,borrow_interest_rate,deadline,second_verify_time,warehousing,borrow_duration_txt,product_type')->find($borrow_id);
            //借款人信息
            $mBorrow = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_regtype,m.user_name,mi.idcard')->where("m.id=$uid")->find();
            if(empty($mBorrow["real_name"])){
                 $co_info=M('members_company mc')->field('mc.company_name,mc.license_no')->where("mc.uid={$uid}")->find();
                 $mBorrow["real_name"]=$co_info["company_name"];
                 $mBorrow["idcard"]=$co_info["license_no"];
                 $mBorrow["is_com"]=1;
            }
            $mInvests=array();
            foreach ($iinfos as  $key =>$val){
                $mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name,m.user_phone,m.user_regtype')->where("m.id={$val['investor_uid']}")->find();
                $mInvests[$key]['user_regtype']=$mInvest['user_regtype'];
                if ($mInvests[$key]['user_regtype'] == 1) {
                    $mInvests[$key]['real_name']=$mInvest['real_name'];
                } elseif($mInvests[$key]['user_regtype'] == 2) {
                    $mCompany = M('members_company mc')->field('mc.company_name')->where("mc.uid={$val['investor_uid']}")->find();
                    $mInvests[$key]['real_name']=$mCompany['company_name'];
                }

                $mInvests[$key]['user_phone']=$mInvest['user_phone'];
                $mInvests[$key]['user_name']=$mInvest['user_name'];
                $detail = M('investor_detail d')->field('d.invest_id,sum(d.capital+d.interest-d.interest_fee) benxi,capital')->where("d.borrow_id={$borrow_id} and d.invest_id ={$val['id']}")->group('d.invest_id')->find();
                $mInvests[$key]['capital']=$detail['capital'];
                $mInvests[$key]['benxi']=$detail['benxi'];
                $mInvests[$key]['total']=$detail['total'];
                $mInvests[$key]['investor_capital']=$val['investor_capital'];


            }

            //$mInvest = M("members m")->join("{$per}member_info mi ON mi.uid=m.id")->field('mi.real_name,m.user_name')->where("m.id={$iinfo['investor_uid']}")->find();
            $jgcode = M("members m")->join("{$per}member_department_info jg ON jg.uid=m.id")->field('jg.institution_code')->where("m.id={$borrow_uid}")->find();

            //if(!is_array($iinfo)||!is_array($binfo)||!is_array($mBorrow)||!is_array($mInvest)) exit;

            //$detail = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,d.capital,sum(d.capital+d.interest-d.interest_fee) benxi,d.total')->where("d.borrow_id={$iinfo['borrow_id']} and d.invest_id ={$iinfo['id']}")->group('d.investor_uid')->find();


            $detailinfo = M('investor_detail d')->field('d.borrow_id,d.investor_uid,d.borrow_uid,(d.capital+d.interest-d.interest_fee) benxi,d.capital,d.interest,d.interest_fee,d.sort_order,d.deadline')->where("d.borrow_id={$borrow_id}")->select();
            $repay=array();
            foreach ($detailinfo as $key =>$val){
                 $repay['sort_order']=$val['sort_order'];
                 $repay['benxi']+=round($val['benxi'],2);
                 $repay['capital']+=$val['capital'];
                 $repay['interest']+=$val['interest'];
                 $repay['interest_fee']+=$val['interest_fee'];
                 $repay['deadline']=$val['deadline'];
            }

            $time = M('borrow_investor')->field('id,add_time')->where("borrow_id={$borrow_id} order by add_time asc")->limit(1)->find();

            if($binfo['repayment_type']==1){
                    $deadline_last = strtotime("+{$binfo['borrow_duration']} day",$time['add_time']);
                }else{
                    $deadline_last = strtotime("+{$binfo['borrow_duration']} month",$time['add_time']);
                }
            $this->assign('deadline_last',$deadline_last);
            //$this->assign('detail',$detail);

            $type1 = $this->gloconf['BORROW_USE'];
            $binfo['borrow_use_no'] = $binfo['borrow_use'];
            $binfo['borrow_use'] = $type1[$binfo['borrow_use']];
            $ht=M('hetong')->field('hetong_img,name,dizhi,tel')->find();

            $this->assign("ht",$ht);
            $type = $borrow_config['REPAYMENT_TYPE'];
            //echo $binfo['repayment_type'];
            $binfo['repayment_name'] = $type[$binfo['repayment_type']];

            $iinfo = M('borrow_investor')->field('id,borrow_id,investor_capital,investor_interest,deadline,investor_uid,add_time')->where("borrow_id={$borrow_id}")->find();
            $iinfo['repay'] = getFloatValue(($iinfo['investor_capital']+$iinfo['investor_interest'])/$binfo['borrow_duration'],2);
            $memberinfo = M('members')->find($uid);
            $this->assign("bid","CCFAX");
            //print_r($type);
            $this->assign('iinfo',$iinfo);
            $this->assign('memberinfo',$memberinfo);
            $this->assign('jgcode',$jgcode);

            //$detail_list = M('investor_detail')->field(true)->where("invest_id={$invest_id}")->select();
            //$this->assign("detail_list",$detail_list);
            //判断类型
            if($binfo['borrow_duration_txt']!=""){ //新版判断方式
                $newhetong=1;
                $add_info= D("borrow_info_additional")->get_additional_info($borrow_id);
                $duration_list=explode("+",$binfo['borrow_duration_txt']);
                if(count($duration_list)==2){
                    $show_type='A';
                    //$this->assign("c_date1",intval($duration_list[0]));
                    //$this->assign("c_date2",intval($duration_list[1]));
                    $this->assign("a_date", intval($duration_list[0])+intval($duration_list[1]));
                    $this->assign("tidan_rate",$add_info['frist_rate']);
                    $this->assign("xianhuo_rate",$add_info['second_rate']);


                    $day_array=explode("+",$binfo['borrow_duration_txt']);
                    $total_time=intval(mb_strcut($day_array[0],0,mb_strlen($day_array[0])-1));
                    if(count($day_array)==2){
                        $day2=intval(mb_strcut($day_array[1],0,mb_strlen($day_array[0])-1));
                        $total_time+=$day2;
                    }
                    //提单转现货模式， 需要修正时间
                    if($binfo['borrow_duration']!=$total_time){
                        $binfo['borrow_duration']=$total_time;
                        if($binfo['repayment_type']==1){
                            $repay['deadline']=$binfo['deadline'] = strtotime("+{$total_time} day",$binfo['second_verify_time']);
                        }else{
                            $repay['deadline']=$binfo['deadline']= strtotime("+{$total_time} month",$binfo['second_verify_time']);
                        }
                        //修正利息
                        foreach($mInvests as $key=>$mInvest){
                            $seconde_interest=getFloatValue($day2*$mInvests[$key]['capital']*$add_info['second_rate']/36000,2);
                            $mInvests[$key]['benxi']+=$seconde_interest;
                            $repay['interest']+=$seconde_interest;
                            $repay['benxi']+=$seconde_interest;
                        }

                    }
                }
                else if(count($duration_list)==1){
                    if($binfo['product_type']==1){
                        $show_type='A';
                        $this->assign("a_date",intval($duration_list[0]));
                        $this->assign("tidan_rate",$add_info['frist_rate']);
                    }
                    else if($binfo['product_type']==3||$binfo['product_type']==7){
                        $show_type='B';
                        $this->assign("b_date",intval($duration_list[0]));
                        $this->assign("xianhuo_rate",$add_info['frist_rate']);
                    }
                    else if($binfo['product_type']==2){ //本来打算提单标，后面转现货
                        $show_type='A';
                        $this->assign("a_date",intval($duration_list[0]));
                        $this->assign("tidan_rate",$add_info['frist_rate']);
                    }
                }
                $this->assign("show_type",$show_type);
                //parser 利率


            }
            $renumber = C('RENUMBER_BORROW.new_grade');
            if($binfo['id']>=$renumber){
                $binfo['id'] = borrowidlayout1($binfo['id']);
            }
            $this->assign('binfo',$binfo);
            $this->assign('mBorrow',$mBorrow);
            $this->assign('mInvests',$mInvests);
            $this->assign('repay',$repay);
            $flag=C('START_FLAG');
            if($binfo['product_type']==5){//分期购
                $where1['uid']=$uid;
                $info=M("members_company")->where($where1)->field("license_no")->find();
                $this->assign("license_no",$info["license_no"]);
                $r_info=getBorrowInvest($borrow_id,$uid);
                foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
                $this->assign("r_info",$r_info["list"]);
                // $this->buildHtml("contract_".$borrow_id,"html/contract/","fqghetong");
                if($binfo["borrow_use_no"] == 9){
                    $this->display("newfqgagreement");
                }else{
                    $this->display("fqghetong");
                }
            }else if($binfo['product_type']==8){//保金链
                 // $this->buildHtml("contract_".$borrow_id, "html/contract/", "bjlhetong");
                 $this->assign("repayment_type",$binfo['repayment_type']);
                 $this->display("bjlhetong");
            }else if($binfo['product_type']==10){//质金链(保)
                 // $this->buildHtml("contract_".$borrow_id, "html/contract/", "bjlhetong");
                 $this->assign("repayment_type",$binfo['repayment_type']);
                 $this->display("cjlhetong");
            }elseif($binfo['product_type']==7){//优金链
                // $this->buildHtml("contract_".$borrow_id, "html/contract/", "yjlhetong");
                $this->display("yjlhetong");
            }else if($binfo['product_type']==6){//信用标
                if($binfo['repayment_type']==2){
                    $where1['uid']=$uid;
                $info=M("members_company")->where($where1)->field("license_no")->find();
                $this->assign("license_no",$info["license_no"]);
                $r_info=getBorrowInvest($borrow_id,$uid);
                foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
                $this->assign("r_info",$r_info["list"]);
                }
                // $this->buildHtml("contract_".$borrow_id,"html/contract/","credithetong");
                $this->display("credithetong");
            }else if($binfo['product_type']==4){//融金链
                $where1['uid']=$uid;
                $info=M("members_company")->where($where1)->field("license_no")->find();
                $this->assign("license_no",$info["license_no"]);
                $r_info=getBorrowInvest($borrow_id,$uid);
                foreach($r_info['list'] as $k => $v){if(intval($v['needpay'])==0){$r_info["list"][$k]['needpay']=$v['paid'];}}
                $this->assign("r_info",$r_info["list"]);
                if($borrow_id > $flag['break_point_3']){
                    // $this->buildHtml("contract_".$borrow_id,"html/contract/","rjlhetong");
                    $this->display("rjlhetong");
                }else{
                     // $this->buildHtml("contract_".$borrow_id,"html/contract/","monthhetong");
                     $this->display("monthhetong");
                }
            }else if($binfo['product_type']<4&&$borrow_id > $flag['break_point_3']){//质金链
                // $this->buildHtml("contract_".$borrow_id, "html/contract/", "zjlhetong");
                $this->display("zjlhetong");
            }else if(isset($newhetong)){
                if ($borrow_id <= $flag['break_point_1']) {
                    // $this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong");
                    $this->display("newhetong");
                }
                if ($flag['break_point_1'] < $borrow_id && $borrow_id <= $flag['break_point_2']) {
                    $this->buildHtml("contract_".$borrow_id, "html/contract/", "newhetong1");
                    $this->display("newhetong1");
                }
                if ($borrow_id > $flag['break_point_2'] && $borrow_id <= $flag['break_point_3']) {
                    // $this->buildHtml("contract_".$borrow_id, "html/contract/", "agreement20160414");
                    $this->display("agreement20160414");
                }
            }else{
                // $this->buildHtml("contract_".$borrow_id,"html/contract/","index");
                $this->display("index");
            }
        // }
    }
    


}