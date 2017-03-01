<?php

	/**
	 *@copyright Copyright 2014 listenlin. All rights reserved.
	 *@author listenlin <listenlin521@foxmail.com>
	 *@version 1.0
	 *@package Acply\Acp_parse_SQL
	 *
	 *把数组形式的SQL语句解析成完整的字符串，并符合prepare形式
	 */
	/**
	 * SQL语句解析类，输出正确的SQL语句或者prepare语句 输入的SQL信息格式为:
	 *  SQL = array( 
	 *  	field => {string} || {array(各字段名)} 
	 *  	repeat => ALL || DISTINCT 或者 SQL_CALC_FOUND_ROWS、SQL_NO_CACHE 等特殊功能 
	 *  	other => array( group => {string} || {array(各字段名)} 
	 *  	order => {string} || {array(字段 => DESC || ASC)}
	 *  	limit => array(start=>end) or array(start) …… )
	 *  	value => array(值的数据类型（CNT(区别字符串常量和字段名) || NUM || STR || LOB || BOOL || NULL || INOUT）=>各个值) 用于insert语句
	 *  	field=value => array(field => array(type=> value)) 用于update语句 
	 *  	condition => array(array(……) , type（AND || OR || = || <> || < || > || <= || >= || between || ……） , array(……))
	 *  	 可无限向下走的循环表达式，终点是一个常量（字符串或数字）或者 数组array(type=> value) or array(type => start,type => end)元素个数最多为2 ) 以下为此类提供的静态接口 Acp_parse_SQL::receive(SQL语句类型 自定义常量,要操作的数据表数组,符合上诉格式的SQL信息数组) Acp_parse_SQL::makePrepare() 生成PDO::prepare所需数据格式，并返回模板和要绑定的值
	 */
	class Acp_parse_SQL extends Acp_base {
		// 数据类型常量
		const NUM = PDO::PARAM_INT;
		const STR = PDO::PARAM_STR;
		const LOB = PDO::PARAM_LOB;
		const BOOL = PDO::PARAM_BOOL;
		const NULL = PDO::PARAM_NULL;
		const INOUT = PDO::PARAM_INPUT_OUTPUT;
		const CNT = 'parseCONST';// 解析有引号的字符串
		const TEXT = 'parseTEXT';// 解析直接的字符串
		// SQL语句类型常量
		const SELECT = 0;
		const INSERT = 1;
		const UPDATE = 2;
		const DELETE = 3;
		// 常用SQL语句模板
		private static $slt = 'SELECT @repeat @field FROM @table @condition @other';
		private static $ist = 'INSERT INTO @table (@field) VALUES(@value)';
		private static $ude = 'UPDATE @table SET @field_value @condition';
		private static $dle = 'DELETE FROM @table @condition';
		// 模板需要用到的各种值
		private static $type = null;
		private static $table = array();
		private static $field = '*';
		private static $condition = array();
		private static $repeat = ''; // ALL || DISTINCT
		private static $other = '';
		private static $value = array();
		private static $field_value = array();
		// 条件关系允许的操作符
		private static $relate = array(
			'+','-','*','/','=','<','>','<=','>=','<>','AND','OR','BETWEEN','LIKE'
		);
		// 解析条件表达
		static private function parsePrepareCondition(array $ary, array & $param) {
			$len = count($ary);

			if (3 <= $len) {

				$cdn = '(';

				for ($i = 0; $i < $len; $i++) {

					if ($i % 2 == 0) {

						if (is_array($ary[$i])) {

							$cdn .= self::parsePrepareCondition($ary[$i], $param);
						} else{
						
							$cdn .= (string )$ary[$i];
						}
					} elseif (in_array(strtoupper($ary[$i]),self::$relate)) {
						
						$cdn .= ' ' . strtoupper($ary[$i]) . ' ';
					} else {
						throw new Acp_error('where条件暂时不允许操作符 - '.$ary[$i], Acp_error::PARAM);
					}
				}

				return $cdn . ')';
			} elseif (2 == $len) {

				$str = array();

				foreach ($ary as $key => $value) {

					if (is_array($value)) {

						$str[] = self::parsePrepareCondition($ary[$key], $param);
					} else {
					
						$str[] = (string)$value;
					}
				}

				return implode(' AND ', $str);
			} elseif (1 == $len) {

				foreach ($ary as $key => $value) {
					if ($key == self::CNT) {
						return "'$value'";
					}elseif($key == self::TEXT){
						return "$value";
					} else {
						$param[] = array($key => $value);
						return " ? ";
					}
				}
			} else {
				throw new Acp_error('解析SQL时传入的where条件信息类型错误！', Acp_error::PARAM);
			}
		}
		// 接受传来需要解析成SQL的信息
		static public function receive($t, array $tb, array $ree) {
			// 初始化各种值
			self::$field = '*';
			self::$condition = array();
			self::$repeat = '';
			self::$other = '';
			self::$value = array();
			self::$field_value = array();

			self::$type = $t;
			self::$table = implode(',', $tb);
			
			// 解析有哪些字段
			if (isset($ree['field'])) {

				if (is_array($ree['field'])) {

					self::$field = implode(',', $ree['field']);
				} elseif (is_string($ree['field'])) {

					self::$field = $ree['field'];
				} else {
					throw new Acp_error('解析SQL时传入的字段信息类型错误！', Acp_error::PARAM);
				}
			}
			// 解析重复结果和其他功能
			if (isset($ree['repeat'])) {

				self::$repeat = $ree['repeat'];
			}
			// 解析SQL select语句中的其他额外功能
			if (isset($ree['other'])) {

				if (is_array($ree['other'])) {
					// 解析group语句
					if (isset($ree['other']['group'])) {

						if (is_array($ree['other']['group'])) {

							self::$other = ' GROUP BY ' . implode(',', $ree['other']['group']);
						} elseif (is_string($ree['other']['group'])) {

							self::$other = ' GROUP BY ' . $ree['other']['group'];
						} else {
							throw new Acp_error('解析SQL时传入的group中信息类型错误！', Acp_error::PARAM);
						}
					}
					// 解析order语句
					if (isset($ree['other']['order'])) {

						if (is_array($ree['other']['order'])) {

							$order = array();
							
							foreach ($ree['other']['order'] as $key => $value) {

								$value = strtolower($value) == 'desc' ? 'DESC' : 'ASC';
								$order[] = "$key $value";
							}
							self::$other .= ' ORDER BY ' . implode(',', $order);
						} elseif (is_string($ree['other']['order'])) {

							self::$other .= ' ORDER BY ' . $ree['other']['order'];
						} else {
							throw new Acp_error('解析SQL时传入的order中信息类型错误！', Acp_error::PARAM);
						}
					}

					// limit语句MYSQL支持
					if (isset($ree['other']['limit'])) {

						if (is_array($ree['other']['limit']) && count($ree['other']['limit']) == 1) {

							foreach ($ree['other']['limit'] as $key => $value) {

								if (!$value && is_numeric($key)) {

									self::$other .= " LIMIT $key";
								} elseif (is_numeric($key) && is_numeric($value)) {

									self::$other .= " LIMIT $key,$value";
								} else {
									throw new Acp_error('解析SQL时传入的limit中信息类型错误！', Acp_error::PARAM);
								}
							}
						} else {
							throw new Acp_error('解析SQL时传入的limit中信息类型错误！', Acp_error::PARAM);
						}
					}
				} else {
					throw new Acp_error('解析SQL时传入的select语句中其他辅助信息类型错误！', Acp_error::PARAM);
				}
			}
			// 外界输入的各种类型的值
			if (isset($ree['value'])) {

				if (is_array($ree['value'])) {

					self::$value = $ree['value'];
				} else {
					throw new Acp_error('解析SQL时传入的insert语句中信息类型错误！', Acp_error::PARAM);
				}
			}
			// 对各字段分别对应赋值修改
			if (isset($ree['field=value'])) {

				if (is_array($ree['field=value'])) {

					self::$field_value = $ree['field=value'];
				} else {
					throw new Acp_error('解析SQL时传入的update语句中信息类型错误！', Acp_error::PARAM);
				}
			}

			if (isset($ree['condition'])) {

				if (is_array($ree['condition'])) {

					self::$condition = $ree['condition'];
				} else {
					throw new Acp_error('解析SQL时传入的where语句中信息类型错误！', Acp_error::PARAM);
				}
			}
		}
		static public function makePrepare() {
			$param = array(); // 需要绑定的值
			$str = ''; // preapre需要的值

			if (count(self::$condition) == 0) {
				$where = "";
			} else {
				$where = 'WHERE ' . self::parsePrepareCondition(self::$condition, $param);
			}

			switch (self::$type) {
				case self::SELECT:

					$str = strtr(self::$slt, array(
						'@repeat' => self::$repeat,
						'@field' => self::$field,
						'@table' => self::$table,
						'@condition' => $where,
						'@other' => self::$other));

					break;
				case self::INSERT:

					$str = implode(',' , array_fill ( 0 , count(self::$value), '?' ) );

					$str = strtr(self::$ist, array(
						'@field' => self::$field,
						'@table' => self::$table,
						'@value' => $str));
					$param = self::$value;
					break;
				case self::UPDATE:

					$str = array();
					$prm = array();
					foreach (self::$field_value as $key => $value) {
						$str[] = "$key=?";
						$prm[] = $value;
					}
					foreach ($param as $value) {
						$prm[] = $value;
					}
					$param = $prm;

					$str = implode(',', $str);

					$str = strtr(self::$ude, array(
						'@field_value' => $str,
						'@table' => self::$table,
						'@condition' => $where));

					break;
				case self::DELETE:

					$str = strtr(self::$dle, array('@table' => self::$table, '@condition' => $where));

					break;
				default:
					throw new Acp_error('输入的SQL语句类型错误', Acp_error::PARAM);
			}

			return array($str, $param);
		}
		static public function bindParam(PDOStatement $p, array $ary) {
			foreach ($ary as $key => $value) {
				foreach ($value as $k => $v) {
					$p->bindValue($key + 1, $v, $k);
				}
			}
		}
	}
