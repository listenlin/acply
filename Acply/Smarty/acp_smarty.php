<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_smarty
	 *
	 *对模板引擎进行再次封装，变成自己的模板
	 */
	/*
	* 模板引擎预处理
	*/
	include_once 'Smarty.class.php';
	class Acp_smarty extends Smarty {
		public function __construct() {
			parent::__construct();
			$this->setTemplateDir($GLOBALS['whole']['smarty'] . '/templates/');
			$this->setCompileDir($GLOBALS['whole']['smarty'] . '/templates_c/');
			$this->setConfigDir($GLOBALS['whole']['smarty'] . '/configs/');
			$this->setCacheDir($GLOBALS['whole']['smarty'] . '/cache/');
			$this->caching = false;
			$this->assign('URL_ROOT', $GLOBALS['whole']['url_root']);
			$this->registerPlugin('function', 'localToUrl', 'Acp_util::localToUrl');
		}
		public function __destruct() {
			parent::__destruct();
		}
		/**
		 * 通过此函数解决跨域请求伪造攻击
		 */
		public function solveCSRF($key) {
			$token = base64_encode(mcrypt_create_iv(300));
			$GLOBALS['whole']['session']->set('ACP_SLOVE_CSRF', $key, $token);
			$this->assign('_CSRF', "<input type=\"hidden\" name=\"_ACPLY_HASH_VALUE\" value=\"$token\" />");
		}
	}
