<?php

    /**
     * 数据库查询的结果集类 要兼容各种结果才行。。。但是。。目前只能PDO
     */
class Acp_result extends Acp_base implements Iterator
{
    // 数据库驱动类型
    const PDO = 0;
    const MYSQLI = 1;
    const MYSQL = 2;
    // 结果获取类型
    const NUM = 0;
    const ASSOC = 1;
    const OBJ = 2;
    private $gettype = array(self::PDO => array(
        PDO::FETCH_NUM,
        PDO::FETCH_ASSOC,
        PDO::FETCH_OBJ));
// 原始结果集对象
    private $result = null;
// 取出来的数据
    private $row = null;
// 迭代位置
    private $position = 0;
// 取出来的数据
    private $data = array();
        
// 结果集类型
    private $type = '';
// 总数
    private $counts = -1;
        
    public function __construct($r, $c = -1, $t = 'pdo')
    {
        $this->result = $r;
        $this->counts = intval($c);
        $this->type = $t;
    }
    public function __destruct()
    {
        $this->result = null;
    }
//
/**
         * 释放数据库结果集
         * @example
         * $result = $db->select();
         * $result->release();//将查询的结果资源释放掉
         * @throws Acp_error
         * @return void
         */
    public function release()
    {
        switch ($this->type) {
            case self::PDO:
                $this->result->closeCursor();
                break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('释放数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
        $this->result = null;
    }
/**
         * 返回数据库原始的结果集对象,也就是将全部结果集获取。由开发者自己来操作。
         * @example
         * $result = $db->select();
         * $rows = $result->getResults(Acp_result::ASSOC);//此处为一个PDO statement对象，用的数据库驱动不同，返回不同。
         * while($row = $rows->fetch(PDO::FETCH_ASSOC)){
         *      print_r($row);
         * }
         * @param int $type
         * @throws Acp_error
         * @return void
         */
    public function getResults($type)
    {
        $mode = $this->gettype[$this->type][$type];
        switch ($this->type) {
            case self::PDO:
                $this->result->setFetchMode($mode);
                break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('获取数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
        return $this->result;
    }
/**
         * 返回符合查询条件的总行数，但在有limit语句时，实际返回的结果不一定有这么多。
         * @example
         * $result = $db->select();
         * $result->getCounts();//返回此数据表总共有多少行记录
         * $result = $db->where('id > 20')->limit(20)->select();
         * $result->getCounts();//此时返回符合where语句记录的总数（假设数据表有100条记录，则此方法返回80），但是实际上结果集中只有20条记录（20-40行的记录）
         * @return int 符合查询条件的总行数
         */
    public function getCounts()
    {
        return $this->counts;
    }
/**
         * 返回此行的第几个字段的值
         * @example
         * $result = $db->select('id,name,email,tel');
         * $result->getIndex();//返回第一条记录的id值
         * $result->getIndex(1);//返回第一条记录的name值
         * $result->getIndex(3);//返回第一条记录的tel值
         * $result->next();//移到下一条记录
         * $result->getIndex();//返回第二条记录的id值
         * $result->getIndex(2);//返回第二条记录的email值
         * @param int $i 默认值为0
         */
    public function getIndex($i = 0)
    {
        if ($this->row === null) {
            $this->nextRow();
        }
        return $this->row[$i];
    }
//
/**
         * 获取为下一行的结果
         * @example 示例见getIndex()方法
         * @return void
         */
    public function nextRow()
    {
        $this->row = $this->getArray();
    }
/**
         * 以数组形式返回结果
         * $result = $db->select();
         * $row = $result->getArray();
         * echo $row[0];//获取第一行第一个字段的值
         * print_r($row);//获取第一行的数字索引数组
         * @throws Acp_error
         * @return array
         */
    public function getArray()
    {
        $mode = $this->gettype[$this->type][self::NUM];
        switch ($this->type) {
            case self::PDO:
                return $this->result->fetch($mode);
            break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('获取数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
    }
/**
         * 返回以字段名为键值的关联数组
         * @example
         * $result = $db->select();
         * $row = $result->getAssoc();
         * echo $row['id'];//第一行ID字段的值
         * print_r($row);//获取第一行的关联数组，以字段名为键值
         * @throws Acp_error
         * @return array
         */
    public function getAssoc()
    {
        $mode = $this->gettype[$this->type][self::ASSOC];
        switch ($this->type) {
            case self::PDO:
                return $this->result->fetch($mode);
            break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('获取数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
    }
/**
         * 返回一个实例对象，以字段名为属性
         * @example
         * $result = $db->select();
         * $row = $result->getObject();
         * echo $row->id;//获取第一行字段名为ID的值
         * print_r($row);//获取第一行的实力对象，以获取的所有字段为属性的对象
         * @throws Acp_error
         * @return object
         */
    public function getObject()
    {
        $mode = $this->gettype[$this->type][self::OBJ];
        switch ($this->type) {
            case self::PDO:
                return $this->result->fetch($mode);
            break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('获取数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
    }
/**
        *获取所有查询值
        */
    public function getAll($mode = null)
    {
        if ($mode === null) {
            $mode = $this->gettype[$this->type][self::ASSOC];
        }
        switch ($this->type) {
            case self::PDO:
                return $this->result->fetchAll($mode);
            break;
            case self::MYSQLI:
            case self::MYSQL:
            default:
                throw new Acp_error('获取数据库结果集时，所需驱动类型错误！错误类型为 - ' . $this->type, Acp_error::PROGRAM);
        }
    }

//下面为实现迭代器接口
// 回到初始位。。相当于迭代前的初始操作
    public function rewind()
    {
        $this->position = 0;
        if (! $this->data) {
            $this->data = array($this->getAssoc());
        }
    }
// 验证当前位置有无值
    public function valid()
    {
        return !empty($this->data[$this->position]);
    }
// 获取位置的值
    public function current()
    {
        return $this->data[$this->position];
    }
// 获取当前位置的键
    public function key()
    {
        return $this->position;
    }
// 移动到下一个位置
    public function next()
    {
        $this->position ++;
        if (empty($this->data[$this->position])) {
            $this->data[$this->position] = $this->getAssoc();
        }
    }
}
