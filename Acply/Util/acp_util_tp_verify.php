<?php

	// +----------------------------------------------------------------------
	// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
	// +----------------------------------------------------------------------
	// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
	// +----------------------------------------------------------------------
	// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
	// +----------------------------------------------------------------------
	// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
	// +----------------------------------------------------------------------

	class Acp_util_Tp_Verify extends Acp_base {
		public $useImgBg = false; // 使用背景图片
		public $fontSize = 25; // 验证码字体大小(px)
		public $length = 4; // 验证码位数

		private $useCurve = false; // 是否画混淆曲线
		private $useNoise = false; // 是否添加杂点
		private $imageH = 0; // 验证码图片宽
		private $imageL = 0; // 验证码图片长
		private $fontttf = ''; // 验证码字体，不设置随机获取
		private $bg = array(
			255,
			255,
			255); // 背景

		/**
		 * 验证码中使用的字符，01IO容易混淆，建议不用
		 *
		 * @var string
		 */
		private $_codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
		private $_image = null; // 验证码图片实例
		private $_color = null; // 验证码字体颜色

		/**
		 * 输出验证码并把验证码的值保存的session中
		 * 验证码保存到session的格式为： array('code' => '验证码值', 'time' => '验证码创建时间');
		 */
		public function entry($code = null) {
			// 图片宽(px)
			$this->imageL || $this->imageL = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
			// 图片高(px)
			$this->imageH || $this->imageH = $this->fontSize * 2.5;
			// 建立一幅 $this->imageL x $this->imageH 的图像
			$this->_image = imagecreate($this->imageL, $this->imageH);
			// 设置背景
			imagecolorallocate($this->_image, $this->bg[0], $this->bg[1], $this->bg[2]);

			// 验证码字体随机颜色
			$this->_color = imagecolorallocate($this->_image, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
			// 验证码使用随机字体

			$ttfPath = $GLOBALS['RESOURCE_DIR'] . 'Verify/ttfs';
			$dir = dir($ttfPath);
			$ttfs = array();
			while (false !== ($file = $dir->read())) {
				if ($file[0] != '.' && substr($file, -4) == '.ttf') {
					$ttfs[] = $file;
				}
			}
			$dir->close();
			$this->fontttf = $ttfPath . '/' . $ttfs[array_rand($ttfs)];

			if ($this->useImgBg) {
				$this->_background();
			}

			if ($this->useNoise) {
				// 绘杂点
				$this->_writeNoise();
			}
			if ($this->useCurve) {
				// 绘干扰线
				$this->_writeCurve();
			}

			if ($code === null) {
				for ($i = 0; $i < $this->length; $i++) {
					$code .= $this->_codeSet[mt_rand(0, 51)];
				}
			}

			// 绘验证码
			$charMarginLeft = 0;
			$codeLength = strlen($code);
			for ($i = 0; $i < $codeLength; $i++) {
				$charMarginLeft += mt_rand($this->fontSize * 1.2, $this->fontSize * 1.6);
				imagettftext($this->_image, $this->fontSize, mt_rand(-30, 30), $charMarginLeft, $this->fontSize * 1.6, $this->_color, $this->fontttf, $code[$i]);
			}

			header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			header("content-type: image/png");

			// 输出图像
			imagepng($this->_image);
			imagedestroy($this->_image);

			return $code;
		}

		/**
		 * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数) 
		 *      
		 *      高中的数学公式咋都忘了涅，写出来
		 *		正弦型函数解析式：y=Asin(ωx+φ)+b
		 *      各常数值对函数图像的影响：
		 *        A：决定峰值（即纵向拉伸压缩的倍数）
		 *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
		 *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
		 *        ω：决定周期（最小正周期T=2π/∣ω∣）
		 *
		 */
		private function _writeCurve() {
			$px = $py = 0;

			// 曲线前部分
			$A = mt_rand(1, $this->imageH / 2); // 振幅
			$b = mt_rand(-$this->imageH / 4, $this->imageH / 4); // Y轴方向偏移量
			$f = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
			$T = mt_rand($this->imageH, $this->imageL * 2); // 周期
			$w = (2 * M_PI) / $T;

			$px1 = 0; // 曲线横坐标起始位置
			$px2 = mt_rand($this->imageL / 2, $this->imageL * 0.8); // 曲线横坐标结束位置

			for ($px = $px1; $px <= $px2; $px = $px + 1) {
				if ($w != 0) {
					$py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
					$i = (int)($this->fontSize / 5);
					while ($i > 0) {
						imagesetpixel($this->_image, $px + $i, $py + $i, $this->_color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
						$i--;
					}
				}
			}

			// 曲线后部分
			$A = mt_rand(1, $this->imageH / 2); // 振幅
			$f = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
			$T = mt_rand($this->imageH, $this->imageL * 2); // 周期
			$w = (2 * M_PI) / $T;
			$b = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
			$px1 = $px2;
			$px2 = $this->imageL;

			for ($px = $px1; $px <= $px2; $px = $px + 1) {
				if ($w != 0) {
					$py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
					$i = (int)($this->fontSize / 5);
					while ($i > 0) {
						imagesetpixel($this->_image, $px + $i, $py + $i, $this->_color);
						$i--;
					}
				}
			}
		}

		/**
		 * 画杂点
		 * 往图片上写不同颜色的字母或数字
		 */
		private function _writeNoise() {
			for ($i = 0; $i < 10; $i++) {
				//杂点颜色
				$noiseColor = imagecolorallocate($this->_image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
				for ($j = 0; $j < 5; $j++) {
					// 绘杂点
					imagestring($this->_image, 5, mt_rand(-10, $this->imageL), mt_rand(-10, $this->imageH), $this->_codeSet[mt_rand(0, 27)], $noiseColor);
				}
			}
		}

		/**
		 * 绘制背景图片
		 * 注：如果验证码输出图片比较大，将占用比较多的系统资源
		 */
		private function _background() {
			$path = $GLOBALS['RESOURCE_DIR'] . 'Verify/bgs/';
			$dir = dir($path);

			$bgs = array();
			while (false !== ($file = $dir->read())) {
				if ($file[0] != '.' && substr($file, -4) == '.jpg') {
					$bgs[] = $path . $file;
				}
			}
			$dir->close();

			$gb = $bgs[array_rand($bgs)];

			list($width, $height) = @getimagesize($gb);
			// Resample
			$bgImage = @imagecreatefromjpeg($gb);
			@imagecopyresampled($this->_image, $bgImage, 0, 0, 0, 0, $this->imageL, $this->imageH, $width, $height);
			@imagedestroy($bgImage);
		}
	}
