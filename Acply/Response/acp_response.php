<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_response
	 *
	 *对用户应答的封装
	 */
	/**
	 * 封装处理给用户返回的信息
	 */
	class Acp_response extends Acp_base {
		private static $ctt = '';
		private static $url = '';
		private static $srt = '';
		private static $time = -1;

		/**
		 * 通过返回JSON数据来回馈前端。
		 * 
		 * @param boolean $return
		 *        	是否返回数据而不发送给用户，默认为发送出去。
		 * @return string 返回JSON格式的字符串
		 */
		static public function echoJSON($return = false) {
			$json = Acp_parse_json::encodeJSON(array(
				'status' => self::$time, // 状态码
				'describe' => self::$url, // 状态码描述信息
				'extra' => self::$srt, // 附加信息
				'data' => self::$ctt // 主体内容信息
			));
			// 保证IE下不会弹出窗口来下载此内容
			header('Content-Type:text/plain');
			if ($return) {
				return $json;
			} else {
				echo $json;
			}
		}
		/**
		 * 通过返回HTML页面来回馈前端。
		 * 
		 * @param boolean $return
		 *        	是否返回数据而不发送给用户，默认为发送出去。
		 * @return void string
		 */
		static public function echoHTML($return = false) {
			
			if(stripos(self::$url, '/') === 0){
			
			}elseif(stripos(self::$url, 'http') === 0){
				
			}else{
				self::$url = $GLOBALS['whole']['url_root'] . self::$url;
			}
			
			if (self::$ctt == '' && self::$srt == '' && self::$time < 0) {
				if (!headers_sent()) {
					header('Location:' . self::$url);
					return;
				}
			}
			if (self::$ctt == '' && self::$srt == '' && self::$time >= 0) {
				$u = self::$url;
				$t = self::$time;
				$html = "<meta http-equiv=\"Refresh\" content=\"$t;url=$u\" />";
				if (!!$return) {
					return $html;
				} else {
					echo $html;
					return;
				}
			}
			$t = self::$time;
			$u = self::$url;
			$h = self::$ctt;
			if ($h != '' && $t == -1) {
				$a = '';
			} else {
				$a = "<a href='$u'>如果 $t 秒后未跳转，请点击此处！</a>";
			}
			$t *= 1000;
			if (self::$srt != '') {
				if ($t >= 0) {
					$s = self::$srt . ";setTimeout(function(){self.location.href='$u'},$t);";
				} else {
					$s = self::$srt;
				}
				$t = -1;
			} else {
				$s = '';
				$t /= 1000;
			}
			$html = <<< DATA
			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<meta http-equiv="Refresh" content="$t;url=$u" />
					<title>信息提示</title>
					<style type="text/css">
						body { width:100%; }
						div { width:80%; margin:100px auto; }
						h1 { text-align:center; color:red; font-size:22px; }
						a { display:block; text-align:center; font-size:14px; }
					</style>
				</head>
				<body>
					<div>
						<h1>$h</h1>
						$a
					</div>
				</body>
				<script type="text/javascript">
				document.onkeydown = function(e) {
					if (e.keyCode === 32) {
						window.location.href = document.getElementsByTagName('a')[0].href;
					}
				}
				$s
				</script>
			</html>
DATA;
			if (!!$return) {
				return $html;
			} else {
				echo $html;
			}
		}
		/**
		 * 返回给用户的初期设置。<br>
		 * 当返回HTML时，第一个参数为需要跳转的URL地址，第二个参数为用户等待跳转时间。<br>
		 * 当返回JSON时，第一个参数为对第二个参数状态码的简单描述。
		 * 
		 * @param string $dir
		 *        	URL或者状态描述
		 * @param int $time
		 *        	等待时间或者状态码
		 */
		static public function start($dir, $time = -1) {
			self::$ctt = '';
			self::$url = '';
			self::$srt = '';
			self::$time = -1;

			self::$url = $dir;
			self::$time = $time;
		}
		/**
		 * 返回给用户查看的信息字符串<br>
		 * 当返回HTML时，为展示的主题信息<br>
		 * 当返回JSON数据时，为返回的主体数据
		 * 
		 * @param string $c
		 *        	展示数据
		 */
		static public function show($c) {
			self::$ctt = $c;
		}
		/**
		 * 返回后，需要用户做的事。<br>
		 * 当返回为HTML时，通常为一个JS脚本。<br>
		 * 当返回为JSON时，为给用户的附加信息，通常为希望用户做的事
		 * 
		 * @param string $d
		 *        	控制信息
		 */
		static public function doing($d) {
			self::$srt = $d;
		}
		/**
		 * 输出所有信息，并停止运行脚本
		 */
		static public function end() {
			$cfg = Acp_config::getConfig();
			if (strtolower($cfg->echoType) === 'html') {
				self::echoHTML();
			} elseif (strtolower($cfg->echoType) === 'json') {
				self::echoJSON();
			} else {
				self::echoHTML();
			}
			exit();
		}

		
		/**
		 * 回应信息提示
		 * @param  string  $msg  提示信息
		 * @param  string  $url  跳转到的地址, 默认调转到当前控制的默认动作
		 * @param  integer $time 延迟时间
		 * @return void
		 */
		static public function response_show($msg, $url = NULL, $time = 2) {
			$url = empty($url) ? $GLOBALS['whole']['accept']->getCmd(0) . '/' . $GLOBALS['whole']['config']->default_action : $url;
			self::start($url, $time);
			self::show($msg);
			self::end();
		}

		/**
		 * 重定向
		 * @param  string $url 定向到
		 * @return void
		 */
		public static function redirect($url)
		{
			header("location: $url");
			exit;
		}
	}
