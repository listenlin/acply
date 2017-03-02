<?php
    /**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_table
     *
     *对数据表的使用进行封装
     */

/**
 * 数据表类，直接面向框架使用者
 * 开发者只需要继承此类便可以使用此数据模型了
 */
class Acp_table extends Acp_db
{
    /**
     * 数据库驱动接口对象
     *
     * @var Acp_driver_pdo
     */
    private $db = null;
    /**
     * 数据表的名字
     *
     * @var string
     */
    protected $table_name = '';
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
     * 缓存需要用到的SQL语句
     *
     * @var array
     */
    private $sql = array ();
    
    /**
     * 开发者需在继承的类中调用本析构函数，并传入数据表名称和所有字段名称
     *
     * @param string $name
     *          数据表名称
     * @param array $fields
     *          所有字段名称
     */
    public function __construct($name, array $fields)
    {
        $this->db = Acp_driver::getDb();
        $this->table_name = $name;
        $this->fields = array_keys($fields);
        $this->field_type = $fields;
    }
    public function __destruct()
    {
        $this->db = null;
    }
    /**
     * 返回此数据表的名字
     *
     * @return string
     */
    public function getName()
    {
        return $this->table_name;
    }
    /**
     * 返回一个数组，包含所有的字段名
     * @param boolean $removePrimaryKey 是否返回去除掉主键的字段数组，默认为false要返回。
     * @return array
     */
    public function getFields($removePrimaryKey = false)
    {
        if ($removePrimaryKey) {
            $f = $this->fields;
            array_shift($f);
            return $f;
        } else {
            return $this->fields;
        }
    }
    /**
     * 获取某个字段的值类型
     * @param string $field 字段名称
     * @return int 值的类型
     */
    public function getFieldValueType($field)
    {
        if (isset($this->field_type[$field])) {
            return $this->field_type[$field];
        } else {
            throw new Acp_error("不存在此字段的值类型 - $field", Acp_error::PARAM);
        }
    }
    public function getAllFieldValueType()
    {
        return $this->field_type;
    }
    // 对数据表的相关操作
    /**
     * 输入一个数组类型的SQL SELECT语句，返回结果集对象
     * $result = $db->getRecord(array(
     *      'condition'=>array(
     *                      'id','>',array(Acp_db::NUM=>20)
     *                  ),
     *      'field'=>array('id','name','email'),
     *      'limit'=>array(10)
     * ));
     * 通过此数组形式的SQL语句来查询数据库
     * @param array $sql
     * @return Acp_result 数据库结果集
     */
    public function getRecord(array $sql)
    {
        if (! isset($sql ['field'])) {
            $sql ['field'] = $this->fields;
        }
        return $this->db->select(array (
                $this->table_name
        ), $sql);
    }
    /**
     * 为数据表增加记录
     * @example
     * $re = $db->addRecord(array(
     *      array(
     *          'field'=>array('name','eamil','tel'),
     *          'value'=>array(
     *              array(Acp_db::STR=>'tom'),
     *              array(Acp_db::STR=>'lfd@da.com'),
     *              array(Acp_db::STR=>'24141412412')
     *          )
     *      ),
     *      array(
     *          'field'=>array('name','eamil','tel'),
     *          'value'=>array(
     *              array(Acp_db::STR=>'mils'),
     *              array(Acp_db::STR=>'lfsdad@da.com'),
     *              array(Acp_db::STR=>'213412412')
     *          )
     *      )
     * ));
     * 通过此种方式给数据表添加记录，并且返回添加后的每条记录的主键值组成的数组
     * @param array $v
     * @throws Acp_error
     * @return array 增加一次后该记录的ID号共同组成数组
     */
    public function addRecord(array $v)
    {
        $i = array ();
        foreach ($v as $value) {
            if (is_array($value)) {
                if (! isset($value ['field'])) {
                    $value ['field'] = $this->fields;
                }
                $i [] = $this->db->insert(array (
                        $this->table_name
                ), $value);
            } else {
                throw new Acp_error('输入参数错误', Acp_error::PROGRAM);
            }
        }
        return $i;
    }
    /**
     * 删除记录
     * @example
     * $num = $db->deleteRecord(array(
     *      'condition'=>array('name','=',array(Acp_db::STR=>'tom'))
     * ));
     * 删除名字是tom的人，并返回删除的记录数。
     * @param array $sql SQL的数组，一般是删除条件
     * @throws Acp_error
     * @return int 删除的记录条数
     */
    public function deleteRecord(array $sql)
    {
        if (isset($sql ['condition'])) {
            return $this->db->delete(array (
                    $this->table_name
            ), $sql);
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
    }
    /**
     * 修改某条记录。
     * 注意一件事，如果更改的前后值一样，会返回0;
     * @example
     * $num = $db->editRecord(array(
     *      'field=value'=>array(
     *          'name'=>array(Acp_db::STR=>'lsl'),
     *          'eamil'=>array(Acp_db::STR=>'dsf@ef.com'),
     *          'tel'=>array(Acp_db::STR=>'3413134134')
     *      ),
     *      'condition'=>array('id','=',array(Acp_db::NUM=>5))
     * ));
     * 需改id为5的记录的值，并返回修改的记录数
     * @param array $sql 修改记录的SQL语句数组
     * @throws Acp_error
     * @return int 修改的记录条数
     */
    public function editRecord(array $sql)
    {
        if (isset($sql ['field=value']) && isset($sql ['condition'])) {
            return $this->db->update(array (
                    $this->table_name
            ), $sql);
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
     * 以分页的方式获取数据表的数据记录
     * @example
     * $result = $db->paging(2,30,array('id','name','eamil','tel'));
     * 查询第二页的数据，并且每页最多有30条记录。
     * 只选择id、name、email、tel四个字段的分页查询
     * @param int $p 需要指出获取哪个分页的页数
     * @param int $l 分页时每页有几条数据
     * @param string $fields 需要获取那些字段值，某认为所有字段
     * @throws Acp_error
     * @return Acp_result 结果集对象
     */
    public function paging($p, $l, $fields = '*')
    {
        if (is_array($fields) || is_string($fields)) {
            $sql ['field'] = $fields;
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
        $sql = array_replace($this->sql, $sql);
        $this->sql = array();
        if (is_numeric($p) && is_numeric($l)) {
            $start = (round($p) - 1 ) * round($l);
            return $this->db->splitPage($start, $l, array (
                    $this->table_name
            ), $sql);
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
    }
    /**
     * 取得该数据表的所有数据
     * @example
     * $results = $db->getAll();//获取所有数据，默认为有所有字段
     * $results = $db->getAll('name,tel');//获取所有数据，但每条记录只需要名字和电话
     * $results = $db->getAll('name','eamil');//获取所有数据，但每条记录只需要名字和邮件地址
     * $results = $db->getAll(array('id','eamil'));//获取所有数据，每条记录只有主键值和邮件地址
     * @param string $fields 取得哪些字段的值，默认为全部
     * @throws Acp_error
     * @return Acp_result 结果集对象
     */
    public function getAll($fields = '')
    {
        if (func_num_args() === 1) {
            if ($fields == '') {
                $sql = array (
                        'field' => $this->fields
                );
            } elseif (is_array($fields) || is_string($fields)) {
                $sql = array (
                        'field' => $fields
                );
            } else {
                throw new Acp_error('获取结果类型参数错误，并无此选项 - ' . $fields, Acp_error::PROGRAM);
            }
        } else {
            $sql = array (
                    'field' => func_get_args()
            );
        }
        return $this->db->select(array (
                $this->table_name
        ), $sql);
    }
    /**
     * 验证某个值是否在此字段中存在
     * @example
     * if($db->checkExist('name','tom')){}
     * 检测该数据表中name字段是否有tom这个值，有则返回true，否则返回false
     * @param string $field 需要验证的字段名称
     * @param array $value 值的类型和值的数组映射
     * @throws Acp_error
     * @return boolean 是否有
     */
    public function checkExist($field, $value)
    {
        if (is_string($field)) {
            $sql ['field'] = '*';
            $value = array ( $this->field_type [$field] => $value );
            $sql ['condition'] = array (
                    $field,
                    '=',
                    $value
            );
            $result = $this->db->select(array (
                    $this->table_name
            ), $sql);
            return ($result->getCounts() > 0);
        } else {
            throw new Acp_error('参数类型错误', Acp_error::PROGRAM);
        }
    }
    /**
     * 输入SQL的条件语句
     * @example
     * $db->where(
     *      'id > ? and tel = :tel and name = ?',
     *      array(
     *          array(Acp_db::NUM=>$_POST['id']),
     *          $_POST['tel'],
     *          array(Acp_db::STR=>$_POST['name'])
     *      )
     * )->select();
     * 第一个参数的where条件中，各项之间必须用空格或其他空白字符分开，不能如“id>?”这样连着写。
     * 条件中有占位符 - “？”,说明此处输入的值，在定义数据模型时没有指定其值的类型，
     * 就需要通过第二个参数来指定，如上所示的array(Acp_db::NUM=>$_POST['id'])，
     * 因为这个数组处于第二个参数的数组的第一个元素，就说明第一个占位符？其值类型为数字，并且值为$_POST['id']。
     * 占位符为：开头，冒号后面为字段名称，说明其值的类型已经在定义数据模型时被指定了，就直接输入值，
     * 如上所以，直接输入$_POST['tel']。
     * 第二个参数的数组中每个元素的位置严格和条件中的占位符的位置一一对应，参考示例。
     * @param string $where where语句，操作符和操作数之间必须用空格隔开。
     * @param array $bindValue 要绑定到占位符？和：的值与及类型
     * @return Acp_table
     */
    public function where($where, array $bindValue = array())
    {
        $where = trim($where);
        if ($where) {
            $wh = preg_split('/[ \t\x0B\f]+/', $where);
            // $wh = preg_split ( '/[?|(:\w+)]/', $where );
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
                        self::CNT => $matches[1]
                    );
                }
            }
            $this->sql ['condition'] = $wh;
        }
        return $this;
    }
    /**
     * 输入要用到的值。
     * @example
     * $id = $db->fields('name','no')->value(array(
     *      array(Acp_db::STR=>'dfa'),
     *      array(Acp_db::NUM=>252)
     * ))->insert();
     * value方法用于增加和更新操作中，并且经常和field方法配对使用，。
     * 在使用新方式时，使用value时，前面必须已经使用了fields，不然无法指定值类型,导致失败。
     * @param array $value 二维数组，第二维是一个键值对的哈希表，类型指定到具体的值
     * @return Acp_table
     */
    public function value(array $value)
    {
        if (!isset($this->sql['field'])) {
            throw new Acp_error('指定字段值前，没有指定字段名称!', Acp_error::PARAM);
        }
        $bindValue = array();
        for ($i=0 , $length = count($this->sql['field']); $i < $length; $i++) {
            $field = $this->sql['field'][$i];
            if (array_key_exists($field, $value)) {
                $v = $value[$field];
            } elseif (array_key_exists($i, $value)) {
                $v = $value[$i];
            } else {
                throw new Acp_error("传入的值中不存在字段 - $field", Acp_error::PARAM);
            }
            $bindValue[] = array($this->field_type[$field] => $v);
        }
        $this->sql['value'] = $bindValue;
        return $this;
    }
    /**
     * 输入需要用到的字段值
     * @example
     * $db->fields('id,name,eamil,tel')->select();
     * 设置有哪些字段，可用于select、update、insert语句。update和insert语句还必有value方法配合。
     * 还可以有以下三种写法。加上上面的，共有四种写法
     * $db->fields(true|fasle);//自动使用数据模型的字段，传入的参数决定是否除去自动增加的主键字段。
     * $db->fields(array('id','name','eamil','tel'));
     * $db->fields('id','name','eamil','tel');
     * @param mixed $fields 每个字段名称就是一个数组元素
     * @return Acp_table
     */
    public function fields($fields)
    {
        if (func_num_args() === 1) {
            if (is_bool($fields)) {
                $this->sql['field'] = $this->getFields($fields);
            } elseif (is_string($fields)) {
                $this->sql['field'] = explode(',', preg_replace('/\s+/i', '', $fields));
            } elseif (is_array($fields)) {
                $this->sql['field'] = $fields;
            }
        } else {
            $this->sql['field'] = func_get_args();
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
     * @return Acp_table
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
     * @return Acp_table
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
     * @return Acp_table
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
     * @return Acp_table
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
    // 用以下四种操作结束
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
    /**
     * 更新数据库信息
     * @example
     * $num = $db->where('id = 6')->fields('name')->value(array(array(Acp_db::STR=>'lop')))->update();
     * 开始修改id等于6的记录，并返回修改操作影响的记录数。
     * @return number 更新的记录数量
     */
    public function update()
    {
        $fv = array ();
        $fields = $this->sql ['field'];
        foreach ($fields as $key => $value) {
            $fv [$value] = $this->sql ['value'] [$key];
        }
        $this->sql ['field=value'] = $fv;
        $result = $this->editRecord($this->sql);
        $this->sql = array ();
        return $result;
    }

    /**
     * 删除数据库记录
     * @example
     * $num = $db->where('id = 8')->delete();
     * 删除id等于8的记录，返回删除的记录数。
     * @return number 被删除的记录数量
     */
    public function delete()
    {
        $result = $this->deleteRecord($this->sql);
        $this->sql = array();
        return $result;
    }
    
    /**
     * 插入记录
     * @example
     * 可参考value方法的用法
     * @return int 插入后该记录的主键值
     */
    public function insert()
    {
        $result = $this->addRecord(array (
                $this->sql
        ));
        $this->sql = array ();
        return $result[0];
    }
    /**
     * 对某个字段进行自增
     * @example
     * $db->increase('id > 7','age',1);//让id大于7的记录中年龄值都自增一，第三个参数如果省略，默认为1
     * @param string $where 设置哪些记录要自增
     * @param string $field 需要自增的字段名称
     * @param int $step 增加的步长
     * @return PDOStatement
     */
    public function increase($where, $field, $step = 1)
    {
        $tn = $this->table_name;
        return $this->db->query("UPDATE $tn SET $field = $field + $step WHERE $where")->rowCount();
    }
    /**
     * 用法参考上面的方法，只不过此方法是自减操作。
     */
    public function decrease($where, $field, $step = 1)
    {
        $tn = $this->table_name;
        return $this->db->query("UPDATE $tn SET $field = $field - $step WHERE $where")->rowCount();
    }
}
