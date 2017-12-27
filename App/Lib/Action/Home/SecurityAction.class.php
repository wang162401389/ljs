<?php
/**
* 安全保障
*/
class SecurityAction extends HCommonAction
{
    public function _initialize(){
        parent::_initialize();
        $title="网络借贷,网络理财,P2P网贷有哪些安全保障措施？信任首选链金所";
        $keyword="网络借贷有哪些安全保障措施,网络理财有哪些安全保障措施,P2P网贷有哪些安全保障措施";
        $description="链金所结合金融专业的风控手段,为用户提供全面的网络贷款,网络借贷,网络理财,P2P理财,P2P网贷安全保障服务.更好的保障投资人的资金安全.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
    }
    // 安全保障
    public function index()
    {
        $this->display();
    }
    // 什么是提单质押
    public function impawn()
    {
        $title="海运提单的货权质押_链金所";
        $keyword="海运提单,货权质押";
        $description="链金所实时落实货权质押情况,货物运输,存仓24小时定位和监控,随时随地可以查询货物状态,查询范围可追至境外.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
        $this->display();
    }
    // 产融结合
    public function integration()
    {
        $title="产融结合——全新平台理念_链金所";
        $keyword="产融结合,产融结合理念";
        $description="链金所首次提出”融汇财富,产业帮扶”的可投可融的平台.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
        $this->display();
    }
    // 按日计息
    public function interest_accrual()
    {
        $title = "按日计息";
        $this->assign("title",$title);
        $this->display();
    }
    // 木材商圈
    public function business_area()
    {
        $title = "木材商圈";
        $this->assign("title",$title);
        $this->display();
    }
    // 新手指导
    public function new_guidance()
    {
        $title="新手指导,新手投资流程_链金所";
        $keyword="链金所新手指导";
        $description="链金所新手指导,开启财富之旅.";
        $this->assign('title',$title);
        $this->assign('keyword',$keyword);
        $this->assign('description',$description);
        $this->display();
    }
	
	//妇女节活动
	public function fnj_activity()
    {
        $title = "妇女节活动";
        $this->assign("title",$title);
        $this->display();
    }
        //活动页
     public function activity()
    {
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $info = $Model->query("SELECT SUM(h.money) AS totalmoney,m.user_name FROM lzh_huodong h INNER JOIN lzh_members m ON m.id = h.uid GROUP BY h.uid ORDER BY totalmoney DESC");
        //print_r($info);
        $k = 0;
        foreach ($info as $i) {
            if($i["totalmoney"] < 10000){
                $info[$k]["gift"] = "新年红包";
            }elseif($i["totalmoney"] >= 10000 && $i["totalmoney"] < 50000){
                $info[$k]["gift"] = "实惠奖";
            }elseif ($i["totalmoney"] >=50000 && $i["totalmoney"] < 100000) {
                $info[$k]["gift"] = "超值奖";
            }elseif ($i["totalmoney"] >=100000 && $i["totalmoney"] < 500000) {
                $info[$k]["gift"] = "精品奖";
            }elseif ($i["totalmoney"] >=500000 && $i["totalmoney"] < 1000000) {
                $info[$k]["gift"] = "豪华奖";
            }elseif ($i["totalmoney"] >= 1000000 && $k == 0) {
                $info[$k]["gift"] = "至尊奖";
            }elseif($i["totalmoney"] >= 1000000 && $k != 0){
                $info[$k]["gift"] = "豪华奖";
            }
            $info[$k]["user_name"] = hidecard($i['user_name'],5);
            $k++;
        }
         if($this->uid){
            $login=1;
        }else{
            $login=0;
        }
        $title = "新年狂欢，礼值气壮";
        $this->assign("login",$login);
        $this->assign("title",$title);
        $this->assign("info",$info);
        $this->display();
    }
    //查询中奖 
    public function checkuser(){
        $username = $_POST["uname"];
        $uid = M("members")->where("user_name LIKE '{$username}'")->field("id")->find();
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $info = $Model->query("SELECT SUM(money) AS totalmoney FROM lzh_huodong  where uid = {$uid["id"]} GROUP BY uid");
        if($uid == null){
             $this->ajaxreturn(0,"无记录",3);
        }
        if($info != null){
            if($info[0]["totalmoney"] < 10000){
                $info[0]["gift"] = "新年红包";
                $info[0]["money"] = 10000-$info[0]["totalmoney"];
            }elseif($info[0]["totalmoney"] >= 10000 && $info[0]["totalmoney"] < 50000){
                $info[0]["gift"] = "实惠奖";
                $info[0]["money"] = 50000-$info[0]["totalmoney"];
            }elseif ($info[0]["totalmoney"] >=50000 && $info[0]["totalmoney"] < 100000) {
                $info[0]["gift"] = "超值奖";
                $info[0]["money"] = 100000-$info[0]["totalmoney"];
            }elseif ($info[0]["totalmoney"] >=100000 && $info[0]["totalmoney"] < 500000) {
                $info[0]["gift"] = "精品奖";
                $info[0]["money"] = 500000-$info[0]["totalmoney"];
            }elseif ($info[0]["totalmoney"] >=500000 && $info[0]["totalmoney"] < 1000000) {
                $info[0]["gift"] = "豪华奖";
                $info[0]["money"] = 1000000-$info[0]["totalmoney"];
            }elseif ($info[0]["totalmoney"] >= 1000000) {
                $info1 = $Model->query("SELECT SUM(money) AS totalmoney FROM lzh_huodong GROUP BY uid ORDER BY totalmoney DESC LIMIT 0,1");
                if($info1[0]['totalmoney'] > $info[0]["totalmoney"]){
                    $info[0]["gift"] = "豪华奖";
                }else{
                    $info[0]["gift"] = "至尊奖";
                }
            }
            $this->ajaxreturn($info[0],"有记录",1);
        }elseif($info == null){
            $this->ajaxreturn("您还未进行投资呢！","有记录",2);
        }
    }

    public function introduce5(){
        $this->display();
    }
	
	//分期购
	public function periodization(){
        $this->display();
    }

    // 体验金
    public function tiyanjin()
    {
        //是否实名认证
        $id_status=M("members_status")->where("uid={$this->uid}")->find();
       if($id_status["id_status"]==1){
           $this->assign("ID_SET",1);
       }else if($id_status["company_status"]!=0){
           $this->assign("ID_SET",1);
       }
       else{
           $go_id= "/member/verify?id=1#fragment-1";
           $this->assign("go_id",$go_id);
       }
        $title = "体验金";
        $this->assign('uid',$this->uid);
        //$this->assign('tsykgurl',C("TS1KG.host"));
        $this->assign("title",$title);

        //大转盘
        $starttime=C("Turntable_starttime");
        $endtime=C("Turntable_endtime");
        $time=time();
        /**
         * 是否在活动时间范围内： 0不在 1在
         */
        $isstart=1;

        /**
         * 是否登录： 0未登录 1登录
         */
        $islogin=1;

        if(strtotime($starttime)>$time || strtotime($endtime)<time ){
            $isstart=0;
        }
        if(!$this->uid){//未登录
            $islogin=0;
        }
        /**
         * 累计抽奖次数
         */
        $person_num=M("apr_zhongjiang")->count();
        $token=mt_rand(1000000,9999999);
        session("token",$token);
        $this->assign("isstart",$isstart);
        $this->assign("islogin",$islogin);
        $this->assign("starttime",$starttime);
        $this->assign("endtime",$endtime);
        if(session("beancount")){
            if($this->uid){
                $left=session("beancount");
            }else{
                $left=0;
            }
        }else{
            if($this->uid){
                $left=$this->getLeftCount();
                session("beancount",$left);
            }else{
                $left=0;
            }
        }
        $list=M("apr_info a")->field("user_name,time,goodsname")->join("lzh_apr_zhongjiang t on t.uid=a.uid")->select();
        $this->assign("list",$list);
        $this->assign("left",$left);//剩余抽奖次数
        $this->assign("person_num",$person_num);
        $this->assign("login_url",U("/member/common/login"));
        $this->assign("uid",$this->uid?$this->uid:-1);
        $this->assign("token",$token);

        //分页处理
        import("ORG.Util.Page");
        $map=array("uid"=>$this->uid);
        $count = M('apr_zhongjiang')->where($map)->count();
        //$p = new Page($count, 10);
        //$page = $p->show();
        //$Lsql = "{$p->firstRow},{$p->listRows}";
        /**$zhongjianlist=M("apr_zhongjiang")->where($map)->order("time desc")->limit($Lsql)->select();**/
        $zhongjianlist=M("apr_zhongjiang")->where($map)->order("time desc")->select();
        //$this->assign("page",$page);
        $this->assign("simple_header_info",$simple_header_info);
        $this->display();
    }

    /**
     * 单击转盘获取结果
     */
    public function getResult(){
        if(empty($this->uid)){
            $this->message(1,"请登录");//没有登录，请登录
        }
        $token=mt_rand(1000000,9999999);
        $start=strtotime(C("Turntable_starttime"));
        if($start>time()){
            session("token",$token);
            $this->message(2,"活动还没有开始");
        }
        $end=strtotime(C("Turntable_endtime"));
        if($end<time()){
            $this->message(2,"活动已经结束");
        }

        $mytoken=$_POST["token"];
        //防止页面狂提交或者用脚本攻击
        if(!$mytoken ||  session("token")!=$mytoken){
            session("token",$token);
            $this->message(2,"请重新刷新再尝试");
        }

        $count=$this->getLeftCount();//抽奖剩余次数
        if($count<=0){
            $this->message(4,"您没有抽奖机会",array("count"=>$count));
        }
        $a=mt_rand(0,10000);
        $apr_prizeModel=M("apr_prize");
        $goodslist=$apr_prizeModel->query("select * from lzh_apr_prize where status=1 and minnum<={$a} and maxnum>={$a}");
        if(count($goodslist) && $goodslist[0]["left"]>0){
            if($goodslist[0]["info"]=="谢谢参与"){
                $message="谢谢参与";
                $person_num=M("apr_zhongjiang")->count();
                session("token",$token);
                $data["count"]=$count-1;
                $data["angle"]=$goodslist[0]["angel"];
                $data["token"]=$token;
                session("beancount",  $data["count"]);
                $apr_beanModel=M("apr_bean");
                $beanlist=$apr_beanModel->where(array("uid"=>$this->uid))->find();
                $left=$beanlist["beancount"]-100;//100个链金豆可以投一次
                $apr_beanModel->where(array("uid"=>$this->uid))->save(array("beancount"=>$left));//扣除使用的链金豆
                $data['info_num']=$person_num;
                $this->message(5,$message,$data);
            }else{
                $apr_infoModel=M("apr_info");
                $list=$apr_infoModel->where(array("uid"=>$this->uid))->find();
                $flag2=true;
                if($list){//如果已经存在则直接插入奖品
                    $id=M("apr_zhongjiang")->add(array(
                        "uid"=>$this->uid,
                        "time"=>date("Y-m-d H:i:s",time()),
                        "goodsid"=>$goodslist[0]["id"],
                        "goodsname"=>$goodslist[0]["info"]));
                    $userlist=M("members")->where(array("id"=>$this->uid))->find();
                    if($goodslist[0]["type"]==0){//如果是投资券
                        M("coupons")->add(array(
                            "user_phone"=>$userlist["user_phone"],
                             "money"=>$goodslist[0]["value"],
                             "endtime"=>strtotime("+3 month",time()),
                             "status"=>0,
                             "serial_number"=>date('YmdHis').mt_rand(100000,999999),
                             "type"=>1,
                              "name"=>"大转盘抽奖送".$goodslist[0]["info"],
                              "addtime"=>date("Y-m-d H:i:s",time()),
                              "isexperience"=>1,
                              "use_money"=>$goodslist[0]["value"]*100
                        ));
                    }
                    if(!$id){
                        $flag2=false;
                    }
                }else{//不存在
                    $apr_infoModel->startTrans();
                    $mem=M("members")->where(array("id"=>$this->uid))->find();
                    $flag=$apr_infoModel->add(array(
                        "uid"=>$this->uid,
                        "user_name"=>$mem["user_name"],
                        "reg_time"=>$mem["reg_time"],
                        "add_time"=>time(),
                        "user_phone"=>$mem["user_phone"]
                    ));
                    if($flag){
                        $flag1=M("apr_zhongjiang")->add(array("uid"=>$this->uid,
                            "time"=>date("Y-m-d H:i:s",time()),
                            "goodsid"=>$goodslist[0]["id"],
                            "goodsname"=>$goodslist[0]["info"]));

                        M("coupons")->add(array(
                            "user_phone"=>$mem["user_phone"],
                            "money"=>$goodslist[0]["value"],
                            "endtime"=>strtotime("+3 month",time()),
                            "status"=>0,
                            "serial_number"=>date('YmdHis').mt_rand(100000,999999),
                            "type"=>1,
                            "name"=>"大转盘抽奖送".$goodslist[0]["info"],
                            "addtime"=>date("Y-m-d H:i:s",time()),
                            "isexperience"=>1,
                            "use_money"=>$goodslist[0]["value"]*100
                        ));
                        if($flag1){
                            $apr_infoModel->commit();
                        }else{
                            $apr_infoModel->rollback();
                            $flag2=false;
                        }
                    }else{
                        $apr_infoModel->rollback();
                        $flag2=false;
                    }
                }
                if($flag2){
                    $message="恭喜你获取了".$goodslist[0]["info"];
                    session("token",$token);
                    $data["count"]=$count-1;
                    session("beancount",  $data["count"]);
                    $apr_beanModel=M("apr_bean");
                    $apr_prizeModel->where(array("id"=>$goodslist[0]["id"]))->save(array("left"=>$goodslist[0]["left"]-1));
                    $beanlist=$apr_beanModel->where(array("uid"=>$this->uid))->find();
                    $left=$beanlist["beancount"]-100;//100个链金豆可以投一次
                    $apr_beanModel->where(array("uid"=>$this->uid))->save(array("beancount"=>$left));//扣除使用的链金豆
                    $data["angle"]=$goodslist[0]["angel"];
                    $arr=array(0,280,160,80);
                    if(!in_array($data["angle"],$arr)){
                        $data["angle"]=360;
                    }
                    $data["token"]=$token;
                    $person_num=M("apr_zhongjiang")->count();
                    $data['info_num']=$person_num;
                    $this->message(5,$message,$data);
                }else{
                    $this->message(2,"请重新刷新再尝试");
                }
            }
        }else{
            $this->message(3,"当前的礼品已经用完，请联系网站管理人员");
        }

    }

    /**
     * ajax输出消息
     * @param $status
     * @param $message
     * @param array $data
     */
    private function message($status,$message,$data=array()){
        $da=[];
        $da["status"]=$status;
        $da["message"]=$message;
        if(count($data)){
            $da["data"]=$data;
        }
        echo json_encode($da);
        exit();
    }

    /**
     * 获取剩下的抽奖次数
     */
    private function getLeftCount(){
        $ret=0;
        $apr_beanModel=M("apr_bean");
        $list=$apr_beanModel->where(array("uid"=>$this->uid))->find();
        if($list){
            $ret=floor($list["beancount"]/100);//向下取整
        }
        return $ret;
    }

    /**
     * 获取我的中奖纪录
     */
    public function getrecode(){
        if(!$this->uid){
            $this->redirect();
        }else{
            //分页处理
            import("ORG.Util.Page");
            $map=array("uid"=>$this->uid);
            $count = M('apr_zhongjiang')->where($map)->count();
            $p = new Page($count, 8);
            $page = $p->ajax_show();
            $total_page=$p->get_total_page();
            $Lsql = "{$p->firstRow},{$p->listRows}";
            $list=M("apr_zhongjiang")->where($map)->order("time desc")->limit($Lsql)->select();
            $this->assign("page",$page);
            $this->assign("list",$list);
            $this->assign("total_page",$total_page);
            $html=$this->fetch();
            echo json_encode(array("status"=>0,"msg"=>"获取成功","data"=>$html));
            exit();


        }
    }
    

    /**
     * 安存
     */
    public function ancun(){
        $this->display();
    }

    /**
     * CEO至客户的一封信
     */
    public function ceomail(){
        $this->display();
    }

    /**
     * 周年庆活动
     */
    public function zhounian(){
        $list=M("activity a ")->field("uname,money,goodsname")->join("lzh_activity_price t on a.priceid=t.id")->order("money desc")->select();
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 周年庆活动获取用户区间时间段内投资金额
     */
    public function zhounianinfo(){
        $name=$_GET["name"];
        if($name){
            $name=trim($name);
            $starttime=strtotime(C("zhounian_starttime"));
            $endtime=strtotime(C("zhounian_endtime"));//status 1:代付款 2 冻结  3.退款  4成功
            $mylist=M('sinalog')->query("select sum(money) as money from lzh_sinalog t inner join lzh_members g on g.id=t.uid where t.type=3 and (t.status=4 or t.status=2) and t.completetime>=$starttime and t.completetime<=$endtime and g.user_name='{$name}'");
            if($mylist && $mylist[0]["money"]){
                echo json_encode(array("status"=>0,"msg"=>"11111","data"=>array("totalmoney"=>$mylist[0]["money"],"name"=>$name)));
                exit();
            }else{
                echo json_encode(array("status"=>2,"msg"=>"当前时间段内您没有任何投资"));
                exit();
            }
        }else{
            echo json_encode(array("status"=>1,"msg"=>"用户名不能为空"));
            exit();
        }
    }

}