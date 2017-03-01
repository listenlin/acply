<?php
/**
 *@copyright Copyright 2014 listenlin. All rights reserved.
 *@author listenlin <listenlin521@foxmail.com>
 *@version 1.0
 *@package Acply\Acp_plugin_manager
 *对应用的插件的管理者
 */
class Acp_plugin_manager extends Acp_base{
	static private $manage = NULL;
	/**
	 * 返回插件的管理器单例对象
	 * @return Acp_plugin_manager
	 */
	static public function getManager(){
		if(self::$manage === NULL){
			self::$manage = new self();
		}
		return self::$manage;
	}
	/**
	 * 插件的数组
	 * @var array
	 */
	private $pluginContainer = array();
	/**
	 * 决定当前应用是否使用插件，以及使用哪一个插件
	 * @param mixed $pluginName 可以输入一个数组，启用好几个插件。也可以输入一个字符串，只启用一个插件。
	 */
	public function usePlugin($pluginName){
		if(empty($pluginName)){
			return;
		}
		if(is_array($pluginName)){
			foreach ($pluginName as $value){
				$this->pluginContainer[$value] = new Acp_plugin_container($value);
			}
		}else{
			$pluginName .= '';
			$this->pluginContainer[$pluginName] = new Acp_plugin_container($pluginName);
		}
	}
	/**
	 * 获取某个插件
	 * @return Acp_plugin_container
	 */
	public function __get($pluginName){
		if( ! isset($this->pluginContainer[$pluginName])){
			$this->pluginContainer[$pluginName] = new Acp_plugin_container();
		}
		return $this->pluginContainer[$pluginName];
	}
}