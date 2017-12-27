<?php

/**
 * 券管理，包含体验券和投资券
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/8/8
 * Time: 14:24
 */
class ExperienceAction extends ACommonAction
{
      public function index(){
         $model=M("coupons");
          //分页处理
          import("ORG.Util.Page");
          $map=array("type"=>2);
          if($_GET["name"]){
              $map["name"]=array('like',"%".$_GET["name"]."%");
          }
          $flag=false;
          if($_GET["start_time"]){
              $map["addtime"]=array('egt',$_GET["start_time"]);
              $flag=true;
          }
          if($_GET["end_time"]){
              if(!$flag){
                  $map["addtime"]=array('elt',$_GET["end_time"]);
              }else{
                  $map["addtime"]=array(array('egt',$_GET["start_time"]),array('elt',$_GET["end_time"])) ;
              }
          }
          if($_GET["user_phone"]){
              $map["user_phone"]=trim($_GET["user_phone"]);
          }
          $count =$model->where($map)->count('id');
          $p = new Page($count, C('ADMIN_PAGE_SIZE'));
          $page = $p->show();
          $Lsql = "{$p->firstRow},{$p->listRows}";

          $field= 'id,name,user_phone,money,endtime,addtime,isexperience,addtime';
          $list = $model->field($field)->where($map)->limit($Lsql)->order("addtime DESC")->select();
          $st=[];
          foreach ($list as $value){
              $value["endtime"]=date("Y-m-d",$value["endtime"]);
              $value["isexperience"]=  $value["isexperience"]==1?"否":"是";
              $st[]=$value;
          }
          $this->assign("list", $st);
          $this->assign("pagebar", $page);
          $this->display();
      }


    public function add(){
        if($_POST){
            $data["name"]=trim($_POST["name"]);
            $data["money"]=trim($_POST["money"]);
            $data["isexperience"]=trim($_POST["isexperience"]);
            $data["endtime"]=timr($_POST["time"]);
            $data["status"]=0;//'状态：'0:未使用，1:已使用,2：已过期',
            $data["addtime"]=date("Y-m-d H:i:s",time());
            $data["type"]=2;
            $data["serial_number"]=date("YmdHis");
            $model=M("experience");
            $flag=$model->add($data);
            if($flag){
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }

        }else{
            $this->display();
        }

    }


    public function edit(){
        if($_GET){
            $id=$_GET["id"];
            $model=M("experience");
            $list=$model->where(array("id"=>$id))->find();
            $this->assign("list",$list);
            $this->display();
        }else{
            $id=$_POST["id"];
            $data["name"]=trim($_POST["name"]);
            $data["amount"]=trim($_POST["amount"]);
            $data["limitamount"]=trim($_POST["limitamount"]);
            $data["isexperience"]=trim($_POST["isexperience"]);
            $data["time"]=timr($_POST["time"]);
            $data["status"]=1;//'状态：1未使用  2已使用   3已失效',
            $model=M("experience");
            $flag=$model->where(array("id"=>$id))->save($data);
            if($flag){
                $this->success("编辑成功");
            }else{
                $this->error("编辑失败");
            }

        }

    }
    public function del(){
        $model=M("experience");
        $flag=$model->where(array("id"=>$_GET["id"]));
        if($flag){
            $this->success("编辑成功");
        }else{
            $this->error("编辑失败");
        }

    }
}