<?php

/**
 * 我要借款
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/12/27
 * Time: 11:44
 */
class JiekuanAction extends  HCommonAction{

    /**
     * 借款通道每日限制笔数字典值
     */
      const JIEKUAN_BISGU = 1007;
      
     /**
     * 申请界面
     */
      public function index(){
          $jiekuanModel = M("jiekuan");
          
//           $login = 0;
//           if($this->uid){
//               $login = 1;
//           }
          
          $value = M('system_setting')->where(array("number" => self::JIEKUAN_BISGU))->getField('value');
          
          $con['addtime'] = array(array('egt', date("Y-m-d",time())), array('lt', date("Y-m-d",strtotime("+1 day"))));
          $con['status'] = array('neq', 0);
          $count = $jiekuanModel->where($con)->count();
          
          $this->assign("full", $value - $count);
          
//           $vo = 0;
//           $jiekuanlist = $jiekuanModel->where(array("uid"=>$this->uid,"status"=>array('in','1,2')))->select();
//           if($this->checkok($this->uid) == false || ($jiekuanlist && count($jiekuanlist) )){
//               $vo = 1;
//           }
//           $this->assign("vo", $vo);
//           $this->assign("login", $login);
          
          $this->display();
      }

    /**
     * 判断是否已经还清借款
     */
    public function checkok($uid){
        //：0 发标 1：初审失败 2 初审通过 4.满标 5.复审失败 6复审成功7还款完成,8表示定时发标，初审通过，但是还没发标
        $op = M("borrow_info")->where(array("borrow_status"=>array("in",array(0,2,4,6)),"borrow_uid"=>$uid))->find();
        return ($op && count($op)) ? false : true;
    }

    /**
     * 上传身份证照片
     *  Array
    (
    [storage] => jiekuan
    [id] => WU_FILE_0
    [name] => QQ图片20161227105133.jpg
    [type] => image/jpeg
    [lastModifiedDate] => Tue Dec 27 2016 10:51:26 GMT+0800 (中国标准时间)
    [size] => 52757
    )
     */
    public function uploadfile(){
        $id=$_POST["id"];
        import("ORG.Net.UploadFile");
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 20971520 ;//3145728 ;// 20M设置附件上传大小 1024*1024 =1048576
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $date="big".date("Ymd",time());
        $path="UF/Uploads/".$date;
        $new_file=$path;
        if(!file_exists($new_file)){
            mkdir($new_file,0777);
        }
        $upload->savePath =$new_file."/";// 设置附件上传目录
        $upload->saveRule="time";
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            $result="/".$info[0]['savepath'].$info[0]['savename'];
            echo json_encode(array("result"=>$result,"id"=>$id));
            exit();
        }
    }

    /**
     * 创建缩略图
     * Array
    (
    [storage] => jiekuan
    [src] =>
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH
    )
     */
    public function makeThumb(){
        if($_POST){
            $file=$_POST['src'];
            $date="small".date("Ymd",time());
            $path="UF/Uploads/".$date;
            $new_file=$path;
            if(!file_exists($new_file)){
                mkdir($new_file,0777);
            }
            $filename=date("YmdHis",time()).mt_rand(10000,99999);
            $new_filename=$new_file."/".$filename;//存储路径
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)){
                $type = $result[2];
                $new_file =$new_filename.".{$type}";
                $url = explode(',' ,$file);
                $img = base64_decode($url[1]);
                file_put_contents($new_file, $img);//返回的是字节数
                logw("/".$path."/".$filename.".{$type}");
                echo json_encode(array("result"=>"/".$path."/".$filename.".{$type}"));
                exit();
            }
        }else{
            echo json_encode(array("result"=>0));
            exit();
        }
    }

    /**
     * 针对个人用户实名认证
     */
    public function realname(){
        if(isset($_POST['usrname']) && isset($_POST['idcard'])) {
            $uinfo = M("members")->where("id={$this->uid}")->field("user_phone")->find();
            $sina['identity_id'] = $this->uid;//用户ID
            $sina['member_type'] = 1;    //用户类型
            $sina['phone'] = $uinfo['user_phone'];    //用户手机
            $sina['real_name'] = $_POST['usrname'];//用户真实姓名
            $sina['cert_no'] =  $_POST['idcard'];//用户身份证号
            $rs = sinamember($sina);
            if ($rs == 1) {
                $rs1['status'] = 1;
                M('name_apply')->where("uid ={$this->uid}")->save($rs1);
                $mydata['real_name'] = $sina['real_name'];
                $mydata['idcard'] =  $sina['cert_no'];
                $mydata['up_time'] = time();
                $c = M('member_info')->where("uid = {$this->uid}")->count('uid');
                if ($c == 1) {
                    M('member_info')->where("uid = {$this->uid}")->save($mydata);
                    ancunUser($this->uid);
                } else {
                    $mydata['uid'] = $this->uid;
                    M('member_info')->add($mydata);
                }
                $this->outmessage(1, "资料填写正确");
            } else {
                $this->outmessage(0, "资料填写错误");
            }
        }else{
            $this->outmessage(0, "参数缺失");
        }
    }
    
    public function verify(){
        import("ORG.Util.Image");
        Image::GBCal();
    }
    
    /**
     * 身份证是否绑定
     */
    public function checkIdcardBind(){
        if (empty($_POST['phone']) || empty($_POST['idcard'])) {
            $this->outmessage(0, "身份证号、手机号请填写完整");
        }
        
        $m_info = M('member_info')->where(array('idcard' => $_POST['idcard']))->field('cell_phone')->find();
        if (empty($m_info)) {
            //$this->outmessage(1, "身份证可用");
			session("idcard_".$_POST['phone'], $_POST['idcard']);
        }else{
            if ($_POST['phone'] != $m_info['cell_phone']) {
                $this->outmessage(0, "身份证已被其他用户绑定");
            }else{
				session("idcard_".$_POST['phone'], $_POST['idcard']);
                //$this->outmessage(1, "身份证正确");
            }
        }
    }

    /**
     * 个人或者企业填写借款信息
     */
    public function borrowinfo(){
        $jiekuanModel = M("jiekuan");
//         $personinfo = M("jiekuan_personinfo");
        if($_POST){
//             if(!$this->uid){
//                 $this->outmessage(0,"未登录请登录");
//             }
//             $listinfo = $jiekuanModel->where(array("status"=>0,"uid"=>$this->uid))->find();//状态0 默认 1 已处理 2 还款已完结
//             $userinfo = $personinfo->where(array("uid"=>$this->uid,"status"=>0))->order("addtime DESC")->find();
//             if($listinfo && !empty($userinfo)){
//                 $this->outmessage(0,"您有未处理的借款暂时不能借款");
//             }
//             if(!$this->checkok($this->uid)){
//                 $this->outmessage(0,"您有为归还的借款，暂时不能借款");
//             }
            $arr = array();
            $arr['purpose'] = text($_POST['purpose']);
            $arr['amount'] = text($_POST['money']);
            $arr['deadline'] = text($_POST['duration']);
            $arr['addtime'] = date("Y-m-d H:i:s",time());
            $arr['status'] = 0;
            $arr['uid'] = 0;
            $arr['amount'] = intval($arr['amount']);
            $type = text($_POST['usertype']);
            $arr['qudao'] = '链金所借款';
            $max_money = $type == 1 ? 200000 : 500000;
            if($arr['amount'] > $max_money || $arr['amount'] < 1000){
                $this->outmessage(0,"金额范围错误");
            }
            
            $arr['user_type'] = $type;
            $id = $jiekuanModel->add($arr);
            if($id){
                if($type == 1) {//个人
                    $this->outmessage(1,"保存成功","/home/jiekuan/personinfo?id=".$id);
                }else{//企业
                    $this->outmessage(1,"保存成功","/home/jiekuan/companyinfo?id=".$id);
                }
            }else{
                $this->outmessage(0,"保存失败");
            }
        }else{

//             if(!$this->uid){
//                 $this->redirect("/member/common/login");
//             }
//             if(!$this->checkok($this->uid)){//存在未还清的借款
//                 $this->redirect("/home/jiekuan/already");
//                 exit();
//             }
            
//             $jiekuanModel->where(array("uid"=>$this->uid,"status"=>0))->delete(); //状态0 默认 1资料填写完毕 2已处理 3还款已完结 4 作废
//             $jiekuanlist = $jiekuanModel->where(array("uid"=>$this->uid,"status"=>array('in','1,2')))->select();
            
//             if($jiekuanlist && count($jiekuanlist)) {//如果存在未处理的借款
//                 if($jiekuanlist[0]['user_type'] == 1){//个人
//                     $userinfo=$personinfo->where(array("uid"=>$this->uid,"status"=>array("neq",4)))->find();// status 0实名认证  1填写个人信息   2上传征信报告   3上传银行流水   4完成
//                     if($userinfo){
//                         if($jiekuanlist[0]['status'] == 1){
//                             $this->redirect("/home/jiekuan/submitapply");
//                             exit();
//                         }
//                     }else{
//                         if($jiekuanlist[0]['status'] == 1){
//                             $this->redirect("/home/jiekuan/submitapply");
//                             exit();
//                         }
//                     }
//                 }else{//企业
//                     $this->redirect("/home/jiekuan/wait");
//                     exit();
//                 }
//             }

            $startdate = date("Y-m-d",time());
            $enddate = date("Y-m-d",strtotime("+1 day")); 
            $value = M('system_setting')->where(array("number" => self::JIEKUAN_BISGU))->getField('value');
            
            $con['addtime'] = array(array('egt', $startdate), array('lt', $enddate));
            $con['status'] = array('neq', 0);
            $count = $jiekuanModel->where($con)->count();
            
            if($value <= $count){
                $this->error("今日借款申请已满额，请明日再试！","/home/jiekuan/index");
            }
            
            $this->display();
        }
    }
    
    /**
     * 验证图形验证码
     */
    public function ckcode(){
        if($_SESSION['verify'] != md5($_POST['sVerCode'])) {
            echo (0);
        }else{
            echo (1);
        }
    }
    
    /**
     * 发送手机验证码
     */
    public function sendphone(){
        $phone = htmlspecialchars($_POST['phone']);
        $txcode = htmlspecialchars($_POST['txcode']);
        
        if ($_SESSION['verify'] != md5($txcode)) {
            echo 2;
        }else {
            $code = rand_string_reg();
            $res = sendsms($phone, "你好，手机号为：".$phone."的验证码是：".$code);
            if($res){
                session("temp_phone", $phone);
                session("code_temp_".$phone, $code);
                echo 1;
            }else{
                echo 2;
            }
        }
    }
    
    /**
     * 验证手机验证码
     */
    public function validatephonecode(){
        if (session('code_temp_'.$_POST['phone']) == text($_POST['code'])){
            if (!session("temp_phone")) {
                echo (0);
            }else{
                echo (1);  
            }
        }else {
            echo (0);
        }
    }
    
    /**
     * 个人详细信息
     *  Array
    (
    [usrname] => sammao
    [idcard] => 360728199411133922
    [mobile_phone] => 13760489226
    [xueli] => 博士
    [marital] => 未婚
    [addr_province] => 湖北省
    [addr_city] => 武汉市
    [addr_county] => 新洲区
    [profession] => 信息传输、软件和信息技术服务业
    [position] =>  待业人员、学生和职位不确定的其他人员
    [year] => 5年以上
    [income] => 12～19万元
    [zhufang] => 按揭
    [asset] => 家庭自有固定资产价值在50万元以下（不含）
    [bankcard] => 6225882765730623
    [id_card_front_pic] => {"s_pic":"/UF/Uploads/small20170207/2017020710060936424.jpeg","m_pic":"/UF/Uploads/big20170207/1486433169.png"}
    [id_card_reverse_pic] => {"s_pic":"/UF/Uploads/small20170207/2017020710062125935.jpeg","m_pic":"/UF/Uploads/big20170207/1486433181.jpg"}
    [handCard_pic] => {"s_pic":"/UF/Uploads/small20170207/2017020710055755911.jpeg","m_pic":"/UF/Uploads/big20170207/1486433157.jpg"}
    [realflag] => 1
    [info] => Array
    (
    [0] => Array
    (
    [rel_usrname] => 喻
    [relation] => 配偶
    [relation_id] => 0
    [rel_mobile_phone] => 13760489225
    )

    [1] => Array
    (
    [rel_usrname] => 刘
    [relation] => 父母
    [relation_id] => 1
    [rel_mobile_phone] => 13760489227
    )

    [2] => Array
    (
    [rel_usrname] => 李
    [relation] => 父母
    [relation_id] => 1
    [rel_mobile_phone] => 13760489221
    )
    )
    )
     */
    public function personinfo(){
        if($_POST){
            if (session('code_temp_'.$_POST['mobile_phone']) != text($_POST['smscode'])){
                $this->outmessage(0, "手机验证码不对");
            }
//             if(!$this->uid){
//                 $this->outmessage(0,"请登录");
//             }
//             $ids = M('members_status')->where("uid={$this->uid}")->field("id_status,company_status")->find();
//             if($ids && $ids['id_status']==1){
//                 ;
//             }else{
//                 $this->outmessage(0,"请填写真实的用户名和身份证号");
//             }

            $jiekuan_id = text($_POST['key']);
            $jiekuan_personinfo_model = M("jiekuan_personinfo");
            $info = $jiekuan_personinfo_model->where(array('jiekuan_id' => $jiekuan_id))->find();
            if (!empty($info)) {
                $this->outmessage(0, "该借款不能重复提交");
            }
            
            $arr = array();
            $arr['phone'] = text($_POST['mobile_phone']);
            $arr['marray'] = text($_POST['marital']);//marital
            $arr['province'] = text($_POST['addr_province']);
            $arr['city'] = text($_POST['addr_city']);
            $arr['area'] = text($_POST['addr_county']);
            $arr['work'] = text($_POST['profession']);
            $arr['income'] = text($_POST['income']);
            $arr['position'] = text($_POST['position']);
            $arr['work_time'] = text($_POST['year']);
            $arr['bankcard'] = text($_POST['bankcard']);
            $arr['zhufang'] = text($_POST['zhufang']);
            $arr['xueli'] = text($_POST['xueli']);
            $arr['asset'] = text($_POST['asset']);
            $arr['realname'] = text($_POST['usrname']);
            $arr['idcard'] = text($_POST['idcard']);
            $arr["id_card_front_pic"] = $_POST['id_card_front_pic'];
            $arr["id_card_reverse_pic"] = $_POST['id_card_reverse_pic'];
            $arr["handcard_pic"] = $_POST['handCard_pic'];
            $arr['now_province'] = text($_POST['now_province']);
            $arr['now_city'] = text($_POST['now_city']);
            $arr['now_county'] = text($_POST['now_county']);
            $arr['now_area'] = text($_POST['now_area']);
            $arr['jiekuan_id'] = $jiekuan_id;
            $arr['status'] = 1;
            $arr['addtime'] = $_SERVER['REQUEST_TIME'];
            $flag = 1;
            foreach ($arr as $value){
                if($value == "" || $value == null){
                    $flag = 0;
                    break;
                }
            }
            
            $relation=array();
            $relation[0]["name"] = text($_POST['info'][0]['rel_usrname']);
            $relation[0]["relation"]=text($_POST['info'][0]['relation']);
            $relation[0]["phone"]=text($_POST['info'][0]['rel_mobile_phone']);
            $relation[1]["name"]=text($_POST['info'][1]['rel_usrname']);
            $relation[1]["relation"]=text($_POST['info'][1]['relation']);
            $relation[1]["phone"]=text($_POST['info'][1]['rel_mobile_phone']);
            $relation[2]["name"]=text($_POST['info'][2]['rel_usrname']);
            $relation[2]["relation"]=text($_POST['info'][2]['relation']);
            $relation[2]["phone"]=text($_POST['info'][2]['rel_mobile_phone']);
            foreach ($relation as $value){
                foreach ($value as $v){
                    if($v == "" || $v == null){
                        $flag = 0;
                        break;
                    }
                }
            }
             
            if(!$flag) {
                $this->outmessage(0,"参数为空");
            }else{
                
                $m_info = M('member_info')->where(array('idcard' => $arr['idcard']))->field('cell_phone')->find();
                if (!empty($m_info)) {
                    if ($arr['phone'] != $m_info['cell_phone']) {
                        $this->outmessage(0, "身份证已被其他用户绑定");
                    }
                }
                
                $jiekuan = M("jiekuan");
                $info = $jiekuan->find($jiekuan_id);
                if($info){
                    $arr['uid'] = $this->checkPhone($arr['phone']);
                    if ($arr['uid'] == 0) {
                        $this->outmessage(0, "注册失败");
                    }

                    $id = $jiekuan_personinfo_model->add($arr);
                    
                    if($id){
                        
                        $myarr["uname"] = $arr['realname'];
                        $myarr['idcard'] = $arr['idcard'];
                        $myarr['phone'] = $arr['phone'];
                        $myarr['status'] = 1;
                        $myarr['uid'] = $arr['uid'];
                        $jiekuan->where(array("id" => $jiekuan_id))->save($myarr);
                        
                        $jiekuan_contact_model = M('jiekuan_contact');
                        
                        $relation[0]['pid'] = $id;
                        $relation[0]['sort'] = 0;
                        $jiekuan_contact_model->add($relation[0]);
                        $relation[1]['pid'] = $id;
                        $relation[1]['sort'] = 1;
                        $jiekuan_contact_model->add($relation[1]);
                        $relation[2]['pid'] = $id;
                        $relation[2]['sort'] = 2;
                        $jiekuan_contact_model->add($relation[2]);
                        $this->outmessage(1,"填写成功","/home/jiekuan/submitapply");
                    }else{
                        $this->outmessage(0,"填写失败");
                    }
                }else{
                    $this->outmessage(0,"填写失败");
                }
            }
        }else{
//             $ids = M('members_status')->where("uid={$this->uid}")->field("id_status,company_status")->find();
//             if($ids['id_status'] == 1){//个人已实名
//                 $vo = M("member_info")->field('idcard,real_name')->where(array("uid"=>$this->uid))->find();
//                 if($vo){
//                     $info['real_name']=$vo['real_name'];
//                     $info['idcard']=$vo['idcard'];
//                 }else{
//                     $lzh_jiekuan_infoModel=M("jiekuan_personinfo");
//                     $vo=$lzh_jiekuan_infoModel->where(array("status"=>0,"uid"=>$this->uid))->find();
//                     $info['real_name']=$vo['real_name'];
//                     $info['idcard']=$vo['idcard'];
//                 }
//                $this->assign("vo",$info);
//                $realflag=1;
//             }else{
//                $realflag=0;
//             }
//             $this->assign("realflag", $realflag);

            $id = intval($_GET['id']);
            if ($id <= 0) {
                $this->error("参数错误","/home/jiekuan/index");
            }
            $info = M("jiekuan")->find($id);
            if (empty($info)) {
                $this->error("没有借款记录","/home/jiekuan/index");
            }
            $this->assign('key', $id);
            $this->display();
        }
    }
    
    /**
     * 取注册id，否则去注册
     */
    private function checkPhone($phone){
        //统一接口
        $interface = C('UNIFY_INTERFACE.enable');
        $uid = 0;
        if($interface == 1) {
            import("@.Phpconectjava.usersapi");
            $users = new usersapi();
            
            $userinfo['user_name'] = $phone;
            $is_register = json_decode($users->isRegister($userinfo), true);
            
            if($is_register['code'] > -1) {
                //已注册
                $member_model = M('members');
                $mid = $is_register['code'];
                $has_reg = $member_model->where(array('id' => $mid))->field('id')->find();
                //members表 id 字段 既是 java 返回usr_id
                if (!empty($has_reg)) {
                    //members有记录
                    $uid = $has_reg['id'];
                    session('jk_flag_'.$phone, 0);
                }else{
                    //members没有记录
                    
                    $usrid['usr_id'] = $mid;
                    $result = json_decode($users->getUsrinf($usrid), true);
                    
                    if($result['code'] == -1){
                        $uid = 0;
                    }else{
                        $result1 = json_decode($result['resultText'], true);
                        $recomuid = json_decode($users->getRecommend($usrid), true);
                        
                        $memberinfo['id'] = $result1['usr_id'];
                        $memberinfo['user_name'] = $result1['user_name'];
                        $memberinfo['user_pass'] = $result1['user_pass'];
                        $memberinfo['user_regtype'] = null;
                        $memberinfo['user_email'] = $result1['user_email'] ? $result1['user_email'] : '';
                        $memberinfo['user_phone'] = $result1['user_phone'];
                        $memberinfo['reg_ip'] = $result1['reg_ip'] ? $result1['reg_ip'] : '';
                        $memberinfo['equipment'] = '';
                        if(!empty($result1['equipment'])){
                            $memberinfo['equipment'] = $result1['equipment'];
                        }
                        $memberinfo['reg_time'] = $_SERVER['REQUEST_TIME'];
                        $memberinfo['last_log_ip'] = get_client_ip();
                        $memberinfo['last_log_time'] = $_SERVER['REQUEST_TIME'];
                        $memberinfo['recommend_id'] = 0;
                        if(!empty($recomuid['recommend_id'])){
                            $memberinfo['recommend_id'] = $recomuid['recommend_id'];
                        }
                        $uid = $member_model->add($memberinfo);
                        
                        if ($uid) {
                            $updata['cell_phone'] = $result1['user_phone'];
                            $member_info_model = M('member_info');
                            $b = $member_info_model->where("uid = {$result1['usr_id']}")->count('uid');
                            if ($b == 1){
                                $member_info_model->where("uid={$result1['usr_id']}")->save($updata);
                            }else{
                                $updata['uid'] = $result1['usr_id'];
                                $updata['cell_phone'] = $result1['user_phone'];
                                $member_info_model->add($updata);
                            }
                            $map['uid'] = $result1['usr_id'];
                            $map['phone_status'] = 1;
                            M("members_status")->add($map);
                        }
                    }
                }
            } else {
                //未注册
                $code = mt_rand(100000, 999999);
                session('clear_pwd_temp', $code);
                session('pwd_temp', md5($code));
                session('temp_phone', $phone);
                session('jk_flag_'.$phone, 1);
                session('name_temp', $phone);
                //已登录时，R方法不能跳转，所以删除以下session
                session('u_id', null);
                $uid = R('Member/Common/regaction');
            }
        }
        
        return $uid > 0 ? $uid : 0;
    }

    /**
     * 提交征信报告或者银行流水
     *  Array
    (
    [zhengxin_pic] => Array
    (
    [s_pic] => /UF/Uploads/small20170106/2017010616375021889.jpeg
    [m_pic] => /UF/Uploads/big20170106/1483691870.png
    )
    )
     */
    public function submitapply(){
        if($_POST){
            $personinfo=M("jiekuan_personinfo g");

            $info=$personinfo->join("lzh_jiekuan t on g.jiekuan_id=t.id")->where(array("g.status"=>4,"g.uid"=>$this->uid,"t.status"=>array("in",'0,1')))->find();
            if($info){
                $this->outmessage(0,"您已经上传无需再次上传");
            }
            $userinfo=$personinfo->field("g.id")->join("lzh_jiekuan t on g.jiekuan_id=t.id")->where(array("t.status"=>array("neq",4),"g.uid"=>$this->uid,"g.status"=>array("in",array(1,2,3))))->find();
            if(!$userinfo){
                $this->outmessage(0,"系统忙，请稍后再试");
            }
          if(isset($_POST['zhengxin_pic'])){//征信报告
              $b=$_POST['zhengxin_pic'];
              $b=json_encode($b);
              $co= cookie("co");
              if(empty($co)){
                  $co=1;
                  $personinfo->where(array("id"=>$userinfo["id"]))->save(array("status"=>3,"zhengxin_pic"=>$b));
                  logw($personinfo->getLastSql());
                  logw(1);
              }else if($co==1){
                  $co=2;
                  $personinfo->where(array("id"=>$userinfo["id"]))->save(array("status"=>4,"zhengxin_pic"=>$b));
              }
              cookie("co",$co);
              if($co==2){
                  cookie("co",0);
                  $this->outmessage(1,"保存成功","/home/index/index");
              }else{
                  $this->outmessage(1,"保存成功");
              }

          }else if(isset($_POST['bank_state'])){//银行流水
              $b=$_POST['bank_state'];
              $b=json_encode($b);
              $co= cookie("co");
              if(empty($co)){
                  $co=1;
                  $personinfo->where(array("id"=>$userinfo["id"]))->save(array("status"=>3,"bank_state"=>$b));
              }else if($co==1){
                  $co=2;
                  $personinfo->where(array("id"=>$userinfo["id"]))->save(array("status"=>4,"bank_state"=>$b));
              }
              cookie("co",$co);
              if($co==2){
                  cookie("co",0);
                  $this->outmessage(1,"保存成功","/home/index/index");
              }else{
                  $this->outmessage(1,"保存成功");
              }
          }else{
              $this->outmessage(0,"请上传文件");
          }
        }else{
            $this->display();
        }
    }

    /**
     * ajax 输出json格式
     * @param $status
     * @param $message
     * @param null $data
     */
    private function outmessage($status,$message,$url='',$data=null){
        $outdata = array();
        $outdata["code"] = $status;
        $outdata["resultText"] = array("message" => $message);
        if($url){
            $outdata["resultText"]["url"] = $url;
            if($data) {
              $outdata["resultText"]["data"] = $data;
            }
        }

        echo json_encode($outdata);
        die();
    }

    /**
     * 您的借款结清后方可再次申请借款！
     */
    public function already(){
       $this->display();
    }

    /**
     * 企业提交申请
     */
    public function wait(){
        $this->display();
    }

    /**
     * err: Array
    (
    [company_name] => 啊按时打发第三方
    [address] => 阿萨德法师打发
    [license_no] => 123456789012345
    [license_expire_date] => 2017-02-28
    [license_address] => 阿萨德法师打发但是
    [business_scope] => 阿萨德法师打发
    [summary] => 阿萨德法师打发水电费
    [organization_no] => 1234567890
    [duration] => 中国银行_BOC
    [bank_num] => 6225882765730623
    [addr_province] => 湖北省
    [addr_city] => 武汉市
    [txt_bankName] => 武汉支行
    [legal_person] => 刘星
    [telephone] => 13760589226
    [legal_person_phone] => 13760489226
    [email] => 101000@qq.com
    [cert_no] => 420117198606280078
    [agent_name] => 刘星
    [agent_mobile] => 13760489226
    [alicense_no] => 420117198606280078
    [license_pic] => Array
    (
    [0] => Array
    (
    [s_pic] => /UF/Uploads/small20170207/2017020715401781919.jpeg
    [m_pic] => /UF/Uploads/big20170207/1486453217.jpg
    )

    [1] => Array
    (
    [s_pic] => /UF/Uploads/small20170207/2017020715405226226.jpeg
    [m_pic] => /UF/Uploads/big20170207/1486453252.png
    )

    [2] => Array
    (
    [s_pic] => /UF/Uploads/small20170207/2017020715410529503.jpeg
    [m_pic] => /UF/Uploads/big20170207/1486453265.jpg
    )

    [3] => Array
    (
    [s_pic] => /UF/Uploads/small20170207/2017020715412196698.jpeg
    [m_pic] => /UF/Uploads/big20170207/1486453281.jpg
    )

    )
    )
     */
    public function companyinfo(){
        if($_POST){
//             if(!$this->uid){
//                 $this->outmessage(0,"用户未登录请先登录");
//             }
            if (session('code_temp_'.$_POST['agent_mobile']) != text($_POST['managersmscode'])){
                $this->outmessage(0, "手机验证码不对");
            }
            
            $jiekuan_id = text($_POST['key']);
            $jiekuan_companyinfo_model = M("jiekuan_companyinfo");
            $info = $jiekuan_companyinfo_model->where(array('jiekuan_id' => $jiekuan_id))->find();
            if (!empty($info)) {
                $this->outmessage(0, "该借款不能重复提交");
            }
            
            $arr['jiekuan_id'] = $jiekuan_id;
            $arr['company_name'] = text($_POST['company_name']);
            $arr['address'] = text($_POST['address']);
            $arr['license_no'] = text($_POST['license_no']);
            $arr['license_expire_date'] = text($_POST['license_expire_date']);
            $arr['license_address'] = text($_POST['license_address']);
            $arr['business_scope'] = text($_POST['business_scope']);
            $arr['summary'] = text($_POST['summary']);
            $arr['organization_no'] = text($_POST['organization_no']);
            $arr['duration'] = text($_POST['duration']);
            $arr['bank_num'] = text($_POST['bank_num']);
            $arr['duration'] = text($_POST['duration']);
            $arr['addr_province'] = text($_POST['addr_province']);
            $arr['addr_city'] = text($_POST['addr_city']);
            $arr['txt_bankName'] = text($_POST['txt_bankName']);
            $arr['legal_person'] = text($_POST['legal_person']);
            $arr['telephone'] = text($_POST['telephone']);
            $arr['legal_person_phone'] = text($_POST['legal_person_phone']);
            $arr['email'] = text($_POST['email']);
            $arr['cert_no'] = text($_POST['cert_no']);
            $arr['agent_name'] = text($_POST['agent_name']);
            $arr['agent_mobile'] = text($_POST['agent_mobile']);
            $arr['alicense_no'] = text($_POST['alicense_no']);
            $arr['license_pic'] = $_POST['license_pic'];

            $flag = true;
            foreach ($arr as $v){
                if(empty($v)){
                    $flag = false;
                    break;
                }
            }
            if($flag == false){
                $this->outmessage(0, "必填项不能为空");
            }
            if(count($arr['license_pic']) == 0){
                $this->outmessage(0, "企业资质不能为空");
            }
            $arr['license_pic'] = json_encode($arr['license_pic']);
            $arr['addtime'] = $_SERVER['REQUEST_TIME'];
            $jiekuan_model = M("jiekuan");
            $info = $jiekuan_model->find($arr['jiekuan_id']);
            if($info){
                $listinfo = $jiekuan_companyinfo_model->where(array("jiekuan_id" => $jiekuan_id))->find();
                if(!$listinfo){
                    
                    $arr['uid'] = $this->checkPhone($arr['agent_mobile']);
                    if ($arr['uid'] == 0) {
                        $this->outmessage(0, "注册失败");
                    }
                    
                    $myflag = $jiekuan_companyinfo_model->add($arr);
                    if($myflag){
                        $data["status"] = 1;
                        $data['uname'] = $arr['agent_name'];
                        $data['idcard'] = $arr['alicense_no'];
                        $data['phone'] = $arr['agent_mobile'];
                        $data['uid'] = $arr['uid'];
                        $jiekuan_model->where(array("id" => $jiekuan_id))->save($data);
                        
                        $this->outmessage(1, "保存成功", "/home/jiekuan/wait");
                    }else{
                        $this->outmessage(0, "保存失败");
                    }
                }else{
                    $this->outmessage(1,"您已经填写", "/home/jiekuan/wait");
                }

            }else{
                $this->outmessage(0, "参数错误");
            }

        }else{
            $id = intval($_GET['id']);
            if ($id <= 0) {
                $this->error("参数错误","/home/jiekuan/index");
            }
            $info = M("jiekuan")->find($id);
            if (empty($info)) {
                $this->error("没有借款记录","/home/jiekuan/index");
            }
            $this->assign('key', $id);
            $this->display();
        }
    }
}