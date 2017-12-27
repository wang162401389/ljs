<?php
class Distribut{
	var $redis;
	var $key="distribut_task";
	function __construct(){
		$this->redis = new redis();
		$redis_info = C("REDIS_INFO");
		$this->redis->connect($redis_info["host"],6379);
		$this->redis->auth($redis_info["auth"]);
	}

	public function release_distribut($bid){
        $this->redis->lPush($this->key,$bid);
    }

    public function get_distribut(){
        $info_ser=$this->redis->lPop($this->key);
        return $info_ser;
    }
}