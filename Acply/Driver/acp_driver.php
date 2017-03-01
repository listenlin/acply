<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_driver
	 *
	 *底层数据库驱动管理类
	 */
	class Acp_driver extends Acp_base {
		/**
		 * 数据库驱动接口对象
		 *
		 * @var Acp_driver_pdo
		 */
		private static $driver = null;
		/**
		 * 获取当前的数据库驱动实例对象
		 */
		static public function getDb() {
			if (self::$driver === null) {
				$c = 'Acp_driver_' . Acp_config::getConfig()->dbdriver;
				self::$driver = new $c();
				if (!(self::$driver instanceof Acp_driver_interface)) {
					self::$driver = null;
					throw new Acp_error("该数据库驱动类：$c 未实现必须的接口", Acp_error::PROGRAM);
				}
			}
			return self::$driver;
		}
	}
