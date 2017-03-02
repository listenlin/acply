<?php
    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_plugin_container
     *具体某类插件的包含者
     */
class Acp_plugin_container extends Acp_base
{
    /**
         * 插件所处的目录
         * @var string
         */
    private $pluginPath = '';
    /**
         * 对某个插件下面类对象的缓存
         * @param array Acp_lugin
         */
    private $pluginCache = array();
    /**
         * 是否实例化一个无功能的空对象。
         * @var boolean
         */
    private $isNull = true;
    public function __construct()
    {
        if (func_num_args() == 1) {
            $this->isNull = false;
            $path = Acp_config::getConfig()->plugin;
            $pluginPath = $GLOBALS['whole']['app_root'] . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . func_get_arg(0);
            if (is_dir($pluginPath)) {
                $this->pluginPath = $pluginPath;
            } else {
                $this->pluginPath = $path;
            }
        }
    }
    public function __destruct()
    {
        $this->pluginCache = null;
        $this->pluginPath = null;
    }
    /**
         * 视图钩子
         */
    public function __call($name, $VIEW_DATA = array())
    {
        if (! $this->isNull) {
            if (! is_array($VIEW_DATA)) {
                $VIEW_DATA = array($VIEW_DATA);
            }
            extract($VIEW_DATA);
            include $this->pluginPath . DIRECTORY_SEPARATOR . strtolower("$name.php");
        }
    }
    /**
         * 获取某个插件的类的对象，视作动作钩子
         * @param string $name
         * @throws Acp_error
         * @return multitype:|Acp_plugin
         */
    public function __get($name)
    {
        if (isset($this->pluginCache[$name])) {
            return $this->pluginCache[$name];
        } else {
            if ($this->isNull) {
                return $this->pluginCache[$name] = new Acp_plugin();
            }
            $pluginFile = $this->pluginPath . DIRECTORY_SEPARATOR . strtolower("$name.php");
            // 在某个插件目录中，存在此具体的插件文件。
            if (file_exists($pluginFile)) {
                include_once $pluginFile;
                // 存在类，说明是动作的钩子
                if (class_exists($name)) {
                    $obj = new $name();
                    if (! ($obj instanceof Acp_plugin)) {
                        throw new Acp_error("应用插件类 - $name 没有继承自Acp_plugin", Acp_error::PROGRAM);
                    }
                } else {
                    throw new Acp_error("文件 $pluginFile 不存在插件类 $name ！", Acp_error::PROGRAM);
                }
                // 否则返回一个空白对象。其执行相应方法为空。
            } else {
                $obj = new Acp_plugin();
            }
            return $this->pluginCache[$name] = $obj;
        }
    }
}
