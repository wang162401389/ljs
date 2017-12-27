<?php
// 全局设置
class ACommonAction extends Action
{
	var $savePathNew=NULL;
	var $thumbMaxWidthNew="10,50";
	var $thumbMaxHeightNew="10,50";
	var $thumbNew=NULL;
	var $allowExtsNew=NULL;
	var $saveRule=NULL;
	var $admin_id = 0;
	var $pre = NULL;
	//验证身份
	function _initialize(){
		$this->pre = C('DB_PREFIX');
		
		$ip=$this->getAcip();
 		//检测ip是否合法
        if(PRODUCTION){
            $this->checkip($ip);    
        }
        
        // check_other_login();
		$query_string = explode("/",$_SERVER['REQUEST_URI']);
		
		!isset($this->justlogin)?$this->justlogin=false:$this->justlogin=$this->justlogin;
		if(session('admin')){//dump(session('adminname'));exit;
			$this->admin_id = session("admin");
			$this->assign('adminname',session('adminname'));
		}elseif(strtolower(ACTION_NAME) != 'verify' && strtolower(ACTION_NAME) != 'login' && strtolower(ACTION_NAME)!='logincheck'){
			redirect("/admin/index/logincheck?code=".$query_string[1]);
			exit;
		}
        //print_r(session('admin'));
        //exit();
		if( !get_user_acl(session('admin')) && !$this->justlogin){
			$this->error('对不起，权限不足');
			exit;
		}

        if (method_exists($this, '_MyInit')) {
            $this->_MyInit();
        }
		
		$datag = get_global_setting();
		$this->glo = $datag;//供PHP里面使用
		$this->assign("glo",$datag);
		
		$bconf = get_bconf_setting();
		$this->gloconf = $bconf;//供PHP里面使用
		$this->assign("gloconf",$bconf);
	}
	
    function checkip($userip){   	
    	//限制ip
    	$ips=require APP_PATH.'Conf/ipconfig.php';   	
    	if(in_array($userip['HTTP_X_FORWARDED_FOR'],$ips) || in_array($userip['REMOTE_ADDR'],$ips) ){
    		return true;
    	}else{
            $this->error('非法ip操作');         
            exit;
        }
	}

    function getAcip(){
        $ip = array();
        $ip['HTTP_X_FORWARDED_FOR'] = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:null;
        $ip['REMOTE_ADDR'] = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:null;
        return $ip;
    }
	
	//上传图片
	function CUpload(){
		if(!empty($_FILES)){
			return $this->_Upload();
		}
	}
	
	function _Upload(){
		import("ORG.Net.UploadFile");
        $upload = new UploadFile();
		
		$upload->thumb = true;
		$upload->saveRule = $this->saveRule;//图片命名规则
		$upload->thumbMaxWidth = $this->thumbMaxWidth;
		$upload->thumbMaxHeight = $this->thumbMaxHeight;
		$upload->maxSize  = C('ADMIN_MAX_UPLOAD') ;// 设置附件上传大小
		$upload->allowExts  = C('ADMIN_ALLOW_EXTS');// 设置附件上传类型
		$upload->savePath =  $this->savePathNew?$this->savePathNew:C('ADMIN_UPLOAD_DIR');// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
		}
		
		return $info;
	}
	//上传图片END
    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param string $name 数据对象名称
      +----------------------------------------------------------
     * @return HashMap
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _search($name = '') {
        //生成查询条件
        if (empty($name)) {
            $name = $this->getActionName();
        }
        $model = M($name);
        $map = array();
        foreach ($model->getDbFields() as $key => $val) {
            if (substr($key, 0, 1) == '_')
                continue;
            if (isset($_REQUEST[$val]) && $_REQUEST[$val] != '') {
                $map[$val] = $_REQUEST[$val];
            }
        }
        return $map;
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _list($model, $field ='*', $map = array(), $sortBy = '', $asc = false,$search=array()) {
		session('listaction',ACTION_NAME);
        //排序字段 默认为主键名
		$pkname = $model->getPk();
        $order = !empty($sortBy) ? $sortBy : $pkname;
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        $sort = $asc ? $asc : 'desc';
        //取得满足条件的记录数
        $count = $model->where($map)->count($pkname);
        import("ORG.Util.Page");
        //创建分页对象
        $listRows = !empty($_REQUEST['listRows'])?$_REQUEST['listRows']:C('ADMIN_PAGE_SIZE');
        $p = new Page($count, $listRows);
        //分页查询数据
        $list = $model->field($field)->where($map)->order($order . ' ' . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
        //分页跳转的时候保证查询条件
        foreach ($map as $key => $val) {
            if (!is_array($val)) {
                $p->parameter .= "$key=" . urlencode($val) . "&";
            }
        }
		
		foreach($search as $key => $val){
            if (!is_array($val)) {
                $p->parameter .= "$key=" . urlencode($val) . "&";
            }
		}

        if (method_exists($this, '_listFilter')) {
            $list = $this->_listFilter($list);
        }

        //分页显示
        $page = $p->show();
        //列表排序显示
        $sortImg = $sort;                                   //排序图标
        $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列';    //排序提示
        $sort = $sort == 'desc' ? 1 : 0;                     //排序方式
		
        //模板赋值显示
        $this->assign('list', $list);
        $this->assign("pagebar", $page);
        return;
    }
	//添加
    public function add() {
		if (method_exists($this, '_addFilter')) {
            $this->_addFilter();
        }
        $this->display();
    }
	//编辑
    public function edit() {
        $model = D(ucfirst($this->getActionName()));

        $id = intval($_REQUEST['id']);
 		
		if (method_exists($this, '_editFilter')) {
            $this->_editFilter($id);
        }
        $colligate=D('borrow_info_additional')->pay_first_money($id);
        
        $vo = $model->find($id);
        $this->assign('vo', $vo);
        if($vo['product_type'] == 5){
            $fee = M("allwood_ljs")->where("borrow_id = {$id}")->find();
            $this->assign("colligate",$fee['fee']);
        }else{
             $this->assign("colligate",$colligate);
        }
        $this->display();
    }
	
	//添加数据
    public function doAdd() {
        $model = D(ucfirst($this->getActionName()));
        if (false === $model->create()) {
            $this->error($model->getError());
        }
		
		if (method_exists($this, '_doAddFilter')) {
            $model = $this->_doAddFilter($model);
        }
		
        //保存当前数据对象
        if ($result = $model->add()) { //保存成功
            //成功提示
            $this->assign('jumpUrl', __URL__);
            $this->success(L('新增成功'));
        } else {
            //失败提示
            $this->error(L('新增失败'));
        }
    }
	
	//添加数据
    public function doEdit() {
        $model = D(ucfirst($this->getActionName()));
        if (false === $model->create()) {
            $this->error($model->getError());
        }
		
		if (method_exists($this, '_doEditFilter')) {
            $model = $this->_doEditFilter($model);
        }
		
        //保存当前数据对象
        if ($result = $model->save()) { //保存成功
			if (method_exists($this, '_AfterDoEdit')) {
				$this->_AfterDoEdit();
			}
            //成功提示
            $this->assign('jumpUrl', __URL__."/".session('listaction'));
            $this->success(L('修改成功'));
        } else {
            //失败提示
            $this->error(L('修改失败'));
        }
    }

	//删除数据
	public function doDel(){
        $model = D(ucfirst($this->getActionName()));
        if (!empty($model)) {
			$id = $_REQUEST['idarr'];
            if (isset($id)) {
				if (method_exists($this, '_doDelFilter')) {
					$this->_doDelFilter($id);
				}
				$pk = $model->getPk();
				if (false !== $model->where("{$pk} in ({$id})")->delete()) {
					$this->success(L('删除成功'),'',$id);
				} else {
					$this->error(L('删除失败'));
				}
            } else {
                $this->error('非法操作');
            }
        }
	}
	
	public function memberheaderuplad(){
		$uid = $_GET['uid'];
		if(empty($uid)) exit;
		else redirect(__ROOT__."/Style/header/upload.php?uid={$uid}");
		exit;
	}
    //标号编排
    // public function borrowidlayout1($borrowid,$protype){
    //     $conf = C(RENUMBER_BORROW);
    //     if($conf['enable']==1){
    //         $newgrade =  $conf['new_grade'];
    //         if($borrowid<$newgrade){
    //             $bid="ZJ".$borrowid;
    //         }else{
    //             if($protype==1||$protype==2||$protype==3){
    //                 $bid = M('borrow_pledge')->where("borrow_id=".$borrowid)->find();
    //                 $bid="ZJ".$bid['id'];
    //             }else if($protype==4){
    //                 $bid = M('borrow_finance')->where("borrow_id=".$borrowid)->find();
    //                 $bid="RJ".$bid['id'];
    //             }else if($protype==6){
    //                 $bid = M('borrow_credit')->where("borrow_id=".$borrowid)->find();
    //                 $bid="XJ".$bid['id'];
    //             }
    //         }
    //         return $bid;
    //     }else{
    //         return $borrowid;
    //     }
    // }
    //标号搜索
    public function bidsousuo($borrowid){
        $conf = C('RENUMBER_BORROW');
        $renumber = $conf['new_grade'];
        if($conf['enable']==1){
            $pre = substr($borrowid,0,2);
                $id = substr($borrowid,2);
                if($pre == ZJ){//质金链
                    if($id<$renumber){
                        $borrowid=$id;
                    }else{
                        $bid = M('borrow_pledge')->where("id=".$id)->find();
                        $borrowid=$bid['borrow_id'];
                    }
                }else if($pre == RJ){//融金链
                    $bid = M('borrow_finance')->where("id=".$id)->find();
                    $borrowid=$bid['borrow_id'];
                }else if($pre == XJ){//信金链
                    $bid = M('borrow_credit')->where("id=".$id)->find();
                    $borrowid=$bid['borrow_id'];
                }else if($pre == YJ){//优金链
                    $bid = M('borrow_optimal')->where("id=".$id)->find();
                    $borrowid=$bid['borrow_id'];
                }
                return $borrowid;
            }else{
                return $borrowid;
            }
    }
}
?>
