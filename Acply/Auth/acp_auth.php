<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_auth
     *
     *验证用户是否拥有控制器的使用权限
     */
    /**
     * 用户权限验证类
     *
     * 暂时还有一个bug，可能会一直存在。
     * 当使用了all后再申明有，申明无，此权限结果是无。
     * 比如 all+a-a 最终无此a权限
     * 使用deny如此，没有bug。
     */
class Acp_auth extends Acp_base
{
    private static $control = array();
    private static $action = array();
    private static $c_all = array();
    private static $a_all = array();
    private static $position = null;
    // 解析配置字符串
    private static function parse($position)
    {
        $per = Acp_config::getConfig()->users;
        $per = trim($per[$position]);
        if ($per !== '') {
            // 清除除换行符外且在+、-、->三个符号周围的所有空格
            $preg = array('/[ \t\x0B\f]+(?=\+|\-|(\->))/', '/(?<=\+|\-|(\->))[ \t\x0B\f]+/');
            $per = preg_replace($preg, '', strtolower($per));
            // 取出每一个控制器至动作的权限申明
            $qx = preg_split('/\s+/', $per);
            self::$control = array();
            self::$action = array();
            self::$c_all = array();
            self::$a_all = array();
            $i = 0;
            foreach ($qx as $value) { // 两层循环中都用了$value变量
                $jg = explode('->', trim($value));
                if (count($jg) === 2) {
                    // 控制器解析，必须先解析+再解析-，
                    // 也就是这里导致有个小BUG，覆盖的原因。
                    // 匹配申明有权限的控制器
                    preg_match_all('/(?<=\+)\w+/', trim($jg[0]), $ctl);
                    foreach ($ctl[0] as $value) {
                        self::$control[$i][$value] = true;
                    }
                    // 匹配申明无权限的控制器
                    preg_match_all('/(?<=\-)\w+/', trim($jg[0]), $ctl);
                    foreach ($ctl[0] as $value) {
                        self::$control[$i][$value] = false;
                    }
                    // 匹配控制器的all和deny申明
                    preg_match_all('/^\w+/', trim($jg[0]), $ctl);
                    if (count($ctl[0]) > 0) { // 或许有
                        foreach ($ctl[0] as $value) {
                            if ($value === 'all') {
                                self::$c_all[$i] = 1;
                            } elseif ($value === 'deny') {
                                self::$c_all[$i] = 0;
                            } else {
                                self::$c_all[$i] = -1;
                                self::$control[$i][$value] = true;
                            }
                        }
                    } else { // 肯定没有all或deny
                        self::$c_all[$i] = -1;
                    }
                    // 只有all或deny的申明
                    if (!isset(self::$control[$i])) {
                        self::$control[$i] = null;
                    }
                    // 动作解析
                    // 所有规则同上
                    preg_match_all('/(?<=\+)\w+/', trim($jg[1]), $ctl);
                    foreach ($ctl[0] as $value) {
                        self::$action[$i][$value] = true;
                    }
                    preg_match_all('/(?<=\-)\w+/', trim($jg[1]), $ctl);
                    foreach ($ctl[0] as $value) {
                        self::$action[$i][$value] = false;
                    }
                    preg_match_all('/^\w+/', trim($jg[1]), $ctl);
                    if (count($ctl[0]) > 0) {
                        foreach ($ctl[0] as $value) {
                            if ($value === 'all') {
                                self::$a_all[$i] = 1;
                            } elseif ($value === 'deny') {
                                self::$a_all[$i] = 0;
                            } else {
                                self::$a_all[$i] = -1;
                                self::$action[$i][$value] = true;
                            }
                        }
                    } else {
                        self::$a_all[$i] = -1;
                    }
                    if (!isset(self::$action[$i])) {
                        self::$action[$i] = null;
                    }
                    $i++;
                } else {
                    throw new Acp_error("解析权限配置出现错误，$position 下  $value 错误！", Acp_error::PROGRAM);
                }
            }
        }
    }
    public static function check($position, $ctl, $atn = null)
    {
        // $atn为null说明只检查控制器权限，为string值检查这一个的动作权限，为array检查一群动作权限
        if (is_string($ctl) && is_string($position)) {
            // 解析权限配置
            if (self::$position !== $position) {
                self::parse($position);
                self::$position = $position;
            }
            $ctl = strtolower($ctl);
            if (is_string($atn)) {
                $atn = strtolower($atn);
            }
        } else {
            throw new Acp_error("检查权限输入参数类型错误", Acp_error::PARAM);
        }
        // 检测有无此控制器权限
        for ($i = count(self::$control) - 1; $i >= 0; $i--) {
            switch (self::$c_all[$i]) {
                case 1: // 申明了all
                    // 同时申明无此控制器权限,必须用===判断，下同
                    if (isset(self::$control[$i][$ctl]) && self::$control[$i][$ctl] === false) {
                        $ctl_status = false;
                        // 申明有此权限或无申明，都是有了权限
                    } else {
                        $ctl_status = true;
                    }
                    break;
                case 0: // 申明了deny
                    // 同时申明有此权限
                    if (isset(self::$control[$i][$ctl]) && self::$control[$i][$ctl] === true) {
                        $ctl_status = true;
                        // 申明无权限或者无申明，都是无权限
                    } else {
                        $ctl_status = false;
                    }
                    break;
                case - 1: // 没有申明all或deny
                    // 没有申明此控制器，通过检测上一个申明来找到结果
                    if (!isset(self::$control[$i][$ctl])) {
                        continue;
                        // 申明有此控制器
                    } elseif (self::$control[$i][$ctl] === true) {
                        $ctl_status = true;
                        // 申明无此控制器
                    } else {
                        $ctl_status = false;
                    }
                    break;
                default:
                    throw new Acp_error("检查权限时出现逻辑错误 - " . self::$c_all[$i], Acp_error::PROGRAM);
            }
            // 检测到了权限申明结果，不再检测，跳出检测
            if (isset($ctl_status)) {
                break;
            }
        }
        if (!isset($ctl_status) || $ctl_status === false) {
            return false;
        }
        // 说明只检查控制器权限
        if ($atn === null) {
            return true;
        }
        if (is_string($atn)) {
            $atns = array($atn);
        } else {
            $atns = $atn;
        }
        // $i记住第几个申明有了此控制器的权限。
        // 检测有无此动作的权限，规则同上。
        // 唯一不同是从控制器申明有权限处检测动作权限
        $atn_status = '';
        $result = array();
        foreach ($atns as $atn) {
            unset($atn_status);
            switch (self::$a_all[$i]) {
                case 1:
                    if (isset(self::$action[$i][$atn]) && self::$action[$i][$atn] === false) {
                        $atn_status = false;
                    } else {
                        $atn_status = true;
                    }
                    break;
                case 0:
                    if (isset(self::$action[$i][$atn]) && self::$action[$i][$atn] === true) {
                        $atn_status = true;
                    } else {
                        $atn_status = false;
                    }
                    break;
                case - 1:
                    if (isset(self::$action[$i][$atn]) && self::$action[$i][$atn] === true) {
                        $atn_status = true;
                    } else {
                        $atn_status = false;
                    }
                    break;
                default:
                    throw new Acp_error("检查权限时出现逻辑错误 - " . self::$c_all[$i], Acp_error::PROGRAM);
            }
            if (isset($atn_status) && $atn_status === true) {
                $result[$atn] = true;
            }
        }
        if (count($atns) === 1) {
            return !!count($result);
        } else {
            return $result;
        }
    }
    public static function checkAuth(array $ctl, $position)
    {
        $result = array();
        foreach ($ctl as $key => $value) {
            if (self::check($position, $key)) {
                $result[$key] = array();
                if (is_array($value)) {
                    $rst = self::check($position, $key, $value);
                    $result[$key] = (count($rst) === 0 ? true : $rst);
                } else {
                    throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
                }
            }
        }
        return $result;
    }
}
