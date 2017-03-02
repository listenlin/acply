<?php

/**
 * 日志记录类
 *
 * @copyright Copyright 2014 listenlin. All rights reserved.
 * @author listenlin <listenlin521@foxmail.com>
 * @version 1.0
 * @package Acply\Acp_log
 */
class Acp_log extends Acp_base
{
    private $where = '';
    private static $logid = null;
    public function __construct()
    {
        parent::__construct();

        // 用户自定义的日志目录
        $logPath = $GLOBALS['whole']['config']->log;

        // 设置了自定义完整的日志目录
        if (empty($logPath)) {
            $this->where = $GLOBALS['whole']['dir_root'] . '/logs';
        } else {
            $this->where = $logPath;
        }

        $this->where = strtr($this->where, '\\', '/');
        $this->where = rtrim($this->where, '/') . '/';

        if (!is_dir($this->where)) {
            mkdir($this->where);
        }
    }
    /**
     * 将一些信息记录到文本文件中去，或者记录到数据库中去
     * @example
     * Acp_log::log('abc','sadf','saa');//会将后面两个参数记录到abc.txt文件中去
     * Acp_log::log(new (dbModel extends Acp_table),'asd','faafa','afafa');
     * 第二种数据库记录方式，需要非主键以外字段个数和后续参数个数相等，才能完美记录到数据库中
     * @param string|Acp_table $dest 将要录入到的地方，文本文件或者数据库
     * @param 后面的参数个数不定，全为string类型
     * @return void
     */
    public static function log($dest, $arg)
    {
        $args = is_array($arg) ? $arg : array_slice(func_get_args(), 1);

        if (is_string($dest)) {
            if (self::$logid === null) {
                self::$logid = new Acp_log();
            }
            $content = Acp_parse_json::encodeJSON($args);
            // 去除换行符和回车符，以免在用file函数读取时出现错误。
            $content = preg_replace('/[\r\n]+/', ' ', $content);
            file_put_contents(self::$logid->where . date('Y-m-d') . ".$dest.log", $content . PHP_EOL, FILE_APPEND);
        } elseif ($dest instanceof Acp_table) {
            return $dest->fields(true)->value($args)->insert();
        } else {
            throw new Acp_error('记录日志时输入参数错误！', Acp_error::PARAM);
        }
    }

    /**
     * 读取日志文件
     * @param  string $file_path 文件路劲
     * @return array 日志信息
     */
    public static function read_log($file_path)
    {
        $return = array();
        if (file_exists($file_path)) {
            $file = file($file_path);

            foreach ($file as $fline) {
                $fline = trim($fline);
                if (strlen($fline) > 0) {
                    $return[] = json_decode($fline, true);
                }
            }

            krsort($return);
        }

        return $return;
    }

    /**
     * 日志信息模板
     * @param  miexd $log_msg 日志主要信息
     * @return miexd 组合后的日志信息
     */
    public static function log_template($log_msg)
    {
        if (!empty($log_msg)) {
            if (is_array($log_msg) || is_object(($log_msg))) {
                $log_msg = json_encode($log_msg);
            }

            $msg = array(
                'TIME: ' . date('Y-m-d H:i:s'),
                'REQUEST IP: ' . $_SERVER['REMOTE_ADDR'],
                'USER: ' . $GLOBALS['whole']['user']->getName(),
                'MESSAGE: ' . $log_msg
                );

            return $msg;
        }

        return false;
    }
}
