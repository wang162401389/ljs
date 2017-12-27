<?php
/**
* 关于我们
*/
class AboutusAction extends HCommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        $title = "链金所简介_网络贷款平台简介_网络理财平台简介_P2P网贷平台简介";
        $keyword = "链金所简介,关于链金所,网络理财平台介绍,P2P网贷平台介绍";
        $description = "链金所网络理财平台旨在营造一个公开透明,高效简洁的网络贷款,网络借贷,网络理财,P2P理财,P2P网贷金融投资平台.";
        $this->assign("title", $title);
        $this->assign("keyword", $keyword);
        $this->assign("description", $description);
    }

    // 关于我们
    public function index()
    {
        //网站公告
        $parm['type_id'] = 9;
        $parm['limit'] = 4;
        $this->assign("noticeList", getArticleList($parm));
        // 公告图片
        $parm['type_id'] = 9;
        $parm['limit'] = 2;
        $this->assign("noticephoto", getArticleList($parm));
        $this->display();
    }

    //渠道商页面
    public function qudao()
    {
        $info = M("huodong h")->join("lzh_members m ON m.id = h.uid")->join("lzh_members_status ms ON h.uid = ms.uid")->field("h.uid,h.money,h.add_time,m.user_phone,m.reg_time,ms.id_status,ms.company_status,m.equipment")->select();
        echo "<table style='border: 1px solid black'>";
        echo "<tr><td style='border: 1px solid'>用户ID</td><td style='border: 1px solid'>注册时间</td><td style='border: 1px solid'>是否实名</td><td style='border: 1px solid'>手机号</td><td style='border: 1px solid'>投资时间</td><td style='border: 1px solid'>投资金额</td><td style='border: 1px solid'>来源渠道</td></tr>";
        foreach ($info as $i) {
            if ($i["id_status"] == 1 || $i["company_status"] == 1) {
                $name = "实名";
            } else {
                $name = "未实名";
            }
            $phone = substr_replace($i["user_phone"], '****', 3, 4);
            echo "<tr><td style='border: 1px solid'>" . $i["uid"] . "</td><td style='border: 1px solid'>" . date("Y-m-d H:i:s", $i["reg_time"]) . "</td><td style='border: 1px solid'>" . $name . "</td><td style='border: 1px solid'>" . $phone . "</td><td style='border: 1px solid'>" . date("Y-m-d H:i:s", $i["add_time"]) . "</td><td style='border: 1px solid'>" . $i["money"] . "</td><td style='border: 1px solid'>" . $i["equipment"] . "</td></tr>";
        }
        echo "</table>";
    }

    //奖品
    public function huojiang()
    {
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $info = $Model->query("SELECT SUM(h.money) AS totalmoney,h.status,m.user_name,m.id,m.user_phone,m.reg_time,mi.real_name FROM lzh_huodong h INNER JOIN lzh_members m ON m.id = h.uid INNER JOIN lzh_member_info mi ON h.uid = mi.uid GROUP BY h.uid ORDER BY totalmoney DESC");
        $k = 0;
        foreach ($info as $i) {
            if ($i["totalmoney"] < 10000) {
                $info[$k]["gift"] = "新年红包";
            } elseif ($i["totalmoney"] >= 10000 && $i["totalmoney"] < 50000) {
                $info[$k]["gift"] = "实惠奖";
            } elseif ($i["totalmoney"] >= 50000 && $i["totalmoney"] < 100000) {
                $info[$k]["gift"] = "超值奖";
            } elseif ($i["totalmoney"] >= 100000 && $i["totalmoney"] < 500000) {
                $info[$k]["gift"] = "精品奖";
            } elseif ($i["totalmoney"] >= 500000 && $i["totalmoney"] < 1000000) {
                $info[$k]["gift"] = "豪华奖";
            } elseif ($i["totalmoney"] >= 1000000 && $k == 0) {
                $info[$k]["gift"] = "至尊奖";
            } elseif ($i["totalmoney"] >= 1000000 && $k != 0) {
                $info[$k]["gift"] = "豪华奖";
            }
            $k++;
        }
        echo " <script type='text/javascript' src='/Style/Js/jquery.js'></script>
                <script type='text/javascript'>
                var i= 0;
                function hb(uid){
                    if(i>0){
                        alert('请勿重复点击');
                    }else{
                         i++;
                         $.ajax({
                            type:'post',
                            url:'fan',
                            data:{uid:uid},
                            dataType:'json',
                            success:function(d){
                                if(d.status == 1){
                                    location.reload();
                                }else{
                                    alert(d.data);
                                }
                            }
                        });
                    }
                }
            </script>";
        echo "<table style='border: 1px solid black'>";
        echo "<tr><td style='border: 1px solid'>用户ID</td><td style='border: 1px solid'>用户名</td><td style='border: 1px solid'>姓名</td><td style='border: 1px solid'>手机号</td><td style='border: 1px solid'>注册时间</td><td style='border: 1px solid'>总投资金额</td><td style='border: 1px solid'>所获得奖品</td><td style='border: 1px solid'>红包金额</td><td style='border: 1px solid'>操作</td></tr>";
        foreach ($info as $i) {
            echo "<tr><td style='border: 1px solid'>" . $i["id"] . "</td><td style='border: 1px solid'>" . $i["user_name"] . "</td><td style='border: 1px solid'>" . $i["real_name"] . "</td><td style='border: 1px solid'>" . $i["user_phone"] . "</td><td style='border: 1px solid'>" . date("Y-m-d H:i:s", $i["reg_time"]) . "</td><td style='border: 1px solid'>" . $i["totalmoney"] . "</td><td style='border: 1px solid'>" . $i["gift"] . "</td>";
            if ($i["totalmoney"] < 10000) {
                echo "<td style='border: 1px solid'>" . ($i["totalmoney"] / 100) . "</td>";
                if ($i["status"] == 1) {
                    echo "<td style='border: 1px solid;color:red;'>已发放</td>";
                } else {
                    echo "<td style='border: 1px solid'><a href='javascript:void(0)' onclick='hb({$i['id']})'>发红包</a></td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";

    }

    //发红包
    // public function fan()
    // {
    //     if (time() < 1454342399) {
    //         $this->ajaxReturn("请在活动结束后发放", "失败", 2);
    //     } else {
    //         $uid = $_POST["uid"];
    //         $money = M("huodong")->where("uid = {$uid}")->SUM("money");
    //         $m = $money / 100;
    //         sinarewardhongdong($uid, $m);
    //         $data1['status'] = 1;
    //         M("huodong")->where("uid = {$uid}")->save($data1);
    //         $data['uid'] = $uid;
    //         $data['type'] = 76;
    //         $data['affect_money'] = $m;
    //         $data['info'] = "新年狂欢红包奖励";
    //         $data['add_time'] = time();
    //         $data['add_ip'] = get_client_ip();
    //         $data['target_uname'] = "系统管理员";
    //         M("member_moneylog")->add($data);
    //         $this->ajaxReturn(0, "成功", 1);
    //     }
    // }



}
