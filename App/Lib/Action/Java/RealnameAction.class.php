<?php

/**
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/9/22
 * Time: 10:49
 * 全木行实名之后再去链金锁登录无法送体验金和链金豆的情况，
 */
class RealnameAction extends JCommonAction
{
       private $open=array("real"=>1);//接口开关，可能一定时期之后接口关闭了。
        /**
         * 送体验券
         */
        public function real(){
            if($this->open["real"]==0){
                echo json_encode(array("code"=>5,"resultText"=>"当前接口已经关闭"));
                exit();
            }
            $phone=$_POST["user_phone"];
            $uid=$_POST["uid"];
            if(empty($phone) || empty($uid)){
                echo json_encode(array("code"=>3,"resultText"=>"参数为空"));
                exit();
            }
            if(!strlen($phone)){
                echo json_encode(array("code"=>4,"resultText"=>"参数非法"));
                exit();
            }
            $reallist=M("members_status")->where(array("uid"=>$uid))->find();
            if(!$reallist || $reallist["id_status"]!=1){
                echo json_encode(array("code"=>6,"resultText"=>"当前用户没有实名认证或者实名认证失败"));
                exit();
            }
            $list=M("coupons")->where(array("user_phone"=>$phone,"name"=>"实名认证赠送投资券"))->find();
            if($list){
                echo json_encode(array("code"=>1,"resultText"=>"当前用户已经参与了实名认证送投资券"));
                exit();
            }
            $flag=flase;
            $userlist=M("members")->where(array("user_phone"=>$phone))->find();
            if(!$userlist){
                $flag=true;
            }else{
                $time=$userlist["reg_time"]-strtotime("2016-08-18 23:59:59");
                if($time>0){
                    $flag=true;
                }
            }

            if($flag) {//只是新用户实名认证才送投资券
                /*****************实名之后送4张投资券******************/
                $arr = [];
                $arr[0]["user_phone"] = $phone;
                $arr[0]["money"] = 10; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                $arr[0]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//strtotime(C("TOUZIQUAN_DEADTIME"));//取配置文件里面的2016-12-31
                $arr[0]["status"] = 0;
                $arr[0]["serial_number"] = time() . rand(100000, 999999);
                $arr[0]["type"] = 1;
                $arr[0]["name"] = "实名认证赠送投资券";
                $arr[0]["addtime"] = date("Y-m-d H:i:s", time());
                $arr[0]["isexperience"] = 1;
                $arr[0]["use_money"] = 1000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                M("coupons")->add($arr[0]);
                $arr[1]["user_phone"] = $phone;
                $arr[1]["money"] = 20; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                $arr[1]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//取配置文件里面的2016-12-31
                $arr[1]["status"] = 0;
                $arr[1]["serial_number"] = time() . rand(100000, 999999);
                $arr[1]["type"] = 1;
                $arr[1]["name"] = "实名认证赠送投资券";
                $arr[1]["addtime"] = date("Y-m-d H:i:s", time());
                $arr[1]["isexperience"] = 1;
                $arr[1]["use_money"] = 2000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                M("coupons")->add($arr[1]);
                $arr[1]["serial_number"] = time() . rand(100000, 999999);
                M("coupons")->add($arr[1]);
                $arr[2]["user_phone"] = $phone;
                $arr[2]["money"] = 50; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                $arr[2]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//取配置文件里面的2016-12-31
                $arr[2]["status"] = 0;
                $arr[2]["serial_number"] = time() . rand(100000, 999999);
                $arr[2]["type"] = 1;
                $arr[2]["name"] = "实名认证赠送投资券";
                $arr[2]["addtime"] = date("Y-m-d H:i:s", time());
                $arr[2]["isexperience"] = 1;
                $arr[2]["use_money"] = 5000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                M("coupons")->add($arr[2]);
                unset($arr);
                $content = "尊敬的链金所用户您好！100元投资券已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
                sendsms($phone, $content);
                echo json_encode(array("code"=>0,"resultText"=>"当前用户参与了实名认证送投资券成功"));
                exit();
            }else{
                echo json_encode(array("code"=>2,"resultText"=>"当前用户不是新用户无法参与实名认证送投资券"));
                exit();
            }
        }
}