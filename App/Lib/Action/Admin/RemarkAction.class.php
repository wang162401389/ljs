<?php
// 全局设置
class RemarkAction extends ACommonAction
{
    public function index(){
        if($_GET['user_name']) $search['user_name'] = text($_GET['user_name']);
        else $search=array();

        import("ORG.Util.Page");
        $count = M('member_remark')->where($search)->count();
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";

        $list = M('member_remark')->where($search)->limit($Lsql)->order('id DESC')->select();
        $this->assign("list",$list);
        $this->assign("pagebar",$page);
        $this->assign("search", $search);
        $this->display();
    }

    public function edit(){
        $data['user_name'] = text($_GET['user_name']);
        $this->assign("vo", $data);
        $this->display();
    }
	
    public function doEdit(){
        $data['user_name'] = text($_POST['user_name']);
        $data['user_id'] = M('members')->getFieldByUser_name($data['user_name'],"id");
        if(!$data['user_id']) $this->error("找不到你要备注的会员");

        $data['remark'] = text($_POST['remark']);
        if(!$data['remark']) $this->error("备注信息不可为空");

        $data['admin_id'] = $_SESSION['admin_id'];
        $data['admin_real_name'] = $_SESSION['admin_user_name'];
        $data['add_time'] = time();

        $newid = M('member_remark')->add($data);
        if($newid){
			alogs("Remark",$newid,1,'成功执行了备注信息的添加操作！');//管理员操作日志
			$this->success("添加成功");
        }else{
			alogs("Remark",$newid,0,'执行备注信息的添加操作失败！');//管理员操作日志
			$this->error("添加失败");
		}
    }

    // 跟踪记录
    public function gzindex(){
        if($_GET['user_name']){
            $search['user_name'] = text($_GET['user_name']);
            $search['borrow_id'] = text($_GET['borrow_id']);
        }else{
            $search=array();
        }

        import("ORG.Util.Page");
        $count = M('member_genzong')->where($search)->count();
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";

        $list = M('member_genzong')->where($search)->limit($Lsql)->order('id DESC')->select();
        $this->assign("borrow_id",text($_GET['borrow_id']));
        $this->assign("list",$list);
        $this->assign("pagebar",$page);
        $this->assign("search", $search);
        $this->display();
    }

    public function gzedit(){
        $id = text($_GET['id']);
        $data = M('member_genzong')->field('id,user_name,borrow_id,remark,add_time')->where("id={$id}")->find();
        if(empty($id)){
            $data['user_name'] = $_GET['user_name'];
            $data['borrow_id'] = text($_GET['borrow_id']);
        }
        $this->assign("vo", $data);
        $this->display();
    }
    
    public function gzdoedit(){
        $data['id'] = text($_POST['id']);
        $data['user_name'] = text($_POST['user_name']);
        $data['user_id'] = M('members')->getFieldByUser_name($data['user_name'],"id");
        if(!$data['user_id']) $this->error("找不到你要备注的会员");

        $data['remark'] = text($_POST['remark']);
        if(!$data['remark']) $this->error("备注信息不可为空");

        $data['admin_id'] = $_SESSION['admin_id'];
        $data['admin_real_name'] = $_SESSION['admin_user_name'];
        $data['borrow_id'] = $_POST['borrow_id'];
        $data['remark_type'] = $_POST['remark_type'];
        $data['add_time'] = time();
        // 入仓不能再添加
        $remarkadd = M('member_genzong')->field('remark,remark_type')->where("remark_type=".$data['remark_type']." and borrow_id=".$data['borrow_id'])->find();
        if ($data['remark_type'] == 0) {
            $this->error("请选正确选项！");
        }
        switch ($remarkadd['remark_type']) {
            case 1:
                if($remarkadd['remark']==text($_POST['remark'])){
                    $this->error("已提单签收，请选择到达中转港！");
                }
                break;
            case 2:
                if($remarkadd['remark']==text($_POST['remark'])){
                    $this->error("已到达中转港，请选择目的港清关！");
                }
            case 3:
                if($remarkadd['remark']==text($_POST['remark'])){
                    $this->error("已目的港清关，请选择提柜！");
                }
                break;
            case 4:
                if($remarkadd['remark']==text($_POST['remark'])){
                    $this->error("已提柜，请选择入仓！");
                }
                break;
            case 5:
                if($remarkadd['remark']==text($_POST['remark'])){
                    $this->error("已入仓，流程已走完！");
                }
                break;
        }

        if(empty($data['id'])){
            $newid = M('member_genzong')->add($data);
        }else{
            $newid = M('member_genzong')->save($data);
        }
        if($newid){
            alogs("Remark",$newid,1,'成功执行了备注信息的添加操作！');//管理员操作日志
            $this->success("操作成功");
        }else{
            alogs("Remark",$newid,0,'执行备注信息的添加操作失败！');//管理员操作日志
            $this->error("操作失败");
        }
    }
}
?>