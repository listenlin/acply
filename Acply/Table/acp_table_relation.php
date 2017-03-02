<?php

    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_table_relation
     *
     *对所有的关系数据表进行封装使用
     */
    /*
	* 此类为关系数据表之间所用，直接面向框架使用者
	*/
class Acp_table_relation extends Acp_base
{
    /**
         * 数据库驱动实例对象
         *
         * @var Acp_driver_pdo
         */
    private $db = null;
    /**
         * 为几张表的关系，通常为jion
         *
         * @var string
         */
    private $relate = '';
    /**
         * 数据表的字段们
         *
         * @var array
         */
    protected $fields = array ();
    /**
         * 对字段类型的缓存
         * @var array
         */
    protected $field_type = array ();
    /**
         * 缓存的模型对象
         * @var array
         */
    private $models = array();
    /**
         * 缓存需要用到的SQL语句
         *
         * @var array
         */
    private $sql = array();

    public function __construct(array $tables, $from)
    {
        $this->db = Acp_driver::getDb();
        foreach ($tables as $k => $v) {
            $this->models[$k] = new $v;
            $type = $this->models[$k]->getAllFieldValueType();
            foreach ($this->models[$k]->getFields() as $value) {
                $newField = "$k.$value";
                $this->fields[] = $newField;
                $this->field_type[$newField] = $type[$value];
            }
        }
        $this->relate = array("$from");
    }
    public function __destruct()
    {
        $this->db = null;
        $this->models = null;
        $this->relate = null;
    }
        
    public static function getModel($key)
    {
        return $this->models[$key];
    }
    // 对数据表的相关操作
    /**
         * 获取几张表的记录
         * @example
         * $result = $db->getRecord(array(
         *      'condition'=>array(
         *                      'id','>',array(Acp_db::NUM=>20)
         *                  ),
         *      'field'=>array('id','name','email'),
         *      'limit'=>array(10)
         * ));
         * 通过此数组形式的SQL语句来查询数据库
         * @param array $sql SQL语句的数组形式
         * @return Acp_result 结果集对象实例
         */
    public function getRecord(array $sql)
    {
        if (!isset($sql['field'])) {
            $sql['field'] = '*';
        }
        return $this->db->select($this->relate, $sql);
    }
    /**
         * 分页形式来获取表的记录
         * @example
         * $result = $db->paging(2,30,array('id','name','eamil','tel'));
         * 查询第二页的数据，并且每页最多有30条记录。
         * 只选择id、name、email、tel四个字段的分页查询
         * @param int $p 想要查询的页码数，比如要查看第3页的数据，此参数就是3
         * @param int $l 每页限制的记录数量，每一页限制最多有几条数据。
         * @param string $fields 需要获取的字段名称
         * @throws Acp_error
         * @return Acp_result 结果集对象实例
         */
    public function paging($p, $l, $fields = '*')
    {
        if (is_array($fields) || is_string($fields)) {
            $sql['field'] = $fields;
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
        $sql = array_replace($this->sql, $sql);
        $this->sql = array();
        if (is_numeric($p) && is_numeric($l)) {
            $start = (round($p) - 1) * round($l);
            return $this->db->splitPage($start, $l, $this->relate, $sql);
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
    }
    /**
         * 获取上次数据库操作的SQL语句
         * @return array
         */
    public function getLastSql()
    {
        return $this->db->getLastSql();
    }
    /**
         * 输入SQL的条件语句。
         * @example
         * $db->where(
         *      'id > ? and name = ?',
         *      array(array(Acp_db::NUM=>6),array(Acp_db::STR=>'tom'))
         * )->select();
         * 查询id大于6并且name是tom的记录，第二个参数的数组用来绑定条件字符串中的问号，并且指定其数据类型，能完全防止SQL注入。
         * @param string $where where语句，操作符和操作数之间必须用空格隔开。
         * @param array $bindValue 要绑定到占位符？的值与及类型
         * @return Acp_table_relation
         */
    public function where($where, array $bindValue = array())
    {
        $where = trim($where);
        if ($where) {
            $wh = preg_split('/[ \t\x0B\f]+/', $where);
            $j = 0;
            for ($i = 0, $len = count($wh); $i < $len; $i ++) {
                $matches = array();
                if ($wh [$i] === '?') {
                    $wh [$i] = $bindValue [$j ++];
                } elseif (strpos($wh [$i], ':') === 0) {
                    $wh [$i] = array(
                    $this->field_type [strtr($wh [$i], array(':'=>''))] => $bindValue [$j ++]
                    );
                } elseif (preg_match("/^['|\"](.*)['|\"]$/", $wh [$i], $matches)) {
                    $wh [$i] = array (
                    Acp_table::CNT => $matches[1]
                    );
                }
            }
            $this->sql ['condition'] = $wh;
        }
        return $this;
    }
    /**
         * 输入需要用到的字段值
         * @example
         * $db->fields('id,name,eamil,tel')->select();
         * 设置有哪些字段，可用于select、update、insert语句。update和insert语句还必有value方法配合。
         * 还可以有以下两种写法。加上上面的，共有三种写法
         * $db->fields(array('id','name','eamil','tel'));
         * $db->fields('id','name','eamil','tel');
         * @param mixed $fields 字段名
         * @return Acp_table_relation
         */
    public function fields($fields)
    {
        if (func_num_args() === 1) {
            if (is_bool($fields)) {
                $this->sql ['field'] = $this->getFields($fields);
            } elseif (is_string($fields)) {
                $this->sql ['field'] = explode(',', $fields);
            } elseif (is_array($fields)) {
                $this->sql ['field'] = $fields;
            }
        } else {
            $this->sql ['field'] = func_get_args();
        }
        return $this;
    }
    /**
         * 对查询结果进行分组
         * @example
         * $db->group('id,eamil');
         * $db->group('id','tel');
         * $db->group(array('tel','name'));
         * 对查询结果进行分组，支持上面的三种写法。
         * @param mixed $group 输入数组时，分组依据的各个字段名称，也可以直接以字符串此写好。
         * @return Acp_table_relation
         */
    public function group($group)
    {
        if (func_num_args() === 1) {
            if (!is_array($group)) {
                $group = explode(',', $group);
            }
        } else {
            $group = func_get_args();
        }
        $this->sql ['other'] ['group'] = $group;
        return $this;
    }
    /**
         * 对查询输出结果进行分页输出
         * @example
         * $db->limit(10)->select();//查询结果最多有10条记录。
         * $db->limit(20,30)->select();//从结果集的第20条记录开始，取出最多30条记录。
         * @param int $start 开始的索引值
         * @param int $end 结束的值
         * @return Acp_table_relation
         */
    public function limit($start, $end = null)
    {
        if ($end === null) {
            $this->sql ['other'] ['limit'] = array (
                    $start
            );
        } else {
            $this->sql ['other'] ['limit'] = array (
                    $start => $end
            );
        }
        return $this;
    }
    /**
         * 对查询结果进行排序
         * @example
         * $db->order(array('name','tel'),array('DESC','ASC'))->select();
         * 对查询结果排序，先对name进行逆序排，再对tel进行顺序排。
         * @param array $field 排序依据的字段名称
         * @param array $order 排序的顺序，DESC || ASC
         * @return Acp_table_relation
         */
    public function order(array $field, array $order)
    {
        for ($i = 0, $len = count($field); $i < $len; $i ++) {
            $this->sql ['other'] ['order'] [$field [$i]] = $order [$i];
        }
        return $this;
    }
    /**
         * 对查询的一些修饰，比如是否需要去掉重复值DISTINCT等
         * @example
         * $db->adorn('DISTINCT')->adorn('SQL_NO_CACHE')->select();//只对select语句有效
         * 这里添加的参数，全都在SQL语句SELECT 和 FROM的中间放着。
         * 最终效果相当于SELECT DISTINCT SQL_NO_CACHE * FROM table_name
         * @param string $adorn 修饰的标识符
         * @return Acp_table_relation
         */
    public function adorn($adorn)
    {
        if (isset($this->sql ['repeat'])) {
            $this->sql ['repeat'] .= " $adorn";
        } else {
            $this->sql ['repeat'] = $adorn . '';
        }
        return $this;
    }
    /**
         * 开始查询
         * @example
         * $result = $db->fields()->limit()->group()->order()->select();
         * 开始查询，并返回一个结果集对象。
         * @param string $fields 可以再设置要查询的字段
         * @return Acp_result 结果集对象
         */
    public function select($fields = null)
    {
        if ($fields !== null) {
            $this->sql ['field'] = $fields;
        }
        $result = $this->getRecord($this->sql);
        $this->sql = array ();
        return $result;
    }
}
