<?php
/**
 *@copyright Copyright 2014 listenlin. All rights reserved.
 *@author listenlin <listenlin521@foxmail.com>
 *@version 1.0
 *@package Acply\Acp_hook
 *框架应用的钩子父类。
 */
class Acp_hook extends Acp_base
{
    /**
     * 覆盖框架基类的方法调用的魔术方法，既然不存在，就让其什么也不做。
     * 主要是为了让钩子的某个方法没有时，不用做钩子是否存在的判断。
     */
    public function __call($name, $value)
    {
    }
}
