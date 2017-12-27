<?php

/* 
 *PHP 连接 JAVA 接口类
 *  
 */

class Php_java{
    
    //调试模式
    public static $debug = true;

    public $config = array();

    public function __construct() {
        //
    }
    
    public function set($name,$value=''){
        $this->config[$name] = $value;
    }


    /*
 * @param $url  接口的URL
 * @param $method  请求的类型
 * @params  接口参数
 * @return string
 */    
    public function curl_request($url,$params=array(),$method = 'get',$timeout=5){
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//不直接输出结果
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);//设置连接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);//设置超时时间
        if ($method = 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if(!empty($params)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    
    /*
     * @param $interface //接口名称
     * @param $params  //接口参数
     * @param $method //请求方式
     */
    
    public function curl_api($interface,$params=array(),$method='get'){
        $url= $this->config['url'].trim($interface,'/');
        $res = $this->curl_request($url, $params, $method);
        $res = iconv("utf-8", "utf-8//ignore", $res);//UTF-8转码
        
        if(self::$debug){
            Log::write('<pre>');
            Log::write('接口URL:'.$url.'<br>');
            Log::write('请求参数:');
            Log::write(var_export($params,true));
            Log::write('返回结果：'.$res);
            Log::write('</pre>');
        }
        return $res;
    }
    
}