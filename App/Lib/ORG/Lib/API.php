<?php

/**
 * Created by PhpStorm.
 * User: Tesu
 * Date: 2016/12/29
 * Time: 16:45
 */
class API
{

    //调试模式
    public static $debug = false;
    public $config = array();


    public function set($name, $value = '') {
        $this->config[$name] = $value;
    }

    /**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @return string
     */
    public  function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array())
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ci, CURLOPT_TIMEOUT, 60);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }

    /**
     * 发起一个API请求
     * @param $command 接口名称 如：t/add
     * @param $params 接口参数  array('content'=>'test');
     * @param $method 请求方式 POST|GET
     * @param $multi 图片信息
     * @return string
     */
    public  function api($command, $params = array(), $method = 'GET', $multi = false, $url2=null)
    {
        $url = $this->config['url'].trim($command, '/');
        if(!is_null($url2)){
            $url = $url2.trim($command, '/');
        }
        logw('url = '.json_encode($url).' params ='.json_encode($params).' method='.$method, 'api');
        $r = self::request($url, $params, $method, $multi);
        $r = preg_replace('/[^\x20-\xff]*/', "", $r); //清除不可见字符
        $r = iconv("utf-8", "utf-8//ignore", $r); //UTF-8转码
        //调试信息
        if (self::$debug) {
            logw("接口URL:".$url);
            logw("接口参数:".print_r($params));
            logw("返回结果：".print_r($r,true));
        }
        return $r;
    }
}