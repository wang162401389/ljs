<?php
//对提交的参数进行过滤
function EnHtml($v)
{
    return $v;
}

function mydate($format, $time, $default='')
{
    if (intval($time)>10000) {
        return date($format, $time);
    } else {
        return $default;
    }
}
function textPost($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $v) {
            $x[$key]=text($v);
        }
    }
    return $x;
}


/*$url：要生成的地址,$vars:参数数组,$domain：是否带域名*/
function MU($url, $type, $vars=array(), $domain=false)
{
    //获得基础地址START
    $path = explode("/", trim($url, "/"));
    $model = strtolower($path[1]);
    $action = isset($path[2])?strtolower($path[2]):"";
    //获得基础地址START
    //获取前缀根目录及分组
    $http = UD($path, $domain);
    //获取前缀根目录及分组
    switch ($type) {
        case "article":
        default:
            if (!isset($vars['id'])) {//特殊栏目,用nid来区分,不用ID
                unset($path[0]);//去掉分组名
                $url = implode("/", $path)."/";
                $newurl=$url;
            } else {//普通栏目,带ID
                if (1==1||strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) {//如果是默认分组则去掉分组名
                    unset($path[0]);//去掉分组名
                    $url = implode("/", $path)."/";
                }
                $newurl=$url.$vars['id'].$vars['suffix'];
            }
        break;
        case "typelist":
                if (1==1||strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) {//如果是默认分组则去掉分组名
                    unset($path[0]);//去掉分组名
                    $url = implode("/", $path);
                }
                $newurl=$url.$vars['suffix'];
        break;
    }

    return $http.$newurl;
}
// URL组装 支持不同模式
// 格式：UD('url参数array('分组','model','action')','显示域名')在传入的url数组中，只用到分组
function UD($url=array(), $domain = false)
{
    // 解析URL
    $isDomainGroup = true;//当值为true时,不对任何链接加分组前缀,当为false时,自动判断分组及域名等,加前缀
    $isDomainD = false;
    $asdd = C('APP_SUB_DOMAIN_DEPLOY');
    //###########修复START#############，增加对当前分组分配了二级域名的判断,变量给下面用
    if ($asdd) {
        foreach (C('APP_SUB_DOMAIN_RULES') as $keyr => $ruler) {
            if (strtolower($url[0]."/") == strtolower($ruler[0])) {
                $isDomainGroup = true;//分组分配了二级域名
                $isDomainD = true;
                break;
            }
        }
    }

    //#########及默认分组不需要加分组名 都转换成小写来比较，避免在linux上出问题
    if (strtolower(GROUP_NAME) == strtolower(C('DEFAULT_GROUP'))) {
        $isDomainGroup = true;
    }
    //###########修复END#############，增加对当前分组分配了二级域名的判断
    // 解析子域名
    if ($domain===true) {
        $domain = $_SERVER['HTTP_HOST'];
        if ($asdd) { // 开启子域名部署
            //###########修复START#############，增加对没带前缀域名的判断
            $xdomain = explode(".", $_SERVER['HTTP_HOST']);
            if (!isset($xdomain[2])) {
                $ydomain="www.".$_SERVER['HTTP_HOST'];
            } else {
                $ydomain=$_SERVER['HTTP_HOST'];
            }
            //###########修复END#############，增加对没带前缀域名的判断
            $domain = $domain=='localhost'?'localhost':'www'.strstr($ydomain, '.');
            // '子域名'=>array('项目[/分组]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                if (false === strpos($key, '*') && $isDomainD) {
                    $domain = $key.strstr($domain, '.'); // 生成对应子域名
                    $url   =  substr_replace($url, '', 0, strlen($rule[0]));
                    break;
                }
            }
        }
    }

    if (!$isDomainGroup) {
        $gpurl = __APP__."/".$url[0]."/";
    } else {
        $gpurl = __APP__."/";
    }

    if ($domain) {
        $url   =  'http://'.$domain.$gpurl;
    } else {
        $url   =  $gpurl;
    }

    return $url;
}

function Mheader($type)
{
    header("Content-Type:text/html;charset={$type}");
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from='gbk', $to='utf-8')
{
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (($to=='utf-8'&&is_utf8($fContents)) || strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key) {
                unset($fContents[$key]);
            }
        }
        return $fContents;
    } else {
        return $fContents;
    }
}
//判断是否utf8
function is_utf8($string)
{
    return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}

//获取日期
/*			case "yesterday";
                $date = date("Y-m-d",$now_time);//d,w,m分别表示天，周，月,后面的第三个参数选填，正数1表示后一天(d)的00:00:00到23:59:59负数表示前一天(d),-2表示前面第二天的00:00:00到23:59:59
                $day = get_date($date,'d',-1);//第三个参数表示时间段包含的天数
            break;
*/
function get_date($date, $t='d', $n=0)
{
    if ($t=='d') {
        $firstday = date('Y-m-d 00:00:00', strtotime("$n day"));
        $lastday = date("Y-m-d 23:59:59", strtotime("$n day"));
    } elseif ($t=='w') {
        if ($n!=0) {
            $date = date('Y-m-d', strtotime("$n week"));
        }
        $lastday = date("Y-m-d 00:00:00", strtotime("$date Sunday"));
        $firstday = date("Y-m-d 23:59:59", strtotime("$lastday -6 days"));
    } elseif ($t=='m') {
        if ($n!=0) {
            if (date("m", time())==1) {
                $date = date('Y-m-d', strtotime("$n months -1 day"));
            }//2特殊的2月份
            else {
                $date = date('Y-m-d', strtotime("$n months"));
            }
        }

        $firstday = date("Y-m-01 00:00:00", strtotime($date));
        $lastday = date("Y-m-d 23:59:59", strtotime("$firstday +1 month -1 day"));
    }
    return array($firstday,$lastday);
}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */


function rand_string($ukey="", $len=6, $type='1', $utype='1', $addChars='')
{
    $str ='';
    switch ($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789', 3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        default:
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if ($len>10) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    $chars   =   str_shuffle($chars);
    $str     =   substr($chars, 0, $len);
    if (!empty($ukey)) {
        $vd['code'] = $str;
        $vd['send_time'] = time();
        $vd['ukey'] = $ukey;
        $vd['type'] = $utype;
        M('verify')->add($vd);
    }
    return $str;
}

//验证是否通过
function is_verify($uid, $code, $utype, $timespan)
{
    if (!empty($uid)) {
        $vd['ukey'] = $uid;
    }
    $vd['type'] = $utype;
    $vd['send_time'] = array("lt",time()+$timespan);
    $vd['code'] = $code;
    $vo = M("verify")->field('ukey')->where($vd)->find();
    if (is_array($vo)) {
        return $vo['ukey'];
    } else {
        return false;
    }
}
//网站基本设置
function get_global_setting()
{
    $list=array();
    if (!S('global_setting')) {
        $list_t = M('global')->field('code,text')->select();
        foreach ($list_t as $key => $v) {
            $list[$v['code']] = de_xie($v['text']);
        }
        S('global_setting', $list);
        S('global_setting', $list, 3600*C('TTXF_TMP_HOUR'));
    } else {
        $list = S('global_setting');
    }

    return $list;
}
//acl权限管理
/*
print_r(acl_get_key(array('global','data','eqaction_edit'),$acl_inc));
*/


//获取用户权限数组
function get_user_acl($uid="")
{
    $model=strtolower(MODULE_NAME);
//var_dump($model);
//exit;
    if (empty($uid)) {
        return false;
    }
    $gid = M('ausers')->field('u_group_id')->find($uid);

    $al = get_group_data($gid['u_group_id']);
    $acl = $al['controller'];
    $acl_key = acl_get_key();
    if (array_keys($acl[$model], $acl_key)) {
        return true;
    } else {
        return false;
    }
}

//获取权限列表
function get_group_data($gid=0)
{
    $gid=intval($gid);
    $list=array();

    if ($gid==0) {
        if (!S("ACL_all")) {
            $_acl_data = M('acl')->select();
            $acl_data=array();

            foreach ($_acl_data as $key => $v) {
                $acl_data[$v['group_id']] = $v;
                $acl_data[$v['group_id']]['controller'] = unserialize($v['controller']);
            }

            S("ACL_all", $acl_data, C('ADMIN_CACHE_TIME'));
            $list = $acl_data;
        } else {
            $list = S("ACL_all");
        }
    } else {
        if (!S("ACL_".$gid)) {
            $_acl_data = M('acl')->find($gid);
            $_acl_data['controller'] = unserialize($_acl_data['controller']);
            $acl_data = $_acl_data;
            S("ACL_".$gid, $acl_data, C('ADMIN_CACHE_TIME'));
            $list = $acl_data;
        } else {
            $list = S("ACL_".$gid);
        }
    }
    return $list;
}
//删除文件夹并重建文件夹
function rmdirr($dirname)
{
    if (!file_exists($dirname)) {
        return false;
    }

    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    $dir = dir($dirname);

    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    $dir->close();

    return rmdir($dirname);
}
//删除文件夹及文件夹下所有内容
function Rmall($dirname)
{
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    $dir = dir($dirname);//如果对像是目录

    while (false !== $file = $dir->read()) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        if (!is_dir($dirname."/".$file)) {
            unlink($dirname."/".$file);
        } else {
            Rmall($dirname."/".$file);
        }

        rmdir($dirname."/".$file);
    }

    $dir->close();

    rmdir($dirname);

    return true;
}

//取得文件内容
function ReadFiletext($filepath)
{
    $htmlfp=@fopen($filepath, "r");
    $string="";
    while ($data=@fread($htmlfp, 1000)) {
        $string.=$data;
    }
    @fclose($htmlfp);
    return $string;
}

//生成文件
function MakeFile($con, $filename)
{//$filename是全物理路径加文件名
    MakeDir(dirname($filename));
    $fp=fopen($filename, "w");
    fwrite($fp, $con);
    fclose($fp);
}

//生成全路径文件夹
function MakeDir($dir)
{
    return is_dir($dir) or (MakeDir(dirname($dir)) and mkdir($dir, 0777));
}

//友情链接
function get_home_friend($type, $datas = array())
{
    $condition['is_show']=array('eq',1);

    $condition['link_type']=array('eq',$type);
    $type = "friend_home".$type;


    if (!S($type)) {
        $_list = M('friend')->field('link_txt,link_href,link_img,link_type')->where($condition)->order("link_order DESC")->select();
        $list=array();
        foreach ($_list as $key => $v) {
            $list[$key] = $v;
        }
        S($type, $list, 3600*C('HOME_CACHE_TIME'));
    } else {
        $list = S($type);
    }
    foreach ($list as $v) {
        echo "<a href='".$v['link_href']."' target='_blank'><img src='/".$v['link_img']."' Style='width:98px;height:39px;margin-right:10px;'></a>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    //return $list;
}

/*
栏目相关函数
Start
*/

//获取某栏目下的所有子栏目以nid-nid顺次链接
function get_type_leve($id="0")
{
    $model = D('Acategory');
    if (!S("type_son_type")) {
        $allid=array();
        $data = $model->field('id,type_nid')->where("parent_id = {$id}")->select();
        if (count($data)>0) {
            foreach ($data as $v) {
                //二级
                $allid[$v['type_nid']]=$v['id'];
                $data_1=array();//清空,避免下面判断错误
                $data_1 = $model->field('id,type_nid')->where("parent_id = {$v['id']}")->select();
                if (count($data_1)>0) {
                    foreach ($data_1 as $v1) {
                        //三级
                        $allid[$v['type_nid']."-".$v1['type_nid']]=$v1['id'];
                        $data_2=array();//清空,避免下面判断错误
                        $data_2 = $model->field('id,type_nid')->where("parent_id = {$v1['id']}")->select();
                        if (count($data_2)>0) {
                            foreach ($data_2 as $v2) {
                                //四级
                                $allid[$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']]=$v2['id'];
                                $data_3=array();//清空,避免下面判断错误
                                $data_3 = $model->field('id,type_nid')->where("parent_id = {$v2['id']}")->select();

                                if (count($data_3)>0) {
                                    foreach ($data_3 as $v3) {
                                        $allid[$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']."-".$v3['type_nid']]=$v3['id'];
                                    }
                                }
                                //四级
                            }
                        }
                        //三级
                    }
                }
                //二级
            }
        }
        S("type_son_type", $allid, 3600*C('HOME_CACHE_TIME'));
    } else {
        $allid = S("type_son_type");
    }

    return $allid;
}


//获取某栏目下的所有子栏目以nid-nid顺次链接
function get_area_type_leve($id="0", $area_id=0)
{
    $model = D('Aacategory');
    if (!S("type_son_type_area".$area_id)) {
        $allid=array();
        $data = $model->field('id,type_nid')->where("parent_id = {$id} AND area_id={$area_id}")->select();
        if (count($data)>0) {
            foreach ($data as $v) {
                //二级
                $allid[$area_id.$v['type_nid']]=$v['id'];
                $data_1=array();//清空,避免下面判断错误
                $data_1 = $model->field('id,type_nid')->where("parent_id = {$v['id']}")->select();
                if (count($data_1)>0) {
                    foreach ($data_1 as $v1) {
                        //三级
                        $allid[$area_id.$v['type_nid']."-".$v1['type_nid']]=$v1['id'];
                        $data_2=array();//清空,避免下面判断错误
                        $data_2 = $model->field('id,type_nid')->where("parent_id = {$v1['id']}")->select();
                        if (count($data_2)>0) {
                            foreach ($data_2 as $v2) {
                                //四级
                                $allid[$area_id.$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']]=$v2['id'];
                                $data_3=array();//清空,避免下面判断错误
                                $data_3 = $model->field('id,type_nid')->where("parent_id = {$v2['id']}")->select();

                                if (count($data_3)>0) {
                                    foreach ($data_3 as $v3) {
                                        $allid[$area_id.$v['type_nid']."-".$v1['type_nid']."-".$v2['type_nid']."-".$v3['type_nid']]=$v3['id'];
                                    }
                                }
                                //四级
                            }
                        }
                        //三级
                    }
                }
                //二级
            }
        }
        S("type_son_type_area".$area_id, $allid, 3600*C('HOME_CACHE_TIME'));
    } else {
        $allid = S("type_son_type_area".$area_id);
    }
    return $allid;
}

//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中1/2
function get_type_leve_nid($id="0")
{
    if (empty($id)) {
        return;
    }
    global $allid;
    static $r=array();//先声明要返回静态变量,不然在下面被赋值时是引用赋值
    get_type_leve_nid_run($id);

    $r = $allid;
    $GLOBALS['allid'] = null;

    return array_reverse($r);
}
//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中2/2
function get_type_leve_nid_run($id="0")
{
    global $allid;
    $data_parent = $data = "";
    $data = D('Acategory')->field('parent_id,type_nid')->find($id);
    $data_parent = D('Acategory')->field('id,type_nid')->where("id = {$data['parent_id']}")->find();
    if (isset($data_parent['type_nid'])>0) {
        if (!isset($allid[0])) {
            $allid[]=$data['type_nid'];
        }
        $allid[]=$data_parent['type_nid'];
        get_type_leve_nid_run($data_parent['id']);
    } else {
        if (!isset($allid[0])) {
            $allid[]=$data['type_nid'];
        }
    }
}


//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中1/2
function get_type_leve_area_nid($id="0", $area_id=0)
{
    if (empty($id)||empty($area_id)) {
        return;
    }
    global $allid_area;
    static $r=array();//先声明要返回静态变量,不然在下面被赋值时是引用赋值

    get_type_leve_area_nid_run($id);

    $r = $allid_area;
    $GLOBALS['allid_area'] = null;

    return array_reverse($r);
}
//获取某栏目的所有父栏目的type_nid,按由远到近的顺序出现在数组中2/2
function get_type_leve_area_nid_run($id="0")
{
    global $allid_area;
    $data_parent = $data = "";
    $data = D('Aacategory')->field('parent_id,type_nid,area_id')->find($id);
    $data_parent = D('Aacategory')->field('id,type_nid,area_id')->where("id = {$data['parent_id']}")->find();
    if (isset($data_parent['type_nid'])>0) {
        if (!isset($allid_area[0])) {
            $allid_area[]=$data['type_nid'];
        }
        $allid_area[]=$data_parent['type_nid'];
        get_type_leve_area_nid_run($data_parent['id']);
    } else {
        if (!isset($allid_area[0])) {
            $allid_area[]=$data['type_nid'];
        }
    }
}

//获取某栏目下的所有子栏目,查询次数较少，查询效率更高,入口函数1/2
function get_son_type($id)
{
    $tempname = "type_sfs_son_all".$id;
    if (!S($tempname)) {
        $row = get_son_type_run($id);
        S($tempname, $row, 3600*C('HOME_CACHE_TIME'));
    } else {
        $row = S($tempname);
    }
    return $row;
}

//获取某栏目下的所有子栏目,查询次数较少，查询效率更高2/2
function get_son_type_run($id)
{
    static $rerow;
    global $allid;
    $data = M('type')->field('id')->where("parent_id in ({$id})")->select();
    if (count($data)>0) {
        foreach ($data as $key=>$v) {
            $allid[]=$v['id'];
            $nowid[]=$v['id'];
        }
        $id = implode(",", $nowid);
        get_son_type_run($id);
    }
//递归函数不要加else来返回内容，不然得不到返回值
//	else{
//		return $allid;
//	}
    $rerow = $allid;
    $allid=array();
    return $rerow;
}

//获取某栏目下所有的子栏目,以数组的形式返回,入口函数1/2
function get_type_son($id=0)
{
    $tempname = "type_son_all".$id;
    if (!S($tempname)) {
        $row = get_type_son_all($id);
        S($tempname, $row, 3600*C('HOME_CACHE_TIME'));
    } else {
        $row = S($tempname);
    }
    return $row;
}
//获取某栏目下所有的子栏目2/2
function get_type_son_all($id="0")
{
    static $rerow;
    global $get_type_son_all_row;

    if (empty($id)) {
        exit;
    }

    $data = M('type')->where("parent_id = {$id}")->field('id')->select();
    foreach ($data as $key=>$v) {
        $get_type_son_all_row[]=$v['id'];
        $data_son = M('type')->where("parent_id = {$v['id']}")->field('id')->select();
        if (count($data_son)>0) {
            get_type_son_all($v['id']);
        }
    }

    $rerow = $get_type_son_all_row;
    $get_type_son_all_row = array();
    return $rerow;
}
//获取所有栏目每个栏目的父栏目的nid,以栏目ID为键名
function get_type_parent_nid()
{
    $row=array();
    $p_nid_new=array();
    if (!S("type_parent_nid_temp")) {
        $data = M('type')->field('id')->select();
        if (count($data)>0) {
            foreach ($data as $key => $v) {
                $p_nid = get_type_leve_nid($v['id']);
                $i=$n=count($p_nid);
                //倒序处理
                if ($i>1) {
                    for ($j=0;$j<$n;$j++, $i--) {
                        $p_nid_new[($i-1)]=$p_nid[$j];
                    }
                } else {
                    $p_nid_new = $p_nid;
                }
                //倒序处理
                $row[$v['id']] = $p_nid_new;
            }
        }
        S("type_parent_nid_temp", $row, 3600*C('HOME_CACHE_TIME'));
    } else {
        $row = S("type_parent_nid_temp");
    }

    return $row;
}

//获取以栏目ID为键的所有栏目数组,二维,如果field只有两个，并且其中一个是id，那么就会自动成为一维数组
function get_type_list($model, $field=true)
{
    $acaheName=md5("type_list_temp".$model.$field);
    if (!S($acaheName)) {
        $list = D(ucfirst($model))->getField($field);
        S($acaheName, $list, 3600*C('HOME_CACHE_TIME'));
    } else {
        $list = S($acaheName);
    }
    return $list;
}

//通过网址获取栏目相关信息
function get_type_infos()
{
    $row=array();
    $type_list = get_type_list('acategory', 'id,type_nid,type_set');
    if (!isset($_GET['typeid'])) {
        $type_nid = get_type_leve();//获得所有栏目自己的nid的组合
        $rurl = explode("?", $_SERVER['REQUEST_URI']);
        $xurl_tmp = explode("/", str_replace(array("index.html",".html"), array('',''), $rurl[0]));//获得组合的type_nid
        $zu = implode("-", array_filter($xurl_tmp));//组合
        //print_r($type_nid);
        $typeid = $type_nid[$zu];
        $typeset = $type_list[$typeid]['type_set'];
    } else {
        $typeid = intval($_GET['typeid']);
        $typeset = $type_list[$typeid]['type_set'];
    }

    if ($typeset==1) {//列表
        $templet = "list_index";
    } else {//单页
        $templet = "index_index";
    }

    $row['typeset'] = $typeset;
    $row['templet'] = $templet;
    $row['typeid'] = $typeid;

    return $row;
}

//通过网址获取栏目相关信息
function get_area_type_infos($area_id=0)
{
    $row=array();
    $type_list = get_type_list('aacategory', 'id,type_nid,type_set,area_id');
    if (!isset($_GET['typeid'])) {
        $type_nid = get_area_type_leve(0, $area_id);//获得所有栏目自己的nid的组合
        $rurl = explode("?", $_SERVER['REQUEST_URI']);
        $xurl_tmp = explode("/", str_replace(array("index.html",".html"), array('',''), $rurl[0]));//获得组合的type_nid
        $zu = implode("-", array_filter($xurl_tmp));//组合
        //print_r($type_nid);
        $typeid = $type_nid[$area_id.$zu];
        $typeset = $type_list[$typeid]['type_set'];
    } else {
        $typeid = intval($_GET['typeid']);
        $typeset = $type_list[$typeid]['type_set'];
    }

    if ($typeset==1) {//列表
        $templet = "list_index";
    } else {//单页
        $templet = "index_index";
    }

    $row['typeset'] = $typeset;
    $row['templet'] = $templet;
    $row['typeid'] = $typeid;

    return $row;
}

//获取栏目列表,按栏目分级,有缩进,入口函数1/2
function get_type_leve_list($id=0, $modelname=false, $type)
{
    static $rerow;
    global $get_type_leve_list_run_row;


    if (!$modelname) {
        $model = D("type");
    } else {
        $model=D(ucfirst($modelname));
    }
    $stype = $modelname."home_type_leve_list".$id;
    if (!S($stype)) {
        get_type_leve_list_run($id, $model, $type);
        $rerow = $get_type_leve_list_run_row;//把全局变量赋值给静态变量，避免引用清空
        $GLOBALS['get_type_leve_list_run_row']=null;//清空全局变量避免影响其他数据,不能用unset,unset只能清空单个变量或者数组中的某一元素,并且unset只能清空局部变量，清空全局变量要用unset($GLOBALS
        $data = $rerow;
        //S($stype,$data,3600*C('HOME_CACHE_TIME'));
    } else {
        $data = S($stype);
    }
    return $data;
}

//获取栏目列表,按栏目分级,有缩进2/2
function get_type_leve_list_run($id=0, $model, $type)
{
    global $get_type_leve_list_run_row;
    //全局变量的定义都要放在最前面
    $spa = "----";
    if (count($get_type_leve_list_run_row)<1) {
        $get_type_leve_list_run_row=array();
    }

    $typelist = $model->where("parent_id={$id} and model='{$type}'")->field('type_name,id,parent_id')->order('sort_order DESC')->select();//上级栏目

    foreach ($typelist as $k=>$v) {
        $leve = intval(get_typeLeve($v['id'], $model));
        $v['type_name'] = str_repeat($spa, $leve).$v['type_name'];
        $get_type_leve_list_run_row[]=$v;

        $typelist_s1 = $model->where("parent_id={$v['id']} and model='{$type}'")->field('type_name,id')->select();//上级栏目
        if (count($typelist_s1)>0) {
            get_type_leve_list_run($v['id'], $model, $type);
        }
    }
}//id


//获取栏目列表地区性的,按栏目分级,有缩进,入口函数1/2
function get_type_leve_list_area($id=0, $modelname=false, $area_id=0)
{
    static $rerow;
    global $get_type_leve_list_area_run_row;


    if (!$modelname) {
        $model = D("type");
    } else {
        $model=D(ucfirst($modelname));
    }
    $stype = $modelname."home_type_leve_list_area".$id.$area_id;
    if (!S($stype)) {
        get_type_leve_list_area_run($id, $model, $area_id);
        $rerow = $get_type_leve_list_area_run_row;//把全局变量赋值给静态变量，避免引用清空
        $GLOBALS['get_type_leve_list_area_run_row']=null;//清空全局变量避免影响其他数据,不能用unset,unset只能清空单个变量或者数组中的某一元素,并且unset只能清空局部变量，清空全局变量要用unset($GLOBALS
        $data = $rerow;
        S($stype, $data, 3600*C('HOME_CACHE_TIME'));
    } else {
        $data = S($stype);
    }
    return $data;
}

//获取栏目列表,按栏目分级,有缩进2/2
function get_type_leve_list_area_run($id=0, $model, $area_id)
{
    global $get_type_leve_list_area_run_row;
    //全局变量的定义都要放在最前面
    $spa = "----";
    if (count($get_type_leve_list_area_run_row)<1) {
        $get_type_leve_list_area_run_row=array();
    }

    $typelist = $model->where("parent_id={$id} AND area_id={$area_id}")->field('type_name,id,parent_id')->order('sort_order DESC')->select();//上级栏目

    foreach ($typelist as $k=>$v) {
        $leve = intval(get_typeLeve($v['id'], $model));
        $v['type_name'] = str_repeat($spa, $leve).$v['type_name'];
        $get_type_leve_list_area_run_row[]=$v;

        $typelist_s1 = $model->where("parent_id={$v['id']}")->field('type_name,id')->select();//上级栏目
        if (count($typelist_s1)>0) {
            get_type_leve_list_area_run($v['id'], $model, $area_id);
        }
    }
}//id


//获取栏目的级别1/2
function get_typeLeve($typeid, $model)
{
    $typeleve = 0;
    global $typeleve;
    static $rt=0;//先声明要返回静态变量,不然在下面被赋值时是引用赋值
    get_typeLeve_run($typeid, $model);
    $rt = $typeleve;
    unset($GLOBALS['typeleve']);
    return $rt;
}
//获取栏目的级别2/2
function get_typeLeve_run($typeid, $model)
{
    global $typeleve;
    $condition['id'] = $typeid;
    $v = $model->field('parent_id')->where($condition)->find();
    if ($v['parent_id']>0) {
        $typeleve++;
        get_typeLeve_run($v['parent_id'], $model);
    }
}

/*
栏目相关函数
End
*/
//在前台显示时去掉反斜线,传入数组，最多二维
function de_xie($arr)
{
    $data=array();
    if (is_array($arr)) {
        foreach ($arr as $key=>$v) {
            if (is_array($v)) {
                foreach ($v as $skey=>$sv) {
                    if (is_array($sv)) {
                    } else {
                        $v[$skey] = stripslashes($sv);
                    }
                }
                $data[$key] = $v;
            } else {
                $data[$key] = stripslashes($v);
            }
        }
    } else {
        $data = stripslashes($arr);
    }
    return $data;
}


//输出纯文本
function text($text, $parseBr=false, $nr=false)
{
    $text = htmlspecialchars_decode($text);
    $text    =    safe($text, 'text');
    if (!$parseBr&&$nr) {
        $text    =    str_ireplace(array("\r","\n","\t","&nbsp;","sleep"), '', $text);
        $text    =    htmlspecialchars($text, ENT_QUOTES);
    } elseif (!$nr) {
        $text    =    htmlspecialchars($text, ENT_QUOTES);
    } else {
        $text    =    htmlspecialchars($text, ENT_QUOTES);
        $text    =    nl2br($text);
    }
    $text    =    trim($text);
    return $text;
}
function safe($text, $type='html', $tagsMethod=true, $attrMethod=true, $xssAuto = 1, $tags=array(), $attr=array(), $tagsBlack=array(), $attrBlack=array())
{

    //无标签格式
    $text_tags    =    '';

    //只存在字体样式
    $font_tags    =    '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';

    //标题摘要基本格式
    $base_tags    =    $font_tags.'<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';

    //兼容Form格式
    $form_tags    =    $base_tags.'<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';

    //内容等允许HTML的格式
    $html_tags    =    $base_tags.'<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed>';

    //专题等全HTML格式
    $all_tags    =    $form_tags.$html_tags.'<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';

    //过滤标签
    $text    =    strip_tags($text, ${$type.'_tags'});

        //过滤攻击代码
        if ($type!='all') {
            //过滤危险的属性，如：过滤on事件lang js
            while (preg_match('/(<[^><]+) (onclick|onload|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
                $text    =    str_ireplace($mat[0], $mat[1].$mat[3], $text);
            }
            while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
                $text    =    str_ireplace($mat[0], $mat[1].$mat[3], $text);
            }
        }
    return $text;
}


//输出安全的html
function h($text, $tags = null)
{
    $text    =    trim($text);
    $text    =    preg_replace('/<!--?.*-->/', '', $text);
    //完全过滤注释
    $text    =    preg_replace('/<!--?.*-->/', '', $text);
    //完全过滤动态代码
    $text    =    preg_replace('/<\?|\?'.'>/', '', $text);
    //完全过滤js
    $text    =    preg_replace('/<script?.*\/script>/', '', $text);

    $text    =    str_replace('[', '&#091;', $text);
    $text    =    str_replace(']', '&#093;', $text);
    $text    =    str_replace('|', '&#124;', $text);
    //过滤换行符
    $text    =    preg_replace('/\r?\n/', '', $text);
    //br
    $text    =    preg_replace('/<br(\s\/)?'.'>/i', '[br]', $text);
    $text    =    preg_replace('/(\[br\]\s*){10,}/i', '[br]', $text);
    //过滤危险的属性，如：过滤on事件lang js
    while (preg_match('/(<[^><]+) (lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat)) {
        $text=str_replace($mat[0], $mat[1], $text);
    }
    while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
        $text=str_replace($mat[0], $mat[1].$mat[3], $text);
    }
    if (empty($tags)) {
        $tags = 'table|tbody|td|th|tr|i|b|u|strong|img|p|br|div|span|em|ul|ol|li|dl|dd|dt|a|alt|h[1-9]?';
        $tags.= '|object|param|embed';    // 音乐和视频
    }
    //允许的HTML标签
    $text    =    preg_replace('/<(\/?(?:'.$tags.'))( [^><\[\]]*)?>/i', '[\1\2]', $text);
    //过滤多余html
    $text    =    preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|style|xml)[^><]*>/i', '', $text);
    //过滤合法的html标签
    while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)) {
        $text=str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
    }
    //转换引号
    while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2\[\]]+)\2([^\[\]]*\])/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
    }
    //过滤错误的单个引号
    // 修改:2011.05.26 kissy编辑器中表情等会包含空引号, 简单的过滤会导致错误
//	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
//		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
//	}
    //转换其它所有不合法的 < >
    $text    =    str_replace('<', '&lt;', $text);
    $text    =    str_replace('>', '&gt;', $text);
    $text   =   str_replace('"', '&quot;', $text);
    //$text   =   str_replace('\'','&#039;',$text);
     //反转换
    $text    =    str_replace('[', '<', $text);
    $text    =    str_replace(']', '>', $text);
    $text    =    str_replace('|', '"', $text);
    //过滤多余空格
    $text    =    str_replace('  ', ' ', $text);
    return $text;
}
//根据原图片地址得到缩略图地址
function get_thumb_pic($str)
{
    $path = explode("/", $str);
    $sc = count($path);
    $path[($sc-1)] = "thumb_".$path[($sc-1)];
    return implode("/", $path);
}
//得到分类kvtable里的分类,以id为键
function get_kvtable($nid="")
{
    $stype = "kvtable".$nid;
    $list = array();
    if (!S($stype)) {
        if (!empty($nid)) {
            $tmplist = M('kvtable')->where("nid='{$nid}'")->field(true)->select();
        } else {
            $tmplist = M('rule')->field(true)->select();
        }
        foreach ($tmplist as $v) {
            $list[$v['id']]=$v;
        }
        S($stype, $list, 3600*C('HOME_CACHE_TIME'));
        $row = $list;
    } else {
        $list = S($stype);
        $row = $list;
    }

    return $row;
}
/*
* 中文截取，支持gb2312,gbk,utf-8,big5
*
* @param string $str 要截取的字串
* @param int $start 截取起始位置
* @param int $length 截取长度
* @param string $charset utf-8|gb2312|gbk|big5 编码
* @param $suffix 是否加尾缀
*/
function cnsubstr($str, $length, $start=0, $charset="utf-8", $suffix=true, $char=".")
{
    $str = strip_tags($str);
    if (function_exists("mb_substr")) {
        if (mb_strlen($str, $charset) <= $length) {
            return $str;
        }
        $slice = mb_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']          = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']          = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        if (count($match[0]) <= $length) {
            return $str;
        }
        $slice = join("", array_slice($match[0], $start, $length));
    }
    if ($suffix) {
        return $slice.$char.$char.$char;
    }
    return $slice;
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if (function_exists("mb_substr")) {
        if ($suffix) {
            return mb_substr($str, $start, $length, $charset)."******";
        } else {
            return mb_substr($str, $start, $length, $charset);
        }
    } elseif (function_exists('iconv_substr')) {
        if ($suffix) {
            return iconv_substr($str, $start, $length, $charset)."******";
        } else {
            return iconv_substr($str, $start, $length, $charset);
        }
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) {
        return $slice."******";
    }
    return $slice;
}

/*
    格式化显示时间
*/
function getLastTimeFormt($time, $type=0)
{
    if ($type==0) {
        $f="m-d H:i";
    } elseif ($type==1) {
        $f="Y-m-d H:i";
    }
    $agoTime = time() - $time;
    if ($agoTime <= 60&&$agoTime >=0) {
        return $agoTime.'秒前';
    } elseif ($agoTime <= 3600 && $agoTime > 60) {
        return intval($agoTime/60) .'分钟前';
    } elseif (date('d', $time) == date('d', time()) && $agoTime > 3600) {
        return '今天 '.date('H:i', $time);
    } elseif (date('d', $time+86400) == date('d', time()) && $agoTime < 172800) {
        return '昨天 '.date('H:i', $time);
    } else {
        return date($f, $time);
    }
}

/**
 * 获取指定uid的头像文件规范路径
 * 来源：Ucenter base类的get_avatar方法
 *
 * @param int $uid
 * @param string $size 头像尺寸，可选为'big', 'middle', 'small'
 * @param string $type 类型，可选为real或者virtual
 * @return unknown
 */
function get_avatar($uid, $size = 'middle', $type = '')
{
    $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
    $uid = abs(intval($uid));
    $uid = sprintf("%09d", $uid);
    $dir1 = substr($uid, 0, 3);
    $dir2 = substr($uid, 3, 2);
    $dir3 = substr($uid, 5, 2);
    $typeadd = $type == 'real' ? '_real' : '';
    $path = __ROOT__.'/Style/header/customavatars/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
    if (!file_exists(C("WEB_ROOT").$path)) {
        $path = __ROOT__."/Style/H/images/member/touxiang_07.png";
    }
    return  $path;
}
/**
 * 获取地区列表，id为键，地区名为值的二维数组
 */
function get_Area_list($id="")
{
    $cacheName = "temp_area_list_s";
    if (!S($cacheName)) {
        $list = M('area')->getField('id,name');
        S($cacheName, $list, 3600*1000000);
    } else {
        $list = S($cacheName);
    }
    if (!empty($id)) {
        return $list[$id];
    } else {
        return $list;
    }
}

/**
 * IP转换成地区
 */
function ip2area($ip="")
{
    if (strlen($ip)<6) {
        return;
    }
    import("ORG.Net.IpLocation");
    $Ip = new IpLocation("CoralWry.dat");
    $area = $Ip->getlocation($ip);
    $area = auto_charset($area);
    if ($area['country']) {
        $res = $area['country'];
    }
    if ($area['area']) {
        $res = $res."(".$area['area'].")";
    }
    if (empty($res)) {
        $res = "未知";
    }
    return $res;
}
//把秒换成小时或者天数
function second2string($second, $type=0)
{
    $day = floor($second/(3600*24));
    $second = $second%(3600*24);//除去整天之后剩余的时间
    $hour = floor($second/3600);
    $second = $second%3600;//除去整小时之后剩余的时间
    $minute = floor($second/60);
    $second = $second%60;//除去整分钟之后剩余的时间

    switch ($type) {
        case 0:
            if ($day>=1) {
                $res = $day."天";
            } elseif ($hour>=1) {
                $res = $hour."小时";
            } else {
                $res = $minute."分钟";
            }
        break;
        case 1:
            if ($day>=5) {
                $res = date("Y-m-d H:i", time()+$second);
            } elseif ($day>=1&&$day<5) {
                $res = $day."天前";
            } elseif ($hour>=1) {
                $res = $hour."小时前";
            } else {
                $res = $minute."分钟前";
            }
        break;
    }
    //返回字符串
    return $res;
}


//快速缓存调用和储存
function FS($filename, $data="", $path="")
{
    $path = C("WEB_ROOT").$path;
    if ($data=="") {
        $f = explode("/", $filename);
        $num = count($f);
        if ($num>2) {
            $fx = $f;
            array_pop($f);
            $pathe = implode("/", $f);
            $re = F($fx[$num-1], '', $pathe."/");
        } else {
            isset($f[1])?$re = F($f[1], '', C("WEB_ROOT").$f[0]."/"):$re = F($f[0]);
        }
        return $re;
    } else {
        if (!empty($path)) {
            $re = F($filename, $data, $path);
        } else {
            $re = F($filename, $data);
        }
    }
}
//格式化URL，只判断域名，前台后台共用，前台生成供判断的URL，后台生成供储存以便对比的URL
function formtUrl($url)
{
    if (!stristr($url, "http://")) {
        $url = str_replace("http://", "", $url);
    }

    $fourl = explode("/", $url);
    $domain = get_domain("http://".$fourl[0]);
    $perfix = str_replace($domain, '', $fourl[0]);
    return $perfix.$domain;
}
function get_domain($url)
{
    $pattern = "/[/w-]+/.(com|net|org|gov|biz|com.tw|com.hk|com.ru|net.tw|net.hk|net.ru|info|cn|com.cn|net.cn|org.cn|gov.cn|mobi|name|sh|ac|la|travel|tm|us|cc|tv|jobs|asia|hn|lc|hk|bz|com.hk|ws|tel|io|tw|ac.cn|bj.cn|sh.cn|tj.cn|cq.cn|he.cn|sx.cn|nm.cn|ln.cn|jl.cn|hl.cn|js.cn|zj.cn|ah.cn|fj.cn|jx.cn|sd.cn|ha.cn|hb.cn|hn.cn|gd.cn|gx.cn|hi.cn|sc.cn|gz.cn|yn.cn|xz.cn|sn.cn|gs.cn|qh.cn|nx.cn|xj.cn|tw.cn|hk.cn|mo.cn|org.hk|is|edu|mil|au|jp|int|kr|de|vc|ag|in|me|edu.cn|co.kr|gd|vg|co.uk|be|sg|it|ro|com.mo)(/.(cn|hk))*/";
    preg_match($pattern, $url, $matches);
    if (count($matches) > 0) {
        return $matches[0];
    } else {
        $rs = parse_url($url);
        $main_url = $rs["host"];
        if (!strcmp(long2ip(sprintf("%u", ip2long($main_url))), $main_url)) {
            return $main_url;
        } else {
            $arr = explode(".", $main_url);
            $count=count($arr);
            $endArr = array("com","net","org");//com.cn net.cn 等情况
            if (in_array($arr[$count-2], $endArr)) {
                $domain = $arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
            } else {
                $domain = $arr[$count-2].".".$arr[$count-1];
            }
            return $domain;
        }
    }
}
//格式化数字
function getFloatValue($f, $len)
{
    return  number_format($f, $len, '.', '');
}

//获取远程图片
function get_remote_img($content)
{
    $rt = C("WEB_ROOT");
    $img_dir = C("REMOTE_IMGDIR")?C("REMOTE_IMGDIR"):"/UF/Remote";//img_dir远程图片的保存目录，带前"/"不带后"/"
    $base_dir = substr($rt, 0, strlen($rt)-1);//$base_dir网站根目录物理路径，不带后"/"

    $content = stripslashes($content);
    $img_array = array();
    preg_match_all("/(src|SRC)=[\"|'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|bmp|png|ico))/isU", $content, $img_array); //获取内容中的远程图片
    $img_array = array_unique($img_array[2]); //把重复的图片去掉
    set_time_limit(0);
    $imgUrl = $img_dir."/".strftime("%Y%m%d", time()); //img_dir远程图片的保存目录，带前"/"不带后"/"
    $imgPath = $base_dir.$imgUrl; //$base_dir网站根目录物理路径，不带后"/"
    $milliSecond = strftime("%H%M%S", time());
    if (!is_dir($imgPath)) {
        MakeDir($imgPath, 0777);
    }//如果路径不存在则创建
    foreach ($img_array as $key =>$value) {
        $value = trim($value);
        $get_file = @file_get_contents($value);
        $rndFileName = $imgPath."/".$milliSecond.$key.".".substr($value, -3, 3);
        $fileurl = $imgUrl."/".$milliSecond.$key.".".substr($value, -3, 3);

        if ($get_file) {
            $fp = @fopen($rndFileName, "w");
            @fwrite($fp, $get_file);
            @fclose($fp);
        }
        $content = ereg_replace($value, $fileurl, $content);
    }
    //$content = addslashes($content);
    return $content;
}

function getSubSite()
{
    $map['is_open'] = 1;
    $list = M("area")->field(true)->where($map)->select();
    $cdomain = explode(".", $_SERVER['HTTP_HOST']);
    $cpx = array_pop($cdomain);
    $doamin = array_pop($cdomain);
    $host = ".".$doamin.".".$cpx;
    foreach ($list as $key=>$v) {
        $list[$key]['host'] = "http://".$v['domain'].$host;
    }
    return $list;
}
function getCreditsLog($map, $size)
{
    if (empty($map['uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_creditslog')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_creditslog')->where($map)->order('id DESC')->limit($Lsql)->select();
    $type_arr = C("MONEY_LOG");
    foreach ($list as $key=>$v) {
        //$list[$key]['type'] = $type_arr[$v['type']];
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function getCredit($uid)
{
    $pre = C('DB_PREFIX');
    $user = M('members m')->join("{$pre}member_money mm ON m.id=mm.uid")->where("m.id={$uid}")->find();
    if (!is_array($user)) {
        return "用户出错，请重新操作";
    }

    $credit = array();
    $credit['xy']['limit'] =    getFloatValue($user['credit_limit'], 2);
    $credit['xy']['use'] =        getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=1")->sum("borrow_money-repayment_money"), 2);
    $credit['xy']['cuse'] =    getFloatValue($credit['xy']['limit'] - $credit['xy']['use'], 2);

    $credit['db']['limit'] =    getFloatValue($user['vouch_limit'], 2);
    $credit['db']['use'] =        getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=2")->sum("borrow_money-repayment_money"), 2);
    $credit['db']['cuse'] =    getFloatValue($credit['db']['limit'] - $credit['db']['use'], 2);

    $credit['dy']['limit'] =    getFloatValue($user['diya_limit'], 2);
    $credit['dy']['use'] =        getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=5")->sum("borrow_money-repayment_money"), 2);
    $credit['dy']['cuse'] =    getFloatValue($credit['dy']['limit'] - $credit['dy']['use'], 2);

    $credit['jz']['limit'] =    getFloatValue(0.9 * M('investor_detail')->where(" investor_uid={$uid} AND status =7 ")->sum("capital+interest-interest_fee"), 2);
    $credit['jz']['use'] =        getFloatValue(M('borrow_info')->where("borrow_uid = {$uid} AND borrow_status in(0,2,4,6) AND borrow_type=4")->sum("borrow_money+borrow_interest-repayment_money-repayment_interest"), 2);
    $credit['jz']['cuse'] =    getFloatValue($credit['jz']['limit'] - $credit['jz']['use'], 2);

    $credit['all']['limit'] =    getFloatValue($credit['xy']['limit'] + $credit['db']['limit'] + $credit['dy']['limit'], 2);
    $credit['all']['use'] =    getFloatValue($credit['xy']['use'] + $credit['db']['use'] + $credit['dy']['use'], 2);
    $credit['all']['cuse'] =    getFloatValue($credit['all']['limit'] - $credit['all']['use'], 2);

    return $credit;
}

//积分日志
function getIntegralLog($map, $size)
{
    if (empty($map['uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_integrallog')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_integrallog')->where($map)->order('id DESC')->limit($Lsql)->select();
    $type_arr = C("INTEGRAL_LOG");
    foreach ($list as $key=>$v) {
        $list[$key]['type'] = $type_arr[$v['type']];
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

//所有圈子列表,以id为键
function Notice($type, $uid, $data=array())
{
    $datag = get_global_setting();
    $datag=de_xie($datag);
    $msgconfig = FS("Webconfig/msgconfig");

    $emailTxt = FS("Webconfig/emailtxt");
    $smsTxt = FS("Webconfig/smstxt");
    $msgTxt = FS("Webconfig/msgtxt");
    $emailTxt=de_xie($emailTxt);
    $smsTxt=de_xie($smsTxt);
    $msgTxt=de_xie($msgTxt);
        //邮件
        // import("ORG.Net.Email");
        // $port =$msgconfig['stmp']['port'];//25;
        // $smtpserver=$msgconfig['stmp']['server'];
        // $smtpuser = $msgconfig['stmp']['user'];
        // $smtppwd = $msgconfig['stmp']['pass'];
        // $mailtype = "HTML";
        // $sender = $msgconfig['stmp']['user'];
        // $smtp = new smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender);
        //邮件
        $minfo = M('members')->field('user_email,user_name,user_phone')->find($uid);
    $uname = $minfo['user_name'];

    switch ($type) {
        case 1://注册成功发送邮件
            $vcode = rand_string($uid, 32, 0, 1);
            $link='<a href="'.C('WEB_URL').'/member/common/emailverify?vcode='.$vcode.'">点击链接验证邮件</a>';
            /*站内信*/
            $body = str_replace(array("#UserName#"), array($uname), $msgTxt['regsuccess']);
            addInnerMsg($uid, "恭喜您注册成功", $body);
            /*站内信*/
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."注册成功";
            $body = str_replace(array("#UserName#","#LINK#"), array($uname,$link), $emailTxt['regsuccess']);
            $to = $minfo['user_email'];
            //$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype);
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;

        case 2://安全中心通过验证码改密码安全问题
            $vcode = rand_string($uid, 10, 3, 3);
            $pcode = rand_string($uid, 6, 1, 3);
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."注册成功";
            $body = str_replace(array("#CODE#"), array($vcode), $emailTxt['safecode']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/

            //手机
            $content = str_replace(array("#CODE#"), array($pcode), $smsTxt['safecode']);
            $sendp = sendsms($minfo['user_phone'], $content);
            return $send;
        break;

        case 3://安全中心通过验证码改手机
            $vcode = rand_string($uid, 6, 1, 4);
            $content = str_replace(array("#CODE#"), array($vcode), $smsTxt['safecode']);
            $send = sendsms($minfo['user_phone'], $content);
            return $send;

        case 4://安全中心新手机验证码
            $vcode = rand_string($uid, 6, 1, 5);
            $content = str_replace(array("#CODE#"), array($vcode), $smsTxt['safecode']);
            $send = sendsms($data['phone'], $content);
            return $send;
        break;

        case 5://安全中心新手机验证码安全码
            $vcode = rand_string($uid, 10, 1, 6);
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."申请更换手机的安全码";
            $body = str_replace(array("#CODE#"), array($vcode), $emailTxt['changephone']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;

        case 6://借款发布成功审核通过
            /*邮件*/
            $subject = "恭喜，你在".$datag['web_name']."发布的借款审核通过";
            $body = str_replace(array("#UserName#"), array($uname), $emailTxt['verifysuccess']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            /*站内信*/
            $body = str_replace(array("#UserName#"), array($uname), $msgTxt['verifysuccess']);
            addInnerMsg($uid, "恭喜借款审核通过", $body);
            /*站内信*/
            return $send;
        break;

        case 7://密码找回
            $vcode = rand_string($uid, 32, 0, 7);
            $link='<a href="'.C('WEB_URL').'/member/common/getpasswordverify?vcode='.$vcode.'">点击链接验证邮件</a>';
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."申请了密码找回";
            $body = str_replace(array("#UserName#","#LINK#"), array($uname,$link), $emailTxt['getpass']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;
        case 8://验证中心邮件验证
            $vcode = rand_string($uid, 32, 0, 1);
            $link='<a href="'.C('WEB_URL').'/member/common/emailverify?vcode='.$vcode.'">点击链接验证邮件</a>';
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."申请邮件验证";
            $body = str_replace(array("#UserName#","#LINK#"), array($uname,$link), $emailTxt['regsuccess']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;


        case 9://还款到期提醒
            /*邮件*/
            $subject = "您在".$datag['web_name']."的还款最终期限即将到期。";
            $body = str_replace(array("#UserName#","#borrowName#","#borrowMoney#"), array($uname,$data['borrowName'],$data['borrowMoney']), $emailTxt['repaymentTip']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;
        case 10://支付密码找回
            $vcode = rand_string($uid, 32, 0, 7);
            $link='<a href="'.C('WEB_URL').'/member/index/getpaypasswordverify?vcode='.$vcode.'">点击链接验证邮件</a>';
            /*邮件*/
            $subject = "您刚刚在".$datag['web_name']."申请了支付密码找回";
            $body = str_replace(array("#UserName#","#LINK#"), array($uname,$link), $emailTxt['getpaypass']);
            $to = $minfo['user_email'];
            $send = sendemail($to, $subject, $body);
            /*邮件*/
            return $send;
        break;
        case 11://支付密码找回
            $vcode = rand_string($uid, 6, 1, 5);
            $content = str_replace(array("#CODE#"), array($vcode), $smsTxt['safecode']);
            $send = sendsms($data['phone'], $content);
            print_r($send);die;
            return $send;
        break;

    }
}

function SMStip($type, $mob, $from=array(), $to=array())
{
    if (empty($mob)) {
        return;
    }
    $datag = get_global_setting();
    $datag=de_xie($datag);
    $smsTxt = FS("Webconfig/smstxt");
    $smsTxt=de_xie($smsTxt);
    if ($smsTxt[$type]['enable']==1) {
        $body = str_replace($from, $to, $smsTxt[$type]['content']);
        $send=sendsms($mob, $body);
    } else {
        return;
    }
}


//所有圈子列表,以id为键
function MTip($type, $uid=0, $info="", $autoid="")
{
    $datag = get_global_setting();
    $datag=de_xie($datag);
        //$port =25;
        //邮件
        $id1 = "{$type}_1";
    $id2 = "{$type}_2";
    $per = C('DB_PREFIX');

    $sql ="select 1 as tip1,0 as tip2,m.user_email,m.id from {$per}members m WHERE m.id={$uid}";
    $memail = M()->query($sql);
    $info = borrowidlayout1($info);
    switch ($type) {

        case "chk1"://修改密码
            /*邮件*/
            $to="";
            $subject = "您刚刚在".$datag['web_name']."修改了登陆密码";
            $body = "您刚刚在".$datag['web_name']."修改了登陆密码,如不是自己操作,请尽快联系客服<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您刚刚修改了登陆密码,如不是自己操作,请尽快联系客服";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您刚刚修改了登陆密码", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
                //if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk2"://修改银行帐号
            /*邮件*/
            $to="";
            $subject = "您刚刚在".$datag['web_name']."修改了提现的银行帐户";
            $body = "您刚刚在".$datag['web_name']."修改了提现的银行帐户,如不是自己操作,请尽快联系客服<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您刚刚修改了提现的银行帐户,如不是自己操作,请尽快联系客服";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您刚刚修改了提现的银行帐户", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk6"://资金提现
            /*邮件*/
            $to="";
            $subject = "您刚刚在".$datag['web_name']."申请了提现操作";
            $body = "您刚刚在".$datag['web_name']."申请了提现操作,如不是自己操作,请尽快联系客服<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您刚刚申请了提现操作,如不是自己操作,请尽快联系客服";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您刚刚申请了提现操作", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk7"://借款标初审未通过
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."发布的借款标刚刚初审未通过";
            $body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚初审未通过<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您发布的第{$info}号借款标刚刚初审未通过";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的借款标初审未通过", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk8"://借款标初审通过
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."发布的借款标刚刚初审通过";
            $body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚初审通过<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您发布的第{$info}号借款标刚刚初审通过";

            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的借款标初审通过", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk9"://借款标复审通过
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."发布的借款标刚刚复审通过";
            $body = "您在".$datag['web_name']."发布的第{$info}号借款标刚刚复审通过
			<hr>
			链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
			网址：www.ccfax.cn<br>
			客服电话：400-6626-985<br>
			运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您发布的第{$info}号借款标刚刚复审通过";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的借款标复审通过", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk12"://借款标复审未通过
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."的发布的借款标刚刚复审未通过";
            $body = "您在".$datag['web_name']."的发布的第{$info}号借款标复审未通过<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您发布的第{$info}号借款标复审未通过";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的借款标复审未通过", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk10"://借款标满标
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."的借款标已满标";
            $body = "刚刚您在".$datag['web_name']."的第{$info}号借款标已满标，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "刚刚您的借款标已满标";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的第{$info}号借款标已满标", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk11"://借款标流标
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."的借款标已流标";
            $body = "您在".$datag['web_name']."发布的第{$info}号借款标已流标，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您的第{$info}号借款标已流标";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "刚刚您的借款标已流标", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk25"://借入人还款成功
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."的借入的还款进行了还款操作";
            $body = "您对在".$datag['web_name']."借入的第{$info}号借款进行了还款，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您对借入的第{$info}号借款进行了还款";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您对借入标还款进行了还款操作", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk27"://自动投标借出完成
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."设置的第{$autoid}号自动投标按设置投了新标";
            $body = "您在".$datag['web_name']."设置的第{$autoid}号自动投标按设置对第{$info}号借款进行了投标，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您设置的第{$autoid}号自动投标对第{$info}号借款进行了投标";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您设置的第{$autoid}号自动投标按设置投了新标", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk14"://借出成功
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."投标的成功了";
            $body = "尊敬的链金所用户您好！<br>
					链金所CCFAX很高兴的通知您，您在链金所投标的第{$info}号标已经成功，您可以登录官网进行查询。<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您在".$datag['web_name']."投标的成功了";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您投标的第{$info}号借款借款成功", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;


        case "chk15"://借出流标
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."投标的借款流标了";
            $body = "您在".$datag['web_name']."投标的第{$info}号借款流标了，相关资金已经返回帐户，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您投标的借款流标了";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您投标的第{$info}号借款流标了，相关资金已经返回帐户", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk16"://收到还款
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."借出的借款收到了新的还款";
            $body = "您在".$datag['web_name']."借出的第{$info}号借款收到了新的还款，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您借出的借款收到了新的还款";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您借出的第{$info}号借款收到了新的还款", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk18"://网站代为偿还
            /*邮件*/
            $to="";
            $subject = "您在".$datag['web_name']."借出的借款逾期网站代还了本金";
            $body = "您在".$datag['web_name']."借出的第{$info}号借款逾期网站代还了本金，请登陆查看<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您借出的第{$info}号借款逾期网站代还了本金";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您借出的第{$info}号借款逾期网站代还了本金", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;

        case "chk19"://修改支付密码
            /*邮件*/
            $to="";
            $subject = "您刚刚在".$datag['web_name']."修改了支付密码";
            $body = "您刚刚在".$datag['web_name']."修改了支付密码,如不是自己操作,请尽快联系客服<br>
					<hr>
					链金所CCFAX 【链金所-融汇财富，产业帮扶】<br>
					网址：www.ccfax.cn<br>
					客服电话：400-6626-985<br>
					运营职场：广东省深圳市福田区车公庙泰然六路雪松大厦B座11DE";
            $innerbody = "您刚刚修改了支付密码,如不是自己操作,请尽快联系客服";
            /*邮件*/
            foreach ($memail as $v) {
                if ($v['tip1']>0) {
                    addInnerMsg($v['id'], "您刚刚修改了支付密码", $innerbody);
                }
                $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
                //if($v['tip2']>0) $to = empty($to)?$v['user_email']:$to.",".$v['user_email'];
            }
        break;
    }

    if (!empty($to)) {
        $send = sendemail($to, $subject, $body);
    }

    return $send;
}

/**
 * 债权平分
*/
function zhaiquan_fen($borrow_id, $uid, $money)
{
    $where["debt_id"] = $borrow_id;
    $where["investor_uid"] = $uid;
    $where["investor_capital"] = $money;
    $debt_investor = M("borrow_investor")->where($where)->find();
    $debt_info = M("debt_borrow_info")->where(array("id"=>$borrow_id))->find();
    $invset_list = M("investor_detail")->where(array("invest_id"=>$debt_info["invest_id"],"repayment_time"=>0))->select();
    $debt_invest = array();
    $debt_invest = $invset_list;
    foreach ($debt_invest as $key => $value) {
        $debt_invest[$key]["invest_id"] = $debt_investor["id"];
        $debt_invest[$key]["investor_uid"] = $uid;
        $debt_invest[$key]["capital"] = getFloatValue($value["capital"]*$debt_investor["debt_percent"], 2);
        $debt_invest[$key]["interest"] = getFloatValue($value["interest"]*$debt_investor["debt_percent"], 2);
        $debt_invest[$key]["jiaxi_money"] = 0;
        $debt_invest[$key]["jiaxi_rate"] = 0;
        $debt_invest[$key]["debt_borrow_id"] = $borrow_id;
        unset($debt_invest[$key]["id"]);
    }
    file_put_contents('debtlog.txt', "债权平分：还款记录表数据：".var_export($debt_invest, true)."\n", FILE_APPEND);
    $debt_investor_detail = M("investor_detail")->addAll($debt_invest);
    if ($debt_investor_detail) {
        $debt_investor_res = M("borrow_investor")->where(array("id"=>$debt_investor["id"]))->save(array("status"=>4));
    }
    file_put_contents("debtlog.txt", "债权平分：还款记录表：".$debt_investor_detail.",债权投资记录表：".$debt_investor_res."\n", FILE_APPEND);
}

/**
 * 债权转让计算利息
 * @param $uid   投资人编号
 * @param $borrow_id 标号
 * @param $money 投资本金
 * @param int $_is_auto 是否自动
 * @return bool
 */
function zhaiquan_investMoney($uid, $borrow_id, $money, $_is_auto=0)
{
    $pre = C('DB_PREFIX');
    $done = false;
    $datag = get_global_setting();
    //$fee_invest_manage = explode("|",$datag['fee_invest_manage']);
    /////////////////////////////锁表  辉 2013-11-16////////////////////////////////////////////////
    $dataname = C('DB_NAME');
    $db_host = C('DB_HOST');
    $db_user = C('DB_USER');
    $db_pwd = C('DB_PWD');
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $bId = $borrow_id;
    $sql1 ="SELECT suo FROM lzh_debt_borrow_info_lock WHERE id = ? FOR UPDATE";
    $stmt1 = $bdb->prepare($sql1);
    $stmt1->bindParam(1, $bId);    //绑定第一个参数值
    $stmt1->execute();

    $debt_info = M("debt_borrow_info")->where(array("id"=>$borrow_id))->find();
    $befor_investor = M("borrow_investor")->where(array("id"=>$debt_info["invest_id"]))->find();
    $investMoney = D('borrow_investor');
    $investMoney->startTrans();
    $borrow_money = getFloatValue($debt_info["borrow_money"], 2);
    $has_borrow = getFloatValue($debt_info["has_borrow"], 2);
    $money = getFloatValue($money, 2);
    $total = getFloatValue($borrow_money-($has_borrow+$money), 2);
    file_put_contents('debtlog.txt', "转让总额:".$borrow_money."\n", FILE_APPEND);
    file_put_contents('debtlog.txt', "已转金额:".$has_borrow."\n", FILE_APPEND);
    file_put_contents('debtlog.txt', "当前购买金额:".$money."\n", FILE_APPEND);
    file_put_contents('debtlog.txt', "剩余金额结果:".$total."\n", FILE_APPEND);
    if ($total < 0 || ($total >0 && $total < 100)) {
        $done = false;
    } else {
        //计算投资占比插入投资记录
        $debt_percent = getFloatValue($money/$debt_info["borrow_money"], 4);
        $investinfo=$befor_investor;
        unset($investinfo["id"]);
        $investinfo["status"]=1;//待审核
        $investinfo["investor_uid"] = $uid;
        $investinfo["borrow_uid"] = $befor_investor["borrow_uid"];
        $investinfo["investor_capital"] = $money;
        $investinfo["investor_interest"] = getFloatValue($befor_investor["investor_interest"]*$debt_percent, 2);
        $investinfo["receive_capital"] = 0;
        $investinfo["receive_interest"] = 0;
        $investinfo["debt_percent"] = $debt_percent;
        $investinfo["debt_status"] = 0;
        $investinfo["debt_uid"] = $debt_info["borrow_uid"];
        $investinfo["debt_id"] = $borrow_id;
        $investinfo["add_time"] = time();
        $investinfo["is_auto"] = $_is_auto;
        $invest_info_id = M('borrow_investor')->add($investinfo);
        file_put_contents('debtlog.txt', "\r\n投资记录参数:".var_export($investinfo, true), FILE_APPEND);

        file_put_contents('debtlog.txt', "\r\n投资记录SQL:".M('borrow_investor')->getLastSql(), FILE_APPEND);
        $debt_data["has_borrow"] = $debt_info["has_borrow"]+$money;
        $debt_data["borrow_times"] = $debt_info["borrow_times"]+1;
        if ($total == 0) {
            $debt_data["borrow_status"] = 4;
            $debt_data["full_time"] = time();
            $caiwu_phone = C('NOTICE_TEL.caiwu');
            $content = '第ZQ'.$borrow_id.'号标已满标，您可登录平台查询详情并审核。';
            sendsms($caiwu_phone, $content);
        }
        $debt_res = M("debt_borrow_info")->where(array("id"=>$borrow_id))->save($debt_data);
        file_put_contents('debtlog.txt', "投资记录结果:".$invest_info_id, FILE_APPEND);

        file_put_contents('debtlog.txt', "债权详情结果:".$debt_res, FILE_APPEND);

        if ($invest_info_id && $debt_res) {
            $done = true;
            $investMoney->commit();
        } else {
            $done = false;
            $investMoney->rollback();
        }
    }
    return $done;
}

// 输入密码后开始投标
function investMoney($uid, $borrow_id, $money, $_is_auto=0, $jx_rate=0)
{
    //file_put_contents('log.txt', $uid."-".$borrow_id."-".$money, FILE_APPEND);
    $pre = C('DB_PREFIX');
    $done = false;
    $datag = get_global_setting();
    //$fee_invest_manage = explode("|",$datag['fee_invest_manage']);
    /////////////////////////////锁表  辉 2013-11-16////////////////////////////////////////////////
    $dataname = C('DB_NAME');
    $db_host = C('DB_HOST');
    $db_user = C('DB_USER');
    $db_pwd = C('DB_PWD');
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $bId = $borrow_id;
    $sql1 ="SELECT suo FROM lzh_borrow_info_lock WHERE id = ? FOR UPDATE";
    $stmt1 = $bdb->prepare($sql1);
    $stmt1->bindParam(1, $bId);    //绑定第一个参数值
    $stmt1->execute();
    /////////////////////////////锁表  辉 2013-11-16////////////////////////////////////////////////
    $binfo = M("borrow_info")->field("borrow_uid,borrow_money,borrow_interest_rate,borrow_type,borrow_duration,repayment_type,has_borrow,reward_money,money_collect,jiaxi_rate")->find($borrow_id);//新加入了奖金reward_money到资金总额里
    $vminfo = getMinfo($uid, 'm.user_leve,m.time_limit,mm.account_money,mm.back_money,mm.money_collect');
    //  $saving=querysaving($uid);
    //     $balance=querybalance($uid);
     // if(($vminfo['account_money']+$vminfo['back_money']+$binfo['reward_money'])<$money) {
 //     if(($saving+$balance)<$money) {
    // 	//return "您当前的可用金额为：".($vminfo['account_money']+$vminfo['back_money']+$binfo['reward_money'])." 对不起，可用余额不足，不能投标";
    // 	return "您当前的可用金额为：".($saving+$balance)." 对不起，可用余额不足，不能投标";
    // }
    ////////////新增投标时检测会员的待收金额是否大于标的设置的代收金额限制，大于就可投标，小于就不让投标 2013-08-26 fan//////////////

    if ($binfo['money_collect']>0) {//判断是否设置了投标待收金额限制
        if ($vminfo['money_collect']<$binfo['money_collect']) {
            return "对不起，此标设置有投标待收金额限制，您当前的待收金额为".$vminfo['money_collect']."元，小于该标设置的待收金额限制".$binfo['money_collect']."元。";
        }
    }

    ////////////新增投标时检测会员的待收金额是否大于标的设置的代收金额限制，大于就可投标，小于就不让投标 2013-08-26 fan//////////////

    //不同会员级别的费率
    //($vminfo['user_leve']==1 && $vminfo['time_limit']>time())?$fee_rate=($fee_invest_manage[1]/100):$fee_rate=($fee_invest_manage[0]/100);
    $fee_rate=$datag['fee_invest_manage']/100;
    //投入的钱
    $havemoney = $binfo['has_borrow'];
    if (($binfo['borrow_money'] - $havemoney -$money)<0) {
        return "对不起，此标还差".($binfo['borrow_money'] - $havemoney)."元满标，您最多投标".($binfo['borrow_money'] - $havemoney)."元";
    }

    $borrow_invest = M("borrow_investor")->where('borrow_id = {$borrow_id}')->sum('investor_capital');//新加投资金额检测

    $investMoney = D('borrow_investor');
    $investMoney->startTrans();
        //还款概要公共信息START
        $investinfo['status'] = 1;//等待复审
        $investinfo['borrow_id'] = $borrow_id;
    $investinfo['investor_uid'] = $uid;
    $investinfo['borrow_uid'] = $binfo['borrow_uid'];

        /////////////////////////////////////新加投资金额检测/////////////////////////////////////////////
        if ($borrow_invest['investor_capital']>$binfo['borrow_money']) {
            $investinfo['investor_capital'] = $binfo['borrow_money'] - $binfo['has_borrow'];
        } else {
            $investinfo['investor_capital'] = $money;
        }
        /////////////////////////////////////新加投资金额检测/////////////////////////////////////////////

        $investinfo['is_auto'] = $_is_auto;
    $investinfo['add_time'] = time();
        //加息
        $jx_total = $binfo['jiaxi_rate']+$jx_rate;//标的加息+加息券的利息

        //还款详细公共信息START
        //$binfo['repayment_type']标的类型 1 信用标 2 担保标
        $savedetail=array();
    switch ($binfo['repayment_type']) {
            case 1://按天到期还款
                //还款概要START
                $investinfo['investor_interest'] = getFloatValue($binfo['borrow_interest_rate']/360*$investinfo['investor_capital']*$binfo['borrow_duration']/100, 4);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);//修改投资人的天标利息管理费2013-03-19 fan
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END
                $investdetail['borrow_id'] = $borrow_id;
                $investdetail['invest_id'] = $invest_info_id;
                $investdetail['investor_uid'] = $uid;
                $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                $investdetail['capital'] = $investinfo['investor_capital'];
                $investdetail['interest'] = $investinfo['investor_interest'];
                $investdetail['interest_fee'] = $investinfo['invest_fee'];
                $investdetail['status'] = 0;
                $investdetail['sort_order'] = 1;
                $investdetail['total'] = 1;
                //加息
                if ($jx_total > 0) {
                    $investdetail['jiaxi_money'] = getFloatValue($jx_total/360*$investinfo['investor_capital']*$binfo['borrow_duration']/100, 4);
                    $investdetail['jiaxi_rate'] = $jx_rate;
                }
                $savedetail[] = $investdetail;
            break;
            case 2://每月还款
                //还款概要START
                $monthData['type'] = "all";
                $monthData['money'] = $investinfo['investor_capital'];
                $monthData['year_apr'] = $binfo['borrow_interest_rate'];
                $monthData['duration'] = $binfo['borrow_duration'];
                $repay_detail = EqualMonth($monthData);

                $investinfo['investor_interest'] = ($repay_detail['repayment_money'] - $investinfo['investor_capital']);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END

                $monthDataDetail['money'] = $investinfo['investor_capital'];
                $monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
                $monthDataDetail['duration'] = $binfo['borrow_duration'];
                $repay_list = EqualMonth($monthDataDetail);
                //加息
                if ($jx_total > 0) {
                    $monthDataDetail['year_apr'] = $jx_total;
                    $jx_list = EqualMonth($monthDataDetail);
                }
                $i=1;
                foreach ($repay_list as $key=>$v) {
                    $investdetail['borrow_id'] = $borrow_id;
                    $investdetail['invest_id'] = $invest_info_id;
                    $investdetail['investor_uid'] = $uid;
                    $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                    $investdetail['capital'] = $v['capital'];
                    $investdetail['interest'] = $v['interest'];
                    $investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'], 4);
                    $investdetail['status'] = 0;
                    $investdetail['sort_order'] = $i;
                    $investdetail['total'] = $binfo['borrow_duration'];
                    //加息
                    if ($jx_total > 0) {
                        $investdetail['jiaxi_money'] =$jx_list[$key]['interest'];
                        $investdetail['jiaxi_rate'] = $jx_rate;
                    }
                    $i++;
                    $savedetail[] = $investdetail;
                }
            break;
            case 3://按季分期还款
                //还款概要START

                $monthData['month_times'] = $binfo['borrow_duration'];
                $monthData['account'] = $investinfo['investor_capital'];
                $monthData['year_apr'] = $binfo['borrow_interest_rate'];
                $monthData['type'] = "all";
                $repay_detail = EqualSeason($monthData);

                $investinfo['investor_interest'] = ($repay_detail['repayment_money'] - $investinfo['investor_capital']);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END

                $monthDataDetail['month_times'] = $binfo['borrow_duration'];
                $monthDataDetail['account'] = $investinfo['investor_capital'];
                $monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
                $repay_list = EqualSeason($monthDataDetail);
                //加息
                if ($jx_total > 0) {
                    $monthDataDetail['year_apr'] = $jx_total;
                    $jx_list = EqualSeason($monthDataDetail);
                }
                $i=1;
                foreach ($repay_list as $key=>$v) {
                    $investdetail['borrow_id'] = $borrow_id;
                    $investdetail['invest_id'] = $invest_info_id;
                    $investdetail['investor_uid'] = $uid;
                    $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                    $investdetail['capital'] = $v['capital'];
                    $investdetail['interest'] = $v['interest'];
                    $investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'], 4);
                    $investdetail['status'] = 0;
                    $investdetail['sort_order'] = $i;
                    $investdetail['total'] = $binfo['borrow_duration'];
                    //加息
                    if ($jx_total > 0) {
                        $investdetail['jiaxi_money'] =$jx_list[$key]['interest'];
                        $investdetail['jiaxi_rate'] = $jx_rate;
                    }
                    $i++;
                    $savedetail[] = $investdetail;
                }
            break;
            case 4://每月还息到期还本
                $monthData['month_times'] = $binfo['borrow_duration'];
                $monthData['account'] = $investinfo['investor_capital'];
                $monthData['year_apr'] = $binfo['borrow_interest_rate'];
                $monthData['type'] = "all";
                $repay_detail = EqualEndMonth($monthData);

                $investinfo['investor_interest'] = ($repay_detail['repayment_account'] - $investinfo['investor_capital']);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END

                $monthDataDetail['month_times'] = $binfo['borrow_duration'];
                $monthDataDetail['account'] = $investinfo['investor_capital'];
                $monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
                $repay_list = EqualEndMonth($monthDataDetail);
                //加息
                if ($jx_total > 0) {
                    $monthDataDetail['year_apr'] = $jx_total;
                    $jx_list = EqualEndMonth($monthDataDetail);
                }
                $i=1;
                foreach ($repay_list as $key=>$v) {
                    $investdetail['borrow_id'] = $borrow_id;
                    $investdetail['invest_id'] = $invest_info_id;
                    $investdetail['investor_uid'] = $uid;
                    $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                    $investdetail['capital'] = $v['capital'];
                    $investdetail['interest'] = $v['interest'];
                    $investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'], 4);
                    $investdetail['status'] = 0;
                    $investdetail['sort_order'] = $i;
                    $investdetail['total'] = $binfo['borrow_duration'];
                    //加息
                    if ($jx_total > 0) {
                        $investdetail['jiaxi_money'] =$jx_list[$key]['interest'];
                        $investdetail['jiaxi_rate'] = $jx_rate;
                    }
                    $i++;
                    $savedetail[] = $investdetail;
                }
            break;
            case 5://一次性还款
                $monthData['month_times'] = $binfo['borrow_duration'];
                $monthData['account'] = $investinfo['investor_capital'];
                $monthData['year_apr'] = $binfo['borrow_interest_rate'];
                $monthData['type'] = "all";
                $repay_detail = EqualEndMonthOnly($monthData);

                $investinfo['investor_interest'] = ($repay_detail['repayment_account'] - $investinfo['investor_capital']);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END

                $monthDataDetail['month_times'] = $binfo['borrow_duration'];
                $monthDataDetail['account'] = $investinfo['investor_capital'];
                $monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
                $monthDataDetail['type'] = "all";
                $repay_list = EqualEndMonthOnly($monthDataDetail);
                //加息
                if ($jx_total > 0) {
                    $monthDataDetail['year_apr'] = $jx_total;
                    $jx_list = EqualEndMonth($monthDataDetail);
                }

                $investdetail['borrow_id'] = $borrow_id;
                $investdetail['invest_id'] = $invest_info_id;
                $investdetail['investor_uid'] = $uid;
                $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                $investdetail['capital'] = $repay_list['capital'];
                $investdetail['interest'] = $repay_list['interest'];
                $investdetail['interest_fee'] = getFloatValue($fee_rate*$repay_list['interest'], 4);
                $investdetail['status'] = 0;
                $investdetail['sort_order'] = 1;
                $investdetail['total'] = 1;
                //加息
                if ($jx_total > 0) {
                    $investdetail['jiaxi_money'] =$jx_list['interest'];
                    $investdetail['jiaxi_rate'] = $jx_rate;
                }

                $savedetail[] = $investdetail;

            break;
            case 7:
                //还款概要START
                $monthData['type'] = "all";
                $monthData['money'] = $investinfo['investor_capital'];
                $monthData['year_apr'] = $binfo['borrow_interest_rate'];
                $monthData['duration'] = $binfo['borrow_duration'];
                $repay_detail = EqualMonth1($monthData);

                $investinfo['investor_interest'] = ($repay_detail['repayment_money'] - $investinfo['investor_capital']);
                $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest'], 4);
                $invest_info_id = M('borrow_investor')->add($investinfo);
                //还款概要END

                $monthDataDetail['money'] = $investinfo['investor_capital'];
                $monthDataDetail['year_apr'] = $binfo['borrow_interest_rate'];
                $monthDataDetail['duration'] = $binfo['borrow_duration'];
                $repay_list = EqualMonth1($monthDataDetail);
                //加息
                if ($jx_total > 0) {
                    $monthDataDetail['year_apr'] = $jx_total;
                    $jx_list = EqualMonth1($monthDataDetail);
                }
                $i=1;
                foreach ($repay_list as $key=>$v) {
                    $investdetail['borrow_id'] = $borrow_id;
                    $investdetail['invest_id'] = $invest_info_id;
                    $investdetail['investor_uid'] = $uid;
                    $investdetail['borrow_uid'] = $binfo['borrow_uid'];
                    $investdetail['capital'] = $v['capital'];
                    $investdetail['interest'] = $v['interest'];
                    $investdetail['interest_fee'] = getFloatValue($fee_rate*$v['interest'], 4);
                    $investdetail['status'] = 0;
                    $investdetail['sort_order'] = $i;
                    $investdetail['total'] = $binfo['borrow_duration'];
                    //加息
                    if ($jx_total > 0) {
                        $investdetail['jiaxi_money'] =$jx_list[$key]['interest'];
                        $investdetail['jiaxi_rate'] = $jx_rate;
                    }
                    $i++;
                    $savedetail[] = $investdetail;
                }
            break;
        }
    foreach ($savedetail as $key => $val) {
        $invest_defail_id = M('investor_detail')->add($val);//保存还款详情
    }

    $last_have_money = M("borrow_info")->getFieldById($borrow_id, "has_borrow");
    $upborrowsql = "update `{$pre}borrow_info` set ";
    $upborrowsql .= "`has_borrow`=".($last_have_money+$money).",`borrow_times`=`borrow_times`+1";
    $upborrowsql .= " WHERE `id`={$borrow_id}";
    $upborrow_res = M()->execute($upborrowsql);

        //更新投标进度
    if ($invest_defail_id && $invest_info_id && $upborrow_res) {//还款概要和详情投标进度都保存成功
        $investMoney->commit();
        $newbid=borrowidlayout1($borrow_id);
        $res = memberMoneyLog($uid, 6, -$money, "对{$newbid}号标进行投标", $binfo['borrow_uid']);
        $today_reward = explode("|", $datag['today_reward']);
        if ($binfo['repayment_type']=='1') {//如果是天标，则执行1个月的续投奖励利率
            $reward_rate = floatval($today_reward[0]);
        } else {
            if ($binfo['borrow_duration']==1) {
                $reward_rate = floatval($today_reward[0]);
            } elseif ($binfo['borrow_duration']==2) {
                $reward_rate = floatval($today_reward[1]);
            } else {
                $reward_rate = floatval($today_reward[2]);
            }
        }
        ////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
        //$reward_rate = floatval($datag['today_reward']);//floatval($datag['today_reward']);//当日回款续投奖励利率
        // 续投奖励公式  投资金额 * 月数 / 1000 后台网站设置（回款投标自动奖励）
        if ($binfo['borrow_type']!=3) {//如果是秒标(borrow_type==3)，则没有续投奖励这一说
            $vd['add_time'] = array("lt",time());
            $vd['investor_uid'] = $uid;
            $borrow_invest_count = M("borrow_investor")->where($vd)->count('id');//检测是否投过标且大于一次
            if ($reward_rate>0 && $vminfo['back_money']>0 && $borrow_invest_count>0) {//首次投标不给续投奖励
                if ($money>$vminfo['back_money']) {//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
                    $reward_money_s = $vminfo['back_money'];
                } else {
                    $reward_money_s = $money;
                }

                $save_reward['borrow_id'] = $borrow_id;
                $save_reward['reward_uid'] = $uid;
                $save_reward['invest_money'] = $reward_money_s;//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
                $save_reward['reward_money'] = $reward_money_s*$reward_rate/1000;//续投奖励
                $save_reward['reward_status'] = 0;
                $save_reward['add_time'] = time();
                $save_reward['add_ip'] = get_client_ip();
                $newidxt = M("today_reward")->add($save_reward);
                if ($newidxt) {
                    $newbid=borrowidlayout1($borrow_id);
                    $result =membermoneylog($uid, 33, $save_reward['reward_money'], "续投有效金额({$reward_money_s})的奖励({$newbid}号标)预奖励", 0, "@网站管理员@");
                }
            } else {
                $result = true;
            }
        }
        /////////////////////////回款续投奖励结束 2013-05-10 fans///////////////////////////////

        if (($havemoney+$money) == $binfo['borrow_money']) {
            borrowFull($borrow_id, $binfo['borrow_type']);//满标，标记为还款中，更新相关数据
        }
        if (!$res && !$result) {//没有正常记录和扣除帐户余额的话手动回滚
            M('investor_detail')->where("invest_id={$invest_info_id}")->delete();
            M('borrow_investor')->where("id={$invest_info_id}")->delete();
            //更新投标进度
            $upborrowsql = "update `{$pre}borrow_info` set ";
            $upborrowsql .= "`has_borrow`=".$havemoney.",`borrow_times`=`borrow_times`-1";
            $upborrowsql .= " WHERE `id`={$borrow_id}";
            $upborrow_res = M()->execute($upborrowsql);
            //更新投标进度
            $done = false;
        } else {
            $done = true;
        }
    } else {
        $investMoney->rollback();
    }
    return $done;
}


/**
 * @param $borrow_id
 * @param int $btype
 */
function borrowFull($borrow_id, $btype = 0)
{
    if ($btype==3) {//秒还标
        borrowApproved($borrow_id);
        sleep(3);
        borrowRepayment($borrow_id, 1);
    } else {
        $saveborrow['borrow_status']=4;
        $saveborrow['full_time']=time();
        $upborrow_res = M("borrow_info")->where("id={$borrow_id}")->save($saveborrow);
        if ($upborrow_res) {
            $newbid=borrowidlayout1($borrow_id);
            $caiwu_phone = C('NOTICE_TEL.caiwu');
            $content = '第'.$newbid.'号标已满标，您可登录平台查询详情并审核。';
            sendsms($caiwu_phone, $content);
        }
    }
}

//流标处理
function borrowRefuse($borrow_id, $type)
{//$type=2 代表流标返还; $type=3代表复审未通过，返还
    $pre = C('DB_PREFIX');
    $done = false;
    $borrowInvestor = D('borrow_investor');
    $binfo = M("borrow_info")->field("id,borrow_type,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
    //$investorList = $borrowInvestor->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
    $investorList = M("borrow_investor")->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
    M('investor_detail')->where("borrow_id={$borrow_id}")->delete();//流标将删除其对应的还款记录表

    if ($binfo['borrow_type']==1) {//如果是普通标
        $limit_credit = memberLimitLog($binfo['borrow_uid'], 12, ($binfo['borrow_money']), $info="{$borrow_id}号标流标,返还借款信用额度");//返回借款额度
    }
    $borrowInvestor->startTrans();

    $bstatus = ($type==2)?3:5;//3:标未满，结束，流标   5:复审未通过，结束
    $upborrow_info = M('borrow_info')->where("id={$borrow_id}")->setField("borrow_status", $bstatus);
    //处理借款概要
    $buname = M('members')->getFieldById($binfo['borrow_uid'], 'user_name');
    //处理借款概要

    if (is_array($investorList)) {
        $upsummary_res = M('borrow_investor')->where("borrow_id={$borrow_id}")->setField("status", $type);
        $moneynewid_x_temp = true;
        $bxid_temp = true;
        foreach ($investorList as $v) {
            MTip('chk15', $v['investor_uid'], $borrow_id);//sss
            $accountMoney_investor = M("member_money")->field(true)->find($v['investor_uid']);
            $datamoney_x['uid'] = $v['investor_uid'];
            $datamoney_x['type'] = ($type==3)?16:8;
            $datamoney_x['affect_money'] = $v['investor_capital'];
            $datamoney_x['account_money'] = ($accountMoney_investor['account_money'] + $datamoney_x['affect_money']);//投标不成功返回充值资金池
            $datamoney_x['collect_money'] = $accountMoney_investor['money_collect'];
            $datamoney_x['freeze_money'] = $accountMoney_investor['money_freeze'] - $datamoney_x['affect_money'];
            $datamoney_x['back_money'] = $accountMoney_investor['back_money'];

            //会员帐户
            $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
            $mmoney_x['money_collect']=$datamoney_x['collect_money'];
            $mmoney_x['account_money']=$datamoney_x['account_money'];
            $mmoney_x['back_money']=$datamoney_x['back_money'];

            //会员帐户
            $_xstr = ($type==3)?"复审未通过":"募集期内标未满,流标";
            $datamoney_x['info'] = "第{$borrow_id}号标".$_xstr."，返回冻结资金";
            $datamoney_x['add_time'] = time();
            $datamoney_x['add_ip'] = get_client_ip();
            $datamoney_x['target_uid'] = $binfo['borrow_uid'];
            $datamoney_x['target_uname'] = $buname;
            $moneynewid_x = M('member_moneylog')->add($datamoney_x);
            if ($moneynewid_x) {
                $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
            }
            $moneynewid_x_temp = $moneynewid_x_temp && $moneynewid_x;
            $bxid_temp = $bxid_temp && $bxid;
        }
    } else {
        $moneynewid_x_temp = true;
        $bxid_temp = true;
        $upsummary_res=true;
    }

    if ($moneynewid_x_temp && $upsummary_res && $bxid_temp && $upborrow_info) {
        /////////////////////////回款续投奖励预奖励取消开始 2013-05-10 fans///////////////////////////////
        $listreward =M("today_reward")->field("reward_uid,reward_money")->where("borrow_id={$borrow_id} AND reward_status=0")->select();
        if (!empty($listreward)) {
            $newbid=borrowidlayout1($borrow_id);
            foreach ($listreward as $v) {
                membermoneylog($v['reward_uid'], 35, 0-$v['reward_money'], "续投奖励({$newbid}号标)预奖励取消", 0, "@网站管理员@");
            }
            $updata_s['deal_time'] = time();
            $updata_s['reward_status'] = 2;
            M("today_reward")->where("borrow_id={$borrow_id} AND reward_status=0")->save($updata_s);
        }
        /////////////////////////回款续投奖励预奖励取消结束 2013-05-10 fans///////////////////////////////
        $done=true;
        $borrowInvestor->commit();
    } else {
        $borrowInvestor->rollback();
    }

    return $done;
}


//借款成功，进入复审处理
function borrowApproved($borrow_id)
{
    $pre = C('DB_PREFIX');
    $done = false;
    $_P_fee = get_global_setting();
    $invest_integral = $_P_fee['invest_integral'];//投资积分
    $borrowInvestor = D('borrow_investor');
    // borrow_info 借款信息管理表
    $binfo = M("borrow_info")->field("id,borrow_type,reward_type,reward_num,borrow_fee,borrow_money,borrow_uid,borrow_duration,repayment_type,borrow_interest_rate,product_type")->find($borrow_id);
    $investorList = $borrowInvestor->field('id,borrow_id,investor_uid,investor_capital,investor_interest,reward_money')->where("borrow_id={$borrow_id}")->select();

    //$endTime = strtotime(date("Y-m-d",time())." 23:59:59");
    //借款天数、还款时间
    $endTime = strtotime(date("Y-m-d", time())." ".$_P_fee['back_time']);// 到期还款时钟，暂定为当天的23:59:59
    if ($binfo['borrow_type']==3 || $binfo['repayment_type']==1) {//天标或秒标
        $binfo['borrow_duration'] = $binfo['borrow_duration']-1;
        $deadline_last = strtotime("+{$binfo['borrow_duration']} day", $endTime);
    } else {//月标
        $deadline_last = strtotime("+{$binfo['borrow_duration']} month", $endTime);
    }
    $getIntegralDays = intval(($deadline_last-$endTime)/3600/24);//借款天数

    //////////////////////////////////

    $borrowInvestor->startTrans();
    try {  //捕获错误异常
        //更新投资概要
        $_investor_num = count($investorList);

        foreach ($investorList as $key=>$v) {
            //sleep(2);
            $_reward_money=0;
            if ($binfo['reward_type']>0) {
                $investorList[$key]['reward_money'] = getFloatValue($v['investor_capital']*$binfo['reward_num']/100, 4);
            } else {
                $investorList[$key]['reward_money'] = 0;
            }

            //MTip('chk14',$v['investor_uid'],$borrow_id);//sss
            $upsummary_res = M()->execute("update `{$pre}borrow_investor` set `deadline`={$deadline_last},`status`=4,`reward_money`='".$investorList[$key]['reward_money']."' WHERE `id`={$v['id']} ");
        }
        //更新投资概要
        //更新借款信息
        $upborrow_res = M()->execute("update `lzh_borrow_info` set `deadline`={$deadline_last},`borrow_status`=6  WHERE `id`={$borrow_id}");
        //更新借款信息
        //更新投资详细
        file_put_contents('log.txt', "还款模式{$binfo['repayment_type']}\n\r", FILE_APPEND);
        switch ($binfo['repayment_type']) {
            case 2://每月还款
                $time=D('borrow_info_additional')->get_return_day($borrow_id);
                $first_time=strtotime("+1 month", $endTime);
                if (($time!=0)&&($time>$first_time)) {
                    $diff=ceil(($time-$first_time)/24/3600);
                    $endTime=$time;//如果设置了还款时间，则按还款时间计算
                    file_put_contents('log.txt', "第一次还款日从".date("Y-m-d", $first_time)."推迟到".date("Y-m-d", $time)."额外计算".$diff."天利息\n\r", FILE_APPEND);
                } else {
                    $diff=0;
                    $endTime=strtotime("+1 month", $endTime);
                }
                file_put_contents('log.txt', "息差天数{$diff}\n\r", FILE_APPEND);

                for ($i=1;$i<=$binfo['borrow_duration'];$i++) {
                    if ($i==1) {
                        $deadline=$endTime;
                    } else {
                        $j=$i-1;
                        $deadline=strtotime("+{$j} month", $endTime);
                    }
                    $updetail_res = M()->execute("update `lzh_investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id} AND `sort_order`=$i and status!=-1");
                }
                //计算额外的时间
                if ($diff!=0) {
                    $borrow_interest_rate   = $binfo['borrow_interest_rate'];
                    $day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率
                    $where['i.sort_order']=1;
                    $where['i.borrow_id']=$borrow_id;
                    $where["status"]=array("neq",-1);
                    $field="i.id,i.interest,b.investor_capital";
                    $list=M("investor_detail i")->field($field)->join("lzh_borrow_investor b on b.id=i.invest_id ")->where($where)->select();
                   // file_put_contents("log.txt",var_export($list),FILE_APPEND);
                    foreach ($list as $key=>$val) {
                        $interest=getFloatValue($day_rate*$val['investor_capital']*$diff+$val['interest'], 2);
                        $date['i.interest']=$interest;
                        $where['i.id']=$val['id'];
                        M("investor_detail i")->where($where)->save($date);
                        file_put_contents('log.txt', "{$val['id']}修改利息，投资额为{$val['investor_capital']},增加{$diff}天，天息为{$day_rate},利息有{$val['interest']}增加到{$interest}\n\r", FILE_APPEND);
                    }
                }
                break;//月还款
            case 3://每季还本
            case 4://期未还本
                for ($i=1;$i<=$binfo['borrow_duration'];$i++) {
                    $deadline=0;
                    $deadline=strtotime("+{$i} month", $endTime);
                    $updetail_res = M()->execute("update `lzh_investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id} AND `sort_order`=$i and status!=-1");
                }
            break;
            case 1://按天一次性还款
            case 5://一次性还款
                    $deadline=0;
                    $deadline=$deadline_last;
                    $updetail_res = M()->execute("update `{$pre}investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id} and status!=-1");
            break;
            case 7: //等本降息
                $endTime=strtotime("+1 month", $endTime);
                for ($i=1;$i<=$binfo['borrow_duration'];$i++) {
                    if ($i==1) {
                        $deadline=$endTime;
                    } else {
                        $j=$i-1;
                        $deadline=strtotime("+{$j} month", $endTime);
                    }
                    $updetail_res = M()->execute("update `lzh_investor_detail` set `deadline`={$deadline},`status`=7 WHERE `borrow_id`={$borrow_id} AND `sort_order`=$i and status!=-1");
                }
                break;
        }

        if ($updetail_res && $upsummary_res && $upborrow_res) {
            $done=true;
            $borrowInvestor->commit();
        } else {
            $done=false;
            $borrowInvestor->rollback();
        }
    } catch (Exception $e) {
        $done=false;
        $borrowInvestor->rollback();
    }


    //更新投资详细

    // 当以上操作没有异常正确执行后执行下面的工作
    if ($done) {

        // 201711 月活动推荐首投返现
        huodong201711FirstInvestRelease($binfo['id']);

        $newbid=borrowidlayout1($borrow_id);
        //借款者帐户
        if ($binfo['product_type'] != 5) {
            $_P_fee=get_global_setting();
            $_borraccount = memberMoneyLog($binfo['borrow_uid'], 17, $binfo['borrow_money'], "第{$newbid}号标复审通过，借款金额入帐");//借款入帐
            if (!$_borraccount) {
                return false;
            }//借款者帐户处理出错
                $_borrfee = memberMoneyLog($binfo['borrow_uid'], 18, -$binfo['borrow_fee'], "第{$newbid}号标借款成功，扣除借款管理费");//借款
            if (!$_borrfee) {
                return false;
            }//借款者帐户处理出错
                $_freezefee = memberMoneyLog($binfo['borrow_uid'], 19, -$binfo['borrow_money']*$_P_fee['money_deposit']/100, "第{$newbid}号标借款成功，冻结{$_P_fee['money_deposit']}%的保证金");//冻结保证金

            if (!$_freezefee) {
                return false;
            }//借款者帐户处理出错
            //借款者帐户
        }

        //投资者帐户

        $_investor_num = count($investorList);
        $_remoney_do = true;
        foreach ($investorList as $v) {
            //sleep(2);
            //////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////

            $integ = intval($v['investor_capital']*$getIntegralDays*$invest_integral/1000);
            //$reintegral = memberIntegralLog($v['investor_uid'],2,$integ,"第{$borrow_id}号标复审通过，应获积分");
            $reintegral = memberIntegralLog($v['investor_uid'], 2, $integ, "第{$newbid}号标复审通过，应获积分：".$integ."分,投资金额：".$v['investor_capital']."元,投资天数：".$getIntegralDays."天");
            if (isBirth($v['investor_uid'])) {
                $reintegral = memberIntegralLog($v['investor_uid'], 2, $integ, "亲，祝您生日快乐，本站特赠送您{$integ}积分作为礼物，以表祝福。");
            }
            //////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////

            //////////////////////邀请奖励开始////////////////////////////////////////
            $vd['add_time'] = array("lt",time());
            $vd['investor_uid'] = $v['investor_uid'];
            $borrow_invest_count = M("borrow_investor")->where($vd)->count('id');//检测是否投过标且大于一次
            $_rate = 10;//$_P_fee['award_invest']/1000;//推广奖励
            $jiangli = getFloatValue($_rate, 2);//, $v['investor_capital'],2);
            $vo = M("members")->where('id='.$v['investor_uid'])->find();
            if ($vo['recommend_id']!=0) {
                if ($borrow_invest_count == 1) {

                    //memberMoneyLog($vo['recommend_id'],13,$jiangli,$vo['user_name']."对{$borrow_id}号标投资成功，你获得推广奖励".$jiangli."元。",$v['investor_uid']);
                }
            }
            /////////////////////邀请奖励结束/////////////////////////////////////////

            //////////////////////////处理待收金额为负的问题/////////////////////
            $wmap['investor_uid'] = $v['investor_uid'];
            $wmap['borrow_id'] = $v['borrow_id'];


            $daishou = M('investor_detail')->field('interest')->where("investor_uid = {$v['investor_uid']} and borrow_id = {$v['borrow_id']} and invest_id ={$v['id']} and status!=-1")->sum('interest');//待收金额
            //dump($daishou);die;
            //////////////////////////处理待收金额为负的问题/////////////////////
            //投标奖励
            if ($v['reward_money']>0) {
                $_remoney_do = false;
                $_reward_m = memberMoneyLog($v['investor_uid'], 20, $v['reward_money'], "第{$newbid}号标复审通过，获取投标奖励", $binfo['borrow_uid']);
                $_reward_m_give = memberMoneyLog($binfo['borrow_uid'], 21, -$v['reward_money'], "第{$newbid}号标复审通过，支付投标奖励", $v['investor_uid']);
                if ($_reward_m && $_reward_m_give) {
                    $_remoney_do = true;
                }
            }
            //投标奖励
            $remcollect = memberMoneyLog($v['investor_uid'], 15, $v['investor_capital'], "第{$newbid}号标复审通过，冻结本金成为待收金额", $binfo['borrow_uid']);
            //$reinterestcollect = memberMoneyLog($v['investor_uid'],28,$v['investor_interest'],"第{$borrow_id}号标复审通过，应收利息成为待收金额",$binfo['borrow_uid']);

            //待收利息$daishou
            $reinterestcollect = memberMoneyLog($v['investor_uid'], 28, $daishou, "第{$newbid}号标复审通过，投标预期收益", $binfo['borrow_uid']);
        }
        if (!$_remoney_do||!$remcollect||!$reinterestcollect) {
            return false;
        }//投资者帐户处理出错
        /////////////////////////回款续投奖励预奖励取消开始 2013-05-10 fans///////////////////////////////
        $listreward =M("today_reward")->field("reward_uid,reward_money")->where("borrow_id={$borrow_id} AND reward_status=0")->select();
        if (!empty($listreward)) {
            foreach ($listreward as $v) {
                //sleep(2);
                membermoneylog($v['reward_uid'], 34, $v['reward_money'], "续投奖励({$newbid}号标)预奖励到账", 0, "@网站管理员@");
            }
            $updata_s['deal_time'] = time();
            $updata_s['reward_status'] = 1;
            M("today_reward")->where("borrow_id={$borrow_id} AND reward_status=0")->save($updata_s);
        }
        /////////////////////////回款续投奖励预奖励取消结束 2013-05-10 fans///////////////////////////////
    }

    return $done;
}


/**
 * 201711 活动复审返现
 * @return [type] [description]
 */
function huodong201711FirstInvestRelease($bId){
    $model = new Huodong201711CountModel();
    $unreleasedList = $model->get201711UnreleaseList($bId);

    foreach ($unreleasedList as $key => $value) {
        $rebate = returnMoney201711($value['first_invest']);
        releaseCommision($value['parent_id'], $rebate, "201711月活动首投返现");
        
        $save['is_released'] = 1;
        $where['id'] = $value['id'];
        $result = M('huodong_201711_count')->where($where)->save($save);
        $parentinfo = M('members')->find($value['parent_id']);
        $userinfo = M('members')->find($value['uid']);
        if (false !== $result){
            sms201711First($parentinfo['user_name'], $userinfo['user_name'], returnMoney201711($value['first_invest']));
        }

        $dreamLogModel = new DreamLogModel();
        $dreamLogModel->huodong201711FirstInvestReleaseLog($value['uid'], $value['bid'], $value['first_invest']);
    

    }
}

function returnMoney201711($investMoney)
{
    if(5000 <= $investMoney && $investMoney < 10000)
    {
        return 30;
    } else if (10000 <= $investMoney && $investMoney < 20000) {
        return 60;
    } else if (20000 <= $investMoney && $investMoney < 50000) {
        return 200;
    } else if (50000 <= $investMoney && $investMoney < 100000) {
        return 500;
    } else if (100000 <= $investMoney) {
        return 1200;
    } else {
        return 0;
    }
}


function lastRepayment($binfo)
{
    $x=true;//因为下面有!x的判断，所以为了避免影响其他标，这里默认为true
    $newbid=borrowidlayout1($binfo['id']);
    if ($binfo['borrow_type']==2) {
        $x=false;
        //返回借款人的借款担保额度
        $x = memberLimitLog($binfo['borrow_uid'], 8, ($binfo['borrow_money']), $info="{$newbid}号标还款完成");
        if (!$x) {
            return false;
        }
        //返回投资人的投资担保额度
        $vocuhlist = M('borrow_vouch')->field("uid,vouch_money")->where("borrow_id={$binfo['id']}")->select();
        foreach ($vocuhlist as $vv) {
            $x = memberLimitLog($vv['uid'], 10, ($vv['vouch_money']), $info="您担保的{$newbid}号标还款完成");
        }
    } elseif ($binfo['borrow_type']==1) {
        $x=false;
        $x = memberLimitLog($binfo['borrow_uid'], 7, ($binfo['borrow_money']), $info="{$binfo['id']}号标还款完成");
    }
    //如果是担保

    if (!$x) {
        return false;
    }

    $debt_count = M("debt_borrow_info")->where("old_borrow_id = {$binfo['id']} and borrow_status = 6")->count();
    if ($debt_count > 0) {
        M("debt_borrow_info")->where("old_borrow_id = {$binfo['id']} and borrow_status = 6")->save(array("borrow_status"=>7));
    }


    //解冻保证金
    $_P_fee=get_global_setting();
    $accountMoney_borrower = M('member_money')->field('account_money,money_collect,money_freeze,back_money')->find($binfo['borrow_uid']);
    $datamoney_x['uid'] = $binfo['borrow_uid'];
    $datamoney_x['type'] = 24;
    $datamoney_x['affect_money'] = ($binfo['borrow_money']*$_P_fee['money_deposit']/100);
    $datamoney_x['account_money'] = ($accountMoney_borrower['account_money'] + $datamoney_x['affect_money']);
    $datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
    $datamoney_x['freeze_money'] = ($accountMoney_borrower['money_freeze']-$datamoney_x['affect_money']);
    $datamoney_x['back_money'] = $accountMoney_borrower['back_money'];

    //会员帐户
    $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
    $mmoney_x['money_collect']=$datamoney_x['collect_money'];
    $mmoney_x['account_money']=$datamoney_x['account_money'];
    $mmoney_x['back_money']=$datamoney_x['back_money'];

    //会员帐户
    $datamoney_x['info'] = "网站对{$binfo['id']}号标还款完成的解冻保证金";
    $datamoney_x['add_time'] = time();
    $datamoney_x['add_ip'] = get_client_ip();
    $datamoney_x['target_uid'] = 0;
    $datamoney_x['target_uname'] = '@网站管理员@';
    $moneynewid_x = M('member_moneylog')->add($datamoney_x);
    if ($moneynewid_x) {
        $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
    }
    //解冻保证金

    if ($bxid && $x) {
        return true;
    } else {
        return false;
    }
}

// 计算提前还款利息
// function borrowInterest($borrow_id){
// 	$pre = C('DB_PREFIX');
// 	$done = false;
// 	$binfo = M("borrow_info")->field("id,borrow_uid,add_time,borrow_interest_rate,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline")->find($borrow_id);
// 	//企业直投与普通标,判断还款期数不一样
// 	//借款天数、还款时间
// 	//利息计算公式 借款总金额*(借款利率/36000)*借款天数
// 	$borrow_money           = intval($binfo['borrow_money']); //借款总额
// 	$borrow_interest_rate   = intval($binfo['borrow_interest_rate']); //借款利率
// 	$day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率

// 	$currentTime            = strtotime(date('Y-m-d').'+1 day'); //当前需还款时间
// 	$issueTime              = strtotime(date('Y-m-d',$binfo['add_time']));//发标时间
// 	$BorrowingDays          = intval(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
// 	return getFloatValue($borrow_money*$day_rate*$BorrowingDays, 2);
// }

function getKey_arr($array)
{
    foreach ($array as $v) {
        $key = array_keys($v);
        foreach ($key as $kv) {
            $new[$kv][] = $v[$kv];
        }
    }
    return $new;
}

// 自由分期还款
// 参数说明 标ID  第一期号  还款金额
function stageborrowRepayment($bid, $sort_order, $money)
{
    $pre = C('DB_PREFIX');
    $done = false;

    // 第一次还款
    // 利息计算天数 = 当前日期 - 发标日期
    // 利息 = 利率/360 * 还款金额 * 利息计算天数
    // 借款人账户 = 减去借款总额 + 利息

    // 第二次还款
    // 利息计算天数 = 当前日期 - 发标日期
    // 利息 = 利率/360 * 还款金额 * 利息计算天数

    // ajaxmsg(); // 提示还款成功
    die;
}

/**
 * 普通标还款
 * @param $borrow_id
 * @param $sort_order
 * @param int $type
 * @param int $repayment_id
 * @return bool
 */
function borrowRepayment($borrow_id, $sort_order, $type=1, $repayment_id=0)
{//type 1:会员自己还,2网站代还
     $newbid = borrowidlayout1($borrow_id);
    $pre = C('DB_PREFIX');
    $done = false;
    $borrowDetail = D('investor_detail');
    $binfo = M("borrow_info")->field("id,borrow_uid,borrow_duration_txt,n_interest,colligate_fee,n_colligate_fee,product_type,add_time,second_verify_time,borrow_interest_rate,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline,jiaxi_rate")->find($borrow_id);
    $b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);

    if ($binfo['has_pay']>=$sort_order) {
        return "本期已还过，不用再还";
    }
    if ($binfo['has_pay'] == $binfo['total']) {
        return "此标已经还完，不用再还";
    }
    if (($binfo['has_pay']+1)<$sort_order) {
        return "对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期";
    }

    $voxe = $borrowDetail->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')->where("borrow_id={$borrow_id} and status!=-1 and is_debt = 0")->group('sort_order')->select();
    foreach ($voxe as $ee=>$ss) {
        if ($ss['sort_order']==$sort_order) {
            $vo = $ss;
        }
    }

    // 复审通过后开始计算借款人利息 获取复审时间
    //$atime = M('borrow_investor')->field("add_time")->where("borrow_id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
    $atime = $binfo['second_verify_time'];
    //企业直投与普通标,判断还款期数不一样
    //借款天数、还款时间
    //利息计算公式 借款总金额*(借款利率/36000)*借款天数
    $borrow_money           = intval($binfo['borrow_money']); //借款总额
    $borrow_interest_rate   = $binfo['borrow_interest_rate']; //借款利率 此处因为利率转成了整数 20% 转成 2
    $day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率



    $issue_m   = M('borrow_info')->where('id='.$borrow_id)->find();
    $deadl  = date('Y-m-d H:i:s', cal_deadline($borrow_id));

    $dq_day    = date('Y-m-d H:i:s');

    // 提前还款 当前还时间小于最后还款时间23:59:59
    if ($binfo['repayment_type'] == 1) {//天标才需要重新计算这个部分
        //计算还款天数，如果不足70%天，需要按70%算利息
        /***********************************************/
        /*
            $duration=$binfo['borrow_duration'];
            $limit_borrow_day=ceil($duration*0.7);
            if($BorrowingDays<$limit_borrow_day)
            $BorrowingDays=$limit_borrow_day;*/




        /**********************************************/
        // 更新利息 M('investor_detail')
        $investor_uid = M('investor_detail')->where('borrow_id='.$borrow_id." and status!=-1 and is_debt = 0")->select();


        $vo['interest'] = 0;
        $Detail = M("investor_detail");
        // 提单质押标1 现货质押标3
        if ($binfo['product_type'] == 1 || $binfo['product_type'] == 3||$binfo['product_type'] == 6||$binfo['product_type'] == 7||$binfo['product_type'] == 8||$binfo['product_type'] == 10) {
            $currentTime            = strtotime(date('Y-m-d')); //当前需还款时间
            $issueTime              = strtotime(date('Y-m-d', $atime));//复审后的时间
            $binfo['deadline']=cal_deadline($borrow_id);//修正bug.
            if (strtotime(date('Y-m-d', $binfo['deadline'])) == $currentTime && $borrow_id <= 325) {
                $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
            } elseif (strtotime(date('Y-m-d', $binfo['deadline']))>$currentTime) {
                $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
            } else {
                $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
            }
            if ($BorrowingDays == 0) {
                $BorrowingDays = $BorrowingDays +1;
            }
            Log::write("还款天数为".$BorrowingDays);
            // 综合服务费 利率/36000 * 借款金额 * 天数
            $colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
            foreach ($investor_uid as $iteme) {
                $tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
                //总加息  加息券和标加息
                $jx_total = $iteme['jiaxi_rate']+$binfo['jiaxi_rate'];
                if ($jx_total>0) {
                    $jx_interest = getFloatValue($iteme['capital']*($jx_total/36000)*$BorrowingDays, 2);//加息重新计算
                }
                $vo['interest'] += $tou_interest;
                unset($iteme['id']);
                if ($jx_total>0) {
                    //标加息
                    $Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest},`jiaxi_money`={$jx_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id} and status!=-1 and is_debt = 0");
                } else {
                    //没有加息
                    $Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id} and status!=-1 and is_debt = 0");
                }
            }
        }
        // 提单转现货质押标2
        if ($binfo['product_type'] == 2) {
            // 投资人额度/标的总额*旧利息
            $vo['interest'] = 0;
            $xhtime = M('borrow_info')->field("add_time")->where("id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
            $currentTime            = strtotime(date('Y-m-d')); //当前时间
            $issueTime              = strtotime(date('Y-m-d', $xhtime['add_time']));//转现货时间
            $binfo['deadline']=cal_deadline($borrow_id);//修正bug.
            if (strtotime(date('Y-m-d', $binfo['deadline'])) == $currentTime && $borrow_id <= 325) {
                $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
            } elseif (strtotime(date('Y-m-d', $binfo['deadline']))>$currentTime) {
                $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
            } else {
                $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
            }
            if ($BorrowingDays == 0) {
                $BorrowingDays = $BorrowingDays +1;
            }
            Log::write("还款天数为".$BorrowingDays);
            //计算还款天数，如果不足70%天，需要按70%算利息
            /***********************************************/
            /*
            $duration=$binfo['borrow_duration'];
            $limit_borrow_day=ceil($duration*0.7);
            if($BorrowingDays<$limit_borrow_day)
                $BorrowingDays=$limit_borrow_day;*/


            // 综合服务费 利率/36000 * 借款金额 * 天数  提单转现货的综合服务费
                $colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
            foreach ($investor_uid as $iteme) {
                $tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
                $vo['interest'] += $tou_interest;
            }
            foreach ($investor_uid as $n) {
                $d_interest = getFloatValue($n['capital']/$binfo['borrow_money']*($vo['interest']+$binfo['n_interest']), 2);
                unset($iteme['id']);
                // print_r($binfo['n_interest']."<br>");
                $Detail->execute("update `{$pre}investor_detail` set `interest`={$d_interest} WHERE `capital`={$n['capital']} and `borrow_id`={$borrow_id} and status!=-1 and is_debt = 0");
            }
            $vo['interest'] += $binfo['n_interest'];
            $colligate_fee +=$binfo['n_colligate_fee'];
        }
    }
    $pay_frist=D("borrow_info_additional")->is_pay_frist($borrow_id);//判断此标是否提前收取了综合服务费。 1表示已经收取。
    if ($pay_frist) {
        $colligate_fee=0;
    }//已经提前收取了综合服务费，这里不能再收取。


    // print_r($colligate_fee."<br>");
    // print_r($vo['interest']);
    // die;
    import("@.conf.borrow_expired");
    $expired=new borrow_expired($borrow_id, $sort_order);


    if ($expired->is_expired()) {//此标已逾期
        $is_expired = true;
        if ($vo['substitute_time']>0) {
            $is_substitute=true;
        }//已代还
        else {
            $is_substitute=false;
        }
        //逾期的相关计算
        $expired_days =$expired->get_expired_day();
        $expired_money = $expired->get_expired__money();
        $call_fee = 0;//getExpiredCallFee($expired_days,$vo['capital'],$vo['interest']);
        //逾期的相关计算
    } else {
        $is_expired = false;
        $expired_days = 0;
        $expired_money = 0;
        $call_fee = 0;
    }

    //企业直投与普通标,判断还款期数不一样
    //MTip('chk25',$binfo['borrow_uid'],$borrow_id);//sss
     $accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
    // if($type==1 && $binfo['borrow_type']<>3 && ($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'])<($vo['capital']+$vo['interest']+$expired_money+$call_fee))
        // return "帐户可用余额不足，本期还款共需".($vo['capital']+$vo['interest']+$expired_money+$call_fee)."元，请先充值";

    if ($is_substitute && $is_expired) {//已代还后的会员还款，则只需要对会员的帐户进行操作后然后更新还款时间即可返回
        $borrowDetail->startTrans();
        $datamoney_x['uid'] = $binfo['borrow_uid'];
        $datamoney_x['type'] = 11;
        $datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
        if (($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0) {//如果需要还款的金额大于回款资金池资金总额
                $datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
            $datamoney_x['back_money'] = 0;
        } else {
            $datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
            $datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
        }
        $datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
        $datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

            //会员帐户
            $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
        $mmoney_x['money_collect']=$datamoney_x['collect_money'];
        $mmoney_x['account_money']=$datamoney_x['account_money'];
        $mmoney_x['back_money']=$datamoney_x['back_money'];
            //会员帐户
            $datamoney_x['info'] = "对{$newbid}号标第{$sort_order}期还款，扣除综合服务费{$colligate_fee}元";
        $datamoney_x['add_time'] = time();
        $datamoney_x['add_ip'] = get_client_ip();
        $datamoney_x['target_uid'] = 0;
        $datamoney_x['target_uname'] = '@网站管理员@';

        $moneynewid_x = M('member_moneylog')->add($datamoney_x);
        if ($moneynewid_x) {
            $bxid_1 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
        }
        //逾期了
            //逾期罚息
            $accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
        $datamoney_x = array();
        $mmoney_x=array();

        $datamoney_x['uid'] = $binfo['borrow_uid'];
        $datamoney_x['type'] = 30;
        $datamoney_x['affect_money'] = -($expired_money);
        if (($datamoney_x['affect_money']+$accountMoney['back_money'])<0) {//如果需要还款的逾期罚息金额大于回款资金池资金总额
                $datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
            $datamoney_x['back_money'] = 0;
        } else {
            $datamoney_x['account_money'] = $accountMoney['account_money'];
            $datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
        }
        $datamoney_x['collect_money'] = $accountMoney['money_collect'];
        $datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

            //会员帐户
            $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
        $mmoney_x['money_collect']=$datamoney_x['collect_money'];
        $mmoney_x['account_money']=$datamoney_x['account_money'];
        $mmoney_x['back_money']=$datamoney_x['back_money'];
            //会员帐户
            $datamoney_x['info'] = "{$newbid}号标第{$sort_order}期的逾期罚息";
        $datamoney_x['add_time'] = time();
        $datamoney_x['add_ip'] = get_client_ip();
        $datamoney_x['target_uid'] = 0;
        $datamoney_x['target_uname'] = '@网站管理员@';

        $moneynewid_x = M('member_moneylog')->add($datamoney_x);
        if ($moneynewid_x) {
            $bxid_2 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
        }

            //催收费
            $accountMoney_2 = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
        $datamoney_x = array();
        $mmoney_x=array();

        $datamoney_x['uid'] = $binfo['borrow_uid'];
        $datamoney_x['type'] = 31;
        $datamoney_x['affect_money'] = -($call_fee);
        if (($datamoney_x['affect_money']+$accountMoney_2['back_money'])<0) {//如果需要还款的催收费金额大于回款资金池资金总额
                $datamoney_x['account_money'] = $accountMoney_2['account_money']+$accountMoney_2['back_money'] + $datamoney_x['affect_money'];
            $datamoney_x['back_money'] = 0;
        } else {
            $datamoney_x['account_money'] = $accountMoney_2['account_money'];
            $datamoney_x['back_money'] = $accountMoney_2['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
        }
        $datamoney_x['collect_money'] = $accountMoney_2['money_collect'];
        $datamoney_x['freeze_money'] = $accountMoney_2['money_freeze'];

            //会员帐户
            $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
        $mmoney_x['money_collect']=$datamoney_x['collect_money'];
        $mmoney_x['account_money']=$datamoney_x['account_money'];
        $mmoney_x['back_money']=$datamoney_x['back_money'];
            //会员帐户
            $datamoney_x['info'] = "网站对借款人收取的第{$newbid}号标第{$sort_order}期的逾期催收费";
        $datamoney_x['add_time'] = time();
        $datamoney_x['add_ip'] = get_client_ip();
        $datamoney_x['target_uid'] = 0;
        $datamoney_x['target_uname'] = '@网站管理员@';

        $moneynewid_x = M('member_moneylog')->add($datamoney_x);
        if ($moneynewid_x) {
            $bxid_3 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
        }

        //逾期了
            $updetail_res = M()->execute("update `{$pre}investor_detail` set `repayment_time`=".time().",`status`=5 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1");
            //更新借款信息
            $upborrowsql = "update `{$pre}borrow_info` set ";
        $upborrowsql .= ",`substitute_money`=0";
        $upborrowsql .= ",`borrow_status`=10";//会员在网站代还后，逾期还款
            $upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
        $upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
        if ($sort_order == $binfo['total']) {
            $upborrowsql .= ",`borrow_status`=7";
        }
        $upborrowsql .= ",`has_pay`={$sort_order}";
        if ($is_expired) {
            $upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";
        }
        $upborrowsql .= " WHERE `id`={$borrow_id}";

        $upborrow_res = M()->execute($upborrowsql);
            //更新借款信息

        if ($updetail_res&&$bxid_1&&$bxid_2&&$bxid_3&&$upborrow_res) {
            //if($updetail_res&&$upborrow_res){
            $borrowDetail->commit() ;
            //撤销转让的债权 ,完成还款更改债权转让状态
            cancelDebt($borrow_id);
            return true;
        } else {
            $borrowDetail->rollback() ;
            return false;
        }
    }

    //企业直投与普通标,判断还款期数不一样
      $detailList = $borrowDetail->field('invest_id,investor_uid,capital,interest,interest_fee,borrow_id,total,jiaxi_money,debt_borrow_id')->where("borrow_id={$borrow_id} AND sort_order={$sort_order} and status!=-1 and is_debt = 0")->select();
    //企业直投与普通标,判断还款期数不一样

    /*************************************逾期还款积分与还款状态处理开始 20130509 fans***********************************/
    $datag = get_global_setting();
    $credit_borrow = explode("|", $datag['credit_borrow']);

    //会员自已还款执行从这里开始
    if ($type==1) {//客户自己还款才需要记录这些操作
        $day_span = ceil(($vo['deadline']-time())/(3600*24));
        // $credits_money = intval($vo['capital']/$credit_borrow[4]);
        // $credits_info = "对第{$newbid}号标的还款操作,获取投资积分";
        if ($day_span>=0 && $day_span<1) {//正常还款
            //$credits_result = memberCreditsLog($binfo['borrow_uid'],3,$credits_money*$credit_borrow[0],$credits_info);
            // $credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital']/1000),"对第{$newbid}号标进行了正常的还款操作,获取投资积分");//还款积分处理
            $idetail_status=1;
        } elseif ($day_span>=-3 && $day_span<0) {//迟还
            // $credits_result = memberCreditsLog($binfo['borrow_uid'],4,$credits_money*$credit_borrow[1],"对第{$newbid}号标的还款操作(迟到还款),扣除信用积分");
            $idetail_status=5;//3
        } elseif ($day_span<-3) {//逾期还款
            // $credits_result = memberCreditsLog($binfo['borrow_uid'],5,$credits_money*$credit_borrow[2],"对第{$newbid}号标的还款操作(逾期还款),扣除信用积分");
            $idetail_status=5;
        } elseif ($day_span>=1) {//提前还款
            //$credits_result = memberCreditsLog($binfo['borrow_uid'],6,$credits_money*$credit_borrow[3],$credits_info);
            //提前还款按天计算的利息
            //投标总金额 $borrow_money
            // $credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital'] * $day_span/1000),"对第{$newbid}号标进行了提前还款操作,获取投资积分");//还款积分处理
            $idetail_status=2;
        }
        // if(!$credits_result) return "因积分记录失败，未完成还款操作";
    }
    /*************************************逾期还款积分与还款状态处理结束 20130509 fans***********************************/

    $borrowDetail->startTrans();
    //对借款者帐户进行减少
    //$vo['interest']提前还款重新按天计算的利息 $colligate_fee综合服务费

    $bxid = true;
    if ($type==1) {
        $bxid = false;
        $datamoney_x['uid'] = $binfo['borrow_uid'];
        $datamoney_x['type'] = 11;
            // 本金$vo['capital'] + 利息$vo['interest'] + 综合服务费
            $datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
            // print_r($datamoney_x['affect_money']);die;
            if (($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0) {//如果需要还款的金额大于回款资金池资金总额
                $datamoney_x['account_money'] = floatval($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money']);
                $datamoney_x['back_money'] = 0;
            } else {
                $datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
                $datamoney_x['back_money'] = floatval($accountMoney_borrower['back_money']) + $datamoney_x['affect_money'];//回款资金注入回款资金池
            }
        $datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
        $datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

            //会员帐户
            $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
        $mmoney_x['money_collect']=$datamoney_x['collect_money'];
        $mmoney_x['account_money']=$datamoney_x['account_money'];
        $mmoney_x['back_money']=$datamoney_x['back_money'];

            //会员帐户
            $datamoney_x['info'] = "对{$newbid}号标第{$sort_order}期还款，扣除综合服务费{$colligate_fee}元";
        $datamoney_x['add_time'] = time();
        $datamoney_x['add_ip'] = get_client_ip();
        $datamoney_x['target_uid'] = 0;
        $datamoney_x['target_uname'] = '@-网站管理员-@';
            //新浪 代收接口（对借款人余额减少并收取综合服务费）
            if ($colligate_fee!=0) {
                $sina['uid'] = $datamoney_x['uid']; //借款人ID
                //$sina['money'] = $vo['capital']+$vo['interest']+$colligate_fee; //还款总金额
                $sina['colligate_fee'] = $colligate_fee;//综合服务费
                $sina['bid'] = $borrow_id;//综合服务费
                //sinacollecttrade($sina); //代收借款人还款金额
                //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起收取综合服务费",1);
                $rs = sinapaytrade($colligate_fee);//代付综合服务费
                if ($rs["code"]=="APPLY_SUCCESS") {
                    $data['uid'] = $datamoney_x['uid'];
                    $data['borrow_id'] = $borrow_id;
                    $data['type'] = 10;
                    $data['order_no'] = $rs["order_no"];
                    $data['money'] = $money;
                    $data['addtime'] = $colligate_fee;
                    $data['sort_order'] = null;
                    $data["status"] = 2;
                    M('sinalog')->add($data);
                }
            }
            //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成收取综合服务费",2);
            //开始写还款数据
            $moneynewid_x = M('member_moneylog')->add($datamoney_x);
        if ($moneynewid_x) {
            $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
        }

        //逾期了
        if ($is_expired) {
            //逾期罚息
            if ($expired_money>0) {
                $accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
                $datamoney_x = array();
                $mmoney_x=array();

                $datamoney_x['uid'] = $binfo['borrow_uid'];
                $datamoney_x['type'] = 30;
                $datamoney_x['affect_money'] = -($expired_money);
                if (($datamoney_x['affect_money']+$accountMoney['back_money'])<0) {//如果需要还款的逾期罚息金额大于回款资金池资金总额
                    $datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
                    $datamoney_x['back_money'] = 0;
                } else {
                    $datamoney_x['account_money'] = $accountMoney['account_money'];
                    $datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
                }
                $datamoney_x['collect_money'] = $accountMoney['money_collect'];
                $datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

                //会员帐户
                $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
                $mmoney_x['money_collect']=$datamoney_x['collect_money'];
                $mmoney_x['account_money']=$datamoney_x['account_money'];
                $mmoney_x['back_money']=$datamoney_x['back_money'];

                //会员帐户
                $datamoney_x['info'] = "{$newbid}号标第{$sort_order}期的逾期罚息";
                $datamoney_x['add_time'] = time();
                $datamoney_x['add_ip'] = get_client_ip();
                $datamoney_x['target_uid'] = 0;
                $datamoney_x['target_uname'] = '@网站管理员@';
                $moneynewid_x = M('member_moneylog')->add($datamoney_x);
                if ($moneynewid_x) {
                    $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
                }
                $expired->sent_expired_money(1, $b_member['user_name']);
            }

            //催收费
            if ($call_fee>0) {
                $accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
                $datamoney_x = array();
                $mmoney_x=array();

                $datamoney_x['uid'] = $binfo['borrow_uid'];
                $datamoney_x['type'] = 31;
                $datamoney_x['affect_money'] = -($call_fee);
                if (($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0) {//如果需要还款的催收费金额大于回款资金池资金总额
                    $datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
                    $datamoney_x['back_money'] = 0;
                } else {
                    $datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
                    $datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
                }
                $datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
                $datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

                //会员帐户
                $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
                $mmoney_x['money_collect']=$datamoney_x['collect_money'];
                $mmoney_x['account_money']=$datamoney_x['account_money'];
                $mmoney_x['back_money']=$datamoney_x['back_money'];

                //会员帐户
                $datamoney_x['info'] = "网站对借款人收取的第{$newbid}号标第{$sort_order}期的逾期催收费";
                $datamoney_x['add_time'] = time();
                $datamoney_x['add_ip'] = get_client_ip();
                $datamoney_x['target_uid'] = 0;
                $datamoney_x['target_uname'] = '@网站管理员@';
                $moneynewid_x = M('member_moneylog')->add($datamoney_x);
                if ($moneynewid_x) {
                    $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
                }
            }
        }
        //逾期了
    } elseif ($type==2) {
        $bxid = false;
        //获取超级管理员ID,只有超级管理员才有资格还款
        //判断还款ID;

        $add_function=C("ADD_FUNCTION");
        $tesu_id_name=$add_function['repayment']['account'];
        $wood_id_name=$add_function['repayment']['account1'];
        $where['user_name'] = "user_name = ".$tesu_id_name."OR".$wood_id_name;
        $list=M('members')->where($where)->select();
        $tesu=$list[0]['id'];
        if ($repayment_id==$tesu) {
            $repayment_name="特速实业";
        } else {
            $repayment_name=D("Members_company")->get_danbao_name($repayment_id);
        }


        $tesu_accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($repayment_id);
        $datamoney_x['uid'] = $repayment_id;
        $datamoney_x['type'] = 10; //网站代替
        // 本金$vo['capital'] + 利息$vo['interest'] + 综合服务费
        $datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
        // print_r($datamoney_x['affect_money']);die;
        if (($datamoney_x['affect_money']+$tesu_accountMoney_borrower['back_money'])<0) {//如果需要还款的金额大于回款资金池资金总额
            $datamoney_x['account_money'] = floatval($tesu_accountMoney_borrower['account_money']+$tesu_accountMoney_borrower['back_money'] + $datamoney_x['affect_money']);
            $datamoney_x['back_money'] = 0;
        } else {
            $datamoney_x['account_money'] = $tesu_accountMoney_borrower['account_money'];
            $datamoney_x['back_money'] = floatval($tesu_accountMoney_borrower['back_money']) + $datamoney_x['affect_money'];//回款资金注入回款资金池
        }
        $datamoney_x['collect_money'] = $tesu_accountMoney_borrower['money_collect'];
        $datamoney_x['freeze_money'] = $tesu_accountMoney_borrower['money_freeze'];

        //会员帐户
        $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
        $mmoney_x['money_collect']=$datamoney_x['collect_money'];
        $mmoney_x['account_money']=$datamoney_x['account_money'];
        $mmoney_x['back_money']=$datamoney_x['back_money'];

        //会员帐户
        $datamoney_x['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期还款，扣除本金{$vo['capital']},利息{$vo['interest']},综合服务费{$colligate_fee}元,";
        $datamoney_x['add_time'] = time();
        $datamoney_x['add_ip'] = get_client_ip();
        $datamoney_x['target_uid'] = 0;
        $datamoney_x['target_uname'] = '@-网站管理员-@';
        //新浪 代收接口（对借款人余额减少并收取综合服务费）
        if ($colligate_fee!=0) {
            $sina['uid'] = $datamoney_x['uid']; //借款人ID
            //$sina['money'] = $vo['capital']+$vo['interest']+$colligate_fee; //还款总金额
            $sina['colligate_fee'] = $colligate_fee;//综合服务费
            $sina['bid'] = $borrow_id;//综合服务费
            //sinacollecttrade($sina); //代收借款人还款金额
            //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起收取综合服务费",1);
            $rs = sinapaytrade($colligate_fee);//代付综合服务费
            if ($rs["code"]=="APPLY_SUCCESS") {
                $data['uid'] = $datamoney_x['uid'];
                $data['borrow_id'] = $borrow_id;
                $data['type'] = 10;
                $data['order_no'] = $rs["order_no"];
                $data['money'] = $money;
                $data['addtime'] = $colligate_fee;
                $data['sort_order'] = null;
                $data["status"] = 2;
                M('sinalog')->add($data);
            }
        }

        //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成收取综合服务费",2);
        //开始写还款数据
        $record_info="{$repayment_name}对{$newbid}号标第{$sort_order}期还款,本金为{$vo['capital']},利息为{$vo['interest']},综合服务费为{$colligate_fee}\n\r";
        file_put_contents("log.txt", $record_info, FILE_APPEND);
        $moneynewid_x = M('member_moneylog')->add($datamoney_x);
        if ($moneynewid_x) {
            $bxid = M('member_money')->where("uid={$repayment_id}")->save($mmoney_x);
        }

        //写代付的moneylog
        $borrower['uid'] = $binfo['borrow_uid'];
        $borrower['type']=10;
        $borrower['affect_money']= $datamoney_x['affect_money'];
        $borrower['account_money']=0;
        $borrower['back_money']=0;
        $borrower['collect_money']=0;
        $borrower['freeze_money']=0;
        $borrower['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期还款，扣除本金{$vo['capital']},利息{$vo['interest']},综合服务费{$colligate_fee}元,";
        $borrower['add_time'] = time();
        $borrower['add_ip'] = get_client_ip();
        $borrower['target_uid'] = 0;
        $borrower['target_uname'] = '@-网站管理员-@';
        M('member_moneylog')->add($borrower);
        //逾期了
        if ($is_expired) {//代还款，也需要罚息
            //逾期罚息
            if ($expired_money > 0) {
                $accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($repayment_id);
                $datamoney_x = array();
                $mmoney_x = array();

                $datamoney_x['uid'] = $repayment_id;
                $datamoney_x['type'] = 30;
                $datamoney_x['affect_money'] = -($expired_money);
                if (($datamoney_x['affect_money'] + $accountMoney['back_money']) < 0) {//如果需要还款的逾期罚息金额大于回款资金池资金总额
                    $datamoney_x['account_money'] = $accountMoney['account_money'] + $accountMoney['back_money'] + $datamoney_x['affect_money'];
                    $datamoney_x['back_money'] = 0;
                } else {
                    $datamoney_x['account_money'] = $accountMoney['account_money'];
                    $datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
                }
                $datamoney_x['collect_money'] = $accountMoney['money_collect'];
                $datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

                //会员帐户
                $mmoney_x['money_freeze'] = $datamoney_x['freeze_money'];
                $mmoney_x['money_collect'] = $datamoney_x['collect_money'];
                $mmoney_x['account_money'] = $datamoney_x['account_money'];
                $mmoney_x['back_money'] = $datamoney_x['back_money'];

                //会员帐户
                $datamoney_x['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期的逾期罚息{$expired_money}";
                $datamoney_x['add_time'] = time();
                $datamoney_x['add_ip'] = get_client_ip();
                $datamoney_x['target_uid'] = 0;
                $datamoney_x['target_uname'] = '@网站管理员@';
                $moneynewid_x = M('member_moneylog')->add($datamoney_x);
                if ($moneynewid_x) {
                    $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
                }
                $expired->sent_expired_money(2, $repayment_name);
            }
        }
    }
    //对借款者帐户进行减少
    //更新借款账户信息 $vo['interest'] 利息
    $upborrowsql = "update `{$pre}borrow_info` set ";
    $upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
    $upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
    //if($sort_order == $binfo['total']) $upborrowsql .= ",`borrow_status`=7";//还款完成
    $upborrowsql .= ",`has_pay`={$sort_order}";
    //如果是网站代还的，则记录代还金额
    if ($type==2) {
        $total_subs = ($vo['capital']+$vo['interest']);
        $upborrowsql .= ",`substitute_money`=`substitute_money`+ {$total_subs}";
        //$upborrowsql .= ",`has_pay`={$binfo['has_pay']}+1";//网站代还款完成
        if ($binfo['has_pay']+1 == $binfo['total']) {
            $upborrowsql .= ",`borrow_status`=9";//网站代还款完成
        }
    }
    //如果是网站代还的，则记录代还金额
    if ($type==1) {
        //$upborrowsql .= ",`has_pay`={$sort_order}";//代还则不记录还到第几期，避免会员还款时，提示已还过
        //还款期数
        if ($sort_order == $binfo['total']) {
            $upborrowsql .= ",`borrow_status`=7";//还款完成
        }
    }

    if ($is_expired) {
        $upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";
    }//代还则不记录还到第几期，避免会员还款时，提示已还过
    $upborrowsql .= " WHERE `id`={$borrow_id}";
    $upborrow_res = M()->execute($upborrowsql);

    $repayment_time = time();
    //到这里终止还款及更新还款状态
    if ($type==2) {//网站代还
        $updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital`,`substitute_time`=".$repayment_time." ,`substitute_money`=`substitute_money`+{$total_subs},`status`=4 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1 and is_debt = 0");
    } elseif ($is_expired) {
        $updetail_res = m()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".$repayment_time.",`call_fee`={$call_fee},`status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1 and is_debt = 0");
    } else {
        //提前还款执行这里
        $updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".$repayment_time.", `status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1 and is_debt = 0");
    }

    //更新还款概要表
    $smsUid = "";
    $trade_list = ""; //新浪的交易列表
    $jiaxi_list = ""; //新浪的交易列表
    $i = 0;
    $k = 0;
    $j = 0;
    $all_jiaxi =0;
    foreach ($detailList as $v) {
        //用于判断是否债权转让 ,债权转让日志不一样
        $debt = M("invest_detb")->field("serialid")->where("invest_id={$v['invest_id']} and status=1")->find();
          //interest_fee利息管理费 interest利息

        $getInterest = $v['interest'] - $v['interest_fee'];
        $upsql = "update `{$pre}borrow_investor` set ";
        $upsql .= "`receive_capital`=`receive_capital`+{$v['capital']},";
        $upsql .= "`receive_interest`=`receive_interest`+ {$getInterest},";
        //网站代还$type==2 capital投资人本金  $getInterest 利息减掉管理费  interest_fee利息管理费
        if ($type==2) {
            $total_s_invest = $v['capital'] + $getInterest;
            $upsql .= "`substitute_money` = `substitute_money` + {$total_s_invest},";
        }
        if ($sort_order == $binfo['total']) {
            $upsql .= "`status`=5,";
        }//还款完成
        $upsql .= "`paid_fee`=`paid_fee`+{$v['interest_fee']}";
        $upsql .= " WHERE `id`={$v['invest_id']}";
        $upinfo_res = M()->execute($upsql);

        //对投资帐户进行增加
        //$v['investor_uid'] 用户ID
        // $v['interest'] 利息按投资比例分配
        if ($upinfo_res) {
            $accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
            $datamoney['uid'] = $v['investor_uid'];
            $datamoney['type'] =9;// ($type==2)?"10":"9";
            $datamoney['affect_money'] = ($v['capital']+$v['interest']);//利息加本金

            $utype = M("members")->where("id={$datamoney['uid']}")->field("user_regtype")->find();
            if ($utype['user_regtype']==1) {
                $account_type = 'SAVING_POT';
            } else {
                $account_type = 'BASIC';
            }
            // if($listnum==0){
            // 	$trade_list = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~SAVING_POT~'.$datamoney['affect_money'].'~~第'.$borrow_id.'号标投资收益还款';
            // }else{
            // 	$trade_list .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~SAVING_POT~'.$datamoney['affect_money'].'~~第'.$borrow_id.'号标投资收益还款';
            // }
            // $listnum++;
            //sinabatchpay($v['investor_uid'],$borrow_id,$datamoney['affect_money']);
            $newbid=borrowidlayout1($borrow_id);
            if ($i < 200) {
                if ($k === 0) {
                    if ($v["debt_borrow_id"] == 0) {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第'.$newbid.'号标投资收益还款';
                        $jiaxi_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第'.$newbid.'号标加息金额';
                    } else {
                        $trade_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第ZQ'.$v["debt_borrow_id"].'号标投资收益还款';
                        $jiaxi_list[$j] = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第ZQ'.$v["debt_borrow_id"].'号标加息金额';
                    }
                    $k++;
                } else {
                    if ($v["debt_borrow_id"] == 0) {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第'.$newbid.'号标投资收益还款';
                        $jiaxi_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第'.$newbid.'号标加息金额';
                    } else {
                        $trade_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第ZQ'.$v["debt_borrow_id"].'号标投资收益还款';
                        $jiaxi_list[$j] .= '$'.date('YmdHis').mt_rand(100000, 999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第ZQ'.$v["debt_borrow_id"].'号标加息金额';
                    }
                }
                $i++;
                if ($i === 200) {
                    $i = 0;
                    $k = 0;
                    $j++;
                }
            }


            $all_jiaxi += $v["jiaxi_money"];




            $investor_detail = M('investor_detail');
            $collect = $investor_detail->where('investor_uid= '.$datamoney['uid'].' AND repayment_time = 0 and status!=-1 and is_debt = 0')->sum('capital+interest');

            if ($collect == null) {
                $collect = 0;
            }
            // 从借款人账户减掉本金加利息
            // $accountMoney['money_collect'] 到期还的利息   $datamoney['affect_money']提前还按天的利息
            $datamoney['collect_money'] = $collect;
            $datamoney['freeze_money'] = $accountMoney['money_freeze'];
            ///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////
            //$binfo borrow_info表查询 borrow_type标的类型
            if ($binfo['borrow_type']<>3) {//如果不是秒标，那么回的款会进入回款资金池，如果是秒标，回款则会进入充值资金池
                $datamoney['account_money'] = $accountMoney['account_money'];
                $datamoney['back_money'] = ($accountMoney['back_money'] + $datamoney['affect_money']);
            } else {
                $datamoney['account_money'] = $accountMoney['account_money'] + $datamoney['affect_money'];
                $datamoney['back_money'] = $accountMoney['back_money'];
            }

            ///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////

            //会员帐户
            $mmoney['money_freeze']=$datamoney['freeze_money'];
            $mmoney['money_collect']=$datamoney['collect_money'];
            $mmoney['account_money']=$datamoney['account_money'];
            $mmoney['back_money']=$datamoney['back_money'];
            //会员帐户
            $vbid = borrowidlayout1($v['borrow_id']);
            $datamoney['info'] ="收到会员对{$vbid}号标第{$sort_order}期的还款";// ($type==2)?"{$repayment_name}对{$v['borrow_id']}号标第{$sort_order}期代还":"收到会员对{$v['borrow_id']}号标第{$sort_order}期的还款";
            //如果债权流水号存在
            $debt['serialid'] &&  $datamoney['info'] ="收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";// ($type==2)?"{$repayment_name}对{$debt['serialid']}号债权第{$sort_order}期代还":"收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";
            $datamoney['add_time'] = time();
            $datamoney['add_ip'] = get_client_ip();
            if ($type==2) {
                $datamoney['target_uid'] = 0;
                $datamoney['target_uname'] = '@网站管理员@';
            } else {
                $datamoney['target_uid'] = $binfo['borrow_uid'];
                $datamoney['target_uname'] = $b_member['user_name'];
            }

            //echo M('member_moneylog')->getLastSql();
            $moneynewid = M('member_moneylog')->add($datamoney);
            if ($moneynewid) {
                $xid = M('member_money')->where("uid={$datamoney['uid']}")->save($mmoney);
            }

            //dump($v['interest'].'<br>');
            // 短信或邮件通知MTip为发送接口
            // if($type==2){//如果是网站代还
            // 	MTip('chk18',$v['investor_uid'],$borrow_id);//sss
            // }else{
            // 	MTip('chk16',$v['investor_uid'],$borrow_id);//sss
            // }
            $smsUid .= (empty($smsUid))?$v['investor_uid']:",{$v['investor_uid']}";

            //利息管理费扣除 $v['interest_fee']  account_money可用资金
            $xid_z = true;
            if ($v['interest_fee']>0 && $type==1) {
                $xid_z = false;
                $accountMoney_z = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
                $datamoney_z['uid'] = $v['investor_uid'];
                $datamoney_z['type'] = 23;
                $datamoney_z['affect_money'] = -($v['interest_fee']);//扣管理费

                $datamoney_z['collect_money'] = $accountMoney_z['money_collect'];
                $datamoney_z['freeze_money'] = $accountMoney_z['money_freeze'];
                if (($accountMoney_z['back_money'] + $datamoney_z['affect_money'])<0) {
                    $datamoney_z['back_money'] =0;
                    $datamoney_z['account_money'] = $accountMoney_z['account_money'] +$accountMoney_z['back_money']+ $datamoney_z['affect_money'];
                } else {
                    //提前还款执行这里
                    $datamoney_z['account_money'] = $accountMoney_z['account_money'];
                    $datamoney_z['back_money'] = ($accountMoney_z['back_money'] + $datamoney_z['affect_money']);
                }
                // 提前还款待收利息返回资金池
                // if($dq_day < $issue_day){
                // 	$datamoney_z['account_money'] += $datamoney_z['collect_money'];
                // 	$datamoney_z['collect_money'] = 0;
                // }

                //会员帐户
                $mmoney_z['money_freeze']  = $datamoney_z['freeze_money'];
                $mmoney_z['money_collect'] = $datamoney_z['collect_money']; // 待收利息
                $mmoney_z['account_money'] = $datamoney_z['account_money']; // 资产总额
                $mmoney_z['back_money']    = $datamoney_z['back_money'];    // 本金加利息

                //会员帐户
                $datamoney_z['info'] = "网站已将第{$vbid}号标第{$sort_order}期还款的利息管理费扣除";
                $datamoney_z['add_time'] = time();
                $datamoney_z['add_ip'] = get_client_ip();
                $datamoney_z['target_uid'] = 0;
                $datamoney_z['target_uname'] = '@网站管理员@';

                $moneynewid_z = M('member_moneylog')->add($datamoney_z);

                if ($moneynewid_z) {
                    $xid_z = M('member_money')->where("uid={$datamoney_z['uid']}")->save($mmoney_z);
                }
            }
        }
        //sleep(1);
    }
    //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起批量代付还款",1);
    foreach ($trade_list as $list) {
        sinabatchpay($list, $v['borrow_id']);
    }
    logw("all_jiaxi:".$all_jiaxi);
    if ($all_jiaxi>0) {
        logw("all_jiaxi:".$all_jiaxi);
        sinapayjiaxi($all_jiaxi, $v['borrow_id'], $jiaxi_list);
    }
    //sinabatchpay($trade_list);//新浪批量代付接口
    //moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成批量代付还款",2);

    //更新还款概要表
    //echo "$updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z";
    if ($updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z) {
        $borrowDetail->commit() ;
         //撤销转让的债权 ,完成还款更改债权转让状态
         cancelDebt($borrow_id);

        $_last = true;
        if ($binfo['total'] == ($binfo['has_pay']+1) && $type==1) {
            $_last=false;
            $_is_last = lastRepayment($binfo);//最后一笔还款
            if ($_is_last) {
                $_last = true;
            }
        }
        $done=true;

        $vphone = M("member_info")->field("cell_phone")->where("uid in({$smsUid}) and cell_phone !=''")->select();
        $sphone = "";
        foreach ($vphone as $v) {
            $sphone.=(empty($sphone))?$v['cell_phone']:",{$v['cell_phone']}";
        }
        SMStip("payback", $sphone, array("#ID#","#ORDER#"), array($newbid,$sort_order));
        file_put_contents('errorlog.txt', '成功了', FILE_APPEND);
    } else {
        $borrowDetail->rollback();
        file_put_contents('errorlog.txt', '失败了', FILE_APPEND);
    }
    //更新附件数据
    D("borrow_info_additional")->update_end($borrow_id);
    if ($type==2) {
        import("@.sms.Notice");
        $notcie=new Notice();
        $notcie->notice_borrower($borrow_id);
    }
    return $done;
}

/**
 * 债权标标还款
 * @param $borrow_id
 * @param $sort_order
 * @param int $type 1 自己还款  2 代还款
 * @param int $repayment_id
 * @return bool
 */
// function zhaiquan_borrowRepayment($borrow_id,$sort_order,$type=1,$repayment_id=0){//type 1:会员自己还,2网站代还
// 	$newbid = borrowidlayout1($borrow_id);
// 	$pre = C('DB_PREFIX');
// 	$done = false;
// 	$borrowDetail = D('investor_detail');
// 	$binfo = M("borrow_info")->field("id,borrow_uid,borrow_duration_txt,n_interest,colligate_fee,n_colligate_fee,product_type,add_time,second_verify_time,borrow_interest_rate,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline")->find($borrow_id);
// 	$b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);

// 	if( $binfo['has_pay']>=$sort_order) return "本期已还过，不用再还";
// 	if( $binfo['has_pay'] == $binfo['total'])  return "此标已经还完，不用再还";
// 	if( ($binfo['has_pay']+1)<$sort_order) return "对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期";

// 	$voxe = $borrowDetail->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')->where("borrow_id={$borrow_id} and status!=-1")->group('sort_order')->select();
// 	foreach($voxe as $ee=>$ss){
// 		if($ss['sort_order']==$sort_order) $vo = $ss;
// 	}

// 	// 复审通过后开始计算借款人利息 获取复审时间
// 	//$atime = M('borrow_investor')->field("add_time")->where("borrow_id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
// 	$atime = $binfo['second_verify_time'];
// 	//企业直投与普通标,判断还款期数不一样
// 	//借款天数、还款时间
// 	//利息计算公式 借款总金额*(借款利率/36000)*借款天数
// 	$borrow_money           = intval($binfo['borrow_money']); //借款总额
// 	$borrow_interest_rate   = $binfo['borrow_interest_rate']; //借款利率 此处因为利率转成了整数 20% 转成 2
// 	$day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率



// 	$issue_m   = M('borrow_info')->where('id='.$borrow_id)->find();
// 	$deadl  = date('Y-m-d H:i:s',cal_deadline($borrow_id));

// 	$dq_day    = date('Y-m-d H:i:s');

// 	// 提前还款 当前还时间小于最后还款时间23:59:59
// 	if( $binfo['repayment_type'] == 1){//天标才需要重新计算这个部分
// 		// 更新利息 M('investor_detail')
// 		$investor_uid = M('investor_detail')->where('borrow_id='.$borrow_id." and status!=-1")->select();


// 		$vo['interest'] = 0;
// 		$Detail = M("investor_detail");
// 		// 提单质押标1 现货质押标3
// 		if($binfo['product_type'] == 1 || $binfo['product_type'] == 3||$binfo['product_type'] == 6||$binfo['product_type'] == 7||$binfo['product_type'] == 8){
// 			$currentTime            = strtotime(date('Y-m-d')); //当前需还款时间
// 			$issueTime              = strtotime(date('Y-m-d',$atime));//复审后的时间
// 			$binfo['deadline']=cal_deadline($borrow_id);//修正bug.
// 			if(strtotime(date('Y-m-d',$binfo['deadline'])) == $currentTime && $borrow_id <= 325){
// 				$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
// 			}else if(strtotime(date('Y-m-d',$binfo['deadline']))>$currentTime){
// 				$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
// 			}else{
// 				$BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
// 			}
// 			if($BorrowingDays == 0){
// 				$BorrowingDays = $BorrowingDays +1;
// 			}
// 			Log::write("还款天数为".$BorrowingDays);
// 			// 综合服务费 利率/36000 * 借款金额 * 天数
// 			$colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
// 			foreach ($investor_uid as $iteme) {
// 				$tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
// 				$vo['interest'] += $tou_interest;
// 				unset($iteme['id']);
// 				$Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id} and status!=-1");
// 			}
// 		}
// 		// 提单转现货质押标2
// 		if ($binfo['product_type'] == 2) {
// 			// 投资人额度/标的总额*旧利息
// 			$vo['interest'] = 0;
// 			$xhtime = M('borrow_info')->field("add_time")->where("id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
// 			$currentTime            = strtotime(date('Y-m-d')); //当前时间
// 			$issueTime              = strtotime(date('Y-m-d',$xhtime['add_time']));//转现货时间
// 			$binfo['deadline']=cal_deadline($borrow_id);//修正bug.
// 			if(strtotime(date('Y-m-d',$binfo['deadline'])) == $currentTime && $borrow_id <= 325){
// 				$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
// 			}else  if(strtotime(date('Y-m-d',$binfo['deadline']))>$currentTime){
// 				$BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
// 			}else{
// 				$BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
// 			}
// 			if($BorrowingDays == 0){
// 				$BorrowingDays = $BorrowingDays +1;
// 			}
// 			Log::write("还款天数为".$BorrowingDays);


// 			// 综合服务费 利率/36000 * 借款金额 * 天数  提单转现货的综合服务费
// 			$colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
// 			foreach ($investor_uid as $iteme) {
// 				$tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
// 				$vo['interest'] += $tou_interest;
// 			}
// 			foreach ($investor_uid as $n) {
// 				$d_interest = getFloatValue($n['capital']/$binfo['borrow_money']*($vo['interest']+$binfo['n_interest']),2);
// 				unset($iteme['id']);
// 				$Detail->execute("update `{$pre}investor_detail` set `interest`={$d_interest} WHERE `capital`={$n['capital']} and `borrow_id`={$borrow_id} and status!=-1");
// 			}
// 			$vo['interest'] += $binfo['n_interest'];
// 			$colligate_fee +=$binfo['n_colligate_fee'];
// 		}
// 	}
// 	$pay_frist=D("borrow_info_additional")->is_pay_frist($borrow_id);//判断此标是否提前收取了综合服务费。 1表示已经收取。
// 	if($pay_frist)
// 		$colligate_fee=0;//已经提前收取了综合服务费，这里不能再收取。
// 	import("@.conf.borrow_expired");
// 	$expired=new borrow_expired($borrow_id,$sort_order);


// 	if($expired->is_expired()){//此标已逾期
// 		$is_expired = true;
// 		if($vo['substitute_time']>0) $is_substitute=true;//已代还
// 		else $is_substitute=false;
// 		//逾期的相关计算
// 		$expired_days =$expired->get_expired_day();
// 		$expired_money = $expired->get_expired__money();
// 		$call_fee = 0;//getExpiredCallFee($expired_days,$vo['capital'],$vo['interest']);
// 		//逾期的相关计算
// 	}else{
// 		$is_expired = false;
// 		$expired_days = 0;
// 		$expired_money = 0;
// 		$call_fee = 0;
// 	}

// 	//企业直投与普通标,判断还款期数不一样
// 	//MTip('chk25',$binfo['borrow_uid'],$borrow_id);//sss
// 	$accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
// 	// if($type==1 && $binfo['borrow_type']<>3 && ($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'])<($vo['capital']+$vo['interest']+$expired_money+$call_fee))
// 	// return "帐户可用余额不足，本期还款共需".($vo['capital']+$vo['interest']+$expired_money+$call_fee)."元，请先充值";

// 	if($is_substitute && $is_expired){//已代还后的会员还款，则只需要对会员的帐户进行操作后然后更新还款时间即可返回
// 		$borrowDetail->startTrans();
// 		$datamoney_x['uid'] = $binfo['borrow_uid'];
// 		$datamoney_x['type'] = 11;
// 		$datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
// 		if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
// 			$datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
// 			$datamoney_x['back_money'] = 0;
// 		}else{
// 			$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
// 			$datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 		}
// 		$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
// 		$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

// 		//会员帐户
// 		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 		$mmoney_x['account_money']=$datamoney_x['account_money'];
// 		$mmoney_x['back_money']=$datamoney_x['back_money'];
// 		//会员帐户
// 		$datamoney_x['info'] = "对{$newbid}号标第{$sort_order}期还款，扣除综合服务费{$colligate_fee}元";
// 		$datamoney_x['add_time'] = time();
// 		$datamoney_x['add_ip'] = get_client_ip();
// 		$datamoney_x['target_uid'] = 0;
// 		$datamoney_x['target_uname'] = '@网站管理员@';

// 		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 		if($moneynewid_x) $bxid_1 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
// 		//逾期了
// 		//逾期罚息
// 		$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
// 		$datamoney_x = array();
// 		$mmoney_x=array();

// 		$datamoney_x['uid'] = $binfo['borrow_uid'];
// 		$datamoney_x['type'] = 30;
// 		$datamoney_x['affect_money'] = -($expired_money);
// 		if(($datamoney_x['affect_money']+$accountMoney['back_money'])<0){//如果需要还款的逾期罚息金额大于回款资金池资金总额
// 			$datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
// 			$datamoney_x['back_money'] = 0;
// 		}else{
// 			$datamoney_x['account_money'] = $accountMoney['account_money'];
// 			$datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 		}
// 		$datamoney_x['collect_money'] = $accountMoney['money_collect'];
// 		$datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

// 		//会员帐户
// 		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 		$mmoney_x['account_money']=$datamoney_x['account_money'];
// 		$mmoney_x['back_money']=$datamoney_x['back_money'];
// 		//会员帐户
// 		$datamoney_x['info'] = "{$newbid}号标第{$sort_order}期的逾期罚息";
// 		$datamoney_x['add_time'] = time();
// 		$datamoney_x['add_ip'] = get_client_ip();
// 		$datamoney_x['target_uid'] = 0;
// 		$datamoney_x['target_uname'] = '@网站管理员@';

// 		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 		if($moneynewid_x) $bxid_2 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);

// 		//催收费
// 		$accountMoney_2 = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
// 		$datamoney_x = array();
// 		$mmoney_x=array();

// 		$datamoney_x['uid'] = $binfo['borrow_uid'];
// 		$datamoney_x['type'] = 31;
// 		$datamoney_x['affect_money'] = -($call_fee);
// 		if(($datamoney_x['affect_money']+$accountMoney_2['back_money'])<0){//如果需要还款的催收费金额大于回款资金池资金总额
// 			$datamoney_x['account_money'] = $accountMoney_2['account_money']+$accountMoney_2['back_money'] + $datamoney_x['affect_money'];
// 			$datamoney_x['back_money'] = 0;
// 		}else{
// 			$datamoney_x['account_money'] = $accountMoney_2['account_money'];
// 			$datamoney_x['back_money'] = $accountMoney_2['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 		}
// 		$datamoney_x['collect_money'] = $accountMoney_2['money_collect'];
// 		$datamoney_x['freeze_money'] = $accountMoney_2['money_freeze'];

// 		//会员帐户
// 		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 		$mmoney_x['account_money']=$datamoney_x['account_money'];
// 		$mmoney_x['back_money']=$datamoney_x['back_money'];
// 		//会员帐户
// 		$datamoney_x['info'] = "网站对借款人收取的第{$newbid}号标第{$sort_order}期的逾期催收费";
// 		$datamoney_x['add_time'] = time();
// 		$datamoney_x['add_ip'] = get_client_ip();
// 		$datamoney_x['target_uid'] = 0;
// 		$datamoney_x['target_uname'] = '@网站管理员@';

// 		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 		if($moneynewid_x) $bxid_3 = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);

// 		//逾期了
// 		$updetail_res = M()->execute("update `lzh_investor_detail` set `repayment_time`=".time().",`status`=5 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1");
// 		//更新借款信息
// 		$upborrowsql = "update `lzh_borrow_info` set ";
// 		$upborrowsql .= ",`substitute_money`=0";
// 		$upborrowsql .= ",`borrow_status`=10";//会员在网站代还后，逾期还款
// 		$upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
// 		$upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
// 		if ( $sort_order == $binfo['total'] )
// 		{
// 			$upborrowsql .= ",`borrow_status`=7";
// 		}
// 		$upborrowsql .= ",`has_pay`={$sort_order}";
// 		if ( $is_expired )
// 		{
// 			$upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";
// 		}
// 		$upborrowsql .= " WHERE `id`={$borrow_id}";

// 		$upborrow_res = M()->execute($upborrowsql);
// 		//更新借款信息

// 		if($updetail_res&&$bxid_1&&$bxid_2&&$bxid_3&&$upborrow_res){
// 			//if($updetail_res&&$upborrow_res){
// 			$borrowDetail->commit() ;
// 			//撤销转让的债权 ,完成还款更改债权转让状态
// 			cancelDebt($borrow_id);
// 			return true;
// 		}else{
// 			$borrowDetail->rollback() ;
// 			return false;
// 		}
// 	}

// 	//企业直投与普通标,判断还款期数不一样
// 	$detailList = $borrowDetail->field('debt_borrow_id,invest_id,investor_uid,capital,interest,interest_fee,borrow_id,total,jiaxi_money')->where("borrow_id={$borrow_id} AND sort_order={$sort_order} and status!=-1")->select();
// 	//企业直投与普通标,判断还款期数不一样

// 	/*************************************逾期还款积分与还款状态处理开始 20130509 fans***********************************/
// 	$datag = get_global_setting();
// 	$credit_borrow = explode("|",$datag['credit_borrow']);

// 	//会员自已还款执行从这里开始
// 	if($type==1){//客户自己还款才需要记录这些操作
// 		$day_span = ceil(($vo['deadline']-time())/(3600*24));
// 		// $credits_money = intval($vo['capital']/$credit_borrow[4]);
// 		// $credits_info = "对第{$newbid}号标的还款操作,获取投资积分";
// 		if($day_span>=0 && $day_span<1){//正常还款
// 			//$credits_result = memberCreditsLog($binfo['borrow_uid'],3,$credits_money*$credit_borrow[0],$credits_info);
// 			// $credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital']/1000),"对第{$newbid}号标进行了正常的还款操作,获取投资积分");//还款积分处理
// 			$idetail_status=1;
// 		}elseif($day_span>=-3 && $day_span<0){//迟还
// 			// $credits_result = memberCreditsLog($binfo['borrow_uid'],4,$credits_money*$credit_borrow[1],"对第{$newbid}号标的还款操作(迟到还款),扣除信用积分");
// 			$idetail_status=5;//3
// 		}elseif($day_span<-3){//逾期还款
// 			// $credits_result = memberCreditsLog($binfo['borrow_uid'],5,$credits_money*$credit_borrow[2],"对第{$newbid}号标的还款操作(逾期还款),扣除信用积分");
// 			$idetail_status=5;
// 		}elseif($day_span>=1){//提前还款
// 			//$credits_result = memberCreditsLog($binfo['borrow_uid'],6,$credits_money*$credit_borrow[3],$credits_info);
// 			//提前还款按天计算的利息
// 			//投标总金额 $borrow_money
// 			// $credits_result = memberIntegralLog($binfo['borrow_uid'],1,intval($vo['capital'] * $day_span/1000),"对第{$newbid}号标进行了提前还款操作,获取投资积分");//还款积分处理
// 			$idetail_status=2;
// 		}
// 		// if(!$credits_result) return "因积分记录失败，未完成还款操作";
// 	}
// 	/*************************************逾期还款积分与还款状态处理结束 20130509 fans***********************************/

// 	$borrowDetail->startTrans();
// 	//对借款者帐户进行减少
// 	//$vo['interest']提前还款重新按天计算的利息 $colligate_fee综合服务费

// 	$bxid = true;
// 	if($type==1){
// 		$bxid = false;
// 		$datamoney_x['uid'] = $binfo['borrow_uid'];
// 		$datamoney_x['type'] = 11;
// 		// 本金$vo['capital'] + 利息$vo['interest'] + 综合服务费
// 		$datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
// 		// print_r($datamoney_x['affect_money']);die;
// 		if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
// 			$datamoney_x['account_money'] = floatval($accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money']);
// 			$datamoney_x['back_money'] = 0;
// 		}else{
// 			$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
// 			$datamoney_x['back_money'] = floatval($accountMoney_borrower['back_money']) + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 		}
// 		$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
// 		$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

// 		//会员帐户
// 		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 		$mmoney_x['account_money']=$datamoney_x['account_money'];
// 		$mmoney_x['back_money']=$datamoney_x['back_money'];

// 		//会员帐户
// 		$datamoney_x['info'] = "对{$newbid}号标第{$sort_order}期还款，扣除综合服务费{$colligate_fee}元";
// 		$datamoney_x['add_time'] = time();
// 		$datamoney_x['add_ip'] = get_client_ip();
// 		$datamoney_x['target_uid'] = 0;
// 		$datamoney_x['target_uname'] = '@-网站管理员-@';
// 		//新浪 代收接口（对借款人余额减少并收取综合服务费）
// 		/**
// 		if($colligate_fee!=0){
// 			$sina['uid'] = $datamoney_x['uid']; //借款人ID
// 			//$sina['money'] = $vo['capital']+$vo['interest']+$colligate_fee; //还款总金额
// 			$sina['colligate_fee'] = $colligate_fee;//综合服务费
// 			$sina['bid'] = $borrow_id;//综合服务费
// 			//sinacollecttrade($sina); //代收借款人还款金额
// 			//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起收取综合服务费",1);
// 			$rs = sinapaytrade($colligate_fee);//代付综合服务费
// 			if($rs["code"]=="APPLY_SUCCESS"){
// 				$data['uid'] = $datamoney_x['uid'];
// 				$data['borrow_id'] = $borrow_id;
// 				$data['type'] = 10;
// 				$data['order_no'] = $rs["order_no"];
// 				$data['money'] = $money;
// 				$data['addtime'] = $colligate_fee;
// 				$data['sort_order'] = null;
// 				$data["status"] = 2;
// 				M('sinalog')->add($data);
// 			}
// 		}
// 		 * **/
// 		//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成收取综合服务费",2);
// 		//开始写还款数据
// 		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 		if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);

// 		//逾期了
// 		if($is_expired){
// 			//逾期罚息
// 			if($expired_money>0){
// 				$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
// 				$datamoney_x = array();
// 				$mmoney_x=array();

// 				$datamoney_x['uid'] = $binfo['borrow_uid'];
// 				$datamoney_x['type'] = 30;
// 				$datamoney_x['affect_money'] = -($expired_money);
// 				if(($datamoney_x['affect_money']+$accountMoney['back_money'])<0){//如果需要还款的逾期罚息金额大于回款资金池资金总额
// 					$datamoney_x['account_money'] = $accountMoney['account_money']+$accountMoney['back_money'] + $datamoney_x['affect_money'];
// 					$datamoney_x['back_money'] = 0;
// 				}else{
// 					$datamoney_x['account_money'] = $accountMoney['account_money'];
// 					$datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 				}
// 				$datamoney_x['collect_money'] = $accountMoney['money_collect'];
// 				$datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

// 				//会员帐户
// 				$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 				$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 				$mmoney_x['account_money']=$datamoney_x['account_money'];
// 				$mmoney_x['back_money']=$datamoney_x['back_money'];

// 				//会员帐户
// 				$datamoney_x['info'] = "{$newbid}号标第{$sort_order}期的逾期罚息";
// 				$datamoney_x['add_time'] = time();
// 				$datamoney_x['add_ip'] = get_client_ip();
// 				$datamoney_x['target_uid'] = 0;
// 				$datamoney_x['target_uname'] = '@网站管理员@';
// 				$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 				if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
// 				$expired->sent_expired_money(1, $b_member['user_name']);
// 			}

// 			//催收费
// 			if($call_fee>0){
// 				$accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($binfo['borrow_uid']);
// 				$datamoney_x = array();
// 				$mmoney_x=array();

// 				$datamoney_x['uid'] = $binfo['borrow_uid'];
// 				$datamoney_x['type'] = 31;
// 				$datamoney_x['affect_money'] = -($call_fee);
// 				if(($datamoney_x['affect_money']+$accountMoney_borrower['back_money'])<0){//如果需要还款的催收费金额大于回款资金池资金总额
// 					$datamoney_x['account_money'] = $accountMoney_borrower['account_money']+$accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];
// 					$datamoney_x['back_money'] = 0;
// 				}else{
// 					$datamoney_x['account_money'] = $accountMoney_borrower['account_money'];
// 					$datamoney_x['back_money'] = $accountMoney_borrower['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 				}
// 				$datamoney_x['collect_money'] = $accountMoney_borrower['money_collect'];
// 				$datamoney_x['freeze_money'] = $accountMoney_borrower['money_freeze'];

// 				//会员帐户
// 				$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 				$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 				$mmoney_x['account_money']=$datamoney_x['account_money'];
// 				$mmoney_x['back_money']=$datamoney_x['back_money'];

// 				//会员帐户
// 				$datamoney_x['info'] = "网站对借款人收取的第{$newbid}号标第{$sort_order}期的逾期催收费";
// 				$datamoney_x['add_time'] = time();
// 				$datamoney_x['add_ip'] = get_client_ip();
// 				$datamoney_x['target_uid'] = 0;
// 				$datamoney_x['target_uname'] = '@网站管理员@';
// 				$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 				if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
// 			}
// 		}
// 		//逾期了
// 	}
// 	else if($type==2){
// 		$bxid = false;
// 		//获取超级管理员ID,只有超级管理员才有资格还款
// 		//判断还款ID;

// 		$add_function=C("ADD_FUNCTION");
// 		$tesu_id_name=$add_function['repayment']['account'];
//         $wood_id_name=$add_function['repayment']['account1'];
//         $where['user_name'] = "user_name = ".$tesu_id_name."OR".$wood_id_name;
//         $list=M('members')->where($where)->select();
//         $tesu=$list[0]['id'];
//         if($repayment_id==$tesu){
//             $repayment_name="特速实业";
// 		}else{
// 			$repayment_name=D("Members_company")->get_danbao_name($repayment_id);
// 		}


// 		$tesu_accountMoney_borrower = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($repayment_id);
// 		$datamoney_x['uid'] = $repayment_id;
// 		$datamoney_x['type'] = 10; //网站代替
// 		// 本金$vo['capital'] + 利息$vo['interest'] + 综合服务费
// 		$datamoney_x['affect_money'] = -($vo['capital']+$vo['interest']+$colligate_fee);
// 		// print_r($datamoney_x['affect_money']);die;
// 		if(($datamoney_x['affect_money']+$tesu_accountMoney_borrower['back_money'])<0){//如果需要还款的金额大于回款资金池资金总额
// 			$datamoney_x['account_money'] = floatval($tesu_accountMoney_borrower['account_money']+$tesu_accountMoney_borrower['back_money'] + $datamoney_x['affect_money']);
// 			$datamoney_x['back_money'] = 0;
// 		}else{
// 			$datamoney_x['account_money'] = $tesu_accountMoney_borrower['account_money'];
// 			$datamoney_x['back_money'] = floatval($tesu_accountMoney_borrower['back_money']) + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 		}
// 		$datamoney_x['collect_money'] = $tesu_accountMoney_borrower['money_collect'];
// 		$datamoney_x['freeze_money'] = $tesu_accountMoney_borrower['money_freeze'];

// 		//会员帐户
// 		$mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
// 		$mmoney_x['money_collect']=$datamoney_x['collect_money'];
// 		$mmoney_x['account_money']=$datamoney_x['account_money'];
// 		$mmoney_x['back_money']=$datamoney_x['back_money'];

// 		//会员帐户
// 		$datamoney_x['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期还款，扣除本金{$vo['capital']},利息{$vo['interest']},综合服务费{$colligate_fee}元,";
// 		$datamoney_x['add_time'] = time();
// 		$datamoney_x['add_ip'] = get_client_ip();
// 		$datamoney_x['target_uid'] = 0;
// 		$datamoney_x['target_uname'] = '@-网站管理员-@';
// 		//新浪 代收接口（对借款人余额减少并收取综合服务费）
// 		/**
// 		 * 债权的不收取综合服务费
// 		if($colligate_fee!=0){
// 			$sina['uid'] = $datamoney_x['uid']; //借款人ID
// 			//$sina['money'] = $vo['capital']+$vo['interest']+$colligate_fee; //还款总金额
// 			$sina['colligate_fee'] = $colligate_fee;//综合服务费
// 			$sina['bid'] = $borrow_id;//综合服务费
// 			//sinacollecttrade($sina); //代收借款人还款金额
// 			//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起收取综合服务费",1);
// 			$rs = sinapaytrade($colligate_fee);//代付综合服务费
// 			if($rs["code"]=="APPLY_SUCCESS"){
// 				$data['uid'] = $datamoney_x['uid'];
// 				$data['borrow_id'] = $borrow_id;
// 				$data['type'] = 10;
// 				$data['order_no'] = $rs["order_no"];
// 				$data['money'] = $money;
// 				$data['addtime'] = $colligate_fee;
// 				$data['sort_order'] = null;
// 				$data["status"] = 2;
// 				M('sinalog')->add($data);
// 			}
// 		}
// 		 * **/

// 		//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成收取综合服务费",2);
// 		//开始写还款数据
// 		$record_info="{$repayment_name}对{$newbid}号标第{$sort_order}期还款,本金为{$vo['capital']},利息为{$vo['interest']},综合服务费为{$colligate_fee}\n\r";
// 		file_put_contents("log.txt",$record_info,FILE_APPEND);
// 		$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 		if($moneynewid_x) $bxid = M('member_money')->where("uid={$repayment_id}")->save($mmoney_x);

// 		//写代付的moneylog
// 		$borrower['uid'] = $binfo['borrow_uid'];
// 		$borrower['type']=10;
// 		$borrower['affect_money']= $datamoney_x['affect_money'];
// 		$borrower['account_money']=0;
// 		$borrower['back_money']=0;
// 		$borrower['collect_money']=0;
// 		$borrower['freeze_money']=0;
// 		$borrower['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期还款，扣除本金{$vo['capital']},利息{$vo['interest']},综合服务费{$colligate_fee}元,";
// 		$borrower['add_time'] = time();
// 		$borrower['add_ip'] = get_client_ip();
// 		$borrower['target_uid'] = 0;
// 		$borrower['target_uname'] = '@-网站管理员-@';
// 		M('member_moneylog')->add($borrower);
// 		//逾期了
// 		if($is_expired) {//代还款，也需要罚息
// 			//逾期罚息
// 			if ($expired_money > 0) {
// 				$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($repayment_id);
// 				$datamoney_x = array();
// 				$mmoney_x = array();

// 				$datamoney_x['uid'] = $repayment_id;
// 				$datamoney_x['type'] = 30;
// 				$datamoney_x['affect_money'] = -($expired_money);
// 				if (($datamoney_x['affect_money'] + $accountMoney['back_money']) < 0) {//如果需要还款的逾期罚息金额大于回款资金池资金总额
// 					$datamoney_x['account_money'] = $accountMoney['account_money'] + $accountMoney['back_money'] + $datamoney_x['affect_money'];
// 					$datamoney_x['back_money'] = 0;
// 				} else {
// 					$datamoney_x['account_money'] = $accountMoney['account_money'];
// 					$datamoney_x['back_money'] = $accountMoney['back_money'] + $datamoney_x['affect_money'];//回款资金注入回款资金池
// 				}
// 				$datamoney_x['collect_money'] = $accountMoney['money_collect'];
// 				$datamoney_x['freeze_money'] = $accountMoney['money_freeze'];

// 				//会员帐户
// 				$mmoney_x['money_freeze'] = $datamoney_x['freeze_money'];
// 				$mmoney_x['money_collect'] = $datamoney_x['collect_money'];
// 				$mmoney_x['account_money'] = $datamoney_x['account_money'];
// 				$mmoney_x['back_money'] = $datamoney_x['back_money'];

// 				//会员帐户
// 				$datamoney_x['info'] = "{$repayment_name}对{$newbid}号标第{$sort_order}期的逾期罚息{$expired_money}";
// 				$datamoney_x['add_time'] = time();
// 				$datamoney_x['add_ip'] = get_client_ip();
// 				$datamoney_x['target_uid'] = 0;
// 				$datamoney_x['target_uname'] = '@网站管理员@';
// 				$moneynewid_x = M('member_moneylog')->add($datamoney_x);
// 				if ($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
// 				$expired->sent_expired_money(2,$repayment_name);

// 			}
// 		}

// 	}
// 	//对借款者帐户进行减少
// 	//更新借款账户信息 $vo['interest'] 利息
// 	$upborrowsql = "update `lzh_borrow_info` set ";
// 	$upborrowsql .= "`repayment_money`=`repayment_money`+{$vo['capital']}";
// 	$upborrowsql .= ",`repayment_interest`=`repayment_interest`+ {$vo['interest']}";
// 	//if($sort_order == $binfo['total']) $upborrowsql .= ",`borrow_status`=7";//还款完成
// 	$upborrowsql .= ",`has_pay`={$sort_order}";
// 	//如果是网站代还的，则记录代还金额
// 	if($type==2){
// 		$total_subs = ($vo['capital']+$vo['interest']);
// 		$upborrowsql .= ",`substitute_money`=`substitute_money`+ {$total_subs}";
// 		//$upborrowsql .= ",`has_pay`={$binfo['has_pay']}+1";//网站代还款完成
// 		if( $binfo['has_pay']+1 == $binfo['total']){
// 			$upborrowsql .= ",`borrow_status`=9";//网站代还款完成
// 		}

// 	}
// 	if($type==1){
// 		//$upborrowsql .= ",`has_pay`={$sort_order}";//代还则不记录还到第几期，避免会员还款时，提示已还过
// 		//还款期数
// 		if($sort_order == $binfo['total']){
// 			$upborrowsql .= ",`borrow_status`=7";//还款完成
// 		}
// 	}

// 	if($is_expired)  $upborrowsql .= ",`expired_money`=`expired_money`+{$expired_money}";//代还则不记录还到第几期，避免会员还款时，提示已还过
// 	$upborrowsql .= " WHERE `id`={$borrow_id}";
// 	$upborrow_res = M()->execute($upborrowsql);

// 	$repayment_time = time();
// 	//到这里终止还款及更新还款状态
// 	if($type==2){//网站代还
// 		$updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital`,`substitute_time`=".$repayment_time." ,`substitute_money`=`substitute_money`+{$total_subs},`status`=4 WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1");
// 	}else if($is_expired){
// 		$updetail_res = m( )->execute( "update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".$repayment_time.",`call_fee`={$call_fee},`status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1" );
// 	}else{
// 		//提前还款执行这里
// 		$updetail_res = M()->execute("update `{$pre}investor_detail` set `receive_capital`=`capital` ,`receive_interest`=(`interest`-`interest_fee`),`repayment_time`=".$repayment_time.", `status`={$idetail_status} WHERE `borrow_id`={$borrow_id} AND `sort_order`={$sort_order} and status!=-1");
// 	}

// 	//更新还款概要表
// 	$smsUid = "";
// 	$trade_list = ""; //新浪的交易列表
// 	$jiaxi_list = ""; //新浪的交易列表
// 	$i = 0;
// 	$k = 0;
// 	$j = 0;
// 	$all_jiaxi =0;
// 	foreach($detailList as $v){
// 		//用于判断是否债权转让 ,债权转让日志不一样
// 		$debt = M("invest_detb")->field("serialid")->where("invest_id={$v['invest_id']} and status=1")->find();
// 		//interest_fee利息管理费 interest利息
// 		if($v['debt_borrow_id']){//如果是债权标
//             $borrow_debtlist=M("borrow_debt")->where(array("borrow_id"=>$borrow_id,"debt_parent_borrow_id"=>$borrow_id))->find();//债权标,找到原始的对应的lzh_borrow_investor的invest_id
// 			if($borrow_debtlist && $borrow_debtlist["invest_id"]){
// 				$getInterest = $v['interest'] - $v['interest_fee'];
// 				$upsql = "update `lzh_borrow_investor` set ";
// 				$upsql .= "`receive_capital`=`receive_capital`+{$v['capital']},";
// 				$upsql .= "`receive_interest`=`receive_interest`+ {$getInterest},";
// 				//网站代还$type==2 capital投资人本金  $getInterest 利息减掉管理费  interest_fee利息管理费
// 				if ($type == 2) {
// 					$total_s_invest = $v['capital'] + $getInterest;
// 					$upsql .= "`substitute_money` = `substitute_money` + {$total_s_invest},";
// 				}
// 				if ($sort_order == $binfo['total']) $upsql .= "`status`=5,";//还款完成
// 				$upsql .= "`paid_fee`=`paid_fee`+{$v['interest_fee']}";
// 				$upsql .= " WHERE `id`={$borrow_debtlist["invest_id"]}";
// 				$upinfo_res = M()->execute($upsql);
// 				logw($upsql);
// 				logw("zhaiquan:1-1-1".print_r($upinfo_res,true));
// 			}else{
// 				$upinfo_res=true;// 如果找不到直接设置结果为true
// 			}
// 		}else {//原始标
// 			$getInterest = $v['interest'] - $v['interest_fee'];
// 			$upsql = "update `lzh_borrow_investor` set ";
// 			$upsql .= "`receive_capital`=`receive_capital`+{$v['capital']},";
// 			$upsql .= "`receive_interest`=`receive_interest`+ {$getInterest},";
// 			//网站代还$type==2 capital投资人本金  $getInterest 利息减掉管理费  interest_fee利息管理费
// 			if ($type == 2) {
// 				$total_s_invest = $v['capital'] + $getInterest;
// 				$upsql .= "`substitute_money` = `substitute_money` + {$total_s_invest},";
// 			}
// 			if ($sort_order == $binfo['total']) $upsql .= "`status`=5,";//还款完成
// 			$upsql .= "`paid_fee`=`paid_fee`+{$v['interest_fee']}";
// 			$upsql .= " WHERE `id`={$v['invest_id']}";
// 			$upinfo_res = M()->execute($upsql);
// 		}

// 		//对投资帐户进行增加
// 		//$v['investor_uid'] 用户ID
// 		// $v['interest'] 利息按投资比例分配
// 		if($upinfo_res){
// 			logw("批量代付款@-1");
// 			$accountMoney = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
// 			$datamoney['uid'] = $v['investor_uid'];
// 			$datamoney['type'] =9;// ($type==2)?"10":"9";
// 			$datamoney['affect_money'] = ($v['capital']+$v['interest']);//利息加本金

// 			$utype = M("members")->where("id={$datamoney['uid']}")->field("user_regtype")->find();
// 			if($utype['user_regtype']==1){
// 				$account_type = 'SAVING_POT';
// 			}else{
// 				$account_type = 'BASIC';
// 			}
// 			// if($listnum==0){
// 			// 	$trade_list = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~SAVING_POT~'.$datamoney['affect_money'].'~~第'.$borrow_id.'号标投资收益还款';
// 			// }else{
// 			// 	$trade_list .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~SAVING_POT~'.$datamoney['affect_money'].'~~第'.$borrow_id.'号标投资收益还款';
// 			// }
// 			// $listnum++;
// 			//sinabatchpay($v['investor_uid'],$borrow_id,$datamoney['affect_money']);
// 			$newbid=borrowidlayout1($borrow_id);
// 			if($i < 200){
// 				if($k === 0){
// 					$trade_list[$j] = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第'.$newbid.'号标投资收益还款';
// 					$jiaxi_list[$j] = date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第'.$newbid.'号标加息金额';
// 					$k++;
// 				}else{
// 					$trade_list[$j] .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$datamoney['affect_money'].'~~第'.$newbid.'号标投资收益还款';
// 					$jiaxi_list[$j] .= '$'.date('YmdHis').mt_rand( 100000,999999).'~20151008'.$datamoney['uid'].'~UID~'.$account_type.'~'.$v['jiaxi_money'].'~~第'.$newbid.'号标加息金额';
// 				}
// 				$i++;
// 				if($i === 200){$i = 0;$k = 0;$j++;}
// 			}


// 			$all_jiaxi += $v["jiaxi_money"];
// 			$investor_detail = M('investor_detail');
// 			$collect = $investor_detail->where('investor_uid= '.$datamoney['uid'].' AND repayment_time = 0 and status!=-1 ')->sum('capital+interest');

// 			if($collect == null){
// 				$collect = 0;
// 			}
// 			// 从借款人账户减掉本金加利息
// 			// $accountMoney['money_collect'] 到期还的利息   $datamoney['affect_money']提前还按天的利息
// 			$datamoney['collect_money'] = $collect;
// 			$datamoney['freeze_money'] = $accountMoney['money_freeze'];
// 			///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////
// 			//$binfo borrow_info表查询 borrow_type标的类型
// 			if($binfo['borrow_type']<>3 ){//如果不是秒标，那么回的款会进入回款资金池，如果是秒标，回款则会进入充值资金池
// 				$datamoney['account_money'] = $accountMoney['account_money'];
// 				$datamoney['back_money'] = ($accountMoney['back_money'] + $datamoney['affect_money']);
// 			}else{
// 				$datamoney['account_money'] = $accountMoney['account_money'] + $datamoney['affect_money'];
// 				$datamoney['back_money'] = $accountMoney['back_money'];
// 			}

// 			///////////////秒标回款不进入汇款资金池，也就可实现秒标回款不给回款续投奖励的功能了 2013-08-23 fan//////////////////

// 			//会员帐户
// 			$mmoney['money_freeze']=$datamoney['freeze_money'];
// 			$mmoney['money_collect']=$datamoney['collect_money'];
// 			$mmoney['account_money']=$datamoney['account_money'];
// 			$mmoney['back_money']=$datamoney['back_money'];
// 			//会员帐户
// 			$vbid = borrowidlayout1($v['borrow_id']);
// 			$datamoney['info'] ="收到会员对{$vbid}号标第{$sort_order}期的还款";// ($type==2)?"{$repayment_name}对{$v['borrow_id']}号标第{$sort_order}期代还":"收到会员对{$v['borrow_id']}号标第{$sort_order}期的还款";
// 			//如果债权流水号存在
// 			$debt['serialid'] &&  $datamoney['info'] ="收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";// ($type==2)?"{$repayment_name}对{$debt['serialid']}号债权第{$sort_order}期代还":"收到会员对{$debt['serialid']}号债权第{$sort_order}期的还款";
// 			$datamoney['add_time'] = time();
// 			$datamoney['add_ip'] = get_client_ip();
// 			if($type==2){
// 				$datamoney['target_uid'] = 0;
// 				$datamoney['target_uname'] = '@网站管理员@';
// 			}else{
// 				$datamoney['target_uid'] = $binfo['borrow_uid'];
// 				$datamoney['target_uname'] = $b_member['user_name'];
// 			}

// 			//echo M('member_moneylog')->getLastSql();
// 			$moneynewid = M('member_moneylog')->add($datamoney);
// 			if($moneynewid){
// 				$xid = M('member_money')->where("uid={$datamoney['uid']}")->save($mmoney);
// 			}

// 			//dump($v['interest'].'<br>');
// 			// 短信或邮件通知MTip为发送接口
// 			// if($type==2){//如果是网站代还
// 			// 	MTip('chk18',$v['investor_uid'],$borrow_id);//sss
// 			// }else{
// 			// 	MTip('chk16',$v['investor_uid'],$borrow_id);//sss
// 			// }
// 			$smsUid .= (empty($smsUid))?$v['investor_uid']:",{$v['investor_uid']}";

// 			//利息管理费扣除 $v['interest_fee']  account_money可用资金
// 			$xid_z = true;
// 			if($v['interest_fee']>0 && $type==1){
// 				$xid_z = false;
// 				$accountMoney_z = M('member_money')->field('money_freeze,money_collect,account_money,back_money')->find($v['investor_uid']);
// 				$datamoney_z['uid'] = $v['investor_uid'];
// 				$datamoney_z['type'] = 23;
// 				$datamoney_z['affect_money'] = -($v['interest_fee']);//扣管理费

// 				$datamoney_z['collect_money'] = $accountMoney_z['money_collect'];
// 				$datamoney_z['freeze_money'] = $accountMoney_z['money_freeze'];
// 				if(($accountMoney_z['back_money'] + $datamoney_z['affect_money'])<0){
// 					$datamoney_z['back_money'] =0;
// 					$datamoney_z['account_money'] = $accountMoney_z['account_money'] +$accountMoney_z['back_money']+ $datamoney_z['affect_money'];
// 				}else{
// 					//提前还款执行这里
// 					$datamoney_z['account_money'] = $accountMoney_z['account_money'];
// 					$datamoney_z['back_money'] = ($accountMoney_z['back_money'] + $datamoney_z['affect_money']);
// 				}
// 				// 提前还款待收利息返回资金池
// 				// if($dq_day < $issue_day){
// 				// 	$datamoney_z['account_money'] += $datamoney_z['collect_money'];
// 				// 	$datamoney_z['collect_money'] = 0;
// 				// }

// 				//会员帐户
// 				$mmoney_z['money_freeze']  = $datamoney_z['freeze_money'];
// 				$mmoney_z['money_collect'] = $datamoney_z['collect_money']; // 待收利息
// 				$mmoney_z['account_money'] = $datamoney_z['account_money']; // 资产总额
// 				$mmoney_z['back_money']    = $datamoney_z['back_money'];    // 本金加利息

// 				//会员帐户
// 				$datamoney_z['info'] = "网站已将第{$vbid}号标第{$sort_order}期还款的利息管理费扣除";
// 				$datamoney_z['add_time'] = time();
// 				$datamoney_z['add_ip'] = get_client_ip();
// 				$datamoney_z['target_uid'] = 0;
// 				$datamoney_z['target_uname'] = '@网站管理员@';

// 				$moneynewid_z = M('member_moneylog')->add($datamoney_z);

// 				if($moneynewid_z) $xid_z = M('member_money')->where("uid={$datamoney_z['uid']}")->save($mmoney_z);
// 			}
// 		}
// 		//sleep(1);
// 	}
// 	//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"平台发起批量代付还款",1);
// 	logw("进入批量还款");
// 	logw("数据：".print_r($trade_list,true));
// 	foreach ($trade_list as $list) {
// 		sinabatchpay($list,$v['borrow_id'],2);
// 	}

// 	if($all_jiaxi>0){
// 		sinapayjiaxi($all_jiaxi,borrowidlayout1($v['borrow_id']),$jiaxi_list);
// 	}
// 	//sinabatchpay($trade_list);//新浪批量代付接口
// 	//moneyactlog($binfo['borrow_uid'],5,$sina['colligate_fee'],0,"新浪完成批量代付还款",2);

// 	//更新还款概要表
// 	//echo "$updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z";
// 	if($updetail_res && $upinfo_res && $xid &&$upborrow_res && $bxid && $xid_z){
// 		$borrowDetail->commit() ;
// 		//撤销转让的债权 ,完成还款更改债权转让状态
// 		cancelDebt($borrow_id);

// 		$_last = true;
// 		if($binfo['total'] == ($binfo['has_pay']+1) && $type==1){
// 			$_last=false;
// 			$_is_last = lastRepayment($binfo);//最后一笔还款
// 			if($_is_last) $_last = true;
// 		}
// 		$done=true;

// 		$vphone = M("member_info")->field("cell_phone")->where("uid in({$smsUid}) and cell_phone !=''")->select();
// 		$sphone = "";
// 		foreach($vphone as $v){
// 			$sphone.=(empty($sphone))?$v['cell_phone']:",{$v['cell_phone']}";
// 		}
// 		SMStip("payback",$sphone,array("#ID#","#ORDER#"),array($newbid,$sort_order));
// 		file_put_contents('errorlog.txt', '成功了', FILE_APPEND);
// 	}else{
// 		$borrowDetail->rollback();
// 		file_put_contents('errorlog.txt', '失败了', FILE_APPEND);
// 	}
// 	//更新附件数据
// 	D("borrow_info_additional")->update_end($borrow_id);
// 	if($type==2){
// 		import("@.sms.Notice");
// 		$notcie=new Notice();
// 		$notcie->notice_borrower($borrow_id);
// 	}
// 	return $done;
// }



function getBorrowInterestRate($rate, $duration)
{
    return ($rate/(12*100)*$duration);
}


function getMoneyLog($map, $size)
{
    if (empty($map['uid'])) {
        return;
    }

    if ($size) {
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_moneylog')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_moneylog')->where($map)->order('id DESC')->limit($Lsql)->select();
    $type_arr = C("MONEY_LOG");
    foreach ($list as $key=>$v) {
        $list[$key]['type'] = $type_arr[$v['type']];
        /*if($v['affect_money']>0){
            $list[$key]['in'] = $v['affect_money'];
            $list[$key]['out'] = '';
        }else{
            $list[$key]['in'] = '';
            $list[$key]['out'] = $v['affect_money'];
        }*/
    }
    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function memberMoneyLog($uid, $type, $amoney, $info="", $target_uid="", $target_uname="", $fee=0)
{
    $xva = floatval($amoney);
    if (empty($xva)) {
        return true;
    }
    $done = false;
    $MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money")->find($uid);
    if (!is_array($MM)||empty($MM)) {
        M("member_money")->add(array('uid'=>$uid));
        $MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money")->find($uid);
    }
    $Moneylog = D('member_moneylog');
    if (in_array($type, array("71","72","73"))) {
        $type_save=7;
    } else {
        $type_save = $type;
    }

    if ($target_uname=="" && $target_uid>0) {
        $tname = M('members')->getFieldById($target_uid, 'user_name');
    } else {
        $tname = $target_uname;
    }
    if ($target_uid=="" && $target_uname=="") {
        $target_uid=0;
        $tname = '@网站管理员@';
    }
    $Moneylog->startTrans();
    $data['uid'] = $uid;
    $data['type'] = $type_save;
    $data['info'] = $info;
    $data['target_uid'] = $target_uid;
    $data['target_uname'] = $tname;
    $data['add_time'] = time();
    $data['add_ip'] = get_client_ip();
    switch ($type) {
        /////////////////////////////////////////
            case 13: //邀请好友奖励
                // $data['account_money'] = $MM['account_money']+$amoney;
                // $data['affect_money'] = $amoney;
                // $data['freeze_money'] = $MM['money_freeze'];
                // $data['collect_money'] = $MM['money_collect'];
                // $data['back_money'] = $MM['back_money'];
                // moneyactlog($uid,4,$amoney,0,"平台发起邀请好友奖励",1);
                // sinareward($uid,"好友推荐奖励");
                // break;
            case 5://撤消提现
                $data['affect_money'] = $amoney;

                if (($MM['back_money']+$amoney+$fee)<0) {//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
                    $data['back_money'] = 0;
                    $data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
                } else {
                    $data['back_money'] = $MM['back_money'];
                    $data['account_money'] = $MM['account_money']+$amoney+$fee;
                }

                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze']-$amoney;
            break;
            case 4://提现冻结
            //case 5://撤消提现
            case 6://投标冻结
            case 37://投企业直投冻结
                $data['affect_money'] = $amoney;

                if (($MM['back_money']+$amoney+$fee)<0) {//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
                    $data['back_money'] = 0;
                    $data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
                } else {
                    $data['back_money'] = $MM['back_money']+$amoney+$fee;
                    $data['account_money'] = $MM['account_money'];
                }

                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze']-$amoney;
            break;
            case 12://提现失败
                $data['affect_money'] = $amoney;

                if (($MM['account_money']+$MM['back_money'])>abs($fee)) {
                    if (($MM['back_money']+$amoney+$fee)<0) {//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
                        $data['back_money'] = 0;
                        $data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney+$fee;
                    } else {
                        $data['back_money'] = $MM['back_money'];
                        $data['account_money'] = $MM['account_money']+$amoney+$fee;  //修改提现失败后不回到可用里去的事情。
                    }
                    $data['collect_money'] = $MM['money_collect'];
                    $data['freeze_money'] = $MM['money_freeze']-$amoney;
                } else {
                    if (($MM['back_money']+$amoney+$fee)<0) {//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
                        $data['back_money'] = 0;
                        $data['account_money'] = $MM['account_money']+$MM['back_money']+$amoney;
                    } else {
                        $data['back_money'] = $MM['back_money'];
                        $data['account_money'] = $MM['account_money']+$amoney;
                    }
                    $data['collect_money'] = $MM['money_collect'];
                    $data['freeze_money'] = $MM['money_freeze']-$amoney+$fee;//修改提现失败后不回到可用里去的事情。
                }
                //$data['back_money'] = $data['back_money']-$amoney;//修改提现失败后不回到可用里去的事情。
            break;

            case 29://提现成功
                    $data['affect_money'] = $amoney;
                    $data['account_money'] = $MM['account_money']-$amoney;
                    $data['back_money'] = $MM['back_money'];
                    $data['collect_money'] = $MM['money_collect'];
                    $data['freeze_money'] = $MM['money_freeze'];
                break;
            case 36://提现通过，处理中
                $data['affect_money'] = $amoney;
                if (($MM['account_money']+$MM['back_money'])>abs($fee)) {
                    if (($MM['back_money']+$fee)<0) {//提现手续费先从回款余额资金池里扣，不够再去充值资金池里减少
                        $data['account_money'] = $MM['account_money']+$MM['back_money']+$fee;
                        $data['back_money'] = 0;
                    } else {
                        $data['account_money'] = $MM['account_money'];
                        $data['back_money'] = $MM['back_money']+$fee;
                    }
                    $data['collect_money'] = $MM['money_collect'];
                    $data['freeze_money'] = $MM['money_freeze'];
                } else {
                    $data['account_money'] =$MM['account_money'];
                    $data['back_money'] = $MM['back_money'];
                    $data['collect_money'] = $MM['money_collect'];
                    $data['freeze_money'] = $MM['money_freeze']+$fee;
                }
            break;
        ////////////////////////////////////////

            case 8://流标解冻
            case 19://借款保证金
            case 24://还款完成解冻
            case 34://预投标奖励撤销
                $data['affect_money'] = $amoney;
                if (($MM['account_money']+$amoney)<0) {
                    $data['account_money'] = 0;
                    $data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
                } else {
                    $data['account_money'] = $MM['account_money']+$amoney;
                    $data['back_money'] = $MM['back_money'];
                }
                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze']-$amoney;
            break;
            case 3://会员充值
            case 17://借款金额入帐
            case 18://借款管理费
            case 20://投标奖励
            case 21://支付投标奖励
            case 40://企业直投续投奖励
            case 41://企业直投投标奖励
            case 42://支付企业直投投标奖励
                $data['affect_money'] = $amoney;
                if (($MM['account_money']+$amoney)<0) {
                    $data['account_money'] = 0;
                    $data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
                } else {
                    $data['account_money'] = $MM['account_money']+$amoney;
                    $data['back_money'] = $MM['back_money'];
                }
                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze'];
            break;
            case 9://会员还款
            case 10://网站代还
                $data['affect_money'] = $amoney;
                $data['account_money'] = $MM['account_money'];
                $data['collect_money'] = $MM['money_collect']-$amoney;
                $data['freeze_money'] = $MM['money_freeze'];
                $data['back_money'] = $MM['back_money']+$amoney;
            break;
            case 15://投标成功冻结资金转为待收资金
            case 39://企业直投投标成功冻结资金转为待收资金
                $data['affect_money'] = $amoney;
                $data['account_money'] = $MM['account_money'];
                $data['collect_money'] = $MM['money_collect']+$amoney;
                $data['freeze_money'] = $MM['money_freeze']-$amoney;
                $data['back_money'] = $MM['back_money'];
            break;
            case 28://投标成功利息待收
                $borrow_in = M('borrow_investor')->where('borrow_id='.$borrow_id)->select();
                foreach ($borrow_in as $item) {
                    $amoney = getFloatValue($item['investor_capital']*$day_rate*$BorrowingDays, 2);
                    $data['affect_money'] = $amoney;
                    $data['account_money'] = $MM['account_money'];
                    $data['collect_money'] = $MM['money_collect']+$amoney;
                    $data['freeze_money'] = $MM['money_freeze']-$amoney;
                    $data['back_money'] = $MM['back_money'];
                }
            case 38://企业直投投标成功利息待收
            case 73://单独操作待收金额
                $data['affect_money'] = $amoney;
                $data['account_money'] = $MM['account_money'];
                $data['collect_money'] = $MM['money_collect']+$amoney;
                $data['freeze_money'] = $MM['money_freeze'];
                $data['back_money'] = $MM['back_money'];
            break;
            case 72://单独操作冻结金额
            case 33://续投奖励(预奖励)
            case 35://续投奖励(取消)
                $data['affect_money'] = $amoney;
                $data['account_money'] = $MM['account_money'];
                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze']+$amoney;
                $data['back_money'] = $MM['back_money'];
            break;
            case 71://单独操作可用余额
            default:
                $data['affect_money'] = $amoney;
                if (($MM['account_money']+$amoney)<=0) {
                    $data['account_money'] = 0;
                    $data['back_money'] = $MM['account_money']+$MM['back_money']+$amoney;
                } else {
                    $data['account_money'] = $MM['account_money']+$amoney;
                    $data['back_money'] = $MM['back_money'];
                }
                //$data['account_money'] = $MM['account_money']+$amoney;
                $data['collect_money'] = $MM['money_collect'];
                $data['freeze_money'] = $MM['money_freeze'];
                //$data['back_money'] = $MM['back_money'];
            break;
        }

    $newid = M('member_moneylog')->add($data);
        //帐户更新
        $mmoney['money_freeze']=$data['freeze_money'];
    $mmoney['money_collect']=$data['collect_money'];
    $mmoney['account_money']=$data['account_money'];
    $mmoney['back_money']=$data['back_money'];
    if ($newid) {
        $xid = M('member_money')->where("uid={$uid}")->save($mmoney);
    }
    if ($xid!==false) {
        $done = true;
        $Moneylog->commit();
    } else {
        $Moneylog->rollback();
    }
    return $done;
}

function memberLimitLog($uid, $type, $alimit, $info="")
{
    $xva = floatval($alimit);
    if (empty($xva)) {
        return true;
    }
    $done = false;
    $MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money", true)->find($uid);
    if (!is_array($MM)) {
        M("member_money")->add(array('uid'=>$uid));
        $MM = M("member_money")->field("money_freeze,money_collect,account_money,back_money", true)->find($uid);
    }
    $Moneylog = D('member_moneylog');
    if (in_array($type, array("71","72","73"))) {
        $type_save=7;
    } else {
        $type_save = $type;
    }

    $Moneylog->startTrans();

    $data['uid'] = $uid;
    $data['type'] = $type_save;
    $data['info'] = $info;
    $data['add_time'] = time();
    $data['add_ip'] = get_client_ip();

    $data['credit_limit'] = 0;
    $data['borrow_vouch_limit'] = 0;
    $data['invest_vouch_limit'] = 0;

    switch ($type) {
            case 1://信用标初审通过暂扣
            case 4://信用标复审未通过返回
            case 7://标的完成，返回
            case 12://流标，返回
                $_data['credit_limit'] = $alimit;
            break;
            case 2://担保标初审通过暂扣
            case 5://担保标复审未通过返回
            case 8://标的完成，返回
                $_data['borrow_vouch_limit'] = $alimit;
            break;
            case 3://参与担保暂扣
            case 6://所担保的标初审未通过，返回
            case 9://所担保的标复审未通过，返回
            case 10://标的完成，返回
                $_data['invest_vouch_limit'] = $alimit;
            break;
            case 11://VIP审核通过
                $_data['credit_limit'] = $alimit;
                $mmoney['credit_limit']=$MM['credit_limit'] + $_data['credit_limit'];
            break;
        }
    $data = array_merge($data, $_data);
    $newid = M('member_limitlog')->add($data);
        //帐户更新
        $mmoney['credit_cuse']=$MM['credit_cuse'] + $data['credit_limit'];
    $mmoney['borrow_vouch_cuse']=$MM['borrow_vouch_cuse'] + $data['borrow_vouch_limit'];
    $mmoney['invest_vouch_cuse']=$MM['invest_vouch_cuse'] + $data['invest_vouch_limit'];
    if ($newid) {
        $xid = M('member_money')->where("uid={$uid}")->save($mmoney);
    }
    if ($xid) {
        $Moneylog->commit();
        $done = true;
    } else {
        $Moneylog->rollback();
    }
    return $done;
}



function memberCreditsLog($uid, $type, $acredits, $info="无")
{
    if ($acredits==0) {
        return true;
    }
    $done = false;
    $mCredits = M("members")->getFieldById($uid, 'credits');
    $Creditslog = D('member_creditslog');
    $Creditslog->startTrans();
    $data['uid'] = $uid;
    $data['type'] = $type;
    $data['affect_credits'] = $acredits;
    $data['account_credits'] = $mCredits + $acredits;
    $data['info'] = $info;
    $data['add_time'] = time();
    $data['add_ip'] = get_client_ip();
    $newid = $Creditslog->add($data);

    $xid = M('members')->where("id={$uid}")->setField('credits', $data['account_credits']);

    if ($xid) {
        $Creditslog->commit() ;
        $done = true;
    } else {
        $Creditslog->rollback() ;
    }

    return $done;
}

function memberIntegralLog($uid, $type, $integral, $info="无")
{
    if ($integral==0) {
        return true;
    }
    $pre = C('DB_PREFIX');
    $done = false;

    $Db = new Model();
    $Db->startTrans(); //多表事务

    $Member = $Db->table($pre."members")->where("id=$uid")->find();

    $data['uid'] = $uid;
    $data['type'] = $type;
    $data['affect_integral'] = $integral;
    $data['active_integral'] = $integral + $Member['active_integral'];
    $data['account_integral'] = $integral + $Member['integral'];
    $data['info'] = $info;
    $data['add_time'] = time();
    $data['add_ip'] = get_client_ip();


    if ($integral<0 && $data['active_integral']<0) {//判断积分是否消费过头
        return false;
    } elseif ($integral<0 && $data['active_integral']>0) {//消费积分只减活跃积分，总积分不变
        $data['account_integral'] = $Member['integral'];
    }

    //消费积分为负数，消费积分只减活跃积分，不减总积分
    $newid = $Db->table($pre.'member_integrallog')->add($data);//积分细则
    $xid = $Db->table($pre."members")->where("id=$uid")->setInc('active_integral', $integral);//活跃积分总数
    if ($integral>0) {
        $yid = $Db->table($pre."members")->where("id=$uid")->setInc('integral', $integral);
    }//积分总数
    else {
        $yid = true;
    }

    if ($newid && $xid && $yid) {
        $Db->commit() ;
        $done = true;
    } else {
        $Db->rollback() ;
    }

    return $done;
}

function getMemberMoneySummary($uid)
{
    $pre = C('DB_PREFIX');
    $umoney = M('member_money')->field(true)->find($uid);

    $withdraw = M('member_withdraw')->field('withdraw_status,sum(withdraw_money) as withdraw_money,sum(second_fee) as second_fee')->where("uid={$uid}")->group("withdraw_status")->select();
    $withdraw_row = array();
    foreach ($withdraw as $wkey=>$wv) {
        $withdraw_row[$wv['withdraw_status']] = $wv;
    }
    $withdraw0 = $withdraw_row[0];
    $withdraw1 = $withdraw_row[1];
    $withdraw2 = $withdraw_row[2];

    $payonline = M('member_payonline')->where("uid={$uid} AND status=1")->sum('money');//累计充值金额

    $commission1 = M('borrow_investor')->where("investor_uid={$uid}")->sum('paid_fee');
    $commission2 = M('borrow_info')->where("borrow_uid={$uid} AND borrow_status in(2,4)")->sum('borrow_fee');//累计借款管理费

    $uplevefee = M('member_moneylog')->where("uid={$uid} AND type=2")->sum('affect_money');//充值总金额

    $czfee = M('member_payonline')->where("uid={$uid} AND status=1")->sum('fee');//在线充值手续费总金额

    $toubiaojl =M('borrow_investor')->where("borrow_uid ={$uid}")->sum('reward_money');//累计支付投标奖励
    $tuiguangjl =M('member_moneylog')->where("uid={$uid} and type=13")->sum('affect_money');//推广奖励
    $xianxiajl =M('member_moneylog')->where("uid={$uid} and type=32")->sum('affect_money');//线下充值奖励
    $xtjl = M('member_moneylog')->where("uid={$uid} and type=34")->sum('affect_money');//累计续投奖励  前台已放弃

    //企业直投代收金额及利息
    $circulation = M('transfer_borrow_investor')
                    ->field('sum(investor_capital)as investor_capital, sum(investor_interest) as investor_interest, sum(invest_fee) as invest_fee')
                    ->where('investor_uid='.$uid.' and status=1')
                    ->find();
    ///////////////////
    $moneylog = M("member_moneylog")->field("type,sum(affect_money) as money")->where("uid={$uid}")->group("type")->select();
    $list=array();
    foreach ($moneylog as $vs) {
        $list[$vs['type']]['money']= ($vs['money']>0)?$vs['money']:$vs['money']*(-1);
    }

    $tx = M('member_withdraw')->field("uid,sum(withdraw_money) as withdraw_money,sum(second_fee) as second_fee")->where("uid={$uid} and withdraw_status=2")->group("uid")->select();
    foreach ($tx as $vt) {
        $list['tx']['withdraw_money']= $vt['withdraw_money'];    //成功提现金额
        $list['tx']['withdraw_fee']= $vt['second_fee'];    //提现手续费
    }

    ////////////////////////////

    $capitalinfo = getMemberBorrowScan($uid);
    $money['zye'] = $umoney['account_money'] + $umoney['back_money']+$umoney['money_collect'] + $umoney['money_freeze'];//帐户总额
    $money['kyxjje'] = $umoney['account_money']+ $umoney['back_money'];//可用金额
    $money['djje'] = $umoney['money_freeze'];//冻结金额
    $money['jjje'] = 0;//奖金金额
    //$umoney['money_collect'];//待收本金+待收利息
    $money['dsbx'] = $capitalinfo['tj']['dsze']+$capitalinfo['tj']['willgetInterest']
                    +$circulation['investor_capital']+$circulation['investor_interest']-$circulation['invest_fee'];

    $money['dfbx'] = $capitalinfo['tj']['dhze'];//待付本息
    $money['dxrtb'] = $capitalinfo['tj']['dqrtb'];//待确认投标
    $money['dshtx'] = $withdraw0['withdraw_money'];//待审核提现
    $money['clztx'] = $withdraw1['withdraw_money'];//处理中提现
    $money['total_1'] = $money['kyxjje']+$money['jjje']+$money['dsbx']-$money['dfbx']+$money['dxrtb']+$money['dshtx']+$money['clztx'];

    $money['jzlx'] = $capitalinfo['tj']['earnInterest'];//净赚利息
    $money['jflx'] = $capitalinfo['tj']['payInterest'];//净付利息
    //$money['ljjj'] = $umoney['reward_money'];//累计收到奖金
    $money['xtjj'] = $list['34']['money']+$list[40]['money'];//$xtjl;//累计续投奖金
    $money['ljhyf'] = $list['14']['money']+$list['22']['money']+$list['25']['money']+$list['26']['money'];//$uplevefee;//累计支付会员费
    $money['ljtxsxf'] = $list['tx']['withdraw_fee'];//$withdraw2['withdraw_fee'];//累计提现手续费
    $money['ljczsxf'] = $czfee;//累计充值手续费

    $money['ljtbjl'] = $list['20']['money']+$list[41]['money'];//$toubiaojl;//累计投标奖励
    $money['ljtgjl'] = $list['13']['money'];//$tuiguangjl;//累计推广奖励
    $money['xxjl'] = $list['32']['money'];//$xianxiajl;//线下充值奖励
    $money['jkglf'] =$list['18']['money'];//借款管理费
    $money['yqf'] = $list['30']['money']+$list['31']['money'];//逾期罚息及催收费
    $money['zftbjl'] = $toubiaojl;//支付投标奖励
    $money['total_2'] = $money['jzlx']
                        -$money['jflx']
                        -$money['ljhyf']
                        -$money['ljtxsxf']
                        -$money['ljczsxf']
                        +$money['ljtbjl']
                        +$money['ljtgjl']
                        +$money['xxjl']
                        +$money['xtjj']
                        -$money['jkglf']
                        -$money['yqf']
                        -$money['zftbjl'];

    $money['ljtzje'] = $capitalinfo['tj']['borrowOut'];//累计投资金额
    $money['ljjrje'] = $capitalinfo['tj']['borrowIn'];//累计借入金额
    $money['ljczje'] = $payonline;//累计充值金额
    $money['ljtxje'] = $withdraw2['withdraw_money'];//累计提现金额
    $money['ljzfyj'] = $commission1 + $commission2;//累计支付佣金

    $money['dslxze'] = $capitalinfo['tj']['willgetInterest'] + $circulation['investor_interest'];//待收利息总额
    $money['dflxze'] = $capitalinfo['tj']['willpayInterest'];//待付利息总额

    return $money;
}

/**
 * @param int $borrowid
 * @param $uid
 * @return array|void
 */
function getBorrowInvest($borrowid=0, $uid)
{
    if (empty($borrowid)) {
        return;
    }
    $vx = M("borrow_info")->field('id')->where("id={$borrowid} AND borrow_uid={$uid}")->find();
    if (!is_array($vx)) {
        return;
    }

    $binfo = M("borrow_info")->field('borrow_name,borrow_uid,borrow_type,borrow_duration,repayment_type,has_pay,total,deadline,product_type')->find($borrowid);
    $list = array();
    switch ($binfo['repayment_type']) {
        case 1://一次性还款
        case 5://一次性还款
                $field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline";
                $vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=1 and status!=-1  AND is_debt = 0")->group('sort_order')->find();
                //$status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
                $status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
                $vo['deadline']=cal_deadline($vo['borrow_id']);//修正N+M模式错误
                ///////////////////
                if ($vo['deadline']<time() && $vo['status']==7) {
                    $vo['status'] ='逾期未还';
                    import("@.conf.borrow_expired");
                    $expired=new borrow_expired($borrowid);
                    $vo['expired__money']=$expired->get_expired__money();
                } else {
                    if ($vo['status']==5) {
                        import("@.conf.borrow_expired");
                        $vo['expired__money']=borrow_expired::get_over_expired__money($borrowid);
                    } else {
                        $vo['expired__money']=0;
                    }
                    $vo['status'] = $status_arr[$vo['status']];
                }
                $return_info=cal_repayment_money($borrowid, 1, 1);
                $r_info=explode("=", $return_info);
                $vo["money"]=$r_info[1];
                ///////////////////
                //$vo['status'] = $status_arr[$vo['status']];
                //$vo['needpay'] = getFloatValue(sprintf("%.2f",($vo['interest']+$vo['capital']-$vo['paid'])),2);
                $vo['needpay'] = sprintf("%.2f", ($vo['interest']+$vo['capital']-$vo['paid']));
                $list[] = $vo;
        break;
        default://每月还款
            for ($i=1;$i<=$binfo['borrow_duration'];$i++) {
                $field = "borrow_id,sort_order,sum(capital) as capital,sum(interest) as interest,status,sum(receive_interest+receive_capital+if(receive_capital>=0,interest_fee,0)) as paid,deadline,is_debt";
                $vo = M("investor_detail")->field($field)->where("borrow_id={$borrowid} AND `sort_order`=$i and status!=-1 AND is_debt = 0 ")->group('sort_order')->find();
                $status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
                ///////////////////
                if ($vo['deadline']<time() && $vo['status']==7) {
                    $vo['status'] ='逾期未还';
                    import("@.conf.borrow_expired");
                    $expired=new borrow_expired($borrowid, $i);
                    $vo['expired__money']=$expired->get_expired__money();
                } else {
                    if ($vo['status']==5) {
                        import("@.conf.borrow_expired");
                        $vo['expired__money']=borrow_expired::get_over_expired__money($borrowid, $i);
                    } else {
                        $vo['expired__money']=0;
                    }
                    $vo['status'] = $status_arr[$vo['status']];
                }
                $return_info=cal_repayment_money($borrowid, $i, 1);
                $r_info=explode("=", $return_info);
                $vo["money"]=$r_info[1];
                ///////////////////
                //$vo['status'] = $status_arr[$vo['status']];
                $vo['needpay'] = sprintf("%.2f", ($vo['interest']+$vo['capital']-$vo['paid']));
                $list[] = $vo;
            }
        break;
    }
    $row=array();
    $row['list'] = $list;
    $row['name'] = $binfo['borrow_name'];
    $row['product_type'] = $binfo['product_type'];
    $row['repayment_type'] = $binfo['repayment_type'];
    return $row;
}

function getDurationCount($uid=0)
{
    if (empty($uid)) {
        return;
    }
    $pre = C('DB_PREFIX');

    $field = "d.status,d.repayment_time";
    $sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where  d.status!=-1 and d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_uid={$uid}) group by d.borrow_id, d.sort_order";
    $list = M()->query($sql);

    $week_1 = array(strtotime("-7 day", strtotime(date("Y-m-d", time())." 00:00:00")),strtotime(date("Y-m-d", time())." 23:59:59"));
    $time_1 = array(strtotime("-1 month", strtotime(date("Y-m-d", time())." 00:00:00")),strtotime(date("Y-m-d", time())." 23:59:59"));
    $time_6 = array(strtotime("-6 month", strtotime(date("Y-m-d", time())." 00:00:00")),strtotime(date("Y-m-d", time())." 23:59:59"));
    $row_time_1=array();
    $row_time_2=array();
    $row_time_3=array();
    $row_time_4=array();
    foreach ($list as $v) {
        switch ($v['status']) {
            case 1:
                if ($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]) {
                    $row_time_3['zc'] = $row_time_3['zc'] + 1;//6个月内
                    if ($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) {
                        $row_time_1['zc'] = $row_time_1['zc'] + 1;
                    }//一周内
                    if ($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) {
                        $row_time_2['zc'] = $row_time_2['zc'] + 1;
                    }//一个月内
                }
                $row_time_4['zc'] = $row_time_4['zc'] + 1;//所有
            break;
            case 2:
                if ($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]) {
                    $row_time_3['tq'] = $row_time_3['tq'] + 1;//6个月内
                    if ($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) {
                        $row_time_1['tq'] = $row_time_1['tq'] + 1;
                    }//一周内
                    if ($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) {
                        $row_time_2['tq'] = $row_time_2['tq'] + 1;
                    }//一个月内
                }
                $row_time_4['tq'] = $row_time_4['tq'] + 1;//所有
            break;
            case 3:
                if ($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]) {
                    $row_time_3['ch'] = $row_time_3['ch'] + 1;//6个月内
                    if ($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) {
                        $row_time_1['ch'] = $row_time_1['ch'] + 1;
                    }//一周内
                    if ($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) {
                        $row_time_2['ch'] = $row_time_2['ch'] + 1;
                    }//一个月内
                }
                $row_time_4['ch'] = $row_time_4['ch'] + 1;//所有
            break;
            case 5:
                if ($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]) {
                    $row_time_3['yq'] = $row_time_3['yq'] + 1;//6个月内
                    if ($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) {
                        $row_time_1['yq'] = $row_time_1['yq'] + 1;
                    }//一周内
                    if ($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) {
                        $row_time_2['yq'] = $row_time_2['yq'] + 1;
                    }//一个月内
                }

                $row_time_4['yq'] = $row_time_4['yq'] + 1;//所有
            break;
            case 6:
                if ($v['repayment_time']>$time_6[0] && $v['repayment_time']<$time_6[1]) {
                    $row_time_3['wh'] = $row_time_3['wh'] + 1;//6个月内
                    if ($v['repayment_time']>$week_1[0] && $v['repayment_time']<$week_1[1]) {
                        $row_time_1['wh'] = $row_time_1['wh'] + 1;
                    }//一周内
                    if ($v['repayment_time']>$time_1[0] && $v['repayment_time']<$time_1[1]) {
                        $row_time_2['wh'] = $row_time_2['wh'] + 1;
                    }//一个月内
                }
                $row_time_4['wh'] = $row_time_4['wh'] + 1;//所有
            break;

        }
    }
    $row['history1'] = $row_time_1;
    $row['history2'] = $row_time_2;
    $row['history3'] = $row_time_3;
    $row['history4'] = $row_time_4;
    return $row;
}


function getMemberBorrow($uid=0, $size=10)
{
    if (empty($uid)) {
        return;
    }
    $pre = C('DB_PREFIX');

    $field = "b.borrow_name,d.total,d.borrow_id,d.sort_order,sum(d.capital) as capital,sum(d.interest) as interest,d.status,sum(d.receive_interest+d.receive_capital+if(d.receive_capital>=0,d.interest_fee,0)) as paid,d.deadline";
    $sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.status!=-1 and  d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_status=6 AND tb.borrow_uid={$uid}) AND d.repayment_time=0 group by d.sort_order, d.borrow_id order by  d.borrow_id,d.sort_order limit 0,10";
    //$sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_uid={$uid} AND d.status=0 group by d.sort_order limit 0,10";
    $list = M()->query($sql);
    $status_arr =array('还未还','已还完','已提前还款','迟到还款','网站代还本金','逾期还款','','待还');
    foreach ($list as $key=>$v) {
        //$list[$key]['status'] = $status_arr[$v['status']];

        if ($v['deadline']<time() && $v['status']==7) {
            $list[$key]['status'] ='逾期未还';
        } else {
            $list[$key]['status'] = $status_arr[$v['status']];
        }
    }
    $row=array();
    $row['list'] = $list;
    return $row;
}

function getLeftTime($timeend, $type=1)
{
    if ($type==1) {
        $timeend = strtotime(date("Y-m-d", $timeend)." 23:59:59");
        $timenow = strtotime(date("Y-m-d", time())." 23:59:59");
        $left = ceil(($timeend-$timenow)/3600/24);
    } else {
        $left_arr = timediff(time(), $timeend);
        $left = $left_arr['day']."天 ".$left_arr['hour']."小时 ".$left_arr['min']."分钟 ".$left_arr['sec']."秒";
    }
    return $left;
}

function timediff($begin_time, $end_time)
{
    if ($begin_time < $end_time) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    $timediff = $endtime - $starttime;
    $days = intval($timediff / 86400);
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600);
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    $secs = $remain % 60;
    $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
    return $res;
}

function addInnerMsg($uid, $title, $msg)
{
    if (empty($uid)) {
        return;
    }
    $data['uid'] = $uid;
    $data['title'] = $title;
    $data['msg'] = $msg;
    $data['send_time'] = time();
    M('inner_msg')->add($data);
}


//获取下级或者同级栏目列表
function getTypeList($parm)
{
    if (isset($parm["order"])) {
        $Osql=$parm["order"];
    } else {
        $Osql="sort_order DESC";
    }
    $field="id,type_name,type_set,add_time,type_url,type_nid,parent_id";
    //查询条件
    $Lsql="{$parm['limit']}";
    $pc = D('navigation')->where("parent_id={$parm['type_id']} and model='navigation'")->count('id');
    if ($pc>0) {
        $map['is_hiden'] = 0;
        $map['parent_id'] = $parm['type_id'];
        $map['model']  = 'navigation';
        $data = D('navigation')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    } elseif (!isset($parm['notself'])) {
        $map['is_hiden'] = 0;
        $map['parent_id'] = D('Acategory')->getFieldById($parm['type_id'], 'parent_id');
        $data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    }

    //链接处理
    $typefix = get_type_leve_nid($parm['type_id']);
    $typeu = $typefix[0];
    $suffix=C("URL_HTML_SUFFIX");
    foreach ($data as $key=>$v) {
        if ($v['type_set']==2) {
            if (empty($v['type_url'])) {
                $data[$key]['turl']="javascript:alert('请在后台添加此栏目链接');";
            } else {
                $data[$key]['turl'] = $v['type_url'];
            }
        } elseif ($parm['model']=='navigation'||($v['parent_id']==0)) {
            $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index", "typelist", array("suffix"=>$suffix));
        } elseif ($parm['model']=='article'||($v['parent_id']==0)) {
            $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index", "typelist", array("suffix"=>$suffix));
        } else {
            $data[$key]['turl'] = MU("Home/{$typeu}/{$v['type_nid']}", "typelist", array("suffix"=>$suffix));
        }
    }
    $row=array();
    $row = $data;

    return $row;
}

//获取下级或者同级栏目列表 文章栏目
function getTypeListActa($parm)
{
    //if(empty($parm['type_id'])) return;
    $Osql="sort_order DESC";
    $field="id,type_name,type_set,add_time,type_url,type_nid,parent_id";
    //查询条件
    $Lsql="{$parm['limit']}";
    //$pc = D('Acategory')->where("parent_id={$parm['type_id']} and model='navigation'")->count('id');
    $pc = D('Acategory')->where("parent_id={$parm['type_id']} and model='article'")->count('id');
    if ($pc>0) {
        $map['is_hiden'] = 0;
        $map['parent_id'] = $parm['type_id'];
        $map['model']  = 'article';
        //$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
        $data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    } elseif (!isset($parm['notself'])) {
        $map['is_hiden'] = 0;
        $map['parent_id'] = D('Acategory')->getFieldById($parm['type_id'], 'parent_id');
        //$data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
        $data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    }

    //链接处理
    $typefix = get_type_leve_nid($parm['type_id']);
    $typeu = $typefix[0];
    $suffix=C("URL_HTML_SUFFIX");
    foreach ($data as $key=>$v) {
        if ($v['type_set']==2) {
            if (empty($v['type_url'])) {
                $data[$key]['turl']="javascript:alert('请在后台添加此栏目链接');";
            } else {
                $data[$key]['turl'] = $v['type_url'];
            }
        }
        //elseif($parm['type_id']==0||($v['parent_id']==0&&count($typefix)==1)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
        elseif ($parm['model']=='article'||($v['parent_id']==0)) {
            $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index", "typelist", array("suffix"=>$suffix));
        } else {
            $data[$key]['turl'] = MU("Home/{$typeu}/{$v['type_nid']}", "typelist", array("suffix"=>$suffix));
        }
    }
    $row=array();
    $row = $data;

    return $row;
}
//新标提醒
function newTip($borrow_id)
{
    $binfo = M("borrow_info")->field('borrow_type,add_time,borrow_interest_rate,borrow_duration')->find();

    if ($binfo['borrow_type']==3) {
        $map['borrow_type'] = 3;
    } else {
        $map['borrow_type'] = 0;
    }
    $tiplist = M("borrow_tip")->field(true)->where($map)->select();

    foreach ($tiplist as $key=>$v) {
        $minfo = M('members m')->field('mm.account_money,mm.back_money,m.user_phone')->join('lzh_member_money mm on m.id=mm.uid')->find($v['uid']);
        if (
        $binfo['borrow_interest_rate'] >= $v['interest_rate'] &&
        $binfo['borrow_duration'] >= $v['doration_from'] &&
        $binfo['borrow_duration'] <= $v['doration_to'] &&
        ($minfo['account_money']+ $minfo['back_money'])>= $v['account_money']
        ) {
            (empty($tipPhone))?$tipPhone .="{$v['user_phone']}":$tipPhone .=",{$v['user_phone']}";
        }
    }
    $smsTxt = FS("Webconfig/smstxt");
    $smsTxt=de_xie($smsTxt);

    sendsms($tipPhone, $smsTxt['newtip']);
}

// 快到期标提醒
function expire()
{
    $_P_fee = get_global_setting();
    // 相差五天之间的数据
    $atdate = strtotime('-7 day');
    $etdate = strtotime('-3 day');
    $binfo = M("borrow_info")->where('add_time >'.$atdate.' and add_time <'.$etdate)->select();
    $borrowName = array();
    foreach ($binfo as $list) {
        $borrowName[] = $list['borrow_name'];
    }
    $borrowName = implode('，', $borrowName);
    sendemail($_P_fee['tx_email'], '链金所即将到期标提醒', '标题：'.$borrowName);
}

//参数说明 还款类型  借款金额  借款期限 借款利率
function getBorrowInterest($type, $money, $duration, $rate)
{
    //if(!in_array($type,C('REPAYMENT_TYPE'))) return $money;
    //echo $month_rate."|".$rate."|".$duration."|".$type;
    switch ($type) {
        case 1://按天到期还款
            $day_rate =  $rate/36000;//计算出天标的天利率
            $interest = getFloatValue($money*$day_rate*$duration, 4); //字数字格式化保留小数点后4位

        break;
        case 2://按月分期还款
            $parm['duration'] = $duration;
            $parm['money'] = $money;
            $parm['year_apr'] = $rate;
            $parm['type'] = "all";
            $intre = EqualMonth($parm);
            $interest = ($intre['repayment_money'] - $money);
        break;
        case 3://按季分期还款
            $parm['month_times'] = $duration;
            $parm['account'] = $money;
            $parm['year_apr'] = $rate;
            $parm['type'] = "all";
            $intre = EqualSeason($parm);
            $interest = $intre['interest'];
        break;
        case 4://每月还息到期还本
            $parm['month_times'] = $duration;
            $parm['account'] = $money;
            $parm['year_apr'] = $rate;
            $parm['type'] = "all";
            $intre = EqualEndMonth($parm);
            $interest = $intre['interest'];
        break;
        case 5://一次性到期还款
            $parm['month_times'] = $duration;
            $parm['account'] = $money;
            $parm['year_apr'] = $rate;
            $parm['type'] = "all";
            $intre = EqualEndMonthOnly($parm);
            $interest = $intre['interest'];
        break;
        case 7:
            $parm['duration'] = $duration;
            $parm['money'] = $money;
            $parm['year_apr'] = $rate;
            $parm['type'] = "all";
            $intre = EqualMonth1($parm);
            $interest = ($intre['repayment_money'] - $money);
        break;

    }
    return $interest;
}
//等本降息

/*
money,year_apr,duration,borrow_time(用来算还款时间的),type(==all时，返回还款概要)

*/
function EqualMonth1($data = array())
{
    if (isset($data['money']) && $data['money']>0) {
        $account = $data['money'];
    } else {
        return "";
    }

    if (isset($data['year_apr']) && $data['year_apr']>0) {
        $year_apr = $data['year_apr'];
    } else {
        return "";
    }

    if (isset($data['duration']) && $data['duration']>0) {
        $duration = $data['duration'];
    }
    if (isset($data['borrow_time']) && $data['borrow_time']>0) {
        $borrow_time = $data['borrow_time'];
    } else {
        $borrow_time = time();
    }
    $month_apr = $year_apr/(12*100);
    //$_li = pow((1+$month_apr),$duration);
    $month_money = $account/$duration;
    $repayment = round(($account*$duration - $month_money*(($duration*($duration-1))/2))*$month_apr, 4);//round($account * ($month_apr * $_li)/($_li-1),4);
    $_result = array();
    if (isset($data['type']) && $data['type']=="all") {
        $_result['repayment_money'] = $month_money*$duration+$repayment;
        $_result['monthly_repayment'] = $repayment;
        $_result['month_apr'] = round($month_apr*100, 4);
    } else {
        //$re_month = date("n",$borrow_time);
        for ($i=0;$i<$duration;$i++) {
            if ($i==0) {
                $interest = round($account*$month_apr, 4);
            } else {
                $interest = round(($account-$month_money*$i)*$month_apr, 4);//round(($account*$month_apr - $repayment)*$_lu + $repayment,4);
            }
            $_result[$i]['repayment_money'] = getFloatValue($month_money+$interest, 4);
            $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
            $_result[$i]['interest'] = getFloatValue($interest, 4);
            $_result[$i]['capital'] = getFloatValue($month_money, 4);
        }
    }
    return $_result;
}
//等额本息法
//贷款本金×月利率×（1+月利率）还款月数/[（1+月利率）还款月数-1]
//a*[i*(1+i)^n]/[(1+I)^n-1]
//（a×i－b）×（1＋i）
/*
money,year_apr,duration,borrow_time(用来算还款时间的),type(==all时，返回还款概要)

*/
function EqualMonth($data = array())
{
    if (isset($data['money']) && $data['money']>0) {
        $account = $data['money'];
    } else {
        return "";
    }

    if (isset($data['year_apr']) && $data['year_apr']>0) {
        $year_apr = $data['year_apr'];
    } else {
        return "";
    }

    if (isset($data['duration']) && $data['duration']>0) {
        $duration = $data['duration'];
    }
    if (isset($data['borrow_time']) && $data['borrow_time']>0) {
        $borrow_time = $data['borrow_time'];
    } else {
        $borrow_time = time();
    }
    $month_apr = $year_apr/(12*100);
    $_li = pow((1+$month_apr), $duration);
    $repayment = round($account * ($month_apr * $_li)/($_li-1), 4);
    $_result = array();
    if (isset($data['type']) && $data['type']=="all") {
        $_result['repayment_money'] = $repayment*$duration;
        $_result['monthly_repayment'] = $repayment;
        $_result['month_apr'] = round($month_apr*100, 4);
    } else {
        //$re_month = date("n",$borrow_time);
        for ($i=0;$i<$duration;$i++) {
            if ($i==0) {
                $interest = round($account*$month_apr, 4);
            } else {
                $_lu = pow((1+$month_apr), $i);
                $interest = round(($account*$month_apr - $repayment)*$_lu + $repayment, 4);
            }
            $_result[$i]['repayment_money'] = getFloatValue($repayment, 4);
            $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
            $_result[$i]['interest'] = getFloatValue($interest, 4);
            $_result[$i]['capital'] = getFloatValue($repayment-$interest, 4);
        }
    }
    return $_result;
}

//按季等额本息法
function EqualSeason($data = array())
{
    //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0) {
      $month_times = $data['month_times'];
  }
  //按季还款必须是季的倍数
  if ($month_times%3!=0) {
      return false;
  }
  //借款的总金额
  if (isset($data['account']) && $data['account']>0) {
      $account = $data['account'];
  } else {
      return "";
  }
  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0) {
      $year_apr = $data['year_apr'];
  } else {
      return "";
  }

  //借款的时间 --- 什么时候开始借款，计算还款的
  if (isset($data['borrow_time']) && $data['borrow_time']>0) {
      $borrow_time = $data['borrow_time'];
  } else {
      $borrow_time = time();
  }
    $season_apr = $year_apr/(4*100);
    //得到总季数
    $_season = $month_times/3;
    $_li = pow((1+$season_apr), $_season);

    $repayment = round($account * ($season_apr * $_li)/($_li-1), 2);
    $_result = array();
    if (isset($data['type']) && $data['type']=="all") {
        $_result['repayment_money'] = $repayment*$_season;
        $_result['monthly_repayment'] = $repayment;
        $_result['month_apr'] = round($season_apr*100, 4);
        $_result['interest'] = $_result['repayment_money']-$account;
    } else {
        $_yes_account=0;
        $repayment_account = 0;//总还款额
        for ($i=0;$i<$month_times;$i++) {
            $repay = $account - $_yes_account;//应还的金额
            $interest = round(($repay*$season_apr)/3, 2);//利息等于应还金额乘季利率
            $repayment_account = $repayment_account+$interest;//总还款额+利息
            $capital = 0;
            if ($i%3==2) {
                $capital = $repayment-$interest*3;//本金只在第三个月还，本金等于借款金额除季度
                $_yes_account = $_yes_account+$capital;
                $repay = $account - $_yes_account;
                $repayment_account = $repayment_account+$capital;//总还款额+本金
            }

            $_result[$i]['repayment_money'] = getFloatValue($interest+$capital, 2);
            $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
            $_result[$i]['interest'] = getFloatValue($interest, 2);
            $_result[$i]['capital'] = getFloatValue($capital, 2);
        }
    }
    return $_result;
}
//按季等本降息息法
function EqualSeason1($data = array())
{
    //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0) {
      $month_times = $data['month_times'];
  }
  //按季还款必须是季的倍数
  if ($month_times%3!=0) {
      return false;
  }
  //借款的总金额
  if (isset($data['account']) && $data['account']>0) {
      $account = $data['account'];
  } else {
      return "";
  }
  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0) {
      $year_apr = $data['year_apr'];
  } else {
      return "";
  }

  //借款的时间 --- 什么时候开始借款，计算还款的
  if (isset($data['borrow_time']) && $data['borrow_time']>0) {
      $borrow_time = $data['borrow_time'];
  } else {
      $borrow_time = time();
  }

  //月利率
  $month_apr = $year_apr/(12*100);

  //得到总季数
  $_season = $month_times/3;

  //每季应还的本金
  $_season_money = round($account/$_season, 4);

  //$re_month = date("n",$borrow_time);
  $_yes_account = 0 ;
    $repayment_account = 0;//总还款额
  $_all_interest = 0;//总利息
  for ($i=0;$i<$month_times;$i++) {
      $repay = $account - $_yes_account;//应还的金额

      $interest = round($repay*$month_apr, 4);//利息等于应还金额乘月利率
      $repayment_account = $repayment_account+$interest;//总还款额+利息
      $capital = 0;
      if ($i%3==2) {
          $capital = $_season_money;//本金只在第三个月还，本金等于借款金额除季度
          $_yes_account = $_yes_account+$capital;
          $repay = $account - $_yes_account;
          $repayment_account = $repayment_account+$capital;//总还款额+本金
      }

      $_result[$i]['repayment_money'] = getFloatValue($interest+$capital, 4);
      $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
      $_result[$i]['interest'] = getFloatValue($interest, 4);
      $_result[$i]['capital'] = getFloatValue($capital, 4);
      $_all_interest += $interest;
  }
    if (isset($data['type']) && $data['type']=="all") {
        $_resul['repayment_money'] = $repayment_account;
        $_resul['monthly_repayment'] = round($repayment_account/$_season, 4);
        $_resul['month_apr'] = round($month_apr*100, 4);
        $_resul['interest'] = $_all_interest;
        return $_resul;
    } else {
        return $_result;
    }
}

//到期还本，按月付息
function EqualEndMonth($data = array())
{

  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0) {
      $month_times = $data['month_times'];
  }

  //借款的总金额
  if (isset($data['account']) && $data['account']>0) {
      $account = $data['account'];
  } else {
      return "";
  }

  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0) {
      $year_apr = $data['year_apr'];
  } else {
      return "";
  }


  //借款的时间
  if (isset($data['borrow_time']) && $data['borrow_time']>0) {
      $borrow_time = $data['borrow_time'];
  } else {
      $borrow_time = time();
  }

  //月利率
  $month_apr = $year_apr/(12*100);



  //$re_month = date("n",$borrow_time);
  $_yes_account = 0 ;
    $repayment_account = 0;//总还款额
  $_all_interest=0;

    $interest = round($account*$month_apr, 4);//利息等于应还金额乘月利率
  for ($i=0;$i<$month_times;$i++) {
      $capital = 0;
      if ($i+1 == $month_times) {
          $capital = $account;//本金只在最后一个月还，本金等于借款金额除季度
      }

      $_result[$i]['repayment_account'] = $interest+$capital;
      $_result[$i]['repayment_time'] = get_times(array("time"=>$borrow_time,"num"=>$i+1));
      $_result[$i]['interest'] = $interest;
      $_result[$i]['capital'] = $capital;
      $_all_interest += $interest;
  }
    if (isset($data['type']) && $data['type']=="all") {
        $_resul['repayment_account'] = $account + $interest*$month_times;
        $_resul['monthly_repayment'] = $interest;
        $_resul['month_apr'] = round($month_apr*100, 4);
        $_resul['interest'] = $_all_interest;
        return $_resul;
    } else {
        return $_result;
    }
}

/////////////////////////////////////////一次性还款//////////////////////////////////////
//到期还本，按月付息
function EqualEndMonthOnly($data = array())
{

  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0) {
      $month_times = $data['month_times'];
  }

  //借款的总金额
  if (isset($data['account']) && $data['account']>0) {
      $account = $data['account'];
  } else {
      return "";
  }

  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0) {
      $year_apr = $data['year_apr'];
  } else {
      return "";
  }

  //借款的时间
  if (isset($data['borrow_time']) && $data['borrow_time']>0) {
      $borrow_time = $data['borrow_time'];
  } else {
      $borrow_time = time();
  }

  //月利率
  $month_apr = $year_apr/(12*100);

    $interest = getFloatValue($account*$month_apr*$month_times, 4);//利息等于应还金额*月利率*借款月数

  if (isset($data['type']) && $data['type']=="all") {
      $_resul['repayment_account'] = $account + $interest;
      $_resul['monthly_repayment'] = $interest;
      $_resul['month_apr'] = round($month_apr*100, 4);
      $_resul['interest'] = $interest;
      $_resul['capital'] = $account;
      return $_resul;
  }
}

///////////////////////////////////////////////////////////////////////////////////////////
function getMinfo($uid, $field='m.id,m.reg_time,m.pin_pass,mm.account_money,mm.back_money,m.user_phone,mi.real_name,mi.idcard,m.user_regtype')
{
    $pre = C('DB_PREFIX');
    $vm = M("members m")->field($field)->join("{$pre}member_money mm ON mm.uid=m.id")->join("{$pre}member_info mi ON mi.uid=m.id")->where("m.id={$uid}")->find();
    // $vm['account_money'] .= $vm['account_money'] + $vm['reward_money'];
    return $vm;
}

//获取借款列表
function getMemberInfoDone($uid)
{
    $pre = C('DB_PREFIX');

    $field = "m.id,m.id as uid,m.user_name,mbank.uid as mbank_id,mi.uid as mi_id,mhi.uid as mhi_id,mci.uid as mci_id,mdpi.uid as mdpi_id,mei.uid as mei_id,mfi.uid as mfi_id,s.phone_status,s.id_status,s.email_status,s.safequestion_status";
    $row = M('members m')->field($field)
    ->join("{$pre}member_banks mbank ON m.id=mbank.uid")
    ->join("{$pre}member_contact_info mci ON m.id=mci.uid")
    ->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")
    ->join("{$pre}member_house_info mhi ON m.id=mhi.uid")
    ->join("{$pre}member_ensure_info mei ON m.id=mei.uid")
    ->join("{$pre}member_info mi ON m.id=mi.uid")
    ->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")
    ->join("{$pre}members_status s ON m.id=s.uid")
    ->where("m.id={$uid}")->find();
    $is_data = M('member_data_info')->where("uid={$row['uid']}")->count("id");
    $i=0;
    if ($row['mbank_id']>0) {
        $i++;
        $row['mbank'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mbank'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mci_id']>0) {
        $i++;
        $row['mci'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mci'] = "<span style='color:black'>未填写</span>";
    }

    if ($is_data>0) {
        $row['mdi_id'] = $is_data;
        $row['mdi'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mdi'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mhi_id']>0) {
        $i++;
        $row['mhi'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mhi'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mdpi_id']>0) {
        $i++;
        $row['mdpi'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mdpi'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mei_id']>0) {
        $i++;
        $row['mei'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mei'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mfi_id']>0) {
        $i++;
        $row['mfi'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mfi'] = "<span style='color:black'>未填写</span>";
    }

    if ($row['mi_id']>0) {
        $i++;
        $row['mi'] = "<span style='color:green'>已填写</span>";
    } else {
        $row['mi'] = "<span style='color:black'>未填写</span>";
    }

    $row['i'] = $i;//7为完成
    return $row;
}

function getMemberBorrowScan($uid)
{
    //借款次数相关
    $field="borrow_status,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money";
    $borrowNum=M('borrow_info')->field($field)->where("borrow_uid = {$uid}")->group('borrow_status')->select();
    foreach ($borrowNum as $v) {
        $borrowCount[$v['borrow_status']] = $v;
    }
    //借款次数相关
    //还款情况相关
    $field="status,sort_order,borrow_id,sum(capital) as capital,sum(interest) as interest";
    $repaymentNum=M('investor_detail')->field($field)->where("borrow_uid = {$uid} and status!=-1")->group('sort_order,borrow_id')->select();
    foreach ($repaymentNum as $v) {
        $repaymentStatus[$v['status']]['capital']+=$v['capital'];//当前状态下的数金额
        $repaymentStatus[$v['status']]['interest']+=$v['interest'];//当前状态下的数金额
        $repaymentStatus[$v['status']]['num']++;//当前状态下的总笔数
    }
    //还款情况相关
    //借出情况相关
    $field="status,count(id) as num,sum(investor_capital) as investor_capital,sum(reward_money) as reward_money,sum(investor_interest) as investor_interest,sum(receive_capital) as receive_capital,sum(receive_interest) as receive_interest,sum(invest_fee) as invest_fee";
    $investNum=M('borrow_investor')->field($field)->where("investor_uid = {$uid}")->group('status')->select();
    $_reward_money = 0;
    foreach ($investNum as $v) {
        $investStatus[$v['status']]=$v;
        $_reward_money+=floatval($v['reward_money']);
    }
    //借出情况相关
    //逾期的借入
    $field="borrow_id,sort_order,sum(`capital`) as capital,count(id) as num";
    $expiredNum=M('investor_detail')->field($field)->where("`repayment_time`=0 and borrow_uid={$uid} AND status=7 and `deadline`<".time()." ")->group('borrow_id,sort_order')->select();
    $_expired_money = 0;
    foreach ($expiredNum as $v) {
        $expiredStatus[$v['borrow_id']][$v['sort_order']]=$v;
        $_expired_money+=floatval($v['capital']);
    }
    $rowtj['expiredMoney'] = getFloatValue($_expired_money, 2);//逾期金额
    $rowtj['expiredNum'] = count($expiredNum);//逾期期数
    //逾期的借入
    //逾期的投资
    $field="borrow_id,sort_order,sum(`capital`) as capital,count(id) as num";
    $expiredInvestNum=M('investor_detail')->field($field)->where("`repayment_time`=0 and `deadline`<".time()." and investor_uid={$uid} AND status <> 0  and status!=-1")->group('borrow_id,sort_order')->select();
    $_expired_invest_money = 0;
    foreach ($expiredInvestNum as $v) {
        $expiredInvestStatus[$v['borrow_id']][$v['sort_order']]=$v;
        $_expired_invest_money+=floatval($v['capital']);
    }
    $rowtj['expiredInvestMoney'] = getFloatValue($_expired_invest_money, 2);//逾期金额
    $rowtj['expiredInvestNum'] = count($expiredInvestNum);//逾期期数
    //逾期的投资

    $rowtj['jkze'] = getFloatValue(floatval($borrowCount[6]['money']+$borrowCount[7]['money']+$borrowCount[8]['money']+$borrowCount[9]['money']), 2);//借款总额
    $rowtj['yhze'] = getFloatValue(floatval($borrowCount[6]['repayment_money']+$borrowCount[7]['repayment_money']+$borrowCount[8]['repayment_money']+$borrowCount[9]['repayment_money']), 2);//应还总额
    $rowtj['dhze'] = getFloatValue($rowtj['jkze']-$rowtj['yhze'], 2);//待还总额
    $rowtj['jcze'] = getFloatValue(floatval($investStatus[4]['investor_capital']), 2);//借出总额
    $rowtj['ysze'] = getFloatValue(floatval($investStatus[4]['receive_capital']), 2);//应收总额
    $rowtj['dsze'] = getFloatValue($rowtj['jcze']-$rowtj['ysze'], 2);
    $rowtj['fz'] = getFloatValue($rowtj['jcze']-$rowtj['jkze'], 2);

    $rowtj['dqrtb'] = getFloatValue($investStatus[1]['investor_capital'], 2);//待确认投标
    //净赚利息
    $circulation = M('transfer_borrow_investor')->field('sum(investor_interest)as investor_interest, sum(invest_fee) as invest_fee')
                                                ->where('investor_uid='.$uid.' and status=1')
                                                ->find();
    $rowtj['earnInterest'] = getFloatValue(floatval($investStatus[5]['receive_interest']
                                                    +$investStatus[6]['receive_interest']
                                                    +$circulation['investor_interest']
                                                    -$investStatus[5]['invest_fee']
                                                    -$investStatus[6]['invest_fee']
                                                    -$circulation['invest_fee']
                                                    ), 2);//净赚利息
    $receive_interest = M('transfer_borrow_investor')->where('investor_uid='.$uid)->sum('investor_capital');
    $rowtj['payInterest'] = getFloatValue(floatval($repaymentStatus[1]['interest']+$repaymentStatus[2]['interest']+$repaymentStatus[3]['interest']), 2);//净付利息
    $rowtj['willgetInterest'] = getFloatValue(floatval($investStatus[4]['investor_interest']-$investStatus[4]['receive_interest']), 2);//待收利息
    $rowtj['willpayInterest'] = getFloatValue(floatval($repaymentStatus[7]['interest']), 2);//待确认支付管理费
    $rowtj['borrowOut'] = getFloatValue(floatval($investStatus[4]['investor_capital']+$investStatus[5]['investor_capital']+$investStatus[6]['investor_capital']+$receive_interest), 2);//借出总额
    $rowtj['borrowIn'] = getFloatValue(floatval($borrowCount[6]['money']+$borrowCount[7]['money']+$borrowCount[8]['money']+$borrowCount[9]['money']), 2);//借入总额

    $rowtj['jkcgcs'] = $borrowCount[6]['num']+$borrowCount[7]['num']+$borrowCount[8]['num']+$borrowCount[9]['num'];//借款成功次数
    $rowtj['tbjl'] = $_reward_money;//投标奖励

    //处理企业直投的相关数据
    //企业直投借出未确定的金额及数量
    $circulation_bor = M('transfer_borrow_investor')->field('sum(investor_capital) as investor_capital, count(id) as num')
                                                        ->where('investor_uid='.$uid.' and status=1')
                                                        ->find();
    $investStatus[8]['investor_capital'] += $circulation_bor['investor_capital'];
    $investStatus[8]['num'] += $circulation_bor['num'];
    unset($circulation_bor);
    //企业直投已回收的投资及数量
    $circulation_bor = M('transfer_borrow_investor')->field('sum(investor_capital) as investor_capital, count(id) as num')
                                                        ->where('investor_uid='.$uid.' and status=2')
                                                        ->find();
    $investStatus[9]['investor_capital'] += $circulation_bor['investor_capital'];
    $investStatus[9]['num'] += $circulation_bor['num'];

    //完成的投资
    $circulation_bor = M("transfer_borrow_investor i")
                        ->field('sum(i.investor_capital) as investor_capital, count(i.id) as num')
                        ->where('i.status=2 and i.investor_uid='.$uid)
                        ->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")
                        ->order("i.id DESC")
                        ->find();

    $row=array();
    $row['tborrowOut']=$receive_interest;//企业直投借出总额
    $row['borrow'] = $borrowCount;
    $row['repayment'] = $repaymentStatus;
    $row['invest'] = $investStatus;
    $row['tj'] = $rowtj;
    $row['circulation_bor'] = $circulation_bor;
    return $row;
}

function getUserWC($uid)
{
    $row=array();
    $field="count(id) as num,sum(withdraw_money) as money";
    $row["W"] = M('member_withdraw')->field($field)->where("uid={$uid} AND withdraw_status=2")->find();
    $field="count(id) as num,sum(money) as money";
    $row["C"] = M('member_payonline')->field($field)->where("uid={$uid} AND status=1")->find();
    return $row;
}
function getExpiredDays($deadline)
{
    if ($deadline<1000) {
        return "数据有误";
    }
    return ceil((time()-$deadline)/3600/24);
}
function getExpiredMoney($expired, $capital, $interest)
{
    $glodata = get_global_setting();
    $expired_fee = explode("|", $glodata['fee_expired']);

    if ($expired<=$expired_fee[0]) {
        return 0;
    }
    return getFloatValue(($capital+$interest)*$expired*$expired_fee[1]/1000, 2);
}
function getExpiredCallFee($expired, $capital, $interest)
{
    $glodata = get_global_setting();
    $call_fee = explode("|", $glodata['fee_call']);

    if ($expired<=$call_fee[0]) {
        return 0;
    }
    return getFloatValue(($capital+$interest)*$expired*$call_fee[1]/1000, 2);
}


function getNet($uid)
{
    //return getFloatValue($minfo['account_money'] + $minfo['money_freeze'] + $minfo['money_collect'] - intval($capitalinfo['borrow'][6]['money'] - $capitalinfo['borrow'][6]['repayment_money']),2);
    $_minfo = getMinfo($uid, "m.pin_pass,mm.account_money,mm.back_money,mm.credit_cuse,mm.money_collect");
    $borrowNum=M('borrow_info')->field("borrow_type,count(id) as num,sum(borrow_money) as money,sum(repayment_money) as repayment_money")->where("borrow_uid = {$uid} AND borrow_status=6 ")->group("borrow_type")->select();
    $borrowDe = array();
    foreach ($borrowNum as $k => $v) {
        $borrowDe[$v['borrow_type']] = $v['money'] - $v['repayment_money'];
    }
    $_netMoney = getFloatValue(0.9*$_minfo['money_collect']-$borrowDe[4], 2);
    return $_netMoney;
}

function setBackUrl($per="", $suf="")
{
    $url = $_SERVER['HTTP_REFERER'];
    $urlArr = parse_url($url);
    $query = $per."?1=1&".$urlArr['query'].$suf;
    session('listaction', $query);
}
function logInvestCredit($uid, $money, $type, $borrow_id, $duration)
{
    $xs = $type == 1 ? 1 : 2;
    if ($duration == 1) {
        $xs = 1;
    }
    $credit = $xs * $duration * $money;
    $data['uid'] = $uid;
    $data['borrow_id'] = $borrow_id;
    $data['invest_money'] = $money;
    $data['duration'] = $duration;
    $data['invest_type'] = $type;
    $data['get_credit'] = $credit;
    $data['add_time'] = time();
    $data['add_ip'] = get_client_ip();
    $newid = M("invest_credit")->add($data);
    $update['invest_credits'] = array("exp","`invest_credits`+{$credit}");
    if ($newid) {
        M("members")->where("id={$uid}")->save($update);
    }
}

//是否生日
function isBirth($uid)
{
    $pre = C('DB_PREFIX');
    $id = M("member_info i")->field("i.idcard")->join("{$pre}members_status s ON s.uid=i.uid")->where("i.uid = $uid AND s.id_status=1 ")->find();
    if (!id) {
        return false;
    }

    $bir = substr($id['idcard'], 10, 4);
    $now = date("md");

    if ($bir==$now) {
        return true;
    } else {
        return false;
    }
}
// 老版本邮件发送
// function sendemail($to,$subject,$body){
// 	$msgconfig = FS("Webconfig/msgconfig");
// 	import("ORG.Net.Email");
// 	$port =$msgconfig['stmp']['port'];//25;
// 	$smtpserver=$msgconfig['stmp']['server'];
// 	$smtpuser = $msgconfig['stmp']['user'];
// 	$smtppwd = $msgconfig['stmp']['pass'];
// 	$mailtype = "HTML";
// 	$sender = $msgconfig['stmp']['user'];

// 	$smtp = new smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender);
// 	//dump($smtp);die;
// 	$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype);
// 	return $send;
// }

// 邮件发送
function sendemail($address, $title, $message)
{
    $msgconfig = FS("Webconfig/msgconfig");
    vendor('PHPMailer.class#phpmailer');

    $mail=new PHPMailer();

    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    // 设置邮件正文
    $mail->Body=$message;
    // 设置邮件头的From字段。
    $mail->From='zwcpe@163.com'; // 邮箱地址
    // 设置发件人名字
    $mail->FromName='链金所';
    //HTML格式发送
    $mail->IsHTML(true);
    // 设置邮件标题
    $mail->Subject=$title;
    // 设置SMTP服务器。
    $mail->Host=$msgconfig['stmp']['server']; // 邮箱SMTP服务器
    // 设置为"需要验证"
    $mail->SMTPAuth=true;
    // 设置用户名和密码。
    $mail->Username=$msgconfig['stmp']['user']; // 邮箱登录帐号
    $mail->Password=$msgconfig['stmp']['pass']; // 邮箱密码
    // 发送邮件。
    $send = $mail->Send();
    return $send;
}

//企业直投投标处理方法
function getTInvestUrl($id)
{
    return __APP__."/tinvest/{$id}".C("URL_HTML_SUFFIX");
}

//定投宝投标处理方法
function getFundUrl($id)
{
    return __APP__."/fund/{$id}".C("URL_HTML_SUFFIX");
}
function TinvestMoney($uid, $borrow_id, $num, $duration, $_is_auto = 0, $repayment_type=5)
{
    $pre = C("DB_PREFIX");
    $done = false;
    $datag = get_global_setting();
    $parm = "企业直投";
    /////////////////////////////锁表  辉 2014-04-1////////////////////////////////////////////////

    $dataname = C('DB_NAME');
    $db_host = C('DB_HOST');
    $db_user = C('DB_USER');
    $db_pwd = C('DB_PWD');

    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $bId = $borrow_id;

    $sql1 ="SELECT suo FROM lzh_transfer_borrow_info_lock WHERE id = ? FOR UPDATE";
    $stmt1 = $bdb->prepare($sql1);
    $stmt1->bindParam(1, $bId);    //绑定第一个参数值
    $stmt1->execute();

    /////////////////////////////锁表  辉 2014-04-1////////////////////////////////////////////////
    $invest_integral = $datag['invest_integral'];//投资积分
    $fee_rate = $datag['fee_invest_manage'];//投资者成交管理费费率
    $binfo = M("transfer_borrow_info")->field(
"id,borrow_uid,borrow_money,borrow_interest_rate,borrow_duration,repayment_type,transfer_out,transfer_back,transfer_total,per_transfer,is_show,deadline,min_month,reward_rate,increase_rate,borrow_fee,is_jijin")->find($borrow_id);

    if ($binfo['is_jijin']==1) {
        $parm ="定投宝";
    } else {
        $parm = "企业直投";
    }
    $vminfo = getMinfo($uid, 'm.user_leve,m.time_limit,mm.account_money,mm.back_money,mm.money_collect');
    //不同会员级别的费率
    //($vminfo['user_leve']==1 && $vminfo['time_limit']>time())?$fee_rate=($fee_invest_manage[1]/100):$fee_rate=($fee_invest_manage[0]/100);
    if ($num<1) {
        return "对不起,您购买的份数小于最低允许购买份数,请重新输入认购份数！";
    }
    if (($binfo['transfer_total']-$binfo['transfer_out'])<$num) {
        return "对不起,您购买的份数已超出当前可供购买份数,请重新输入认购份数！";
    }
    if ($num < 1) {
        return "最少要投一份！";
    }
    $money = $binfo['per_transfer'] * $num;
    if (($vminfo['account_money']+$vminfo['back_money'])<$money) {
        return "对不起，您的可用余额不足,不能投标";
    }
    $investMoney =D("transfer_borrow_investor");
    $investMoney->startTrans();
    $now = time();

    if ($binfo['is_jijin'] == 1) {
        $binfo['repayment_type'] = $repayment_type;
    }
    switch ($binfo['repayment_type']) {
        case 2://按月分期还款
            $interest_rate = $binfo['borrow_interest_rate'];
            $monthData['duration'] = $duration;
            $monthData['money'] = $money;
            $monthData['year_apr'] = $interest_rate;
            $monthData['type'] = "all";
            $repay_detail = EqualMonth($monthData);

            $investinfo['status'] = 1;
            $investinfo['borrow_id'] = $borrow_id;
            $investinfo['investor_uid'] = $uid;
            $investinfo['borrow_uid'] = $binfo['borrow_uid'];
            $investinfo['investor_capital'] = $money;
            $investinfo['transfer_num'] = $num;
            $investinfo['transfer_month'] = $duration;
            $investinfo['add_time'] = $now;
            $investinfo['deadline'] = $now + $duration * 30 * 24 * 3600;
            $investinfo['reward_money'] = getFloatValue($binfo['reward_rate'] * $money/100, 2);

            //$investinfo['investor_interest'] = $repay_detail['repayment_money'] - $money;
            $investinfo['final_interest_rate'] = $interest_rate;
            //$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest']/100, 2);
            //$investinfo['mujiqi_interest'] = $mujiqi;//募集期应得利息

            //$detailInterest = getFloatValue($investinfo['investor_interest']/$duration,2);

            $monthDataDetail['duration'] = $duration;
            $monthDataDetail['money'] = $money;
            $monthDataDetail['year_apr'] = $interest_rate;
            $repay_list = EqualMonth($monthDataDetail);
            $i=1;
            foreach ($repay_list as $key=>$v) {
                $investinfo['investor_interest'] += round($v['interest'], 2);//待收利息
                $investinfo['invest_fee'] += getFloatValue($fee_rate*$v['interest']/100, 2);//待收手续费
                $i++;
            }
            $invest_info_id = M("transfer_borrow_investor")->add($investinfo);
            $i=1;
            $capital_detail_all = 0;
            foreach ($repay_list as $key=>$v) {
                $investDetail['repayment_time'] = 0;
                $investDetail['borrow_id'] = $borrow_id;
                $investDetail['invest_id'] = $invest_info_id;
                $investDetail['investor_uid'] = $uid;
                $investDetail['borrow_uid'] = $binfo['borrow_uid'];
                if ($i < $duration) {
                    $investDetail['capital'] = round($v['capital'], 2);
                    $capital_detail_all += $investDetail['capital'];
                } else {
                    $investDetail['capital'] = $money - $capital_detail_all;//最后一期的本金
                }
                $investDetail['interest'] = $v['interest'];
                $investDetail['interest_fee'] = getFloatValue($fee_rate*$v['interest']/100, 2);
                $investDetail['status'] = 7;
                $investDetail['receive_interest'] = 0;
                $investDetail['receive_capital'] = 0;
                $investDetail['sort_order'] = $i;
                $investDetail['total'] = $duration;
                $investDetail['deadline'] = $now +$i*30*24*3600;
                $IDetail[] = $investDetail;
                $i++;
            }
            break;
        case 4://每月还息到期还本
            $interest_rate = $binfo['borrow_interest_rate'];
            $monthData['month_times'] = $duration;
            $monthData['account'] = $money;
            $monthData['year_apr'] = $interest_rate;
            $monthData['type'] = "all";
            $repay_detail = EqualEndMonth($monthData);

            $investinfo['status'] = 1;
            $investinfo['borrow_id'] = $borrow_id;
            $investinfo['investor_uid'] = $uid;
            $investinfo['borrow_uid'] = $binfo['borrow_uid'];
            $investinfo['investor_capital'] = $money;
            $investinfo['transfer_num'] = $num;
            $investinfo['transfer_month'] = $duration;
            $investinfo['add_time'] = $now;
            $investinfo['deadline'] = $now + $duration * 30 * 24 * 3600;
            $investinfo['reward_money'] = getFloatValue($binfo['reward_rate'] * $money/100, 2);
            if ($binfo['is_jijin'] == 1) {
                $investinfo['is_jijin'] = 1;
            }
            //$investinfo['investor_interest'] = $repay_detail['repayment_account'] - $money ;
            $investinfo['final_interest_rate'] = $interest_rate;
            //$investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest']/100, 2);
            //$investinfo['mujiqi_interest'] = $mujiqi;//募集期应得利息

            //$detailInterest = getFloatValue($investinfo['investor_interest']/$duration,2);

            $monthDataDetail['month_times'] = $duration;
            $monthDataDetail['account'] = $money;
            $monthDataDetail['year_apr'] = $interest_rate;
            $repay_list = EqualEndMonth($monthDataDetail);
            $i=1;
            foreach ($repay_list as $key=>$v) {
                $investinfo['investor_interest'] += round($v['interest'], 2);//待收利息
                $investinfo['invest_fee'] += getFloatValue($fee_rate*$v['interest']/100, 2);//待收手续费
                $i++;
            }
            $invest_info_id = M("transfer_borrow_investor")->add($investinfo);
            $i=1;
            foreach ($repay_list as $key=>$v) {
                $investDetail['repayment_time'] = 0;
                $investDetail['borrow_id'] = $borrow_id;
                $investDetail['invest_id'] = $invest_info_id;
                $investDetail['investor_uid'] = $uid;
                $investDetail['borrow_uid'] = $binfo['borrow_uid'];
                $investDetail['capital'] = $v['capital'];
                if ($i == $duration) {
                    $investDetail['interest'] = $v['interest'];
                } else {
                    $investDetail['interest'] = $v['interest'];
                }
                $investDetail['interest_fee'] = getFloatValue($fee_rate*$v['interest']/100, 2);
                $investDetail['status'] = 7;
                $investDetail['receive_interest'] = 0;
                $investDetail['receive_capital'] = 0;
                $investDetail['sort_order'] = $i;
                $investDetail['total'] = $duration;
                $investDetail['deadline'] = $now +$i*30*24*3600;
                $IDetail[] = $investDetail;
                $i++;
            }
            break;
        case 5://一次性还款
            $investinfo['status'] = 1;
            $investinfo['borrow_id'] = $borrow_id;
            $investinfo['investor_uid'] = $uid;
            $investinfo['borrow_uid'] = $binfo['borrow_uid'];
            $investinfo['investor_capital'] = $money;
            $investinfo['transfer_num'] = $num;
            $investinfo['transfer_month'] = $duration;
            $investinfo['is_auto'] = $_is_auto;
            $investinfo['add_time'] = time();
            $investinfo['deadline'] = time() + $investinfo['transfer_month'] * 30 * 24 * 3600;
            $investinfo['reward_money'] = getFloatValue($binfo['reward_rate'] * $money/100, 2);//奖励会在会员投标后一次性发放//getFloatValue($binfo['reward_rate'] * $money * $duration/100, 2);
            $interest_rate = $binfo['borrow_interest_rate'] + $duration * $binfo['increase_rate'];
            $investinfo['investor_interest'] = getFloatValue($interest_rate * $money * $duration/1200, 2);
            $investinfo['final_interest_rate'] = $interest_rate;
            $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest']/100, 2);
            $invest_info_id = M("transfer_borrow_investor")->add($investinfo);


            //$endTime = strtotime(date("Y-m-d",time())." 11:59:59");
            $endTime = strtotime(date("Y-m-d", time())." ".$datag['auto_back_time']);//企业直投自动还款时间设置
            $detailInterest = getFloatValue($investinfo['investor_interest']/$duration, 2);

            $investDetail['repayment_time'] = 0;
            $investDetail['borrow_id'] = $borrow_id;
            $investDetail['invest_id'] = $invest_info_id;
            $investDetail['investor_uid'] = $uid;
            $investDetail['borrow_uid'] = $binfo['borrow_uid'];
            $investDetail['capital'] = $money;
            //$investDetail['interest'] = $i == $duration - 1 ? $detailInterest:0;
            $investDetail['interest'] = getFloatValue($investinfo['investor_interest'], 2);
            $investDetail['interest_fee'] = $investinfo['invest_fee'];
            $investDetail['status'] = 7;
            $investDetail['receive_interest'] = 0;
            $investDetail['receive_capital'] = 0;
            $investDetail['sort_order'] = 1;
            $investDetail['total'] = 1;
            $investDetail['deadline'] = $now +$duration*30*24*3600;
            $IDetail[] = $investDetail;
            break;
        case 6://利息复投
            $interest_rate = $binfo['borrow_interest_rate'];
            $monthData['month_times'] = $duration;
            $monthData['account'] = $money;
            $monthData['year_apr'] = $interest_rate;
            $monthData['type'] = "all";
            $repay_detail = CompoundMonth($monthData);

            $investinfo['status'] = 1;
            $investinfo['borrow_id'] = $borrow_id;
            $investinfo['investor_uid'] = $uid;
            $investinfo['borrow_uid'] = $binfo['borrow_uid'];
            $investinfo['investor_capital'] = $money;
            $investinfo['transfer_num'] = $num;
            $investinfo['transfer_month'] = $duration;
            $investinfo['is_auto'] = $_is_auto;
            $investinfo['add_time'] = time();
            $investinfo['deadline'] = time() + $investinfo['transfer_month'] * 30 * 24 * 3600;
            $investinfo['reward_money'] = getFloatValue($binfo['reward_rate'] * $money/100, 2);//奖励会在会员投标后一次性发放//getFloatValue($binfo['reward_rate'] * $money * $duration/100, 2);
            $interest_rate = $binfo['borrow_interest_rate'];
            $investinfo['investor_interest'] = getFloatValue($repay_detail['interest'], 2);
            $investinfo['final_interest_rate'] = $interest_rate;
            $investinfo['invest_fee'] = getFloatValue($fee_rate * $investinfo['investor_interest']/100, 2);
            if ($binfo['is_jijin'] == 1) {
                $investinfo['is_jijin'] = 1;
            }
            $invest_info_id = M("transfer_borrow_investor")->add($investinfo);


            //$endTime = strtotime(date("Y-m-d",time())." 11:59:59");
            $endTime = strtotime(date("Y-m-d", time())." ".$datag['auto_back_time']);//企业直投自动还款时间设置
            $detailInterest = getFloatValue($investinfo['investor_interest']/$duration, 2);

            $investDetail['repayment_time'] = 0;
            $investDetail['borrow_id'] = $borrow_id;
            $investDetail['invest_id'] = $invest_info_id;
            $investDetail['investor_uid'] = $uid;
            $investDetail['borrow_uid'] = $binfo['borrow_uid'];
            $investDetail['capital'] = $money;
            //$investDetail['interest'] = $i == $duration - 1 ? $detailInterest:0;
            $investDetail['interest'] = getFloatValue($investinfo['investor_interest'], 2);
            $investDetail['interest_fee'] = $investinfo['invest_fee'];
            $investDetail['status'] = 7;
            $investDetail['receive_interest'] = 0;
            $investDetail['receive_capital'] = 0;
            $investDetail['sort_order'] = 1;
            $investDetail['total'] = 1;
            $investDetail['deadline'] = $now +$duration*30*24*3600;
            $IDetail[] = $investDetail;

            break;
    }
    $Tinvest_defail_id = M("transfer_investor_detail")->addAll($IDetail);
    if ($invest_info_id && $Tinvest_defail_id) {
        $investMoney->commit();
        $newbid=borrowidlayout1($borrow_id);
        $res = memberMoneyLog($uid, 37, -$money, "对{$newbid}号{$parm}进行了投标", $binfo['borrow_uid']);

            //借款人资金增加
            $_borraccount = memberMoneyLog($binfo['borrow_uid'], 17, $money, "第{$newbid}号{$parm}已被认购{$money}元，{$money}元已入帐");//借款入帐
            //if(!$_borraccount) return false;//借款者帐户处理出错
            if (empty($binfo['transfer_out'])) {
                $binfo['transfer_out'] = 0;
            }
        if ((intval($binfo['transfer_out'])+$num)==$binfo['transfer_total']) {//如果企业直投被认购完毕，则扣除借款人借款管理费
                $_borrfee = memberMoneyLog($binfo['borrow_uid'], 18, -$binfo['borrow_fee'], "第{$newbid}号{$parm}被认购完毕，扣除借款管理费{$binfo['borrow_fee']}元");//借款管理费扣除
                if (!$_borrfee) {
                    return false;
                }//借款者帐户处理出错
        }


            //借款天数、还款时间
            $endTime = strtotime(date("Y-m-d", time())." ".$_P_fee['back_time']);
        $deadline_last = strtotime("+{$duration} month", $endTime);
        $getIntegralDays = intval(($deadline_last-$endTime)/3600/24);//借款天数


            //////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////

            $integ = intval($investinfo['investor_capital']*$getIntegralDays*$invest_integral/1000);//dump($invest_integral);exit;
            if ($integ>0) {
                $reintegral = memberIntegralLog($uid, 2, $integ, "对{$newbid}号{$parm}进行投标，应获积分：".$integ."分,投资金额：".$investinfo['investor_capital']."元,投资天数：".$getIntegralDays."天");
                if (isBirth($uid)) {
                    $reintegral = memberIntegralLog($uid, 2, $integ, "亲，祝您生日快乐，本站特赠送您{$integ}积分作为礼物，以表祝福。");
                }
            }

            //////////////////////////增加投资者的投资积分 2013-08-28 fans////////////////////////////////////

            $res1 = memberMoneyLog($uid, 39, $investinfo['investor_capital'], "您对第{$newbid}号{$parm}投标成功，冻结本金成为待收金额", $binfo['borrow_uid']);
        $res2 = memberMoneyLog($uid, 38, $investinfo['investor_interest'] - $investinfo['invest_fee'], "第{$newbid}号{$parm}应收利息成为待收利息", $binfo['borrow_uid']);

            //投标奖励
            if ($investinfo['reward_money']>0) {
                $_remoney_do = false;
                $_reward_m = memberMoneyLog($uid, 41, $investinfo['reward_money'], "第{$newbid}号{$parm}认购成功，获取投标奖励", $binfo['borrow_uid']);
                $_reward_m_give = memberMoneyLog($binfo['borrow_uid'], 42, -$investinfo['reward_money'], "第{$newbid}号{$parm}已被认购，支付投标奖励", $uid);
                if ($_reward_m && $_reward_m_give) {
                    $_remoney_do = true;
                }
            }
            //投标奖励
            //////////////////////邀请奖励开始////////////////////////////////////////
            $vo = M('members')->field('user_name,recommend_id')->find($uid);
        $_rate = $datag['award_invest']/1000;//推广奖励
            $jiangli = getFloatValue($_rate * $investinfo['investor_capital'], 2);
        if ($vo['recommend_id']!=0) {
            //memberMoneyLog($vo['recommend_id'],13,$jiangli,$vo['user_name']."对{$borrow_id}号标投资成功，你获得推广奖励".$jiangli."元。",$uid);
        }
            /////////////////////邀请奖励结束/////////////////////////////////////////

            $out =$binfo['transfer_out']+$num;
        $progress = getfloatvalue($out / $binfo['transfer_total'] * 100, 2);
        $upborrowsql = "update `{$pre}transfer_borrow_info` set ";
        $upborrowsql .= "`transfer_out` = `transfer_out` + {$num},";
        $upborrowsql .= "`progress`= {$progress}";
        if ($progress == 100 || ($binfo['transfer_out'] + $num == $binfo['transfer_total'])) {
            $upborrowsql .= ",`is_show` = 0";
        }
        $upborrowsql .= " WHERE `id`={$borrow_id}";
        $upborrow_res = M()->execute($upborrowsql);
        if (!$res || !$res1 || !$res2) {
            $out =$binfo['transfer_out']+$num;
            $progress = getfloatvalue($out / $binfo['transfer_total'] * 100, 2);
            M("transfer_borrow_investor")->where("id={$invest_info_id}")->delete();
            M("transfer_investor_detail")->where("invest_id={$invest_info_id}")->delete();
            $upborrowsql = "update `{$pre}transfer_borrow_info` set ";
            $upborrowsql .= "`transfer_out` = `transfer_out` - {$num}";
            $upborrowsql .= "`progress`= {$progress}";
            if ($binfo['transfer_out'] + $num == $binfo['transfer_total']) {
                $upborrowsql .= ",`is_show` = 1";
            }
            $upborrowsql .= " WHERE `id`={$borrow_id}";
            $upborrow_res = M()->execute($upborrowsql);
            $done = false;
        } else {
            ////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
                $today_reward = explode("|", $datag['today_reward']);
            if ($binfo['borrow_duration']==1) {
                $reward_rate = floatval($today_reward[0]);
            } elseif ($binfo['borrow_duration']==2) {
                $reward_rate = floatval($today_reward[1]);
            } else {
                $reward_rate = floatval($today_reward[2]);
            }
            ////////////////////////////////////////回款续投奖励规则 fan 2013-07-20////////////////////////////
                $vd['add_time'] = array("lt",time());
            $vd['investor_uid'] = $uid;
            $borrow_invest_count = M("transfer_borrow_investor")->where($vd)->count('id');//检测是否投过标且大于一次
                //dump($borrow_invest_count);exit;
                if ($reward_rate>0 && $vminfo['back_money']>0 && $borrow_invest_count>0) {//首次投标不给续投奖励
                    if ($money>$vminfo['back_money']) {//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
                        $reward_money_s = $vminfo['back_money'];
                    } else {
                        $reward_money_s = $money;
                    }

                    $save_reward['borrow_id'] = $borrow_id;
                    $save_reward['reward_uid'] = $uid;
                    $save_reward['invest_money'] = $reward_money_s;//如果投标金额大于回款资金池金额，有效续投奖励以回款金额资金池总额为标准，否则以投标金额为准
                    $save_reward['reward_money'] = $reward_money_s*$reward_rate/1000;//续投奖励
                    $save_reward['reward_status'] = 1;
                    $save_reward['add_time'] = time();
                    $save_reward['add_ip'] = get_client_ip();
                    $newidxt = M("today_reward")->add($save_reward);

                    //dump($newidxt);exit;
                    if ($newidxt) {
                        $result =memberMoneyLog($uid, 40, $save_reward['reward_money'], "{$parm}续投有效金额({$reward_money_s})的奖励({$newbid}号{$parm})奖励", 0, "@网站管理员@");
                    }
                }
            $done = true;
        }
    } else {
        $investMoney->rollback();
    }
    return $done;
}


function getTransferLeftmonth($deadline)
{
    $lefttime = $deadline-time();
    if ($lefttime<=0) {
        return 0;
    }
    //echo $lefttime/(24*3600*30);
    $leftMonth = floor($lefttime/(24*3600*30));
    return $leftMonth;
}

//后台管理员登陆日志
function alogs($type, $tid, $tstatus, $deal_info='', $deal_user='')
{
    $arr = array();
    $arr['type'] = $type;
    $arr['tid'] = $tid;
    $arr['tstatus'] = $tstatus;
    $arr['deal_info'] = $deal_info;

    $arr['deal_user'] = ($deal_user)?$deal_user:session('adminname');
    $arr['deal_ip'] = get_client_ip();
    $arr['deal_time'] = time();
    //dump($arr);exit;
    $newid = M("auser_dologs")->add($arr);
    return $newid;
}
function getMarketUrl($id)
{
    return __APP__."/Market/{$id}".C('URL_HTML_SUFFIX');
}
function cnsubstr2($str, $length, $start=0, $charset="utf-8", $suffix=true)
{
    $str = strip_tags($str);
    if (function_exists("mb_substr")) {
        if (mb_strlen($str, $charset) <= $length) {
            return $str;
        }
        $slice = mb_substr($str, $start, $length, $charset);
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']          = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']          = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        if (count($match[0]) <= $length) {
            return $str;
        }
        $slice = join("", array_slice($match[0], $start, $length));
    }
    if ($suffix) {
        return $slice;
    }
    return $slice;
}
/////////////////////////////////////////利息复投//////////////////////////////////////

function CompoundMonth($data = array())
{

  //借款的月数
  if (isset($data['month_times']) && $data['month_times']>0) {
      $month_times = $data['month_times'];
  }

  //借款的总金额
  if (isset($data['account']) && $data['account']>0) {
      $account = $data['account'];
  } else {
      return "";
  }

  //借款的年利率
  if (isset($data['year_apr']) && $data['year_apr']>0) {
      $year_apr = $data['year_apr'];
  } else {
      return "";
  }

  //借款的时间
  if (isset($data['borrow_time']) && $data['borrow_time']>0) {
      $borrow_time = $data['borrow_time'];
  } else {
      $borrow_time = time();
  }

  //月利率
  $month_apr = $year_apr/(12*100);
    $mpow = pow((1 + $month_apr), $month_times);
    $repayment_account = getFloatValue($account*$mpow, 4);//利息等于应还金额*月利率*借款月数

  if (isset($data['type']) && $data['type']=="all") {
      $_resul['repayment_account'] = $repayment_account;
      $_resul['month_apr'] = round($month_apr*100, 4);
      $_resul['interest'] = $repayment_account - $account;
      $_resul['capital'] = $account;
      $_resul['shouyi'] = round($_resul['interest']/$account*100, 2);
      return $_resul;
  }
}


//日志写入 20151019 @廖兆彬
    function moneyactlog($uid, $type, $money, $allmoney, $remark, $status)
    {
        $data['uid'] = $uid;  //用户ID
        $data['type'] = $type;    //操作类型
        $data['money'] = $money; //变动金额
        $data['all_money'] = $allmoney;    //用户余额
        $data['remark'] = $remark; //备注
        $data['optime'] = time();    //时间
        $data['ip'] = get_client_ip();    //IP
        $data['status'] = $status;        //状态
        $newid = M('member_moneyactlog')->add($data);
    }
//投标记录
    function investlog($uid, $borrow_id, $money, $order_no, $status)
    {
        $data['uid'] = $uid;
        $data['borrow_id'] = $borrow_id;
        $data['money'] = $money;
        $data['order_no'] = $order_no;
        $data['status'] = $status;
        if ($status == 1) {
            $data['createtime'] = time();
        } else {
            $data['completetime'] = time();
        }
        M('member_investlog')->add($data);
    }

//新浪操作日志
    function sinalog($uid, $borrow_id, $type, $order_no, $money, $addtime, $sort_order, $coupons=null, $is_auto=0, $jx_coupons=null)
    {
        $data['uid'] = $uid;
        $data['borrow_id'] = $borrow_id;
        $data['type'] = $type;
        $data['order_no'] = $order_no;
        $data['money'] = $money;
        $data['addtime'] = $addtime;
        $data['sort_order'] = $sort_order;
        $data['coupons'] = $coupons;
        $data['is_auto'] = $is_auto;
        $data['jx_coupons'] = $jx_coupons;
        M('sinalog')->add($data);
    }

//提现手续费记录
    function withdrawlog($uid, $fee, $fee_orderno)
    {
        $data['uid'] = $uid;
        $data['fee'] = $fee;
        $data['fee_orderno'] = $fee_orderno;
        $data['add_time'] = time();
        M('withdrawlog')->add($data);
    }
///////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 综合服务费或者债权标代付接口
     * @param $money
     * @param int $type 0 普通标  1 债权标
     * @return mixed
     */
    function sinapaytrade($money, $type=0)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '2002';                                                    //交易码 2001代付借款金 2002代付（本金/收益）金
        $data['amount']                  = $money;                                                    //金额
        if ($type==0) {
            $data['summary']              = '收取综合服务费';
        } else {
            $data['summary']              = '收取债权服务费';
        }
        $data['payee_identity_id']      = $payConfig['sinapay']['email'];                            //收款人邮箱
        $data['payee_identity_type']  = 'EMAIL';                                                //ID类型
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        if ($rs["response_code"] == "APPLY_SUCCESS") {
            $rs1["code"]=$rs["response_code"];
            $rs1["order_no"]=$data["out_trade_no"];
            return $rs1;
        } else {
            $rs1["code"]=$rs["response_code"];
            $rs1["order_no"]=$data["out_trade_no"];
            return $rs1;
        }
    }

    //担保金代付接口
    function sinapaydanbao($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '2002';                                                    //交易码 2001代付借款金 2002代付（本金/收益）金
        $data['amount']                  = $sina['money'];                                            //金额
        $newbid=borrowidlayout1($sina['bid']);
        $data['summary']              = '第'.$newbid.'号标付咨询服务费';                                                //摘要
        $data['payee_identity_id']      = '20151008'.$sina['uid'];                                //收款人ID
        $data['payee_identity_type']  = 'UID';                                                    //ID类型
        $data['account_type']          = "BASIC";                                            //账户类型
        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/paydanbaonotify";        //异步回调地址
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        sinalog($sina['uid'], $sina['bid'], 13, $data['out_trade_no'], $sina['money'], time(), null);
        return $rs["response_code"];
    }

    //担保金代收接口
    function sinapaydanbaotrade($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = $sina["orderno"];                //交易订单号
        $data['out_trade_code']          = "1002";                                            //交易码 1001代收投资金，1002代收还款金
        $data['summary']              = "代收咨询服务费";                                        //摘要
        $data['payer_id']              = '20151008'.$sina['uid'];                                //用户ID
        $data['payer_identity_type']  = 'UID';                                                    //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "online_bank^".$sina['money']."^SINAPAY,DEBIT,C";        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        $data['return_url']              = $sina['return_url'];
        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/closeddanbaonotify";
        // $data['extend_param']		  = "channel_black_list^online_bank^binding_pay^quick_pay";
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        return $result;
    }

    /**
     * 综合服务费代收接口或者债权手续费
     * @param $sina
     * @param int $type 0 综合服务费 1 债权手续费
     * @return string
     */
    function sinapayfeecollecttrade($sina, $type=0)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = $sina["orderno"];                //交易订单号
        $data['out_trade_code']          = "1002";                                            //交易码 1001代收投资金，1002代收还款金
        if ($type==0) {
            $data['summary']              = "代收综合服务费";
        } else {
            $data['summary']              = "债权手续费";
        }
        $data['payer_id']              = '20151008'.$sina['uid'];                                //用户ID
        $data['payer_identity_type']  = 'UID';                                                    //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "online_bank^".$sina['money']."^SINAPAY,DEBIT,C";        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        $data['return_url']              = $sina['return_url'];
        if ($type==0) {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/payfeenotify";
        } else {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquan_payfeenotify";
        }
        // $data['extend_param']		  = "channel_black_list^online_bank^binding_pay^quick_pay";
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        return $result;
    }

    //还款新浪代付接口
    // function sinabatchpay($uid,$bid,$money){
    // 	import("@.Oauth.sina.Weibopay");
    // 	$payConfig = FS("Webconfig/payconfig");
    // 	$weibopay = new Weibopay();
    // 	$data['service'] 			  = "create_single_hosting_pay_trade";						//接口名称
    // 	$data['version']			  = $payConfig['sinapay']['version'];						//接口版本
    // 	$data['request_time']		  = date('YmdHis');											//请求时间
    //  $data['user_ip']			  = get_client_ip();												//用户IP地址
    // 	$data['partner_id'] 		  = $payConfig['sinapay']['partner_id'];					//合作者身份ID
    // 	$data['_input_charset'] 	  = $payConfig['sinapay']['_input_charset'];				//网站编码格式
    // 	$data['sign_type'] 			  = $payConfig['sinapay']['sign_type'];						//签名方式 MD5
    // 	$data['out_trade_no']         = date('YmdHis').mt_rand( 100000,999999); 				// 交易订单号
    // 	$data['out_trade_code']		  = '2002';													//交易码 2001代付借款金 2002代付（本金/收益）金
    // 	$data['amount']			  	  = $money;													//金额
    // 	$data['summary']			  = '第'.$bid.'号标投资还款收益';							//摘要
    // 	$data['payee_identity_id']	  = "20151008".$uid;													//收款人ID
    // 	$data['payee_identity_type']  = 'UID';													//ID类型
    // 	$data['account_type']		  = "SAVING_POT";											//账户类型
    // 	$data['notify_url']		 	  = "http://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/batchpaynotify";	//异步回调地址
    // 	ksort($data);
    // 	$data['sign'] 				  = $weibopay->getSignMsg($data,$data['sign_type']);		//计算签名
    // 	$setdata 					  = $weibopay->createcurl_data($data);
    // 	$result						  = $weibopay->curlPost($payConfig['sinapay']['mas'],$setdata);//模拟表单提交
    // 	$rs = checksinaerror($result);
    // 	if($rs["response_code"] == "APPLY_SUCCESS"){
    // 		sinalog($uid,$bid,4,$data['out_trade_no'],$money,time(),null);
    // 		moneyactlog($uid,5,$money,0,"新浪完成对".$bid."号标付收益",2);
    // 	}else{
    // 		moneyactlog($uid,5,$money,0,"新浪对".$bid."号标付收益,错误原因：".$rs["response_message"],2);
    // 	}
    // }

    /**
     * 复审后新浪代付接口
     * @param $sina
     * @param int $type 1 普通标 2 债权标
     * @return bool
     */
    function sinatrade($sina, $type=1)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '2001';                                                    //交易码 2001代付借款金 2002代付（本金/收益）金
        $data['amount']                  = $sina['money'];                                            //金额
        if ($type==1) {
            $newbid=borrowidlayout1($sina['bid']);
            $data['summary']              = '第'.$newbid.'号标付借款金';                                                //摘要
        } else {
            // $newbid=zhaiquan_borrowidlayout1($sina['bid']);
            $data['summary']              = '债权第ZQ'.$sina['bid'].'号债权标付借款金';                                            //摘要
        }
        $data['payee_identity_id']      = '20151008'.$sina['uid'];                                //收款人ID
        $data['payee_identity_type']  = 'UID';                                                    //ID类型
        $data['account_type']          = $sina['account_type'];                                            //账户类型
        if ($type==1) {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/paytradenotify";        //异步回调地址
        } else {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquanpaytradenotify";        //异步回调地址
        }
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        file_put_contents('log.txt', var_export($rs, true), FILE_APPEND);
        if ($rs["response_code"] == "APPLY_SUCCESS") {
            if ($type==1) {
                sinalog($sina['uid'], $sina['bid'], 7, $data['out_trade_no'], $sina['money'], time(), null);
            } else {
                logw("债权复审3");
                sinalog($sina['uid'], $sina['bid'], 20, $data['out_trade_no'], $sina['money'], time(), null);
            }

            moneyactlog($sina['uid'], 6, $sina['money'], 0, "新浪完成对".$sina['bid']."号标付借款金", 2);
            return ture;
        } else {
            moneyactlog($sina['uid'], 6, $sina['money'], 0, "新浪对".$sina['bid']."号标付借款金失败,错误原因：".$rs["response_message"], 2);
            return false;
        }
    }

    /**
     * 新浪代收接口 ：投标或者还款   3, 投标  4 还款  16债权标投标 17 债权标还款
     * @param $sina 接口参数数组
     * @return string 返回结果
     */
    function sinacollecttrade($sina, $type=1)
    {
        file_put_contents('sinatestlog.txt', "进接口：时间：".date("Y-m-d H:i:s")."参数：".var_export($sina, true)."\n", FILE_APPEND);
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                //交易订单号
        $data['out_trade_code']          = $sina['code'];                                            //交易码 1001代收投资金，1002代收还款金
        $data['summary']              = $sina['content'];                                        //摘要
        $data['payer_id']              = '20151008'.$sina['uid'];                                //用户ID
        $data['payer_identity_type']  = 'UID';                                                    //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "online_bank^".$sina['money']."^SINAPAY,DEBIT,C";        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        $data['return_url']              = $sina['return_url'];
        $data['notify_url']              = $sina['notify_url'];
        $data['extend_param']          = "channel_black_list^online_bank^binding_pay^quick_pay";
        if ($sina['code'] == "1001") {
            $data['collect_trade_type']              = 'pre_auth';                                    //投资代收冻结
        }
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        file_put_contents('sinatestlog.txt', "接口数据返回：时间：".date("Y-m-d H:i:s")."参数：".var_export($result, true)."\n", FILE_APPEND);
        if ($sina['code'] == "1001") {
            if ($type==1) {//普通标投标
                sinalog($sina['uid'], $sina['bid'], 3, $data['out_trade_no'], $sina['money']+$sina['coupons_num'][0], time(), null, $sina['coupons_num'][1], 0, $sina['jx_num']);
            } elseif ($type==2) {//债权标投标
                sinalog($sina['uid'], $sina['bid'], 16, $data['out_trade_no'], $sina['money'], time(), null);
            }
        } else {
            sinalog($sina['uid'], $sina['bid'], 4, $data['out_trade_no'], $sina['money'], time(), $sina['sort_order']);
        }
        file_put_contents('log.txt', var_export($sina, true), FILE_APPEND);
        return $result;
    }

    //新浪代收完成接口
    function sinafinishpretrade($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "finish_pre_auth_trade";                                //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_request_no']       = date('YmdHis').mt_rand(100000, 999999);                //交易订单号
        $data['trade_list']              = $sina;
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        file_put_contents('log.txt', "复审代收完成：".var_export($rs, true), FILE_APPEND);
        return $rs;
    }

    /**
     * 新浪代收撤销接口
     * @param $sina
     * @param $type 1 普通标  2 债权标
     */
    function sinacancelpretrade($sina, $type=1)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "cancel_pre_auth_trade";                                //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_request_no']       = date('YmdHis').mt_rand(100000, 999999);                //交易订单号
        $data['trade_list']              = date('YmdHis').mt_rand(100000, 999999)."~".$sina["orderno"]."~第".$sina["bid"]."号标投资失败";
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        if ($type==1) {
            sinalog($sina['uid'], $sina['bid'], 5, $sina['orderno'], $sina['money'], time(), null);
        } else {
            sinalog($sina['uid'], $sina['bid'], 22, $sina['orderno'], $sina['money'], time(), null);//债权退款
        }
    }

    //新浪代收提现手续费撤销接口
    function sinafeecancel($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "cancel_pre_auth_trade";                                //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_request_no']       = date('YmdHis').mt_rand(100000, 999999);                //交易订单号
        $data['trade_list']              = date('YmdHis').mt_rand(100000, 999999)."~".$sina["orderno"]."~提现失败";
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        return checksinaerror($result);
    }

    /**
     * @param $trade_list
     * @param $bid
     * @param $type 1  默认 2 债权
     */
    function sinabatchpay($trade_list, $bid, $type=1)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_batch_hosting_pay_trade";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_pay_no']           = date('YmdHis').mt_rand(100000, 999999);                //交易订单号
        $data['out_trade_code']          = '2002';                                                    //交易码 2001代付借款金 2002代付（本金/收益）金
        $data['trade_list']              = $trade_list;                                            //交易列表
        $data['notify_method']          = 'batch_notify';                                            //通知方式：single_notify: 交易逐笔通知 batch_notify: 批量通知
        if ($type==1) {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/batchpaynotify";    //异步回调地址
        } else {
            $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/zhaiquan_batchpaynotify";    //异步回调地址
        }

        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        file_put_contents('sinaerrorlog.txt', "\n\r债权1 批量".var_export($rs, true), FILE_APPEND);
        if ($type==1) {
            sinalog(1, $bid, 4, $data['out_pay_no'], "0.00", time(), null);
        } else {
            sinalog(1, $bid, 17, $data['out_pay_no'], "0.00", time(), null);
        }
    }

    //新浪托管代收代付接口(奖励）
    function sinareward($uid, $content)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '1001';                                                    //交易码
        $data['summary']              = '平台付出'.$content;                                    //摘要
        $data['payer_id']              = $payConfig['sinapay']['email'];                            //付款人邮箱
        $data['payer_identity_type']  = 'EMAIL';                                                //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "balance^10^BASIC";                                        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $order_no = $data['out_trade_no'];
        $data1['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data1['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data1['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data1['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data1['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data1['sign_type']          = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data1['out_trade_no']        = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data1['out_trade_code']      = '2001';                                                    //交易码
        $data1['amount']              = '10';                                                    //金额
        $data1['summary']              = '用户收取'.$content;                                    //摘要
        $data1['payee_identity_id']      = '20151008'.$uid;                                        //用户ID
        $data1['payee_identity_type'] = 'UID';                                                    //用户类型
        $data1['account_type']          = "SAVING_POT";                                            //账户类型
        $data1['notify_url']          = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/hongbao";
        ksort($data1);
        $data1['sign']                  = $weibopay->getSignMsg($data1, $data1['sign_type']);        //计算签名
        $setdata1                      = $weibopay->createcurl_data($data1);
        $result1                      = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata1);//模拟表单提交
        sinalog($uid, null, 6, $data1['out_trade_no'], 10, time(), null);                            //新浪操作日志
        $rs = checksinaerror($result1);
        // if($rs['response_code'] == 'APPLY_SUCCESS'){
        // 	$sina['status'] = 2;
        // 	$sina['completetime'] = time();
        // 	M('sinalog')->where("order_no = ".$order_no." and type = 6")->save($sina);
        // 	moneyactlog($uid,4,10,0,"新浪完成".$content.",信息：".$splitdata['response_message'],2);
        // }else{
        // 	$sina['status'] = 3;
        // 	$sina['completetime'] = time();
        // 	M('sinalog')->where("order_no = ".$order_no." and type = 6")->save($sina);
        // 	moneyactlog($uid,4,10,0,"新浪完成".$content.",信息：".$splitdata['response_message'],2);
        // }
    }

    /**
     * 平台垫付投资卷投资资金
     * @param $money 金额
     * @param $bid 标号
     * @return mixed
     */

    //平台垫付投资卷投资资金

    function sinapaycoupons($money, $bid)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '1001';                                                    //交易码
        $data['summary']              = "第{$bid}号标,平台付出".$money.'元投资券';                //摘要
        $data['payer_id']              = $payConfig['sinapay']['email'];                            //付款人邮箱
        $data['payer_identity_type']  = 'EMAIL';                                                //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "balance^".$money."^BASIC";                                //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        return $rs['response_code'];
    }

    /**
     * 平台垫付加息
     * @param $money 金额
     * @param $bid 标号
     * @return mixed
     */

    function sinapayjiaxi($money, $bid, $list)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '1002';                                                    //交易码
        $data['summary']              = "第".borrowidlayout1($bid)."号标,平台付出共".$money.'元加息';                //摘要
        $data['payer_id']              = $payConfig['sinapay']['email'];                            //付款人邮箱
        $data['payer_identity_type']  = 'EMAIL';                                                //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "balance^".$money."^BASIC";                                //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        if ($rs['response_code'] == "APPLY_SUCCESS") {
            foreach ($list as $l) {
                sinabatchpay($l, $bid);
            }
        }
    }

    //新浪托管代收代付接口(活动红包返现奖励）
    function sinarewardhongdong($uid, $money, $utype, $desc)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '1001';                                                    //交易码
        $data['summary']              = $desc;                                    //摘要
        $data['payer_id']              = $payConfig['sinapay']['email'];                            //付款人邮箱
        $data['payer_identity_type']  = 'EMAIL';                                                //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "balance^".$money."^BASIC";                                        //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $order_no = $data['out_trade_no'];
        $data1['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data1['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data1['request_time']          = date('YmdHis');                                            //请求时间
        $data1['user_ip']              = get_client_ip();                                                //用户IP地址
        $data1['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data1['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data1['sign_type']          = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data1['out_trade_no']        = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data1['out_trade_code']      = '2001';                                                    //交易码
        $data1['amount']              = $money;                                                    //金额
        $data1['summary']              = $desc;                                    //摘要
        $data1['payee_identity_id']      = '20151008'.$uid;                                        //用户ID
        $data1['payee_identity_type'] = 'UID';                                                    //用户类型
        if ($utype == 1) {
            $data1['account_type']          = "SAVING_POT";                                            //账户类型
        } else {
            $data1['account_type']          = "BASIC";                                            //账户类型
        }

        $data1['notify_url']          = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/hongbao";
        ksort($data1);
        $data1['sign']                  = $weibopay->getSignMsg($data1, $data1['sign_type']);        //计算签名
        $setdata1                      = $weibopay->createcurl_data($data1);
        $result1                      = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata1);//模拟表单提交
        sinalog($uid, null, 6, $data1['out_trade_no'], $money, time(), null);                            //新浪操作日志
        $rs = checksinaerror($result1);
        return $rs;
        // if($rs['response_code'] == 'APPLY_SUCCESS'){
        // 	$sina['status'] = 2;
        // 	$sina['completetime'] = time();
        // 	M('sinalog')->where("order_no = ".$order_no." and type = 6")->save($sina);
        // 	moneyactlog($uid,4,10,0,"新浪完成".$content.",信息：".$splitdata['response_message'],2);
        // }else{
        // 	$sina['status'] = 3;
        // 	$sina['completetime'] = time();
        // 	M('sinalog')->where("order_no = ".$order_no." and type = 6")->save($sina);
        // 	moneyactlog($uid,4,10,0,"新浪完成".$content.",信息：".$splitdata['response_message'],2);
        // }
    }

    //查询用户是否设置新浪支付密码
    function checkissetpaypwd($uid)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "query_is_set_pay_password";                            //绑定认证信息的接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['identity_id']          = "20151008".$uid;                        //用户ID
        $data['identity_type']          = "UID";                                                    //用户标识类型 UID
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        return checksinaerror($result);
    }
    //重定向到新浪设置支付密码
    function setpaypassword($uid)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "set_pay_password";                                        //绑定认证信息的接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['identity_id']          = "20151008".$uid;                        //用户ID
        $data['identity_type']          = "UID";                                                    //用户标识类型 UID
        $data['return_url']              = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/charge#fragment-1";    //回调充值页面
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        return $rs['redirect_url'];
    }

    //新浪查询用户基本余额
    function querybalance($uid, $type = 0)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "query_balance";                                    //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                    //接口版本
        $data['request_time']          = date('YmdHis');                                        //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['identity_id']          = '20151008'.$uid;                                    //用户ID
        $data['identity_type']          = 'UID';                                                //ID类型
        $data['account_type']          = 'BASIC';
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $deresult = urldecode($result);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            if ($splitdata["response_code"] == "APPLY_SUCCESS") {
                if ($type == 0) {
                    return $splitdata['available_balance'];
                } else {
                    return $splitdata['balance']-$splitdata['available_balance'];
                }
            } else {
                return 0.00;
            }
        } else {
            return "sing error!" ;
            exit();
        }
    }

    //新浪查询用户存钱罐余额
    function querysaving($uid, $type = 0)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "query_balance";                                    //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                    //接口版本
        $data['request_time']          = date('YmdHis');                                        //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['identity_id']          = '20151008'.$uid;                                    //用户ID
        $data['identity_type']          = 'UID';                                                //ID类型
        $data['account_type']          = 'SAVING_POT';
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $deresult = urldecode($result);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            if ($splitdata["response_code"] == "APPLY_SUCCESS") {
                if ($type == 0) {
                    return $splitdata['available_balance'];
                } else {
                    return $splitdata['balance']-$splitdata['available_balance'];
                }
            } else {
                return 0.00;
            }
        } else {
            return "sing error!" ;
            exit();
        }
    }

    //新浪退款接口
    function sinarefund($orderno, $money, $uid, $borrow_id)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_refund";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                    //接口版本
        $data['request_time']          = date('YmdHis');                                        //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['out_trade_no']          = date('YmdHis').mt_rand(100000, 999999);            //交易号
        $data['orig_outer_trade_no']  = $orderno;                                            //退款订单号
        $data['refund_amount']          = $money;                                                //退款金额
        $data['summary']              = '投标失败';
        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/refundnotify";
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $deresult = urldecode($result);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            moneyactlog($uid, 6, $money, 0, '投标失败退款处理退款单号：'.$orderno."，信息：".$splitdata["response_message"], 1);
            if ($splitdata["response_code"] == "APPLY_SUCCESS") {
                sinalog($uid, $borrow_id, 5, $data['out_trade_no'], $money, time(), null, null);
            }
        }
    }

    //新浪托管提现接口
    function sinawithdraw($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_withdraw";                                //绑定认证信息的接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                                    // 交易订单号
        if ($sina['phone'] == 'yes') {
            $data['return_url']           = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/user/sinareturn"; // *支付成功后跳转回的页面链接
        } else {
            $data['return_url']           = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/withdraw/sinareturn"; // *支付成功后跳转回的页面链接
        }

        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/withdrawnotify"; //新浪回调处理提现操作
        $data['identity_id']          = '20151008'.$sina['uid'];                                //收款人ID
        $data['identity_type']          = 'UID';                                                    //ID类型
        $data['user_ip']=get_client_ip();
        $data['amount']                  = $sina['withdraw'];//$_REQUEST['withdraw'];				//金额
        $data['account_type']          = $sina['account_type'];                                    //账户类型
        $data['user_fee']              = $sina['user_fee'];                                            //用户承担的手续费
        $data['extend_param']          = 'customNotify^Y';                        //申请成功通知
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $rs =  $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);
        //$ad=checksinaerror($rs);
        sinalog($sina['uid'], null, 2, $data['out_trade_no'], $sina['withdraw'], time(), null, null);
        if ($sina["fee_orderno"] != null) {
            $data1['money'] = $data['amount'];
            $data1['money_orderno'] = $data['out_trade_no'];
            $data1['money_status'] = 1;
            M("withdrawlog")->where("uid={$sina['uid']} AND fee_orderno={$sina['fee_orderno']}")->save($data1);
        }
        return $rs;
    }

    //新浪托管收取手续费代收接口
    function sinafreecollecttrade($sina)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_hosting_collect_trade";                            //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '1001';                                                    //交易码
        $data['summary']              = '提现手续费';                                            //摘要
        $data['payer_id']              = '20151008'.$sina['uid'];                                //用户ID
        $data['payer_identity_type']  = 'UID';                                                    //ID类型
        $data['payer_ip']=get_client_ip();
        $data['pay_method']              = "online_bank^".$sina['fee']."^SINAPAY,DEBIT,C";            //支付方式：支付方式^金额^扩展|支付方式^金额^扩展。扩展信息内容以“，”分隔
        $data['extend_param']          = "channel_black_list^online_bank";
        $data['collect_trade_type']      = "pre_auth";                                                //冻结  代收交易类型
        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/withdrawfreenotify"; //新浪回调处理提现操作
        if ($sina['phone']=="yes") {
            $data['return_url']           = session('xieyi')."://".$_SERVER['HTTP_HOST']."/M/user/sinawithdrawfee?withdraw=".$sina['withdraw']."&fee_orderno=".$data['out_trade_no']; // *支付成功后跳转回的页面链接
        } else {
            $data['return_url']           = session('xieyi')."://".$_SERVER['HTTP_HOST']."/member/Withdraw/sinawithdrawfee?withdraw=".$sina['withdraw']."&fee_orderno=".$data['out_trade_no']; // *支付成功后跳转回的页面链接
        }
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        sinalog($sina['uid'], null, 8, $data['out_trade_no'], $sina['fee'], time(), null, null);
        withdrawlog($sina['uid'], $sina['fee'], $data['out_trade_no']);
        return $result;
    }

    // 新浪代付接口
    function sinapayfreetrade($fee)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "create_single_hosting_pay_trade";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['user_ip']              = get_client_ip();                                                //用户IP地址
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['out_trade_no']         = date('YmdHis').mt_rand(100000, 999999);                // 交易订单号
        $data['out_trade_code']          = '2001';                                                    //交易码
        $data['amount']                  = $fee;                                                    //金额
        $data['summary']              = '收取提现手续费';                                        //摘要
        $data['payee_identity_id']      = $payConfig['sinapay']['email'];                            //收款人邮箱
        $data['payee_identity_type']  = 'EMAIL';
        $data['notify_url']              = "https://".$_SERVER['HTTP_HOST']."/Home/Sinanotify/freenotify"; //新浪回调处理提现操作										//ID类型
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        sinalog(null, null, 9, $data['out_trade_no'], $fee, time(), null, null);
    }

    //查询绑定银行卡
    function queryusercard($uid)
    {
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $cardlist = $sina->querycard($uid);
        if ($cardlist != null) {
            $card = explode("^", $cardlist);
            return $card[0];
        } else {
            return false;
        }
    }

    //查询用户收支明细
    function queryusedetail($uid, $starttime=null, $endtime=null, $page_no=1)
    {
        $usertype = "SAVING_POT";
        $utype = M("members")->where("id={$uid}")->field("user_regtype")->find();
        if ($utype['user_regtype']==1) {
            $usertype = "SAVING_POT";
        } else {
            $usertype = "BASIC";
        }
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $detaillist = $sina->queryuserdet($uid, $usertype, $starttime, $endtime, $page_no);

        if ($detaillist != null) {
            return $detaillist;
        } else {
            return false;
        }
    }

    //解绑银行卡
    function unbindcard($uid, $cardid)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service'] = "unbinding_bank_card";                                    //绑定认证信息的接口名称
        $data['version'] = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time'] = date('YmdHis');                                        //请求时间
        $data['partner_id'] = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset'] = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type'] = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['identity_id'] = "20151008".$uid;                                //用户ID
        $data['identity_type'] = "UID";                                                //用户标识类型 UID
        $data['card_id'] = $cardid;                                                    //钱包编号
        $data["client_ip"]=get_client_ip();
        ksort($data);                                                                //对签名参数数据排序
        $data['sign'] = $weibopay->getSignMsg($data, $data['sign_type']);            //计算签名
        $setdata = $weibopay->createcurl_data($data);
        $result = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);        //模拟表单提交
        return checksinaerror($result);
    }

    //验证新浪接口响应信息
    function checksinaerror($data)
    {
        import("@.Oauth.sina.Weibopay");
        $weibopay = new Weibopay();
        $deresult = urldecode($data);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序

        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            return $splitdata;
        } else {
            return "sing error!" ;
            exit();
        }
    }

    function querywithdraw($uid, $utype=1, $starttime=null, $endtime=null, $page_no=1)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service'] = "query_hosting_withdraw";                                //接口名称
        $data['version'] = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time'] = date('YmdHis');                                        //请求时间
        $data['partner_id'] = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset'] = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type'] = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['identity_id'] = "20151008".$uid;                                        //用户ID
        $data['identity_type'] = "UID";                                                //用户标识类型 UID
        if ($utype == 1) {
            $data['account_type'] = "SAVING_POT";
        } else {
            $data['account_type'] = "BASIC";
        }
        if ($starttime != null) {
            $data['start_time'] = date("YmdHis", strtotime($starttime." 00:00:00"));//开始时间
        } else {
            $data['start_time'] = date("Ymd", strtotime("-1 month +1 day"))."000000";                    //开始时间
        }
        if ($endtime != null) {
            $data['end_time'] = date("YmdHis", strtotime($endtime." 23:59:59"));    //结束时间
        } else {
            $data['end_time'] = date("Ymd")."235959";                                        //结束时间
        }
        $data['page_no'] = $page_no;                                                //页号
        ksort($data);                                                    //对签名参数数据排序
        $data['sign'] = $weibopay->getSignMsg($data, $data['sign_type']);//计算签名
        $setdata = $weibopay->createcurl_data($data);
        $result = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        return checksinaerror($result);
    }


    function checkwithdraw($uid, $utype, $page_no=1)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service'] = "query_hosting_withdraw";                                //接口名称
        $data['version'] = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time'] = date('YmdHis');                                        //请求时间
        $data['partner_id'] = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset'] = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type'] = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['identity_id'] = "20151008".$uid;                                        //用户ID
        $data['identity_type'] = "UID";                                                //用户标识类型 UID
        if ($utype == 1) {
            $data['account_type'] = "SAVING_POT";
        } else {
            $data['account_type'] = "BASIC";
        }
        $data['start_time'] = date('Ym01', time())."000000";                            //开始时间
        $data['end_time'] = date("Ymd")."235959";                                    //结束时间
        $data['page_no'] = $page_no;                                            //页大小
        ksort($data);                                                    //对签名参数数据排序
        $data['sign'] = $weibopay->getSignMsg($data, $data['sign_type']);//计算签名
        $setdata = $weibopay->createcurl_data($data);
        $result = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        return checksinaerror($result);
    }

    //新浪银行卡管理接口
    function bankcard($uid, $return_url)
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "web_binding_bank_card";                        //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                        //接口版本
        $data['request_time']          = date('YmdHis');                                            //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                    //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];                //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                        //签名方式 MD5
        $data['identity_id']         = "20151008".$uid;                // 交易订单号
        $data['identity_type']          = 'UID';                                                    //交易码 2001代付借款金 2002代付（本金/收益）金
        $data['could_unbind']                  = "Y";                                    //金额
        $data['return_url']              = $return_url;
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mgs'], $setdata);//模拟表单提交
        $rs = checksinaerror($result);
        return $rs['redirect_url'];
    }

    //存钱罐收益接口
    function piggybankearnings()
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $data['service']              = "query_fund_yield";                                    //接口名称
        $data['version']              = $payConfig['sinapay']['version'];                    //接口版本
        $data['request_time']          = date('YmdHis');                                        //请求时间
        $data['partner_id']          = $payConfig['sinapay']['partner_id'];                //合作者身份ID
        $data['_input_charset']      = $payConfig['sinapay']['_input_charset'];            //网站编码格式
        $data['sign_type']              = $payConfig['sinapay']['sign_type'];                    //签名方式 MD5
        $data['fund_code']              = C("PIGGY");
        ksort($data);
        $data['sign']                  = $weibopay->getSignMsg($data, $data['sign_type']);        //计算签名
        $setdata                      = $weibopay->createcurl_data($data);
        $result                          = $weibopay->curlPost($payConfig['sinapay']['mas'], $setdata);//模拟表单提交
        $deresult = urldecode($result);
        $splitdata = array();
        $splitdata = json_decode($deresult, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata["sign_type"])) {
            if ($splitdata["response_code"] == "APPLY_SUCCESS") {
                return $splitdata;
            } else {
                return 0.00;
            }
        } else {
            return "sing error!" ;
            exit();
        }
    }


    //用户身份认证（新浪创建激活会员：个人（手机认证，实名认证）企业（企业审核））
    /**
     * @param $sinadata
     * @return true
     */
    function sinamember($sinadata)
    {
        $ustatus = M("members_status")->where("uid={$sinadata['identity_id']}")->field("sina_member_status,sina_phone,id_status")->find();
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        if ($ustatus["sina_member_status"] == 0) {
            $rs = $sina->createmember($sinadata);  //创建激活会员
            if ($rs === true) {
                $data["sina_member_status"] = 1;
                M("members_status")->where("uid={$sinadata['identity_id']}")->save($data);
            } else {
                return $rs;
            }
        }
        if ($sinadata["member_type"] == 1) {
            $utype["user_regtype"] = 1;
            M("members")->where("id={$sinadata['identity_id']}")->save($utype);
            //个人信息认证
            if ($ustatus["sina_phone"] == 0) {
                //手机认证
                //$rs = $sina->bindingverify($sinadata);
                //if($rs === true){
                    $data1["sina_phone"] = 1;
                M("members_status")->where("uid={$sinadata['identity_id']}")->save($data1);
                //}else{
                //	return $rs;
                //}
            }
            if ($ustatus["id_status"] == 0) {
                //实名认证
                $rs = $sina->setrealname($sinadata);
                if ($rs === true) {
                    //20170727 实名后送600元投资券
                    realnameCouponsGift($sinadata['phone']);
                    
                    // $userlist=M("members")->where(array("user_phone"=>$sinadata["phone"]))->find();
                    // $time=$userlist["reg_time"]-strtotime("2016-08-18 23:59:59");
                    // if ($time>0) {//只是新用户实名认证才送投资券
                    //     /*****************实名之后送4张投资券******************/
                    //     $arr = [];
                    //     $arr[0]["user_phone"] = $sinadata["phone"];
                    //     $arr[0]["money"] = 10; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                    //     $arr[0]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//strtotime(C("TOUZIQUAN_DEADTIME"));//取配置文件里面的2016-12-31
                    //     $arr[0]["status"] = 0;
                    //     $arr[0]["serial_number"] = time() . rand(100000, 999999);
                    //     $arr[0]["type"] = 1;
                    //     $arr[0]["name"] = "实名认证";
                    //     $arr[0]["addtime"] = date("Y-m-d H:i:s", time());
                    //     $arr[0]["isexperience"] = 1;
                    //     $arr[0]["use_money"] = 1000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                    //     M("coupons")->add($arr[0]);
                    //     $arr[1]["user_phone"] = $sinadata["phone"];
                    //     $arr[1]["money"] = 20; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                    //     $arr[1]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//取配置文件里面的2016-12-31
                    //     $arr[1]["status"] = 0;
                    //     $arr[1]["serial_number"] = time() . rand(100000, 999999);
                    //     $arr[1]["type"] = 1;
                    //     $arr[1]["name"] = "实名认证";
                    //     $arr[1]["addtime"] = date("Y-m-d H:i:s", time());
                    //     $arr[1]["isexperience"] = 1;
                    //     $arr[1]["use_money"] = 2000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                    //     M("coupons")->add($arr[1]);
                    //     $arr[1]["serial_number"] = time() . rand(100000, 999999);
                    //     M("coupons")->add($arr[1]);
                    //     $arr[2]["user_phone"] = $sinadata["phone"];
                    //     $arr[2]["money"] = 50; //100投资券拆分为10投资券1张，20投资券2张，50投资券1张
                    //     $arr[2]["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+3 months -1 days")));//取配置文件里面的2016-12-31
                    //     $arr[2]["status"] = 0;
                    //     $arr[2]["serial_number"] = time() . rand(100000, 999999);
                    //     $arr[2]["type"] = 1;
                    //     $arr[2]["name"] = "实名认证";
                    //     $arr[2]["addtime"] = date("Y-m-d H:i:s", time());
                    //     $arr[2]["isexperience"] = 1;
                    //     $arr[2]["use_money"] = 5000; //投资券的使用比例按照100:1的标准，即1000元抵扣10元，2000元抵扣20元，5000元抵扣50元。
                    //     M("coupons")->add($arr[2]);
                    //     unset($arr);
                    //     $content = "尊敬的链金所用户您好！100元投资券已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
                    //     sendsms($sinadata["phone"], $content);
                    // }
                    /********************************************************************* */
                    $data2["id_status"] = 1;
                    $result = M("members_status")->where("uid={$sinadata['identity_id']}")->save($data2);
                    import("@.Oauth.ancun.Shang");
                    $shang = new Shang();
                    $shang->shanglogin($sinadata["phone"], $sinadata['identity_id'], $sinadata['cert_no'], "中国", $sinadata['real_name']);
                    the_may_active("realname", $sinadata['identity_id']);
                    return $result;
                } else {
                    if ($rs=="实名已认证") {//对于新浪已经实名的但是本地数据库状态没有修改的
                        M("members_status")->where("uid={$sinadata['identity_id']}")->save(array("id_status"=>1));
                    }

                    return $rs;
                }
            }
        } elseif ($sinadata["member_type"] == 2) {
            //企业信息认证
            $utype["user_regtype"] = 2;
            M("members")->where("id={$sinadata['identity_id']}")->save($utype);
            $minfo = M("members")->where("id={$sinadata['identity_id']}")->find();
            $rs = $sina->auditmember($sinadata);
            
            return $rs;
        }
    }

    /**
     * 代付提现卡
     * @param $uid
     * @param $amount
     * @param $bid
     * @param  $type 1 默认标， 2 债权标
     */
    function paytocard($uid, $amount, $bid, $type=1)
    {
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $cardid = queryusercard($uid);
        $orderno = date('YmdHis').mt_rand(100000, 999999);
        $newbid = borrowidlayout1($bid);
        $sina->paytocardtrade($orderno, $uid, $amount, $newbid, $cardid, $type);
        if ($type==1) {
            sinalog($uid, $bid, 14, $orderno, $amount, time(), null);
        } else {
            sinalog($uid, $bid, 21, $orderno, $amount, time(), null);//债权代付卡
        }
    }

    /**
     * 存钱罐下载文件
     * @return array
     */
    function sftp()
    {
        import("@.Oauth.sina.Weibopay");
        $payConfig = FS("Webconfig/payconfig");
        $weibopay = new Weibopay();
        $filename = array();
        $filename["jymx-zjtg"] = 'jymx-zjtg';//交易明细资金托管
        // $filename["zwmx-yh-cqg"] = 'zwmx-yh-cqg';//账务明细-用户-存钱罐
        $filename["zhye-yh-cqg"] = 'zhye-yh-cqg';//存钱罐账户余额及收益
        $date=date('Ymd', time()-1*24*3600);//年月日日期
        //$date='20160606';
        $filetype = ".zip";//目前对账文件都是打成zip压缩包提供下载
        //按照对账日期创建文件夹
        $zipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" . $date."/";
        $unzipflo=dirname(dirname(dirname(__FILE__)))."/UF/tmp/zip" .$date."/unzip/";
        $zipfloresult = $weibopay->mkFolder($zipflo);
        $unzipfloresult = $weibopay->mkFolder($unzipflo);
        $zip = new ZipArchive;
        $mydate=date("Y-m-d", time());
        foreach ($filename as $key => $value) {
            $result = $weibopay->sftp_download($zipflo, $date . "_" . $value . $filetype);
            if ($result) {
                $res = $zip->open($zipflo.$date . "_" . $value . $filetype);
                if ($res === true) {
                    //解压缩到文件夹
                    $serveresult=$zip->extractTo($unzipflo);
                    if ($serveresult) {
                        file_put_contents('sftplog.txt', '保存成功'.$unzipflo."\n", FILE_APPEND);
                    } else {
                        cunqianguan_filelog($mydate);
                        file_put_contents('sftplog.txt', '保存失败'.$unzipflo."\n", FILE_APPEND);
                        die();
                    }
                    $zip->close();
                } else {
                    cunqianguan_filelog($mydate);
                    file_put_contents('sftplog.txt', '解压缩失败'.$zipflo.$date . "_" . $value . $filetype."\n", FILE_APPEND);
                    die();
                }
            } else {
                cunqianguan_filelog($mydate);
                file_put_contents('sftplog.txt', '下载失败'.$unzipflo."\n", FILE_APPEND);
                die();
            }
        }
        $handler = opendir($unzipflo);
        while (($filename = readdir($handler)) !== false) {
            if ($filename !="." && $filename !="..") {
                $row = 1;
                $file=$unzipflo.$filename;
                $handle = fopen($file, "r");
                $resultarray=array();
                while ($data = fgetcsv($handle)) {
                    //统计数据行数
                    $num = count($data);
                    $row++;
                    //对数组进行迭代，迭代每条数据
                    for ($c = 0; $c < $num; $c++) {
                        //注意中文乱码问题
                        $data[$c] = iconv("gbk", "utf-8//IGNORE", $data[$c]);
                        //将数据放在2维数组进行存放
                        $resultarray[$row][$c] = $data[$c];
                    }
                }
                // return $resultarray;
                fclose($handle);
            }
        }
        closedir($handler);
        return $resultarray;
    }

    /**
     * 存钱罐文件下载失败记录下来，方便运营人员手动下载
     */
    function cunqianguan_filelog($date)
    {
        $msg=$date."新浪存钱罐收益下载失败，需要运营手动下载";
        sendsms(C("NOTICE_TEL.cunqianguan"), $msg);
        $member_piggfaillog=M("member_piggfaillog");
        $list=$member_piggfaillog->where(array("addtime"=>$date))->find();
        if ($list) {
            if ($list["status"]==1) {
                $member_piggfaillog->where(array("id"=>$list["id"]))->save(array("status"=>0));
            }
        } else {
            $member_piggfaillog->add(
                array(
                    "content"=>"新浪存钱罐收益下载失败，需要运营手动下载",
                    "status"=>0,
                    "addtime"=>$date
                )
            );
        }
    }

    function check_other_login()
    {
        if (strtolower(ACTION_NAME) != 'verify' && strtolower(ACTION_NAME) != 'login' && strtolower(ACTION_NAME)!='logincheck'&&strtolower(ACTION_NAME)!='index') {
            $session_id=session_id();
            $uid=session('admin');

            $redis = new Redis();
            $redis_info=C("REDIS_INFO");
            $redis->connect($redis_info['host'], 6379);
            $redis->auth($redis_info['auth']);
            $cach_id=$redis->get($uid);

            if ($session_id!=$cach_id) {
                session(null);
                echo '<script type="text/javascript">alert("您的账号在其他地方登陆");top.location.href="/admin/index/login";</script>';
                exit;
            }
        }
    }

    function get_day($day_string)
    {
        $day_array=explode("+", $day_string);
        $day=intval(mb_strcut($day_array[0], 0, mb_strlen($day_array[0])-1));
        if (count($day_array)==2) {
            $day2=intval(mb_strcut($day_array[1], 0, mb_strlen($day_array[0])-1));
            $day+=$day2;
        }
        if (mb_strpos($day_string, "月")) {
            $day=$day*30;
        }

        return $day;
    }
    function show_contract($borrow_id)
    {
        $file="html/contract/contract_".$borrow_id.".html";
        if (file_exists($file)) {
            echo file_get_contents($file);
            exit;
        }
    }

    /**
     * @param $borrow_id
     * @param int $type 1 普通标  2 债权标
     * @return int
     */
    function cal_deadline($borrow_id, $type=1)
    {
        $where['id']=$borrow_id;
        if ($type==1) {
            $info=M('borrow_info')->field("borrow_duration,deadline,borrow_duration_txt,second_verify_time,repayment_type,product_type")->where($where)->select();
        } else {
            $info=M('debt_borrow_info')->field("borrow_duration,deadline,borrow_duration_txt,second_verify_time,repayment_type,product_type")->where($where)->select();
        }


        if ($info[0]['product_type'] != 1) {
            return $info[0]['deadline'];
        }

        if ($info[0]['repayment_type']!=1) {
            return $info[0]['deadline'];
        }
        if ($info[0]['borrow_duration_txt']=='') {
            return $info[0]['deadline'];
        }

        $total=get_day($info[0]['borrow_duration_txt']);
        if ($total==intval($info[0]['borrow_duration'])) {
            return $info[0]['deadline'];
        } else {  //N+M模式
            $target=($total-1)*24*60*60+strtotime(date("Y-m-d 23:59:59", $info[0]['second_verify_time']));
            return $target;
        }
    }

    function partake_filter($id)
    {
        $filter=C("CCFAX_USER");
        return in_array($id, $filter);
    }

    /**
     * 计算还款金额
     * @param $borrow_id
     * @param $sort_order
     * @param int $output
     * @param int $type 1 普通标 2 债权标
     * @return string
     */
    function cal_repayment_money($borrow_id, $sort_order, $output=0)
    {
        $pre = C('DB_PREFIX');
        $done = false;
        $borrowDetail = D('investor_detail');
        $binfo = M("borrow_info")->field("id,borrow_uid,n_interest,n_colligate_fee,colligate_fee,product_type,add_time,second_verify_time,borrow_interest_rate,borrow_type,borrow_money,borrow_duration,repayment_type,has_pay,total,deadline")->find($borrow_id);
        $b_member=M('members')->field("user_name")->find($binfo['borrow_uid']);
        if ($binfo['has_pay']>=$sort_order) {
            return "本期已还过，不用再还";
        }
        if ($binfo['has_pay'] == $binfo['total']) {
            return "此标已经还完，不用再还";
        }
        if (($binfo['has_pay']+1)<$sort_order) {
            return "对不起，此借款第".($binfo['has_pay']+1)."期还未还，请先还第".($binfo['has_pay']+1)."期";
        }

        $voxe = $borrowDetail->field('sort_order,sum(capital) as capital, sum(interest) as interest,sum(interest_fee) as interest_fee,deadline,substitute_time')->where("borrow_id={$borrow_id} and status!=-1 and is_debt = 0")->group('sort_order')->select();
        foreach ($voxe as $ee=>$ss) {
            if ($ss['sort_order']==$sort_order) {
                $vo = $ss;
            }
        }

        // 复审通过后开始计算借款人利息 获取复审时间
        //$atime = M('borrow_investor')->field("add_time")->where("borrow_id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
        $atime = $binfo['second_verify_time'];
        Log::write("复审时间".date("Y-m-d H:i:s", $atime));
        //企业直投与普通标,判断还款期数不一样
        //借款天数、还款时间
        //利息计算公式 借款总金额*(借款利率/36000)*借款天数
        $borrow_money           = intval($binfo['borrow_money']); //借款总额
        $borrow_interest_rate   = $binfo['borrow_interest_rate']; //借款利率 此处因为利率转成了整数 20% 转成 2
        $day_rate               =  $borrow_interest_rate/36000;//计算出天标的天利率

        $colligate_fee =0;//综合服务费
        if ($binfo['repayment_type'] == 1) { //债权标没有综合服务费
            // 更新利息 M('investor_detail')
            $investor_uid = M('investor_detail')->where('borrow_id='.$borrow_id." and status!=-1 and is_debt = 0")->select();


            $vo['interest'] = 0;
            $Detail = M("investor_detail");
            // 提单质押标
            if ($binfo['product_type'] == 1 || $binfo['product_type'] == 3||$binfo['product_type']==6||$binfo['product_type'] ==7||$binfo['product_type'] ==8||$binfo['product_type'] ==10) {
                $currentTime            = strtotime(date('Y-m-d')); //当前需还款时间
                $issueTime              = strtotime(date('Y-m-d', $atime));//复审后的时间

                $binfo['deadline']=cal_deadline($borrow_id);//修正bug.
                if (strtotime(date('Y-m-d', $binfo['deadline'])) == $currentTime && $borrow_id <= 325) {
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
                } elseif (strtotime(date('Y-m-d', $binfo['deadline']))>$currentTime) {
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
                } else {
                    $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
                }

                if ($BorrowingDays == 0) {
                    $BorrowingDays = $BorrowingDays +1;
                }
                Log::write("借款天数".$BorrowingDays);
                // 综合服务费 利率/36000 * 借款金额 * 天数  提单、现货的综合服务费
                $colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
                foreach ($investor_uid as $iteme) {
                    $tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
                    $vo['interest'] += $tou_interest;
                    unset($iteme['id']);
                    //$Detail->execute("update `{$pre}investor_detail` set `interest`={$tou_interest} WHERE `capital`={$iteme['capital']} and `borrow_id`={$borrow_id}");
                }
            }
            // 转现货质押标
            if ($binfo['product_type'] == 2) {

                // 投资人额度/标的总额*旧利息
                $vo['interest'] = 0;
                $xhtime = M('borrow_info')->field("add_time")->where("id={$borrow_id} and borrow_uid={$binfo['borrow_uid']}")->find();
                $currentTime            = strtotime(date('Y-m-d')); //当前时间
                $issueTime              = strtotime(date('Y-m-d', $xhtime['add_time']));//转现货时间
                $binfo['deadline']=cal_deadline($borrow_id);//修正bug.
                if (strtotime(date('Y-m-d', $binfo['deadline'])) == $currentTime && $borrow_id <= 325) {
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24);//计算借款天数 不足一天按一天算
                } elseif (strtotime(date('Y-m-d', $binfo['deadline']))>$currentTime) {
                    $BorrowingDays = ceil(($currentTime - $issueTime)/3600/24+1);//计算借款天数 不足一天按一天算
                } else {
                    $BorrowingDays = ceil(($binfo['deadline'] - $issueTime)/3600/24);//逾期的时候，按照deadline算，后续会计算逾期利息
                }
                if ($BorrowingDays == 0) {
                    $BorrowingDays = $BorrowingDays +1;
                }
                //计算还款天数，如果不足70%天，需要按70%算利息
                /***********************************************/
                /*
                $duration=$binfo['borrow_duration'];
                $limit_borrow_day=ceil($duration*0.7);
                if($BorrowingDays<$limit_borrow_day)
                    $BorrowingDays=$limit_borrow_day;*/

                $totalinterest = 0;
                // 综合服务费 利率/36000 * 借款金额 * 天数  提单转现货的综合服务费
                $colligate_fee = getFloatValue($binfo['colligate_fee']/36000*$binfo['borrow_money']*$BorrowingDays, 2);
                foreach ($investor_uid as $iteme) {
                    $tou_interest = getFloatValue($iteme['capital']*$day_rate*$BorrowingDays, 2);
                    $vo['interest'] += $tou_interest;
                }
                foreach ($investor_uid as $n) {
                    $d_interest = getFloatValue($n['capital']/$binfo['borrow_money']*($vo['interest']+$binfo['n_interest']), 2);
                    $totalinterest += $d_interest;
                    unset($iteme['id']);
                    // print_r($binfo['n_interest']."<br>");
                    //$Detail->execute("update `{$pre}investor_detail` set `interest`={$d_interest} WHERE `capital`={$n['capital']} and `borrow_id`={$borrow_id}");
                }
                $vo['interest'] = $totalinterest;
                $colligate_fee +=$binfo['n_colligate_fee'];
            }
        } else {
            $field = "sum(capital) as capital,sum(interest) as interest";
            $vo = M("investor_detail")->field($field)->where("borrow_id={$borrow_id} AND `sort_order`={$sort_order} and status!=-1 and is_debt = 0")->find();
            $money = $vo['capital']+$vo['interest'];
        }

        //$this->ajaxreturn($vo['interest'],"还款",2);
        $pay_frist=D("borrow_info_additional")->is_pay_frist($borrow_id);//判断此标是否提前收取了综合服务费。 1表示已经收取。
        if ($pay_frist) {
            $colligate_fee=0;
        }

        import("@.conf.borrow_expired");
        $expired=new borrow_expired($borrow_id, $sort_order);
        $expired__money=$expired->get_expired__money();
        // if($type==2){//如果是债权的综合服务费不计算
        // 	$colligate_fee=0;
        // }
        if ($output==0) {
            if ($expired__money==0) {
                $hh = $vo['capital']+$vo['interest']+$colligate_fee;
                ajaxmsg("本金(".$vo['capital'].")+利息(".$vo['interest'].")+综合服务费(".$colligate_fee.")=".$hh);
            } else {
                $hh = $vo['capital']+$vo['interest']+$colligate_fee+$expired__money;
                ajaxmsg("本金(".$vo['capital'].")+利息(".$vo['interest'].")+综合服务费(".$colligate_fee.")+逾期罚息(".$expired__money.")=".$hh);
            }
        } else {
            if ($expired__money==0) {
                $hh = $vo['capital']+$vo['interest']+$colligate_fee;
            } else {
                $hh = $vo['capital']+$vo['interest']+$colligate_fee+$expired__money;
            }
            $info="本金(".$vo['capital'].")+利息(".$vo['interest'].")+综合服务费(".$colligate_fee.")+逾期罚息(".$expired__money.")=".$hh;
            return $info;
        }
    }

     //标号编排
    function borrowidlayout1($borrowid)
    {
        $conf = C(RENUMBER_BORROW);
        if ($conf['enable']==1) {
            $newgrade =  $conf['new_grade'];
            if ($borrowid<$newgrade) {
                $bid=$borrowid;
            } else {
                $protype = M("borrow_info")->where("id={$borrowid}")->field("product_type")->find();
                if ($protype["product_type"]==1||$protype["product_type"]==2||$protype["product_type"]==3) {
                    $bid = M('borrow_pledge')->where("borrow_id=".$borrowid)->find();
                    $bid="ZJ".$bid['id'];
                } elseif ($protype["product_type"]==4) {
                    $bid = M('borrow_finance')->where("borrow_id=".$borrowid)->find();
                    $bid="RJ".$bid['id'];
                } elseif ($protype["product_type"]==6) {
                    $bid = M('borrow_credit')->where("borrow_id=".$borrowid)->find();
                    $bid="XJ".$bid['id'];
                } elseif ($protype["product_type"]==7) {
                    $bid = M('borrow_optimal')->where("borrow_id=".$borrowid)->find();
                    $bid="YJ".$bid['id'];
                } elseif ($protype["product_type"] == 5) {
                    $bid = M('borrow_installment')->where("borrow_id=".$borrowid)->find();
                    $bid="FQG".$bid['id'];
                } elseif ($protype["product_type"] == 8) {
                    $bid = M('borrow_guarantee')->where("borrow_id=".$borrowid)->find();
                    $bid="BJ".$bid['id'];
                } elseif ($protype["product_type"] == 10) {
                    $bid = M('borrow_assets')->where("borrow_id=".$borrowid)->find();
                    $bid="ZJB".$bid['id'];
                }
            }
            return $bid;
        } else {
            return $borrowid;
        }
    }

     function zhaiquan_borrowidlayout1($borrowid)
     {
         $protype = M("debt_borrow_info")->where("id={$borrowid}")->field("product_type")->find();
         if ($protype["product_type"]==1||$protype["product_type"]==2||$protype["product_type"]==3) {
             $bid="ZJ";
         } elseif ($protype["product_type"]==4) {
             $bid="RJ";
         } elseif ($protype["product_type"]==6) {
             $bid="XJ";
         } elseif ($protype["product_type"]==7) {
             $bid="YJ";
         } elseif ($protype["product_type"] == 5) {
             $bid="FQG";
         } elseif ($protype["product_type"] == 8) {
             $bid="BJ";
         }
         return $bid.$borrowid;
     }




    //推荐人修改操作权限
    function permissions($uid)
    {
        $info = M("recommend_permissions")->where("uid={$uid}")->find();
        if (empty($info)||$info['permissions']==0) {
            $permissions=0;
        } elseif ($info['permissions']==1) {
            $permissions=1;
        } elseif ($info['permissions']==2) {
            $permissions=2;
        } elseif ($info['permissions']==3) {
            $permissions=3;
        }
        return $permissions;
    }

    function convert($str)
    {
        $arr = explode(";", $str);
        array_filter($arr);
        return $arr;
    }

    //分销系统 分销效果
    function setDistribut($data)
    {
        if ($data["usrid"] != null) {
            $usr_id = $data["usrid"];
            $source = 0;
            $is_active = 0;
            $amount = 0;
            if ($data['source'] > 0) {
                $source = $data["source"];
            }
            if ($data["is_active"]>0) {
                $is_active = 1;
            }
            if ($data["amount"]>0) {
                $amount = $data["amount"];
            }
            setCpsResult($usr_id, $source, $is_active, $amount);
        }
    }
    //新增或更新分销记录
    function setCpsResult($usr_id, $source, $is_active, $amount)
    {
        if ($usr_id == null) {
            exit;
        }
        $estimate_commission = 0;

        $rs = M("distribution")->where("cps_date = '".date("Y-m-d")."' AND usr_id = {$usr_id}")->find();
        $data["usr_id"] = $usr_id;

        //判断来源 1:第三方 2:链接 3:二维码
        switch ($source) {
            case '1':
                if ($rs) {
                    $data['form_1'] = $rs["form_1"]+1;
                    $data['hits'] = $rs["hits"]+1;
                } else {
                    $data['form_1'] = 1;
                    $data['hits'] = 1;
                }
                break;
            case '2':
                if ($rs) {
                    $data['form_2'] = $rs["form_2"]+1;
                    $data['hits'] = $rs["hits"]+1;
                } else {
                    $data['form_2'] = 1;
                    $data['hits'] = 1;
                }
                break;
            case '3':
                if ($rs) {
                    $data['form_3'] = $rs["form_3"]+1;
                    $data['hits'] = $rs["hits"]+1;
                } else {
                    $data['form_3'] = 1;
                    $data['hits'] = 1;
                }
                break;
        }

        //投资金额不为0 计算预估收入
        if ($amount != 0) {
            $rate = getRate();
            $estimate_commission = number_format($amount*$rate, 2, '.', '');
        }

        if ($rs) {
            //有当天数据
            if ($amount != 0) {
                $data["invest_amount"] = $amount+$rs["invest_amount"];
                $data["estimate_commission"] = $estimate_commission+$rs["estimate_commission"];
            }
            //判断是否为当天有效客户
            if ($is_active == 1) {
                $data['customer_cnt'] = $rs["customer_cnt"]+1;
            }
            M("distribution")->where("id = {$rs['id']}")->save($data);
        } else {
            //无当天数据
            $data["invest_amount"] = $amount;
            $data["estimate_commission"] = $estimate_commission;
            $data["cps_date"] = date("Y-m-d");
            M("distribution")->add($data);
        }
    }
    function getRate()
    {
        $rate = 0;
        $ratecount = M('policy')->where("(end_time > '".date('Y-m-d')."' OR is_permanent = 1) AND check_status = 1")->count();
        $ratedata1 = M('policy')->where("end_time > '".date('Y-m-d')."' AND check_status = 1")->order("begin_time asc")->find();
        $ratedata2 = M('policy')->where("is_permanent = 1 AND check_status = 1")->order("begin_time asc")->find();
        if ($ratecount == 1) {
            $ratedata = M('policy')->where("(end_time > '".date('Y-m-d')."' OR is_permanent = 1) AND check_status = 1")->order("begin_time asc")->find();
            $rate = $ratedata["commission_rate"];
        } else {
            if ($ratedata1['begin_time'] >= $ratedata2['begin_time']) {
                $rate = $ratedata2['commission_rate'];
            } else {
                $rate = $ratedata1['commission_rate'];
            }
        }

        return $rate;
    }

    //安存数据入库
    function addAcunUser($uid, $type, $recordNo)
    {
        $data['uid'] = $uid;
        $data['type'] = $type;
        $data['recordNo'] = $recordNo;
        $data['add_time'] = time();
        M('ancun_userrecord')->add($data);
    }

    //安存数据入库
    function addAcunInvest($uid, $bid, $recordNo)
    {
        $data['uid'] = $uid;
        $data['bid'] = $bid;
        $data['invest_recordNo'] = $recordNo;
        $data['add_time'] = time();
        M('ancun_investrecord')->add($data);
    }

    //安存用户信息保全
    function ancunUser($uid)
    {
        $memberinfo = M('members m')->join('lzh_member_info mi on m.id = mi.uid')->field('m.user_name,m.user_phone,m.reg_time,mi.idcard,mi.real_name')->where("m.id = {$uid}")->find();
        $map['real_name']        = $memberinfo['real_name'];                        //姓名
        $map['user_name']        = $memberinfo['user_name'];                        //平台账号
        $map['user_phone']        = $memberinfo['user_phone'];                        //手机号
        $map['idcard']            = $memberinfo['idcard'];                            //身份证号码
        $map['reg_time']        = $memberinfo['reg_time'];        //注册时间
        import("@.Oauth.ancun.AnCun");
        $ancun = new AnCun();
        $data = $ancun->userInfoSafe($map);
        addAcunUser($uid, 1, $data['recordNo']);
    }

    //安存充值保全
    function ancunRecharge($order_no)
    {
        $status = M('sinalog')->where("order_no = '".$order_no."' and type = 1")->find();
        $memberinfo = M('members m')->join('lzh_member_info mi on m.id = mi.uid')->where("m.id = {$status["uid"]}")->field("m.user_phone,mi.real_name,mi.idcard")->find();
        $map['uid'] = $status['uid'];
        $map['real_name'] = $memberinfo['real_name'];
        $map['idcard'] = $memberinfo['idcard'];
        $map['order_no'] = $order_no;
        $map['money'] = $status['money'];
        $map['addtime'] = $status['addtime'];
        $map['completetime'] = $status['completetime'];

        import("@.Oauth.ancun.AnCun");
        $ancun = new AnCun();
        $data = $ancun->rechargeSafe($map);
        addAcunUser($status['uid'], 2, $data['recordNo']);
    }

    //安存提现保全
    function ancunwithdraw($order_no)
    {
        $status = M('sinalog')->where("order_no = '".$order_no."' and type = 2")->find();
        $memberinfo = M('members m')->join('lzh_member_info mi on m.id = mi.uid')->where("m.id = {$status["uid"]}")->field("m.user_phone,mi.real_name,mi.idcard")->find();
        $map['uid'] = $status['uid'];
        $map['real_name'] = $memberinfo['real_name'];
        $map['idcard'] = $memberinfo['idcard'];
        $map['order_no'] = $order_no;
        $map['money'] = $status['money'];
        $map['addtime'] = $status['addtime'];
        $map['completetime'] = $status['completetime'];

        import("@.Oauth.ancun.AnCun");
        $ancun = new AnCun();
        $data = $ancun->withdrawSafe($map);
        addAcunUser($status['uid'], 3, $data['recordNo']);
    }

    /**
     * 安存投资交易过程保全
     * @param $order_no
     * @param int $type 1 默认普通标 2 债权转让标
     */
    function ancunInvestSafe($order_no, $type=1)
    {
        if ($type==1) {
            $ty=3;
        } else {
            $ty=16;
        }
        $status = M('sinalog')->where("order_no = '".$order_no."' and type ={$ty}")->find();
        $memberinfo = M('members m')->join('lzh_member_info mi on m.id = mi.uid')->where("m.id = {$status["uid"]}")->field("m.user_phone,mi.real_name,mi.idcard")->find();
        $map['real_name']            = $memberinfo['real_name'];                            //姓名
        $map['user_phone']            = $memberinfo['user_phone'];                            //手机号码
        $map['idcard']                = $memberinfo['idcard'];                                //身份证号码
        $Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
        $borrowconfig = FS("Webconfig/borrowconfig");
        if ($type==1) {
            $binfo = M('borrow_info')->where("id = {$status['borrow_id']}")->find();
        } else {
            $binfo = M('debt_borrow_info')->where("id = {$status['borrow_id']}")->find();
        }
        $map['borrow_name']            = $binfo['borrow_name'];                            //项目名称
        foreach ($Bconfig['PRODUCT_TYPE'] as $key => $value) {
            if ($key == $binfo['product_type']) {
                $binfo['product_type'] = $value;
            }
        }
        $map['product_type']        = $binfo['product_type'];                        //标的类型
        $map['borrow_money']        = $binfo['borrow_money'].'元';                        //借款金额
        $map['borrow_interest_rate']= $binfo['borrow_interest_rate'].'%';                //年化收益率
        if ($binfo['repayment_type'] == 1) {
            $reptype = '天';
        } elseif ($binfo['repayment_type'] == 2 || $binfo['repayment_type'] == 4) {
            $reptype = '个月';
        }
        $map['borrow_duration']        = $binfo['borrow_duration'].$reptype;                        //借款期限
        foreach ($borrowconfig['BORROW_USE'] as $key => $value) {
            if ($key == $binfo['borrow_use']) {
                $binfo['borrow_use'] = $value;
            }
        }
        $map['borrow_use']            = $binfo['borrow_use'];                            //借款用途
        $map['borrow_min']            = $binfo['borrow_min'].'元';                            //起投金额
        $map['second_verify_time']    = "复审之日开始计息";                            //计息规则
        foreach ($Bconfig['REPAYMENT_TYPE'] as $key => $value) {
            if ($key == $binfo['repayment_type']) {
                $binfo['repayment_type'] = $value;
            }
        }
        $map['repayment_type']        = $binfo['repayment_type'];                        //还款方式
        $map['investor_captial']    = $status['money'].'元';                    //投资金额
        $map['investor_uid']        = $status['uid'];                        //付款方账户
        $map['borrow_uid']            = $binfo['borrow_uid'];                            //收款方账户
        $map['order_no']            = $order_no;                            //支付流水号
        $map['addtime']                = $status['addtime'];            //购买时间
        $map['completetime']        = $status['completetime'];    //支付成功时间

        import("@.Oauth.ancun.AnCun");
        $ancun = new AnCun();
        $data = $ancun->investSafe1($map);
        addAcunInvest($status['uid'], $status['borrow_id'], $data['recordNo']);
    }

    //安存投资交易过程保全
    function ancunProjectSafe($bid, $src)
    {
        $Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
        $borrowconfig = FS("Webconfig/borrowconfig");
        $binfo = M('borrow_info')->where("id = {$bid}")->find();
        $map['borrow_name']            = $binfo['borrow_name'];                            //项目名称
        foreach ($Bconfig['PRODUCT_TYPE'] as $key => $value) {
            if ($key == $binfo['product_type']) {
                $binfo['product_type'] = $value;
            }
        }
        $map['product_type']        = $binfo['product_type'];                        //标的类型
        $map['borrow_money']        = $binfo['borrow_money'].'元';                        //借款金额
        $map['borrow_interest_rate']= $binfo['borrow_interest_rate'].'%';                //年化收益率
        if ($binfo['repayment_type'] == 1) {
            $reptype = '天';
        } elseif ($binfo['repayment_type'] == 2 || $binfo['repayment_type'] == 4) {
            $reptype = '个月';
        }
        $map['borrow_duration']        = $binfo['borrow_duration'].$reptype;                        //借款期限
        foreach ($borrowconfig['BORROW_USE'] as $key => $value) {
            if ($key == $binfo['borrow_use']) {
                $binfo['borrow_use'] = $value;
            }
        }
        $map['borrow_use']            = $binfo['borrow_use'];                            //借款用途
        $map['borrow_min']            = $binfo['borrow_min'].'元';                            //起投金额
        $map['second_verify_time']    = "复审之日开始计息";                            //计息规则
        foreach ($Bconfig['REPAYMENT_TYPE'] as $key => $value) {
            if ($key == $binfo['repayment_type']) {
                $binfo['repayment_type'] = $value;
            }
        }
        $map['repayment_type']        = $binfo['repayment_type'];                        //还款方式
        $map['add_time']            = $binfo['add_time'];
        $map['first_verify_time']    = $binfo['first_verify_time'];
        $map['collect_time']        = $binfo['collect_time'];
        $map['deadline']            = $binfo['deadline'];
        $map['repayment_money']        = $binfo['borrow_money']+$binfo['borrow_interest'];
        $map['src']            = $src;
        $map['content']            = '第'.borrowidlayout1($bid).'号标合同';
        import("@.Oauth.ancun.AnCun");
        $ancun = new AnCun();
        $data = $ancun->projectSafe($map);
    }

    //投资卷获取
    function getCoupons($uid, $type=1,$min_investrange=-1)
    {
        if ($type == 1) {
            //投资券
            $coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")
                                     ->where("m.id = {$uid} AND c.status = 0 AND c.type = 1 AND c.min_investrange <= {$min_investrange}")
                                     ->group("c.money")
                                     ->select();
        } else {
            $coupons = M("coupons c")->join("lzh_members m ON m.user_phone = c.user_phone")
                                     ->where("m.id = {$uid} ")
                                     ->order("c.status asc")
                                     ->select();
        }
        return $coupons;
    }
     //客服
    function getuserinfo($uname)
    {
        $info = M('members')->where("user_name = '{$uname}'")->find();
        if (!empty($info)) {
            $info['isvip'] = 1;  //会员
        } else {
            $info['isvip'] = 0;
        }
        $res1 = explode("//", get_url());
        $res2 = explode('.html', $res1[1]);
        $res3 = explode('/', $res2[0]);
        $info['url'] = $res3;
        return $info;
    }
    function get_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
    //客服接待组ID
    function peception()
    {
        $pecepionid =  C('RECEPTION.id');
        return $pecepionid;
    }

    function logw($message, $level="err")
    {
        $os = PHP_OS;
        $destination = APP_PATH."Runtime/Logs/";
        $now = @date('Y-m-d H:i:s', time());
        $log[] = "[{$now}] {$level}: {$message}\r\n";
        if (trim(strtolower($os)) == 'linux') {
            $destination = "/data001/www/logs/ccfax/";
            if (!file_exists($destination)) {
                mkdir($destination);
            }
        }
        $log_file = $destination.date('Ymd', time()).'.log';
        $url = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        $content = "[{$now}] {$url}\r\n{$level}: {$message}\r\n";
        file_put_contents($log_file, $content, FILE_APPEND);
    }

    /**
     * 获取投资次数
     * 复审完成标的
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    function getInvestCount($uid,$maxtime)
    {
        $where['investor_uid'] = $uid;
        $where['status'] = array("EGT",4);
        $where['add_time'] = array("ELT",$maxtime);
        return M('borrow_investor')->where($where)->count();
    }

    //个人认证信息
    function certification($uname)
    {
        $data = M('members')->field('id,user_img')->where("user_name = '{$uname}'")->find();
        $level=25;
        //是否实名认证
        $members_statusModel=M("members_status");
        $id_status=$members_statusModel->where("uid={$data['id']}")->find();
        if ($id_status["id_status"]==1) {
            $info["ID_SET"] = 1;
            $level+=25;
        } elseif ($id_status["company_status"]!=0) {
            $info["ID_SET"] = 1;
            $level+=25;
        } else {
            $go_id= "/member/verify?id=1#fragment-1";
            $info["go_id"]=$go_id;
            $improve=$go_id;
        }
        //查询支付密码
        if ($id_status["is_pay_passwd"]) {
            $sina_password["is_set_paypass"]="Y";
        } else {
            $sina_password=checkissetpaypwd($data['id']);
            if ($sina_password["is_set_paypass"]=="Y") {
                $notset = M('members_status')->where(['uid'=>$data['id'],'is_pay_passwd'=>1])->find();
                $result = $members_statusModel->where(array("uid"=>$data['id']))->save(array("is_pay_passwd"=>1));//存储新浪密码为已经设置状态
                if($notset==null&&$result!==false)
                {
                    //设置新浪密码成功
                    setPaypasswd($data['id']);
                }
            }
        }
        if ($sina_password["is_set_paypass"]=="Y") {
            $info["SINA_SET"]=1;
            $level+=25;
        } else {
            $go_sina="/member/promotion/checkissetpwd?i=2";
            $info["go_sina"]=$go_sina;
            if (empty($improve)) {
                $improve=$go_sina;
            }
        }
        //是否邮箱验证
        if ($id_status["email_status"]==1) {
            $info["EMAIL_SET"]=1;
            $level+=25;
        } else {
            $go_email= "/member/verify?id=1#fragment-2";
            $info["go_email"]=$go_email;
            if (empty($improve)) {
                $improve=$go_email;
            }
        }
        switch ($level) {
            case 25: $info["level_text"]="低";break;
            case 50: $info["level_text"]="中";break;
            case 75: $info["level_text"]="高";break;
            case 100: $info["level_text"]="优";break;
        }
        $info["improve"]=$improve;
        $info['saving'] = querysaving($data['id']);
        $info['balance'] = querybalance($data['id']);
        $info["user_img"]=$data["user_img"];
        return $info;
    }
    function ccfaxapibalace($uid=0, $bid=0)
    {
        if ($uid!=0) {
            $data["uid"] = $uid;
        } elseif ($bid!=0) {
            $data["bid"] = $bid;
        } else {
            return false;
        }
        $url = C("CCFAXAPI_URL")."/sina/Sinaapi/checkUserBalance";
        return curl_post($url, $data);
    }

    function curl_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        log::write("post结果：", $result);
        return $result;
    }

    /**
     * CURL GET 请求
     */
    function curl_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * json response with pattern {code:**,msg:**} to request
     * @return [type] [description]
     */
    function ajaxRes($code, $msg,$data=[])
    {
        $tmp['code'] = $code;
        $tmp['msg'] = $msg;
        $tmp['data'] = $data;
        echo json_encode($tmp);
    }

    function ajaxSuccess($message="success",$data=[])
    {
        ajaxRes(1, $message,$data);
    }

    function ajaxFail($msg,$data=[])
    {
        ajaxRes(0, $msg,$data);
    }

    /**
     * 1.reveal all prize
     * 2.set dream_feeds,dream_invest_total,dream_invested value in table lzh_members to 0
     * if dream activity is over
     * @return [type] [description]
     */
    function checkDreamOver()
    {
        $end = M('global')->where(array('code'=>'dream_end_time'))->find();
        $end = $end['text'];

        $over = M('global')->where(array('code'=>'dream_is_over'))->find();
        $over = $over['text'];
        $isOver = $over;
        if ($isOver) {
            return true;
        }


        if ($end > time()) {
            return true;
        }

        $model = new Model();
        try {
            $model->startTrans();

            //reveal all prize remained
            revealAllPrize();

            //set
            $clear['dream_feeds'] = 0;
            $clear['dream_invest_total'] = 0;
            $clear['dream_invested'] = 0;
            $res = M('members')->where(1)->save($clear);
            if ($res!==false) {
                $tmp['create_time'] = time();
                $tmp['type'] = 0;
                $tmp['desc'] = "dream activity is over ,set dream_feeds,dream_invest_total,dream_invested to zero ";
                M('dream_log')->add($tmp);
            }

            //set global var dream is over to 1
            $savestatus['text']    = 1;
            $res1 = M('global')->where(array('code'=>'dream_is_over'))->save($savestatus);
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            return false;
        }

        return true;
    }

    /**
     * 向dream_log中写日志
     * type = 100 68元红包日志
     * @param  [type] $message [description]
     * @param  [type] $type    [description]
     * @return [type]          [description]
     */
    function writeDreamLog($message,$type)
    {
        $tmp['create_time'] = time();
        $tmp['type'] = $type;
        $tmp['desc'] = $message;
        return M('dream_log')->add($tmp);
    }

    function redPacketActivity($uid)
    {
        $info = M('members')->where(array("id"=>$uid))
                    ->find();
        if(!$info){
            writeDreamLog("use id equals {$uid} not found ,redpacket activity failed",100);
            return false;
        }

        investCouponsGift($info['user_phone']);
        giveBean($uid, 500);
    }

    /**
     * 注册成功
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    function regSuccess($uid)
    {
        pro9($uid);
        pro92($uid);
        redPacketActivity($uid);
        huodong201711($uid);
    }

    /**
     * 9月的活动
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    function pro9($uid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        $info = M('members')->find($uid);
      
        $isExist = M('p9_count')->where(['uid'=>$uid])->find();
        if($isExist){
            return;    
        }

        $data['uid'] = $uid;
        $data['user_phone'] = $info['user_phone'];
        $data['parent_id'] = $info['recommend_id'];
        $data['invest_money'] = 0;
        $data['create_time'] = time();
        $result = M('p9_count')->add($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->newUserLog($uid);
        }
    }

    /**
     * 9月的 送活动
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    function pro92($uid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        //如果未投资,且不存在 p9_count2 ,增加新记录
        $info = M('members')->find($uid);

        $isExist = M('p9_count2')->where(['uid'=>$uid])->find();
        if($isExist){
            return;    
        }

        $data['uid']          = $uid;
        $data['user_phone']   = $info['user_phone'];
        $data['parent_id']    = $info['recommend_id'];
        $data['invest_money'] = 0;
        $data['create_time']  = time();
        $result               = M('p9_count2')->add($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->newUserLog2($uid);
        }
    }

    /**
     * 201711 月活动
     * @return [type] [description]
     */
    function huodong201711($uid)
    {
        //不在活动时间内,exit
        if(time()>$glo['end_201711']&&time()<$glo['start_201711'])
        {
            return;
        }

        //如果未投资,且不存在 lzh_huodong_201711_count ,增加新记录
        $info = M('members')->find($uid);

        $isExist = M('huodong_201711_count')->where(['uid'=>$uid])->find();
        if($isExist){
            return;    
        }

        $data['uid']          = $uid;
        $data['user_phone']   = $info['user_phone'];
        $data['parent_id']    = $info['recommend_id'];
        $data['invest_money'] = 0;
        $data['create_time']  = time();
        $result               = M('huodong_201711_count')->add($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->huodong201711Regist($uid);
        }else{
        }
    }


    function setPaypasswd($uid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        $info = M('p9_count')->where(['uid'=>$uid])->find();
        if($info['parent_id']==0)
        {
            return;
        }

        $parent_id = $info['parent_id'];
        $logInfo = M('dream_log')->where(['type'=>1006,'desc'=>"{$uid} recommended by {$parent_id}"])->find();
        if($logInfo!=null){
            return;
        }


        //给 parent_id 增加一次砸冰块的机会
        $data['count_1'] = array('exp','count_1+1');
        $result = M('p9_count')->where(['uid'=>$info['parent_id']])->save($data);
        if($result){
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->p9RecommendLog($uid,$info['parent_id']);
        }else{
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->p9RecommendErrorLog($uid,$info['parent_id']);
        }
        
    }

    function p9Recharge($uid)
    {
        //不在活动时间内,exit
        if(time()>$glo['p9_end']&&time()<$glo['p9_start'])
        {
            return;
        }

        $isExist = M('p9_count')->where(['uid'=>$uid])->find();
        if(!$isExist)
            return;

        $update['count_2'] = array('exp','count_2+1');
        $result = M('p9_count')->where(['uid'=>$uid])->save($update);
        if($result)
        {
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->p9RechargeLog($uid);
        }else{
            $dreamLogModel = new DreamLogModel();
            $dreamLogModel->p9RechargeErrorLog($uid);
        }
    }

    /**
     * 送炼金豆,发短信
     * @param  [type] $phonenumber [description]
     * @return [type]              [description]
     */
    function giveBean($uid, $beannumber)
    {
        $isExist = M("apr_bean")->where(array("uid"=>$uid))
                                ->find();

        if ($isExist) {
            M("apr_bean")->where(array("uid"=>$uid))
                         ->save(array("beancount"=>$list["beancount"]+500));
        } else {
            $userlist=M("members")->where(array("id"=>$uid))
                                  ->find();
            M("apr_bean")->add(array("uid"=>$uid,"user_name"=>$userlist["user_name"],"user_phone"=>$userlist["user_phone"],"beancount"=>500));
        }
        $msg = "user id  equals {$uid} reg success, {$beannumber} apr bean gift granted";
        writeDreamLog($msg,100);
        $content1 = "尊敬的链金所用户您好! 恭喜你获得 500 链金豆，可登录活动页面参与抽取iphone7、金条等好礼，详询客服中心：400-6626-985";
        sendsms($user['user_phone'], $content1);
    }

    /**
     * 20170726 为庆祝特速A轮融资，增加注册成功后68元=3+5+10+20+30的红包
     * @return [type] [description]
     */
    function investCouponsGift($phonenumber)
    {
      
        $list = [
                    ['money'=>'3', 'use_money'=>100,  'min_investrange'=>30,'expired'=>3],
                    ['money'=>'5', 'use_money'=>300,  'min_investrange'=>30,'expired'=>7],
                    ['money'=>'10','use_money'=>1000, 'min_investrange'=>30,'expired'=>30],
                    ['money'=>'20','use_money'=>2000, 'min_investrange'=>30,'expired'=>30],
                    ['money'=>'30','use_money'=>3000, 'min_investrange'=>30,'expired'=>30],
                ];
        $giftResult = true;

        $model = new Model();
        try {
            $model->startTrans();

            foreach ($list as $key => $item) {
                $coup['user_phone']      = $phonenumber;
                $coup['money']           = $item['money'];
                $coup['use_money']       = $item['use_money'];
                $coup['min_investrange'] = $item['min_investrange'];
                $coup['endtime']         = $item['expired']*24*3600+time();//体验金的期限,减去当前天数
                $coup['status']          = '0';
                $coup['serial_number']   = date('YmdHis', time()).mt_rand(100000, 999999);
                $coup['type']            = '1';
                $coup['name']            = '注册红包';
                $coup['addtime']         = date("Y-m-d H:i:s", time());
                $coup['isexperience']    = '0';

                if (M('coupons')->add($coup) === false)
                {
                    $giftResult = false;
                }       
            }

            if(!$giftResult)
            {
                //如果红包有一个没有送到，回滚
                $model->rollback();
                $msg = "user {$phonenumber} reg success, gift failed";
                writeDreamLog($msg,100);
                return false;
            }

            $model->commit();
            $msg = "user phone equals {$phonenumber} reg success, gift 68=3+5+10+20+30";
            $content = "尊敬的链金所用户您好！68元红包已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
            
            sendsms($phonenumber, $content);
            writeDreamLog($msg,100);
            //记录日志

        } catch (Exception $e) {
            $msg = "user {$phonenumber} reg success, gift failed";
            writeDreamLog($msg,100);
            $model->rollback();
            
        }

        return true;
    }

    /**
     * 2017 11 total 
     * @param  [type] $phone     [description]
     * @param  [type] $recommend [description]
     * @param  [type] $rebate    [description]
     * @return [type]            [description]
     */
    function sms201711Total($phone, $recommend, $rebate) {

        $recommend  = substr($recommend,0,3)."*****".substr($recommend,8,11);

        $content1 = "尊敬的链金所用户您好! 您邀请的用户 {$recommend} 完成一笔投资，{$rebate} 元返现奖励 ，已发放至您的账户中，感谢您对链金所的关注与支持！";
        sendsms($phone, $content1);   
    }

    /**
     * 2017 11 first
     * @param  [type] $phone     [description]
     * @param  [type] $recommend [description]
     * @param  [type] $rebate    [description]
     * @return [type]            [description]
     */
    function sms201711First($phone, $recommend, $rebate) {
        $recommend  = substr($recommend,0,3)."*****".substr($recommend,8,11);
        $content1 = "尊敬的链金所用户您好! 您邀请的用户 {$recommend} 完成首笔投资，{$rebate} 元返现奖励 ，已发放至您的账户中，感谢您对链金所的关注与支持！";
        sendsms($phone, $content1);   
    }

    /**
     * 实名送 600 投资券
     * @param  [type] $phonenumber [description]
     * @return [type]              [description]
     */
    function realnameCouponsGift($phonenumber)
    {
        $list = [
                    ['money'=>'100', 'use_money'=>10000,  'min_investrange'=>40,'expired'=>90],
                    ['money'=>'200', 'use_money'=>20000,  'min_investrange'=>90,'expired'=>90],
                    ['money'=>'300','use_money'=> 50000, 'min_investrange'=>180,'expired'=>90],
                    
                ];
        $giftResult = true;

        $model = new Model();
        try {
            $model->startTrans();

            foreach ($list as $key => $item) {
                $coup['user_phone']      = $phonenumber;
                $coup['money']           = $item['money'];
                $coup['use_money']       = $item['use_money'];
                $coup['min_investrange'] = $item['min_investrange'];
                $coup['endtime']         = $item['expired']*24*3600+time();//体验金的期限,减去当前天数
                $coup['status']          = '0';
                $coup['serial_number']   = date('YmdHis', time()).mt_rand(100000, 999999);
                $coup['type']            = '1';
                $coup['name']            = '实名红包';
                $coup['addtime']         = date("Y-m-d H:i:s", time());
                $coup['isexperience']    = '0';

                if (M('coupons')->add($coup) === false)
                {
                    $giftResult = false;
                }       
            }

            if(!$giftResult)
            {
                //如果红包有一个没有送到，回滚
                $model->rollback();
                $msg = "user {$phonenumber} reg success, gift failed";
                writeDreamLog($msg,100);
                return false;
            }

            $model->commit();
            $msg = "user phone equals {$phonenumber} reg success, gift 600=100+200+300";
            $content = "尊敬的链金所用户您好！600元红包已送达您的账户，您可登录平台账户-我的赠券中查看，链金所助您资产稳健增值，详询客服中心：400-6626-985。";
            
            sendsms($phonenumber, $content);
            writeDreamLog($msg,100);
            //记录日志

        } catch (Exception $e) {
            $msg = "user {$phonenumber} realname confirm success, gift failed";
            writeDreamLog($msg,100);
            $model->rollback();
            
        }

        return true;
    }

    function checkDreamStart()
    {
        $end = M('global')->where(array('code'=>'dream_end_time'))->find();
        $end = $end['text'];

        $over = M('global')->where(array('code'=>'dream_is_over'))->find();
        $over = $over['text'];
        $isOver = $over;

        if (($end > time())&&$isOver) {
            $savestatus['text']    = 0;
            $res1 = M('global')->where(array('code'=>'dream_is_over'))->save($savestatus);
            if ($res1!==false) {
                $tmp['create_time'] = time();
                $tmp['type'] = 0;
                $tmp['desc'] = "dream activity started ";
                M('dream_log')->add($tmp);
            }
        }
    }

    function checkDreamStatus()
    {
        checkDreamStart();
        checkDreamOver();
    }

    function revealAllPrize()
    {
        $list = M('dream_prizehistory')->where(array('status'=>0))->select();
        foreach ($list as $key => $value) {
            libRevealWinner($value['id'], false);
        }
    }

    /**
     * reveal the winner
     * @return  [description]
     */
    function libRevealWinner($priid,$fullCheck=true,$notice=false, $newprize=true){
        $prizeHisId = $priid;
        $winFeedNo = 10000001;

        $model = new Model();
        try {
            $model->startTrans();

            $where['id'] = $prizeHisId;
            if ($fullCheck) {
                $where['status'] = 0;
                $where['feeds_left'] = array('elt', 0);
            }

            $isFull = M('dream_prizehistory')->lock(true)->where($where)->find();
            if ($fullCheck&&!$isFull) {
                throw new Exception("奖品未满，无法开奖", 1);
                return true;
            }

            $prizeInfo = M('dream_prizehistory')->lock(true)->find($prizeHisId);
            if (!$prizeInfo) {
                throw new Exception("奖品不存在!", 1);
            }

            $count     = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time');
            $counthour = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 3600 mod 24 ');
            $countmin  = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 60 mod 60');
            $countsec  = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time mod 60');

            $winFeedNo = $count % $prizeInfo['prize_total_feeds'];
            $to = $counthour + $countmin + $countsec;
            $m = ($prizeInfo['prize_total_feeds']-$prizeInfo['feeds_left'])/$prizeInfo['prize_min_feeds'];
            if ($m == 0) {
                $emptyprize = true;
            }
            logw(' total = '.$to.' m = '.$m);
            $ran = $to%$m;
            //$winFeedNo = 10000000 + rand(1,$prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds']);
            $winFeedNo = 10000001 + $ran;

            //内定中奖,收尾，制定最后一个号中奖
            if($newprize === false){
                $winFeedNo = $prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds']+10000000;
            }

            //find winner
            $wincon['prize_id'] = $prizeHisId;
            $wincon['feed_no']  = $winFeedNo;
            logw('==win connd = '.json_encode($wincon));
            $winner = M('dream_invest')->where($wincon)->find();
            logw(' winner = '.json_encode($winner));
            if (!$winner&&!$emptyprize) {
                throw new Exception("没有中奖人信息 !", 1);
            }

            if (!$emptyprize) {
                //write winner
                $winnerinfo['prize_id']   = $prizeInfo['id'];
                $winnerinfo['prize_name'] = $prizeInfo['prize_name'];
                $winnerinfo['qishu']      = $prizeInfo['qishu'];
                $winnerinfo['uid']        = $winner['uid'];
                $winnerinfo['mobile']     = $winner['mobile'];
                $winnerinfo['money']      = $winner['money'];
                $winnerinfo['feed_no']    = $winFeedNo;
                $winnerinfo['create_time'] = time();
                if (!(M('dream_true')->add($winnerinfo))) {
                    throw new Exception("保存中奖人信息失败!", 1);
                } else {
                    if (!$fullCheck) {
                        $log['create_time'] = time();
                        $log['type'] = 0;
                        $log['desc'] = "reveal prize before complete ,priid = {$prizeInfo['id']} name = {$prizeInfo['prize_name']} qishu = {$prizeInfo['qishu']} left = {$prizeInfo['feeds_left']}";
                        M('dream_log')->add($log);
                    }
                    $content1 = "尊敬的用户，恭喜您获得平台圆梦活动第{$prizeInfo['qishu']}期{$prizeInfo['prize_name']}，请登录账户了解具体详情或拨打客服专线4006626985";
                    sendsms($winner['mobile'], $content1);
                }
            } else {
                //empty prize ,do nothing
                        $log['create_time'] = time();
                $log['type'] = 0;
                $log['desc'] = "reveal prize before complete ,priid = {$prizeInfo['id']} name = {$prizeInfo['prize_name']} qishu = {$prizeInfo['qishu']} left = {$prizeInfo['feeds_left']}, prize is empty";
                M('dream_log')->add($log);
            }


            //update winner record
            $full['status'] = 1;

            $full['luck_no'] = $winFeedNo;
            if ($emptyprize) {
                $full['luck_no'] = $winFeedNo-1;
                $full['feeds_left'] = 0;
            }

            if (!$fullCheck) {
                $full['feeds_left'] = 0;
            }


            if (!(M('dream_prizehistory')->where(array('id' => $prizeHisId))->save($full))) {
                throw new Exception("更新奖品信息失败 !", 1);
            }

            //check if another prize is accessiable
            $priType        = $prizeInfo['prize_type'];
            $curqishu       = $prizeInfo['qishu'];
            $oricon['type'] = $priType;
            $oricon['default'] = 1;
            $pri = M('dream_prize')->where($oricon)->find();
            logw(' pri  =  '.json_encode($pri));
            $maxqishu = $pri['inventory'];
            if($fullCheck&&($maxqishu > $curqishu)&&$newprize){
                //create a new record
                $newPrizeHistory['prize_id']          = $pri['id'];
                $newPrizeHistory['prize_name']        = $pri['name'];
                $newPrizeHistory['prize_min_feeds']   = $pri['min_feeds'];
                $newPrizeHistory['prize_total_feeds'] = $pri['total_feeds'];
                $newPrizeHistory['prize_type']        = $pri['type'];
                $newPrizeHistory['create_time']       = time();
                $newPrizeHistory['feeds_left']        = $newPrizeHistory['prize_total_feeds'];
                $newPrizeHistory['invest_times']      = 0;
                $newPrizeHistory['qishu']             = $curqishu + 1;
                if (!(M('dream_prizehistory')->add($newPrizeHistory))) {
                    throw new Exception("奖品释放失败!", 1);
                }
            }

            //commit
            $model->commit();
        } catch (Exception $e) {
            logw('reveal the winner msg = '.$e->getMessage());
            logw('trace = '.json_encode($e->getTrace()));
            $model->rollback();
            if ($notice) {
                $this->error("保存失败".$e->getMessage(), __URL__."/dream");
            } else {
                ajaxFail($e->getMessage());
            }
        }
    }


       /**
     * reveal the winner
     * @return  [description]
     */
    function ajaxRevealTheWinner($priid, $fullCheck=true)
    {
        $prizeHisId = $priid;
        $winFeedNo = 10000001;

        $model = new Model();
        try {
            $model->startTrans();

            $where['id'] = $prizeHisId;
            if ($fullCheck) {
                $where['status'] = 0;
                $where['feeds_left'] = array('elt', 0);
            }

            $isFull = M('dream_prizehistory')->lock(true)->where($where)->find();
            if ($fullCheck&&!$isFull) {
                return true;
            }

            $prizeInfo = M('dream_prizehistory')->lock(true)->find($prizeHisId);
            if (!$prizeInfo) {
                throw new Exception("奖品不存在!", 1);
            }

            $count     = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time');
            $counthour = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 3600 mod 24 ');
            $countmin  = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time div 60 mod 60');
            $countsec  = M('dream_invest')->where(array('prize_id' => $isFull['id']))->sum('create_time mod 60');

            $winFeedNo = $count % $prizeInfo['prize_total_feeds'];
            $to = $counthour + $countmin + $countsec;
            $m = ($prizeInfo['prize_total_feeds']-$prizeInfo['feeds_left'])/$prizeInfo['prize_min_feeds'];
            if ($m == 0) {
                $emptyprize = true;
            }
            logw(' total = '.$to.' m = '.$m);
            $ran = $to%$m;
            //$winFeedNo = 10000000 + rand(1,$prizeInfo['prize_total_feeds']/$prizeInfo['prize_min_feeds']);
            $winFeedNo = 10000001 + $ran;
            //find winner
            $wincon['prize_id'] = $prizeHisId;
            $wincon['feed_no']  = $winFeedNo;
            logw('==win connd = '.json_encode($wincon));
            $winner = M('dream_invest')->where($wincon)->find();
            logw(' winner = '.json_encode($winner));
            if (!$winner) {
                throw new Exception("没有中奖人信息 !", 1);
            }

            if (!$emptyprize) {
                //write winner
                $winnerinfo['prize_id']   = $prizeInfo['id'];
                $winnerinfo['prize_name'] = $prizeInfo['prize_name'];
                $winnerinfo['qishu']      = $prizeInfo['qishu'];
                $winnerinfo['uid']        = $winner['uid'];
                $winnerinfo['mobile']     = $winner['mobile'];
                $winnerinfo['money']      = $winner['money'];
                $winnerinfo['feed_no']    = $winFeedNo;
                $winnerinfo['create_time'] = time();
                if (!(M('dream_true')->add($winnerinfo))) {
                    throw new Exception("保存中奖人信息失败!", 1);
                } else {
                    if (!$fullCheck) {
                        $log['create_time'] = time();
                        $log['type'] = 0;
                        $log['desc'] = "reveal prize before complete ,priid = {$prizeInfo['id']} name = {$prizeInfo['prize_name']} qishu = {$prizeInfo['qishu']} left = {$prizeInfo['feeds_left']}";
                        M('dream_log')->add($log);
                    }
                }
            } else {
                //empty prize ,do nothing
                        $log['create_time'] = time();
                $log['type'] = 0;
                $log['desc'] = "reveal prize before complete ,priid = {$prizeInfo['id']} name = {$prizeInfo['prize_name']} qishu = {$prizeInfo['qishu']} left = {$prizeInfo['feeds_left']}, prize is empty";
                M('dream_log')->add($log);
            }


            //update winner record
            $full['status'] = 1;
            $full['luck_no'] = $winFeedNo;
            if (!(M('dream_prizehistory')->where(array('id' => $prizeHisId))->save($full))) {
                throw new Exception("更新奖品信息失败 !", 1);
            }

            //check if another prize is accessiable
            $priType        = $prizeInfo['prize_type'];
            $curqishu       = $prizeInfo['qishu'];
            $oricon['type'] = $priType;
            $oricon['default'] = 1;
            $pri = M('dream_prize')->where($oricon)->find();
            logw(' pri  =  '.json_encode($pri));
            $maxqishu = $pri['inventory'];
            if ($fullCheck&&($maxqishu > $curqishu)) {
                //create a new record
                $newPrizeHistory['prize_id']          = $pri['id'];
                $newPrizeHistory['prize_name']        = $pri['name'];
                $newPrizeHistory['prize_min_feeds']   = $pri['min_feeds'];
                $newPrizeHistory['prize_total_feeds'] = $pri['total_feeds'];
                $newPrizeHistory['prize_type']        = $pri['type'];
                $newPrizeHistory['create_time']       = time();
                $newPrizeHistory['feeds_left']        = $newPrizeHistory['prize_total_feeds'];
                $newPrizeHistory['invest_times']      = 0;
                $newPrizeHistory['qishu']             = $curqishu + 1;
                if (!(M('dream_prizehistory')->add($newPrizeHistory))) {
                    throw new Exception("奖品释放失败!", 1);
                }
            }

            //commit
            $model->commit();
        } catch (Exception $e) {
            logw('reveal the winner msg = '.$e->getMessage());
            logw('trace = '.json_encode($e->getTrace()));
            $model->rollback();
            ajaxFail($e->getMessage());
        }
    }

    /**
     * CPS 检查
     * @return [type] [description]
     */
    function checkSource(){
        logw(' request = '.json_encode($_REQUEST));
        //模式一检查
        checkSource1();
        //模式二检查
        checkSource2();
    }

    /**
     * ＣＰＳ模式一，标记写入ｃｏｏｋｉｅ，保存为３０天，３０天内，任何在链金所网站
     * 注册的用户都算在该　cps　头上
     * utm_source 注册来源
     * uid        推荐人id
     * @return [type] [description]
     */
    function checkSource1(){
        if(isset($_REQUEST['mode'])){
            return;
        }
        
        if(isset($_REQUEST['utm_source'])&&isset($_REQUEST['uid'])){
            cookie('utmsource', $_REQUEST['utm_source'], 86400*30);
            cookie('utmid', $_REQUEST['uid'], 86400*30);
        }

        $utmSource = cookie('utmsource');
        $uid = cookie('utmid');

        switch ($utmSource) {
            case 'rph':
                # code...
                break;
            case 'fubaba':
                session('utmsource', $utmSource);
                session('utmid', $uid);
                break;
            case 'fengche':
                # code...
                break;
            default:
                break;
        }
    }

    /**
     * CPS　模式二,一次性CPS,来源信息不保存cookie
     * @return [type] [description]
     */
    function checkSource2(){
        if($_REQUEST['mode']!=2){
            return;
        }


        if (isset($_REQUEST['utm_source'])&&isset($_REQUEST['uid'])){
            $source = strtolower(trim($_REQUEST['utm_source']));
            session('utmsource', $_REQUEST['utm_source']);
            session('utmid', $_REQUEST['uid']);
        }
        logw('xxxx = '.session('utmsource'));
    }

    function cpsData($data){
        logw('enter into utmSource = '.session('utmsource'));

        //如果不存在utm_source ,返回原data
        if(!isset($_SESSION['utmsource'])&&!$_SESSION['utmsource'])
             return $data;


        switch (session('utmsource')) {
            case 'rph':
                # code...
                break;
            //富爸爸
            case 'fubaba':
                $data['equipment'] = 'fubaba';
                $data['fubabaid'] = session('utmid');
                break;
            //搜利网
            case 'soli':
                $data['equipment'] = 'soli';
                $data['fubabaid'] = session('utmid');
                break;
            default:
                $data['equipment'] = session('utmsource');
                $data['fubabaid'] = session('utmid');
                break;
        }
        return $data;
    }

    function exportToCSV($header,$data,$filename)
    {
        require_once 'CORE/Extend/spout-2.7.2/src/Spout/Autoloader/autoload.php';
        $writer = Box\Spout\Writer\WriterFactory::create(Box\Spout\Common\Type::CSV);
        $filePath = $filename;
        $writer->openToBrowser($filePath);
        $writer->addRow($header);

        // $row = array();
        // foreach ($list as $key=>$v) {
        // $row[$key+1]['uid'] = $v['id'];
        // $row[$key+1]['is_vip'] = $v['is_vip'] == 1 ? "投资人/借款人" : "投资人";
        // $row[$key+1]['uname'] = $v['user_name'];
        // $row[$key+1]['idcard'] = "\t".strval($v['idcard'])." ";
        // $row[$key+1]["user_phone"] = $v["user_phone"];
        // $row[$key+1]['real_name'] = $v['real_name'];
        // $row[$key+1]['jiguan'] = $v['jiguan'];
        // $row[$key+1]['is_jieyang'] = $v['is_jieyang'] == 1 ? "是" : "否";
        // $row[$key+1]['recommend_name'] = $v['recommend_name'];
        // $row[$key+1]['equipment'] = $v['equipment'];
        // $row[$key+1]["reg_time"] = date("Y-m-d", $v["reg_time"]);
        // $row[$key+1]["last_log_time"] = $v["last_log_time"] > 0 ? date("Y-m-d", $v["last_log_time"]) : '';
        // $row[$key+1]['id_status'] = $v['id_status'] == 1 ? "是" : "否";
        // $row[$key+1]['is_invest'] = intval($v['invest_id'])? "是" : "否";
        // $row[$key+1]['invest_total'] = $v['invest_total'];
        // $row[$key+1]["add_time"] = $v["tiyanjin_time"] > 0 ? date("Y-m-d", $v["tiyanjin_time"]) : '';
        // $row[$key+1]['expire_time'] = empty($v['co_min_endtime'])?"":date('Y-m-d',$v["co_min_endtime"]);
        // $row[$key+1]['first_invest_time'] = empty($v['first_invest_time'])?"尚未投资":date('Y-m-d',$v["first_invest_time"]);
        // $row[$key+1]['first_invest_amount'] = empty($v['first_invest_time'])?"无":$v['first_invest_amount'];
        // $row[$key+1]['firstmonth_invest_amount'] = empty($v['first_invest_time'])?"无":$v['firstmonth_invest_amount'];
        // }
        $writer->addRows($data);
        $writer->close();
        die;
    }

/**
 * 格式化金额
 * @param  [type] $num [description]
 * @return [type]      [description]
 */
    function receipt_format($num)
    {
        if (!is_numeric($num)) {
            return false;
        }
        setlocale(LC_MONETARY, "zh_CN");
        $rvalue =  money_format("%!#2n", $num);
        return $rvalue;
    }

    /**
     * 五月活动方法
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    function the_may_active($type, $invest_uid, $invest_money=0)
    {
        if (strtotime(C("THE_MAY_ACTIVE.start_time"))<=time() && strtotime(C("THE_MAY_ACTIVE.end_time"))>=time()) {
            $recommend = M("members m")->join("lzh_members mm ON m.recommend_id = mm.id")->where(array("m.id"=>$invest_uid))->field("m.recommend_id,mm.user_phone")->find();
            if ($recommend["recommend_id"] == 0) {
                return;
            }
            switch ($type) {
                case 'realname':
                    $list = M("recommend_first")->where(array("recommend_uid"=>$recommend["recommend_id"]))->find();
                    if (!$list) {
                        $first_data["recommend_uid"] = $recommend["recommend_id"];
                        M("recommend_first")->add($first_data);
                    }
                    $data["recommend_count"] = $list["recommend_count"] + 1;
                    if ($data["recommend_count"] > 50) {
                        $c_data["user_phone"] = $recommend["user_phone"];
                        $c_data["money"] = 5;
                        $c_data["endtime"] = strtotime(date("Y-m-d 23:59:59", strtotime("+14 days")));
                        $c_data["status"] = 0;
                        $c_data["serial_number"] = time() . rand(100000, 999999);
                        $c_data["type"] = 1;
                        $c_data["name"] = "五月活动";
                        $c_data["addtime"] = date("Y-m-d H:i:s", time());
                        $c_data["isexperience"] = 1;
                        $c_data["use_money"] = 500;
                        M("coupons")->add($c_data);
                        $data["coupons_count"] = $list["coupons_count"] + 1;
                    } else {
                        $data["experience_money"] = $list["experience_money"] + 1000;
                    }
                    $data["update_time"] = time();
                    M("recommend_first")->where(array("recommend_uid"=>$recommend["recommend_id"]))->save($data);
                    M("recommend_invest")->where(array("invest_uid"=>$invest_uid))->save(array("verify_time"=>time()));
                    themay_scende($recommend["recommend_id"]);
                    break;

                case 'invest':
                    $invest_info = M("members")->where(array("id"=>$invest_uid))->field("reg_time")->find();
                    if ($invest_info["reg_time"] >= strtotime(C("THE_MAY_ACTIVE.start_time"))) {
                        $list = M("recommend_invest")->where(array("invest_uid"=>$invest_uid))->find();
                        $data["invest_money"] = $list["invest_money"] + $invest_money;
                        $data["update_time"] = time();
                        M("recommend_invest")->where(array("invest_uid"=>$invest_uid))->save($data);

                        $lucky_info = M("recommend_lucky")->where(array("uid"=>$recommend["recommend_id"]))->find();
                        $invest_sum = M("recommend_invest")->where(array("recommend_uid"=>$recommend["recommend_id"]))->sum("invest_money");
                        if ($lucky_info) {
                            $lucy_data['total_count'] = floor($invest_sum/500);
                            M("recommend_lucky")->where(array("uid"=>$recommend["recommend_id"]))->save($lucy_data);
                        } else {
                            $lucy_data["uid"] = $recommend["recommend_id"];
                            $lucy_data['total_count'] = floor($invest_sum/500);
                            $lucy_data['used_count'] = 0;
                            M("recommend_lucky")->add($lucy_data);
                        }

                        themay_scende($recommend["recommend_id"]);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * 投骰子
     * @param  [type]  $tablename    奖品表
     * @param  [type]  $callbackfunc 回调函数
     * @param  integer $activity     活动类型
     * @param  integer $rateindex    概率集index
     * @return [type]                [description]
     */
    function randomRoll($uid,$oppoNoOri,$tablename,$callbackfunc,$activity=0,$rateindex=0)
    {
        //抽奖区间定义在0-10000
        $a=mt_rand(0,10000);
        //概率集 0 表示默认
        $index = $rateindex;
        //抽奖次数
        $count = $oppoNoOri;
        $token = "xxxxx";

        //获取随机数,根据 minnum_0 maxnum_0 确定 name value type
        if($index == -1){
            //报错
        }else{
            $min = "minnum_".$index;
            $max = "maxnum_".$index;
            $where[$min]       = array('ELT',$a);
            $where[$max]       = array('EGT',$a);
            $where['num_left'] = array('GT',0);
            $where['active_type'] = $activity;
        }

        //exit($tablename);
        //如果奖品数量用完,重新摇奖
        //启用事务
        //
        $res =M($tablename)->where($where)->find();
        //exit(__LINE__);

        // 根据概率找不到奖品
        if($res == null){
            // 寻找下一个剩余奖品不为0 的
            unset($where);
            $where['num_left'] = array('GT',0);
            $where['active_type'] = $activity;
            $res =M($tablename)->where($where)->order('id asc')->find();
            if($res==null){
                $data['left'] = $count;
                $data['angle'] = 0;
                $data['token'] = "token";
                $data['ret'] = 0;
                ajaxFail('no prize info was found',$data);    
            }else{
                //调用回调函数
                $callbackfunc($res,$uid,$oppoNoOri);
            }
            
        }else{

            //调用回调函数
            $callbackfunc($res,$uid,$oppoNoOri);
        }
    }

    /**
     * 2017年9月活动 送 回调函数
     * @param  [type] $prizeRec [description]
     * @return [type]           [description]
     */
    function songCallback($prizeRec,$uid,$oppoNoOri)
    {
        $model = M('dream_log');
        $model->startTrans();
        $prizeModel = new P9WinModel();
        $dreamLogModel = new DreamLogModel();
        $prize = new P9PrizeModel();
        
        try{
            $message           = floatval($prizeRec['value']);
            $data['left']      = $oppoNoOri -1;
            $data['angle']     = $prizeRec['angle'];
            $data['name']      = $prizeRec['mark'];
            $data['value']     = floatval($prizeRec['value']);
            $data['datettime'] = date('Y.m.d H:i:s',time());

            if($prizeRec['type'] == 0){
                // 插入投资券记录
                //releaseCommision($uid, $prizeRec['value'],"9月活动送");
            }

            //更新抽奖次数信息
            //写入奖品记录表,标记发放状态status = 0
            $prizeModel->insertSongRecord($prizeRec,$uid);
            $prize->descNum($prizeRec['id'],$uid,$prizeRec['info']);

            //写入日志
            $dreamLogModel->songLog($prizeRec,$uid);

            $model->commit();
            //返回
            ajaxSuccess($message,$data);

        }catch(Exception $e){
            //写入日志
            $dreamLogModel->songExceptionLog($prizeRec,$uid);
            $model->rollback();
            ajaxFail($e->getMessage(), $data);
        }
    }


    function zaCallback($prizeRec,$uid,$oppoNoOri)
    {
        $model = M('dream_log');
        $model->startTrans();
        $prizeModel = new P9WinModel();
        $dreamLogModel = new DreamLogModel();
        $countModel = new P9CountModel();
        $couponModel = new CouponsModel();
        $prize = new P9PrizeModel();

        try{
            
            $message = floatval($prizeRec['value']);
            $data['left']  = $oppoNoOri -1;
            $data['angle'] = $prizeRec['angle'];
            $data['name'] = $prizeRec['mark'];
            $data['value'] = floatval($prizeRec['value']);

            // 锁定 p9_count 表
            $where['uid'] = $uid;
            $where['count_1'] = array('gt',0);
            $canPlay = M('p9_count')->lock(true)->where($where)->find();
            if (!$canPlay) {
                ajaxFail("没有砸奖次数", $data);
            }

            $minfo = M('p9_count')->where(['uid'=>$uid])->find();
            if(!$minfo)
            {
                throw new Exception("用户{$uid}不存在", 1);
            }
            if($minfo['count_1']<=0){
                throw new Exception("抽奖次数已经用完", 1);
            }

            if($prizeRec['type'] == 3){
                // 发放红包
                releaseCommision($uid, $prizeRec['value'],"9月活动砸");
                $data['release_status'] = 1;
            }elseif($prizeRec['type'] == 0){
                //发放代金券
                $couponModel->addInvestCouponFor9($uid,$prizeRec['value'],"9月活动砸");
                $data['release_status'] = 1;
            }else{
                $data['release_status'] = 0;
            }

            //更新抽奖次数信息
            //写入奖品记录表,标记发放状态status = 0
            $prizeModel->insertZaRecord($prizeRec,$uid);
            $countModel->descCount($uid,1);
            $prize->descNum($prizeRec['id'],$uid,$prizeRec['info']);

            //写入日志
            $dreamLogModel->zaLog($prizeRec,$uid);


            $model->commit();
            //返回
            ajaxSuccess($message,$data);

        }catch(Exception $e){
            //写入日志
            $dreamLogModel->zaExceptionLog($prizeRec,$uid);
            $model->rollback();
            ajaxFail($e->getMessage(), $data);
        }
    }

    function qiangCallback($prizeRec,$uid,$oppoNoOri)
    {
        $model = M('dream_log');
        $model->startTrans();
        $prizeModel = new P9WinModel();
        $dreamLogModel = new DreamLogModel();
        $couponModel = new CouponsModel();
        $countModel = new P9CountModel();
        $prize = new P9PrizeModel();

        try{
            $message = $prizeRec['type'] == 5?floatval($prizeRec['value']):0;
            $data['left']  = $oppoNoOri -1;
            $data['angle'] = $prizeRec['angle'];
            $data['value'] = $prizeRec['type'] == 5?floatval($prizeRec['value']):0;
            
            if($prizeRec['type'] == 5){
                // 插入投资券记录
                $couponModel->addInterestCoupon($uid,$prizeRec['value'],"9月活动抢");
            }

            //更新抽奖次数信息
            //写入奖品记录表,标记发放状态status = 0
            $prizeModel->insertQiangRecord($prizeRec,$uid);
            $countModel->descCount($uid,2);
            $prize->descNum($prizeRec['id'],$uid,$prizeRec['info']);

            //写入日志
            $dreamLogModel->qiangLog($prizeRec,$uid);

            $model->commit();
            //返回
            //根据p9_prize 中求出剩余加息券个数
            $m = new P9WinModel();
            $icleft = $m->icLeft();
            $data['icleft'] = $icleft;
            ajaxSuccess($message,$data);

        }catch(Exception $e){
            //写入日志
            $dreamLogModel->qiangExceptionLog($prizeRec,$uid);
            $model->rollback();
            ajaxFail($e->getMessage(), $data);
        }
    }

    /**
     * 发放现金
     * @param  [type] $uid   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    function releaseCommision($uid, $money, $desc="")
    {
        $total = $money;
        import("@.Oauth.sina.Sina");
        $sina = new Sina();
        $result = $sina->collecttradecompany($total, $desc);
        if ($result == "APPLY_SUCCESS") {
            
            $order_no = date('YmdHis') . mt_rand(100000, 999999);
            $account_type = 'SAVING_POT';
            $val = date('YmdHis').mt_rand(100000, 999999).'~20151008'.$uid.'~UID~'.$account_type.'~'.$total.'~~'.$desc.'返现';
            $sina->batchpaytrade($order_no, "", "", $val, "vcCommission2");
            sinalog(0, "", 25, $order_no, $total, time(), 0);
        }
    }

    /**
     * 二重礼奖项
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    function themay_scende($uid)
    {
        if (in_array($uid, C("OFFLINE_UID"))) {
            Log::write("推荐人是陈晓升或蔡晓佳,不能玩");
            return;
        }
        //邀请总数
        $recommend_count = M("recommend_first")->where(array("recommend_uid"=>$uid))->sum("recommend_count");
        file_put_contents('recommend_log.txt', "邀请总数:".var_export($recommend_count, true)."!!!!!!", FILE_APPEND);
        //投资量
        $recommend_sum = M("recommend_invest")->where(array("recommend_uid"=>$uid))->sum("invest_money");
        file_put_contents('recommend_log.txt', "投资量:".var_export($recommend_sum, true)."!!!!!!", FILE_APPEND);
        //实际投资人数
        $invest_count = M("recommend_invest")->where(array("recommend_uid"=>$uid,"invest_money"=>array("gt",0
    )))->count("id");
    file_put_contents('recommend_log.txt', "实际投资人数:".var_export($invest_count, true)."!!!!!!", FILE_APPEND);

        //邀请人获奖情况
        $first = M("recommend_seconde")->where(array("uid"=>$uid,"prize_type"=>1))->count();
        $seconde = M("recommend_seconde")->where(array("uid"=>$uid,"prize_type"=>2))->count();
        $third = M("recommend_seconde")->where(array("uid"=>$uid,"prize_type"=>3))->count();
        file_put_contents('recommend_log.txt', "邀请人获奖情况1:".var_export($first, true)."!!!!!!", FILE_APPEND);
        file_put_contents('recommend_log.txt', "邀请人获奖情况2:".var_export($seconde, true)."!!!!!!", FILE_APPEND);
        file_put_contents('recommend_log.txt', "邀请人获奖情况3:".var_export($third, true)."!!!!!!", FILE_APPEND);
        //二重礼状况
        $seconde_info = M("recommend_seconde")->field("COUNT(id) as num,prize_type")->group("prize_type")->select();
        $num_1 = 0;
        $num_2 = 0;
        $num_3 = 0;
        foreach ($seconde_info as $key => $value) {
            if ($value["prize_type"] == 1) {
                $num_1 = $value["num"];
            } elseif ($value["prize_type"] == 2) {
                $num_2 = $value["num"];
            } elseif ($value["prize_type"] == 3) {
                $num_3 = $value["num"];
            }
        }
        file_put_contents('recommend_log.txt', "二重礼状况1:".var_export($num_1, true)."!!!!!!", FILE_APPEND);
        file_put_contents('recommend_log.txt', "二重礼状况2:".var_export($num_2, true)."!!!!!!", FILE_APPEND);
        file_put_contents('recommend_log.txt', "二重礼状况3:".var_export($num_3, true)."!!!!!!", FILE_APPEND);
        if ($first > 0) {
            return;
        }

        if($third==0 && $seconde==0 && $first==0) {
            if ($recommend_count>=8 && $recommend_sum >= 30000 && $num_3 < 20) {
                $data["prize_type"] = 3;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            } elseif ($recommend_count>=20 && $recommend_sum >= 80000 && $num_2 < 10) {
                $data["prize_type"] = 2;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            } elseif ($recommend_count>=50 && $recommend_sum >= 500000 && $num_1 < 1 && $invest_count >= 50) {
                $data["prize_type"] = 1;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            }
        }elseif ($third == 1 && $seconde == 0 && $first==0) {
            if ($recommend_count>=28 && $recommend_sum >= 110000 && $num_2 < 10) {
                $data["prize_type"] = 2;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            } elseif ($recommend_count>=58 && $recommend_sum >= 530000 && $num_1 < 1 && $invest_count >= 50) {
                $data["prize_type"] = 1;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            }
        }elseif ($third == 0 && $seconde == 1 && $first==0){
            if ($recommend_count>=70 && $recommend_sum >= 580000 && $num_1 < 1 && $invest_count >= 50) {
                $data["prize_type"] = 1;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            }
        }elseif ($third == 1 && $seconde == 1 && $first==0){
            if ($recommend_count>=78 && $recommend_sum >= 610000 && $num_1 < 1 && $invest_count >= 50) {
                $data["prize_type"] = 1;
                $data["uid"] = $uid;
                $data["add_time"] = time();
                M("recommend_seconde")->add($data);
            }
        }

    }
