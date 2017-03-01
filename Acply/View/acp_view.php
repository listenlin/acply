<?php
	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_view
	 *
	 *视图辅助类
	 */
	class Acp_view extends Acp_base{
		private $control = null;
		public function __construct(Acp_control $ctl){
			parent::__construct();
			$this->control = $ctl;
		}
		public function __destruct(){
			parent::__destruct();
		}
		public function link($href) {
			if ( ! is_array ( $href ) ) {
				$href = array ( $href . '' );
			}
			$URL_ROOT = $this->control->getUrlRoot();
			foreach ( $href as $v ) {
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$URL_ROOT}{$v}\" />";
			}
		}
		public function js($src){
			if ( ! is_array ( $src ) ) {
				$src = array ( $src . '' );
			}
			$URL_ROOT = $this->control->getUrlRoot();
			foreach ( $src as $v ) {
				echo "<script type=\"text/javascript\" src=\"{$URL_ROOT}{$v}\"></script>";
			}
		}
		public function jslteIE($version,$src){
			echo "<!--[if lte IE $version]><script type=\"text/javascript\" src=\"$src\"></script><![endif]-->";
		}
		public function base(){
			echo "<base href=\"http://$_SERVER[SERVER_NAME]" . $this->control->getUrlRoot() . '" />';
		}
		public function baseSsl(){
			echo "<base href=\"https://$_SERVER[SERVER_NAME]" . $this->control->getUrlRoot() . '" />';
		}
		/**
		 * 通过此函数解决表单的跨域请求伪造攻击
		 */
		public function solvePostCSRF($key) {
			if(empty($key)){
				throw new Acp_error("key不能为空", Acp_error::PARAM);
			}
			
			// Ubuntu下面很慢，不知道为什么！！！
			// $token = base64_encode(mcrypt_create_iv(300));
			
			$token = base64_encode(sha1(time() * mt_rand() / mt_rand() * mt_rand() / mt_rand()));
			
			$this->control->setSession('ACP_SLOVE_CSRF', $key, $token);
			echo "<input type=\"hidden\" name=\"_ACPLY_HASH_VALUE\" value=\"$token\" />";
		}
		/**
		 * 通过此函数解决GET参数的跨域请求伪造攻击
		 */
		public function solveGetCSRF($key){
			if(empty($key)){
				throw new Acp_error("key不能为空", Acp_error::PARAM);
			}
			
			// Ubuntu下面很慢，不知道为什么！！！
			// $token = md5(mcrypt_create_iv(1000));
			
			$token = base64_encode(sha1(time() * mt_rand() / mt_rand() * mt_rand() / mt_rand()));
			
			$this->control->setSession('ACP_SLOVE_CSRF', $key, $token);
			return "_ACPLY_HASH_VALUE=$token";
		}
	}
