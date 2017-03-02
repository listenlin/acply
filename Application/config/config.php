<?php
return array(
        //是否开启错误信息显示来调试
        'debug' => true,
        //是否开启数据库错误信息显示来调试
        'dbdebug' => true,
        //默认的控制器
        'default_control' => 'site',
        //默认的动作
        'default_action' => 'index',
        //设置框架返回什么格式的数据
        'echoType' => 'HTML',
        //会话起作用的URL路径
        'session_name' => 'acplyid',
        'session_path' => '/',
        //会话起作用的网站域名，二级域名
        'session_domain' => '',
        //日志目录配置 错误日志会存放此处 配置错误则存在默认目录中（用户整体应用根目录下的logs目录）， 目录可为绝对地址和相对于整体应用根目录。
        'log' => 'application/logs/',
        
        //数据库名称
        'dbname' => 'acp',
        'userM' => 'root',
        'passwordM' => 'root',
        'userL' => 'root',
        'passwordL' => 'root'
);
