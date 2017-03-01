<?php

	/**
	 * @copyright Copyright 2014 listenlin. All rights reserved.
	 * @author listenlin <listenlin521@foxmail.com>
	 * @version 1.0
	 * @package Acply\Acp_route
	 *
	 * 对URL的路径映射转发
	 *
	 * 请求转发类<br>
	 * 将本控制器的权利暂时移交转发给另一个控制器
	 */
	class Acp_route extends Acp_base {


		/**
		 * 记录此时详细的信息到日志文件中
		 *
		 * @param string $class 此时控制器名字
		 * @param string $method 此时控制器的动作名字
		 * @param string $result 发生错误的原因描述
		 * @return void
		 */
		static private function logDetail($class, $method, $result) {
			function build_post($arr) {
				$v = array();
				foreach ($arr as $value) {
					if (is_array($value)) {
						$v[] = ' ( ' . build_post($value) . ' ) ';
					} else {
						$v[] = $value;
					}
				}
				return implode(',', $v);
			}
			$post = build_post($_POST);
			Acp_log::log(
				'routeError',
				date('Y-m-d H:i:s'),
				$_SERVER['REMOTE_ADDR'],
				'引导用户到达当前页面的URL：' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
				"请求的控制器为：$class",
				"请求的控制器动作为：$method",
				'请求的方法为：' . (isset($_SERVER['METHOD']) ? $_SERVER['METHOD'] : ''),
				'请求的GET查询字符串为：' . http_build_query($_GET),
				"请求的POST参数为：$post",
				'用户代理信息：' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
				"结果：$result"
				);
		}


		/**
		 * 给用户返回404状态，同时结束脚本执行
		 *
		 * @param string $class 此时控制器名字
		 * @param string $method 此时控制器的动作名字
		 * @param string $result 发生错误的原因描述
		 */
		static private function notFound($class, $method, $result) {
			self::logDetail($class, $method, $result);
			Acp_util::notFound();
		}


		/**
		 * 将自己的控制权转发给另一个控制器或者执行一个动作
		 *
		 * @param string $class 控制器的类名
		 * @param string $originMethod 控制器的动作名称
		 * @throws Acp_error
		 */
		static public function relay($class, $originMethod) {
			$class = strtolower($class);
			$method = strtolower($originMethod) . 'Action';

			$control_file_path = $GLOBALS['whole']['control'] . "/{$class}.php";
			if ( ! file_exists($control_file_path)) {
				// Acp_response::response_show("控制器（{$class}）不存在!", '', -1);
				self::notFound($class, $method, '不存在此控制器的源代码文件');
			}

			// 载入控制器文件
			include_once $control_file_path;

			// 判断控制器是否存在
			if ( ! class_exists($class))
			{
				// Acp_response::response_show("请确定在文件中是否存在（{$class}）控制器!", '', -1);
				self::notFound($class, $method, "存在此控制器$class 的源代码文件，但不存在此控制器类！");
			}
			else
			{
				// 实例化该控制器类
				$Ctlr = new $class();

				// 判断当前控制器是否继承主控制器
				if ($Ctlr instanceof Acp_control == FALSE)
				{
					// Acp_response::response_show("请确定控制器（{$class}）格式是否正确!", '', -1);
					self::notFound($class, $method, "此请求控制器对象不是控制器类的实例");
				}

				// 判断当前控制器是否定义指定方法
				if ( ! method_exists($class, $method))
				{
					// Acp_response::response_show("您请求的地址错误（{$class}->{$method}()）!", '', -1);
					self::notFound($class, $method, "控制器$class 不存在动作方法 - $method");
				}
				else
				{

					// 如果实现了 检查是否登录接口
					if ($Ctlr instanceof Acp_control_check_login)
					{
						if ($GLOBALS['whole']['user']->checkLogin())
						{
							// 如果已登录，也调用此自定义方法
							$Ctlr->logined();
						}
						else
						{
							// 没有登录，则调用用户的自定义方法
							$Ctlr->notLogin();
						}
					}

					// 如果实现了控制器验证功能接口
					if ($Ctlr instanceof Acp_control_auth)
					{

						// 获取到代表用户身份的字符串
						$position = $Ctlr->checkControlAuth();

						// 检测用户是否有权限使用此控制器和动作
						if (!Acp_auth::check(trim($position), $class, $method))
						{
							$name = $GLOBALS['whole']['user']->getName();
							self::logDetail($class, $method, "用户$name 尝试进入无权限的禁止区域$class - $method 。已被阻止。");
							throw new Acp_error("用户$name 尝试进入无权限的禁止区域$class - $method 。已被阻止。", Acp_error::AUTHORITY);
						}
					}

					// 在执行动作前的总体先行方法
					if (method_exists($Ctlr, "Before"))
					{
						$Ctlr->Before();
					}

					// 该动作的先行方法
					if(method_exists($Ctlr, $originMethod.'Before'))
					{
						call_user_func(array($Ctlr, $originMethod.'Before'));
					}

					/**
					 * 执行该动作  原来的方法 $Ctlr->$method();
					 * @example control::method(segments)
					 */
					if(empty($_GET['acplyaccept']))
					{
						$args = array();
					}
					else
					{
						$args = array_slice(explode('/', $_GET['acplyaccept']), 2);
					}
					call_user_func_array(array($Ctlr, $method), $args);


					// 该动作的后行方法
					if(method_exists($Ctlr, $originMethod.'After'))
					{
						call_user_func(array($Ctlr, $originMethod.'After'));
					}

					// 执行动作后的总体后行方法
					if (method_exists($Ctlr, "After"))
					{
						$Ctlr->After();
					}
				}
			}
		}
	}