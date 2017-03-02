<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_control_auth
     *
     *
     * 实现此接口的控制器，说明需要检测用户对控制器和动作的使用权限。必须返回代表用户身份的字符串，否则此接口无效。
     */
interface Acp_control_auth
{
    public function checkControlAuth();
}
