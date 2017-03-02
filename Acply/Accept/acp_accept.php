<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_accept
     *
     *对当前接收到的所有GET和POST信息进行封装和安全过滤等
     */
    /**
     * 处理（安全过滤）客户端传来的各种命令参数和信息，将其封装起来，留给后面使用。
     */
class Acp_accept extends Acp_base
{
    // 命令参数
    private $cmd = array();

    // 传来的信息
    private $information = array();

    public function __construct()
    {

        parent::__construct();

        // 默认路由
        $_route = array(
            $GLOBALS['whole']['config']->default_control,
            $GLOBALS['whole']['config']->default_action,
        );
        // 用户请求路由
        $_acceptRoute = array();
        if (!empty($_GET['acplyaccept'])) {
            $_acceptRoute = explode('/', trim(strtr($_GET['acplyaccept'], '\\', '/'), '/'));
        }

        // 使用用户请求的路由信息替换默认的
        $this->cmd = array_replace($_route, $_acceptRoute);

        // GET 参数过滤
        foreach ($_GET as $Kget => $Vget) {
            $this->cmd[$Kget] = htmlentities(strip_tags($Vget), ENT_NOQUOTES);
        }
            
        $this->information = $this->walk_array($_POST);
    }


    /**
         * 对POST数据进行过滤
         *
         * @param array $v POST数据
         * @return array 过滤后的POST数据
         */
    private function walk_array(array $v)
    {
        $info = array();
        foreach ($v as $Kpost => $Vpost) {
            if (is_array($Vpost)) {
                $info[$Kpost] = $this->walk_array($Vpost);
            } else {
                if (!is_numeric($Vpost) || settype($Vpost, 'string')) {
                    $info[$Kpost] = htmlentities(strip_tags($Vpost), ENT_QUOTES, 'UTF-8');
                }
            }
        }
        return $info;
    }


    /**
         * 返回框架处理后的GET值（URL重写后），可以通过0，1，2的索引来取得。
         *
         * @example 当入口文件在根目录下且url为/test/ac/as?sf=9989&f=sd时 <br>
         * echo getCmd(0);//test <br>
         * echo getCmd(1);//ac <br>
         * echo getCmd(2);//as <br>
         * echo getCmd('sf');//9989 <br>
         * echo getCmd('f');//sd <br>
         * echo getCmd();//获取一个数组，包含所有值。
         *
         * @param string|int $index
         *          索引值
         * @return mixed 返回GET数据
         */
    public function getCmd($index = null)
    {
        if ($index === null) {
            return $this->cmd;
        } else {
            return isset($this->cmd[$index]) ? $this->cmd[$index] : null;
        }
    }


    /**
         * 返回框架处理后的POST数据
         *
         * @example 表单为&lt;form method="post"&gt;&lt;input value="aa" name="name" /&gt;&lt;input type="password" name="pw" /&gt;&lt;/form&gt;<br>
         * echo getInfo('name');//aa <br>
         * echo getInfo('pw);//输入的密码 <br>
         * echo getInfo();//返回一个数组，包含所有POST值
         *
         * @param  string|integer $index     索引值
         * @param  bool           $trimSpace 是否删除两端空格
         * @return mixed 返回POST数据
         */
    public function getInfo($index = null, $trimSpace = false)
    {
        if ($index === null) {
            return $this->information;
        }

        if (!isset($this->information[$index])) {
            return null;
        }

        if ($trimSpace) {
            return trim($this->information[$index]);
        }

        return $this->information[$index];
    }
}
