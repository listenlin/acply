<?php
	/**
	 * @copyright Copyright 2014 listenlin. All rights reserved.
	 * @author listenlin <listenlin521@foxmail.com>
	 * @version 1.0
	 * @package Acply\Acp_frontcontrol
	 *
	 * 对前端控制器的封装
	 *
	 * 站在框架入口观看全局，做好整体框架的相关配置和去调用某个控制器
	 */
	class Acp_frontcontrol extends Acp_base {
		// 前端控制类实例对象
		private static $fcid = null;

		public function __construct($application, array $custom, array $configs) {

			$application = realpath($application);

			// 定义资源所在文件夹的路径
			$GLOBALS['RESOURCE_DIR'] = strtr(dirname(__dir__ ),'\\', '/') . '/Resource/';

			// 定义加载目录(控制器,模型,视图等)
			$include_path = array(
				'control_path' => $application . DIRECTORY_SEPARATOR . 'control',
				'model_path' => $application . DIRECTORY_SEPARATOR . 'model',
				'view_path' => $application . DIRECTORY_SEPARATOR . 'view',
				'smarty_path' => realpath($application . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Smarty'),
			);

			// 自定义加载目录
			foreach ($custom as $ve) {
				$ve = realpath($ve);
				if (is_dir($ve)) {
					$include_path[] = $ve;
				}
			}
			set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $include_path));

			// 添加自定义配置文件
			$configs[] = $application . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
			
			// 入口的URL地址
			$GLOBALS['whole']['url_root'] = strtr(dirname($_SERVER['SCRIPT_FILENAME']).'/',array(rtrim($_SERVER['DOCUMENT_ROOT'], '/')=>''));
			
			// 本应用的目录
			$GLOBALS['whole']['app_root'] = $application;
			
			// 总体应用的目录
			$GLOBALS['whole']['dir_root'] = dirname($application);
			
			// 控制器的目录
			$GLOBALS['whole']['control'] = $include_path['control_path'];
			
			// 视图的目录
			$GLOBALS['whole']['smarty'] = $include_path['view_path'];
			
			// CONFIG
			$GLOBALS['whole']['config'] = Acp_config::getConfig($configs);
			
			// SESSION
			$GLOBALS['whole']['session'] = Acp_session::getSession();
			
			// ACCEPT
			$GLOBALS['whole']['accept'] = new Acp_accept();
			
			// USER
			$GLOBALS['whole']['user'] = new Acp_user();

			// 显示所有错误
			error_reporting(E_ALL);
			ini_set('display_errors', $GLOBALS['whole']['config']->debug);
		}


		/**
		 * 传入配置参数，返回前端控制对象
		 *
		 * @param  string $application 应用目录路径地址
		 * @param  array $include_dir	自定义配置
		 * @param  array $config 其它配置
		 * @return Acp_frontcontrol
		 */
		static public function getFrontControl($application = '', array $include_dir, array $config) {
			if ($application !== '')
			{
				if (self::$fcid === null)
				{
					self::$fcid = new self($application , $include_dir , $config);
				}
			}
			elseif (self::$fcid === null)
			{
				throw new Acp_error('未输入框架应用的目录路径', Acp_error::PARAM);
			}
			return self::$fcid;
		}


		/**
		 * 根据路由运行控制器，完成相应功能。
		 */
		public function run() {
			$control = $GLOBALS['whole']['accept']->getCmd(0);
			$action = $GLOBALS['whole']['accept']->getCmd(1);

			Acp_route::relay($control, $action);
		}
	}