<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/1
 * Time: 11:45
 */
class redis_task{
    var $redis;
    var $key="invest_task";
    function  __construct($config=false){
            $this->redis = new Redis();
            if($config==false)
                $redis_info=C("REDIS_INFO");
            else{
                $redis_info=$config;
            }
            $this->redis->connect($redis_info['host'], 6379);
            $this->redis->auth($redis_info['auth']);
    }
   public function release_task($url,$data){
       $info=array();
       $info['url']=$url;
       $info['data']=$data;
       $info_ser=serialize($info);
       $this->redis->lPush($this->key,$info_ser);
   }
    public function get_task(){
        $info_ser=$this->redis->lPop($this->key);
        $info=unserialize($info_ser);
        return $info;
    }
}