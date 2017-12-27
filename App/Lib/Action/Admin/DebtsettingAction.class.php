<?php

/**
 * 债权参数设置
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/11/28
 * Time: 10:04
 */
class DebtsettingAction extends ACommonAction
{
    /**
     * 对应字典编号： 债转审核方式
     */
    const ZHAIQUANTYPE=1005;

    /**
     *对应字典编号：债权每日转让笔数
     */
    const ZHUANRANG_COUNT=1006;
     /**
     * 债权参数设置
     */
       public function index(){
           $system_settingModel=M("system_setting");

          if($_POST["sub"]){
              $zhuantype=$_POST["zhuantype"];//zhuantype
              $debt_count=$_POST["debt_count"];

              if($zhuantype && $debt_count){
                  $zhaiquantype=$system_settingModel->where(array("number"=>self::ZHAIQUANTYPE))->find();
                  $zhuanrang_count=$system_settingModel->where(array("number"=>self::ZHUANRANG_COUNT))->find();
                  if($zhaiquantype){
                      $flag=$system_settingModel->where(array("number"=>self::ZHAIQUANTYPE))->save(array("value"=>$zhuantype));
                  }else{
                      $flag=$system_settingModel->add(array(
                          "number"=>self::ZHAIQUANTYPE,
                          "name"=>"zhaiquantype",
                          "value"=>$zhuantype,
                          "mark"=>"债转审核方式"
                      ));

                  }
                  if($zhuanrang_count){
                      $flag1=$system_settingModel->where(array("number"=>self::ZHUANRANG_COUNT))->save(array("value"=>$debt_count));
                  }else{
                      $flag1=$system_settingModel->add(array(
                          "number"=>self::ZHUANRANG_COUNT,
                          "name"=>"debt_count",
                          "value"=>$debt_count,
                          "mark"=>"每日债转总笔数"
                      ));
                  }
                  if($flag1 || $flag){
                      $this->success("设置成功");
                  }else{
                      $this->success("设置失败");
                  }
              }else{
                  $this->error("参数不能为空");
              }
          }else{
              $zhaiquantype=$system_settingModel->where(array("number"=>self::ZHAIQUANTYPE))->find();
              $zhuanrang_count=$system_settingModel->where(array("number"=>self::ZHUANRANG_COUNT))->find();
              $this->assign("zhaiquantype",$zhaiquantype);
              $this->assign("zhuanrang_count",$zhuanrang_count);
              $this->display();
          }

       }
}