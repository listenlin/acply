<?php
return array(
	//是否开启错误信息显示来调试
	'debug' => FALSE,
	//是否开启数据库错误信息显示来调试 
	'dbdebug' => FALSE,
	//默认的控制器 
	'default_control' => 'login',
	//默认的动作
	'default_action' => 'index',
	//设置框架返回什么格式的数据，还有JSON选项
	'echoType' => 'HTML',
	//会话起作用的URL路径
	'session_path' => '',
	'session_name' => '',
	//会话起作用的网站域名，一般为二级域名
	'session_domain' => '',

	//日志目录配置 错误日志会存放此处 配置错误则存在默认目录中（用户整体应用根目录下的logs目录）， 目录可为绝对地址和相对于整体应用根目录。
	'log' => '/logs/',

	// 缓存文件存放目录
	'cache_path' => 'cache',
	
	// 框架应用所有钩子的目录
	'hook' => 'hook',
		
	// 框架应用所有插件的目录
	'plugin' => 'plugin',
	
	//数据库类型 
	'db' => 'mysql',
	//数据库驱动类型 
	'dbdriver' => 'pdo',
	//数据库地址
	'host' => 'localhost',
	//数据库名称
	'dbname' => '',
	//数据库用户帐号密码，根据权限大小，从上至下，依次减少
	'userH' => 'root',
	'passwordH' => 'root',
	'userM' => '',
	'passwordM' => '',
	'userL' => 'root',
	'passwordL' => '',
	/**
	 * 用户群的权限配置 将用户群的表示信息（某个字符串） 拿来当一个标签，然后在下面进行相关配置。
	 * 权限分为“控制器权限” 和 “动作权限”。
	 * 当授予了某个控制器权限后，还要检测是否有 控制器相应动作的权限。
	 * 配置的值为： 
	 * 1.可用all代表拥有所有权限 
	 * 	（必须放在最前面
	 * 		(前面没有任何符号)， 放中间和后面或者前面有符号将看作控制器或动作）
	 * 2.可用deny（同上）代表没有所有权限 
	 * 3.用->表示控制器到动作的指引 
	 * 4.用“+”号代表拥有的权限（前面无符号默认有加号）。“-”代表没有的权限 
	 * 5.如果留空，表示没有此权限。
	 * 6.后面的权限申明会覆盖前面的申明
	 * 7.没有出现一次的控制器和动作没有权限（条件：前面没有申明all）
	 * 例如： all -> all - receive 
	 * //表示有所有控制器的权限， //但是没有所有控制器的receive动作权限
	 */
	'users' => array(
		array('super'=>''),
		array('general'=>'')
	),
	
);