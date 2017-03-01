<?php
	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_hook_container
	 *某一类钩子的包含容器类
	 */
	class Acp_hook_container extends Acp_base{
		/**
		 * 某个钩子的名称
		 */
		private $hookName = '';
		/**
		 * 某一类钩子的路径组成的数组
		 */
		private $hookPaths = array();
		/**
		 * 对某个钩子在所有容器下的对象的缓存
		 * @param array Acp_hook
		 */
		private $hookObjectCache = array();
		
		public function __construct($hookName , array $hookPaths){
			$this->hookName = $hookName;
			$this->hookPaths = $hookPaths;
		}
		public function __destruct(){
			$this->hookObjectCache = NULL;
			$this->hookPaths = NULL;
		}
		/**
		 * 动作钩子
		 */
		public function __call($name,$value){
			$result = NULL;
			$passIndex = count($value[0]);
			foreach ($this->hookPaths as $path){
				if( ! isset($this->hookObjectCache[$path])){
					$hookFile = $path . DIRECTORY_SEPARATOR . strtolower($this->hookName.'.php');
					// 在某个插件目录中，存在此具体的插件文件。
					if(file_exists($hookFile)){
						include_once $hookFile;
						$hookClass = $this->hookName;
						// 不存在类，说明有问题
						if( ! class_exists($hookClass)){
							throw new Acp_error("文件 $hookFile 不存在钩子类 $hookClass ！", Acp_error::PROGRAM);
						}
						$obj = new $hookClass();
						if( ! ($obj instanceof Acp_hook) ){
							throw new Acp_error("应用钩子类 - $hookClass 没有继承自Acp_hook", Acp_error::PROGRAM);
						}
						$this->hookObjectCache[$path] = $obj;
					}else{
						continue;
					}
				}
				$value[0][$passIndex] = $result;
				$result = call_user_func_array(array($this->hookObjectCache[$path], $name), $value[0]);
			}
			return $result;
		}
	}