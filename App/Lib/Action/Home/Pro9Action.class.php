<?php

/**
 * 2017 9月活动 -- 送,耍,抢,战
 * 
 */
class Pro9Action extends HCommonAction
{

    /**
     * 9 月活动首页
     * @return [type] [description]
     */
    public function index()
    {
        $model = new MembersStatusModel();
        $userstatus = $model->getUserStatus();
        if($userstatus  == 0)
        {
            $this->assign('uid',0);
        }else{
            $this->assign('uid',session('u_id'));
        }
        $this->assign('userstatus',$userstatus);
        //获取"送"相关参数
        $this->songVar();
        //获取"砸"相关参数
        $this->zaVar();
        //获取"抢"相关参数
        $this->qiangVar();
        //获取"战"相关参数
        $this->zhanVar();

        // 渲染页面
        if (is_mobile()) {
            // H5 页面,上一页跳转地址
            $simple_header_info=array("url"=>"/","title"=>"活动详情");
            $this->assign("simple_header_info", $simple_header_info);
            $this->display("h5index");
        } else {
            $this->display("index");
        }
    }

    /**
     * "送" 活动变量
     * @return [type] [description]
     */
    public function songVar()                                                               
    {
        $isNew = M('p9_count2')->where(['uid'=>$this->uid])->find();
        if($isNew==null){
            $isNew = 0;
        }else{
            $isNew = 1;
        }

        $mypacket = [
                        ['name'=>'','value'=>'0','status'=>'未激活','time'=>'2017-08-27 10:00:00'],
                     ];
    
        $mypacket = M('p9_win')->where(['uid'=>session('u_id'),'type'=>0])->field('name,status,date_format(from_unixtime(`create_time`),"%Y.%m.%d %H:%i:%s") as time,value')->select();
        foreach ($mypacket as $key => $value) {
            if($mypacket[$key] == 1){
                $mypacket[$key]['status'] = "已激活";
            }else{
                $mypacket[$key]['status'] = "未激活";
            }
        }

        //exit('isnew='.$isNew.'/packet='.json_encode($mypacket));
      
        $this->assign('isSongNew',$isNew);
        $this->assign('mypacket',$mypacket);
    }

    /**
     * "砸" 活动变量
     * @return [type] [description]
     */
    public function zaVar()
    {
        $isNew = M('p9_count')->where(['uid'=>session('u_id')])->find();
        if($isNew==null){
            $isNew = 0;
        }else{
            $isNew = 1;
        }

        $zleft = 0;
        $zprize = [];

        $info = M('p9_count')->where(['uid'=>session('u_id')])->find();
        if($info == null){
            $zleft = 0;
        }else{
            $zleft = $info['count_1'];    
        }

        $zprize = M('p9_win')->where(['uid'=>session('u_id'),'type'=>1])->field('name,status,value')->select();
        foreach ($zprize as $key => $value) {
            if($zprize[$key]['status'] == 1){
                $zprize[$key]['status'] = "已发放";
            }else{
                if(intval($zprize[$key]['value']) == 0){
                    //谢谢参与
                    $zprize[$key]['status'] = "";    
                }else{
                    $zprize[$key]['status'] = "活动结束后发放";
                }
                
            }
        }
        
        //exit('isnew='.$isNew.'left='.$zleft.'  prize='.json_encode($zprize).'  p9_count='.json_encode($info));

        $this->assign('isZaNew',$isNew);
        $this->assign('zleft',$zleft);
        $this->assign('myprize',$zprize);
    }

    /**
     * "抢" 活动变量
     * @return [type] [description]
     */
    public function qiangVar()
    {
        $isNew = M('p9_count')->where(['uid'=>session('u_id')])->find();
        if($isNew==null){
            $isNew = 0;
        }else{
            $isNew = 1;
        }

        // 剩余抽奖次数
        $qcount = 10;
        $icleft = 1000;
        // 今天抢过次数 最大为1 
        $qtimes = 0;

        $info = M('p9_count')->where(['uid'=>session('u_id')])->find();
        $qcount = $info==null?0:$info['count_2'];

        //根据p9_prize 中求出剩余加息券个数
        $m = new P9WinModel();
        $icleft = $m->icLeft();

        //根据p9_prize 中的中奖次数,求出今天已抢次数
        $qtimes = $m->timesOfToday(session('u_id'));

        //exit('info='.json_encode($info).'  qtimes='.$qtimes.'  icleft='.$icleft);
        $this->assign('isQiangNew',$isNew);
        $this->assign('qcount',$qcount);
        //剩余加息券
        $this->assign('icleft',$icleft);
        $this->assign('qtimes',$qtimes);
    }

    /**
     * "战" 活动变量$
     * @return [type] [description]
     */
    public function zhanVar()
    {
        $totalinvest = [
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ];
        $singleinvest = [
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ['user_phone'=>'137*****624','invest'=>'1000000','time'=>'2017-08-08 23:59:59'],
                        ];

        //计算单次最高投资和累计投资
        $glo = get_global_setting();
        $con['investor_uid'] = session('u_id');
        //$con['state'] = 4;
        $con['add_time'] = array('between',$glo['p9_start'].','.$glo['p9_end']);
       
        $zhanTotalInvest = M('borrow_investor')->where($con)->sum('investor_capital');
        $zhanMaxInvest = M('borrow_investor')->where($con)->field('investor_capital')->max('investor_capital');
        $zhanTotalInvest = $zhanTotalInvest==null?0:$zhanTotalInvest;
        $zhanMaxInvest = $zhanMaxInvest == null ? 0:$zhanMaxInvest;

        $idlist = M('p9_count')->select();
        $idlist = $idlist==null?[]:array_column($idlist, 'uid');
        unset($con);
        //$con['status'] = 4;
        $con['b.add_time'] = array('between',$glo['p9_start'].','.$glo['p9_end']);
        $con['b.investor_uid'] = array('in',$idlist);

        $t = M('borrow_investor b')->where($con)
                                   ->join('lzh_members m on m.id=b.investor_uid')
                                   ->field("sum(b.investor_capital) as invest,b.investor_uid,m.user_phone,date_format(from_unixtime(b.`add_time`),'%Y-%m-%d %H:%i:%s') as time,b.add_time")
                                   ->group('investor_uid')
                                   ->limit('0,10')
                                   ->order('invest desc,add_time desc')
                                   ->select();

        $s = M('borrow_investor b')->where($con)
                                   ->join('lzh_members m on m.id=b.investor_uid')
                                   ->field("b.investor_capital as invest,b.investor_uid,m.user_phone,date_format(from_unixtime(b.`add_time`),'%Y-%m-%d %H:%i:%s') as time,b.add_time")
                                   ->limit('0,50')
                                   //->group('investor_uid')
                                   ->order('investor_capital desc,add_time asc')
                                   ->select();
       

        foreach ($t as $key => $value) {
            $t[$key]['user_phone'] = substr($t[$key]['user_phone'], 0,3).'*****'.substr($t[$key]['user_phone'], -3);
            $t[$key]['index'] = $key+1;
            if($value['investor_uid'] == session('u_id')){
                $t[$key]['red'] = "myinvite-cla";
            }else{
                $t[$key]['red'] = "";
            }

        }

        foreach ($s as $key => $value) {
            $s[$key]['user_phone'] = substr($s[$key]['user_phone'], 0,3).'*****'.substr($s[$key]['user_phone'], -3);
            $s[$key]['index'] = $key+1;
            if($value['investor_uid'] == session('u_id')){
                $s[$key]['red'] = "myinvite-cla";
            }else{
                $s[$key]['red'] = "";
            }
        }

        //单笔投资金额取最高一笔
        $ids = [];
        foreach ($s as $key => $value) {
            if(sizeof($ids) >= 10){
                unset($s[$key]);
            }elseif(!in_array($value['investor_uid'], $ids)){
                $s[$key]['index'] = sizeof($ids)+1;
                array_push($ids, $value['investor_uid']);
            }else{
                unset($s[$key]);
            }
        }
        $s = array_values($s);

        $totalinvest = $t;
        $singleinvest = $s;

        $this->assign('empty','<p class="invest-empty">对不起，数据为空！</p>');
        $this->assign('totalinvest',$totalinvest);
        $this->assign('singleinvest',$singleinvest);
        $this->assign('zhanMaxInvest',$zhanMaxInvest);
        $this->assign('zhanTotalInvest',$zhanTotalInvest);
    }

    /**
     * 获取结果
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getSongResult()
    {
        //validate
        $this->songValidate();

        //roll dice
        randomRoll($this->uid,1,"p9_prize","songCallback",1);

    }

    /**
     * 获取砸冰块的结果
     * @return [type] [description]
     */
    public function getZaResult()
    {
        //validate
        $this->zaValidate();
        
        $info = M('p9_count')->where(['uid'=>session('u_id')])->find();
        //roll dice
        randomRoll($this->uid,$info['count_1'],"p9_prize","zaCallback",2);
        
    }

    public function getQiangResult()
    {
        //validate
        $this->qiangValidate();
        
        $info = M('p9_count')->where(['uid'=>session('u_id')])->find();
        // roll dice
        randomRoll($this->uid,$info['count_2'],"p9_prize","qiangCallback",3);
        
    }

    private function songValidate()
    {
        //活动时间
        $glo = get_global_setting();
        if(time()>$glo['p9_end']||time()<$glo['p9_start'])
        {
            exit(ajaxFail("活动已结束",['errorcode'=>1]));
        }

        //是否登录
        if(empty($this->uid)){
            exit(ajaxFail("请登录",['errorcode'=>2]));
        }

        //是否开通新浪支付
        $model = new MembersStatusModel();
        $status = $model->getUserStatus();
        if(!($status&EnumUserStatus::SinaPay))
        {
            exit(ajaxFail("未开通新浪支付",['errorcode'=>3]));
        }

        //是否有抽奖次数
        $result = M('p9_win')->where(['uid'=>$_SESSION['u_id'],'type'=>0])->count();
        if($result>0)
        {
            exit(ajaxFail("红包次数已用完 {$result}",['errorcode'=>4]));
        }

        // if($status&EnumUserStatus::NotInvestBefore)
        // {
        //     exit(ajaxFail("请投资先"));
        // }

        $result2 = M('p9_count')->where(['uid'=>$_SESSION['u_id']])->count();
        if(!$result2)
        {
            exit(ajaxFail('非新用户.',['errorcode'=>5]));
        }

    }

    private function zaValidate()
    {
        //活动时间
        $glo = get_global_setting();
        if(time()>$glo['p9_end']||time()<$glo['p9_start'])
        {
            exit(ajaxFail("活动已结束",['errorcode'=>1]));
        }

        //是否登录
        if(empty($this->uid)){
            exit(ajaxFail("请登录",['errorcode'=>2]));
        }

        //抽奖次数达到上限
        $win = new P9WinModel();
        if($win->zaReachLimit() >= 5){
            exit(ajaxFail('今日次数用完,请明日再来!',['errorcode'=>3]));
        }
        
        //是否有抽奖次数
        $minfo = M('p9_count')->where(['uid'=>session('u_id')])->find();
        if(!$minfo)
        {
            exit(ajaxFail('非新用户.',['errorcode'=>4]));
        }

        //是否开通新浪支付
        $model = new MembersStatusModel();
        $status = $model->getUserStatus();
        if(!($status&EnumUserStatus::SinaPay))
        {
            exit(ajaxFail("未开通新浪支付",['errorcode'=>6]));
        }

        if($minfo['count_1']<=0)
        {
            exit(ajaxFail('没有抽奖次数.',['errorcode'=>5]));   
        }
      
    }

    private function qiangValidate()
    {
        //活动时间
        $glo = get_global_setting();
        if(time()>$glo['p9_end']||time()<$glo['p9_start'])
        {
            exit(ajaxFail("活动已结束",['errorcode'=>1]));
        }

        //10点开抢
        $morning = strtotime(date('Y-m-d 10:00:00',time()));
        if(time()<$morning)
        {
            exit(ajaxFail("10点开始",['errorcode'=>2]));   
        }

        //是否登录
        if(empty($this->uid)){
            exit(ajaxFail("请登录",['errorcode'=>3]));
        }

        //是否有抽奖次数
        $minfo = M('p9_count')->where(['uid'=>session('u_id')])->find();
        if(!$minfo)
        {
            exit(ajaxFail('非新用户.',['errorcode'=>4]));
        }

        if($minfo['count_2']<=0)
        {
            exit(ajaxFail('没有抽奖次数.',['errorcode'=>5]));   
        }

        //抽奖次数达到上限
        $win = new P9WinModel();
        if($win->qiangReachLimit() >= 1){
            exit(ajaxFail('今日次数用完,请明日再来!',['errorcode'=>6]));
        }

        $m = new P9WinModel();
        $icleft = $m->icLeft();
        if($icleft ==0){
            exit(ajaxFail('今日加息券已经被抢完,请明日再来',['errorcode'=>7]));   
        }

    }

    public function rockAndRoll2()
    {
        //已注册但是未开通新浪支付的用户,加入到 p9_count2 中
        S('global_setting', null);
        $glo = get_global_setting();
        $code = M('vc_tmp')->where(['type'=>103])->find();
        if (null == $code)
            exit(" can not find vc_tmp record for p2_count2!");

        $p = new MembersStatusModel();
        $list = $p->getPaypwdIdList();

        echo 'list ='.sizeof($list);
        $p9 = M('p9_count2')->select();
        $ids = array_column($p9, 'uid');
        $ids == null?$ids=[]:"";
        echo 'ids='.sizeof($ids);
        echo '###';

        $list = array_merge($list,$ids);
        echo 'last='.sizeof($list);


        $con['id'] = array('not in',$list);
        $con['id'] = array('gt',$code['log_id']);

        $range = implode(',', $list);
        $max = $code['log_id'];

        $sql = "SELECT * from lzh_members where id > {$max} and id not in ({$range}) limit 0,5000";
        $memberList = M()->query($sql);

        if ($memberList == null){
            exit('mission complete');
        }

        foreach ($memberList as $key => $value) {
            $isExist = M('p9_count2')->where(['uid'=>$value['id']])->find();
            if($isExist)
                continue;

            $data['uid']          = $value['id'];
            $data['user_phone']   = $value['user_phone'];
            $data['parent_id']    = $value['recommend_id'];
            $data['invest_money'] = 0;
            $data['create_time']  = time();
            $result = M('p9_count2')->add($data);

            if($result){
                M('vc_tmp')->where(['type'=>103])->save(['log_id'=>$value['id']]);
            }else{
                exit(mysql_error());
            }
            
        }

        exit('yes,to continue.');
        
    }

    public function rockAndRoll()
    {
        //已注册但是未投资,加入到 p9_count 里面
        S('global_setting', null);
        $glo = get_global_setting();

        $code = M('vc_tmp')->where(['type'=>104])->find();
        if (null == $code)
            exit(" can not find vc_tmp record for p2_count.");

        $p = new BorrowInvestorModel();
        $list = $p->getInvestedIdList(null,$glo['p9_start']);


        echo 'list ='.sizeof($list);
        $p9 = M('p9_count')->select();

        $ids = array_column($p9, 'uid');
        echo '#ids='.sizeof($ids);
        $ids == null?$ids=[]:"";
        echo '#';

        $list = array_merge($list,$ids);
        echo 'last='.sizeof($list);
        echo '#log_max='.$code['log_id'].'#';

        $con['id'] = array('not in',$list);
        $con['id'] = array('gt',$code['log_id']);

        $range = implode(',', $list);
        $max = $code['log_id'];

        $sql = "SELECT * from lzh_members where id > {$max} and id not in ({$range}) limit 0,5000";
        $memberList = M()->query($sql);

        if ($memberList == null){
            exit('mission complete ');
        }

        foreach ($memberList as $key => $value) {

            $isExist = M('p9_count')->where(['uid'=>$value['id']])->find();
            if($isExist)
                continue;

            $data['uid']          = $value['id'];
            $data['user_phone']   = $value['user_phone'];
            $data['parent_id']    = $value['recommend_id'];
            $data['invest_money'] = 0;
            $data['create_time']  = time();
            $result = M('p9_count')->add($data);
            if($result){
                M('vc_tmp')->where(['type'=>104])->save(['log_id'=>$value['id']]);    
            }else{
                exit(mysql_error());
            }

            
        }
        echo 'valueid='.$value['id'];

        exit('yes,to continue.');
        
    }

    public function getTimerange()
    {
        S('global_setting', null);
        $glo = get_global_setting();
        $start_time = date("Y-m-d H:i:s", $glo['p9_start']);
        $end_time = date("Y-m-d H:i:s", $glo['p9_end']);
        exit('start = '.$start_time.'   end ='.$end_time);
    }

    public function refreshQiang()
    {
        if (trim($_GET['username'])!="lushixin"||trim($_GET['pwd'])!="lushixin")
        {
            exit(" You are not authorized!");
        }

        $p9prize = new P9PrizeModel();
        $result = $p9prize->refreshQiang();
        if($result === false){
            exit('failed   '.mysql_error());
        }

        exit('success');


    }

}
