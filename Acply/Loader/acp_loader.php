<?php

	/**
	 * @copyright Copyright 2014 listenlin. All rights reserved.
	 * @author listenlin <listenlin521@foxmail.com>
	 * @version 1.0
	 * @package Acply\Loader
	 *
	 * 用自动加载函数将include路径设置好
	 * 同时注册类自动加载函数，使其自动加载框架所需的类
	 */
	set_include_path('.' . PATH_SEPARATOR . dirname(__DIR__) . PATH_SEPARATOR . realpath(dirname(__DIR__) . '\\..\\Smarty'));
	function acpautoload($cname) {
		$c = explode('_', $cname);
		// 加载框架类
		if (isset($c[1]) && $c[0] === 'Acp') {
			include ucfirst($c[1]) . DIRECTORY_SEPARATOR . strtolower($cname) . '.php';
			// 一般用来加载数据模型
		} elseif ($c[0] !== 'Acp') {
			include strtolower("$cname.php");
		}
	}
	spl_autoload_register('acpautoload');
