<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_config
	 *
	 *对应用和框架的配置处理
	 */
	/**
	 * 框架配置；处理类
	 */
	class Acp_config extends Acp_base {

		private static $cfgid = NULL;

		// 各项配置的数组储存
		private $cfg = NULL;

		// 是否允许在程序运行中修改配置项
		private $allow_edit = FALSE; 

		// 自定义配置文件路径，是否允许编辑配置，是否允许修改配置文件
		static public function getConfig(array $dir = array()) {
			if (self::$cfgid === NULL) {
				self::$cfgid = new self($dir);
			}
			return self::$cfgid;
		}


		public function __construct($dir) {
			$this->cfg = include "$GLOBALS[RESOURCE_DIR]Config/global_config.php";
			foreach ($dir as $v){
				$this->cfg = array_replace_recursive ( $this->cfg , include $v );
			}
		}


		public function __destruct() {
			$this->cfg = NULL;
		}


		/**
		 * 不允许程序增加临时配置项，运行完后就销毁
		 */
		public function allowEdit() {
			$this->allow_edit = true;
		}


		/**
		 * 允许程序增加临时配置项
		 */
		public function noAllowEdit() {
			$this->allow_edit = false;
		}


		public function __get($n) {
			if (isset($this->cfg[$n])) {
				return $this->cfg[$n];
			} else {
				throw new Acp_error("没有配置项 - $n", Acp_error::PROGRAM);
			}
		}

		
		public function __set($n, $v) {
			if ($this->allow_edit) { // 如果允许程序运行时增加编辑配置项
				$this->cfg[$n] = $v;
			}else{
				throw new Acp_error("不允许修改配置项！", Acp_error::PROGRAM);
			}
		}
	}
