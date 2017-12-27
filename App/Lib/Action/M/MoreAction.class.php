<?php
    /**
    * 手机版(wap)默认首页
    * @author  张继立  
    * @time 2014-02-24
    */
    class MoreAction extends HCommonAction
    {

        public function more()
        {
            $this->assign("tab","more");
            $this->assign("no_footer_seg","1");
            $this->display();
        }
        public function about(){
            $simple_header_info=array("url"=>"/M/more/more.html","title"=>"关于我们");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("tab","more");
            $this->display();
        }
		public function announcement(){
			$simple_header_info=array("url"=>"/M/more/more.html","title"=>"网站公告");
            //网站公告
            $parm['type_id'] = 9;
            $parm['limit'] =4;
            $this->assign("noticeList",getArticleList($parm));
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("tab","more");
            $this->display();
        }
		public function help(){
			$simple_header_info=array("url"=>"/M/more/more.html","title"=>"帮助中心");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("tab","more");
            $this->display();
        }
		public function czwt(){
			$simple_header_info=array("url"=>"/M/more/help.html","title"=>"充值问题");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("tab","more");
            $this->display();
        }
		public function txwt(){
			$simple_header_info=array("url"=>"/M/more/help.html","title"=>"提现问题");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("tab","more");
            $this->display();
        }
        public  function  notice(){
            $id = intval($_GET['news']);
            if($_GET['type']=="subsite") {
                $vo = M('article_area')->find($id);
            }else {
                $vo = M('article')->find($id);
                $tid = $vo['type_id'];
                $wo = M('article_category')->find($tid);
                $this->assign("wo",$wo);
            }
            $simple_header_info=array("url"=>"/M/more/announcement.html","title"=>"公告详情");
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("vo",$vo);
            $this->assign("tab","more");
            $this->display();
        }
        private function  get_content($value){
            $map['type_nid']=$value;
            $result=M('article_category')->field('type_content')->where($map)->select();
            $content=$result[0]['type_content'];
            $this->assign("tab","more");
            return $content;
        }
        private  function show_common($value,$title){
            $this->assign("content",$this->get_content($value));
            $simple_header_info=array("url"=>"/M/more/help.html","title"=>$title);
            $this->assign("simple_header_info",$simple_header_info);
            $this->display("common");
        }
        public function tdjs(){
            $this->assign("content",$this->get_content('tdjs'));
            $simple_header_info=array("url"=>"/M/more/help.html","title"=>"团队介绍");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        public function zhgl(){
            $this->show_common("zhgl","账户管理");
        }
        public function fxbz(){
            $this->show_common("fxbz","风险保障");
        }
        public function rhtz(){
            $this->show_common('rhtz',"如何投资");
        }
        public function mcjs(){
            $this->show_common('mcjs',"名称解释");
        }
        public function zfsm(){
            $this->assign("content",$this->get_content('zfsm'));
            $simple_header_info=array("url"=>"/M/more/help.html","title"=>"资费说明");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        public function cztx(){
            $this->assign("content",$this->get_content('cztx'));
            $simple_header_info=array("url"=>"/M/more/help.html","title"=>"充值提现");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        public function cqg(){
            $simple_header_info=array("url"=>"/M/more/help.html","title"=>"存钱罐");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        public function tiyanjin(){
            $simple_header_info=array("url"=>"/M/index.html","title"=>"体验金");
            $this->assign("simple_header_info",$simple_header_info);
            
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
            $this->assign("login_url",U("/M/pub/login"));
            $this->assign("uid",$this->uid?$this->uid:-1);
            $simple_header_info["title"]="红包嘉年华，百万福利欢乐送";
            $this->assign("simple_header_info",$simple_header_info);
            $this->assign("token",$token);
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
                $this->message(2,"请重新刷新再尝试11");
            }

            $count=$this->getLeftCount();//抽奖剩余次数
            if($count<=0){
                $this->message(4,"您抽奖机会已经用完，请再接再厉",array("count"=>$count));
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
                $this->redirect("M/pub/login");
            }else{
                $map=array("uid"=>$this->uid);
                $list=M("apr_zhongjiang")->where($map)->order("time desc")->select();
                $this->assign("list",$list);
                $simple_header_info=array("url"=>"/M/more/tiyanjin/","title"=>"中奖记录");
                $this->assign("simple_header_info",$simple_header_info);
                $this->display();
            }
        }

        /**
         * 安存
         */
        public function ancun(){
            $simple_header_info=array("url"=>"/M/index/index/","title"=>"安存");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        /**
         * CEO至客户的一封信
         */
        public function ceomail(){
            $simple_header_info=array("url"=>"/M/index/index/","title"=>"CEO致信");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        public function zhounian(){
            $list=M("activity a ")->field("uname,money,goodsname")->join("lzh_activity_price t on a.priceid=t.id")->order("money desc")->select();
            $simple_header_info=array("url"=>"/M/index/index/","title"=>"周年庆活动");
            $this->assign("simple_header_info",$simple_header_info);
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
                $endtime=strtotime(C("zhounian_endtime"));
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

        /**
         * 公司动态
         */
        public function allwood(){
            $simple_header_info=array("url"=>"/M/index/index/","title"=>"公司动态");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }

        /**
         * 水滴直播
         */
        public function shuidi(){
            $simple_header_info=array("url"=>"/M/index/index/","title"=>"水滴直播");
            $this->assign("simple_header_info",$simple_header_info);
            $this->display();
        }
        public  function shuidia(){
            $this->display();
        }
        public  function shuidib(){
            $this->display();
        }
        public  function shuidic(){
            $this->display();
        }

    }
?>
