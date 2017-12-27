<?php
// 本类由系统自动生成，仅供测试用途
class MsgAction extends MCommonAction {

    public function index(){
		$this->display();
    }

    public function sysmsg(){
		$map['uid'] = $this->uid;
		//分页处理
		import("ORG.Util.Page");
		$count = M('inner_msg')->where($map)->count('id');
		$p = new Page($count, 15);
		$page = $p->ajax_show();
		$this->assign("total_page", $p->get_total_page());
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$list = M('inner_msg')->where($map)->order('id DESC')->limit($Lsql)->select();
		$read=M("inner_msg")->where("uid={$this->uid} AND status=1")->count('id');

		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$this->assign("read",$read);
		$this->assign("unread",$count-$read);
		$this->assign("count",$count);
		$data['html'] = $this->fetch();
		exit(json_encode($data));
    }

	public  function pageinfo(){
		$map['uid'] = $this->uid;
		//分页处理
		import("ORG.Util.Page");
		$count = M('inner_msg')->where($map)->count('id');
		$p = new Page($count, 15);
		$page = $p->ajax_show();
		$this->assign("total_page", $p->get_total_page());
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
		$list = M('inner_msg')->where($map)->order('id DESC')->limit($Lsql)->select();
		$this->assign("list",$list);
		$this->assign("pagebar",$page);
		$html=$this->fetch();
		echo $html;
	}

	
	public function viewmsg(){
		$id = intval($_GET['id']);
		$msgModel=M("inner_msg");
		$vo = $msgModel->field('msg')->where("id={$id} AND uid={$this->uid}")->find();
		if(!is_array($vo)){
			$this->assign("msg","数据有误");
			$data['content'] = $this->fetch();
			exit(json_encode($data));
		}
		$msgModel->where("id={$id} AND uid={$this->uid}")->save(array("status"=>1));
		$totalcount=$msgModel->where(array("uid"=>$this->uid))->count("id");
		$readcount=$msgModel->where(array("uid"=>$this->uid,"status"=>1))->count("id");
		$this->assign("mid",$id);
		$this->assign("msg",$vo['msg']);
		$this->assign("read",$readcount);
		$this->assign("unread",$totalcount-$readcount);
		$this->assign("count",$totalcount);
		$data['content'] = $this->fetch();
		exit(json_encode($data));
	}
	
	public function delmsg(){
		$id =$_POST['idarr'];
		$msgModel=M("inner_msg");
		$up = $msgModel->where(array("uid"=>$this->uid,"id"=>array("in",$id)))->delete();
		if($up){
			$data['status'] = 1;
			$totalcount=$msgModel->where(array("uid"=>$this->uid))->count("id");
			$readcount=$msgModel->where(array("uid"=>$this->uid,"status"=>1))->count("id");
			$data['data'] =array("total"=>$totalcount,"read"=>$readcount);
			echo json_encode($data);
		}else{
			$data['status'] = 0;
			echo json_encode($data);
		}
	}

}