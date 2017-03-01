<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_session
	 *
	 *对当前会话的封装
	 */
	/*
	* 将会话SESSION抽象出来
	*/
	class Acp_session extends Acp_base {
		private static $seid = null;
		static public function getSession() {
			if (self::$seid === null) {
				self::$seid = new self();
			}
			return self::$seid;
		}
		public function __construct() {
			$cfg = Acp_config::getConfig();
			$path = trim($cfg->session_path);
			if ($path !== '') ini_set('session.cookie_path', $path);
			$domain = trim($cfg->session_domain);
			if ($domain !== '') ini_set('session.cookie_domain', $domain);
			$name = trim($cfg->session_name);
			if ($name !== '') {
				ini_set('session.name', $name);
			}
			ini_set('session.use_cookies', 1);
			ini_set('session.use_only_cookies', 1);
			ini_set('session.referer_check', $domain);
			ini_set('session.cookie_httponly', true);
			session_start();
		}
		public function __isset($n) {
			return isset($_SESSION[$n]);
		}
		public function __get($n) {
			return isset($_SESSION[$n]) ? $_SESSION[$n] : null;
		}
		public function get() {
			$length = func_num_args();
			if ($length < 1) return $_SESSION;
			for ($i = 0; $i < $length; $i++) {
				$k = func_get_arg($i);
				if ($i == 0) {
					if (!isset($_SESSION[$k])) {
						return null;
					}
					$current = &$_SESSION[$k];
				} else {
					// 当前指向不是数组.
					if (!is_array($current)) {
						return null;
					}
					// 此项不存在
					if (!isset($current[$k])) {
						return null;
						// 此项存在但不是数组.
					}
					$current = &$current[$k];
				}
			}
			return $current;
		}
		public function __set($n, $v) {
			$_SESSION[$n] = $v;
		}
		public function set() {
			$length = func_num_args();
			if ($length < 2) return;
			$session = $_SESSION;
			for ($i = 0; $i < $length - 1; $i++) {
				$k = func_get_arg($i);
				if ($i == 0) {
					if (!isset($session[$k])) {
						$session[$k] = array();
					}
					$current = &$session[$k];
				} else {
					// 当前指向不是数组，覆盖成数组
					if (!is_array($current)) $current = array();
					// 此项不存在
					if (!isset($current[$k])) {
						$current[$k] = array();
						// 此项存在但不是数组，就覆盖掉。
					} elseif (!is_array($current[$k])) {
						$current[$k] = array();
					}
					$current = &$current[$k];
				}
			}
			$current = func_get_arg($length - 1);
			$_SESSION = $session;
		}
		public function __unset($n) {
			unset($_SESSION[$n]);
		}
		public function destroy() {
			$length = func_num_args();
			if ($length === 0) {
				foreach ($_SESSION as $key => $v) {
					unset($_SESSION[$key]);
				}
				if (!headers_sent()) {
					foreach ($_COOKIE as $key => $v) {
						setcookie($key, "", time() - 3600);
					}
				}
				session_destroy();
			} elseif ($length === 1) {
				unset($_SESSION[func_get_arg(0)]);
			} else {
				$session = $_SESSION;
				for ($i = 0; $i < $length - 1; $i++) {
					$k = func_get_arg($i);
					if ($i == 0) {
						if (!isset($session[$k])) {
							return;
						}
						$current = &$session[$k];
					} else {
						// 当前指向不是数组.
						if (!is_array($current)) {
							return;
						}
						// 此项不存在
						if (!isset($current[$k])) {
							return;
						}
						$current = &$current[$k];
					}
				}
				$k = func_get_arg($i);
				if (isset($current[$k])) unset($current[$k]);
				$_SESSION = $session;
			}
		}
	}
