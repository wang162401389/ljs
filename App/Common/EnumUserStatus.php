<?php
// 全局设置
class EnumUserStatus 
{
	const __default = self::logout;
    const NotExist = 1024;
    //未登录
    const Logout = 0;
    //设置新浪密码
    const SinaPay = 1;
    //实名
    const RealName = 2;
    //绑卡
    const BindCard = 4;
    //未投资
    const NotInvestBefore = 8;
}
?>