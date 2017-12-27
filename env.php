<?php
//检查当前版本
if (isset($_SERVER['APP_ENV'])) {
    $env = strtoupper($_SERVER['APP_ENV']);
    if (!defined($env)) {
        define($env, true);
    }
    unset($env);
}

// 预发环境
defined('STAGING') || define('STAGING', false);
// 测试环境
defined('TESTING') || define('TESTING', false);
// 开发环境
defined('DEVELOPMENT') || define('DEVELOPMENT', false);
// 生产环境
defined('PRODUCTION') || define('PRODUCTION', !(DEVELOPMENT || STAGING || TESTING));
?>