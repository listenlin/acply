<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_user
	 *
	 *对当前用户状态的记录与相关数据的记录
	 */
	/*
	* 记录当前用户的状况。 1.登录的用户 2.未登录的游客
	*/
	class Acp_user extends Acp_base {
		/**
		 *  尚未登录的游客
		 * @var int
		 */
		const NOT_LOGIN = 0;
		/**
		 * 登录的网站用户
		 * @var int
		 */
		const LOGINED = 1;
		/**
		 * 用户登录状态
		 * @var int
		 */
		private $type;
		/**
		 * 用户的唯一ID号
		 * @var int
		 */
		private $id;
		/**
		 * 用户名称
		 * @var string
		 */
		private $name = '已修改User类机制';

		private $_session;

		// USER 对象
		public static $user;

		public function __construct() {
			$this->_session = Acp_session::getSession();

			if ($this->_session->__id !== null) {
				$this->id = $this->_session->__id;
			}

			if ($GLOBALS['whole']['session']->ACPLY_VDY_LOGIN === true) {
				$this->type = self::LOGINED;
				$this->id = $GLOBALS['whole']['session']->ACPLY_ADMINID;
				$this->name = $GLOBALS['whole']['session']->ACPLY_ADMINNAME;
			} else {
				$this->type = self::NOT_LOGIN;
				$this->id = -1;
				$this->name = '尚未登录';
			}
		}
		
		/**
		 * 判断用户是否登陆（静态方法）
		 * 
		 * @return boolean
		 */
		public static function isGuest()
		{
			return self::$user === null;
		}

		/**
		 * 检查当前用户有无登录
		 * @return boolean
		 */
		
		public function checkLogin() {
			return $this->id !== null;
			// return !!$this->type;
		}
		/**
		 * 使该用户变为已登录状态
		 * @param int $id 用户的唯一主键值
		 * @param string $name 用户名称
		 * @param string $other 用户信息(包括权限)
		 */
		public function login($id, $name, $pass = '') {
			$this->id = $this->_session->__id = $id;

			$this->name = $name;

			$this->_session->ACPLY_ADMINPASS = $pass;

			$s->ACPLY_VDY_LOGIN = true;
			$s->ACPLY_ADMINID = ($this->id = $id);
			$s->ACPLY_ADMINNAME = ($this->name = $name);

			// 将盐值存入 SESSION 中
			$s->ACPLY_ADMINPASS = $pass;
			
			$this->type = self::LOGINED;
		}
		/**
		 * 是当前用户退出登录
		 * @return void
		 */
		public function logout() {
			$this->id = $this->_session->__id = null;

			$this->_session->destroy();

			$this->id = '';
			$this->name = '';
			$this->type = self::NOT_LOGIN;
		}
		/**
		 * 给用户设置一个序列化后的值
		 * @param mixed $d
		 */
		public function setData($d) {
			$s = Acp_session::getSession();
			$s->ACPLYUSERDATA = serialize($d);
		}
		/**
		 * 返回解序列化的值
		 * @throws Acp_error
		 * @return mixed
		 */
		public function getData() {
			$s = Acp_session::getSession();
			$d = unserialize($s->ACPLYUSERDATA);
			if ($d === false) {
				throw new Acp_error('反序列化出错 - ' . $s->ACPLYUSERDATA, Acp_error::PROGRAM);
			}
			return $d;
		}
		/**
		 * 获取用户主键ID值
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}

		/**
		 * 获取用户的名称
		 * 
		 * @return string
		 */
		public static function getName()
		{
			if (self::$user) {
				return self::$user['username'];
			}
			return null;
		}
	}
