<?php
/**
     *@copyright Copyright 2014 listenlin. All rights reserved.
     *@author listenlin <listenlin521@foxmail.com>
     *@version 1.0
     *@package Acply\Acp_driver_pdo
     *
     *数据库PDO驱动类
     */
/**
 * 数据库PDO驱动类
 * 目前没有发挥prepare的准备一次，多次使用的效果。。
 * 需要抽时间去做好。
 */
class Acp_driver_pdo extends Acp_base implements Acp_driver_interface
{
    private static $permiss = '';
    /**
     * @var PDO
     */
    private $connection = null;
    /**
     * @var PDOStatement
     */
    private $statement = null;
    /**
     * @var array
     */
    private $sqlData = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->changeUser();
    }
    public function __destruct()
    {
        parent::__destruct();
        $this->connection = null;
    }
    private function testStatementError($result)
    {
        if ($this->statement->errorCode() !== '00000') {
            $cfg = Acp_config::getConfig();
            $error [] = "<h1 style='color:red'>prepare语句信息：</h1>";
            $error [] = print_r($result, true);
            $error [] = "<br /><h1 style='color:red'>预处理语句包含的详细信息：</h1>";
            ob_start();
            $this->statement->debugDumpParams();
            $error [] = ob_get_contents();
            ob_end_clean();
            $error = implode("\r\n", $error);
            if ($cfg->dbdebug === true) {
                // 调试
                echo $error;
            }
            Acp_log::log('DBdebug', $error);
            $message = $this->statement->errorInfo();
            throw new Acp_error($message [2], Acp_error::DB);
        }
    }
    private function testConnectionError()
    {
        if ($this->connection->errorCode() !== '00000') {
            $message = $this->connection->errorInfo();
            throw new Acp_error($message [2], Acp_error::DB);
        }
    }
    private function sql($sql_type, array $tb, array $ary)
    {
        // 接受SQL信息
        Acp_parse_SQL::receive($sql_type, $tb, $ary);
        // 生成PDO所需的格式
        $result = Acp_parse_SQL::makePrepare();
        //
        $this->sqlData = $result;
        // 建立PDO prepare
        $this->statement = $this->connection->prepare($result [0]);
        //p($result, TRUE);
        // 检测有无错误
        $this->testConnectionError();
        // 将参数绑定到prepare语句
        Acp_parse_SQL::bindParam($this->statement, $result [1]);
        // 执行SQL
        $this->statement->execute();
        // 检测执行有无错误
        $this->testStatementError($result);
    }
    // 返回查询到的总行数
    public function select(array $table_name, array $sql)
    {
        // MYSQL专属实现方法
        if (isset($sql ['repeat'])) {
            $sql ['repeat'] .= ' SQL_CALC_FOUND_ROWS';
        } else {
            $sql ['repeat'] = 'SQL_CALC_FOUND_ROWS';
        }
        $this->sql(Acp_parse_SQL::SELECT, $table_name, $sql);
        $result = $this->statement;
        $this->query('SELECT FOUND_ROWS()');
        // 返回一个结果对象
        return new Acp_result($result, $this->getResult()->getIndex(), Acp_result::PDO);
    }
    public function insert(array $table_name, array $sql)
    {
        $this->sql(Acp_parse_SQL::INSERT, $table_name, $sql);
        return $this->lastid();
    }
    public function update(array $table_name, array $sql)
    {
        $this->sql(Acp_parse_SQL::UPDATE, $table_name, $sql);
        return $this->affectRows();
    }
    public function delete(array $table_name, array $sql)
    {
        $this->sql(Acp_parse_SQL::DELETE, $table_name, $sql);
        return $this->affectRows();
    }
    public function query($sql)
    {
        $this->statement = $this->connection->query($sql . '');
        if ($this->statement === false) {
            throw new Acp_error('执行SQL语句出错，SQL:' . $sql, Acp_error::DB);
        }
        $this->testStatementError($sql);
        $this->testConnectionError();
        return $this->statement;
    }
    public function lastid()
    {
        return $this->connection->lastInsertId();
    }
    public function affectRows()
    {
        return $this->statement->rowCount();
    }
    // 分页的开始和最大限制，数据表名，和SQL信息
    public function splitPage($page, $limit, $t, $sql)
    {
        $sql ['other'] ['limit'] = array (
                $page => $limit
        );
        return $this->select($t, $sql);
    }
    public function getResult()
    {
        return new Acp_result($this->statement, false, Acp_result::PDO);
    }
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }
    public function commit()
    {
        $this->connection->commit();
    }
    public function rollBack()
    {
        $this->connection->rollBack();
    }
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }
    public function getError()
    {
        $this->testStatementError('没有信息！');
        $this->testConnectionError();
        return true;
    }
    public function getLastSql()
    {
        return $this->sqlData;
    }
    public function changeUser($qx = 'LOW')
    {
        if (self::$permiss === $qx && $this->connection !== null) {
            return;
        } else {
            self::$permiss = $qx;
        }
        $cfg = Acp_config::getConfig();
        $option = array (
                PDO::ATTR_EMULATE_PREPARES => false,//禁止PDO模拟预处理语句，而使用真正的预处理语句，即由MySQL执行预处理语句
                PDO::ATTR_PERSISTENT => false, // 持久连接
                PDO::ATTR_CASE => PDO::CASE_NATURAL, // 使列名按原始的方式
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        // PDO::CASE_LOWER：强制小写，PDO::CASE_UPPER：强制大写
        $option [PDO::ATTR_ERRMODE] = ($cfg->dbdebug === true ? PDO::ERRMODE_WARNING : PDO::ERRMODE_EXCEPTION);
        $db = $cfg->db . ':dbname=' . $cfg->dbname . ';host=' . $cfg->host.';charset=utf8';
        switch ($qx) {
            case 'HIGH':
                $user = (string)$cfg->userH;
                $pw = (string)$cfg->passwordH;
                break;
            case 'MIDDLE':
                $user = (string)$cfg->userM;
                $pw = (string)$cfg->passwordM;
                break;
            case 'LOW':
                $user = (string)$cfg->userL;
                $pw = (string)$cfg->passwordL;
                break;
            default:
                throw new Acp_error('数据库连接无此权限-' . $qx, Acp_error::DB);
        }
        $this->connection = null;
        $this->connection = new PDO($db, $user, $pw, $option);
    }


    public function getConnect()
    {
        return $this->connection;
    }
}
