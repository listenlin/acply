<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_cotrol
	 *
	 * 所有用户控制器类的抽象父类
	 */
	/**
	 * 所有控制器应该继承的抽象父类
	 */
	abstract class Acp_control extends Acp_base {
		/**
		 * 视图辅助类
		 * @var Acp_view
		 */
		private $html = NULL;
		/**
		 * 应用钩子辅助类
		 * @var Acp_hook_manager
		 */
		protected $hook = NULL;
		/**
		 * 插件的管理类
		 * @var Acp_plugin_manager
		 */
		protected $plugin = NULL;
		
		public function __construct() {
			parent::__construct();
			$this->html = new Acp_view($this);
			$this->hook = Acp_hook_manager::getManager();
		}

		public function __destruct() {
			parent::__destruct();
			$this->html = NULL;
			$this->hook = NULL;
		}

		/**
		 * 获取某个配置项的值
		 * @example
		 * $this->getConfig('debug');//获取到是否debug的配置值<br>
		 * $this->getConfig('dbdriver');//获取数据库驱动类型<br>
		 * $this->getConfig('自定义的配置');//可以在配置文件里增添自己的配置项
		 * @param string $key 配置项的键
		 * @return mixed 返回配置的值，也有可能是XML对象
		 */
		public function getConfig($key) {
			return $GLOBALS['whole']['config']->$key;
		}
		/**
		 * 获取当前用户的对象引用
		 * @return Acp_user
		 */
		public function getUser() {
			return $GLOBALS['whole']['user'];
		}
		/**
		 * 获取某个用户发来的GET信息
		 * 输入参数则返回那个值。不输入则返回全部值。
		 * @example
		 * 请求URL为“control/action/get/id?a=b&c=d”
		 * 那么print_r($this->getGet());//array([0]=>control,[1]=>action,[2]=>get,[3]=>id,[a]=>b,[c]=>d)
		 * echo $this->getGet(2);//输出“get”
		 * echo $this->getGet('c');//输出“d”
		 * @param string $key 信息的键
		 * @return mixed 输入参数正确，返回字符串否则返回NULL
		 */
		public function getGet($key = null) {
			return $GLOBALS['whole']['accept']->getCmd($key);
		}
		/**
		 * 获取某个用户发来的POST信息
		 * 不输入参数代表获取所有的POST数据。
		 * @param string $key 信息的键
		 * @return mixed 输入参数正确，返回字符串否则返回NULL
		 */
		public function getPost($key = null) {
			return $GLOBALS['whole']['accept']->getInfo($key);
		}
		/**
		 * 设当前某个seesion值
		 * @example 
		 * $this->setSession('das','qwe','会话值');
		 * 相当于$_SESSION['das']['qwe'] = '会话值';
		 * 可以通过参数个数来设置多维的会话值。最后一项是会话的值。下面的两个方法一样。
		 * @return void
		 */
		public function setSession() {
			call_user_func_array(array($GLOBALS['whole']['session'], 'set'), func_get_args());
		}
		/**
		 * 返回当前某个seesion值
		 * @example
		 * echo $this->getSession('das','qwe');//输出“会话值”
		 * @return string seesion的值
		 */
		public function getSession() {
			return call_user_func_array(array($GLOBALS['whole']['session'], 'get'), func_get_args());
		}
		/**
		 * 销毁当前的某项或者全部会话数据
		 * @example
		 * $this->destroySession('asd','qwe');
		 * 相当于unset($_SESSION['das']['qwe']);
		 * $this->destroySession();//这会把当前会话数据全部销毁！
		 * @return void
		 */
		public function destroySession() {
			call_user_func_array(array($GLOBALS['whole']['session'], 'destroy'), func_get_args());
		}
		/**
		 * 返回应用所在的文件夹地址
		 * @example
		 * 如果应用的工程目录在d:/applications/test/中
		 * 那么echo $this->getApplicationRoot();
		 * 值为“d:/applications/test”
		 * @return string
		 */
		public function getApplicationRoot() {
			return $GLOBALS['whole']['app_root'];
		}
		/**
		 * 获取入口URL根路径
		 * @example
		 * 比如应用入口文件在网站根目录的test文件夹中，d:/www/test
		 * 那么echo $this->getUrlRoot();//输出为“/test”
		 * @return string
		 */
		public function getUrlRoot() {
			return $GLOBALS['whole']['url_root'];
		}
		/**
		 * 是否通过请求跨域验证
		 */
		public function validateCSRF($key) {
			$hash_value = $this->getSession('ACP_SLOVE_CSRF', $key);
			$this->destroySession('ACP_SLOVE_CSRF', $key);
			
			$post_value = $this->getPost('_ACPLY_HASH_VALUE');
			$get_value = $this->getGet('_ACPLY_HASH_VALUE');
			if ( ! empty ( $hash_value ) ) {
				if ( $hash_value === $post_value || $hash_value === $get_value ) {
					return true;
				}
			}
			return false;
		}
		/**
		 * 装载视图
		 * @param string $VIEW_FILE_NAME string 文件名(可以省略后缀名)
		 * @param array $VIEW_DATA 传递给视图的参数
		 * @return void 直接输出解析的代码
		 */
		public function view($VIEW_FILE_NAME,array $VIEW_DATA = array()) {
			// 模板名字
			define('VIEW_FILE', (pathinfo($VIEW_FILE_NAME, PATHINFO_EXTENSION) == '') ? "$VIEW_FILE_NAME.php" : $VIEW_FILE_NAME);
			// 视图模板文件夹地址
			define('VIEW_PATH',$this->getApplicationRoot() . '/view/');
			// 模板文件全部路径
			define('VIEW_FILE_PATH',VIEW_PATH . VIEW_FILE);
			// 是否存在
			if ( ! file_exists(VIEW_FILE_PATH)) {
				throw new Acp_error('视图模板文件 - '. VIEW_FILE_PATH . ' 不存在!', Acp_error::PARAM);
			}
			// 将关联数组中的元素释放出来。
			extract($VIEW_DATA);
			// 输出模板
			if ((bool)@ini_get('short_open_tag') === false) {
				echo eval('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents(VIEW_FILE_PATH))));
			} else {
				include VIEW_FILE_PATH;
			}
		}
	}