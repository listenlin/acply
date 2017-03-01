<?php
/**
 *@copyright Copyright 2014 listenlin. All rights reserved.
 *@author listenlin <listenlin521@foxmail.com>
 *@version 1.0
 *@package Acply\Acp_hook_manager
 *对应用的所有钩子的管理者
 */
class Acp_hook_manager extends Acp_base{
	static private $manage = NULL;
	/**
	 * 返回插件的管理器单例对象
	 * @return Acp_hook_manager
	 */
	static public function getManager(){
		if(self::$manage === NULL){
			self::$manage = new self();
		}
		return self::$manage;
	}
	/**
	 * 钩子所处的目录
	 * @var string
	 */
	private $hookPath = '';
	/**
	 * 钩子所处文件夹路径的数组，有哪些文件夹就会使用哪些文件夹下的钩子。
	 * @var array
	 */
	private $hookContainerPath = array();
	/**
	 * 对某个钩子集合类对象的缓存
	 * @param array
	 */
	private $containerCache = array();
	
	private $hasNewHook = FALSE;
	
	public function __construct(){
		$path = Acp_config::getConfig()->hook;
		$hookPath = $GLOBALS['whole']['app_root'] . DIRECTORY_SEPARATOR . $path;
		if( is_dir ( $hookPath )){
			$this->hookPath = $hookPath;
		}else{
			$this->hookPath = $path;
		}
	}
	/**
	 * 决定当前应用是否使用钩子，以及使用哪个目录下的钩子
	 * @param mixed $hookName 可以输入一个数组，启用好几个钩子。也可以输入一个字符串，只启用一个钩子。
	 */
	public function registerHook($hookName){
		if(empty($hookName)){
			return;
		}
		if(is_array($hookName)){
			foreach ($hookName as $value){
				$this->hookContainerPath[] = $this->hookPath . DIRECTORY_SEPARATOR . "$value";
			}
		}else{
			$this->hookContainerPath[] = $this->hookPath . DIRECTORY_SEPARATOR . "$hookName";
		}
		$this->hasNewHook = TRUE;
	}
	/**
	 * 视图钩子
	 */
	public function __call($name,$VIEW_DATA){
		if( ! isset($VIEW_DATA[0])){
			$VIEW_DATA[0] = array();
		}else{
			$VIEW_DATA = $VIEW_DATA[0];
		}
		extract($VIEW_DATA);
		foreach ($this->hookContainerPath as $path){
			$VIEW_FILE_PATH = $path . DIRECTORY_SEPARATOR . strtolower("$name.php");
			if(file_exists($VIEW_FILE_PATH)){
				include $VIEW_FILE_PATH;
			}
		}
	}
	/**
	 * 获取某个钩子的包含器
	 * @return Acp_hook_container
	 */
	public function __get($hookName){
		// 如果新注册了钩子容器，则要把以前的钩子容器刷新，以便新增的钩子容器能被搜索到。
		if($this->hasNewHook){
			foreach ($this->containerCache as $name){
				$this->containerCache[$name] = new Acp_hook_container($name,$this->hookContainerPath);
			}
			$this->hasNewHook = FALSE;
		}
		if(! isset($this->containerCache[$hookName])){
			$this->containerCache[$hookName] = new Acp_hook_container($hookName,$this->hookContainerPath);
		}
		return $this->containerCache[$hookName];
	}
}