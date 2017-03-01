<?php

	/**
	 * @copyright Copyright 2014 listenlin. All rights reserved.
	 * @author listenlin <listenlin521@foxmail.com>
	 * @version 1.0
	 * @package Acply\Acp_db
	 *
	 * 对数据库连接的使用进行封装
	 */
	/**
	 * 
	 */
	class Acp_db extends Acp_base {
		// 字段数据类型常量
		const NUM = Acp_parse_SQL::NUM;
		const STR = Acp_parse_SQL::STR;
		const LOB = Acp_parse_SQL::LOB;
		const BOOL = Acp_parse_SQL::BOOL;
		const NULL = Acp_parse_SQL::NULL;
		const INOUT = Acp_parse_SQL::INOUT;
		const CNT = Acp_parse_SQL::CNT;
		const TEXT = Acp_parse_SQL::TEXT;
		/**
		 * 数据库驱动类实例对象
		 *
		 * @staticvar
		 * @var Acp_driver_pdo
		 */
		public static $sdb = null;

		/**
		 * 更改当前连接到数据库的用户。
		 * 传入参数分别为‘LOW’，‘MIDDLE’，‘HIGH’。
		 * 分别代表有着三种低、中、高权限的数据库用户。
		 * 三种用户的账户名称和密码在用户配置文件中配置
		 *
		 * @param string $qx
		 *        	默认为低权限用户
		 */
		public static function changeUser($qx = 'LOW') {
			self::$sdb->changeUser($qx);
		}
		/**
		 * 开启数据库的事务。
		 * MYSQL的话，数据表的储存引擎必须为InnoDB才支持此功能。
		 */
		public static function beginTransaction() {
			self::$sdb->beginTransaction();
		}
		/**
		 * 返回当前事都处于事务中 PHP version >= 5.3.3
		 * @return boolean
		 */
		public static function inTransaction(){
			return self::$sdb->inTransaction();
		}
		/**
		 * 提交事务
		 */
		public static function commit() {
			self::$sdb->commit();
		}
		/**
		 * 回滚事务
		 */
		public static function rollBack() {
			self::$sdb->rollBack();
		}
		/**
		 * 直接输入SQL语句的接口
		 * @example
		 * $statement = Acp_db::query('SELECT * FROM table_name WHERE id > 10');<br>
		 * 返回什么类型的结果集，取决于用什么样的数据库驱动与及哪种类型的SQL语句，PDO的驱动返回PDOStatement对象。<br>
		 * 只用PHP的mysql_*函数驱动，返回数据库资源类型的值
		 * @param string $s SQL语句
		 * @throws Acp_error
		 * @return PDOStatement 返回一个预处理语句
		 */
		public static function query($s) {
			return self::$sdb->query($s);
		}
		/**
		 * 检测上一个数据库操作是否有错误，无错误返回true。有错误则抛出异常
		 *
		 * @throws Acp_error DB类型的异常
		 */
		public static function getError() {
			return self::$sdb->getError();
		}
		/**
		 * 执行一个sql文件里的所有语句
		 * @example
		 * $result = Acp_db::execSqlFile('d:/applications/your_app/sql/init_table.sql');<br>
		 * 会依次执行sql文件里的所有SQL语句，并将每条语句的返回值通过数组返回
		 * @param string $dir SQL文件地址
		 * @return array 由每条SQL语句执行结果构成的数组
		 */
		public static function execSqlFile($dir) {
			$sql = Acp_util::getFileSQL($dir);
			$result = array();
			foreach ($sql as $value) {
				$result[] = self::query($value);
			}
			return $result;
		}
	}

	Acp_db::$sdb = Acp_driver::getDb();