<?php
class SinanotifyAction extends HCommonAction
{
    //充值异步处理
    public function depositnotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        if ($_REQUEST["deposit_status"] == 'SUCCESS') {
            $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 1")->find();
            ccfaxapibalace($status['uid']);
            if ($status['status'] == 2) {
                //已处理充值，不在进行处理
                echo 'success';
            } else {
                $sina['status'] = 2;
                $sina['completetime'] = time();
                M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 1")->save($sina);
                // 写交易记录
                $vo = M('member_payonline')->where("requestId='".$_REQUEST["outer_trade_no"]."'")->field('nid,money,fee,uid,way,requestId')->find();
                memberMoneyLog($vo['uid'], 3, $vo['money']-$vo['fee'], "系统自动审核");
                // 短信提醒
                $vx = M('members')->field("user_name,user_phone")->find($vo['uid']);
                if ($vo['way']=="off") {
                    SMStip("payoffline", $vx['user_phone'], array("#USERANEM#","#MONEY#"), array($vx['user_name'],$vo['money']));
                } else {
                    SMStip("payonline", $vx['user_phone'], array("#USERANEM#","#MONEY#"), array($vx['user_name'],$vo['money']));
                }
                ancunRecharge($_REQUEST["outer_trade_no"]);
                //充值成功
                p9Recharge($vo['uid']);
                echo 'success';
            }
        } else {
            //充值失败！告知新浪已接收
            echo 'success';
        }
    }


    //提现异步处理
    public function withdrawnotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 2")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST["withdraw_status"] == 'PROCESSING' && $status['status'] == 1) {
            $data['status'] = 4;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 2")->save($data);
            echo 'success';
        } elseif ($_REQUEST["withdraw_status"] == 'SUCCESS' && ($status['status'] == 1 || $status['status'] == 4)) {
            $fee_no = M('withdrawlog')->where("uid={$status['uid']} AND money_orderno = {$_REQUEST["outer_trade_no"]}")->field("fee_orderno,fee")->find();
            if ($fee_no != null) {
                $sina = date('YmdHis').mt_rand(100000, 999999)."~".$fee_no["fee_orderno"]."~".$fee_no["fee"]."~提现手续费收取成功";
                //代收完成提现手续费
                $rs = sinafinishpretrade($sina);
                $d['money_status'] = 2;
                $d['fee_status'] = 2;
                $w["money_orderno"] = $_REQUEST["outer_trade_no"];
                $w["uid"]=$status['uid'];
                M('withdrawlog')->where($w)->save($d);
            }
            $data1['status'] = 2;
            $data1['completetime'] = time();
            $where["order_no"] = $_REQUEST["outer_trade_no"];
            $where["type"] = 2;
            M('sinalog')->where($where)->save($data1);

            $um = M('members')->field("user_name,user_phone")->find($status['uid']);
            $content = "尊敬的链金所用户您好！您在平台申请的提现：{$_REQUEST["withdraw_amount"]}元，已经成功到账了。我们非常感谢您对链金所平台的支持，我们期待与您再次合作，欢迎您随时与我们联系，客服热线：400-6626-985。";
            sendsms($um['user_phone'], $content);
            ancunwithdraw($_REQUEST["outer_trade_no"]);
            echo 'success';
        } elseif (($_REQUEST["withdraw_status"] == 'FAILED' || $_REQUEST["withdraw_status"] == 'RETURNT_TICKET') && $status['status'] != 3) {
            $info = M('withdrawlog')->where("uid={$status['uid']} AND money_orderno={$_REQUEST["outer_trade_no"]}")->field("fee_orderno,fee")->find();
            //代收撤销
            $data['uid'] = $status['uid'];
            $data['money'] = $info['fee'];
            $data["orderno"] = $info['fee_orderno'];
            $rs = sinacancelpretrade($data);
            $sina['status'] = 3;
            $sina['completetime'] = time();
            $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 2")->save($sina);
            if ($status['status'] != 1) {
                $um = M('members')->field("user_name,user_phone")->find($status['uid']);
                $content = "尊敬的链金所用户您好！您的提现申请未成功，请您与客服中心联系400-6626-985。";
                sendsms($um['user_phone'], $content);
            }
            echo 'success'; //失败！告知新浪已接收
        } else {
            echo 'success';
        }
    }

    //付提现手续费异步处理
    public function withdrawfreenotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 8")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'PRE_AUTH_APPLY_SUCCESS' && $status['status']==1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 8")->save($sina);
            $data['fee_status'] = 1;
            M("withdrawlog")->where("fee_orderno = {$_REQUEST["outer_trade_no"]}")->save($data);
            memberMoneyLog($status['uid'], 79, -$status["money"], "提现手续费", '0', '@SINA@', 0, 0);
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == "TRADE_FINISHED" && $status['status'] == 2) {
            sinapayfreetrade($status['money']);
            $data['fee_status'] = 2;
            M("withdrawlog")->where("fee_orderno = {$_REQUEST["outer_trade_no"]}")->save($data);
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PRE_AUTH_CANCELED') {
            $data['fee_status'] = 3;
            M("withdrawlog")->where("fee_orderno = {$_REQUEST["outer_trade_no"]}")->save($data);
            memberMoneyLog($status['uid'], 79, $status["money"], "提现失败，手续费退回", '0', '@SINA@', 0, 0);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //收提现手续费异步处理
    public function freenotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 9")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status['status']==1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 9")->save($sina);
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 周年庆活动
     * @param $user
     * @param $uid
     */
    private function zhouninaqing($user, $uid)
    {
        $starttime=strtotime(C("zhounian_starttime"));
        $endtime=strtotime(C("zhounian_endtime"));
        $listmoney=M('sinalog')->query("select sum(money) as money from lzh_sinalog t where t.type=3 and (t.status=2 or t.status=4) and t.completetime>=$starttime and t.completetime<=$endtime and t.uid={$uid}");
        if ($listmoney&& $listmoney[0]["money"]) {//在此期间有投资
            $money=$listmoney[0]["money"]/10000;
            $rulelist=M("activity_rule")->where(array("money"=>array("elt",$money)))->order("money desc")->select();
            if ($rulelist && $rulelist[0]) {//如果能找到规则则向奖品表插入数据
                $pricelist=M("activity")->where(array("uid"=>$uid))->find();
                if ($pricelist) {//如果存在
                    M("activity")->where(array("id"=>$pricelist["id"]))->save(array(
                        "money"=>$listmoney[0]["money"],
                        "priceid"=>$rulelist[0]["goodsid"]
                    ));//更新投资总额和奖品对应礼品编号
                } else {//则插入数据
                    M("activity")->add(array(
                        "uid"=>$uid,
                        "uname"=>$user["user_name"],
                        "phone"=>$user["user_phone"],
                        "registertime"=>date("Ymd", $user["reg_time"]),
                        "money"=>$listmoney[0]["money"],
                        "priceid"=>$rulelist[0]["goodsid"]
                    ));
                }
            }
        }
    }

    //投标异步处理结果
    public function borrownotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $stype = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->find();
        ccfaxapibalace($stype['uid']);
        if ($_REQUEST['trade_status'] == 'PRE_AUTH_APPLY_SUCCESS' && $stype['status']==1) {
            //交易结束
            $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->find();
            $borrow_id = $status['borrow_id'];
            $money = $status['money'];
            $uid = $status['uid'];
            // $done = investMoney($uid,$borrow_id,$money,$status["is_auto"]);
            //是否使用加息券
            if (!empty($status['jx_coupons'])) {
                $jx = M('coupons')->where('serial_number = '.$status['jx_coupons'])->find();
                $jx_rate = $jx['money'];
            }
            $done = investMoney($uid, $borrow_id, $money, $status["is_auto"], $jx_rate);
            if ($done === true) {
                //投标成功
                $sina['status'] = 2;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->save($sina);
                $user = M('members')->where('id='.$uid)->find();
                $newbid=borrowidlayout1($borrow_id);
                $content = " 尊敬的链金所用户您好！您投资的第".$newbid."号标已成功。您可登录平台账户查询详情，也可与客服中心联系：400-6626-985。";
                sendsms($user['user_phone'], $content);

                /************ *****************周年庆活动2016-10-13*******************************************************/
                $opentype=C("zhounain_open");
                if ($opentype=="1") {//如果开关打开
                  $this->zhouninaqing($user, $uid);
                }
                /****************************************周年活动结束****************************************************************/
               
                //如果使用投资卷链金所垫付投资资金
                if ($status['coupons'] != null || $status['coupons'] != "") {
                    $couponsModel=M("coupons");
                    $coupons_money =$couponsModel->where("serial_number = '".$status['coupons']."' and  user_phone='".$user['user_phone']."'")->find();
                    $rs = sinapaycoupons($coupons_money['money'], $newbid);
                    $couponsModel->where(array("id"=>$coupons_money["id"]))->save(array("status"=>1));
                    $coupons_money =$couponsModel->where("serial_number = '".$status['coupons']."' and  user_phone='".$user['user_phone']."'")->field("money")->find();
                    file_put_contents('log1.txt', "投资券回调中使用查询投资券sql".$couponsModel->getLastsql(), FILE_APPEND);
                }
                if ($status["is_auto"]==1) {
                    $data["uid"] = $uid;
                    $is_exist = M("auto_temp")->where($data)->count();
                    if ($is_exist>0) {
                        M("auto_temp")->where($data)->delete();
                    }
                    M("auto_temp")->add($data);
                }
                //判断是否有使用加息券
                if (!empty($status['jx_coupons'])) {
                    $result =  M("coupons")->where(array("serial_number"=>$status['jx_coupons']))->save(array("status"=>1));
                    file_put_contents('log1.txt', '用户:'.$status['uid'].'使用加息券:'.$status['jx_coupons'].'结果:'.$result, FILE_APPEND);
                }
                $disdata['amount'] = $status['money'];
                $disdata['usrid'] = $user['recommend_id'];
                setDistribut($disdata);
                ancunInvestSafe($_REQUEST["outer_trade_no"]);
                //调用风车接口
                $fc_count = M("members_fengche")->where(array("uid"=>$status['uid']))->count();
                if ($fc_count>0) {
                    $fc_data["uid"] = $status['uid'];
                    $fc_data["bid"] = $status['borrow_id'];
                    $fc_url = C("CCFAXAPI_URL")."/fengche/notice/sendToFc";
                    $this->curl_post($fc_url, $fc_data);
                }

                //调用车轮接口
                $fc_count = M("members_chelun")->where(array("uid"=>$status['uid']))->count();
                if ($fc_count>0) {
                    $fc_data["uid"] = $status['uid'];
                    $fc_data["bid"] = $status['borrow_id'];
                    $fc_url = C("CCFAXAPI_URL")."/chelun/notice/sendToCl";
                    $this->curl_post($fc_url, $fc_data);
                }
                
                $cl_data_info["bid"] = $status['borrow_id'];
                $cl_data_url = C("CCFAXAPI_URL")."/chelun/notice/sendInfoToCl";
                $this->curl_post($cl_data_url, $cl_data_info);

                $where['id'] = $status['uid'];
                $rph_info = M('members')->where($where)->find();

                //调用融普惠导航数据更新接口，对当前标的投标进度进行更新
                $rph_data["bid"] = $borrow_id;
                $rph_url = C("CCFAXAPI_URL")."/rph/index/updateDB";
                $this->curl_post($rph_url, $rph_data);


                if ($rph_info['equipment'] == "rph") {
                    //调用融普惠投资返利接口
                    $fields = "m.user_phone,l.money,bi.borrow_type,bi.borrow_duration";
                    unset($where);
                    $where['l.order_no'] = $_REQUEST["outer_trade_no"];
                    $where['l.type'] = 3;
                    $invest_info = M('sinalog l')->field($fields)
                                                 ->join('lzh_borrow_info bi on bi.id = l.borrow_id')
                                                 ->join("lzh_members m ON m.id=l.uid")
                                                 ->where($where)
                                                 ->find();
                    $rph_invest_data['mobile'] = $invest_info['user_phone'];
                    $rph_invest_data['money'] = $invest_info['money'];
                    $rph_invest_data['type'] = $invest_info['borrow_type'];
                    $rph_invest_data['duration'] = $invest_info['borrow_duration'];
                    $rph_url_1 = C("CCFAXAPI_URL")."/rph/Index/investsuccessNotify";
                    $this->curl_post($rph_url_1, $rph_invest_data);
                }


                if ($rph_info['equipment'] == "rph") {
                    //调用融普惠投资返利接口
                    $fields = "m.user_phone,l.money,bi.borrow_type,bi.borrow_duration";
                    unset($where);
                    $where['l.order_no'] = $_REQUEST["outer_trade_no"];
                    $where['l.type'] = 3;
                    $invest_info = M('sinalog l')->field($fields)
                                                 ->join('lzh_borrow_info bi on bi.id = l.borrow_id')
                                                 ->join("lzh_members m ON m.id=l.uid")
                                                 ->where($where)
                                                 ->find();
                    $rph_invest_data['mobile'] = $invest_info['user_phone'];
                    $rph_invest_data['money'] = $invest_info['money'];
                    $rph_invest_data['type'] = $invest_info['borrow_type'];
                    $rph_invest_data['duration'] = $invest_info['borrow_duration'];
                    $rph_url_1 = C("CCFAXAPI_URL")."/rph/Index/investsuccessNotify";
                    $this->curl_post($rph_url_1, $rph_invest_data);
                }

                $borrowInfo = M('borrow_info')->where(array('id'=>$borrow_id))->find();
                $days = get_day($borrowInfo['borrow_duration_txt']);

                $this->cpsNotify($rph_info);

                // 追梦活动
                $this->chaseYourDream(intval($money), $uid);

                // 20170801 大转盘活动
                $this->vcCount(intval($money), $uid, $days, $borrow_id);

                // 20170801 融资推荐活动
                $this->vcRecom(intval($money), $uid, $days, $borrow_id);

                // 20170801 A轮融资豪礼
                $this->vcGift(intval($money), $uid, $days, $borrow_id);

                $this->p9123(intval($money), $uid, $days, $borrow_id);
                $this->p9Invest2000(intval($money), $uid, $days, $borrow_id);

                $this->huodong201711PaymentCallback(intval($money), $uid, $days, $borrow_id);



                //记录首次投资记录
                $in = M('borrow_investor')->where(array('investor_uid'=>$uid,'investor_capital'=>$money,'borrow_id'=>$borrow_id))->select();

                if(is_array($in)&&!empty($in)){
                    $last = array_pop($in);
                    $borrow_investor_id = $last['id'];
                } else {
                    $borrow_investor_id = -1;
                }
                
                $this->investAggregate(intval($money), $uid, $borrow_investor_id);

                ccfaxapibalace($status['uid']);

                //五月活动
                the_may_active("invest", $status['uid'], $money);
            } else {
                //投标失败
                $sina['status'] = 3;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->save($sina);
                //代收撤销
                logw("撤销123123start");
                $data['uid'] = $uid;
                $data['bid'] = $borrow_id;
                $data['money'] = $money;
                $data["orderno"] = $_REQUEST["outer_trade_no"];
                sinacancelpretrade($data);
                logw("撤销123123end");
            }
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $stype['status']==2) {
            $dataname = C('DB_NAME');
            $db_host = C('DB_HOST');
            $db_user = C('DB_USER');
            $db_pwd = C('DB_PWD');
            $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
            $bdb->beginTransaction();
            //$bId = $borrow_id;
            $sql1 ="SELECT suo FROM lzh_borrow_info_lock WHERE id = ? FOR UPDATE";
            $stmt1 = $bdb->prepare($sql1);
            $stmt1->bindParam(1, $stype['borrow_id']);    //绑定第一个参数值
            $stmt1->execute();
            $sinalog = M('sinalog');
            $sinalog->startTrans();
            $status = $sinalog->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->find();
            $sina['status'] = 4;
            $sina['completetime'] = time();
            $sinalog->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->save($sina);

            $count = $sinalog->where("borrow_id={$status['borrow_id']} AND status=4 AND type = 3")->count();
            $binfo = M("borrow_info")->where("id={$status['borrow_id']}")->field("borrow_uid,borrow_money,borrow_times")->find();
            if ($count == $binfo["borrow_times"]) {
                //新浪代付接口 付款给借款人
                $utype = M("members")->where("id={$binfo["borrow_uid"]}")->field("user_regtype")->find();
                $sina['uid'] = $binfo["borrow_uid"]; //借款人ID
                $sina['money'] = $binfo["borrow_money"]; //借款金额
                $sina['bid'] = $status['borrow_id']; //标号
                $btype = M("borrow_info")->where("id={$sina["bid"]}")->find();
                if ($btype['product_type'] == 5) {
                    $allwood_config = C('ALLWOOD_ORDER');
                    paytocard($allwood_config['uid'], $binfo["borrow_money"], $status['borrow_id']);//全木行打款到线下
                } else {
                    $tocard = M("borrow_info_additional")->where("bid={$sina["bid"]}")->field("is_tocard")->find();
                    if ($tocard["is_tocard"] == 0) {
                        if ($utype['user_regtype']==1) {
                            $sina['account_type'] = 'SAVING_POT';
                        } else {
                            $sina['account_type'] = 'BASIC';
                        }
                        sinatrade($sina);
                    } else {
                        paytocard($binfo["borrow_uid"], $binfo["borrow_money"], $status['borrow_id']);//代付提现卡
                    }
                }

                import("@.redis.Distribut");
                $distribut = new Distribut();
                $distribut->release_distribut($sina['bid']);

                //调用车轮接口
                $cl_count = M("borrow_investor bi")
                            ->join("lzh_members_chelun mc on mc.uid = bi.investor_uid")
                            ->where("bi.borrow_id = ".$sina["bid"])
                            ->count();
                if ($cl_count>0) {
                    $cl_data["bid"] = $sina["bid"];
                    $cl_url = C("CCFAXAPI_URL")."/chelun/notice/sendToCl";
                    $this->curl_post($cl_url, $cl_data);
                }
            }
            $sinalog->commit();
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PRE_AUTH_CANCELED') {
            logw("撤销123123345345start");
            $sina['status'] = 2;
            $sina['completetime'] = time();
            $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 5")->save($sina);
            //投标失败做退款处理
            if ($rs) {
                //sinarefund($_REQUEST["outer_trade_no"],$_REQUEST["trade_amount"],$status['uid'],$borrow_id);
                $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 3")->find();
                $borrow_id = $status['borrow_id'];
                $money = $status['money'];
                $uid = $status['uid'];
                $user = M('members')->where('id='.$uid)->find();
                $newbid=borrowidlayout1($borrow_id);
                $content = "尊敬的链金所用户您好！您投资的第".$newbid."号标未成功，我们已做退款处理，您可以登录平台账户查询详情，也可与客服中心联系：400-6626-985。";
                sendsms($user['user_phone'], $content);
            }
            logw("撤销123123345345end");
            echo 'success';
        } else {
            //交易失败
            echo 'success';
        }
    }

    /**
     * 债权转让投标异步处理结果
     */
    public function zhaiquanborrownotify()
    {
        file_put_contents('debtlog.txt', var_export($_REQUEST, true), FILE_APPEND);
        $stype = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->find();
        @ccfaxapibalace($stype['uid']);
        if ($_REQUEST['trade_status'] == 'PRE_AUTH_APPLY_SUCCESS' && $stype['status']==1) {
            file_put_contents('debtlog.txt', "债权冻结成功\n", FILE_APPEND);
            //交易结束
            $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->find();
            $borrow_id = $status['borrow_id'];
            $money = $status['money'];
            $uid = $status['uid'];
            $done=zhaiquan_investMoney($uid, $borrow_id, $money);
            if ($done === true) {
                //投标成功
                $sina['status'] = 2;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->save($sina);
                $user = M('members')->where('id='.$uid)->find();
                $content = " 尊敬的链金所用户您好！您投资的第ZQ".$borrow_id."号标已成功。您可登录平台账户查询详情，也可与客服中心联系：400-6626-985。";
                sendsms($user['user_phone'], $content);
            } else {
                //投标失败
                $sina['status'] = 3;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->save($sina);
                //代收撤销
                $data['uid'] = $uid;
                $data['bid'] = $borrow_id;
                $data['money'] = $money;
                $data["orderno"] = $_REQUEST["outer_trade_no"];
                sinacancelpretrade($data, 2);
            }
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $stype['status']==2) {
            $sinalog = M('sinalog');
            $status = $sinalog->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->find();
            $sina['status'] = 4;
            $sina['completetime'] = time();
            $sinalog->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->save($sina);

            //处理债权平均分
            zhaiquan_fen($status["borrow_id"], $status["uid"], $status["money"]);

            $count = $sinalog->where("borrow_id={$status['borrow_id']} AND status=4 AND type = 16")->count();
            $binfo = M("debt_borrow_info")->where("id={$status['borrow_id']}")->field("id,borrow_uid,borrow_money,borrow_times")->find();
            file_put_contents('debtlog.txt', "债权复审代付：收取成功次数：".$count."，投资人数:".$binfo["borrow_times"]."\n", FILE_APPEND);
            if ($count == $binfo["borrow_times"]) {
                file_put_contents('debtlog.txt', "债权复审代付：可以付款了\n", FILE_APPEND);

                //新浪代付接口 付款给借款人
                $utype = M("members")->where("id={$binfo["borrow_uid"]}")->field("user_regtype")->find();
                $sina['uid'] = $binfo["borrow_uid"]; //借款人ID
                $sina['money'] = $binfo["borrow_money"]; //借款金额
                $sina['bid'] = $status['borrow_id']; //标号
                if ($utype['user_regtype']==1) {
                    $sina['account_type'] = 'SAVING_POT';
                } else {
                    $sina['account_type'] = 'BASIC';
                }
                sinatrade($sina, 2);
            }
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PRE_AUTH_CANCELED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 22")->save($sina);//退款
            $sina1['status'] = 3;
            $sina1['completetime'] = time();
            $rs1 = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->save($sina1);//退款
            //投标失败做退款处理
            if ($rs || $rs1) {
                $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->find();
                $borrow_id = $status['borrow_id'];
                $money = $status['money'];
                $uid = $status['uid'];
                $user = M('members')->where('id='.$uid)->find();
                $content = "尊敬的链金所用户您好！您投资的第ZQ".$borrow_id."号标未成功，我们已做退款处理，您可以登录平台账户查询详情，也可与客服中心联系：400-6626-985。";
                sendsms($user['user_phone'], $content);
                $debt_info = M("debt_borrow_info")->where(array("id"=>$borrow_id))->find();
                $tui_count = M("sinalog")->where(array("type"=>16,"borrow_id"=>$borrow_id,"status"=>3))->count();
                file_put_contents('debtlog.txt', "债权撤销\n", FILE_APPEND);
                if ($debt_info["borrow_times"] == $tui_count && $debt_info["borrow_times"] > 0 && $debt_info["collect_time"] < time()) {
                    file_put_contents('debtlog.txt', "债权撤销：处理记录\n", FILE_APPEND);
                    $rsd = M("debt_borrow_info")->where(array("id"=>$borrow_id))->save(array("borrow_status"=>3));
                    $rsd1 = M("borrow_investor")->where(array("id"=>$debt_info["invest_id"]))->save(array("debt_status"=>0));
                    $rsd2 = M("borrow_investor")->where(array("debt_id"=>$borrow_id))->save(array("status"=>2));
                    file_put_contents('debtlog.txt', "债权撤销：处理记录:标的结果:".$rsd."，转让人：".$rsd1."，购买人:".$rsd2."\n", FILE_APPEND);
                    $content1 = "尊敬的链金所用户！您提交的债权ZQ{$borrow_id}号标转让失败，可于下个工作日登陆平台账户再次申请转让，如有疑问请与客服中心联系400-6626-985.";
                    $user1 = M("members")->where(array("id"=>$debt_info["borrow_uid"]))->find();
                    sendsms($user1['user_phone'], $content1);
                    // import("@.Sdk.API");
                    // $api=new API();
                    // $ccfax_java = C('CCFAX_JAVA');
                    // $url=$ccfax_java."/ccfax_background/debt/flowDebtBorrow.do";
                    // $result=$api->request($url,array("borrow_id"=>$borrow_id,"borrow_uid"=>$debt_info['borrow_uid']));
                }
            }
            echo 'success';
        } else {
            //交易失败
            echo 'success';
        }
    }

    //退款异步处理
    public function refundnotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 5")->find();
        ccfaxapibalace($status['uid']);
        $user = M('members')->where('id='.$status['uid'])->find();
        if ($_REQUEST['refund_status'] == 'SUCCESS' && $status['status'] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 5")->save($sina);
            $content = "尊敬的链金所用户您好！您投标未成功的资金已退款到您的平台账户内，您可以登录平台账户查询详情，也可以与客服中心联系：400-6626-985。";
            sendsms($user['user_phone'], $content);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //普通标异步还款还款异步处理
    public function collecttradenotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 4")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status['status'] == 1) {
            $pre = C('DB_PREFIX');
            $borrow_id = $status['borrow_id'];
            $sort_order = $status['sort_order'];
            $add_function=C("ADD_FUNCTION");
            //判断是否为代还款
            $buid = M("borrow_info")->where("id={$borrow_id}")->field("borrow_uid,danbao")->find();
            if ($buid['borrow_uid']==$status["uid"]) {
                $repayment=0;
                file_put_contents('log.txt', "自己还款\n\r", FILE_APPEND);
            } elseif ($buid['danbao']==$status["uid"]) {
                $repayment=1;
                file_put_contents('log.txt', "担保公司代还款\n\r", FILE_APPEND);
            } elseif ($add_function['repayment']['enable']) {
                $add_function=C("ADD_FUNCTION");
                $super_name=$add_function['repayment']['account'];
                $super_name1=$add_function['repayment']['account1'];
                $minfo =getMinfo($status['uid'], true);
                if ($super_name==$minfo['user_name'] || $super_name1==$minfo['user_name']) {
                    $repayment=1;
                    file_put_contents('log.txt', "网站代还款\n\r", FILE_APPEND);
                } else {
                    file_put_contents('log.txt', "为什么".$status["uid"]."要还".$borrow_id."\n\r", FILE_APPEND);
                    echo "success";
                    exit;
                }
            } else {
                file_put_contents('log.txt', "为什么".$status["uid"]."要还".$borrow_id."\n\r", FILE_APPEND);
                echo "success";
                exit;
            }

            if ($repayment) {
                $res=borrowRepayment($borrow_id, $sort_order, 2, $status["uid"]);
            } else {
                $res = borrowRepayment($borrow_id, $sort_order);
            }
            file_put_contents('errorlog.txt', var_export($res, true), FILE_APPEND);
            if (true===$res) {
                $sina['status'] = 2;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 4")->save($sina);
                //调用车轮接口
                $cl_count = M("borrow_investor bi")
                    ->join("lzh_members_chelun mc ON mc.uid = bi.investor_uid ")->where(array("borrow_id"=>$borrow_id))->count();
                if ($cl_count>0) {
                        $cl_data["bid"] = $borrow_id;
                        $cl_url = C("CCFAXAPI_URL")."/chelun/notice/sendToCl";
                        $this->curl_post($cl_url, $cl_data);
                        unset($cl_data);
                }
                
                //如果还款成功，判断是不是提前还款
                // logw("债权提前还款222222");
                // $info=M("borrow_info")->where(array("apply_status"=>2,"id"=>$borrow_id))->find();//如果是提前还款
                // if($info['deadline']<time() && $info['borrow_status']==7){//如果是提前还款并且是天标
                //  $debtinfo=M("borrow_debt t")->where(array("borrow_id"=>$borrow_id))->find();
                //  $debt_borrow_id=$debtinfo['debt_borrow_id'];
                //  import("@.Sdk.API");
                //  $api=new API();
                //  $url="http://121.201.66.7:8080/ccfax_background/debt/flowDebtBorrow.do";
                //  $result=$api->request($url,array("borrow_id"=>$debt_borrow_id,"borrow_uid"=>$debtinfo['debt_borrow_uid']));
                //  logw("债权提前还款".print_r($result,true));
                //  logw("canshu: biaohao".$borrow_id." borrow_uid:".$buid['borrow_uid']);
                // }
                // logw("债权提前还款11111");
            }
            file_put_contents('errorlog.txt', var_export($rs, true), FILE_APPEND);
            if ($rs) {
                echo 'success';
            }
        } elseif ($_REQUEST['trade_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 债权标异步还款
     */
    public function zhaiquan_collecttradenotify()
    {
        file_put_contents('log.txt', ' ', FILE_APPEND);
        file_put_contents('log.txt', '\n\r 债权还款-开始：'.var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 17")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status['status'] == 1) {
            $pre = C('DB_PREFIX');
            $borrow_id = $status['borrow_id'];
            $sort_order = $status['sort_order'];
            $add_function=C("ADD_FUNCTION");
            //判断是否为代还款
            $buid = M("borrow_info")->where("id={$borrow_id}")->field("borrow_uid,danbao")->find();
            if ($buid['borrow_uid']==$status["uid"]) {
                $repayment=0;
                file_put_contents('log.txt', "自己还款\n\r", FILE_APPEND);
            } elseif ($buid['danbao']==$status["uid"]) {
                $repayment=1;
                file_put_contents('log.txt', "担保公司代还款\n\r", FILE_APPEND);
            } elseif ($add_function['repayment']['enable']) {
                $add_function=C("ADD_FUNCTION");
                $super_name=$add_function['repayment']['account'];
                $super_name1=$add_function['repayment']['account1'];
                $minfo =getMinfo($status['uid'], true);
                if ($super_name==$minfo['user_name'] || $super_name1==$minfo['user_name']) {
                    $repayment=1;
                    file_put_contents('log.txt', "网站代还款\n\r", FILE_APPEND);
                } else {
                    file_put_contents('log.txt', "为什么1".$status["uid"]."要还".$borrow_id."\n\r", FILE_APPEND);
                    echo "success";
                    exit;
                }
            } else {
                file_put_contents('log.txt', "为什么2".$status["uid"]."要还".$borrow_id."\n\r", FILE_APPEND);
                echo "success";
                exit;
            }

            if ($repayment) {//代还款
                file_put_contents('log.txt', ' ', FILE_APPEND);
                file_put_contents('log.txt', '进入还款函数1 ', FILE_APPEND);
                $res=zhaiquan_borrowRepayment($borrow_id, $sort_order, 2, $status["uid"]);
            } else {//自己还款
                file_put_contents('log.txt', ' ', FILE_APPEND);
                file_put_contents('log.txt', '进入还款函数2 ', FILE_APPEND);
                $res =zhaiquan_borrowRepayment($borrow_id, $sort_order);
            }

            file_put_contents('errorlog.txt', "债权还款结果".var_export($res, true), FILE_APPEND);
            if (true===$res) {
                $sina['status'] = 2;
                $sina['completetime'] = time();
                $rs = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 17")->save($sina);
                $borrow_list=M("borrow_debt")->field("debt_borrow_id,id")->where(array("borrow_id"=>$borrow_id))->select();
                $debt_borrow_infoModel=M("debt_borrow_info");
                foreach ($borrow_list as $va) {
                    $debt_borrow_infoModel->where(array("id"=>$va['debt_borrow_id']))->save(array("borrow_status"=>7));
                }
            }
            file_put_contents('errorlog.txt', var_export($rs, true), FILE_APPEND);
            if ($rs) {
                echo 'success';
            }
        } elseif ($_REQUEST['trade_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //复审后付款异步处理
    public function paytradenotify()
    {
        file_put_contents('log.txt', "普通标复审处理完成".var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 7")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 7")->save($sina);
            import("@.redis.AncunProject");
            $project = new AncunProject();
            $project->release_ancun($status['borrow_id']);
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 债权复审后付款异步处理
     */
    public function zhaiquanpaytradenotify()
    {
        file_put_contents('debtlog.txt', "\n\r债权复审处理完成". var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 20")->find();//借款sinalog
        @ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 20")->save($sina);
            $debt_info = M("debt_borrow_info")->where(array("id"=>$status['borrow_id']))->find();
            M("borrow_investor")->where(array("id"=>$debt_info['invest_id']))->save(array("debt_status"=>3));
            M("investor_detail")->where(array("invest_id"=>$debt_info['invest_id'],"repayment_time"=>0))->save(array("is_debt"=>1));
            $debt_info["borrow_status"]=6;
            $debt_info["second_verify_time"]=time();
            M("debt_borrow_info")->where(array("id"=>$status['borrow_id']))->save($debt_info);
            $content = "尊敬的链金所用户！您的债权ZQ{$status['borrow_id']}号标已成功转让，资金已返还到平台账户内，请登录平台查询，如有疑问请与客服中心联系400-6626-985.";
            $user = M("members")->where(array("id"=>$debt_info["borrow_uid"]))->find();
            sendsms($user["user_phone"], $content);
            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //还款批量代付异步处理
    public function batchpaynotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 4")->find();
        ccfaxapibalace($status['borrow_id']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status['status'] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 4")->save($sina);
            $newbid = borrowidlayout1($status['borrow_id']);
            $phone = C('NOTICE_TEL.fengkong').','.C('NOTICE_TEL.caiwu');
            $content = '第'.$newbid.'号标已还款，您可登录平台查询详情。';
            sendsms($phone, $content);
            
            echo 'success';
        } else {
            echo 'success';
        }
        if ($_REQUEST['batch_status'] == 'FINISHED' && $status['status'] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            $sina['money'] = $_REQUEST['batch_amount'];
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 4")->save($sina);
            $newbid = borrowidlayout1($status['borrow_id']);
            $phone = C('NOTICE_TEL.fengkong').','.C('NOTICE_TEL.caiwu');
            $content = '第'.$newbid.'号标已还款，您可登录平台查询详情。';
            sendsms($phone, $content);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 债权还款批量代付异步处理
     */
    public function zhaiquan_batchpaynotify()
    {
        file_put_contents('log.txt', '\n\r 债权2'.var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 17")->find();
        @ccfaxapibalace($status['borrow_id']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 17")->save($sina);
            echo 'success';
            logw("zhaiquan:ok 17-1");
        }
        if ($_REQUEST['batch_status'] == 'FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            $sina['money'] = $_REQUEST['batch_amount'];
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 17")->save($sina);
            logw("zhaiquan:ok 17-2");
            echo 'success';
        } else {
            echo 'success';
            logw("zhaiquan:ok 17-3");
        }
    }


    //综合服务费代收异步处理
    public function payfeenotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 10")->select();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status[0]['status'] == 1) {
            $data['status'] = 2;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 10")->save($data);
            foreach ($status as $i) {
                $bid=borrowidlayout1($i["borrow_id"]);
                $confirm = D("Confirm");
                $confirm->UpdateFee($i["borrow_id"]);//修改状态
                $listinfo=$confirm->field("fee")->where(array("bid"=>$i["borrow_id"]))->find();
                $data1['uid'] = $status[0]['uid'];
                $data1['type'] = 77;
                $data1['affect_money'] = (-1.00)*$listinfo["fee"];
                $data1['info'] = "{$bid}标号，综合服务费";
                $data1['add_time'] = time();
                $data1['add_ip'] = get_client_ip();
                $data1['target_uname'] = "@SINA@";
                M("member_moneylog")->add($data1);
            }
            $user = M('members')->where('id='.$status[0]['uid'])->find();
            $content = " 尊敬的链金所用户您好！您当前融资款的综合服务费".$_REQUEST['trade_amount']."元已支付成功。您可在平台账户内进行当前融资款的提现操作，如有疑问请与客服中心联系：400-6626-985 我们将竭诚为您服务！";
            sendsms($user['user_phone'], $content);
            sinapaytrade($_REQUEST['trade_amount']);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 债权手续费回调
     */
    public function zhaiquan_payfeenotify()
    {
        file_put_contents('debtlog.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 19")->select();
        ccfaxapibalace($status[0]['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status[0]['status'] == 1) {
            $data['status'] = 2;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 19")->save($data);

            foreach ($status as $key => $value) {
                M("debt_borrow_info")->where(array("id"=>$value['borrow_id']))->save(array("pay_fee"=>1));
            }
            $user = M('members')->where('id='.$status[0]['uid'])->find();
            $content = " 尊敬的链金所用户您好！您当前转让标的手续费".$_REQUEST['trade_amount']."元已支付成功。您可在平台账户内进行当前融资款的提现操作，如有疑问请与客服中心联系：400-6626-985 我们将竭诚为您服务！";
            sendsms($user['user_phone'], $content);
            sinapaytrade($_REQUEST['trade_amount'], 1);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //担保金代收异步处理
    public function closeddanbaonotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 12")->select();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status[0]["status"] == 1) {
            $data['status'] = 2;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 12")->save($data);
            foreach ($status as $i) {
                $confirm = D("Confirm");
                $confirm->UpdateDanbao($i["borrow_id"]);
            }
            $binfo = M("borrow_info")->where("id={$status[0]['borrow_id']}")->field("borrow_uid")->find();
            $data1['uid'] = $binfo['borrow_uid'];
            $data1['type'] = 78;
            $data1['affect_money'] = -$_REQUEST['trade_amount'];
            $data1['info'] = "咨询服务费";
            $data1['add_time'] = time();
            $data1['add_ip'] = get_client_ip();
            $data1['target_uname'] = "@SINA@";
            M("member_moneylog")->add($data1);
            foreach ($status as $d) {
                $sina["uid"] = $d["uid"];
                $sina["money"] = $d["money"];
                $sina["bid"] = $d["borrow_id"];
                import("@.redis.redis_task");
                $paydanbao = new redis_task();
                $paydanbao->release_task("https://develop.ccfax.cn/Home/Sinanotify/paydan", $sina);
                //sinapaydanbao($sina);
            }
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //代付担保金
    public function paydan()
    {
        $sina["uid"] = $_REQUEST["uid"];
        $sina["money"] = $_REQUEST["money"];
        $sina["bid"] = $_REQUEST["bid"];
        $status = sinapaydanbao($sina);
        if ($status=="APPLY_SUCCESS") {
            echo "success";
        } else {
            echo "fail";
        }
    }

    //担保金代付异步处理
    public function paydanbaonotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 13")->find();
        ccfaxapibalace($status['uid']);
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED' && $status['status'] == 1) {
            $data["status"] = 2;
            $data["completetime"] = time();
            M("sinalog")->where("order_no='{$_REQUEST["outer_trade_no"]}' AND type = 13")->save($data);
            $data1['uid'] = $status['uid'];
            $data1['type'] = 78;
            $data1['affect_money'] = $_REQUEST['trade_amount'];
            $newbid=borrowidlayout1($status["borrow_id"]);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //企业会员审核异步处理
    public function companystatus()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $uid = M('members_company')->where("audit_order_no ='".$_REQUEST["audit_order_no"]."'")->find();
        if ($_REQUEST["audit_status"] == 'SUCCESS') {
            if ($uid>0) {
                import("@.Oauth.sina.Sina");
                $sina = new Sina();
                $comdata['uid'] = $uid['uid'];
                $comdata['agent_name'] = $uid["agent_name"];
                $comdata['agent_mobile'] = $uid["agent_mobile"];
                $comdata['license_no'] = $uid["alicense_no"];
                $rs1 = $sina->smtfundagentbuy($comdata);
                if ($rs1["response_code"] == "APPLY_SUCCESS") {
                    $data['company_status'] = 3;
                    $rs = M('members_status')->where("uid=".$uid['uid'])->save($data);
                    import("@.Oauth.ancun.Shang");
                    $shang = new Shang();
                    $shang->shanglogin($uid["legal_person_phone"], $uid['uid'], $uid['cert_no'], $uid["address"], $uid['company_name']);
                }
                echo 'success';
            }
        } else {
            //审核失败
            if ($uid>0) {
                $rs['result'] = $_REQUEST["audit_message"];
                M('members_company')->where("audit_order_no ='".$_REQUEST["audit_order_no"]."'")->save($rs);
                $data['company_status'] = 4;
                $rs = M('members_status')->where("uid=".$uid['uid'])->save($data);
                if ($rs) {
                    echo 'success';
                }
            }
        }
    }

    //红包记录
    public function hongbao()
    {
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 6")->find();
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 6")->save($sina);
            $data['uid'] = $status['uid'];
            $data['money'] = $status['money'];
            $data['status'] = 2;
            $data['add_time'] = time();
            $data['order_no'] = $_REQUEST["outer_trade_no"];
            M("hongbao")->add($data);
            //增加moneylog
            $data1['uid'] = $status['uid'];
            $data1['type'] = 45;
            $data1['affect_money'] = $status['money'];
            $data1['info'] = "活动红包奖励".$status['money']."元";
            $data1['add_time'] = time();
            $data1['add_ip'] = get_client_ip();
            $data1['target_uname'] = "@SINA@";
            M("member_moneylog")->add($data1);


            echo 'success';
        } elseif ($_REQUEST['trade_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }
    //虚拟标的利润发放奖励,暂时不用，预留
    public function vinvest_money()
    {
        echo 'success';
    }
    //公司推荐人奖励
    public function company_profit()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 20")->find();
        Log::write(var_export($status, true));
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 20")->save($sina);
            //company_profit 完成
            $where["borrow_id"]=$status["borrow_id"];
            $data["end_time"]=time();
            M("company_profit")->where($where)->save($data);
            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //外部推荐人奖励
    public function outside_profit()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->find();
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->save($sina);
            //outside_profit 完成
            $where["borrow_id"]=$status["borrow_id"];
            $data["end_time"]=time();
            $data["return_status"]=1;
            M("outside_profit")->where($where)->save($data);
            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     *  20170801 A 轮融资活动,拉新投资,新浪返现
     * @return [type] [description]
     */
    public function vcCommission()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->find();
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->save($sina);

            //记录日志
            $logdata['create_time'] = time();
            $logdata['desc'] = 'recom commission for uid ='.$status['uid'].'  total = '.$status['money'].' orderno='.$status['order_no'].' succeed';
            $logdata['type'] = 103;
            M('dream_log')->add($logdata);

            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

      /**
     *  20170801 A 轮融资活动,拉新投资,新浪返现
     * @return [type] [description]
     */
    public function vcCommission2()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->find();
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 25")->save($sina);

            //记录日志
            $logdata['create_time'] = time();
            $logdata['desc'] = 'vc rotate  for uid ='.$status['uid'].'  total = '.$status['money'].' orderno='.$status['order_no'].' succeed';
            $logdata['type'] = 105;
            M('dream_log')->add($logdata);

            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //投标奖励
    public function invest_profit()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 26")->find();
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 26")->save($sina);
            //outside_profit 完成
            $where["borrow_id"]=$status["borrow_id"];
            $data["end_time"]=time();
            $data["return_status"]=1;
            M("invest_profit")->where($where)->save($data);
            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //体验标返现
    public function active_borrow()
    {
        Log::write(var_export($_REQUEST, true));
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 26")->find();
        if (($_REQUEST['batch_status'] == 'FINISHED')&& $status["status"] == 1) {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 26")->save($sina);
            echo 'success';
        } elseif ($_REQUEST['batch_status'] == 'PAY_FINISHED') {
            //付款结束不处理
            echo 'success';
        } else {
            echo 'success';
        }
    }


    //代付提现卡
    public function paytocardnotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M("sinalog")->where("type = 14 and order_no = '".$_REQUEST["outer_trade_no"]."'")->find();
        if ($_REQUEST["withdraw_status"] == 'PROCESSING' && $status['status'] == 1) {
            file_put_contents('withdrawlog.txt', var_export($_REQUEST, true), FILE_APPEND);
            $data['status'] = 4;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 2")->save($data);
            echo 'success';
        } elseif ($_REQUEST["withdraw_status"] == 'SUCCESS' && ($status['status'] == 1 || $status['status'] == 4)) {
            $data["status"] = 2;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 14")->save($data);
            $allwood_config = C('ALLWOOD_ORDER');
            if ($status["uid"] == $allwood_config['uid']) {
                $order = M("allwood_ljs")->where("borrow_id = {$status['borrow_id']}")->find();
                $order_no = $order["allwood_orderno"];
                $datas["order_id"] = $order_no;
                $datas["collect_money"] = $status["money"];
                $result = $this->curl_post($allwood_config['URL'], $datas);
                file_put_contents('javalog.txt', var_export($result, true), FILE_APPEND);
            }
            echo 'success';
        } else {
            echo 'success';
        }
    }

    /**
     * 债权代付卡
     */
    public function zhaiquan_paytocardnotify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M("sinalog")->where("type = 21 and order_no = '".$_REQUEST["outer_trade_no"]."'")->find();
        if ($_REQUEST["withdraw_status"] == 'PROCESSING' && $status['status'] == 1) {
            file_put_contents('withdrawlog.txt', var_export($_REQUEST, true), FILE_APPEND);
            $data['status'] = 4;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 2")->save($data);
            echo 'success';
        } elseif ($_REQUEST["withdraw_status"] == 'SUCCESS' && ($status['status'] == 1 || $status['status'] == 4)) {
            $data["status"] = 2;
            $data['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 21")->save($data);
            $allwood_config = C('ALLWOOD_ORDER');
            if ($status["uid"] == $allwood_config['uid']) {
                $order = M("allwood_ljs")->where("borrow_id = {$status['borrow_id']}")->find();
                $order_no = $order["allwood_orderno"];
                $datas["order_id"] = $order_no;
                $datas["collect_money"] = $status["money"];
                $result = $this->curl_post($allwood_config['URL'], $datas);
                file_put_contents('javalog.txt', var_export($result, true), FILE_APPEND);
            }
            echo 'success';
        } else {
            echo 'success';
        }
    }

    //curl Post提交
    public function curl_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        Log::write("打募集款到全木行结果：".$result);
        return $result;
    }
    public function experience_notify()
    {
        file_put_contents('log.txt', var_export($_REQUEST, true), FILE_APPEND);
        $status = M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 16")->find();
        if ($_REQUEST['trade_status'] == 'TRADE_FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            M('sinalog')->where("order_no = '".$_REQUEST["outer_trade_no"]."' and type = 16")->save($sina);
            echo 'success';
        }
        if ($_REQUEST['batch_status'] == 'FINISHED') {
            $sina['status'] = 2;
            $sina['completetime'] = time();
            $sina['money'] = $_REQUEST['batch_amount'];
            M('sinalog')->where("order_no = '".$_REQUEST["outer_batch_no"]."' and type = 16")->save($sina);
            echo 'success';
        } else {
            echo 'success';
        }
    }

    private function cpsNotify($rph_info)
    {
        if ($rph_info['equipment'] == "fubaba") {
            //调用融普惠投资返利接口
            $fields = "m.id,m.fubabaid,m.user_phone,l.money,l.order_no,l.borrow_id,bi.borrow_type,bi.borrow_duration";
            unset($where);
            $where['l.order_no'] = $_REQUEST["outer_trade_no"];
            $where['l.type'] = 3;
            $invest_info = M('sinalog l')->field($fields)
                                     ->join('lzh_borrow_info bi on bi.id = l.borrow_id')
                                     ->join("lzh_members m ON m.id=l.uid")
                                     ->where($where)
                                     ->find();
            $userid = $invest_info['id'];
            $investlist = M('borrow_investor')->where(array('investor_uid'=>$userid))->select();
            if (is_array($investlist)&&count($investlist)>1) {
                $goodsmark = 2;
            } else {
                $goodsmark = 1;
            }

            $fubaba_invest_data['mobile']       = $invest_info['user_phone'];
            $fubaba_invest_data['investamount'] = $invest_info['money'];
            $fubaba_invest_data['contractid']   = $invest_info['borrow_id'];
            $fubaba_invest_data['fubabauid']    = $invest_info['fubabaid'];
            $fubaba_invest_data['goodsmark']    = $goodsmark;
            $fubaba_invest_data['ordersn']      = $invest_info['order_no'];

            $fubaba_url_1 = C("CCFAXAPI_URL")."/fubaba/Index/investsuccessNotify";
            $this->curl_post($fubaba_url_1, $fubaba_invest_data);
        }
    }

    /**
     * 追梦活动，投资送圆梦种子
     * @param  [type] $money [description]
     * @param  [type] $uid   [description]
     * @return [type]        [description]
     */
    private function chaseYourDream($money, $uid)
    {
        $glo = get_global_setting();
        $start = $glo['dream_start_time'];
        $end = $glo['dream_end_time'];
        $status = $glo['dream_status'];

        if ($status == 0) {
            //closed
            return ;
        }

        if ($start > time()) {
            //not ready
            return ;
        }

        if ($end < time()) {
            //is over
            return;
        }

        $savedata['dream_invest_total'] = array('exp', "dream_invest_total+".$money);
        $result = M('members')->where(array('id'=>$uid))->save($savedata);
        if ($result) {
            $logdata['create_time'] = time();
            $logdata['desc'] ="{$uid} invest ".$money;
            $logdata['type'] = 2;
            M('dream_log')->add($logdata);
        }
    }

    /**
     * A 轮融资活动 好礼不断,计算累计投资金额\
     * @param  $money 投资金额
     * @param  $uid   用户id
     * @param  $uid   投资天数 
     *
     */
    private function vcGift($money, $uid, $days=1000,$bid)
    {
        if (!$this->checkBorrowDuration($days,$bid,$uid)){
            return false;
        }

        //检查时间,如果没到时间,直接返回
        if((time()<=C('VC_FROM'))||(time()>=C('VC_TO')))
        {
            return false;
        }

        $savedata['dream_invest_total'] = array('exp', "dream_invest_total+".$money);
        $result = M('members')->where(array('id'=>$uid))->save($savedata);
        if ($result) {
            $logdata['create_time'] = time();
            $logdata['desc'] ="{$uid} invest ".$money;
            $logdata['type'] = 102;
            M('dream_log')->add($logdata);
        }
    }

    /**
     * 2017 9 月活动
     * @param  $money 投资金额
     * @param  $uid   用户id
     * @param  $uid   投资天数 
     *
     */
    private function p9123($money, $uid, $days=1000,$bid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        //已投资打用户不参与
        if (getInvestCount($uid,$glo['p9_start']) > 0 )
            return true;

        //如果是已注册为投资用户,p8_account 中创建用户,invest_money
        $data['uid'] = $uid;
        $data['user_phone'] = $info['user_phone'];
        $data['parent_id'] = $info['recommend_id'];
        $data['invest_money'] = array('exp','invest_money+'.$money);
        $data['create_time'] = time();
        $result = M('p9_count')->add($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->newUserInvestLog($uid,$money);
        }
    }

    private function p9Invest2000($money, $uid, $days=1000,$bid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        $isExist = M('p9_count')->where(['uid'=>$uid])->find();
        if(null == $isExist)
        {
            $isExist = false;
        }else{
            $isExist = true;
        }

        if(!$isExist)
            return;

        //如果是已注册为投资用户,p8_account 中创建用户,invest_money
        if($money>=2000)
        {
            $data['count_1'] = array('exp','count_1+1');
        }        
        
        $data['invest_money'] = array('exp','invest_money+'.$money);
        $result = M('p9_count')->where(['uid'=>$uid])->save($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->newUserInvestLog($uid,$money);
        }
    }

    /**
     * 201711 月活动投资回调函数
     * @param  [type] $money [description]
     * @param  [type] $uid   [description]
     * @param  [type] $days  [description]
     * @param  [type] $bId   [description]
     * @return [type]        [description]
     */
    private function huodong201711PaymentCallback($money, $uid, $days, $bId)
    {
        try {
            $this->huodong201711FirstInvest($money, $uid, $days, $bId);
            $this->huodong201711TotalInvest($money, $uid, $days, $bId);    
        } catch (Exception $e) {
            
        }
        
        
    }

    /**
     * 201711 月活动，投资回调，活动期间被推荐人首次投资金额记录
     * @param  [type] $money [description]
     * @param  [type] $uid   [description]
     * @param  [type] $days  [description]
     * @param  [type] $bId   [description]
     * @return [type]        [description]
     */
    private function huodong201711FirstInvest($money, $uid, $days, $bId)
    {
        //不在活动时间内,exit
        if(time()>$glo['start_201711']&&time()<$glo['end_201711'])
        {
            return;
        }

        $isExist = M('huodong_201711_count')->where(['uid'=>$uid,'first_invest'=>0,'bid'=>0])->find();
        if(null == $isExist)
        {
            $isExist = false;
        }else{
            $isExist = true;
        }

        if(!$isExist)
            return;
        
        $data['first_invest'] = $money;
        $data['bid'] = $bId;
        $result = M('huodong_201711_count')->where(['uid'=>$uid])->save($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->huodong201711FirstInvestLog($uid,$money,$bId);
        }
    }

    /**
     * 201711 月活动，投资回调，活动期间被推荐人投资总额记录
     * @param  [type] $money [description]
     * @param  [type] $uid   [description]
     * @param  [type] $days  [description]
     * @param  [type] $bId   [description]
     * @return [type]        [description]
     */
    private function huodong201711TotalInvest($money, $uid, $days, $bId)
    {
        //不在活动时间内,exit
        if(time()>$glo['start_201711']&&time()<$glo['end_201711'])
        {
            return;
        }

        $isExist2 = M('huodong_201711_count')->where(['uid'=>$uid])->find();
        $binfo = M('borrow_info')->find($bId);
        if(null == $isExist2 || null == $binfo)
        {
            $isExist = false;
        }else{
            $isExist = true;
        }

        if(!$isExist)
            return;      
        
        $data['invest_total'] = array('exp','invest_total+'.$money);
        $result = M('huodong_201711_count')->where(['uid'=>$uid])->save($data);

    
        $new['count_id'] = $isExist2['id'];
        $new["invest"] = $money;
        $new["create_time"] = time();
        $new['rebate'] = getFloatValue($money*$binfo['borrow_duration']*0.008/360, 2);
        $new['days'] = $binfo['borrow_duration'];
        $new['bid'] = $bId;
        $result  = M('huodong_201711_detail')->add($new);

        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->huodong201711InvestTotalLog($uid,$money);
        }


    }

    /**
     * 20170801 活动,用户投资一次,可以获得一次转盘机会
     * 投资金额不同,玩转盘时中奖概率也不同
     * 100   - 1000
     * 1001  - 5000
     * 5001  - 20000
     * 20001 - 1000000
     * 100000 以上
     *
     * @param  $money 投资金额
     * @param  $uid   用户id
     * @param  $uid   投资天数 
     * @return [type] [description]
     */
    private function vcCount($money, $uid,$days=1000,$bid)
    {
        if (!$this->checkBorrowDuration($days,$bid,$uid)){
            return false;
        }
        
        //检查时间,如果没到时间,直接返回
        if((time()<=C('VC_FROM'))||(time()>=C('VC_TO')))
        {
            return false;
        }

        //已注册为投资不可以累积抽奖次数
        if (getInvestCount($uid,C('VC_FROM')) <= 0 )
            return true;

        $index = $this->getVcIndex($money);

        //写入 vc_count 表
        $field = 'count_'.$index;
        $data['count_'.$index] = array("exp", "{$field}+1");
        $data['uid'] = $uid;

        $data1['count_'.$index] = 1;
        $data1['uid'] = $uid;

        return $this->updateOrInsert($data,$data1);

    }

    /**
     * A 轮融资拉新活动
     * @param  $money 投资金额
     * @param  $uid   用户id
     * @param  $uid   投资天数 
     * @return [type] [description]
     */
    private function vcRecom($money, $uid, $days=1000,$bid)
    {
        if (!$this->checkBorrowDuration($days,$bid,$uid)){
            return false;
        }
        
        //检查时间,如果没到时间,直接返回
        if((time()<=C('VC_FROM'))||(time()>=C('VC_TO')))
        {
            return false;
        }

        // 查询uid用户,如果存在,更新invest_money
        $query['uid'] = $uid;
        $res = M('vc_recom')->where($query)->find();

        if ($res == null)
        {
            // 已注册为投资的用户定义未新用户,累计投资金额
            if (getInvestCount($uid,C('VC_FROM')) > 0 )
                return true;

            //创建vc_recom 记录
            $userinfo = M('members')->where(array('id'=>$uid))->find();
            $newrecom['uid'] = $uid;
            $newrecom['user_phone'] = $userinfo['user_phone'];
            $newrecom['parent_id'] = 0;
            $newrecom['invest_money'] = $money;
            $newrecom['create_time'] = time();
            M('vc_recom')->add($newrecom);
        }else{
            $ori = $res['invest_money'];
            $res['invest_money'] = $res['invest_money'] + $money;
            $result = M('vc_recom')->save($res);

            $con['parent_id'] = $res['parent_id'];
            $con['invest_money'] = array("NEQ",0);
            $count = M('vc_recom')->where($con)->count();
            if ($count > 4){
                // 记录日志
                $logdata['create_time'] = time();
                $logdata['desc'] ="uid = {$uid}, bid = {$bid} investmoney = {$money}  commission number reach 4,abort";
                $logdata['type'] = 104;
                M('dream_log')->add($logdata);
                return true;
            }

            // 用户首次投资,且是别人推荐进来的,送5元投资券
            if($ori==0&&$res['parent_id']!=0)
            {
                // 返利5元
                $this->commissionAlloc($res['parent_id'],$bid);
            }
        }


    }

    /**
     * A 轮融资活动,拉人投资送5元
     * @return [type] [description]
     */
    private function commissionAlloc($uid, $bid)
    {
        $total = 5;
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $result = $sina->collecttradecompany($total, "A轮融资拉新奖励");
        if ($result == "APPLY_SUCCESS") {
            
            $order_no = date('YmdHis') . mt_rand(100000, 999999);
            $account_type = 'SAVING_POT';
            $val = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$uid.'~UID~'.$account_type.'~'.$total.'~~推荐返利';
            $sina->batchpaytrade($order_no, "", "", $val, "vcCommission");
            sinalog(0, $bid, 25, $order_no, $total, time(), 0);
        }
    }

    /**
     * vc 活动 30天一下的标不参与活动
     * @param  [type] $days [description]
     * @param  [type] $bid  [description]
     * @return [type]       [description]
     */
    private function checkBorrowDuration($days,$bid,$uid)
    {
        if ($days <= 30)
        {
            $logdata['create_time'] = time();
            $logdata['desc'] ="uid = {$uid}, bid = {$bid} days = {$days}  no vc";
            $logdata['type'] = 102;
            M('dream_log')->add($logdata);
            return false;
        }

        return true;
    }

    /**
     * 大转盘打中奖概率档位需要
     * 100   - 1000    是一档 序号为0
     * 1001  - 5000    是二档 序号为1
     * 5001  - 20000   是三档 序号为2
     * 20001 - 100000  是四档 序号为3
     *100000 - 以上    是五档 序号为4
     *错误,返回序号 -1
     * @param  $money 投资金额
     * @param  $uid   用户id
     * @param  $uid   投资天数 
     * @param  [type] $money [description]
     * @return [type]        [description]
     */
    private function getVcIndex($money){

        if (intval($money) === false)
        {
            return -1;
        }

        if ($money/100000>=1)
        {
            return 4;
        }elseif($money/20000 >= 1)
        {
            return 3;
        }elseif($money/5000 >= 1)
        {
            return 2;
        }elseif($money/1000 >= 1)
        {
            return 1;
        }elseif($money/100 >= 1){
            return 0;
        }else{
            return -1;
        }
    }

    /**
     * 查询 vc_count 中是否存在 uid 为 {uid} 的记录
     * 存在, update
     * 不存在, insert
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function updateOrInsert($data,$insertdata)
    {
        $query['uid'] = $data['uid'];
        $isExist = M('vc_count')->where($query)->find();

        if ($isExist == null)
        {
            return M('vc_count')->add($insertdata);
        }else{
            unset($data['uid']);
            foreach ($data as $key => $value) {
                $isExist[$key] = $value;
            }
            $res = M('vc_count')->save($isExist);
        }
    }



    /**
     * 投资统计，记录首次投资的borrow_invest_id　，投资金额和投资时间
     * @param  [type] $money 投资金额
     * @param  [type] $uid   投资人uid
     * @param  [type] $bid   borrow_investor_id
     * @return [type]        [description]
     */
    private function investAggregate($money, $uid, $bid)
    {

        //判断是否已经存记录，有返回
        $res = M('invest_aggregate')->where(array('uid'=>$uid))->select();
        if(is_array($res)&&!empty($res)){
            return true;
        }

        //插入记录
        $data['uid'] = $uid;
        $data['first_invest_amount'] = $money;
        $data['add_time'] = time();
        $data['borrow_investor_id'] = $bid;
        M('invest_aggregate')->add($data);
        return true;

    }
}
