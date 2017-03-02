<?php
    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_util
     *
     *框架公用辅助方法
     */
class Acp_util extends Acp_base
{
    /**
         * 形成一个随机的字符串包含小写字母和数字
         * @return string
         */
    public static function randStr()
    {
        /**
             * mcrypt_create_iv() 响应慢，资料：http://www.laruence.com/2012/09/24/2810.html
             */
        return md5((microtime(true) + mt_rand()) . base64_encode(mcrypt_create_iv(10, MCRYPT_DEV_URANDOM)));
    }

    /**
         * 生成唯一字符串
         * @param  integer $len 生成字符串的长度
         * @return string
         */
    public static function generateUniqueString($length = 32)
    {
        if (extension_loaded('openssl')) {
            $bytes = openssl_random_pseudo_bytes($length, $cryptoStrong);
        } else {
            $bytes = null;
            for ($i = 0; $i < $length; $i++) {
                $bytes .= chr(mt_rand());
            }
        }

        return strtr(substr(base64_encode(sha1($bytes . microtime(true))), 0, $length), '+/', '_-');
    }


    /**
         * 生成验证码的图像流至浏览器
         * @example
         * Acp_util::createValidateCode(18,6,true);//生成的验证码图中验证码18个像素大，有6位数，有背景图混淆,更难识别<br>
         * Acp_util::createValidateCode();//默认15个像素大，4位数，没有背景图
         * @param int $fs 验证码字体大小
         * @param int $len 验证码的位数
         * @param boolean $uimg 是否添加背景图
         * @return string|boolean 随机生成的验证码，生成失败返回false
         */
    public static function createValidateCode($fs = 15, $len = 4, $uimg = false)
    {
        $tpv = new Acp_util_Tp_Verify();
        $tpv->fontSize = $fs;
        $tpv->length = $len;
        $tpv->useImgBg = $uimg;
        return $tpv->entry();
    }
    /**
         * 从sql文件中获取SQL语句，返回每条SQL语句组成的数组
         *
         * @param string $dir SQL文件地址
         * @throws Acp_error
         * @return array SQL语句数组
         */
    public static function getFileSQL($dir)
    {
        if (file_exists($dir)) {
            $preg = array(
                '/\/\*.*\*\//Us', // 匹配多行/**/式注释
                '/^\s*/m', // 匹配从一行开始的空白字符
                '/^-{2}.*$/m', // 匹配从一行开始的--注释
                '/-{2}.*$/m', // 匹配从任意字符开始，换行结束的--注释
                '/\\r*\\n*/'); // 匹配换行符
            $sql = explode(';', preg_replace($preg, '', file_get_contents($dir)));
            if (trim($sql[count($sql) - 1]) == '') {
                unset($sql[count($sql) - 1]);
            }
            return $sql;
        } else {
            throw new Acp_error('找不到sql文件地址-' . $dir, Acp_error::PARAM);
        }
    }
    /**
         * 返回当前时间的一般格式字符串 “年-月-日 时:分:秒”
         *
         * @return string
         */
    public static function getCurrentTime()
    {
        return date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
    }
    /**
         * 返回可以给插入数据库用的当前时间
         * @return string
         */
    public static function getDbTime()
    {
        return date('YmdHis');
    }
    /**
         * 给用户发送404未找到错误
         */
    public static function notFound()
    {
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found');
            header('Status:404 Not Found');
            echo file_get_contents($GLOBALS['RESOURCE_DIR'] . 'Error/404.html');
            // echo 'HTTP/1.1 404 Not Found';
        } else {
            echo 'HTTP/1.1 404 Not Found';
        }
        exit;
    }
    /**
         * 将本地的路径转换为URL地址
         * @example
         * 网站根目录为d:/www,域名为www.abc.com<br>
         * echo Acp_util::localToUrl('d:/www/sf/aff/fg.html');<br>.
         * 输出http://www.abc.com/sf/aff/fg.html<br>
         * echo Acp_util::localToUrl('d:/www/ss/fsdf','www.baidu.com');<br>
         * 输出www.baidu.com/ss/fsdf
         * @param string $dir 需要转换的本地地址
         * @param string $prefix URL中的域名,输入的话转换为此域名下的URL地址，否则转换为框架所处服务器的域名或IP
         * @return string 转换后的URL字符串
         */
    public static function localToUrl($dir, $prefix = null)
    {
        if (empty($dir)) {
            return '';
        } elseif (is_array($dir)) {
            $dir = empty($dir['dir']) ? '' : $dir['dir'];
        }
        if (empty($prefix)) {
            $prefix = strtolower(Acp_config::getConfig()->channelPath);
        }

        return strtr(strtolower($dir), array($prefix => 'https://www.tianyanar.com'));
    }

    public static function replaceToUrl($string)
    {
        $config = Acp_config::getConfig();
        return strtr(strtolower($string), array(strtolower($config->channelPath) => 'https://www.tianyanar.com'));
    }


    /**
         * 将URL转换为本地绝对路径
         * @param string $dir
         * @return string
         */
    public static function urlToLocal($dir, $prefix = null)
    {
        if (!$dir) {
            return '';
        }
        if (!$prefix) {
            $prefix = 'http://' . $_SERVER['SERVER_NAME'];
        } elseif ($prefix instanceof Smarty_Internal_Template) {
            $prefix = 'http://' . $_SERVER['SERVER_NAME'];
        }
        if (stripos($prefix, 'http') !== 0) {
            $prefix = "http://$prefix";
        }
        return strtr($dir, array($prefix => $_SERVER['DOCUMENT_ROOT']));
    }
    /**
         * 将本地路径转换成https的URL
         * @param string $dir
         * @param string $prefix
         * @return string
         */
    public static function localToSslUrl($dir, $prefix = null)
    {
        if (!$dir) {
            return '';
        }
        if (is_array($dir)) {
            $dir = empty($dir['dir']) ? '' : $dir['dir'];
        }
        if (!$prefix) {
            $prefix = 'https://' . $_SERVER['SERVER_NAME'];
        } elseif ($prefix instanceof Smarty_Internal_Template) {
            $prefix = 'https://' . $_SERVER['SERVER_NAME'];
        }
        return strtr(strtolower($dir), array(strtolower(strtr($_SERVER['DOCUMENT_ROOT'], '\\', '/')) => $prefix));
    }
    /**
         * 把https的url转换成本地绝对路径
         * @param string $dir
         * @return string
         */
    public static function sslUrlToLocal($dir, $prefix = null)
    {
        if (!$dir) {
            return '';
        }
        if (!$prefix) {
            $prefix = 'https://' . $_SERVER['SERVER_NAME'];
        } elseif ($prefix instanceof Smarty_Internal_Template) {
            $prefix = 'https://' . $_SERVER['SERVER_NAME'];
        }
        if (stripos($prefix, 'https') !== 0) {
            $prefix = "https://$prefix";
        }
        return strtr($dir, array($prefix => $_SERVER['DOCUMENT_ROOT']));
    }

    /**
         * 十六进制转 RGB
         * @param string $hexColor 十六颜色 ,如：#ff00ff
         * @return array RGB数组
         */
    public static function hColor2RGB($hexColor)
    {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = str_replace('#', '', $hexColor);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }

    /**
         * RGB转 十六进制
         * @param $rgb RGB颜色的字符串 如：rgb(255,255,255);
         * @return string 十六进制颜色值 如：#FFFFFF
         */
    public static function RGBToHex($r, $g, $b)
    {
        if ($r < 0 || $g < 0 || $b < 0 || $r > 255 || $g > 255|| $b > 255) {
            return false;
        }
        return "#".(substr("00".dechex($r), -2)).(substr("00".dechex($g), -2)).(substr("00".dechex($b), -2));
    }

    /**
         * 获取请求IP地址
         *
         * @return string
         */
    public static function request_ip()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }

        return $ip;
    }

    /**
         * 在字符串前面加 http://
         * @param  [type] $str [description]
         * @return [type]      [description]
         */
    public static function toUrl($str, $suffix = 'http://')
    {
        if (!preg_match('/^https?:\/\//i', $str)) {
            $str = $suffix . $str;
        }
        return $str;
    }

    /**
         * 判断字符串是不是一个URL地址
         *
         * @param  string  $str 字符串
         * @return boolean
         */
    public static function isUrl($str)
    {
        if (!$str) {
            return false;
        }

        return preg_match('/^(https?:\/\/)?([\w-\d]+\.?)+[\w-\d]+([\/\d\w- \.\?%&=]*)?/i', $str);
    }

    /**
         * 设置为下载头
         */
    public static function setDownloadHeaders($attachmentName, $mimeType, $contentLength)
    {
        // if (headers_sent()) {
        //     return;
        // }
        $statusCode = 200;
        $statusText = 'OK';
        $version = (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') ? '1.0' : '1.1';
        header("HTTP/{$version} $statusCode {$statusText}");

        $headers = array(
            'Pragma' => array('public'),
            'Accept-Ranges' => array('bytes'),
            'Expires' => array('0'),
            'Cache-Control' => array('must-revalidate, post-check=0, pre-check=0'),
            'Content-Disposition' => array("attachment; filename=\"$attachmentName\""),
            'Content-Type' => array($mimeType),
            'Content-Length' => array($contentLength),
        );
        foreach ($headers as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            // set replace for first occurrence of header but false afterwards to allow multiple
            $replace = true;
            foreach ($values as $value) {
                header("$name: $value", $replace);
                $replace = false;
            }
        }
    }
        
    public static function isMobile()
    {
            // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
            // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
            // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
            // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
            return false;
    }
}
