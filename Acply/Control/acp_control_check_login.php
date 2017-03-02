<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_control_check_login
     */
    /**
     * 此接口用以实现检测用户没有登录时的反应。如果继承了此接口，说明需要检测用户有无登录系统。
     * 登录与否，会调用相应的接口方法。
     */
interface Acp_control_check_login
{
    public function notLogin();
    public function logined();
}
