<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/15
 * Time: 14:57
 */
class SynchronizedAction extends HCommonAction
{
    public function _initialize()
    {
        $ips=array("127.0.0.1","115.159.114.87","115.159.65.162","192.168.0.5","192.168.0.210","218.17.34.102","172.16.20.78","172.16.20.79");
        $userip=$_SERVER['REMOTE_ADDR'];
        if (!in_array($userip, $ips)) {
            //   $this->error('非法ip操作');
            Log::write("异步任务,非正常访问".$userip);
            exit;
        }
    }

    public function reminder_repayment()
    {
        Log::write("启动短信到期提醒业务");
        $where['i.substitute_time']=array("eq",0);
        $where['i.repayment_time']=array("eq",0);
        $where['b.repayment_type']=2;//月标
        $where['b.borrow_status']=6;//月标
         $max_time=time()+2*24*60*60;
        $where['i.deadline']=array("ELT",$max_time);
        $field="i.id,b.id as borrow_id,b.borrow_uid,m.user_phone";
        //$info=M("investor_detail i")->join("lzh_borrow_info as b on i.borrow_id=b.id")->join("lzh_members as m on m.id=b.borrow_uid")->where($where)->select();
        $info=M("investor_detail i")->join("lzh_borrow_info as b on b.id=i.borrow_id")->join("lzh_members as m on m.id=b.borrow_uid")->field($field)->where($where)->group("i.borrow_id")->select();
        if (is_array($info)) {
            foreach ($info as $key=>$val) {
                if (!isset($tel)) {
                    $tel=$val['user_phone'];
                } elseif (strpos($tel, $val['user_phone'])<0) {
                    $tel.=$val['user_phone'].",";
                }
            }
            $tel=trim($tel, ',');
            //发送消息
            if (strlen($tel)>0) {
                import("@.sms.Notice");
                $sms=new Notice();
                $sms->remind_replay($tel);
                Log::write("已经向".$tel."发送提醒短信");
            }
        } else {
            Log::write("没有快到期的业务");
        }
    }
    private function do_task($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($ch);
        Log::write($url."异步返回".$return);
        curl_close($ch);
        return $return;
    }
    public function synch_task()
    {
        import("@.redis.redis_task");
        $task=new redis_task();
        while (1) {
            $info=$task->get_task();
            if (is_array($info)) {
                Log::write(var_export($info, true));
                $return=$this->do_task($info['url'], $info['data']);
                if ($return!="success") {
                    $fail['url']=$info['url'];
                    $fail['data']=$info['data'];
                    $fail_list[]=$fail;
                    Log::write($fail['url']."发送数据失败");
                }
            } else {
                Log::write("目前没有任务");
                break;
            }
        }
       //处理失败任务
       if (count($fail_list)>0) {
           foreach ($fail_list as $key=>$val) {
               $task->release_task($val['url'], $val['data']);
           }
       }
    }

    public function apr_sent_money()
    {
        $token=$_REQUEST['token'];
        $uid=intval($_REQUEST["uid"]);
        $id=intval($_REQUEST["id"]);
        $money_array=array(8,10,15);
        $where['id']=$id;
        $where['uid']=$uid;
        $where['sessionid']=$token;
        $where["status"]=1;
        $info=M("offlinep2p.apr_info", "lzh_")->where($where)->find();
        $utype = M("members")->where("id={$uid}")->field("user_regtype")->find();
        Log::write(var_export($info, true));
        if (is_array($info)) {
            $money=$money_array[intval($info["type"])-1];
            Log::write($money);
            if ($money!=0) {
                //设置付款了。
                $tmp=0;
                do {
                    if ($tmp>5) {
                        Log::write("5次还没有等待机会，不等了，看运气");
                        break;
                    }
                    $where1['money_time']=time();
                    $count=M("offlinep2p.apr_info", "lzh_")->where($where1)->count();
                    Log::write(date("Y-m-d H:m:i", time())."申请次数为".$count);
                    if ($count>=4) {
                        Log::write("休息2s");
                        $tmp++;
                        sleep(2);
                    } else {
                        break;
                    }
                } while (1);

                $data1['status']=2;
                $data1['money_time']=time();
                M("offlinep2p.apr_info", "lzh_")->where($where)->save($data1);
                Log::write("给".$uid."发".$money."元");
                sinarewardhongdong($uid, $money, $utype['user_regtype'],"平台付出投资红包");
            }
        }
    }
    public function v_sent_money()
    {
        $money=$_POST['money'];
        $info=$_POST['info'];
        $list=unserialize($info);
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $result = $sina->collecttradecompany($money, "虚拟标奖励");
        if ($result == "APPLY_SUCCESS") {
            $sina->batchpaytrade(date('YmdHis') . mt_rand(100000, 999999), $money, 0, $list, "vinvest_money"); //后续增虚拟标的时候，增加标的号
        }
        log::write(var_export($list, true), true);
        log:write($money);
    }



     //验证新浪接口响应信息
    private function checksinaerror($data)
    {
        import("@.Oauth.sina.Weibopay");
        $weibopay = new Weibopay();
        $deresult = urldecode($data);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序

        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            return $splitdata;
        } else {
            return "sing error!" ;
            exit();
        }
    }

    private function setpaypwd($url, $uid)
    {
        Log::write("setpaypwd");
        $url="https://miror.ccfax.cn/invest/8.html";
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "set_pay_password";                                        //绑定认证信息的接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['identity_id']          = "20151008".$uid;                        //用户ID
        $data['identity_type']          = "UID";                                                    //用户标识类型 UID
        $data['return_url']              = $url;
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $rs = $this->checksinaerror($result);
        Log::write(var_export($rs, true));
        if (isset($rs['redirect_url'])) {
            return urlencode($rs['redirect_url']);
        } else {
            return "OK";
        }
    }

    public function check_sina_password()
    {
        $uid=$_POST["uid"];
        $url=$_POST["url"];
        Log::write($uid);
        Log::write($url);
        $ids = M('members m')->join("lzh_members_status s on s.uid = m.id")->where("m.id={$uid}")->field('m.user_regtype,s.id_status,s.company_status')->find();
        Log::write(var_export($ids, true));
        if (($ids['id_status']!=1)&&($ids["company_status"]==0)) {
            $https=session("xieyi");
            if (empty($https)) {
                $https="https";
            }
            $return=$https.'://'.$_SERVER['HTTP_HOST'].'/member/verify?id=1#fragment-1';
            echo $return;
            exit;
        }
        $i = $_REQUEST['i'];
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "query_is_set_pay_password";                            //绑定认证信息的接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['identity_id']          = "20151008".$this->uid;                        //用户ID
        $data['identity_type']          = "UID";                                                    //用户标识类型 UID
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $rs =  $this->checksinaerror($result);
        log::write(var_export($rs, true));
        if ($rs['is_set_paypass'] == 'N') {
            $result= $this->setpaypwd($url, $uid);        //设置支付密码
            echo $result;
            exit;
        } elseif ($i ==1) {
            echo "OK";
            exit;
        }
    }


    // 线上发放推荐人奖励
    public function do_company_profit()
    {
        if (C('COM_PROFIT.enable')) {
            $start_time=strtotime(date("Y-m-d 00:00:00"), time());
            $end_time=strtotime(date("Y-m-d 23:59:59"), time());
            $map['add_time']=array("between",array($start_time,$end_time));
            $info=M("company_profit")->where($map)->field("borrow_id")->group("borrow_id")->select();
            if ($info) {
                $tmp=array();
                foreach ($info as $key=>$val) {
                    $tmp[]=$val["borrow_id"];
                }
                $where["id"]=array("not in",$tmp);//筛选没有付款的borrow_id
            }
            $where["second_verify_time"]=array("between",array($start_time,$end_time));
            //获取复审的ID
            $binfo=M("borrow_info")->where($where)->field("id,borrow_duration_txt,second_verify_time")->find();

            if (empty($binfo)) {
                Log::write("截止目前没有复审的标的");
                exit;
            } else {
                Log::write("复审标的号为{$binfo['id']}");
            }


            //查找这个标的是否有公司内部推荐员工
            unset($where);
            $where["b.borrow_id"]=$binfo['id'];
            $field="b.borrow_id,m.id as buid,mm.id as uid,b.investor_capital,b.id as investor_id";
            $result= M("borrow_investor b")->join("lzh_members m on m.id=b.investor_uid")->join("lzh_members mm on m.recommend_id=mm.id")->field($field)->where($where)->select();
            $during=get_day($binfo['borrow_duration_txt']);
            $add_time=time();
            $data_list=array();
            foreach ($result as $key=>$val) {
                // if (in_array($val['uid'], C("OFFLINE_UID"))) {
                //     Log::write("推荐人是陈晓升或蔡晓佳,线下结算");
                // } else
                if (partake_filter($val['uid']) || in_array($val['uid'], C("FANLI")) ){
                    unset($data);
                    $data['borrow_id']=$binfo['id'];
                    $data['uid']=$val["uid"];
                    $return_money=getFloatValue($val['investor_capital']*$during*0.012/360, 2);
                    $data['money']=$return_money;
                    $data["buid"]=$val["buid"];
                    $data["add_time"]=$add_time;
                    $data["investor_id"]=$val['investor_id'];
                    array_push($data_list, $data);
                } else {
                    Log::write("{$val['uid']}不是公司员工");
                }
            }
            if (count($data_list)==0) {
                $data0["borrow_id"]=$binfo['id'];
                $data0["add_time"]=$data0["end_time"]=$add_time;
                M("company_profit")->add($data0);
                Log::write($binfo['id']."无需要发线上奖励");
                return 0;//无需发奖励
            } else {
                M("company_profit")->addall($data_list);
            }
            $i = 0;
            $k = 0;
            $j = 0;
            $total=0;
            $trade_list = ""; //新浪的交易列表
            $phone="";
            $account_type = 'SAVING_POT';
            $newbid=borrowidlayout1($binfo['id']);
            foreach ($data_list as $key=>$val) {
                if ($i < 200) {
                    if ($k === 0) {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['money'].'~~第'.$newbid.'号标线上投资返现';
                        $k++;
                    } else {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['money'].'~~第'.$newbid.'号标线上投资返现';
                    }
                    $i++;
                    if ($i === 200) {
                        $i = 0;
                        $k = 0;
                        $j++;
                    }
                }
                $total += $val['money'];
            }
            if ($binfo['id']<1038) {
                Log::write("当前标号".$binfo['id']."从1038标号开始线上发奖");
                exit;
            }


            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $result = $sina->collecttradecompany($total, "线上发放投资奖励");
            if ($result == "APPLY_SUCCESS") {
                foreach ($trade_list as $key=>$val) {
                    $order_no = date('YmdHis') . mt_rand(100000, 999999);
                    $sina->batchpaytrade($order_no, $total, $binfo['id'], $val, "company_profit");
                }
                sinalog(0, $binfo['id'], 20, $order_no, $total, time(), 0);
            }
        }

        //顺便执行投资返利
        $this->do_invset_profit();
    }

    /**
     * 外部推荐返利
     * @return [type] [description]
     */
    public function do_outside_profit()
    {
        if (C("OUTSIDE_PROFIT.enable")) {
            $start_time=strtotime(date("Y-m-d 00:00:00"), time());
            $end_time=strtotime(date("Y-m-d 23:59:59"), time());
            $map['add_time']=array("between",array($start_time,$end_time));
            $info=M("outside_profit")->where($map)->field("borrow_id")->group("borrow_id")->select();
            if ($info) {
                $tmp=array();
                foreach ($info as $key=>$val) {
                    $tmp[]=$val["borrow_id"];
                }
                $where["id"]=array("not in",$tmp);//筛选没有付款的borrow_id
            }
            $where["second_verify_time"]=array("between",array($start_time,$end_time));
            //获取复审的ID
            $binfo=M("borrow_info")->where($where)->field("id,borrow_duration_txt,second_verify_time")->find();

            if (empty($binfo)) {
                Log::write("外部返利：截止目前没有复审的标的");
                exit;
            } else {
                Log::write("外部返利：复审标的号为{$binfo['id']}");
            }

            //查找这个标的是否有公司内部推荐员工
            unset($where);
            $where["b.borrow_id"]=$binfo['id'];
            $field = "b.borrow_id,m.id as buid,mm.id as uid,b.investor_capital,b.id as investor_id,m.reg_time,b.investor_uid as invuid";
            $result = M("borrow_investor b")
                    ->join("lzh_members m on m.id=b.investor_uid")
                    ->join("lzh_members mm on m.recommend_id=mm.id")
                    ->field($field)
                    ->where($where)
                    ->select();
            $during = get_day($binfo['borrow_duration_txt']);
            $add_time = time();
            $data_list = array();
            $store_list = array();

            $smslist = [];
            foreach ($result as $key=>$val) {
                if ($val['reg_time'] > strtotime(C("THE_MAY_ACTIVE.start_time"))) {
                    if (!partake_filter($val['uid']) || !in_array($val['uid'], C("FANLI"))) {
                        unset($data);
                        $data['borrow_id']=$binfo['id'];
                        $data['uid']=$val["uid"];
                        $return_money=getFloatValue($val['investor_capital']*$during*C("OUTSIDE_PROFIT.simple_fee")/360, 2);
                        $data['return_money']=$return_money;
                        $data['invest_money']=$val['investor_capital'];
                        $data["invest_uid"]=$val["buid"];
                        $data["add_time"]=$add_time;
                        $data["investor_id"]=$val['investor_id'];

                        // uid is parent id
                        $smslist[$val['uid']] = [];
                        $smslist[$val['uid']]['rebate'] = $return_money;
                        $smslist[$val['uid']]['childid'] = $val['invuid'];

                        if($data['uid'] > 0){
                         array_push($data_list, $data);
                        }
                        $before_user = M("members")->where(array("id"=>$val["uid"]))->field("recommend_id")->find();
                        if (in_array($before_user["recommend_id"], C("OFFLINE_UID"))) {
                            unset($data_store);
                            $data_store['borrow_id']=$binfo['id'];
                            $data_store['recommend_uid']=$val["uid"];
                            $data_store['store_uid']=$before_user["recommend_id"];
                            $return_money=getFloatValue($val['investor_capital']*$during*C("OUTSIDE_PROFIT.store_fee")/360, 2);
                            $data_store['return_money']=$return_money;
                            $data_store['invest_money']=$val['investor_capital'];
                            $data_store["invest_uid"]=$val["buid"];
                            $data_store["add_time"]=$add_time;
                            $data_store["investor_id"]=$val['investor_id'];
                            array_push($store_list, $data_store);
                        }
                    } else {
                        Log::write("外部返利：{$val['uid']}是公司员工");
                    }
                }
            }

            if (count($data_list)==0) {
                $data0["borrow_id"]=$binfo['id'];
                $data0["add_time"]=$data0["end_time"]=$add_time;
                M("outside_profit")->add($data0);
                Log::write("外部返利：".$binfo['id']."无需要发线上奖励");
                return 0;//无需发奖励
            } else {
                M("outside_profit")->addall($data_list);
            }

            if (count($store_list)>0) {
                M("store_outside")->addall($store_list);
            }

            $i = 0;
            $k = 0;
            $j = 0;
            $total=0;
            $trade_list = ""; //新浪的交易列表
            $phone="";
            $account_type = 'SAVING_POT';
            $newbid=borrowidlayout1($binfo['id']);
            foreach ($data_list as $key=>$val) {
                if ($i < 200) {
                    if ($k === 0) {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['return_money'].'~~第'.$newbid.'号标外部投资返现';
                        $k++;
                    } else {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['return_money'].'~~第'.$newbid.'号标外部投资返现';
                    }
                    $i++;
                    if ($i === 200) {
                        $i = 0;
                        $k = 0;
                        $j++;
                    }
                }
                $total += $val['return_money'];
            }

            if ($binfo['id']<1038) {
                Log::write("当前标号".$binfo['id']."从1038标号开始线上发奖");
                exit;
            }


            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $result = $sina->collecttradecompany($total, "外部投资奖励");
            if ($result == "APPLY_SUCCESS") {
                foreach ($trade_list as $key=>$val) {
                    $order_no = date('YmdHis') . mt_rand(100000, 999999);
                $sina->batchpaytrade($order_no, $total, $binfo['id'], $val, "outside_profit");

                }
                sinalog(0, $binfo['id'], 25, $order_no, $total, time(), 0);
            }

            // sms message
            if (null != $smslist) {
                // send message 
                foreach ($smslist as $uid => $value) {
                    $currentuser = M("members")->where(array("id"=>$value['childid']))->find();
                    $parent = M("members")->where(array("id"=>$uid))->find();
                    sms201711Total($parent['user_name'], $currentuser['user_name'], $value['rebate']);
                }
            }
            
        } else {
            Log::write("外部推荐返现关闭");
            exit;
        }
    }



    /**
     * 自己投资返利
     * @return [type] [description]
     */
    public function do_invset_profit()
    {
            $start_time=strtotime(date("Y-m-d 00:00:00"), time());
            $end_time=strtotime(date("Y-m-d 23:59:59"), time());
            $map['add_time']=array("between",array($start_time,$end_time));
            $info=M("invest_profit")->where($map)->field("borrow_id")->group("borrow_id")->select();
            if ($info) {
                $tmp=array();
                foreach ($info as $key=>$val) {
                    $tmp[]=$val["borrow_id"];
                }
                $where["id"]=array("not in",$tmp);//筛选没有付款的borrow_id
            }
            $where["second_verify_time"]=array("between",array($start_time,$end_time));
            //获取复审的ID
            $binfo=M("borrow_info")->where($where)->field("id,borrow_duration_txt,second_verify_time")->find();

            if (empty($binfo)) {
                Log::write("投标返利：截止目前没有复审的标的");
                exit;
            } else {
                Log::write("投标返利：复审标的号为{$binfo['id']}");
            }

            //查找这个标的是否有指定返利人员
            unset($where);
            $where["b.borrow_id"]=$binfo['id'];
            $where["m.is_rebate"]=1;
            $field = "b.borrow_id,m.id as uid,b.investor_capital,b.id as investor_id,m.is_rebate";
            $result = M("borrow_investor b")
                    ->join("lzh_members m on m.id=b.investor_uid")
                    ->field($field)
                    ->where($where)
                    ->select();
            $during = get_day($binfo['borrow_duration_txt']);
            $add_time = time();
            $data_list = array();
            $store_list = array();
            foreach ($result as $key=>$val) {
                if (!partake_filter($val['uid']) || !in_array($val['uid'], C("FANLI")) ){
                    unset($data);
                    $data['borrow_id']=$binfo['id'];
                    $data['uid']=$val["uid"];
                    $return_money=getFloatValue($val['investor_capital']*$during*C("OUTSIDE_PROFIT.simple_fee")/360, 2);
                    $data['return_money']=$return_money;
                    $data['invest_money']=$val['investor_capital'];
                    $data["add_time"]=$add_time;
                    $data["investor_id"]=$val['investor_id'];
                    array_push($data_list, $data);
                } else {
                    unset($data);
                    $data['borrow_id']=$binfo['id'];
                    $data['uid']=$val["uid"];
                    $return_money=getFloatValue($val['investor_capital']*$during*0.012/360, 2);
                    $data['return_money']=$return_money;
                    $data['invest_money']=$val['investor_capital'];
                    $data["add_time"]=$add_time;
                    $data["investor_id"]=$val['investor_id'];
                    array_push($data_list, $data);

                }
            }

            if (count($data_list)==0) {
                $data0["borrow_id"]=$binfo['id'];
                $data0["add_time"]=$data0["end_time"]=$add_time;
                M("invest_profit")->add($data0);
                Log::write("投标返利：".$binfo['id']."无需要发线上奖励");
                return 0;//无需发奖励
            } else {
                M("invest_profit")->addall($data_list);
            }

            $i = 0;
            $k = 0;
            $j = 0;
            $total=0;
            $trade_list = ""; //新浪的交易列表
            $phone="";
            $account_type = 'SAVING_POT';
            $newbid=borrowidlayout1($binfo['id']);
            foreach ($data_list as $key=>$val) {
                if ($i < 200) {
                    if ($k === 0) {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['return_money'].'~~第'.$newbid.'号标自己投资返现';
                        $k++;
                    } else {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['uid'].'~UID~'.$account_type.'~'.$val['return_money'].'~~第'.$newbid.'号标自己投资返现';
                    }
                    $i++;
                    if ($i === 200) {
                        $i = 0;
                        $k = 0;
                        $j++;
                    }
                }
                $total += $val['return_money'];
            }

            if ($binfo['id']<1038) {
                Log::write("当前标号".$binfo['id']."从1038标号开始线上发奖");
                exit;
            }


            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $result = $sina->collecttradecompany($total, "自己投资奖励");
            if ($result == "APPLY_SUCCESS") {
                foreach ($trade_list as $key=>$val) {
                    $order_no = date('YmdHis') . mt_rand(100000, 999999);
                $sina->batchpaytrade($order_no, $total, $binfo['id'], $val, "invest_profit");
                    
                }
                sinalog(0, $binfo['id'], 25, $order_no, $total, time(), 0);
            }
    }


    //体验标分账
    public function paytonewhand()
    {
        $deadline = strtotime(date("Y-m-d 23:59:59", time()));
        $repay_count = M("investor_detail_experience")->where("borrow_id = 1 AND status = 1 AND repayment_time = 0 AND deadline <= {$deadline}")->count();
        if ($repay_count > 0) {
            $repay = M("investor_detail_experience")->where("borrow_id = 1 AND status = 1 AND repayment_time = 0 AND deadline <= {$deadline}")->select();
            foreach ($repay as $key=>$val) {
                $investor_id.=(empty($investor_id))?$val['id']:",{$val['id']}";
                $vphone = M("members")->field("user_phone")->where("id = {$val['investor_uid']} ")->find();
                $sphone.=(empty($sphone))?$vphone['user_phone']:",{$vphone['user_phone']}";
                $c_data['user_phone'] = $vphone['user_phone'];
                $c_data['money'] = $val['interest'];
                $c_data['endtime'] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));
                $c_data['status'] = 0;
                $c_data['serial_number'] = date('YmdHis') . mt_rand(100000, 999999);
                $c_data['type'] = 1;
                $c_data['use_money'] = $val['interest']*100;
                M("coupons")->add($c_data);
            }
            $data['status'] = 2;
            $data['repayment_time'] = time();
            M("investor_detail_experience")->where("id IN ({$investor_id})")->save($data);
            $content = "尊敬的链金所用户！您投资的新手体验标利息已成功转换为投资券并发放到平台账户，请您登录后在我的赠券中查看，如有疑问请与客服中心联系400-6626-985。";
            sendsms($sphone, $content);
        } else {
            Log::write("没有需要分账的体验标");
            exit;
        }
    }

    /**
     * 所有的体验金过期设置状态为过期
     */
    public function tiyanjin()
    {
        $model=M("coupons");
        $time=time();
        $sql="update lzh_coupons c set c.status=2 where c.status=0 and (c.endtime-$time)<=0 ";
        $flag=$model->execute($sql);
        if ($flag) {
            Log::write("跟新体验金和投资券过期成功");
            exit;
        } else {
            Log::write("跟新体验金和投资券过期失败");
            exit;
        }
    }

    /**
     * 【链金所】尊敬的用户！您的体验金将于3天后过期，请及时使用，链金所助您资产稳健增值，详询客服中心：400-6626-985
     * 体验金明天要过期短信提醒
     */
    public function notify()
    {
        $model=M("coupons");
        $date=date("Y-m-d", time());//获取当天的日期
        $time=strtotime($date);
        $threeday=259200;//三天
        $siday=345600;//四天
        $sql=" select user_phone from lzh_coupons c
              where c.status=0 and (c.endtime-{$time})<{$threeday} and  (c.endtime-{$time})>={$siday}  and  user_phone not in(select user_phone from lzh_message where type=1)";
        $list=$model->query($sql);//注意已经发送了的不能再次发送，用表lzh_message记录已经发送的
        if ($list && count($list)) {
            //短信一次最大可以发送500个
            $phone=[];
            $content = "尊敬的链金所用户！您的体验金将于明日过期，请及时使用，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
            foreach ($list as $key=>$va) {
                if (($key+1)%200==0) {
                    $sphone=implode(",", $phone);
                    sendsms($sphone, $content);
                    $phone=[];//清空以前存储的手机号
                }
                $phone[]=$va["user_phone"];
                M("message")->add(array("user_phone"=>$va["user_phone"],"addtime"=>time(),"type"=>1,"desc"=>"链金锁体验标3天后过期短信提醒","content"=>$content));
            }
            $sphone=implode(",", $phone);//剩下的不足500的部分直接发送
            sendsms($sphone, $content);
        }
    }



    /**
     * 追梦活动，３个奖品每天最多开５期，当天５期开完结束
     * @return [type] [description]
     */
    public function dream()
    {
        checkDreamStatus();
        $this->checkSchedual();

        $timenow = time();
        $glo = get_global_setting();
        $start = $glo['dream_start_time'];
        $del = intval(($timenow - $start)/86400);
        $daynum = $del + 1;

        $model = new Model();
        try {
            $model->startTrans();

            for ($i=1; $i <4 ; $i++) {
                $this->checkPrize($i);
            }

            $model->commit();
        } catch (Exception $e) {
            $model->rollback();

            $tmp['create_time'] = time();
            $tmp['type'] = 5;
            $tmp['desc'] = "day {$daynum} , sync failed msg = ".$e->getMessage();
            M('dream_log')->add($tmp);
            exit('failed');
        }

        $tmp['create_time'] = time();
        $tmp['type'] = 5;
        $tmp['desc'] = "day {$daynum} , sync success";
        M('dream_log')->add($tmp);
        exit('success');
    }

    private function checkPrize($n)
    {
        $prihis = M('dream_prizehistory')->where(array('prize_type'=>$n))->order('id desc')->find();
        $priInfo = M('dream_prize')->where(array('type'=>$n))->order('id desc')->find();

        if (!$prihis) {
        }

        if (!$priInfo) {
        }

        $cur = $prihis['qishu'];
        $inv = $priInfo['inventory'];


        $max = $cur + 4;
        //如果今天的期数已经开满，则需要多增加５期(not 4)
        if ($prihis['status'] == 1) {
            $max = $max + 1;
        }

        //update prize
        $updata['inventory'] = $max;
        $saveRes = M('dream_prize')->where(array('type'=>$n))->save($updata);
        if ($saveRes === false) {
            throw new Exception("1sync {$n} failed ".json_encode($updata).'sq ='.M('prize')->getLastSql(), 1);
        }

        if ($cur < $inv) {
            $this->updateLog($n, $inv, $max);
            return;
        }

        if (($cur == $inv)&&($prihis['status']==0)) {
            $this->updateLog($n, $inv, $max);
            return;
        }

        //insert new pri his
        $newPrizeHistory['prize_id']          = $priInfo['id'];
        $newPrizeHistory['prize_name']        = $priInfo['name'];
        $newPrizeHistory['prize_min_feeds']   = $priInfo['min_feeds'];
        $newPrizeHistory['prize_total_feeds'] = $priInfo['total_feeds'];
        $newPrizeHistory['prize_type']        = $priInfo['type'];
        $newPrizeHistory['create_time']       = time();
        $newPrizeHistory['feeds_left']        = $newPrizeHistory['prize_total_feeds'];
        $newPrizeHistory['invest_times']      = 0;
        $newPrizeHistory['qishu']             = $cur + 1;
        $res2 = M('dream_prizehistory')->add($newPrizeHistory);
        if ($res2 === false) {
            throw new Exception("sync failed ,type {$n} new history record insert failed", 1);
        }

        $this->updateLog($n, $inv, $max);
    }

    private function updateLog($type, $cur, $max)
    {
        $tmp['create_time'] = time();
        $tmp['type'] = 5;
        $tmp['desc'] = "update prize ,type {$type} cur = {$cur} to {$max}";
        M('dream_log')->add($tmp);
    }

    /**
     *
     * check timetable for activity
     */
    private function checkSchedual()
    {
        $glo = get_global_setting();
        $start = $glo['dream_start_time'];
        $end = $glo['dream_end_time'];
        $status = $glo['dream_status'];

        if ($status == 0) {
            exit(0);
        }

        if ($start > time()) {
            exit(0);
        }

        if ($end < time()) {
            exit;
        }
    }

    //活动体验标分账
    public function themayactiveborrow()
    {
        $deadline = strtotime(date("Y-m-d 23:59:59", time()));
        $repay_count = M("investor_detail_experience de")->join("lzh_recommend_first rf ON rf.recommend_uid = de.investor_uid")->where("de.borrow_id = 2 AND de.status = 1 AND de.repayment_time = 0 AND de.deadline <= {$deadline} AND rf.is_freeze = 0")->count();
        Log::write("分账的体验标数量:".$repay_count."\n");
        if ($repay_count > 0) {
            $repay = M("investor_detail_experience de")->join("lzh_recommend_first rf ON rf.recommend_uid = de.investor_uid")->where("de.borrow_id = 2 AND de.status = 1 AND de.repayment_time = 0 AND de.deadline <= {$deadline} AND rf.is_freeze = 0")->field("de.id,de.investor_uid,de.interest")->select();
            $total_money = 0;
            $i = 0;
            $k = 0;
            $j = 0;
            $trade_list = ""; //新浪的交易列表
            $account_type = 'SAVING_POT';
            foreach ($repay as $key=>$val) {
                $investor_id.=(empty($investor_id))?$val['id']:",{$val['id']}";
                $total_money = $total_money + $val["interest"];
                if ($i < 200) {
                    if ($k === 0) {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['investor_uid'].'~UID~'.$account_type.'~'.$val['interest'].'~~第TY2号标投资返现';
                        $k++;
                    } else {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$val['investor_uid'].'~UID~'.$account_type.'~'.$val['interest'].'~~第TY2号标投资返现';
                    }
                    $i++;
                    if ($i === 200) {
                        $i = 0;
                        $k = 0;
                        $j++;
                    }
                }
            }
            import("@.Oauth.sina.Sina");
            $sina = new Sina();
            $result = $sina->collecttradecompany($total_money, "活动体验标返利");
            Log::write("体验标付款结果：".$result);
            if ($result == "APPLY_SUCCESS") {
                foreach ($trade_list as $key=>$val) {
                    $order_no = date('YmdHis') . mt_rand(100000, 999999);
                    $sina->batchpaytrade($order_no, $total_money, 2, $val, "active_borrow");
                    sinalog(0, 2, 26, $order_no, $total_money, time(), 0);
                }
                $data['status'] = 2;
                $data['repayment_time'] = time();
                M("investor_detail_experience")->where("id IN ({$investor_id})")->save($data);
            }
        } else {
            Log::write("没有需要分账的体验标");
            exit;
        }
    }

    /**
     * 投资统计数据，统计用户首次投资当月的总投资额
     * 对应表格 lzh_invest_aggregate
     * @return [type] [description]
     */
    public function investAggregate(){
        $date = $_REQUEST['date'];
        
        if($date){
            //获取制定月的第一天和最后一天
            list($startTime, $endTime) = $this->getSpecMonthRange($date);
        } else {
            //统计上月的投资数据
            list($startTime, $endTime) = $this->getLastMonthRange();    
        }

        //遍历lzh_invest_aggregate 中所有落在时间区间　startTime - endTime 中的用户
        $timespan = $startTime.",".$endTime;
        $map['add_time'] = array("between",$timespan);
        $map['complete'] = array("NEQ", 1);

        //每次处理５０条
        $investList = M('invest_aggregate')->where($map)->limit("0,500")->select();

        if($investList === false){
            //查询出错
            exit('query error');
        }
        
        if($investList == NULL){
            exit('null data set');
        }

        if(is_array($investList)&&empty($investList)){
            exit('empty list');
        }

        foreach ($investList as $key => $value) {

            $cond['add_time'] = array("between",$timespan);
            $cond['investor_uid'] = $value['uid'];

            $sum = M('borrow_investor')->where($cond)->sum('investor_capital');

            $data['complete'] = 1;
            $data['firstmonth_invest_amount'] = $sum;

            $res = M('invest_aggregate')->where(array('id' => $value['id']))->save($data);
            if($res === false){
                //出错，记录
            }
        }

        exit('success');

    }

    /**
     * 获取上个月的timestamp 时间段
     * @return [type] array
     */
    private function getLastMonthRange()
    {
        //查询时间
        $first_day = 'first day of last month';
        $dt=date_create($first_day);
        $first_day_morning =  $dt->format('Y-m-d 00:00:00');
        $first = strtotime($first_day_morning);

        $last_day = "last day of last month";
        $dt=date_create($last_day);
        $last_day_night =  $dt->format('Y-m-d 23:59:59');
        $last = strtotime($last_day_night);

        $month[] = $first;
        $month[] = $last;
        return $month;
    }

    /**
     * 获取制定月的第一天和最后一天
     * @param  [type] $query_date [description]
     * @return [type]             [description]
     */
    private function getSpecMonthRange($query_date){
        //$query_date = '2010-02-04';

        // First day of the month.
        $startTime = strtotime(date('Y-m-01', strtotime($query_date)));

        // Last day of the month.
        $endTime =  strtotime(date('Y-m-t 23:59:59', strtotime($query_date)));

        $month[] = $startTime;
        $month[] = $endTime;
        return $month;
    }
}
