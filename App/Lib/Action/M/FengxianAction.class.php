<?php

/**
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/9/9
 * Time: 10:38
 */
class FengxianAction  extends HCommonAction
{
       public function index(){
       		$this->assign('source',$_REQUEST['source']);
       		$this->assign('bid',$_SESSION['riskbid']);
       		$this->assign("no_footer_seg","1");
       	    $simple_header_info=array("url"=>"/M/user/index.html","title"=>"投资者风险承受能力调查评估");
		    $this->assign("simple_header_info",$simple_header_info);
		    
		    $fxpg_popup_status = M("members_status")->where(array("uid" => $this->uid))->getField('fxpg_popup_status');
		    $risk = M("risk_result")->where(array("uid" => $this->uid))->limit(1)->find();
		    $fxpg_popup_status = empty($risk) ? $fxpg_popup_status : 0;
		    $this->assign("fxpg_popup_status", $fxpg_popup_status);
		    
           $this->display();
       }

       public function detail(){
	    	$list=M("risk_problem g")->field("g.*, answer_id")->join("lzh_risk_result s on g.id=s.problem_id and s.uid='{$this->uid}'")->order("g.id")->select();
	        $data=[];
	        foreach ($list as $k=>$v){
	            $data[$k]["question"]=$v["problem"];
	            $data[$k]["id"]=$v["id"];
	            $data[$k]["answer_id"]=$v["answer_id"];
	            $answerlist=M("risk_answer")->where(array("problem_id"=>$v["id"]))->order("id")->select();
	            foreach ($answerlist as $ke=>$va){
	                $data[$k]["answer"][$ke]=$va["answer"];
	                $data[$k]["score"][$ke]=$va["score"];
	                $data[$k]["answerid"][$ke]=$va["id"];
	            }
	        }
	        unset($list);
	        $this->assign('source',$_REQUEST['source']);
	        $this->assign("data",$data);
	        $this->assign("no_footer_seg","1");
	   	   $simple_header_info=array("url"=>"/M/fengxian/index/source/1.html","title"=>"投资者风险承受能力调查评估");
	       $this->assign("simple_header_info",$simple_header_info);
	   		$this->display();
       }

       public function answer(){
	            $risk_resultModel=M("risk_result");
	            $answer=$_POST["data"];
	            $risk_resultModel->where(array("uid"=>$this->uid))->delete();
	            foreach ($answer as $value){
	                $risk_resultModel->add(array(
	                    "uid"=>$this->uid,
	                    "problem_id"=>$value["problem_id"],
	                    "answer_id"=>$value["answer"],
	                    "time"=>time()
	                ));
	            }
	            echo json_encode(array("ret"=>0,"message"=>"恭喜您测评成功"));
	            exit();
	    }

      public function result(){
      	  $info = M('risk_result r')->field('sum(a.score) as score')->join('lzh_risk_answer a on a.id = r.answer_id')->where('r.uid ='.$this->uid)->select();
      	  $score=$info[0]['score'];
			if($score>=7 && $score<=12){
				$ftype = '保守型';
			}else if($score>=13 && $score<=17){
				$ftype = '谨慎型';
			}else if($score>=18 && $score<=23){
				$ftype = '稳健型';
			}else if($score>=24 && $score<=28){
				$ftype = '积极型';
			}else{
				$ftype = "无";
			}
	      $this->assign('source',$_REQUEST['source']);
	      $this->assign('bid',$_SESSION['riskbid']);
	      $this->assign('ftype',$ftype);
	      $this->assign("no_footer_seg","1");
	      $simple_header_info=array("url"=>"/M/fengxian/detail/source/1.html","title"=>"投资者风险承受能力调查评估");
		  $this->assign("simple_header_info",$simple_header_info);
          $this->display();
      }
}