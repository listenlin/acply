<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_base
     *
     *作为框架的基础抽象类存在
     */
    /**
     * 框架基础类
     *
     * @abstract
     *
     */
abstract class Acp_base
{
    public function __construct()
    {
    }
    public function __destruct()
    {
    }
    public function __call($n, $v)
    {
        throw new Acp_error("找不到类" . get_class($this) . "的方法$n", Acp_error::PROGRAM);
    }
}
