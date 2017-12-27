<?php
/*充值
*/
class ChargeAction extends MCommonAction {

    public function index(){
        // $balance = querybalance($this->uid);
        // if($balance > '0.00'){
        //    echo "<script>alert('您的账户将升级成存钱罐账户，您的账户余额将提现到个人银行帐号。');location.href='http://".$_SERVER['HTTP_HOST']."/Pay/shengji';</script>";
        // }
		$this->display();
    }

    public function allcharge(){
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function charge(){
        $this->del_mem_cach();
		$map['uid'] = $this->uid;
		$account_money = M('member_money')->field('(account_money+back_money) account_money')->where($map)->find();
         /**
          $utype = M("members")->where("id={$this->uid}")->field("user_regtype")->find();
         if($utype['user_regtype']==1){
		  //直接调取新浪余额
		    $account_money['account_money']=number_format( querysaving($this->uid), 2, ".", "" );//个人
        }else{
            $account_money['account_money']=number_format( querybalance($this->uid), 2, ".", "" );//企业
        }
          * **/
        $money= querysaving($this->uid)+querybalance($this->uid);
        $account_money['account_money']=number_format($money, 2, ".", "" );
		$this->assign("account_money",$account_money);
		$this->assign("payConfig",FS("Webconfig/payconfig"));
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

    public function chargeoff(){
		$this->assign("vo",M('article_category')->where("type_name='线下充值'")->find());
		
        $config = FS("Webconfig/payoff");
        $this->assign('bank', $config['BANK']);
        $this->assign('info',$config['BANK_INFO']);
        $data['html'] = $this->fetch();
		exit(json_encode($data));
    }

  //   public function chargelog(){
		// $map['uid'] = $this->uid;
		
		// if($_GET['start_time']&&$_GET['end_time']){
		// 	$_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
		// 	$_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
			
		// 	if($_GET['start_time']<$_GET['end_time']){
		// 		$map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
		// 		$search['start_time'] = $_GET['start_time'];
		// 		$search['end_time'] = $_GET['end_time'];
		// 	}
		// }
		// $list = getChargeLog($map,10);
		// $this->assign('search',$search);
		// $this->assign("list",$list['list']);
		// $this->assign("pagebar",$list['page']);
		// $this->assign("success_money",$list['success_money']);
		// $this->assign("fail_money",$list['fail_money']);
		
		// $data['html'] = $this->fetch();
		// exit(json_encode($data));
  //   }

    // 充值记录
    public function chargelog(){
        $pagesize = 20;
        $page = 1;
        if($_GET['page']>1){
            $page = $_GET['page'];
        }
        $start = ($page-1)*$pagesize;
        $where["uid"]=$this->uid;
        $where["type"]=1;
        $mywhere=array();
        if($_GET['start_time']){
            $mywhere[]=array("egt",strtotime($_GET['start_time']."000000"));
        }
        if($_GET['end_time']){
            $mywhere[]=array("elt",strtotime($_GET['end_time']."235959"));
        }
        if(count($mywhere)){
            $where['addtime']=$mywhere;
        }
        $limit=$start.",".$pagesize;
        $withdrawlist = M("sinalog")->where($where)->order("addtime desc")->limit($limit)->select();
        $count = M("sinalog")->where($where)->count();
        $totalpage = ceil($count/$pagesize);
        $i = $start;
        $list = null;
        foreach ($withdrawlist as $l) {
            $list[$i][1] = $l["money"];
            $list[$i][5] = $i+1;
            $list[$i][3] = date("Y-m-d H:i:s",$l["addtime"]);
            if($l["status"] == 2){
                $list[$i][2] = "充值成功";
            }elseif($l["status"] == 1){
                $list[$i][2] = "处理中";
            }elseif($l["status"] == 3){
                $list[$i][2] = "充值失败";
            }
            $i++;
        }
        $this->assign("list",$list);
        $this->assign("page",$page);
        $this->assign("total_item",$totalpage);
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }
    
    public function uploadImg()
    {
        $uid = $this->uid;
         
        if ( $_POST['picpath'] ){ //删除
            $imgpath = substr( $_POST['picpath'], 1 );           
            if ( in_array( $imgpath, $_SESSION['imgfiles'] ) ){                
                $res = unlink( C( "WEB_ROOT" ).$imgpath );                
                if ( $res )        $this->success( "删除成功", "", $_POST['oid'] );                
                else             $this->error( "删除失败", "", $_POST['oid'] );                
            }else{                
                $this->error( "图片不存在", "", $_POST['oid'] );            
            }        
        } else { //上传
            $this->savePathNew = C( "MEMBER_UPLOAD_DIR" )."PayImg/$uid/";            
            $this->saveRule = date( "YmdHis", time() ).mt_rand( 0, 1000 );            
            $info = $this->CUpload(); 

            if ( !isset( $_SESSION['count_file'] ) )    $_SESSION['count_file'] = 1;            
            else                 ++$_SESSION['count_file'];

            $data['img'] = $info[0]['savepath'].$info[0]['savename'];  
            
                      
            $_SESSION['imgfiles'][$_SESSION['count_file']] = $data['img'];            
            echo "{$_SESSION['count_file']}:".__ROOT__."/".$data['img'];        
        }
    }

}