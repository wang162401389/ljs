<?php
    // 自动执行
    ignore_user_abort(); // 关掉浏览器，PHP脚本也可以继续执行.
    set_time_limit(0); // 通过set_time_limit(0)可以让程序无限制的执行下去
    $interval = 5;// 每隔5s运行
    //方法1--死循环
    do{
        expire(); 
        sleep($interval); // 等待5s    
    }while(true);
?>