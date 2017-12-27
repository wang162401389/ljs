<?php
return array(
    'DB_TYPE'           => 'mysql',
    'DB_HOST'           => '172.16.20.90',
    'DB_NAME'           =>'ccfaxp2p',
    'DB_USER'           =>'lianjinsuo8899',
    'DB_PWD'            =>'ZuDrhfnpLTGjQ6@zhouwei_998877',
    'DB_PORT'           =>'3306',
    'DB_PREFIX'         =>'lzh_',


    'REDIS_INFO'=>array(
        'host'=>'172.16.20.75',
        'auth'=>'tesu@365.com',
    ),
    'ADD_FUNCTION'=>array(
        'repayment'=>array('enable'=>1,'account'=>'family','tel'=>'15889675827',"account1"=>' ','tel1'=>' '),
        'friend_invest'=>array('enable'=>0),
        'borrow_expired'=>array('enable'=>1),
    ),
    'START_FLAG'=>array(
        'break_point_1'=>'798',//新合同起始ID
        'break_point_2'=>'865',//第2版本的合同起始ID
        'break_point_3'=>'900',//第3版本的合同起始ID
    ),
    'EVENT_INFO'=>array(
        'enable'=>0,
        'mobile_url'=>"https://offline.ccfax.cn/members/apr",
        "mobile_banner"=>"img/banner/6.png",
        'index_url'=>"https://offline.ccfax.cn/members/apr",
        "mobile_prom"=>"四月活动"
    ),
    'Frind_INFO'=>array(
        'enable'=>1,
    ),

    'SINA_FILE'=>"200016527623_encrypt_pub.pem",
    'SINA_RSAFILE'=>array(
        'private' => 'private_key.pem',
        'public' => '200016527623_sign_pub.pem',
    ),
    'ANCUN'=>array(
            'apiAddress' => 'https://www.51cunzheng.com/openapi',
            'partnerKey' => 'a569d97d75d4e882f54c6bd8108c90ad',
            'secret'     => '92e144a57f15ae122ee46cb067cba20730612f7f',
        ),
    'V_INVEST'=>array(
        'enable'=>0,
        'db'=>array(
            'db_type'           => 'mysql',
            'db_host'           =>'172.16.20.90',
            'db_name'           =>'ccfaxp2p',
            'db_user'           =>'lianjinsuo8899',
            'db_pwd'            =>'ZuDrhfnpLTGjQ6@zhouwei_998877',
            'db_port'           =>'3306',
            'db_prefix'         =>'lzh_',
        ),
         'v_url'=>'v.ccfax.cn',
    ),
    'CCFAX_USER'=>array(
        3215,102,165,148,76,35392,35391,107,3296,8488,3287,71136,80,60430,43405,43624,34811,71770,36064,36519,36164,342,71382,43573,36350,70775,71052,3228,101,3106,4674,73,3124,4650,17340,3243,42301,4627,43611,3585,77,19111,35077,3526,5723,3179,109,108,143,3290,13973,178,137,116,114,36458,169,142,149,138,115,177,17202,16463,8575,20621,7947,128,154,3152,19220,41437,70817,36515,19592,4652,155,15667,15676,3189,21834
    ),
    // 通知短信
    'NOTICE_TEL'=>array(
        // 初審
        "chu"=>'13692267720',
        // 136 9226 7720 許巧真
        // 複審
        "fu"=>'15820425250',
        // 158 2042 5250 龐惠元
        // 風控
        'fengkong'=>'18320830983',
        // 183 2083 0983 劉哲新
        // 財務
        'caiwu'=>'13430873159,18820425250,13692267720'
        // 134 3087 3159 諸葛娟弟
        // 188 2042 5250 龐惠元
        // 136 9226 7720 許巧真

    ),
    'UNIFY_INTERFACE'=>array(
        'enable'=>1,
        'url'=>'http://172.16.20.66:8080',//正式服务
       // 'url'=>'http://182.254.131.15:8080',//测试服
    ),
    'UPLOAD_ZIP'=> array(
            'strServer' => "180.153.89.72",
            'strServerUsername' => "200016527623",
            'strServerprivatekey' => "id_rsa",
            'strServerpublickey' => "id_rsa.pub",
            'UPLOAD_PATH' => "/data001/www/site/ccfaxp2p_online",
             ),
    'RENUMBER_BORROW' =>array(
        'new_grade' => '1039',//标号重新编排起始ID
        'enable' => 1,//开启标号编排
    ),
    'SINGLE_LOGIN'=>array(
        'enable'=>1
    ),
    'EARNINGS' =>array(
        'starting' => '2016-09-01'
    ),
    'DISTRIBUTION'=>array(
            'url' => 'http://115.159.208.43:8080/tscps_foreground/', //分销系统测试服URL
        ),
    'TS1KG'=>array(
            'enable'=>1,
            'host' => 'http://www.ts1kg.com',
            'key'=> '0c355fd6ad99f5605813d4e7859fac77'
    ),
    'RECEPTION'=>array(
        //'id' => 'kf_9372_1470295269297', //测试客服接待组ID
        'id' => 'kf_9372_1470212593293', //正式客服接待组ID
        ),
    'ALLWOOD_ORDER'=>array(
            'URL'=>'http://172.16.20.117:8080/credit_money_background/user/setAllwoodCollectMoney.do',
            'DONG_URL'=>'http://172.16.20.117:8080/credit_money_background/user/setAllwoodFrostMoney.do',
            'uid'=>"2806",
        ),
    'CCFAXAPI_SINA'=>'http://172.16.20.79:8087/sina/sinaauto/handexecute',
    'CCFAX_JAVA'=>'http://172.16.20.98:8087',
    'SHANG_HETONG'=>'1676',
    'SHANG_URL'=>'http://172.16.20.98:8080/',
    'SHANG_CCFAXURL'=>'http://172.16.20.80:8080/',
    'FK_BORROW' => 1765,
    'CCFAXAPI_URL' => "http://172.16.20.79:8087",
    'PIGGY' => "000330",
    'OFFLINE_UID' => array('160','3380'), // 陈晓升160 蔡晓佳3380

    'OUTSIDE_PROFIT'=>array(
        'enable' => 1,
        'simple_fee' =>'0.008',
        'store_fee' =>'0.004',
    ),

    'THE_MAY_ACTIVE' => array(
        'start_time' => "2017-05-04 00:00:00",
        'end_time' => "2017-05-31 23:59:59"
    ),
    
    'THE_OCTOBER_ACTIVE' => array(
        'start_time' => "2017-10-24 00:00:00",
        'end_time' => "2017-10-31 23:59:59"
    ),

    'WEIXIN' => array(
        'appid' => 'wx4523f6c280b89aa9',
        'secret' => '50030fc266fd5804c4091ab527d0ffec'
    ),

    'TIANYU' => array(
            'enable'=>0,
            'APPID'=>'1251559281',
            'SecretId'=>'AKIDADn6LZ4luIPPOSNu55vPmV4Sep6E5h3J',
            'SecretKey'=>'IPM8iLNEOr9ILLHABzc3MVSTM5g8vtDV',
        ),

    'FANLI' => array(
            160,181,105,179,287,3118,3089,3085,161,4504,3088,3380,227,15682,35030
        ),
    "VC_ENABLED" => 1,
    //开始时间
    "VC_FROM" => 1501776000,
    //终止时间
    "VC_TO" => 1504195199,
    // 一些CPS不希望在后台被人看到,单独列出来
    'BLOCK_CPS' => array(
        array("code"=>'caiqi',"name"=>"caiqi"),
        array("code"=>'yinqiao',"name"=>"yinqiao"),
        array("code"=>'mika',"name"=>"mika"),
        array("code"=>'yichengda',"name"=>"yichengda"),
        array("code"=>'ggzh',"name"=>"ggzh"),
        array("code"=>'yicaidao',"name"=>"yicaidao"),
        array("code"=>'siji',"name"=>"siji"),
        array("code"=>'tdn1',"name"=>"tdn1"),
        array("code"=>'tdn2',"name"=>"tdn2"),
        array("code"=>'tdn3',"name"=>"tdn3"),
        array("code"=>'tdn4',"name"=>"tdn4"),
        array("code"=>'tdn5',"name"=>"tdn5"),
        array("code"=>'df',"name"=>"df"),
        array("code"=>'jxh',"name"=>"jxh"),
        array("code"=>'klz',"name"=>"klz"),
        array("code"=>'ym',"name"=>"ym"),
        array("code"=>'zgkj',"name"=>"zgkj"),
    ),
    'BLOCK_CPSTWO' => array(
        array("code"=>'tdn1',"name"=>"tdn1"),
        array("code"=>'tdn2',"name"=>"tdn2"),
        array("code"=>'tdn3',"name"=>"tdn3"),
        array("code"=>'tdn4',"name"=>"tdn4"),
        array("code"=>'tdn5',"name"=>"tdn5"),
    ),
);
