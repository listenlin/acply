<?php
    /**
     * @copyright Copyright 2014 listenlin. All rights reserved.
     * @author listenlin <listenlin521@foxmail.com>
     * @version 1.0
     * @package Acply
     *
     * 框架的入口文件，欲使用本框架，应用入口文件只需包含本文件即可。同时给出应用目录的路径变量$applicationPath
     * 与及开发者希望包含的路径（必须使用绝对路径）
     */
    
    // 当前php版本检测
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP version > 5.3.0 !');
}

    // 载入自动加载模块
    include __DIR__ . '/Loader/acp_loader.php';

    // 设置错误处理
    set_error_handler('Acp_error::phpErrorHandler');

    // 设置异常处理
    set_exception_handler('Acp_error::errorHandle');

    // PHP进程关闭调用函数
    register_shutdown_function('Acp_error::shutdown');

    // 获取前端控制器
    $Afc = Acp_frontcontrol::getFrontControl($applicationPath, $customIncludePath, $configPath);

    // 根据客户端accept来决定ECHO配置值
if ((isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || (isset($_GET['AcceptContentType']) && $_GET['AcceptContentType'] == 'json')) {
    $GLOBALS['whole']['config']->allowEdit();
    $GLOBALS['whole']['config']->echoType = 'JSON';
    $GLOBALS['whole']['config']->noAllowEdit();
}

    // 运行应用
    $Afc->run();
