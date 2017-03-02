<?php

/**
 * 扩展 PHP 内置的异常处理类
 *
 * @copyright Copyright 2014 listenlin. All rights reserved.
 * @author listenlin <listenlin521@foxmail.com>
 * @version 1.0
 * @package Acply\Acp_error
 */
class Acp_error extends Exception
{
    // 出错类型自定义常量
    const PROGRAM = 0; // 程序逻辑或设置出现错误，需立即更改！！
    const AUTHORITY = 1; // 用户权限错误，有人试图解析攻击后台！！
    const PARAM = 2; // 使用框架时，程序猿输入的函数参数不符合要求
    const DB = 3; // 数据库错误
    const INPUT = 4; // 用户输入信息不符合要求
    const FUNC = 5; // PHP自带函数执行出现错误。
    private $txt = array(
        'program',
        'authority',
        'parameter',
        'database',
        'user_input',
        'function');
    private $time; // 出错的时间
    private $type; // 出错的自定义类型

    public function __construct($message, $type, $code = 0)
    {
        $this->time = date('Y-m-d H:i:s');
        $this->type = $type;

        // 确保所有变量都被正确赋值
        parent::__construct($message, $code);

        $msg = array(
            "时间: {$this->time}",
            "错误描述: $message",
            "信息: {$this->file}({$this->line})",
            "异常点调用栈：".$this->getTraceAsString(),
        );
        Acp_log::log($this->txt[$type], $msg);
    }
    // 自定义字符串输出的样式
    public function __toString()
    {
        return __class__ . ": [{$this->code}]: {$this->message}\n";
    }
    public function getType()
    {
        return $this->type;
    }
    public static function errorHandle($e)
    {
        $cfg = Acp_config::getConfig();
        if ($cfg->debug === true) {
            $show = $e->getMessage();
            Acp_response::start('');
        } else {
            $show = '抱歉，出现错误，请重试。';
        }
        switch (true) {
            case $e instanceof Acp_error:
                switch ($e->getType()) {
                    case Acp_error::PROGRAM:
                        Acp_response::show($show);
                        break;
                    case Acp_error::AUTHORITY:
                        Acp_response::show('您没有权限进行此项操作！');
                        break;
                    case Acp_error::PARAM:
                        Acp_response::show($show);
                        break;
                    case Acp_error::DB:
                        Acp_response::show($show);
                        break;
                    case Acp_error::INPUT:
                        Acp_response::show($e->getMessage());
                        break;
                    case Acp_error::FUNC:
                        Acp_response::show($show);
                        break;
                    default:
                        Acp_response::show($show);
                        break;
                }
                break;
            case $e instanceof PDOException:
                Acp_response::show($show);
                Acp_log::log('PDOException', Acp_user::getName(), date('Y-m-d H:i:s'), $e->file, $e->line, $e->getMessage(), $e->getTraceAsString());
                break;
            case $e instanceof Exception:
                Acp_response::show($show);
                Acp_log::log('Exception', Acp_user::getName(), date('Y-m-d H:i:s'), $e->file, $e->line, $e->getMessage(), $e->getTraceAsString());
                break;
            default:
                Acp_response::show($show);
                Acp_log::log('otherException', Acp_user::getName(), date('Y-m-d H:i:s'), $e->file, $e->line, $e->getMessage(), $e->getTraceAsString());
                break;
        }
        Acp_response::end();
    }
    public static function phpErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        // $cfg = Acp_config::getConfig();
        // if ($cfg->debug === true) {
        //     var_dump(func_get_args());
        // } else {
            Acp_log::log('phpRunError', array(
                '时间: ' . date('Y-m-d H:i:s'),
                '用户: ' . Acp_user::getName(),
                '错误级别: ' . $errno,
                "错误文件: $errfile($errline)",
                '错误描述: ' . $errstr,
            ));
        // }
        return false;
    }
    public static function shutdown()
    {
    }
}
