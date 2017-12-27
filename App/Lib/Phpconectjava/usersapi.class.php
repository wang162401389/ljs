<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
import("@.Phpconectjava.Php_java");
class usersapi extends Php_java{
    var $url;
    public function __construct() {
         $this->url=C('UNIFY_INTERFACE.url');
    }
    //登录接口
    public function logindo($option){
        return $this->curl_api($this->url."/unify_interface/user/login.do",$option);
    }
    //注册接口
    public function regdo($option){
        return $this->curl_api($this->url."/unify_interface/user/register.do",$option);
    }
    //登录获取用户信息
    public function getUsrinf($uid){
        return $this->curl_api($this->url."/unify_interface/user/getUsrinf.do",$uid);
    }
    //获取推荐人
    public function getRecommend($uid){
        return $this->curl_api($this->url."/unify_interface/user/getRecommend.do",$uid);
    }
    //修改用户名接口
    public function setUsrname($option){
        return $this->curl_api($this->url."/unify_interface/user/setUsrname.do",$option);
    }
    //修改用户密码接口
    public function setUsrpwd($option){
        return $this->curl_api($this->url."/unify_interface/user/setUsrpwd.do",$option);
    }
    //绑定用户手机接口
    public function setUsrphone($option){
        return $this->curl_api($this->url."/unify_interface/user/setUsrphone.do",$option);
    }
    //绑定用户邮箱接口
    public function setUsremail($option){
        return $this->curl_api($this->url."/unify_interface/user/setUsremail.do",$option);
    }
    //根据用户名查找usrid接口
    public function getUsrid($option){
        return $this->curl_api($this->url."/unify_interface/user/setUsrid.do",$option);
    }
    //查询用户是否已经注册接口
    public function isRegister($option){
        return $this->curl_api($this->url."/unify_interface/user/isRegister.do",$option);
    }
    //修改推荐人
    public function setrecommend($option){
        return $this->curl_api($this->url."/unify_interface/user/setRecommend.do",$option);
    }
    //额度审核获取未审核用户
    public function getnocheck(){
        return $this->curl_api($this->url."/allwood_finance/installment/getnocheck.do");
    }
    //审核获取用户信息
    public function getcheckusrinf($uid){
        return $this->curl_api($this->url."/allwood_finance/installment/getusrinf.do",$uid);
    }
    //审核结果写入
    public function setlimit($option){
        return $this->curl_api($this->url."/allwood_finance/installment/setlimit.do",$option);
    }
    //额度查询
    public function getlimit($uid){
        return $this->curl_api($this->url."/allwood_finance/installment/getlimit.do",$uid);
    }
    //查询转账账号
    public function gettransferccount($uid){
        return $this->curl_api($this->url."/allwood_finance/installment/gettransferccount.do",$uid);
    }
    //确认转账成功
    public function settransfer($option){
        return $this->curl_api($this->url."/allwood_finance/installment/settransfer.do",$option);
    }
    //确认用户还款成功
    public function setrepaymenta($option){
        return $this->curl_api($this->url."/allwood_finance/installment/setrepaymenta.do",$option);
    }
    //查询用户注册数量
    public function getmembercount($option){
        return $this->curl_api($this->url."/unify_interface/user/getLogincntList.do",$option);
    }
}