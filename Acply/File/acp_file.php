<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_file
	 *
	 *对文件系统的统一封装处理类
	 */
	/*
	* 文件系统处理类 包括本地目录、上传的文件、本地和远程文件(暂时不支持)
	* 
	*/
	class Acp_file extends Acp_base {
		const DIR = 0; // 本地目录
		const UPLOAD = 1; // 上传的文件
		const LOCAL = 2; // 本地文件
		const REMOTE = 3; // 远程文件
		private $dir; // 文件或目录地址
		private $type; // 类型，目录？上传？本地？远程？
		/**
		 * @example 
		 * $f = new Acp_file('./sd.txt');或者$f = new Acp_file('./sd.txt',Acp_file::LOCAL);//实例化文件对象<br>
		 * $d = new Acp_file('../fd/',Acp_file::DIR);//实例化目录对象<br>
		 * $upload = new Acp_file($_FILES['thumb'],Acp_file::UPLOAD);//处理上传的文件
		 * @param string $file_dir
		 * @param Acp_file::const $type
		 * @throws Acp_error
		 */
		public function __construct($file_dir, $type = self::LOCAL) {
			$this->dir = $type !== self::UPLOAD ? strtr($file_dir, '\\', '/') : $file_dir;

			$this->type = $type;
			clearstatcache();
			if ($type === self::UPLOAD) {
				if (is_array($file_dir)) {
					$file = $file_dir['name'];
					switch ($file_dir['error']) {
						case UPLOAD_ERR_OK:
							// 说明OK没问题
							break;
						case UPLOAD_ERR_INI_SIZE:
							throw new Acp_error("$file 大小超过服务器上传限制大小！", Acp_error::INPUT);
							break;
						case UPLOAD_ERR_FORM_SIZE:
							throw new Acp_error("$file 大小超过表单限制的上传大小！", Acp_error::INPUT);
							break;
						case UPLOAD_ERR_PARTIAL:
							throw new Acp_error("$file 文件只上传了一部分！", Acp_error::INPUT);
							break;
						case UPLOAD_ERR_NO_FILE:
							throw new Acp_error("你没有选择 $file 文件！", Acp_error::INPUT);
							break;
						case UPLOAD_ERR_NO_TMP_DIR:
							throw new Acp_error("服务器无法找到上传目录， $file 上传失败！", Acp_error::INPUT);
							break;
						case UPLOAD_ERR_CANT_WRITE:
							throw new Acp_error("服务器无法写入 $file 文件！", Acp_error::INPUT);
							break;
						default:
							throw new Acp_error("发生未知错误！", Acp_error::INPUT);
					}
					$dir = $GLOBALS['whole']['dir_root'] . '/tmp_upload/';
					if (!is_dir($dir)) {
						self::deep_mkdir($dir);
					}
					$dir = $dir . Acp_util::randStr() . '.' . pathinfo($file, PATHINFO_EXTENSION);
					if (!move_uploaded_file($file_dir['tmp_name'], $dir)) {
						throw new Acp_error('移动上传文件' . $file . '失败', Acp_error::FUNC);
					}
					$this->dir = $dir;
				} else {
					throw new Acp_error('上传文件需要输入数组', Acp_error::PARAM);
				}
			} elseif ($type === self::LOCAL) {
				if (!file_exists($this->dir)) throw new Acp_error('文件地址不存在-' . $this->dir, Acp_error::PARAM);
			} elseif ($type === self::DIR) {
				if (!is_dir($this->dir)) self::deep_mkdir($this->dir);
			} else {
				throw new Acp_error('暂不支持此种类型的文件或目录系统-' . $type, Acp_error::PARAM);
			}
		}
		/**
		 * 将某一个目录下的所有文件和目录打包成ZIP
		 * @param ZipArchive $zip
		 * @param string $dir
		 * @param string $pre_fix
		 */
		private function addDirectoryToZip(ZipArchive $zip, $dir, $pre_fix = '') {
			$g = glob($dir . '/*');
			if (count($g) == 0) $zip->addEmptyDir($pre_fix);
			if ($pre_fix !== '') $pre_fix = $pre_fix . '/';
			foreach ($g as $file) {
				if (is_dir($file)) {
					$this->addDirectoryToZip($zip, $file, $pre_fix . basename($file));
				} else {
					$zip->addFile($file, $pre_fix . basename($file));
				}
			}
		}
		/**
		 * 此函数建立深层次不存在的目录
		 * @example
		 * 如果目录只到d:/a/b
		 * 欲建立d:/a/b/c/d/e
		 * 可使用 本函数Acp_file::deep_mkdir('d:/a/b/c/d/e')，PHP自带mkdir达不到此效果
		 * @param string $dir 需要建立的目录路径
		 * @param number $mode 建立的文件夹爱的权限，window下无效。默认为777，开启全部权限。
		 * @throws Acp_error
		 */
		public static function deep_mkdir($dir,$mode = 0777) {
			if ( ! is_dir($dir) && ! mkdir ( $dir ,  $mode ,  true )){
				throw new Acp_error("创建深层目录 $dir 失败", Acp_error::FUNC);
			}
		}
		/**
		 * 获取完整的路径字符串
		 */
		public function get() {
			return $this->dir;
		}
		/**
		 * 复制文件或目录（和目录下的所有文件及子文件夹）到输入的目的地
		 * @example
		 * $f = new Acp_file('d:/a/c.txt');<br>
		 * $f->copy('d:/a.txt');//d盘下已有此文件，并被重命名<br>
		 * $d = new Acp_file('d:/a/',Acp_file::DIR);<br>
		 * $d->copy('d:/c/');//a文件夹下的所有文件及子文件夹复制到了c文件夹下
		 * @param string $dest 复制到的目的地路径
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function copy($dest) {
			if ($this->type === self::REMOTE) return $this;
			if ($this->type === self::DIR) {
				$args = func_get_args();
				if (func_num_args() === 2) {
					$orign = $args[0];
					$dest = $args[1];
				} else {
					$orign = $this->dir;
					$dest = $args[0];
				}
				if (!is_dir($dest)) {
					self::deep_mkdir($dest);
				}
				$objects = scandir($orign);
				if (sizeof($objects) > 0) {
					foreach ($objects as $file) {
						if ($file == '.' || $file == '..') continue;
						$o = $orign . DIRECTORY_SEPARATOR . $file;
						if (is_dir($o)) {
							$this->copy($o, $dest . DIRECTORY_SEPARATOR . $file);
						} else {
							if (!copy($o, $dest . DIRECTORY_SEPARATOR . $file)) {
								throw new Acp_error("复制文件{$o}到" . $dest . DIRECTORY_SEPARATOR . $file . '失败！', Acp_error::FUNC);
							}
						}
					}
				}
			} else {
				if (!copy($this->dir, $dest)) {
					throw new Acp_error("复制文件{$this->dir}到$dest 失败！", Acp_error::FUNC);
				}
			}
			return $this;
		}
		/**
		 * 将文件夹或文件移动到某一处去
		 * @example
		 * $d = new Acp_file('d:/test',Acp_file::DIR);<br>
		 * $d->moveTo('d:/www/fsd/');<br>
		 * 即将文件夹d:/test完整移动到d:/www/fsd/。文件一样的操作。
		 * @param string $dest
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function moveTo($dest) {
			if ($this->type === self::REMOTE) return $this;

			if (!rename($this->dir, $dest)) {
				throw new Acp_error('移动文件或目录' . $this->dir . '至' . $dest . '失败', Acp_error::FUNC);
			}
			$this->dir = $dest;
			return $this;
		}
		/**
		 * 更改文件或文件夹的名字
		 * @example
		 * $d = new Acp_file('d:/test',Acp_file::DIR);<br>
		 * $d->changeName('first');<br>
		 * 即可将d:/test重命名为d:/first。<br>
		 * $f = new Acp_file('d:/www/a.html');<br>
		 * $f->changeName('bcd');<br>
		 * 即可将d:/www/a.html重命名为d:/www/bcd.html。<br>
		 * 或者$f->changeName();<br>
		 * 不传入参数，此时将用随机字符串来命名。文件夹也一样!<br>
		 * 结果可能为d:/www/fa3r3qhrjeq3rqbjqbb.html
		 * @param string $new_name
		 * @return Acp_file
		 */
		public function changeName($new_name = '') {
			if ($this->type === self::REMOTE) return $this;
			if ($new_name === '') {
				$new_name = Acp_util::randStr();
			} else {
				$new_name = basename($new_name);
			}
			if ($this->type === self::DIR) {
				$this->moveTo(dirname($this->dir) . DIRECTORY_SEPARATOR . $new_name);
			} else {
				$info = pathinfo($this->dir);
				$this->moveTo($info['dirname'] . DIRECTORY_SEPARATOR . "$new_name.$info[extension]");
			}
			return $this;
		}
		/**
		 * 删除文件或文件夹
		 * @example
		 * $f = new Acp_file('d:/www/a.html');<br>
		 * $f->delete();//即删除了该文件
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function delete() {
			if ($this->type === self::REMOTE) return $this;
			if ($this->type === self::DIR) {
				$dir = func_num_args() === 1 ? func_get_arg(0) : $this->dir;
				$files = array_diff(scandir($dir), array('.', '..'));
				foreach ($files as $file) {
					$df = $dir . DIRECTORY_SEPARATOR . $file;
					if (is_dir($df)) {
						$this->delete($df);
					} elseif (file_exists($df) && !unlink($df)) {
						throw new Acp_error("删除文件$df 失败", Acp_error::FUNC);
					}
				}
				if (is_dir($dir) && !rmdir($dir)) {
					throw new Acp_error("删除目录$dir 失败", Acp_error::FUNC);
				}
			} else {
				if (file_exists($this->dir) && !unlink($this->dir)) {
					throw new Acp_error("删除文件{$this->dir}失败", Acp_error::FUNC);
				}
			}
			return $this;
		}

		// 以下针对文件而言可用
		/**
		 * 将文件内容发给客户端或者通过函数返回给函数值
		 * @example
		 * $f = new Acp_file('d:/www/a.html');<br>
		 * $f->output();//相当于取出该文件内容组成的字符串，然后echo 出去。
		 * @param boolean $return 是否通过返回值返回，默认为不返回，直接发给客户端
		 * @throws Acp_error
		 * @return Acp_file|string
		 */
		public function output($return = false) {
			if ($this->type === self::REMOTE) return $this;
			if ($this->type === self::LOCAL) {
				if (!!$return) {
					$ctt = file_get_contents($this->dir);
					if ($ctt === false) {
						throw new Acp_error("读取文件内容{$this->dir}失败！", Acp_error::FUNC);
					}
					return $ctt;
				}
				flush();
				ob_clean();
				readfile($this->dir);
			}
			return $this;
		}
		/**
		 * 获取文件的MIME类型，非文件返回空字符串。
		 * 
		 * @return string
		 */
		public function getMime() {
			if (!is_file($this->dir)) return '';
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $this->dir);
			finfo_close($finfo);
			return $mimetype;
		}
		/**
		 * 验证该文件是否是图片
		 * @return boolean
		 */
		public function validateImage() {
			if (!is_file($this->dir)) return false;
			return stripos ($this->getMime(), 'image') >= 0 ? true : false;
		}
		/**
		 * 获取去除路径的文件名或文件夹名
		 * @return mixed
		 */
		public function getBase() {
			$info = pathinfo($this->dir);
			return $info['basename'];
		}
		/**
		 * 获取文件所处路径
		 * @return mixed
		 */
		public function getDir() {
			$info = pathinfo($this->dir);
			return $info['dirname'];
		}
		/**
		 * 获取后缀文件格式
		 * @return mixed
		 */
		public function getExt() {
			$info = pathinfo($this->dir);
			return $info['extension'];
		}
		/**
		 * 获取文件或文件夹名字
		 * @return mixed
		 */
		public function getName() {
			$info = pathinfo($this->dir);
			return $info['filename'];
		}
		/**
		 * 将某一文件或文件夹打包成zip文件
		 * @param string $dest 打包后，生成的ZIP包的路径地址
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function zip($dest) {
			if ($this->type === self::REMOTE) return $this;
			$dir = func_num_args() === 1 ? func_get_arg(0) : basename($this->dir) . '.zip';
			$zip = new ZipArchive();
			if ($zip->open($dir, ZipArchive::CREATE)) {
				if ($this->type === self::DIR) {
					$this->addDirectoryToZip($zip, dirname($this->dir) . DIRECTORY_SEPARATOR . basename($this->dir));
				} else {
					$zip->addFile($this->dir, basename($this->dir));
				}
				$zip->close();
			} else {
				throw new Acp_error("新建压缩文件$dir 失败！", Acp_error::FUNC);
			}
			return $this;
		}
		// 以下针对ZIP压缩文件有用
		/**
		 * 将ZIP文件解压出来
		 * @param string $dir
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function extractZip($dir = '.') {
			if ($this->type === self::REMOTE) return $this;
			if (strtolower($this->getExt()) === 'zip') {
				$zip = new ZipArchive();
				if ($zip->open($this->dir)) {
					$zip->extractTo($dir);
					$zip->close();
				} else {
					throw new Acp_error("打开压缩文件{$this->dir}失败！", Acp_error::FUNC);
				}
			}
			return $this;
		}
		/**
		 * 将某一文件夹或文件增加到ZIP包中
		 * @param string $file 欲打包进去的文件或文件夹路径
		 * @param string $zip_file ZIP包中的路径地址
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function addZip($file, $zip_file) {
			if ($this->type === self::REMOTE) return $this;
			if (strtolower($this->getExt()) === 'zip') {
				$zip = new ZipArchive();
				if ($zip->open($this->dir, ZipArchive::CREATE)) {
					if (is_dir($file)) {
						$this->addDirectoryToZip($zip, $file);
					} else {
						$zip->addFile($file, $zip_file);
					}
					$zip->close();
				} else {
					throw new Acp_error("打开压缩文件{$this->dir}失败！", Acp_error::FUNC);
				}
			}
			return $this;
		}
		/**
		 * 删除包中的某一个文件或文件夹
		 * @param string $file 欲删除的包中的文件或文件夹路径
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function deleteZip($file) {
			if ($this->type === self::REMOTE) return $this;
			if (strtolower($this->getExt()) === 'zip') {
				$zip = new ZipArchive();
				if ($zip->open($this->dir)) {
					$zip->deleteName($file);
					$zip->close();
				} else {
					throw new Acp_error("打开压缩文件{$this->dir}失败！", Acp_error::FUNC);
				}
			}
			return $this;
		}
		/**
		 * 修改包中的文件或文件夹
		 * @param string $old 欲修改的文件或文件夹路径
		 * @param string $new 修改后的文件或文件夹路径
		 * @param string $new_zip_file 包中修改后的文件或文件夹路径
		 * @throws Acp_error
		 * @return Acp_file
		 */
		public function changeZip($old, $new, $new_zip_file) {
			if ($this->type === self::REMOTE) return $this;
			if (strtolower($this->getExt()) === 'zip') {
				$zip = new ZipArchive();
				if ($zip->open($this->dir)) {
					$zip->deleteName($old);
					if (is_dir($new)) {
						$this->addDirectoryToZip($zip, $new);
					} else {
						$zip->addFile($new, $new_zip_file);
					}
					$zip->close();
				} else {
					throw new Acp_error("打开压缩文件{$this->dir}失败！", Acp_error::FUNC);
				}
			}
			return $this;
		}
	}
