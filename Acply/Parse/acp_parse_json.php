<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_parse_json
     *解码和编码JSON数据的辅助类
     */
class Acp_parse_json extends Acp_base
{
    // 还是自己写一个编码JSON的函数算了。。。PHP自带的各种蛋疼。
    /**
         * 编码JSON函数
         *
         * 将输入的数组转换为JSON字符串数据
         * 数组为数字索引，编码后为数组型
         * 数组为键值对型，数组中有一个键值对认定为键值对型，编码后为对象型
         * 输入数字原样返回
         * 输入为字符串，会将"双引号转换为&quot，将\转换成\\
         * 数据编码不会改变，最大递归层为200层
         *
         * @access public
         * @param mixed $data 一般为数组型输入，也可以输入数字或字符串
         * @param int $num 函数递归时用到的层数
         * @return string JSON字符串数据
         */
    public static function encodeJSON($data, $num = 0)
    {
        if ($num === 200) {
            throw new Acp_error('编码JSON 到达了最大允许堆栈深度。', Acp_error::PROGRAM);
        }
        switch (true) {
            case is_array($data):
                $num++;
                $a = array();
                // 判断为索引数组，但是有BUG，比如array(null,'d'=>0,2=>'sa')也会判断为索引
                $max = count($data) - 1;
                if ($max == -1 || isset($data[$max]) || array_key_exists($max, $data)) {
                    foreach ($data as $value) {
                        $a[] = self::encodeJSON($value, $num);
                    }
                    return '[' . implode(',', $a) . ']';
                } else {
                    foreach ($data as $key => $value) {
                        $a[] = "\"$key\":" . self::encodeJSON($value, $num);
                    }
                    return '{' . implode(',', $a) . '}';
                }
                break;
            case is_bool($data):
                return $data === true ? 'true' : 'false';
                break;
            case is_null($data):
                return 'null';
                break;
            case is_string($data):
                return '"' . strtr($data, array('"' => '&quot;', '\\' => <<< TS
\\\
TS
                    )) . '"';
                break;
            case is_numeric($data):
                return $data;
                break;
            case is_object($data):
                $num++;
                $a = array();
                foreach ($data as $key => $value) {
                    $a[] = "\"$key\":" . self::encodeJSON($value, $num);
                }
                return '{' . implode(',', $a) . '}';
                break;
            default:
                throw new Acp_error('编码JSON时输入的数据类型不支持!', Acp_error::PARAM);
        }
    }
    // 解码JSON数据
    /**
         * 将JSON字符串解码成对象或者数组
         *
         * @param string $data
         *          待解码的JSON字符串
         * @param boolean $assoc
         *          为true返回数组，false返回对象，默认为false
         * @return mixed 返回类型根据第二个输入参数确定
         */
    public static function decodeJSON($data, $assoc = false)
    {
        $re = json_decode($data . '', $assoc);
        if ($re === null) {
            if (is_string($data)) {
                return $data;
            } else {
                throw new Acp_error('JSON 解码时发生错误(常量值【整型】) - ' . json_last_error(), Acp_error::FUNC);
            }
        }
        return $re;
    }
}
